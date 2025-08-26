<?php 
session_start();
require_once 'db.php';
require_once 'access_control.php';
require_once 'user_utils.php';

// Cek akses menggunakan utility function
require_login();

// Role-based Access Control
$user_role = get_current_user_role();
if (!in_array($user_role, ['Administrator', 'Management', 'Admin Office'])) {
    $_SESSION['notification'] = ['type' => 'error', 'message' => 'Access Denied.'];
    header('Location: index.php');
    exit;
}

$can_create = check_access('user', 'create');
$can_update = check_access('user', 'update');

// Execute DB migration: move data from user_role -> role, then drop user_role
try {
    $cols = [];
    $stmtCols = $pdo->query("SELECT column_name FROM information_schema.columns WHERE table_name = 'users'");
    while ($r = $stmtCols->fetch(PDO::FETCH_ASSOC)) { $cols[$r['column_name']] = true; }
    if (!isset($cols['role'])) {
        try { $pdo->exec("ALTER TABLE users ADD COLUMN role VARCHAR(40) NULL"); } catch (Throwable $e) {}
        $cols['role'] = true;
    }
    if (isset($cols['user_role'])) {
        try { $pdo->exec("UPDATE users SET role = CASE WHEN role IS NULL OR role = '' THEN user_role ELSE role END WHERE user_role IS NOT NULL AND user_role <> ''"); } catch (Throwable $e) {}
        try { $pdo->exec("ALTER TABLE users DROP COLUMN user_role"); } catch (Throwable $e) {}
    }
} catch (Throwable $e) { /* ignore */ }

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create'])) {
        if (!$can_create) {
            $error_message = "You do not have permission to create users.";
        } else {
            // Create new user logic...
            $display_name = trim($_POST['display_name'] ?? '');
            $full_name = trim($_POST['full_name']);
            $email = trim($_POST['email']);
            $password = $_POST['password'];
            $tier = $_POST['tier'];
            $role = $_POST['role'];
            $start_work = $_POST['start_work'] ?: null;
            
            try {
                // Check if display_name already exists
                $check_display_name_sql = "SELECT id FROM users WHERE display_name = :display_name";
                $check_display_name_stmt = $pdo->prepare($check_display_name_sql);
                $check_display_name_stmt->execute(['display_name' => $display_name]);
                
                if ($check_display_name_stmt->fetch()) {
                    $error_message = "Display name is already taken! Please choose a different one.";
                } else {
                    // Check if email already exists
                    $check_email_sql = "SELECT id FROM users WHERE email = :email";
                    $check_email_stmt = $pdo->prepare($check_email_sql);
                    $check_email_stmt->execute(['email' => $email]);
                    
                    if ($check_email_stmt->fetch()) {
                        $error_message = "Email is already registered!";
                    } else {
                        // Set default values if not provided
                        $tier = $tier ?: 'New Born';
                        $role = $role ?: 'User';
                        
                        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                        $insert_sql = "INSERT INTO users (display_name, full_name, email, password, tier, role, start_work, created_at) VALUES (:display_name, :full_name, :email, :password, :tier, :role, :start_work, NOW())";
                        $insert_stmt = $pdo->prepare($insert_sql);
                        $insert_stmt->execute([
                            'display_name' => $display_name,
                            'full_name' => $full_name,
                            'email' => $email,
                            'password' => $hashed_password,
                            'tier' => $tier,
                            'role' => $role,
                            'start_work' => $start_work
                        ]);
                        
                        $_SESSION['notification'] = ['type' => 'success', 'message' => 'User created successfully!'];
                        header("Location: users.php");
                        exit;
                    }
                }
            } catch (PDOException $e) {
                $error_message = "Error: " . $e->getMessage();
            }
        }
    } elseif (isset($_POST['update'])) {
        if (!$can_update) {
            $error_message = "You do not have permission to update users.";
        } else {
                         // Update existing user logic...
             $user_id = $_POST['user_id'];
             $display_name = trim($_POST['display_name'] ?? '');
             $full_name = trim($_POST['full_name']);
             $tier = $_POST['tier'];
             $role = $_POST['role'];
             $start_work = $_POST['start_work'] ?: null;
            $new_password = $_POST['new_password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';
            
            try {
                $curr_stmt = $pdo->prepare("SELECT role AS current_role, tier FROM users WHERE id = :user_id");
                $curr_stmt->execute(['user_id' => $user_id]);
                $current = $curr_stmt->fetch(PDO::FETCH_ASSOC);
                $current_role = $current['current_role'] ?? null;

                $tierToSave = ($tier === '' || $tier === null) ? $current['tier'] : $tier;
                $roleToSave = ($role === '' || $role === null) ? $current_role : $role;
                
                $setParts = ['full_name = :full_name', 'tier = :tier', 'role = :role', 'start_work = :start_work'];
                $params = [
                    'full_name' => $full_name,
                    'tier' => $tierToSave,
                    'role' => $roleToSave,
                    'start_work' => $start_work,
                    'user_id' => $user_id
                ];

                if ($new_password !== '') {
                    if ($new_password !== $confirm_password) {
                        throw new PDOException('Password confirmation does not match.');
                    }
                    $params['password'] = password_hash($new_password, PASSWORD_DEFAULT);
                    $setParts[] = 'password = :password';
                }

                $update_sql = 'UPDATE users SET ' . implode(', ', $setParts) . ' WHERE id = :user_id';
                $update_stmt = $pdo->prepare($update_sql);
                $update_stmt->execute($params);

                $_SESSION['notification'] = ['type' => 'success', 'message' => 'User updated successfully!'];
                header("Location: users.php");
                exit;
                         } catch (PDOException $e) {
                 $error_message = "Error: " . $e->getMessage();
             }
        }
    }
}

