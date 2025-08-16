<?php
session_start();
require_once 'db.php';
require_once 'access_control.php';
require_once 'user_utils.php';

// Proteksi akses: hanya Administrator dan Management yang bisa melihat log
require_roles(['Administrator', 'Management']);

// Filter
$search = trim($_GET['search'] ?? '');
$filter_action = $_GET['filter_action'] ?? '';
$filter_user = $_GET['filter_user'] ?? '';
$filter_date = $_GET['filter_date'] ?? '';

$where = [];
$params = [];
if ($search) {
    $where[] = "(description ILIKE :search OR user_email ILIKE :search)";
    $params['search'] = "%$search%";
}
if ($filter_action) {
    $where[] = "action = :action";
    $params['action'] = $filter_action;
}
if ($filter_user) {
    $where[] = "user_email = :user_email";
    $params['user_email'] = $filter_user;
}
if ($filter_date) {
    $where[] = "DATE(created_at) = :date";
    $params['date'] = $filter_date;
}
$where_sql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

// Pagination
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 30;
$offset = ($page - 1) * $per_page;
$sql_count = "SELECT COUNT(*) FROM logs $where_sql";
$stmt_count = $pdo->prepare($sql_count);
$stmt_count->execute($params);
$total_data = $stmt_count->fetchColumn();
$total_pages = max(1, ceil($total_data / $per_page));

$sql = "SELECT * FROM logs $where_sql ORDER BY id DESC LIMIT $per_page OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

/*
 * Note: MAC address cannot be captured from web applications due to browser security restrictions.
 * The IP address shown is the best available identifier for the client device.
 * For more detailed device identification, consider implementing:
 * 1. Client-side fingerprinting (with user consent)
 * 2. Device registration system
 * 3. Mobile app with appropriate permissions
 */

