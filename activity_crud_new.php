<?php
session_start();
require_once 'db.php';
require_once 'access_control.php';

// Cek akses
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Fungsi helper untuk logging
function log_activity($action, $description) {
    global $pdo;
    $stmt = $pdo->prepare('INSERT INTO logs (user_email, action, description, created_at) VALUES (?, ?, ?, ?)');
    $stmt->execute([$_SESSION['email'] ?? 'unknown', $action, $description, date('Y-m-d H:i:s')]);
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
$message_type = '';

// Create Activity
if (isset($_POST['create'])) {
    if (!csrf_verify()) {
        $message = 'CSRF token tidak valid!';
        $message_type = 'error';
    } else {
        try {
            $stmt = $pdo->prepare('INSERT INTO activities (information_date, priority, user_position, department, application, type, project_id, customer, cnc_number, completed_date, status, description, action_solution, created_by, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([
                $_POST['information_date'],
                $_POST['priority'],
                $_POST['user_position'] ?: null,
                $_POST['department'],
                $_POST['application'],
                $_POST['type'],
                $_POST['project_id'] ?: null,
                $_POST['customer'] ?: null,
                $_POST['cnc_number'] ?: null,
                $_POST['completed_date'] ?: null,
                $_POST['status'],
            $_POST['description'],
                $_POST['action_solution'] ?: null,
                $_SESSION['user_id'],
            date('Y-m-d H:i:s')
        ]);
        $message = 'Activity created!';
            $message_type = 'success';
            log_activity('create_activity', 'Activity: ' . $_POST['description']);
        } catch (Exception $e) {
            $message = 'Error creating activity: ' . $e->getMessage();
            $message_type = 'error';
        }
    }
}

// Update Activity
if (isset($_POST['update'])) {
    if (!csrf_verify()) {
        $message = 'CSRF token tidak valid!';
        $message_type = 'error';
    } else {
        try {
            $stmt = $pdo->prepare('UPDATE activities SET information_date=?, priority=?, user_position=?, department=?, application=?, type=?, project_id=?, customer=?, cnc_number=?, completed_date=?, status=?, description=?, action_solution=? WHERE id=?');
        $stmt->execute([
                $_POST['information_date'],
                $_POST['priority'],
                $_POST['user_position'] ?: null,
                $_POST['department'],
                $_POST['application'],
                $_POST['type'],
                $_POST['project_id'] ?: null,
                $_POST['customer'] ?: null,
                $_POST['cnc_number'] ?: null,
                $_POST['completed_date'] ?: null,
                $_POST['status'],
            $_POST['description'],
                $_POST['action_solution'] ?: null,
            $_POST['id']
        ]);
        $message = 'Activity updated!';
            $message_type = 'info';
        log_activity('update_activity', 'Activity ID: ' . $_POST['id']);
        } catch (Exception $e) {
            $message = 'Error updating activity: ' . $e->getMessage();
            $message_type = 'error';
        }
    }
}

// Cancel Activity (Update status to Cancel)
if (isset($_POST['cancel'])) {
    if (!csrf_verify()) {
        $message = 'CSRF token tidak valid!';
        $message_type = 'error';
    } else {
        try {
            $stmt = $pdo->prepare('UPDATE activities SET status=? WHERE id=?');
            $stmt->execute(['Cancel', $_POST['id']]);
            $message = 'Activity canceled!';
            $message_type = 'warning';
            log_activity('cancel_activity', 'Activity ID: ' . $_POST['id']);
        } catch (Exception $e) {
            $message = 'Error canceling activity: ' . $e->getMessage();
            $message_type = 'error';
        }
    }
}

// Pagination dan filtering
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 10;
$offset = ($page - 1) * $limit;

$search = trim($_GET['search'] ?? '');
$filter_status = $_GET['filter_status'] ?? '';

$where_conditions = [];
$params = [];

