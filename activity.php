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

// Helper: hitung default due date berdasarkan Information Date dan Type
function compute_default_due_date(?string $informationDate, ?string $type): ?string {
    if (empty($informationDate)) { return null; }
    $offsetByType = [
        'Issue' => 0,
        'Setup' => 2,
        'Question' => 1,
        'Report Issue' => 2,
        'Report Request' => 7,
        'Feature Request' => 30,
    ];
    $offsetDays = $offsetByType[$type ?? ''] ?? 0;
    try {
        $dt = new DateTime($informationDate);
        if ($offsetDays !== 0) { $dt->modify("+{$offsetDays} days"); }
        return $dt->format('Y-m-d');
    } catch (Exception $e) {
        return null;
    }
}

// Create Activity
if (isset($_POST['create'])) {
    if (!csrf_verify()) {
        $message = 'CSRF token tidak valid!';
        $message_type = 'error';
        $notification_type = 'error';
    } else {
        // Default Information Date ke hari ini jika kosong (berlaku hanya untuk CREATE)
        $informationDate = !empty($_POST['information_date']) ? $_POST['information_date'] : date('Y-m-d');
        $typeVal = $_POST['type'] ?? '';
        $dueDateInput = isset($_POST['due_date']) ? trim((string)$_POST['due_date']) : '';
        // Edit: jangan override due date; jika kosong biarkan NULL (tetap sesuai terakhir tersimpan jika tidak diubah)
        $dueDate = $dueDateInput !== '' ? $dueDateInput : null;
        if (!empty($informationDate) && !empty($dueDate)) {
            try {
                $inf = new DateTime($informationDate);
                $due = new DateTime($dueDate);
                if ($due < $inf) { $dueDate = $inf->format('Y-m-d'); }
            } catch (Exception $e) { }
        }
        $stmt = $pdo->prepare('INSERT INTO activities (project_id, no, information_date, user_position, department, application, type, description, action_solution, due_date, status, cnc_number, priority, customer, project, created_by, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            $_POST['project_id'] ?? null,
            $_POST['no'] ?? null,
            $informationDate,
            $_POST['user_position'],
            $_POST['department'],
            $_POST['application'],
            $typeVal,
            $_POST['description'],
            $_POST['action_solution'],
            $dueDate ?: null,
            $_POST['status'],
            $_POST['cnc_number'],
            $_POST['priority'] ?? 'Normal',
            $_POST['customer'] ?? null,
            $_POST['project'] ?? null,
            get_current_user_id(),
            date('Y-m-d H:i:s')
        ]);
        $message = 'Activity berhasil dibuat!';
        $message_type = 'success';
        $notification_type = 'created';
        log_activity('create_activity', 'Activity: ' . $_POST['type']);
    }
}

// Update Activity
if (isset($_POST['update'])) {
    if (!csrf_verify()) {
        $message = 'CSRF token tidak valid!';
        $message_type = 'error';
        $notification_type = 'error';
    } else {
        $informationDate = !empty($_POST['information_date']) ? $_POST['information_date'] : null;
        $typeVal = $_POST['type'] ?? '';
        $dueDateInput = isset($_POST['due_date']) ? trim((string)$_POST['due_date']) : '';
        $dueDate = $dueDateInput !== '' ? $dueDateInput : compute_default_due_date($informationDate, $typeVal);
        if (!empty($informationDate) && !empty($dueDate)) {
            try {
                $inf = new DateTime($informationDate);
                $due = new DateTime($dueDate);
                if ($due < $inf) { $dueDate = $inf->format('Y-m-d'); }
            } catch (Exception $e) { }
        }
        $stmt = $pdo->prepare('UPDATE activities SET project_id=?, no=?, information_date=?, user_position=?, department=?, application=?, type=?, description=?, action_solution=?, due_date=?, status=?, cnc_number=?, priority=?, customer=?, project=?, edited_by=?, edited_at=? WHERE id=?');
        $stmt->execute([
            $_POST['project_id'] ?? null,
            $_POST['no'] ?? null,
            $informationDate,
            $_POST['user_position'],
            $_POST['department'],
            $_POST['application'],
            $typeVal,
            $_POST['description'],
            $_POST['action_solution'],
            $dueDate ?: null,
            $_POST['status'],
            $_POST['cnc_number'],
            $_POST['priority'] ?? 'Normal',
            $_POST['customer'] ?? null,
            $_POST['project'] ?? null,
            get_current_user_id(), // Set edited_by
            date('Y-m-d H:i:s'), // Set edited_at
            $_POST['id']
        ]);
        
        // Deteksi perubahan status untuk notifikasi yang sesuai
        $newStatus = $_POST['status'];
        if ($newStatus === 'Cancel') {
            $message = 'Activity dibatalkan!';
            $message_type = 'warning';
            $notification_type = 'cancelled';
            log_activity('cancel_activity', 'Activity ID: ' . $_POST['id'] . ' - Status changed to Cancel');
        } else {
            $message = 'Activity berhasil diperbarui!';
            $message_type = 'info';
            $notification_type = 'updated';
            log_activity('update_activity', 'Activity ID: ' . $_POST['id']);
        }
    }
}

