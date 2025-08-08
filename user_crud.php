<?php
session_start();
require_once 'db.php';
require_once 'access_control.php';
require_once 'user_utils.php';

// Cek akses menggunakan utility function
require_login();

// Fungsi helper untuk logging - menggunakan utility function
function log_activity($action, $description) {
    log_user_activity($action, $description);
}

// CSRF Protection
function csrf_field() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return '<input type="hidden" name="csrf_token" value="' . $_SESSION['csrf_token'] . '">';
}

function csrf_verify() {
    return isset($_POST['csrf_token']) && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']);
}

$message = '';

// Create User
if (isset($_POST['create'])) {
    if (!csrf_verify()) {
        $message = 'CSRF token tidak valid!';
    } else {
        $stmt = $pdo->prepare('INSERT INTO users (display_name, full_name, email, tier, role, start_work, password, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
        $password_hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $stmt->execute([
            $_POST['display_name'],
            $_POST['full_name'],
            $_POST['email'],
            $_POST['tier'],
            $_POST['role'],
            $_POST['start_work'] ?: null,
            $password_hash,
            date('Y-m-d H:i:s')
        ]);
        $message = 'User created!';
        log_activity('create_user', 'Email: ' . $_POST['email']);
    }
}

// Update User
if (isset($_POST['update'])) {
    if (!csrf_verify()) {
        $message = 'CSRF token tidak valid!';
    } else {
        $stmt = $pdo->prepare('UPDATE users SET display_name=?, full_name=?, email=?, tier=?, role=?, start_work=? WHERE id=?');
        $stmt->execute([
            $_POST['display_name'],
            $_POST['full_name'],
            $_POST['email'],
            $_POST['tier'],
            $_POST['role'],
            $_POST['start_work'] ?: null,
            $_POST['id']
        ]);
        $message = 'User updated!';
        log_activity('update_user', 'User ID: ' . $_POST['id']);
    }
}

// Delete User
if (isset($_POST['delete'])) {
    if (!csrf_verify()) {
        $message = 'CSRF token tidak valid!';
    } else {
        $id = $_POST['id'];
        $stmt = $pdo->prepare('DELETE FROM users WHERE id = ?');
        $stmt->execute([$id]);
        $message = 'User deleted!';
        log_activity('delete_user', 'User ID: ' . $id);
    }
}

// Pagination dan filtering
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 10;
$offset = ($page - 1) * $limit;

$search = trim($_GET['search'] ?? '');
$filter_role = $_GET['filter_role'] ?? '';
$filter_tier = $_GET['filter_tier'] ?? '';

$where_conditions = [];
$params = [];

if ($search) {
    $where_conditions[] = "(full_name ILIKE ? OR email ILIKE ? OR display_name ILIKE ?)";
    $search_term = "%$search%";
    $params = array_merge($params, [$search_term, $search_term, $search_term]);
}

if ($filter_role) {
    $where_conditions[] = "role = ?";
    $params[] = $filter_role;
}

if ($filter_tier) {
    $where_conditions[] = "tier = ?";
    $params[] = $filter_tier;
}

$where_clause = $where_conditions ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Get total count
$count_sql = "SELECT COUNT(*) FROM users $where_clause";
$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute($params);
$total_users = $count_stmt->fetchColumn();
$total_pages = ceil($total_users / $limit);

// Get users with pagination
$sql = "SELECT * FROM users $where_clause ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include './partials/layouts/layoutHorizontal.php'; ?>

        <div class="dashboard-main-body">

            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
                <h6 class="fw-semibold mb-0">User List</h6>
                <ul class="d-flex align-items-center gap-2">
                    <li class="fw-medium">
                        <a href="index.php" class="d-flex align-items-center gap-1 hover-text-primary">
                            <iconify-icon icon="solar:home-smile-angle-outline" class="icon text-lg"></iconify-icon>
                            Dashboard
                        </a>
                    </li>
                    <li>-</li>
                    <li class="fw-medium">User List</li>
                </ul>
            </div>

            <div class="card">
                <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div class="d-flex flex-wrap align-items-center gap-3">
                        <div class="d-flex align-items-center gap-2">
                            <span>Show</span>
                            <select class="form-select form-select-sm w-auto">
                                <option>10</option>
                                <option>15</option>
                                <option>20</option>
                            </select>
                        </div>
                        <div class="icon-field">
                            <input type="text" name="search" class="form-control form-control-sm w-auto" placeholder="Search" value="<?= htmlspecialchars($search) ?>">
                            <span class="icon">
                                <iconify-icon icon="ion:search-outline"></iconify-icon>
                            </span>
                        </div>
                    </div>
                    <div class="d-flex flex-wrap align-items-center gap-3">
                        <select class="form-select form-select-sm w-auto" name="filter_role">
                            <option value="">All Roles</option>
                            <option value="Administrator" <?= $filter_role === 'Administrator' ? 'selected' : '' ?>>Administrator</option>
                            <option value="Management" <?= $filter_role === 'Management' ? 'selected' : '' ?>>Management</option>
                            <option value="Admin Office" <?= $filter_role === 'Admin Office' ? 'selected' : '' ?>>Admin Office</option>
                            <option value="User" <?= $filter_role === 'User' ? 'selected' : '' ?>>User</option>
                            <option value="Client" <?= $filter_role === 'Client' ? 'selected' : '' ?>>Client</option>
                        </select>
                        <a href="#" onclick="showCreateForm()" class="btn btn-sm btn-primary-600"><i class="ri-add-line"></i> Create User</a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if ($message): ?>
                        <div class="alert alert-info"> <?= htmlspecialchars($message) ?> </div>
                    <?php endif; ?>

                    <!-- Create User Form (Hidden by default) -->
                    <div id="createUserForm" style="display:none; margin-bottom:24px; padding:20px; border:1px solid #ddd; border-radius:8px; background:#f9f9f9;">
                        <h5 class="mb-3">Add New User</h5>
                        <form method="post" class="row g-3">
                            <?= csrf_field() ?>
                            <div class="col-md-6">
                                <label class="form-label">Display Name</label>
                                <input type="text" name="display_name" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Full Name *</label>
                                <input type="text" name="full_name" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email *</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Tier</label>
                                <select name="tier" class="form-select">
                                    <option value="New Born">New Born</option>
                                    <option value="Tier 1">Tier 1</option>
                                    <option value="Tier 2">Tier 2</option>
                                    <option value="Tier 3">Tier 3</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Role</label>
                                <select name="role" class="form-select">
                                    <option value="Administrator">Administrator</option>
                                    <option value="Management">Management</option>
                                    <option value="Admin Office">Admin Office</option>
                                    <option value="User">User</option>
                                    <option value="Client">Client</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Start Work</label>
                                <input type="date" name="start_work" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Password *</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <div class="col-12">
                                <button type="submit" name="create" class="btn btn-primary">Add User</button>
                                <button type="button" onclick="hideCreateForm()" class="btn btn-secondary">Cancel</button>
                            </div>
                        </form>
                    </div>

                    <table class="table bordered-table mb-0">
                        <thead>
                            <tr>
                                <th scope="col">
                                    <div class="form-check style-check d-flex align-items-center">
                                        <input class="form-check-input" type="checkbox" value="" id="checkAll">
                                        <label class="form-check-label" for="checkAll">
                                            S.L
                                        </label>
                                    </div>
                                </th>
                                <th scope="col">Name</th>
                                <th scope="col">Email</th>
                                <th scope="col">Tier</th>
                                <th scope="col">Role</th>
                                <th scope="col">Created Date</th>
                                <th scope="col">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $index => $u): ?>
                            <tr>
                                <td>
                                    <div class="form-check style-check d-flex align-items-center">
                                        <input class="form-check-input" type="checkbox" value="<?= $u['id'] ?>" id="check<?= $u['id'] ?>">
                                        <label class="form-check-label" for="check<?= $u['id'] ?>">
                                            <?= str_pad($index + 1 + $offset, 2, '0', STR_PAD_LEFT) ?>
                                        </label>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="assets/images/avatar/avatar1.png" alt="" class="flex-shrink-0 me-12 radius-8" style="width:40px;height:40px;">
                                        <h6 class="text-md mb-0 fw-medium flex-grow-1"><?= htmlspecialchars($u['display_name'] ?: $u['full_name']) ?></h6>
                                    </div>
                                </td>
                                <td><a href="mailto:<?= htmlspecialchars($u['email']) ?>" class="text-primary-600"><?= htmlspecialchars($u['email']) ?></a></td>
                                <td><span class="bg-neutral-200 text-neutral-600 px-8 py-4 rounded-pill fw-medium text-sm"><?= htmlspecialchars($u['tier']) ?></span></td>
                                <td><span class="bg-info-focus text-info-main px-8 py-4 rounded-pill fw-medium text-sm"><?= htmlspecialchars($u['role']) ?></span></td>
                                <td><?= date('d M Y', strtotime($u['created_at'])) ?></td>
                                <td>
                                    <a href="javascript:void(0)" onclick="editUser(<?= $u['id'] ?>)" class="w-32-px h-32-px bg-success-focus text-success-main rounded-circle d-inline-flex align-items-center justify-content-center">
                                        <iconify-icon icon="lucide:edit"></iconify-icon>
                                    </a>
                                    <a href="javascript:void(0)" onclick="deleteUser(<?= $u['id'] ?>)" class="w-32-px h-32-px bg-danger-focus text-danger-main rounded-circle d-inline-flex align-items-center justify-content-center">
                                        <iconify-icon icon="mingcute:delete-2-line"></iconify-icon>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <!-- Pagination -->
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
                </div>
            </div>
        </div>

<script>
function showCreateForm() {
    document.getElementById('createUserForm').style.display = 'block';
}

function hideCreateForm() {
    document.getElementById('createUserForm').style.display = 'none';
}

function editUser(userId) {
    // Implement edit functionality
    alert('Edit user functionality to be implemented');
}

function deleteUser(userId) {
    if (confirm('Are you sure you want to delete this user?')) {
        // Create form and submit
        const form = document.createElement('form');
        form.method = 'post';
        form.innerHTML = `
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="${userId}">
            <input type="hidden" name="delete" value="1">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<?php include './partials/layouts/layoutBottom.php'; ?>