if ($search) {
    $where_conditions[] = "(description ILIKE ? OR cnc_number ILIKE ?)";
    $search_term = "%$search%";
    $params = array_merge($params, [$search_term, $search_term]);
}

if ($filter_status) {
    $where_conditions[] = "status = ?";
    $params[] = $filter_status;
}

// Priority filter removed as it doesn't exist in PostgreSQL schema

$where_clause = $where_conditions ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Get total count
$count_sql = "SELECT COUNT(*) FROM activities $where_clause";
$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute($params);
$total_activities = $count_stmt->fetchColumn();
$total_pages = ceil($total_activities / $limit);

// Get activities with pagination
$sql = "SELECT a.*, p.project_name, u.display_name as created_by_name FROM activities a LEFT JOIN projects p ON a.project_id = p.project_id LEFT JOIN users u ON a.created_by = u.id $where_clause ORDER BY a.created_at DESC LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$activities = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get projects for dropdown
$projects = $pdo->query('SELECT project_id, project_name FROM projects ORDER BY project_name')->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include './partials/layouts/layoutHorizontal.php'; ?>

<!-- Include notification system -->
<script src="assets/js/logo-notifications.js"></script>

<style>
/* Custom table styling for better spacing and layout */
.table {
    font-size: 0.875rem;
}

.table th {
    background-color: #f8f9fa;
    border-bottom: 2px solid #dee2e6;
    font-weight: 600;
    padding: 12px 8px;
    white-space: nowrap;
}

.table td {
    padding: 12px 8px;
    border-bottom: 1px solid #dee2e6;
    vertical-align: middle;
}

/* Column width adjustments */
.table th:nth-child(1), .table td:nth-child(1) { width: 5%; }  /* No */
.table th:nth-child(2), .table td:nth-child(2) { width: 12%; } /* Information Date */
.table th:nth-child(3), .table td:nth-child(3) { width: 10%; } /* Priority */
.table th:nth-child(4), .table td:nth-child(4) { width: 12%; } /* Department */
.table th:nth-child(5), .table td:nth-child(5) { width: 12%; } /* Application */
.table th:nth-child(6), .table td:nth-child(6) { width: 10%; } /* Type */
.table th:nth-child(7), .table td:nth-child(7) { width: 25%; } /* Description */
.table th:nth-child(8), .table td:nth-child(8) { width: 10%; } /* Status */
.table th:nth-child(9), .table td:nth-child(9) { width: 14%; } /* Created By */

/* Badge styling improvements */
.badge, .rounded-pill {
    font-size: 0.75rem;
    padding: 4px 8px;
    white-space: nowrap;
}

/* Description column text truncation */
.table td:nth-child(7) h6 {
    margin-bottom: 4px;
    line-height: 1.3;
}

.table td:nth-child(7) span {
    font-size: 0.75rem;
    opacity: 0.8;
}

/* Hover effect for table rows */
.table tbody tr:hover {
    background-color: #f8f9fa;
    transition: background-color 0.2s ease;
}