// Data untuk filter user dan action
$user_emails = $pdo->query('SELECT DISTINCT user_email FROM logs WHERE user_email IS NOT NULL ORDER BY user_email')->fetchAll(PDO::FETCH_COLUMN);
$actions = $pdo->query('SELECT DISTINCT action FROM logs ORDER BY action')->fetchAll(PDO::FETCH_COLUMN);
?>
<?php include './partials/layouts/layoutHorizontal.php'; ?>

        <div class="dashboard-main-body">

            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
                <h6 class="fw-semibold mb-0">Audit Logs</h6>
                <ul class="d-flex align-items-center gap-2">
                    <li class="fw-medium">
                        <a href="index.php" class="d-flex align-items-center gap-1 hover-text-primary">
                            <iconify-icon icon="solar:home-smile-angle-outline" class="icon text-lg"></iconify-icon>
                            Dashboard
                        </a>
                    </li>
                    <li>-</li>
                    <li class="fw-medium">Audit Logs</li>
                </ul>
            </div>

            <div class="card">
                <div class="card-body">
                    <!-- Filter Section -->
                    <div class="filter-section">
                        <form method="get" class="filter-form">
                            <div class="filter-row">
                                <div class="filter-group">
                                    <label class="filter-label">Search</label>
                                    <div class="icon-field">
                                        <input type="text" name="search" class="form-control" placeholder="Search logs..." value="<?= htmlspecialchars($search) ?>">
                                        <span class="icon">
                                            <iconify-icon icon="ion:search-outline"></iconify-icon>
                                        </span>
                                    </div>
                                </div>
                                <div class="filter-group">
                                    <label class="filter-label">Action</label>
                                    <select class="form-select" name="filter_action">
                                        <option value="">All Actions</option>
                                        <?php foreach ($actions as $a): ?>
                                            <option value="<?= htmlspecialchars($a) ?>" <?= $filter_action===$a?'selected':'' ?>><?= htmlspecialchars($a) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="filter-group">
                                    <label class="filter-label">User</label>
                                    <select class="form-select" name="filter_user">
                                        <option value="">All Users</option>
                                        <?php foreach ($user_emails as $u): ?>
                                            <option value="<?= htmlspecialchars($u) ?>" <?= $filter_user===$u?'selected':'' ?>><?= htmlspecialchars($u) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="filter-group">
                                    <label class="filter-label">Date</label>
                                    <input type="date" name="filter_date" class="form-control" value="<?= htmlspecialchars($filter_date) ?>">
                                </div>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn-apply">Apply Filters</button>
                                <a href="log_view.php" class="btn-reset">Reset</a>
                            </div>
                        </form>
                    </div>
                    
                    <style>
                        /* Filter section styling */
                        .filter-section {
                            margin-bottom: 24px;
                            padding: 20px;
                            background: #f8f9fa;
                            border-radius: 8px;
                            border: 1px solid #e9ecef;
                        }
                        
                        [data-theme="dark"] .filter-section {
                            background: #1f2937;
                            border-color: #374151;
                        }
                        
                        .filter-row {
                            display: flex;
                            flex-wrap: wrap;
                            gap: 16px;
                            margin-bottom: 16px;
                        }
                        
                        .filter-group {
                            flex: 1;
                            min-width: 200px;
                        }
                        
                        .filter-label {
                            display: block;
                            margin-bottom: 8px;
                            font-weight: 500;
                            color: #333;
                            font-size: 14px;
                        }
                        
                        [data-theme="dark"] .filter-label {
                            color: #e5e7eb;
                        }
                        
                        .icon-field {
                            position: relative;
                        }
                        
                        .icon-field .icon {
                            position: absolute;
                            left: 12px;
                            top: 50%;
                            transform: translateY(-50%);
                            color: #6c757d;
                        }
                        
                        .icon-field input {
                            padding-left: 40px;
                        }
                        
                        .btn-apply {
                            padding: 10px 20px;
                            background: #007bff;
                            color: white;
                            border: none;
                            border-radius: 4px;
                            cursor: pointer;
                            font-weight: 500;
                        }
                        
                        .btn-apply:hover {
                            background: #0056b3;
                        }
                        
                        .btn-reset {
                            padding: 10px 20px;
                            background: #6c757d;
                            color: white;
                            border: none;
                            border-radius: 4px;
                            cursor: pointer;
                            font-weight: 500;
                            text-decoration: none;
                            display: inline-block;
                        }
                        
                        .btn-reset:hover {
                            background: #545b62;
                        }
                        
                        /* Table styling */
                        .table-responsive {
                            overflow-x: auto;
                            border-radius: 8px;
                            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
                        }
                        
                        [data-theme="dark"] .table-responsive {
                            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
                        }
                        
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
                            letter-spacing: 0.5px;
                            text-align: center;
                            box-shadow: 0 2px 8px rgba(79, 70, 229, 0.3);
                            transition: all 0.3s ease;
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
                            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
                            transition: left 0.5s;
                        }
                        
                        .table-header:hover::before {
                            left: 100%;
                        }
                        
                        [data-theme="dark"] .table-header {
                            background: linear-gradient(135deg, #0f172a 0%, #111827 100%) !important;
                            color: #e5e7eb !important;
                            box-shadow: 0 1px 4px rgba(0,0,0,.6) !important;
                            border: 1px solid #334155 !important;
                        }
                        
                        .table thead th {
                            padding: 0 !important;
                            vertical-align: middle !important;
                        }
                        
                        .table th:nth-child(1) {
                            width: 70px;
                            min-width: 70px;
                        }
                        
                        .table th:nth-child(2) {
                            width: 70px;
                            min-width: 70px;
                        }
                        
                        .table th:nth-child(3) {
                            width: 120px;
                            min-width: 120px;
                        }
                        
                        .table th:nth-child(4) {
                            width: 250px;
                            min-width: 250px;
                        }
                        
                        .table th:nth-child(5) {
                            width: 100px;
                            min-width: 100px;
                        }
                        
                        .table td {
                            vertical-align: middle !important;
                            padding: 12px 8px;
                        }
                        
                        .table td:nth-child(1),
                        .table td:nth-child(2),
                        .table td:nth-child(3),
                        .table td:nth-child(5) {
                            text-align: center;
                        }
                        
                        .table td:nth-child(4) {
                            text-align: left;
                        }
                        
                        /* Pagination styling */
                        .pagination {
                            display: flex;
                            flex-wrap: wrap;
                            align-items: center;
                            gap: 2px;
                            justify-content: center;
                        }
                        
                        .page-item {
                            list-style: none;
                        }
                        
                        .page-link {
                            display: block;
                            padding: 8px 12px;
                            text-decoration: none;
                            border-radius: 4px;
                            border: 1px solid #dee2e6;
                            color: #007bff;
                            background: #fff;
                            transition: all 0.2s ease;
                        }
                        
                        .page-link:hover {
                            background: #e9ecef;
                            border-color: #adb5bd;
                        }
                        
                        .page-link.active,
                        .page-link.active:hover {
                            background: #007bff;
                            color: white;
                            border-color: #007bff;
                        }
                        
                        [data-theme="dark"] .page-link {
                            background: #1f2937;
                            border-color: #374151;
                            color: #3b82f6;
                        }
                        
                        [data-theme="dark"] .page-link:hover {
                            background: #374151;
                            border-color: #4b5563;
                        }
                        
                        [data-theme="dark"] .page-link.active,
                        [data-theme="dark"] .page-link.active:hover {
                            background: #3b82f6;
                            color: white;
                            border-color: #3b82f6;
                        }
                    </style>
                    
                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th scope="col">
                                        <div class="table-header">Time</div>
                                    </th>
                                    <th scope="col">
                                        <div class="table-header">User</div>
                                    </th>
                                    <th scope="col">
                                        <div class="table-header">Action</div>
                                    </th>
                                    <th scope="col">
                                        <div class="table-header">Description</div>
                                    </th>
                                    <th scope="col">
                                        <div class="table-header">IP</div>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($logs as $log): ?>
                                <tr>
                                    <td data-label="Time"><?= htmlspecialchars($log['created_at']) ?></td>
                                    <td data-label="User"><?= htmlspecialchars($log['user_email']) ?></td>
                                    <td data-label="Action"><?= htmlspecialchars($log['action']) ?></td>
                                    <td data-label="Description"><?= htmlspecialchars($log['description']) ?></td>
                                    <td data-label="IP"><?= htmlspecialchars($log['ip']) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mt-24">
                        <span class="text-md text-secondary-light fw-normal">Showing <?= count($logs) ?> of <?= $total_data ?> results</span>
                        <?php if ($total_pages > 1): ?>
                        <ul class="pagination d-flex flex-wrap align-items-center gap-2 justify-content-center">
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <?php if ($i == $page): ?>
                                    <li class="page-item">
                                        <a class="page-link active" href="#"><?= $i ?></a>
                                    </li>
                                <?php else: ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
                                    </li>
                                <?php endif; ?>
                            <?php endfor; ?>
                        </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

<?php include './partials/layouts/layoutBottom.php'; ?>
