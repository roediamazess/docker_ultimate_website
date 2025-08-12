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

// Ensure database has project_information (ENUM) and project_info_text (TEXT)
try {
    $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    if ($driver === 'pgsql') {
        // Ensure ENUM type exists
        $typeExists = $pdo->query("SELECT 1 FROM pg_type WHERE typname = 'project_info_type' LIMIT 1")->fetchColumn();
        if (!$typeExists) {
            $pdo->exec("CREATE TYPE project_info_type AS ENUM ('Request', 'Submission')");
        }
        // Ensure columns exist
        $stmtCol = $pdo->prepare("SELECT 1 FROM information_schema.columns WHERE table_name = 'projects' AND column_name = 'project_information' LIMIT 1");
        $stmtCol->execute();
        if (!$stmtCol->fetchColumn()) {
            $pdo->exec("ALTER TABLE projects ADD COLUMN project_information project_info_type NULL");
        }
        $stmtCol2 = $pdo->prepare("SELECT 1 FROM information_schema.columns WHERE table_name = 'projects' AND column_name = 'project_remark' LIMIT 1");
        $stmtCol2->execute();
        if (!$stmtCol2->fetchColumn()) {
            $pdo->exec("ALTER TABLE projects ADD COLUMN project_remark TEXT NULL");
        }
    } elseif ($driver === 'mysql') {
        // Ensure column exists in MySQL
        $stmtCol = $pdo->prepare("SELECT 1 FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'projects' AND column_name = 'project_information' LIMIT 1");
        $stmtCol->execute();
        if (!$stmtCol->fetchColumn()) {
            $pdo->exec("ALTER TABLE projects ADD COLUMN project_information ENUM('Request','Submission') NULL");
        }
        $stmtCol2 = $pdo->prepare("SELECT 1 FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'projects' AND column_name = 'project_remark' LIMIT 1");
        $stmtCol2->execute();
        if (!$stmtCol2->fetchColumn()) {
            $pdo->exec("ALTER TABLE projects ADD COLUMN project_remark TEXT NULL");
        }
    }
} catch (Throwable $e) {
    // Silent fail: do not block page if migration fails
}