/* Responsive table */
@media (max-width: 768px) {
    .table {
        font-size: 0.8rem;
    }
    
    .table th, .table td {
        padding: 8px 4px;
    }
    
    .badge, .rounded-pill {
        font-size: 0.7rem;
        padding: 3px 6px;
    }
}
</style>

        <div class="dashboard-main-body">

            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
                <h6 class="fw-semibold mb-0">Activity List</h6>
                <ul class="d-flex align-items-center gap-2">
                    <li class="fw-medium">
                        <a href="index.php" class="d-flex align-items-center gap-1 hover-text-primary">
                            <iconify-icon icon="solar:home-smile-angle-outline" class="icon text-lg"></iconify-icon>
                            Dashboard
                        </a>
                    </li>
                    <li>-</li>
                    <li class="fw-medium">Activity List</li>
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
                        <select class="form-select form-select-sm w-auto" name="filter_status">
                            <option value="">All Status</option>
                            <option value="Open" <?= $filter_status === 'Open' ? 'selected' : '' ?>>Open</option>
                            <option value="On Progress" <?= $filter_status === 'On Progress' ? 'selected' : '' ?>>On Progress</option>
                            <option value="Need Requirement" <?= $filter_status === 'Need Requirement' ? 'selected' : '' ?>>Need Requirement</option>
                            <option value="Done" <?= $filter_status === 'Done' ? 'selected' : '' ?>>Done</option>
                            <option value="Cancel" <?= $filter_status === 'Cancel' ? 'selected' : '' ?>>Cancel</option>
                        </select>
                        <a href="#" onclick="showCreateForm()" class="btn btn-sm btn-primary-600"><i class="ri-add-line"></i> Create Activity</a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if ($message): ?>
                        <div class="alert alert-<?= $message_type ?> alert-dismissible fade show" role="alert">
                            <?= htmlspecialchars($message) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Create Activity Form (Hidden by default) -->
                    <div id="createActivityForm" style="display:none; margin-bottom:24px; padding:20px; border:1px solid #ddd; border-radius:8px; background:#f9f9f9;">
                        <h5 class="mb-3">Add New Activity</h5>
                        <form method="post" class="row g-3">
                            <?= csrf_field() ?>
                            <div class="col-md-6">
                                <label class="form-label">Information Date *</label>
                                <input type="date" name="information_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Priority *</label>
                                <select name="priority" class="form-select" required>
                                    <option value="Low">Low</option>
                                    <option value="Normal" selected>Normal</option>
                                    <option value="Hard">Hard</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">User & Position</label>
                                <input type="text" name="user_position" class="form-control" placeholder="Enter user and position">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Department *</label>
                                <select name="department" class="form-select" required>
                                    <option value="">Select Department</option>
                                    <option value="Food & Beverage">Food & Beverage</option>
                                    <option value="Kitchen">Kitchen</option>
                                    <option value="Room Division">Room Division</option>
                                    <option value="Front Office">Front Office</option>
                                    <option value="Housekeeping">Housekeeping</option>
                                    <option value="Engineering">Engineering</option>
                                    <option value="Sales & Marketing">Sales & Marketing</option>
                                    <option value="IT / EDP">IT / EDP</option>
                                    <option value="Accounting">Accounting</option>
                                    <option value="Executive Office">Executive Office</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Application *</label>
                                <select name="application" class="form-select" required>
                                    <option value="Power FO" selected>Power FO</option>
                                    <option value="My POS">My POS</option>
                                    <option value="My MGR">My MGR</option>
                                    <option value="Power AR">Power AR</option>
                                    <option value="Power INV">Power INV</option>
                                    <option value="Power AP">Power AP</option>
                                    <option value="Power GL">Power GL</option>
                                    <option value="Keylock">Keylock</option>
                                    <option value="PABX">PABX</option>
                                    <option value="DIM">DIM</option>
                                    <option value="Dynamic Room Rate">Dynamic Room Rate</option>
                                    <option value="Channel Manager">Channel Manager</option>
                                    <option value="PB1">PB1</option>
                                    <option value="Power SIGN">Power SIGN</option>
                                    <option value="Multi Properties">Multi Properties</option>
                                    <option value="Scanner ID">Scanner ID</option>
                                    <option value="IPOS">IPOS</option>
                                    <option value="Power Runner">Power Runner</option>
                                    <option value="Power RA">Power RA</option>
                                    <option value="Power ME">Power ME</option>
                                    <option value="ECOS">ECOS</option>
                                    <option value="Cloud WS">Cloud WS</option>
                                    <option value="Power GO">Power GO</option>
                                    <option value="Dashpad">Dashpad</option>
                                    <option value="IPTV">IPTV</option>
                                    <option value="HSIA">HSIA</option>
                                    <option value="SGI">SGI</option>
                                    <option value="Guest Survey">Guest Survey</option>
                                    <option value="Loyalty Management">Loyalty Management</option>
                                    <option value="AccPac">AccPac</option>
                                    <option value="GL Consolidation">GL Consolidation</option>
                                    <option value="Self Check In">Self Check In</option>
                                    <option value="Check In Desk">Check In Desk</option>
                                    <option value="Others">Others</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Type *</label>
                                <select name="type" class="form-select" required>
                                    <option value="Setup">Setup</option>
                                    <option value="Question">Question</option>
                                    <option value="Issue" selected>Issue</option>
                                    <option value="Report Issue">Report Issue</option>
                                    <option value="Report Request">Report Request</option>
                                    <option value="Feature Request">Feature Request</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Project</label>
                                <select name="project_id" class="form-select">
                                    <option value="">Select Project</option>
                                    <?php foreach ($projects as $project): ?>
                                        <option value="<?= $project['project_id'] ?>"><?= htmlspecialchars($project['project_name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Customer</label>
                                <input type="text" name="customer" class="form-control" placeholder="Enter customer name">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">CNC Number</label>
                                <input type="text" name="cnc_number" class="form-control" placeholder="Enter CNC number">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Completed Date</label>
                                <input type="date" name="completed_date" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Status *</label>
                                <select name="status" class="form-select" required>
                                    <option value="Open" selected>Open</option>
                                    <option value="On Progress">On Progress</option>
                                    <option value="Need Requirement">Need Requirement</option>
                                    <option value="Done">Done</option>
                                    <option value="Cancel">Cancel</option>
                                </select>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Description *</label>
                                <textarea name="description" class="form-control" rows="3" placeholder="Enter activity description" required></textarea>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Action / Solution</label>
                                <textarea name="action_solution" class="form-control" rows="3" placeholder="Enter action or solution"></textarea>
                            </div>
                            <div class="col-12">
                                <button type="submit" name="create" class="btn btn-primary">Add Activity</button>
                                <button type="button" onclick="hideCreateForm()" class="btn btn-secondary">Cancel</button>
                            </div>
                        </form>
                    </div>

                    <!-- Update Activity Form (Hidden by default) -->
                    <div id="updateActivityForm" style="display:none; margin-bottom:24px; padding:20px; border:1px solid #ddd; border-radius:8px; background:#f9f9f9;">
                        <h5 class="mb-3">Update Activity</h5>
                        <form method="post" class="row g-3">
                            <?= csrf_field() ?>
                            <input type="hidden" name="id" id="update_id">
                            <div class="col-md-6">
                                <label class="form-label">Information Date *</label>
                                <input type="date" name="information_date" id="update_information_date" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Priority *</label>
                                <select name="priority" id="update_priority" class="form-select" required>
                                    <option value="Low">Low</option>
                                    <option value="Normal">Normal</option>
                                    <option value="Hard">Hard</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">User & Position</label>
                                <input type="text" name="user_position" id="update_user_position" class="form-control" placeholder="Enter user and position">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Department *</label>
                                <select name="department" id="update_department" class="form-select" required>
                                    <option value="">Select Department</option>
                                    <option value="Food & Beverage">Food & Beverage</option>
                                    <option value="Kitchen">Kitchen</option>
                                    <option value="Room Division">Room Division</option>
                                    <option value="Front Office">Front Office</option>
                                    <option value="Housekeeping">Housekeeping</option>
                                    <option value="Engineering">Engineering</option>
                                    <option value="Sales & Marketing">Sales & Marketing</option>
                                    <option value="IT / EDP">IT / EDP</option>
                                    <option value="Accounting">Accounting</option>
                                    <option value="Executive Office">Executive Office</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Application *</label>
                                <select name="application" id="update_application" class="form-select" required>
                                    <option value="Power FO">Power FO</option>
                                    <option value="My POS">My POS</option>
                                    <option value="My MGR">My MGR</option>
                                    <option value="Power AR">Power AR</option>
                                    <option value="Power INV">Power INV</option>
                                    <option value="Power AP">Power AP</option>
                                    <option value="Power GL">Power GL</option>
                                    <option value="Keylock">Keylock</option>
                                    <option value="PABX">PABX</option>
                                    <option value="DIM">DIM</option>
                                    <option value="Dynamic Room Rate">Dynamic Room Rate</option>
                                    <option value="Channel Manager">Channel Manager</option>
                                    <option value="PB1">PB1</option>
                                    <option value="Power SIGN">Power SIGN</option>
                                    <option value="Multi Properties">Multi Properties</option>
                                    <option value="Scanner ID">Scanner ID</option>
                                    <option value="IPOS">IPOS</option>
                                    <option value="Power Runner">Power Runner</option>
                                    <option value="Power RA">Power RA</option>
                                    <option value="Power ME">Power ME</option>
                                    <option value="ECOS">ECOS</option>
                                    <option value="Cloud WS">Cloud WS</option>
                                    <option value="Power GO">Power GO</option>
                                    <option value="Dashpad">Dashpad</option>
                                    <option value="IPTV">IPTV</option>
                                    <option value="HSIA">HSIA</option>
                                    <option value="SGI">SGI</option>
                                    <option value="Guest Survey">Guest Survey</option>
                                    <option value="Loyalty Management">Loyalty Management</option>
                                    <option value="AccPac">AccPac</option>
                                    <option value="GL Consolidation">GL Consolidation</option>
                                    <option value="Self Check In">Self Check In</option>
                                    <option value="Check In Desk">Check In Desk</option>
                                    <option value="Others">Others</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Type *</label>
                                <select name="type" id="update_type" class="form-select" required>
                                    <option value="Setup">Setup</option>
                                    <option value="Question">Question</option>
                                    <option value="Issue">Issue</option>
                                    <option value="Report Issue">Report Issue</option>
                                    <option value="Report Request">Report Request</option>
                                    <option value="Feature Request">Feature Request</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Project</label>
                                <select name="project_id" id="update_project_id" class="form-select">
                                    <option value="">Select Project</option>
                                    <?php foreach ($projects as $project): ?>
                                        <option value="<?= $project['project_id'] ?>"><?= htmlspecialchars($project['project_name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Customer</label>
                                <input type="text" name="customer" id="update_customer" class="form-control" placeholder="Enter customer name">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">CNC Number</label>
                                <input type="text" name="cnc_number" id="update_cnc_number" class="form-control" placeholder="Enter CNC number">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Completed Date</label>
                                <input type="date" name="completed_date" id="update_completed_date" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Status *</label>
                                <select name="status" id="update_status" class="form-select" required>
                                    <option value="Open">Open</option>
                                    <option value="On Progress">On Progress</option>
                                    <option value="Need Requirement">Need Requirement</option>
                                    <option value="Done">Done</option>
                                    <option value="Cancel">Cancel</option>
                                </select>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Description *</label>
                                <textarea name="description" id="update_description" class="form-control" rows="3" placeholder="Enter activity description" required></textarea>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Action / Solution</label>
                                <textarea name="action_solution" id="update_action_solution" class="form-control" rows="3" placeholder="Enter action or solution"></textarea>
                            </div>
                            <div class="col-12">
                                <button type="submit" name="update" class="btn btn-primary">Update Activity</button>
                                <button type="button" onclick="hideUpdateForm()" class="btn btn-secondary">Cancel</button>
                            </div>
                        </form>
                    </div>

                    <table class="table bordered-table mb-0" style="table-layout: fixed;">
                        <thead>
                            <tr>
                                <th scope="col" style="width: 5%;">No</th>
                                <th scope="col" style="width: 12%;">Information Date</th>
                                <th scope="col" style="width: 10%;">Priority</th>
                                <th scope="col" style="width: 12%;">Department</th>
                                <th scope="col" style="width: 12%;">Application</th>
                                <th scope="col" style="width: 10%;">Type</th>
                                <th scope="col" style="width: 25%;">Description</th>
                                <th scope="col" style="width: 10%;">Status</th>
                                <th scope="col" style="width: 14%;">Created By</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($activities as $index => $a): ?>
                            <tr style="cursor: pointer;" onclick="editActivity(<?= $a['id'] ?>, '<?= addslashes($a['project_id']) ?>', '<?= addslashes($a['description']) ?>', '<?= addslashes($a['cnc_number']) ?>', '<?= addslashes($a['status']) ?>', '<?= addslashes($a['type']) ?>', null, '<?= $a['information_date'] ?>', '<?= addslashes($a['priority']) ?>', '<?= addslashes($a['user_position']) ?>', '<?= addslashes($a['department']) ?>', '<?= addslashes($a['application']) ?>', '<?= addslashes($a['customer']) ?>', '<?= addslashes($a['project_name']) ?>', '<?= $a['completed_date'] ?>', '<?= addslashes($a['action_solution']) ?>')">
                                <td style="text-align: center; vertical-align: middle;"><?= $index + 1 ?></td>
                                <td style="text-align: center; vertical-align: middle;"><?= $a['information_date'] ? date('d M Y', strtotime($a['information_date'])) : '-' ?></td>
                                <td style="text-align: center; vertical-align: middle;">
                                    <?php
                                    $priority_colors = [
                                        'Low' => 'bg-success-focus text-success-main',
                                        'Normal' => 'bg-info-focus text-info-main',
                                        'Hard' => 'bg-danger-focus text-danger-main'
                                    ];
                                    $color_class = $priority_colors[$a['priority']] ?? 'bg-neutral-200 text-neutral-600';
                                    ?>
                                    <span class="<?= $color_class ?> px-8 py-4 rounded-pill fw-medium text-sm"><?= htmlspecialchars($a['priority'] ?: 'Normal') ?></span>
                                </td>
                                <td style="text-align: center; vertical-align: middle;"><?= htmlspecialchars($a['department'] ?: '-') ?></td>
                                <td style="text-align: center; vertical-align: middle;"><?= htmlspecialchars($a['application'] ?: '-') ?></td>
                                <td style="text-align: center; vertical-align: middle;">
                                    <?php
                                    $type_colors = [
                                        'Setup' => 'bg-success-focus text-success-main',
                                        'Question' => 'bg-info-focus text-info-main',
                                        'Issue' => 'bg-warning-focus text-warning-main',
                                        'Report Issue' => 'bg-danger-focus text-danger-main',
                                        'Report Request' => 'bg-primary-focus text-primary-main',
                                        'Feature Request' => 'bg-secondary-focus text-secondary-main'
                                    ];
                                    $color_class = $type_colors[$a['type']] ?? 'bg-neutral-200 text-neutral-600';
                                    ?>
                                    <span class="<?= $color_class ?> px-8 py-4 rounded-pill fw-medium text-sm"><?= htmlspecialchars($a['type']) ?></span>
                                </td>
                                <td style="text-align: left; vertical-align: middle;">
                                    <div>
                                        <h6 class="text-md mb-0 fw-medium"><?= htmlspecialchars(substr($a['description'], 0, 50)) ?><?= strlen($a['description']) > 50 ? '...' : '' ?></h6>
                                        <span class="text-sm text-secondary-light"><?= htmlspecialchars($a['cnc_number'] ?: 'No CNC Number') ?></span>
                                    </div>
                                </td>
                                <td style="text-align: center; vertical-align: middle;">
                                    <?php
                                    $status_colors = [
                                        'Open' => 'bg-warning-focus text-warning-main',
                                        'On Progress' => 'bg-info-focus text-info-main',
                                        'Need Requirement' => 'bg-danger-focus text-danger-main',
                                        'Done' => 'bg-success-focus text-success-main',
                                        'Cancel' => 'bg-secondary-focus text-secondary-main'
                                    ];
                                    $color_class = $status_colors[$a['status']] ?? 'bg-neutral-200 text-neutral-600';
                                    ?>
                                    <span class="<?= $color_class ?> px-8 py-4 rounded-pill fw-medium text-sm"><?= htmlspecialchars($a['status']) ?></span>
                                </td>
                                <td style="text-align: center; vertical-align: middle;"><?= htmlspecialchars($a['created_by_name'] ?: '-') ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <!-- Pagination -->
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mt-24">
                        <span class="text-md text-secondary-light fw-normal">Showing <?= count($activities) ?> of <?= $total_activities ?> results</span>
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
// Initialize notification system
let notificationManager;

document.addEventListener('DOMContentLoaded', function() {
    // Check if notification manager is available
    if (window.logoNotificationManager) {
        notificationManager = window.logoNotificationManager;
        console.log('✅ Notification system initialized');
    } else {
        console.warn('⚠️ Notification system not available, using fallback alerts');
    }
});

function showCreateForm() {
    document.getElementById('createActivityForm').style.display = 'block';
    // Hide update form if it's open
    document.getElementById('updateActivityForm').style.display = 'none';
}

function hideCreateForm() {
    document.getElementById('createActivityForm').style.display = 'none';
}

function showUpdateForm(id, project_id, description, cnc_number, status, type, due_date, information_date, priority, user_position, department, application, customer, project, completed_date, action_solution) {
    // Hide create form if it's open
    document.getElementById('createActivityForm').style.display = 'none';
    
    // Populate all form fields
    document.getElementById('update_id').value = id;
    document.getElementById('update_information_date').value = information_date;
    document.getElementById('update_priority').value = priority || 'Normal';
    document.getElementById('update_user_position').value = user_position || '';
    document.getElementById('update_department').value = department || '';
    document.getElementById('update_application').value = application || 'Power FO';
    document.getElementById('update_type').value = type || 'Issue';
    document.getElementById('update_project_id').value = project_id || '';
    document.getElementById('update_customer').value = customer || '';
    document.getElementById('update_cnc_number').value = cnc_number || '';
    document.getElementById('update_completed_date').value = completed_date || '';
    document.getElementById('update_status').value = status || 'Open';
    document.getElementById('update_description').value = description || '';
    document.getElementById('update_action_solution').value = action_solution || '';
    
    document.getElementById('updateActivityForm').style.display = 'block';
}

function hideUpdateForm() {
    document.getElementById('updateActivityForm').style.display = 'none';
}

function editActivity(activityId, projectId, description, cncNumber, status, type, dueDate, informationDate, priority, userPosition, department, application, customer, project, completedDate, actionSolution) {
    showUpdateForm(activityId, projectId, description, cncNumber, status, type, dueDate, informationDate, priority, userPosition, department, application, customer, project, completedDate, actionSolution);
}

// Function to show notifications based on PHP message
function showNotificationFromPHP(message, type) {
    if (!notificationManager) return;
    
    switch(type) {
        case 'success':
            notificationManager.showActivityCreated(message, 5000);
            break;
        case 'info':
            notificationManager.showActivityUpdated(message, 5000);
            break;
        case 'warning':
            notificationManager.showActivityCanceled(message, 5000);
            break;
        case 'error':
            notificationManager.showActivityError(message, 5000);
            break;
        default:
            notificationManager.showInfo(message, 5000);
    }
}

// Auto-show notification if PHP message exists
<?php if ($message): ?>
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(() => {
        showNotificationFromPHP('<?= addslashes($message) ?>', '<?= $message_type ?>');
    }, 500);
});
<?php endif; ?>
</script>

<?php include './partials/layouts/layoutBottom.php'; ?>