// Get users from database
$search = trim($_GET['search'] ?? '');
$filter_role = trim($_GET['filter_role'] ?? '');
$filter_tier = trim($_GET['filter_tier'] ?? '');

$where_conditions = [];
$params = [];

if ($search) {
    $where_conditions[] = "(id::text ILIKE :search OR display_name ILIKE :search OR full_name ILIKE :search OR email ILIKE :search)";
    $params['search'] = "%$search%";
}

if ($filter_role) {
    $where_conditions[] = "role = :role";
    $params['role'] = $filter_role;
}

if ($filter_tier) {
    $where_conditions[] = "tier = :tier";
    $params['tier'] = $filter_tier;
}

$where_clause = '';
if (!empty($where_conditions)) {
    $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
}

// Pagination
$page = max(1, intval($_GET['page'] ?? 1));
$limit = intval($_GET['limit'] ?? 10); if (!in_array($limit, [10,15,20], true)) { $limit = 10; }
$offset = ($page - 1) * $limit;

// Count total
$count_sql = "SELECT COUNT(*) FROM users $where_clause";
$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute($params);
$total_users = $count_stmt->fetchColumn();
$total_pages = ceil($total_users / $limit);

// Get users with pagination
$sql = "SELECT id, display_name, full_name, email, tier, role, start_work, created_at FROM users $where_clause ORDER BY id DESC LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