// Create Project
if (isset($_POST['create'])) {
    if (!csrf_verify()) {
        $message = 'CSRF token tidak valid!';
    } else {
        $stmt = $pdo->prepare('INSERT INTO projects (project_id, pic, assignment, project_information, project_remark, req_pic, hotel_name, project_name, start_date, end_date, total_days, type, status, handover_official_report, handover_days, ketertiban_admin, point_ach, point_req, percent_point, month, quarter, week_no, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        
        $start_date = $_POST['start_date'] ?: null;
        $end_date = $_POST['end_date'] ?: null;
        $total_days = null;
        
        if ($start_date && $end_date) {
            $diff = date_diff(date_create($start_date), date_create($end_date));
            $total_days = $diff->days + 1;
        }
        
        $assignment = $_POST['assignment'] !== '' ? $_POST['assignment'] : null;
        // Map project_info input: if matches ENUM allowed values use ENUM column, else store to project_info_text
        $rawProjectInfo = trim((string)($_POST['project_information'] ?? ''));
        $enumAllowed = ['Request','Submission'];
        $project_information = in_array($rawProjectInfo, $enumAllowed, true) ? $rawProjectInfo : null;
        $project_remark = trim((string)($_POST['project_remark'] ?? ''));
        if ($project_remark === '') { $project_remark = null; }
        $req_pic = $_POST['req_pic'] !== '' ? $_POST['req_pic'] : null;
        $handover_official_report = $_POST['handover_report'] ?: null;
        $ketertiban_admin = $_POST['ketertiban_admin'] !== '' ? $_POST['ketertiban_admin'] : null;
        $monthVal = $_POST['month'] !== '' ? $_POST['month'] : null;
        $quarterVal = $_POST['quarter'] !== '' ? $_POST['quarter'] : null;
        $weekNo = $_POST['week_number'] !== '' ? $_POST['week_number'] : null;

        $stmt->execute([
            $_POST['project_id'],
            ($_POST['pic'] !== '' ? $_POST['pic'] : null),
            $assignment,
            $project_information,
            $project_remark,
            $req_pic,
            ($_POST['hotel_name'] !== '' ? $_POST['hotel_name'] : null),
            $_POST['project_name'],
            $start_date,
            $end_date,
            $total_days,
            $_POST['type'],
            $_POST['status'],
            $handover_official_report,
            $_POST['handover_days'] ?: null,
            $ketertiban_admin,
            $_POST['point_ach'] ?: null,
            $_POST['point_req'] ?: null,
            $_POST['percent_point'] ?: null,
            $monthVal,
            $quarterVal,
            $weekNo,
            date('Y-m-d H:i:s')
        ]);
        $message = 'Project created!';
        log_activity('create_project', 'Project ID: ' . $_POST['project_id']);
    }
}

// Update Project
if (isset($_POST['update'])) {
    if (!csrf_verify()) {
        $message = 'CSRF token tidak valid!';
    } else {
        $stmt = $pdo->prepare('UPDATE projects SET project_id=?, pic=?, assignment=?, project_information=?, project_remark=?, req_pic=?, hotel_name=?, project_name=?, start_date=?, end_date=?, total_days=?, type=?, status=?, handover_official_report=?, handover_days=?, ketertiban_admin=?, point_ach=?, point_req=?, percent_point=?, month=?, quarter=?, week_no=? WHERE id=?');
        
        $start_date = $_POST['start_date'] ?: null;
        $end_date = $_POST['end_date'] ?: null;
        $total_days = null;
        
        if ($start_date && $end_date) {
            $diff = date_diff(date_create($start_date), date_create($end_date));
            $total_days = $diff->days + 1;
        }
        
        $assignment = $_POST['assignment'] !== '' ? $_POST['assignment'] : null;
        $rawProjectInfo = trim((string)($_POST['project_information'] ?? ''));
        $enumAllowed = ['Request','Submission'];
        $project_information = in_array($rawProjectInfo, $enumAllowed, true) ? $rawProjectInfo : null;
        $project_remark = trim((string)($_POST['project_remark'] ?? ''));
        if ($project_remark === '') { $project_remark = null; }
        $req_pic = $_POST['req_pic'] !== '' ? $_POST['req_pic'] : null;
        $handover_official_report = $_POST['handover_report'] ?: null;
        $ketertiban_admin = $_POST['ketertiban_admin'] !== '' ? $_POST['ketertiban_admin'] : null;
        $monthVal = $_POST['month'] !== '' ? $_POST['month'] : null;
        $quarterVal = $_POST['quarter'] !== '' ? $_POST['quarter'] : null;
        $weekNo = $_POST['week_number'] !== '' ? $_POST['week_number'] : null;

        $stmt->execute([
            $_POST['project_id'],
            ($_POST['pic'] !== '' ? $_POST['pic'] : null),
            $assignment,
            $project_information,
            $project_remark,
            $req_pic,
            ($_POST['hotel_name'] !== '' ? $_POST['hotel_name'] : null),
            $_POST['project_name'],
            $start_date,
            $end_date,
            $total_days,
            $_POST['type'],
            $_POST['status'],
            $handover_official_report,
            $_POST['handover_days'] ?: null,
            $ketertiban_admin,
            $_POST['point_ach'] ?: null,
            $_POST['point_req'] ?: null,
            $_POST['percent_point'] ?: null,
            $monthVal,
            $quarterVal,
            $weekNo,
            $_POST['id']
        ]);
        $message = 'Project updated!';
        log_activity('update_project', 'Project ID: ' . $_POST['id']);
    }
}

// Delete Project
if (isset($_POST['delete'])) {
    if (!csrf_verify()) {
        $message = 'CSRF token tidak valid!';
    } else {
        $id = $_POST['id'];
        $stmt = $pdo->prepare('DELETE FROM projects WHERE id = ?');
        $stmt->execute([$id]);
        $message = 'Project deleted!';
        log_activity('delete_project', 'Project ID: ' . $id);
    }
}

// Pagination dan filtering
$page = max(1, intval($_GET['page'] ?? 1));
// Allow user-controlled page size like Activities list (10/15/20)
$limit = intval($_GET['limit'] ?? 10);
if (!in_array($limit, [10, 15, 20], true)) { $limit = 10; }
$offset = ($page - 1) * $limit;

$search = trim($_GET['search'] ?? '');
$filter_status = $_GET['filter_status'] ?? '';
$filter_type = $_GET['filter_type'] ?? '';
$filter_project_information = $_GET['filter_project_information'] ?? '';

$where_conditions = [];
$params = [];

if ($search) {
    $where_conditions[] = "(project_id ILIKE ? OR project_name ILIKE ? OR hotel_name ILIKE ?)";
    $search_term = "%$search%";
    $params = array_merge($params, [$search_term, $search_term, $search_term]);
}

if ($filter_status) {
    $where_conditions[] = "status = ?";
    $params[] = $filter_status;
}

if ($filter_type) {
    $where_conditions[] = "type = ?";
    $params[] = $filter_type;
}

if ($filter_project_information) {
    $where_conditions[] = "project_information = ?";
    $params[] = $filter_project_information;
}

$where_clause = $where_conditions ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Get total count
$count_sql = "SELECT COUNT(*) FROM projects $where_clause";
$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute($params);
$total_projects = $count_stmt->fetchColumn();
$total_pages = ceil($total_projects / $limit);

// Get projects with pagination
$sql = "SELECT * FROM projects $where_clause ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fixed options synced with database ENUMs
$typeOptions = [
    'Implementation', 'Upgrade', 'Maintenance', 'Retraining', 'On Line Training', 'On Line Maintenance',
    'Remote Installation', 'In House Training', 'Special Request', '2nd Implementation', 'Jakarta Support',
    'Bali Support', 'Others'
];
$statusOptions = [
    'Scheduled', 'Running', 'Document', 'Document Check', 'Done', 'Cancel', 'Rejected'
];
?>

<?php include './partials/layouts/layoutHorizontal.php'; ?>

        <div class="dashboard-main-body">

            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
                <h6 class="fw-semibold mb-0">Project List</h6>
                <ul class="d-flex align-items-center gap-2">
                    <li class="fw-medium">
                        <a href="index.php" class="d-flex align-items-center gap-1 hover-text-primary">
                            <iconify-icon icon="solar:home-smile-angle-outline" class="icon text-lg"></iconify-icon>
                            Dashboard
                        </a>
                    </li>
                    <li>-</li>
                    <li class="fw-medium">Project List</li>
                </ul>
            </div>

            <div class="card">
                <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div class="d-flex align-items-center gap-2">
                        <span class="fw-semibold">Show</span>
                        <form method="get" class="d-inline">
                            <select class="form-select form-select-sm w-auto" name="limit" onchange="this.form.submit()">
                                <option value="10" <?= $limit===10?'selected':''; ?>>10</option>
                                <option value="15" <?= $limit===15?'selected':''; ?>>15</option>
                                <option value="20" <?= $limit===20?'selected':''; ?>>20</option>
                            </select>
                            <?php if ($search) echo '<input type="hidden" name="search" value="'.htmlspecialchars($search).'">'; ?>
                            <?php if ($filter_status) echo '<input type="hidden" name="filter_status" value="'.htmlspecialchars($filter_status).'">'; ?>
                            <?php if ($filter_type) echo '<input type="hidden" name="filter_type" value="'.htmlspecialchars($filter_type).'">'; ?>
                        </form>
                    </div>
                    <button type="button" class="btn btn-sm btn-primary-600 d-flex align-items-center gap-2" onclick="showCreateForm()">
                        <iconify-icon icon="solar:add-circle-outline" class="icon"></iconify-icon>
                        Create Project
                    </button>
                </div>
                <div class="card-body">
<?php if ($message): ?>
                        <div class="alert alert-info"> <?= htmlspecialchars($message) ?> </div>
<?php endif; ?>

                    <style>
                    .custom-modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,.5); display: none; align-items: center; justify-content: center; z-index: 1050; }
                    .custom-modal { width: min(980px, 96vw); background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 20px 60px rgba(0,0,0,.2); }
                    .custom-modal-header { padding: 16px 20px; display: flex; align-items: center; justify-content: space-between; background: linear-gradient(135deg, #0ea5e9 0%, #3b82f6 100%); color: #fff; }
                    .custom-modal-title { margin: 0; font-size: 18px; font-weight: 600; }
                    .custom-modal-close { background: transparent; border: 0; color: #fff; font-size: 22px; cursor: pointer; line-height: 1; }
                    .custom-modal-body { padding: 20px; }
                    .custom-modal-footer { padding: 14px 20px; background: #f8fafc; display: flex; gap: 10px; justify-content: flex-end; }
                    .custom-modal-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 12px; }
                    .custom-modal-col { display: flex; flex-direction: column; gap: 6px; }
                    .custom-modal-label { font-weight: 600; font-size: 12px; color: #111827; }
                    .custom-modal-input, .custom-modal-select, .custom-modal-textarea { width: 100%; padding: 10px 12px; border-radius: 8px; border: 1px solid #e5e7eb; background: #fff; }
                    .custom-modal-input:focus, .custom-modal-select:focus, .custom-modal-textarea:focus { outline: none; border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,.15); }
                    .custom-btn { padding: 10px 14px; border-radius: 8px; border: 0; cursor: pointer; font-weight: 600; }
                    .custom-btn-primary { background: #2563eb; color: #fff; }
                    .custom-btn-danger { background: #dc2626; color: #fff; }
                    .custom-btn-secondary { background: #374151; color: #fff; }
                    [data-theme="dark"] .custom-modal { background: #111827; color: #e5e7eb; border: 1px solid #374151; }
                    [data-theme="dark"] .custom-modal-header { background: linear-gradient(135deg, #0b1220 0%, #111827 100%); }
                    [data-theme="dark"] .custom-modal-footer { background: #0b1220; }
                    [data-theme="dark"] .custom-modal-input, [data-theme="dark"] .custom-modal-select, [data-theme="dark"] .custom-modal-textarea { background: #0b1220; border-color: #374151; color: #e5e7eb; }
                    [data-theme="dark"] .custom-modal-label { color: #e5e7eb; }
                    @media (max-width: 768px) { .custom-modal-row { grid-template-columns: 1fr; } }
                    </style>

                    <!-- Create Project Form (Hidden by default) -->
                    <div id="createProjectForm" style="display:none; margin-bottom:24px; padding:20px; border:1px solid #ddd; border-radius:8px; background:#f9f9f9;">
                        <h5 class="mb-3">Add New Project</h5>
                        <form method="post" class="row g-3">
    <?= csrf_field() ?>
                            <div class="col-md-6">
                                <label class="form-label">Project ID *</label>
                                <input type="text" name="project_id" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Project Name *</label>
                                <input type="text" name="project_name" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Hotel Name</label>
                                <input type="text" name="hotel_name" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">PIC</label>
                                <input type="text" name="pic" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="Planning">Planning</option>
                                    <option value="In Progress">In Progress</option>
                                    <option value="Completed">Completed</option>
                                    <option value="On Hold">On Hold</option>
    </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Start Date</label>
                                <input type="date" name="start_date" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">End Date</label>
                                <input type="date" name="end_date" class="form-control">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Project Info</label>
                                <textarea name="project_info" class="form-control" rows="3"></textarea>
                            </div>
                            <div class="col-12">
                                <button type="submit" name="create" class="btn btn-primary">Add Project</button>
                                <button type="button" onclick="hideCreateForm()" class="btn btn-secondary">Cancel</button>
                            </div>
</form>
                    </div>

                    <!-- Filter Section -->
                    <div class="filter-section" style="margin-bottom:16px;">
                        <form method="get" class="filter-form">
                            <div class="filter-row" style="display:flex; flex-wrap:wrap; gap:16px;">
                                <div class="filter-group" style="min-width:240px; flex:1;">
                                    <label class="filter-label">Search</label>
                                    <div class="icon-field">
                                        <input type="text" name="search" class="form-control" placeholder="Search projects..." value="<?= htmlspecialchars($search) ?>">
                                        <span class="icon">
                                            <iconify-icon icon="ion:search-outline"></iconify-icon>
                                        </span>
                                    </div>
                                </div>
                                <div class="filter-group" style="min-width:200px;">
                                    <label class="filter-label">Status</label>
                                    <select class="form-select" name="filter_status">
                                        <option value="">All Status</option>
                                        <?php foreach ($statusOptions as $s): ?>
                                            <option value="<?= htmlspecialchars($s) ?>" <?= $filter_status === $s ? 'selected' : '' ?>><?= htmlspecialchars($s) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="filter-group" style="min-width:200px;">
                                    <label class="filter-label">Type</label>
                                    <select class="form-select" name="filter_type">
                                        <option value="">All Type</option>
                                        <?php foreach ($typeOptions as $t): ?>
                                            <option value="<?= htmlspecialchars($t) ?>" <?= $filter_type === $t ? 'selected' : '' ?>><?= htmlspecialchars($t) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="filter-group" style="min-width:200px;">
                                    <label class="filter-label">Project Information</label>
                                    <select class="form-select" name="filter_project_information">
                                        <option value="">All</option>
                                        <option value="Request" <?= ($_GET['filter_project_information'] ?? '') === 'Request' ? 'selected' : '' ?>>Request</option>
                                        <option value="Submission" <?= ($_GET['filter_project_information'] ?? '') === 'Submission' ? 'selected' : '' ?>>Submission</option>
                                    </select>
                                </div>
                            </div>
                            <div class="d-flex gap-2" style="margin-top:12px;">
                                <button type="submit" class="btn-apply btn btn-sm btn-primary-600">Apply Filters</button>
                                <a href="project.php" class="btn-reset btn btn-sm btn-secondary">Reset</a>
                            </div>
                        </form>
                    </div>

                    <style>
                        .table-responsive { overflow-x: auto; overflow-y: hidden; border-radius: 2px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
                        .table-header {
                            padding: 12px 16px; border: none; border-radius: 8px; margin: 0; font-weight: 600; color: white; font-size: 12px;
                            text-transform: uppercase; letter-spacing: .5px; text-align: center; box-shadow: 0 2px 8px rgba(79,70,229,.3);
                            transition: all .3s ease; position: relative; overflow: hidden; display: flex; align-items: center; justify-content: center;
                            height: 100%; min-height: 52px;
                        }
                        .table-header::before { content: ''; position: absolute; top: 0; left: -100%; width: 100%; height: 100%;
                            background: linear-gradient(90deg, transparent, rgba(255,255,255,.2), transparent); transition: left .5s; }
                        .table-header:hover::before { left: 100%; }
                        .table thead th { padding: 0 !important; vertical-align: middle !important; }
                    </style>
                    <div class="table-responsive">
                    <table class="table table-striped mb-0">
                        <thead>
                            <tr>
                                <th scope="col"><div class="table-header">Project ID</div></th>
                                <th scope="col"><div class="table-header">PIC</div></th>
                                <th scope="col"><div class="table-header">Hotel Name</div></th>
                                <th scope="col"><div class="table-header">Project Name</div></th>
                                <th scope="col"><div class="table-header">Start Date</div></th>
                                <th scope="col"><div class="table-header">End Date</div></th>
                                <th scope="col"><div class="table-header">Total Days</div></th>
                                <th scope="col"><div class="table-header">Type</div></th>
                                <th scope="col"><div class="table-header">Status</div></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($projects as $index => $p): ?>
                            <tr class="project-row"
                                data-id="<?= $p['id'] ?>"
                                data-project_id="<?= htmlspecialchars($p['project_id'] ?? '') ?>"
                                data-project_name="<?= htmlspecialchars($p['project_name'] ?? '') ?>"
                                data-hotel_name="<?= htmlspecialchars($p['hotel_name'] ?? '') ?>"
                                data-pic="<?= htmlspecialchars($p['pic'] ?? '') ?>"
                                data-status="<?= htmlspecialchars($p['status'] ?? '') ?>"
                                data-type="<?= htmlspecialchars($p['type'] ?? '') ?>"
                                data-start_date="<?= htmlspecialchars($p['start_date'] ?? '') ?>"
                                data-end_date="<?= htmlspecialchars($p['end_date'] ?? '') ?>"
                                data-project_info="<?= htmlspecialchars($p['project_info'] ?? '') ?>"
                                data-assignment="<?= htmlspecialchars($p['assignment'] ?? '') ?>"
                                data-req_pic="<?= htmlspecialchars($p['req_pic'] ?? '') ?>"
                                data-handover_report="<?= htmlspecialchars($p['handover_report'] ?? '') ?>"
                                data-handover_days="<?= htmlspecialchars($p['handover_days'] ?? '') ?>"
                                data-ketertiban_admin="<?= htmlspecialchars($p['ketertiban_admin'] ?? '') ?>"
                                data-point_ach="<?= htmlspecialchars($p['point_ach'] ?? '') ?>"
                                data-point_req="<?= htmlspecialchars($p['point_req'] ?? '') ?>"
                                data-percent_point="<?= htmlspecialchars($p['percent_point'] ?? '') ?>"
                                data-month="<?= htmlspecialchars($p['month'] ?? '') ?>"
                                data-quarter="<?= htmlspecialchars($p['quarter'] ?? '') ?>"
                                data-week_number="<?= htmlspecialchars($p['week_number'] ?? '') ?>"
                            >
                                <td data-label="Project ID"><?= htmlspecialchars($p['project_id'] ?: '-') ?></td>
                                <td data-label="PIC"><?= htmlspecialchars($p['pic'] ?: '-') ?></td>
                                <td data-label="Hotel Name"><?= htmlspecialchars($p['hotel_name'] ?: '-') ?></td>
                                <td data-label="Project Name"><?= htmlspecialchars($p['project_name'] ?: '-') ?></td>
                                <td data-label="Start Date"><?= $p['start_date'] ? date('d M Y', strtotime($p['start_date'])) : '-' ?></td>
                                <td data-label="End Date"><?= $p['end_date'] ? date('d M Y', strtotime($p['end_date'])) : '-' ?></td>
                                <td data-label="Total Days"><?= htmlspecialchars($p['total_days'] ?: '-') ?></td>
                                <td data-label="Type">
                                    <span class="type-badge bg-neutral-200 text-neutral-600 px-8 py-4 rounded-pill fw-medium text-sm"><?= htmlspecialchars($p['type'] ?: '-') ?></span>
                                </td>
                                <td data-label="Status">
                                    <?php
                                    $status_colors = [
                                        'Scheduled' => 'bg-warning-focus text-warning-main',
                                        'Running' => 'bg-info-focus text-info-main',
                                        'Document' => 'bg-secondary-focus text-secondary-main',
                                        'Document Check' => 'bg-cyan-100 text-cyan-700',
                                        'Done' => 'bg-success-focus text-success-main',
                                        'Cancel' => 'bg-danger-focus text-danger-main',
                                        'Rejected' => 'bg-neutral-200 text-neutral-600',
                                    ];
                                    $color_class = $status_colors[$p['status']] ?? 'bg-neutral-200 text-neutral-600';
                                    ?>
                                    <span class="status-badge <?= $color_class ?> px-8 py-4 rounded-pill fw-medium text-sm"><?= htmlspecialchars($p['status']) ?></span>
                                </td>
</tr>
<?php endforeach; ?>
                        </tbody>
                    </table>
                    </div>

                    <!-- Edit Project Modal (Custom like Activity) -->
                    <div class="custom-modal-overlay" id="editProjectModal">
                        <div class="custom-modal">
                            <div class="custom-modal-header">
                                <h5 class="custom-modal-title">Edit Project</h5>
                                <button type="button" class="custom-modal-close" onclick="closeProjectEditModal()">&times;</button>
                            </div>
                            <form method="post">
                                <div class="custom-modal-body">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="id" id="edit_id">

                                    <div class="custom-modal-row">
                                        <div class="custom-modal-col">
                                            <label class="custom-modal-label">Project ID</label>
                                            <input type="text" name="project_id" id="edit_project_id" class="custom-modal-input">
                                        </div>
                                        <div class="custom-modal-col">
                                            <label class="custom-modal-label">Project Name</label>
                                            <input type="text" name="project_name" id="edit_project_name" class="custom-modal-input">
                                        </div>
                                    </div>

                                    <div class="custom-modal-row">
                                        <div class="custom-modal-col">
                                            <label class="custom-modal-label">Hotel Name</label>
                                            <input type="text" name="hotel_name" id="edit_hotel_name" class="custom-modal-input">
                                        </div>
                                        <div class="custom-modal-col">
                                            <label class="custom-modal-label">PIC</label>
                                            <input type="text" name="pic" id="edit_pic" class="custom-modal-input">
                                        </div>
                                    </div>

                                    <div class="custom-modal-row">
                                        <div class="custom-modal-col">
                                            <label class="custom-modal-label">Project Information</label>
                                            <select name="project_information" id="edit_project_information" class="custom-modal-select" required>
                                                <option value="Request">Request</option>
                                                <option value="Submission">Submission</option>
                                            </select>
                                        </div>
                                        <div class="custom-modal-col">
                                            <label class="custom-modal-label">Status</label>
                                            <select name="status" id="edit_status" class="custom-modal-select" required>
                                                <?php foreach ($statusOptions as $s): ?>
                                                    <option value="<?= htmlspecialchars($s) ?>"><?= htmlspecialchars($s) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="custom-modal-col">
                                            <label class="custom-modal-label">Type</label>
                                            <select name="type" id="edit_type" class="custom-modal-select" required>
                                                <?php foreach ($typeOptions as $t): ?>
                                                    <option value="<?= htmlspecialchars($t) ?>"><?= htmlspecialchars($t) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="custom-modal-row">
                                        <div class="custom-modal-col">
                                            <label class="custom-modal-label">Start Date</label>
                                            <input type="date" name="start_date" id="edit_start_date" class="custom-modal-input">
                                        </div>
                                        <div class="custom-modal-col">
                                            <label class="custom-modal-label">End Date</label>
                                            <input type="date" name="end_date" id="edit_end_date" class="custom-modal-input">
                                        </div>
                                    </div>

                                    <div class="custom-modal-row">
                                        <div class="custom-modal-col" style="grid-column: 1 / -1;">
                                            <label class="custom-modal-label">Project Remark</label>
                                            <textarea name="project_remark" id="edit_project_remark" class="custom-modal-textarea" rows="3"></textarea>
                                        </div>
                                    </div>

                                    <!-- Hidden to keep update handler satisfied -->
                                    <input type="hidden" name="assignment" id="edit_assignment">
                                    <input type="hidden" name="req_pic" id="edit_req_pic">
                                    <input type="hidden" name="handover_report" id="edit_handover_report">
                                    <input type="hidden" name="handover_days" id="edit_handover_days">
                                    <input type="hidden" name="ketertiban_admin" id="edit_ketertiban_admin">
                                    <input type="hidden" name="point_ach" id="edit_point_ach">
                                    <input type="hidden" name="point_req" id="edit_point_req">
                                    <input type="hidden" name="percent_point" id="edit_percent_point">
                                    <input type="hidden" name="month" id="edit_month">
                                    <input type="hidden" name="quarter" id="edit_quarter">
                                    <input type="hidden" name="week_number" id="edit_week_number">
                                </div>
                                <div class="custom-modal-footer">
                                    <button type="submit" name="update" value="1" class="custom-btn custom-btn-primary">Save</button>
                                    <button type="button" class="custom-btn custom-btn-secondary" onclick="closeProjectEditModal()">Close</button>
                                </div>
                            </form>
                        </div>
                    </div>

<!-- Pagination -->
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mt-24">
                        <span class="text-md text-secondary-light fw-normal">Showing <?= count($projects) ?> of <?= $total_projects ?> results</span>
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

<script src="assets/js/activity-table.js"></script>
<script>
function showCreateForm() {
    document.getElementById('createProjectForm').style.display = 'block';
}

function hideCreateForm() {
    document.getElementById('createProjectForm').style.display = 'none';
}


// Row click to open edit modal (same behavior as Activity List)
document.addEventListener('DOMContentLoaded', function() {
    const rows = document.querySelectorAll('.project-row');
    const modalEl = document.getElementById('editProjectModal');

    function openEditModalFromRow(row) {
        const get = (name) => row.getAttribute('data-' + name) || '';
        document.getElementById('edit_id').value = get('id');
        document.getElementById('edit_project_id').value = get('project_id');
        document.getElementById('edit_project_name').value = get('project_name');
        document.getElementById('edit_hotel_name').value = get('hotel_name');
        document.getElementById('edit_pic').value = get('pic');
        document.getElementById('edit_status').value = get('status');
        document.getElementById('edit_type').value = get('type');
        document.getElementById('edit_start_date').value = get('start_date');
        document.getElementById('edit_end_date').value = get('end_date');
        const pi = get('project_info');
        const piSelect = document.getElementById('edit_project_information');
        if (piSelect) {
            if (pi === 'Request' || pi === 'Submission') { piSelect.value = pi; } else { piSelect.value = 'Request'; }
        }
        const remarkEl = document.getElementById('edit_project_remark');
        if (remarkEl) { remarkEl.value = get('project_remark'); }
            const hiddenIds = ['assignment','req_pic','handover_report','handover_days','ketertiban_admin','point_ach','point_req','percent_point','month','quarter','week_number'];
        hiddenIds.forEach(id => {
            const el = document.getElementById('edit_' + id);
            if (el) el.value = get(id);
        });

        // Delete disabled: function removed per request

        // Show custom modal overlay
        if (modalEl) { modalEl.style.display = 'flex'; }

        // Safety: allow closing by ESC and backdrop click even if bootstrap not active
        function handleKeydown(e){ if (e.key === 'Escape') closeModal(); }
        function closeModal(){
            if (modalEl) { modalEl.style.display = 'none'; }
            document.removeEventListener('keydown', handleKeydown);
        }
        document.addEventListener('keydown', handleKeydown);

        // Close on backdrop click (Bootstrap auto handles; fallback for non-bootstrap)
        modalEl.addEventListener('click', function onClick(e){
            if (e.target === modalEl) { // clicked outside dialog
                modalEl.removeEventListener('click', onClick);
                closeModal();
            }
        });
    }

    rows.forEach(function(row){
        row.style.cursor = 'pointer';
        row.addEventListener('click', function(){ openEditModalFromRow(row); });
    });
});

function closeProjectEditModal(){
    const modalEl = document.getElementById('editProjectModal');
    if (modalEl) { modalEl.style.display = 'none'; }
}
</script>

<?php include './partials/layouts/layoutBottom.php'; ?>