// Pagination dan filtering
$page = max(1, intval($_GET['page'] ?? 1));
$limit = max(1, intval($_GET['limit'] ?? 10));
$offset = ($page - 1) * $limit;

$search = trim($_GET['search'] ?? '');
$filter_status = $_GET['filter_status'] ?? '';
$filter_type = $_GET['filter_type'] ?? '';
$filter_priority = $_GET['filter_priority'] ?? '';
$filter_department = $_GET['filter_department'] ?? '';
$filter_application = $_GET['filter_application'] ?? '';

// Filter default: jika tidak ada filter yang dipilih, tampilkan status yang belum Done
$is_first_visit = !isset($_GET['search']) && !isset($_GET['filter_status']) && !isset($_GET['filter_type']) && 
                  !isset($_GET['filter_priority']) && !isset($_GET['filter_department']) && !isset($_GET['filter_application']);
$default_status_filter = $is_first_visit ? 'not_done' : '';

// Sorting
$sort_column = $_GET['sort'] ?? 'no';
$sort_order = $_GET['order'] ?? 'asc';

// Validasi kolom sorting yang diizinkan
$allowed_sort_columns = ['no', 'information_date', 'due_date', 'priority', 'user_position', 'department', 'application', 'type', 'description', 'action_solution', 'status'];
if (!in_array($sort_column, $allowed_sort_columns)) {
    $sort_column = 'no';
}

// Validasi order sorting
$sort_order = strtolower($sort_order) === 'desc' ? 'DESC' : 'ASC';

$where_conditions = [];
$params = [];

if ($search) {
    $where_conditions[] = "(a.description ILIKE ? OR a.user_position ILIKE ? OR a.cnc_number ILIKE ? OR a.no::text ILIKE ?)";
    $search_term = "%$search%";
    $params = array_merge($params, [$search_term, $search_term, $search_term, $search_term]);
}

if ($filter_status === 'not_done') {
    // Filter untuk status yang belum Done dan bukan Cancel
    $where_conditions[] = "a.status NOT IN ('Done', 'Cancel')";
} elseif ($filter_status) {
    $where_conditions[] = "a.status = ?";
    $params[] = $filter_status;
} elseif ($default_status_filter === 'not_done') {
    // Filter default: tampilkan status yang belum Done dan bukan Cancel
    $where_conditions[] = "a.status NOT IN ('Done', 'Cancel')";
}

if ($filter_type) {
    $where_conditions[] = "a.type = ?";
    $params[] = $filter_type;
}

if ($filter_priority) {
    $where_conditions[] = "a.priority = ?";
    $params[] = $filter_priority;
}

if ($filter_department) {
    $where_conditions[] = "a.department = ?";
    $params[] = $filter_department;
}

if ($filter_application) {
    $where_conditions[] = "a.application = ?";
    $params[] = $filter_application;
}

$where_clause = $where_conditions ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Query untuk data activities
$query = "SELECT a.*, 
                 CASE 
                     WHEN a.due_date < CURRENT_DATE AND a.status NOT IN ('Done', 'Cancel') THEN 'Overdue'
                     WHEN a.due_date = CURRENT_DATE AND a.status NOT IN ('Done', 'Cancel') THEN 'Due Today'
                     WHEN a.due_date > CURRENT_DATE AND a.status NOT IN ('Done', 'Cancel') THEN 'Upcoming'
                     ELSE a.status
                 END as status_display
          FROM activities a 
          $where_clause 
          ORDER BY a.$sort_column $sort_order 
          LIMIT ? OFFSET ?";