include './partials/layouts/layoutHorizontal.php' 
?>

        <div class="dashboard-main-body">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
                <h6 class="fw-semibold mb-0">Users List</h6>
                <ul class="d-flex align-items-center gap-2">
                    <li class="fw-medium">
                        <a href="index.php" class="d-flex align-items-center gap-1 hover-text-primary">
                            <iconify-icon icon="solar:home-smile-angle-outline" class="icon text-lg"></iconify-icon>
                            Dashboard
                        </a>
                    </li>
                    <li>-</li>
                    <li class="fw-medium">Users List</li>
                </ul>
            </div>

            <div class="card h-100 radius-12">
                <div class="card-body p-24">
                    <!-- Filter Section -->
                    <div class="filter-section">
                        <form method="get" class="filter-form" action="users.php">
                            <div class="filter-row">
                                <div class="filter-group">
                                    <label class="filter-label">Search</label>
                                    <input type="text" name="search" class="form-control" placeholder="Search by ID, Display Name, Full Name, Email..." value="<?= htmlspecialchars($search) ?>">
                                </div>
                                <div class="filter-group">
                                    <label class="filter-label">Role</label>
                                    <select class="form-select" name="filter_role">
                                        <option value="">All Roles</option>
                                        <option value="Administrator" <?= $filter_role === 'Administrator' ? 'selected' : '' ?>>Administrator</option>
                                        <option value="Management" <?= $filter_role === 'Management' ? 'selected' : '' ?>>Management</option>
                                        <option value="Admin Office" <?= $filter_role === 'Admin Office' ? 'selected' : '' ?>>Admin Office</option>
                                        <option value="User" <?= $filter_role === 'User' ? 'selected' : '' ?>>User</option>
                                        <option value="Client" <?= $filter_role === 'Client' ? 'selected' : '' ?>>Client</option>
                                    </select>
                                </div>
                                <div class="filter-group">
                                    <label class="filter-label">Tier</label>
                                    <select class="form-select" name="filter_tier">
                                        <option value="">All Tiers</option>
                                        <option value="New Born" <?= $filter_tier === 'New Born' ? 'selected' : '' ?>>New Born</option>
                                        <option value="Tier 1" <?= $filter_tier === 'Tier 1' ? 'selected' : '' ?>>Tier 1</option>
                                        <option value="Tier 2" <?= $filter_tier === 'Tier 2' ? 'selected' : '' ?>>Tier 2</option>
                                        <option value="Tier 3" <?= $filter_tier === 'Tier 3' ? 'selected' : '' ?>>Tier 3</option>
                                    </select>
                                </div>
                            </div>
                            <div class="filter-buttons">
                                <button type="submit" class="btn btn-primary">Apply Filters</button>
                                <a href="users.php" class="btn btn-secondary">Reset</a>
                                <?php if ($can_create): ?>
                                <button type="button" class="btn btn-success" onclick="showModal('createModal'); return false;">Add User</button>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                
                    <?php if (isset($error_message)): ?>
                        <div class="alert alert-danger m-3" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($error_message) ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th scope="col" style="width: 25%;"><div class="table-header">Display Name</div></th>
                                    <th scope="col" style="width: 25%;"><div class="table-header">Full Name</div></th>
                                    <th scope="col" style="width: 15%;"><div class="table-header">Email</div></th>
                                    <th scope="col" style="width: 12%;"><div class="table-header text-center">Tier</div></th>
                                    <th scope="col" style="width: 13%;"><div class="table-header text-center">Role</div></th>
                                    <th scope="col" style="width: 30%;"><div class="table-header text-center">Start Work</div></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $u): ?>
                                <tr class="user-row" data-id="<?= $u['id'] ?>" data-display_name="<?= htmlspecialchars($u['display_name'] ?? '') ?>" data-full_name="<?= htmlspecialchars($u['full_name'] ?? $u['name'] ?? '') ?>" data-email="<?= htmlspecialchars($u['email'] ?? '') ?>" data-role="<?= htmlspecialchars($u['role'] ?? '') ?>" data-tier="<?= htmlspecialchars($u['tier'] ?? '') ?>" data-start_work="<?= htmlspecialchars($u['start_work'] ?? '') ?>">
                                    <td style="width: 25%;"><?= htmlspecialchars($u['display_name'] ?: '-') ?></td>
                                    <td style="width: 25%;"><?= htmlspecialchars($u['full_name'] ?? $u['name'] ?: '-') ?></td>
                                    <td style="width: 15%;"><?= htmlspecialchars($u['email'] ?: '-') ?></td>
                                    <td style="width: 12%; text-align: center;"><span class="priority-badge bg-neutral-200 text-neutral-600 px-8 py-4 rounded-pill fw-medium text-sm"><?= htmlspecialchars($u['tier'] ?: '-') ?></span></td>
                                    <td style="width: 12%; text-align: center;"><span class="priority-badge bg-info-focus text-info-main px-8 py-4 rounded-pill fw-medium text-sm"><?= htmlspecialchars($u['role'] ?: '-') ?></span></td>
                                    <td style="width: 30%; text-align: center;"><?= $u['start_work'] ? date('d M Y', strtotime($u['start_work'])) : '-' ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Footer: info + pagination -->
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mt-24">
                <span class="text-md text-secondary-light fw-normal">Showing <?= count($users) ?> of <?= $total_users ?> results</span>
                <?php if ($total_pages > 1): ?>
                <ul class="pagination d-flex flex-wrap align-items-center gap-2 justify-content-center">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <?php if ($i == $page): ?>
                            <li class="page-item">
                                <a class="page-link bg-primary-600 text-white rounded-8 fw-medium text-md px-9 py-6" href="#"><?= $i ?></a>
                            </li>
                        <?php else: ?>
                            <li class="page-item">
                                <a class="page-link bg-neutral-200 text-secondary-light rounded-8 fw-medium text-md px-9 py-6 hover-bg-primary-600 hover-text-white" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
                            </li>
                        <?php endif; ?>
                    <?php endfor; ?>
                </ul>
                <?php endif; ?>
            </div>

            <!-- Create User Modal -->
            <?php if ($can_create): ?>
            <div class="custom-modal-overlay" id="createModal">
                <div class="custom-modal">
                    <div class="custom-modal-header">
                        <h3 class="custom-modal-title">Add New User</h3>
                        <button type="button" class="custom-modal-close" onclick="hideModal('createModal')">&times;</button>
                    </div>
                    <div class="custom-modal-body">
                        <form id="createUserForm" action="users.php" method="post">
                            <div class="custom-modal-row">
                                <div class="custom-modal-col">
                                    <label class="custom-modal-label">Display Name *</label>
                                    <input type="text" name="display_name" class="custom-modal-input" placeholder="Unique display name" required>
                                    <small style="color: #6b7280; font-size: 11px;">Display name and email must be unique and cannot be changed later</small>
                                </div>
                                <div class="custom-modal-col">
                                    <label class="custom-modal-label">Full Name *</label>
                                    <input type="text" name="full_name" class="custom-modal-input" required>
                                </div>
                            </div>
                            <div class="custom-modal-row">
                                <div class="custom-modal-col">
                                    <label class="custom-modal-label">Email *</label>
                                    <input type="email" name="email" class="custom-modal-input" required>
                                    <small style="color: #6b7280; font-size: 11px;">Email cannot be changed after creation</small>
                                </div>
                                <div class="custom-modal-col">
                                    <label class="custom-modal-label">Password *</label>
                                    <input type="password" name="password" class="custom-modal-input" required>
                                </div>
                            </div>
                            <div class="custom-modal-row">
                                <div class="custom-modal-col">
                                    <label class="custom-modal-label">Tier *</label>
                                    <select name="tier" class="custom-modal-select" required>
                                        <option value="New Born" selected>New Born</option>
                                        <option value="Tier 1">Tier 1</option>
                                        <option value="Tier 2">Tier 2</option>
                                        <option value="Tier 3">Tier 3</option>
                                    </select>
                                </div>
                                <div class="custom-modal-col">
                                    <label class="custom-modal-label">Role *</label>
                                    <select name="role" class="custom-modal-select" required>
                                        <option value="User" selected>User</option>
                                        <option value="Administrator">Administrator</option>
                                        <option value="Management">Management</option>
                                        <option value="Admin Office">Admin Office</option>
                                        <option value="Client">Client</option>
                                    </select>
                                </div>
                            </div>
                            <div class="custom-modal-row">
                                <div class="custom-modal-col">
                                    <label class="custom-modal-label">Start Work</label>
                                    <input type="date" name="start_work" class="custom-modal-input">
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="custom-modal-footer">
                        <button type="submit" form="createUserForm" name="create" class="custom-btn custom-btn-primary">Save</button>
                        <button type="button" class="custom-btn custom-btn-secondary" onclick="hideModal('createModal')">Close</button>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Edit User Modal -->
            <?php if ($can_update): ?>
            <div class="custom-modal-overlay" id="editModal">
                <div class="custom-modal">
                    <div class="custom-modal-header">
                        <h3 class="custom-modal-title">Edit User</h3>
                        <button type="button" class="custom-modal-close" onclick="hideModal('editModal')">&times;</button>
                    </div>
                    <div class="custom-modal-body">
                        <form id="editUserForm" action="users.php" method="post">
                            <input type="hidden" name="user_id" id="edit_user_id">
                            <div class="custom-modal-row">
                                <div class="custom-modal-col">
                                    <label class="custom-modal-label">Display Name</label>
                                    <input type="text" name="display_name" id="edit_display_name" class="custom-modal-input" placeholder="Display name" readonly style="background-color: #f3f4f6; cursor: not-allowed;">
                                    <small style="color: #6b7280; font-size: 11px;">Display name cannot be changed after creation</small>
                                </div>
                                <div class="custom-modal-col">
                                    <label class="custom-modal-label">Full Name *</label>
                                    <input type="text" name="full_name" id="edit_full_name" class="custom-modal-input" required>
                                </div>
                            </div>
                            <div class="custom-modal-row">
                                <div class="custom-modal-col">
                                    <label class="custom-modal-label">Email *</label>
                                    <input type="email" name="email" id="edit_email" class="custom-modal-input" required readonly style="background-color: #f3f4f6; cursor: not-allowed;">
                                    <small style="color: #6b7280; font-size: 11px;">Email cannot be changed after creation</small>
                                </div>
                                <div class="custom-modal-col">
                                    <label class="custom-modal-label">Start Work</label>
                                    <input type="date" name="start_work" id="edit_start_work" class="custom-modal-input">
                                </div>
                            </div>

                            <div class="custom-modal-row">
                                <div class="custom-modal-col">
                                    <label class="custom-modal-label">Tier</label>
                                    <select name="tier" id="edit_tier" class="custom-modal-select">
                                        <option value="New Born">New Born</option>
                                        <option value="Tier 1">Tier 1</option>
                                        <option value="Tier 2">Tier 2</option>
                                        <option value="Tier 3">Tier 3</option>
                                    </select>
                                </div>
                                <div class="custom-modal-col">
                                    <label class="custom-modal-label">Role</label>
                                    <select name="role" id="edit_role" class="custom-modal-select">
                                        <option value="User">User</option>
                                        <option value="Administrator">Administrator</option>
                                        <option value="Management">Management</option>
                                        <option value="Admin Office">Admin Office</option>
                                        <option value="Client">Client</option>
                                    </select>
                                </div>
                            </div>
                            <div class="custom-modal-row">
                                <div class="custom-modal-col">
                                    <label class="custom-modal-label">New Password</label>
                                    <input type="password" name="new_password" id="edit_new_password" class="custom-modal-input" placeholder="Leave blank to keep current">
                                </div>
                                <div class="custom-modal-col">
                                    <label class="custom-modal-label">Confirm Password</label>
                                    <input type="password" name="confirm_password" id="edit_confirm_password" class="custom-modal-input" placeholder="Repeat new password">
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="custom-modal-footer">
                        <button type="submit" form="editUserForm" name="update" class="custom-btn custom-btn-primary">Save</button>
                        <button type="button" class="custom-btn custom-btn-secondary" onclick="hideModal('editModal')">Close</button>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php 
            include './partials/layouts/layoutBottom.php';
            ?>
            
            <style>
            /* Filter Section Styles */
            .filter-section{padding:1rem;margin-bottom:1rem;background:#f8fafc;border:1px solid #e5e7eb;border-radius:.5rem}
            .filter-form .filter-row{display:flex;flex-wrap:wrap;gap:1rem;margin-bottom:1rem}
            .filter-form .filter-group{flex:1;min-width:150px}
            .filter-form .filter-label{font-weight:600;font-size:12px;color:#374151;margin-bottom:6px;display:block}
            .filter-form .filter-buttons{display:flex;gap:.5rem}
            [data-theme="dark"] .filter-section{background:#1f2937;border-color:#374151}
            [data-theme="dark"] .filter-label{color:#e5e7eb}
            [data-theme="dark"] .form-control, [data-theme="dark"] .form-select{background-color:#111827;border-color:#374151;color:#e5e7eb}

            /* Modal styles - sama persis seperti activity.php */
            .custom-modal-overlay {
                position: fixed;
                inset: 0;
                background: rgba(0,0,0,.5);
                display: none;
                align-items: center;
                justify-content: center;
                z-index: 1050;
            }
            
            .custom-modal-overlay.show {
                display: flex;
            }
            
            .custom-modal {
                width: min(980px, 96vw);
                background: #fff;
                border-radius: 12px;
                overflow: hidden;
                box-shadow: 0 20px 60px rgba(0,0,0,.2);
            }
            
            .custom-modal-header {
                padding: 16px 20px;
                display: flex;
                align-items: center;
                justify-content: space-between;
                background: linear-gradient(135deg, #0ea5e9 0%, #3b82f6 100%);
                color: #fff;
            }
            
            .custom-modal-title {
                margin: 0;
                font-size: 18px;
                font-weight: 600;
            }
            
            .custom-modal-close {
                background: transparent;
                border: 0;
                color: #fff;
                font-size: 22px;
                cursor: pointer;
                line-height: 1;
            }
            
            .custom-modal-body {
                padding: 20px;
            }
            
            .custom-modal-footer {
                padding: 14px 20px;
                background: #f8fafc;
                display: flex;
                gap: 10px;
                justify-content: flex-end;
            }
            
            .custom-modal-row {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 16px;
                margin-bottom: 12px;
            }
            
            .custom-modal-col {
                display: flex;
                flex-direction: column;
                gap: 6px;
            }
            
            .custom-modal-label {
                font-weight: 600;
                font-size: 12px;
                color: #111827;
            }
            
            .custom-modal-input, .custom-modal-select, .custom-modal-textarea {
                width: 100%;
                padding: 10px 12px;
                border-radius: 8px;
                border: 1px solid #e5e7eb;
                background: #fff;
                box-sizing: border-box;
            }
            
            .custom-modal-input:focus, .custom-modal-select:focus, .custom-modal-textarea:focus {
                outline: none;
                border-color: #3b82f6;
                box-shadow: 0 0 0 3px rgba(59,130,246,.15);
            }
            
            .custom-btn {
                padding: 10px 14px;
                border-radius: 8px;
                border: 0;
                cursor: pointer;
                font-weight: 600;
            }
            
            .custom-btn-primary {
                background: #2563eb;
                color: #fff;
            }
            
            .custom-btn-secondary {
                background: #374151;
                color: #fff;
            }
            
            /* Hide modals by default */
            .custom-modal-overlay {
                display: none;
            }
            
            /* Show modal when needed */
            .custom-modal-overlay.show {
                display: flex;
            }
            
            /* Table header styling */
            .table-header { 
                padding: 12px 16px; 
                background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); 
                border: none; 
                border-radius: 8px; 
                margin: 0; 
                font-weight: 600; 
                color: white; 
                font-size: 12px; 
                text-transform: uppercase; 
                letter-spacing: .5px; 
                text-align: center; 
                box-shadow: 0 2px 8px rgba(79,70,229,.3); 
                position: relative; 
                overflow: hidden; 
                display: flex; 
                align-items: center; 
                justify-content: center; 
                height: 100%; 
                min-height: 52px; 
            }
            .table-header::before { 
                content: ''; 
                position: absolute; 
                top: 0; 
                left: -100%; 
                width: 100%; 
                height: 100%; 
                background: linear-gradient(90deg, transparent, rgba(255,255,255,.2), transparent); 
                transition: left .5s; 
            }
            .table-header:hover::before { 
                left: 100%; 
            }
            
            /* Dark mode support */
            [data-theme="dark"] .custom-modal { 
                background: #111827; 
                color: #e5e7eb; 
                border: 1px solid #374151; 
            }
            [data-theme="dark"] .custom-modal-header { 
                background: linear-gradient(135deg, #0b1220 0%, #111827 100%); 
            }
            [data-theme="dark"] .custom-modal-footer { 
                background: #0b1220; 
            }
            [data-theme="dark"] .custom-modal-input, [data-theme="dark"] .custom-modal-select, [data-theme="dark"] .custom-modal-textarea { 
                background: #0b1220; 
                border-color: #374151; 
                color: #e5e7eb; 
            }
            [data-theme="dark"] .custom-modal-label { 
                color: #e5e7eb; 
            }
            
            /* Alert messages styling */
            .alert {
                border: none;
                border-radius: 8px;
                padding: 12px 16px;
                margin: 16px 24px;
                font-weight: 500;
            }
            
            .alert-success {
                background: #d1fae5;
                color: #065f46;
                border-left: 4px solid #10b981;
            }
            
            .alert-danger {
                background: #fee2e2;
                color: #991b1b;
                border-left: 4px solid #ef4444;
            }
            
            .alert i {
                font-size: 16px;
            }
            
            @media (max-width: 768px) { 
                .custom-modal-row { 
                    grid-template-columns: 1fr; 
                } 
            }
            </style>
            
            <script>
            // Very simple modal functions
            function showModal(modalId) {
                var modal = document.getElementById(modalId);
                if (modal) {
                    modal.classList.add('show');
                }
            }
            
            function hideModal(modalId) {
                var modal = document.getElementById(modalId);
                if (modal) {
                    modal.classList.remove('show');
                }
            }
            
            // Close modal when clicking outside
            document.addEventListener('click', function(e) {
                if (e.target.classList.contains('custom-modal-overlay')) {
                    e.target.classList.remove('show');
                }
            });
            
            // ESC key to close modals
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    var modals = document.querySelectorAll('.custom-modal-overlay.show');
                    modals.forEach(function(modal) {
                        modal.classList.remove('show');
                    });
                }
            });
            
            // Initialize page
            document.addEventListener('DOMContentLoaded', function() {
                <?php if ($can_update): ?>
                // Add click handlers to user rows for editing
                var userRows = document.querySelectorAll('.user-row');
                userRows.forEach(function(row) {
                    row.style.cursor = 'pointer';
                    row.addEventListener('click', function() {
                        var userId = row.getAttribute('data-id');
                        
                        // Populate edit form
                        document.getElementById('edit_user_id').value = userId;
                        document.getElementById('edit_display_name').value = row.getAttribute('data-display_name') || '';
                        document.getElementById('edit_full_name').value = row.getAttribute('data-full_name') || '';
                        document.getElementById('edit_email').value = row.getAttribute('data-email') || '';
                        document.getElementById('edit_role').value = row.getAttribute('data-role') || '';
                        document.getElementById('edit_tier').value = row.getAttribute('data-tier') || '';
                        document.getElementById('edit_start_work').value = row.getAttribute('data-start_work') || '';
                        
                        showModal('editModal');
                    });
                });
                <?php endif; ?>
            });
            </script>