$params[] = $limit;
$params[] = $offset;

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$activities = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Query untuk total count
$count_query = "SELECT COUNT(*) FROM activities a $where_clause";
$count_stmt = $pdo->prepare($count_query);
$count_stmt->execute(array_slice($params, 0, -2)); // Remove limit and offset
$total_records = $count_stmt->fetchColumn();
$total_pages = ceil($total_records / $limit);

// Get user info
$user_id = $_SESSION['user_id'] ?? null;
$user_name = $_SESSION['user_display_name'] ?? 'User';
$user_email = $_SESSION['user_email'] ?? 'user@example.com';
$user_role = $_SESSION['user_role'] ?? 'User';

// Get activities count
try {
    $stmt = $pdo->query('SELECT COUNT(*) as total FROM activities');
    $total_activities = $stmt->fetchColumn();
} catch (Exception $e) {
    $total_activities = 0;
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PowerPro Dashboard - Activity</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/iconify-icon@1.0.7/dist/iconify-icon.min.js"></script>
    <style>
        body { 
            background: #f8fafc; 
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        }
        
        .dashboard-header { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
            color: white; 
            padding: 2rem; 
            border-radius: 12px; 
            margin-bottom: 2rem; 
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .activity-card { 
            background: white; 
            border-radius: 12px; 
            padding: 1.5rem; 
            box-shadow: 0 4px 6px rgba(0,0,0,0.1); 
            margin-bottom: 1rem; 
            border: 1px solid #e2e8f0;
        }
        
        .user-info { 
            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%); 
            padding: 1.5rem; 
            border-radius: 12px; 
            margin-bottom: 1.5rem; 
            border-left: 4px solid #2196f3;
        }
        
        .quick-actions { 
            display: flex; 
            gap: 1rem; 
            flex-wrap: wrap; 
        }
        
        .btn-action { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
            color: white; 
            border: none; 
            padding: 0.75rem 1.5rem; 
            border-radius: 8px; 
            text-decoration: none; 
            display: inline-flex; 
            align-items: center; 
            gap: 0.5rem; 
            transition: all 0.3s ease;
            font-weight: 500;
        }
        
        .btn-action:hover { 
            background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%); 
            color: white; 
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }
        
        .nav-sidebar {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            border: 1px solid #e2e8f0;
        }
        
        .nav-item {
            margin-bottom: 0.5rem;
        }
        
        .nav-item a {
            color: #64748b;
            text-decoration: none;
            padding: 0.5rem 0.75rem;
            border-radius: 8px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .nav-item a:hover {
            background: #f1f5f9;
            color: #334155;
        }
        
        .nav-item a.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .stats-card {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            border: 1px solid #cbd5e1;
            border-radius: 12px;
            padding: 1.5rem;
            text-align: center;
        }
        
        .stats-number {
            font-size: 2rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 0.5rem;
        }
        
        .stats-label {
            color: #64748b;
            font-size: 0.875rem;
            font-weight: 500;
        }
        
        .table-modern {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            border: 1px solid #e2e8f0;
        }
        
        .table-modern thead {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .table-modern thead th {
            border: none;
            padding: 1rem;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
        }
        
        .table-modern tbody td {
            padding: 1rem;
            border-top: 1px solid #e2e8f0;
            vertical-align: middle;
        }
        
        .table-modern tbody tr:hover {
            background: #f8fafc;
        }
        
        .badge-status {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        
        .badge-pending { background: #fef3c7; color: #92400e; }
        .badge-progress { background: #dbeafe; color: #1e40af; }
        .badge-done { background: #d1fae5; color: #065f46; }
        .badge-cancel { background: #fee2e2; color: #991b1b; }
        .badge-overdue { background: #fecaca; color: #dc2626; }
        .badge-due-today { background: #fed7aa; color: #ea580c; }
        .badge-upcoming { background: #e0e7ff; color: #3730a3; }
        
        .btn-modern {
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }
        
        .btn-primary-modern {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-primary-modern:hover {
            background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
            color: white;
            transform: translateY(-1px);
        }
        
        .btn-success-modern {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
        }
        
        .btn-warning-modern {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
        }
        
        .btn-danger-modern {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
        }
        
        .filter-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            border: 1px solid #e2e8f0;
            margin-bottom: 1.5rem;
        }
        
        .pagination-modern .page-link {
            border: none;
            color: #667eea;
            padding: 0.75rem 1rem;
            margin: 0 0.25rem;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .pagination-modern .page-link:hover {
            background: #667eea;
            color: white;
        }
        
        .pagination-modern .page-item.active .page-link {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .alert-modern {
            border: none;
            border-radius: 12px;
            padding: 1rem 1.5rem;
            margin-bottom: 1.5rem;
            font-weight: 500;
        }
        
        .alert-success-modern {
            background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
            color: #065f46;
            border-left: 4px solid #10b981;
        }
        
        .alert-error-modern {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            color: #991b1b;
            border-left: 4px solid #ef4444;
        }
        
        .alert-warning-modern {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            color: #92400e;
            border-left: 4px solid #f59e0b;
        }
        
        .alert-info-modern {
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
            color: #1e40af;
            border-left: 4px solid #3b82f6;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <!-- Header -->
        <div class="dashboard-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 d-flex align-items-center gap-2">
                        <iconify-icon icon="solar:calendar-outline" style="font-size: 1.5rem;"></iconify-icon>
                        Activity Dashboard
                    </h1>
                    <p class="mb-0 opacity-75">Manage and track your activities efficiently</p>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <div class="stats-card">
                        <div class="stats-number"><?= $total_activities ?></div>
                        <div class="stats-label">Total Activities</div>
                    </div>
                    <a href="logout.php" class="btn btn-outline-light d-flex align-items-center gap-2">
                        <iconify-icon icon="solar:logout-2-outline"></iconify-icon>
                        Logout
                    </a>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3">
                <div class="nav-sidebar">
                    <h6 class="fw-semibold mb-3 d-flex align-items-center gap-2">
                        <iconify-icon icon="solar:menu-outline"></iconify-icon>
                        Navigation
                    </h6>
                    <ul class="list-unstyled">
                        <li class="nav-item">
                            <a href="index.php" class="d-flex align-items-center gap-2">
                                <iconify-icon icon="solar:home-smile-angle-outline"></iconify-icon>
                                Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="customer.php" class="d-flex align-items-center gap-2">
                                <iconify-icon icon="solar:users-group-two-rounded-outline"></iconify-icon>
                                Customers
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="activity.php" class="d-flex align-items-center gap-2 active">
                                <iconify-icon icon="solar:calendar-outline"></iconify-icon>
                                Activity
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="users.php" class="d-flex align-items-center gap-2">
                                <iconify-icon icon="solar:users-group-rounded-outline"></iconify-icon>
                                Users
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9">
                <!-- User Information -->
                <div class="user-info">
                    <h6 class="fw-semibold mb-3 d-flex align-items-center gap-2">
                        <iconify-icon icon="solar:user-outline"></iconify-icon>
                        User Information
                    </h6>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-2">
                                <strong class="text-primary">Name:</strong><br>
                                <span class="text-dark"><?= htmlspecialchars($user_name) ?></span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-2">
                                <strong class="text-primary">Email:</strong><br>
                                <span class="text-dark"><?= htmlspecialchars($user_email) ?></span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-2">
                                <strong class="text-primary">Role:</strong><br>
                                <span class="badge bg-primary"><?= htmlspecialchars($user_role) ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Message Alert -->
                <?php if ($message): ?>
                    <div class="alert-modern alert-<?= $message_type ?>-modern">
                        <div class="d-flex align-items-center gap-2">
                            <iconify-icon icon="solar:info-circle-outline"></iconify-icon>
                            <?= htmlspecialchars($message) ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Filter Card -->
                <div class="filter-card">
                    <h6 class="fw-semibold mb-3 d-flex align-items-center gap-2">
                        <iconify-icon icon="solar:filter-outline"></iconify-icon>
                        Filter & Search
                    </h6>
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <input type="text" class="form-control" name="search" placeholder="Search activities..." value="<?= htmlspecialchars($search) ?>">
                        </div>
                        <div class="col-md-2">
                            <select class="form-select" name="filter_status">
                                <option value="">All Status</option>
                                <option value="not_done" <?= $filter_status === 'not_done' ? 'selected' : '' ?>>Not Done</option>
                                <option value="Pending" <?= $filter_status === 'Pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="In Progress" <?= $filter_status === 'In Progress' ? 'selected' : '' ?>>In Progress</option>
                                <option value="Done" <?= $filter_status === 'Done' ? 'selected' : '' ?>>Done</option>
                                <option value="Cancel" <?= $filter_status === 'Cancel' ? 'selected' : '' ?>>Cancel</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select class="form-select" name="filter_priority">
                                <option value="">All Priority</option>
                                <option value="High" <?= $filter_priority === 'High' ? 'selected' : '' ?>>High</option>
                                <option value="Normal" <?= $filter_priority === 'Normal' ? 'selected' : '' ?>>Normal</option>
                                <option value="Low" <?= $filter_priority === 'Low' ? 'selected' : '' ?>>Low</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select class="form-select" name="filter_type">
                                <option value="">All Types</option>
                                <option value="Issue" <?= $filter_type === 'Issue' ? 'selected' : '' ?>>Issue</option>
                                <option value="Setup" <?= $filter_type === 'Setup' ? 'selected' : '' ?>>Setup</option>
                                <option value="Question" <?= $filter_type === 'Question' ? 'selected' : '' ?>>Question</option>
                                <option value="Report Issue" <?= $filter_type === 'Report Issue' ? 'selected' : '' ?>>Report Issue</option>
                                <option value="Report Request" <?= $filter_type === 'Report Request' ? 'selected' : '' ?>>Report Request</option>
                                <option value="Feature Request" <?= $filter_type === 'Feature Request' ? 'selected' : '' ?>>Feature Request</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select class="form-select" name="limit">
                                <option value="10" <?= $limit === 10 ? 'selected' : '' ?>>10 per page</option>
                                <option value="25" <?= $limit === 25 ? 'selected' : '' ?>>25 per page</option>
                                <option value="50" <?= $limit === 50 ? 'selected' : '' ?>>50 per page</option>
                                <option value="100" <?= $limit === 100 ? 'selected' : '' ?>>100 per page</option>
                            </select>
                        </div>
                        <div class="col-md-1">
                            <button type="submit" class="btn btn-primary-modern w-100">
                                <iconify-icon icon="solar:magnifer-outline"></iconify-icon>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Activities Table -->
                <div class="activity-card">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-semibold mb-0 d-flex align-items-center gap-2">
                            <iconify-icon icon="solar:list-outline"></iconify-icon>
                            Activities List
                        </h6>
                        <button class="btn btn-primary-modern" data-bs-toggle="modal" data-bs-target="#createModal">
                            <iconify-icon icon="solar:add-circle-outline"></iconify-icon>
                            Add Activity
                        </button>
                    </div>
                    
                    <div class="table-modern">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Information Date</th>
                                    <th>Due Date</th>
                                    <th>Priority</th>
                                    <th>User Position</th>
                                    <th>Department</th>
                                    <th>Application</th>
                                    <th>Type</th>
                                    <th>Description</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($activities)): ?>
                                    <tr>
                                        <td colspan="11" class="text-center py-4">
                                            <iconify-icon icon="solar:calendar-outline" style="font-size: 3rem; color: #cbd5e1;"></iconify-icon>
                                            <p class="text-muted mt-3 mb-0">No activities found.</p>
                                            <small class="text-muted">Try adjusting your filters or create a new activity.</small>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($activities as $activity): ?>
                                        <tr>
                                            <td><strong><?= htmlspecialchars($activity['no']) ?></strong></td>
                                            <td><?= htmlspecialchars($activity['information_date']) ?></td>
                                            <td><?= htmlspecialchars($activity['due_date']) ?></td>
                                            <td>
                                                <span class="badge bg-<?= $activity['priority'] === 'High' ? 'danger' : ($activity['priority'] === 'Normal' ? 'primary' : 'secondary') ?>">
                                                    <?= htmlspecialchars($activity['priority']) ?>
                                                </span>
                                            </td>
                                            <td><?= htmlspecialchars($activity['user_position']) ?></td>
                                            <td><?= htmlspecialchars($activity['department']) ?></td>
                                            <td><?= htmlspecialchars($activity['application']) ?></td>
                                            <td><?= htmlspecialchars($activity['type']) ?></td>
                                            <td>
                                                <div style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="<?= htmlspecialchars($activity['description']) ?>">
                                                    <?= htmlspecialchars($activity['description']) ?>
                                                </div>
                                            </td>
                                            <td>
                                                <?php
                                                $status = $activity['status_display'];
                                                $badge_class = '';
                                                if ($status === 'Overdue') $badge_class = 'badge-overdue';
                                                elseif ($status === 'Due Today') $badge_class = 'badge-due-today';
                                                elseif ($status === 'Upcoming') $badge_class = 'badge-upcoming';
                                                elseif ($status === 'Pending') $badge_class = 'badge-pending';
                                                elseif ($status === 'In Progress') $badge_class = 'badge-progress';
                                                elseif ($status === 'Done') $badge_class = 'badge-done';
                                                elseif ($status === 'Cancel') $badge_class = 'badge-cancel';
                                                ?>
                                                <span class="badge-status <?= $badge_class ?>"><?= htmlspecialchars($status) ?></span>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button class="btn btn-sm btn-outline-primary" onclick="editActivity(<?= $activity['id'] ?>)">
                                                        <iconify-icon icon="solar:pen-outline"></iconify-icon>
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-info" onclick="viewActivity(<?= $activity['id'] ?>)">
                                                        <iconify-icon icon="solar:eye-outline"></iconify-icon>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <nav class="mt-3">
                            <ul class="pagination pagination-modern justify-content-center">
                                <?php if ($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">
                                            <iconify-icon icon="solar:arrow-left-outline"></iconify-icon>
                                        </a>
                                    </li>
                                <?php endif; ?>
                                
                                <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>
                                
                                <?php if ($page < $total_pages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">
                                            <iconify-icon icon="solar:arrow-right-outline"></iconify-icon>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Activity Modal -->
    <div class="modal fade" id="createModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create New Activity</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <?= csrf_field() ?>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Project ID</label>
                                <input type="text" class="form-control" name="project_id" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">No</label>
                                <input type="number" class="form-control" name="no" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Information Date</label>
                                <input type="date" class="form-control" name="information_date" value="<?= date('Y-m-d') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Due Date</label>
                                <input type="date" class="form-control" name="due_date">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">User Position</label>
                                <input type="text" class="form-control" name="user_position" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Department</label>
                                <select class="form-select" name="department" required>
                                    <option value="">Select Department</option>
                                    <option value="IT">IT</option>
                                    <option value="HR">HR</option>
                                    <option value="Finance">Finance</option>
                                    <option value="Operations">Operations</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Application</label>
                                <input type="text" class="form-control" name="application" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Type</label>
                                <select class="form-select" name="type" required>
                                    <option value="">Select Type</option>
                                    <option value="Issue">Issue</option>
                                    <option value="Setup">Setup</option>
                                    <option value="Question">Question</option>
                                    <option value="Report Issue">Report Issue</option>
                                    <option value="Report Request">Report Request</option>
                                    <option value="Feature Request">Feature Request</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Priority</label>
                                <select class="form-select" name="priority">
                                    <option value="Normal">Normal</option>
                                    <option value="High">High</option>
                                    <option value="Low">Low</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Status</label>
                                <select class="form-select" name="status" required>
                                    <option value="Pending">Pending</option>
                                    <option value="In Progress">In Progress</option>
                                    <option value="Done">Done</option>
                                    <option value="Cancel">Cancel</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Description</label>
                                <textarea class="form-control" name="description" rows="3" required></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Action/Solution</label>
                                <textarea class="form-control" name="action_solution" rows="3"></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">CNC Number</label>
                                <input type="text" class="form-control" name="cnc_number">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Customer</label>
                                <input type="text" class="form-control" name="customer">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="create" class="btn btn-primary-modern">Create Activity</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editActivity(id) {
            // Implement edit functionality
            alert('Edit functionality will be implemented');
        }
        
        function viewActivity(id) {
            // Implement view functionality
            alert('View functionality will be implemented');
        }
    </script>
</body>
</html>
