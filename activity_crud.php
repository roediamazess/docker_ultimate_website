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

// Create Activity
if (isset($_POST['create'])) {
    if (!csrf_verify()) {
        $message = 'CSRF token tidak valid!';
    } else {
        $stmt = $pdo->prepare('INSERT INTO activities (project_id, no, information_date, user_position, department, application, type, description, action_solution, due_date, status, cnc_number, priority, customer, project, created_by, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            $_POST['project_id'] ?? null,
            $_POST['no'] ?? null,
            $_POST['information_date'] ?? null,
            $_POST['user_position'],
            $_POST['department'],
            $_POST['application'],
            $_POST['type'],
            $_POST['description'],
            $_POST['action_solution'],
            $_POST['due_date'] ?: null,
            $_POST['status'],
            $_POST['cnc_number'],
            $_POST['priority'] ?? 'Normal',
            $_POST['customer'] ?? null,
            $_POST['project'] ?? null,
            get_current_user_id(),
            date('Y-m-d H:i:s')
        ]);
        $message = 'Activity created!';
        log_activity('create_activity', 'Activity: ' . $_POST['type']);
    }
}

// Update Activity
if (isset($_POST['update'])) {
    if (!csrf_verify()) {
        $message = 'CSRF token tidak valid!';
    } else {
        $informationDate = !empty($_POST['information_date']) ? $_POST['information_date'] : null;
        $stmt = $pdo->prepare('UPDATE activities SET project_id=?, no=?, information_date=?, user_position=?, department=?, application=?, type=?, description=?, action_solution=?, due_date=?, status=?, cnc_number=?, priority=?, customer=?, project=? WHERE id=?');
        $stmt->execute([
            $_POST['project_id'] ?? null,
            $_POST['no'] ?? null,
            $informationDate,
            $_POST['user_position'],
            $_POST['department'],
            $_POST['application'],
            $_POST['type'],
            $_POST['description'],
            $_POST['action_solution'],
            $_POST['due_date'] ?: null,
            $_POST['status'],
            $_POST['cnc_number'],
            $_POST['priority'] ?? 'Normal',
            $_POST['customer'] ?? null,
            $_POST['project'] ?? null,
            $_POST['id']
        ]);
        $message = 'Activity updated!';
        log_activity('update_activity', 'Activity ID: ' . $_POST['id']);
    }
}

// Delete Activity
if (isset($_POST['delete'])) {
    if (!csrf_verify()) {
        $message = 'CSRF token tidak valid!';
    } else {
        $id = $_POST['id'];
        $stmt = $pdo->prepare('DELETE FROM activities WHERE id = ?');
        $stmt->execute([$id]);
        $message = 'Activity deleted!';
        log_activity('delete_activity', 'Activity ID: ' . $id);
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
$allowed_sort_columns = ['no', 'information_date', 'priority', 'user_position', 'department', 'application', 'type', 'description', 'action_solution', 'status'];
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
    // Filter untuk status yang belum Done
    $where_conditions[] = "a.status != 'Done'";
} elseif ($filter_status) {
    $where_conditions[] = "a.status = ?";
    $params[] = $filter_status;
} elseif ($default_status_filter === 'not_done') {
    // Filter default: tampilkan status yang belum Done
    $where_conditions[] = "a.status != 'Done'";
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

// Get total count
$count_sql = "SELECT COUNT(*) FROM activities a $where_clause";
$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute($params);
$total_activities = $count_stmt->fetchColumn();
$total_pages = ceil($total_activities / $limit);

// Get activities with pagination and sorting - hanya kolom yang diperlukan untuk display
$sql = "SELECT a.no, a.information_date, a.priority, a.user_position, a.department, a.application, a.type, a.description, a.action_solution, a.status, a.id FROM activities a $where_clause ORDER BY a.$sort_column $sort_order LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$activities = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get projects for dropdown
$projects = $pdo->query('SELECT project_id, project_name FROM projects ORDER BY project_name')->fetchAll(PDO::FETCH_ASSOC);
// Next auto number for display (server tetap akan hitung saat insert)
$next_no = (int)($pdo->query('SELECT COALESCE(MAX(no),0)+1 FROM activities')->fetchColumn());
?>

<?php include './partials/layouts/layoutHorizontal.php'; ?>

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
                        <div class="d-flex align-items-center gap-2">
                        <span class="fw-semibold">Show</span>
                            <select class="form-select form-select-sm w-auto" name="limit" onchange="this.form.submit()">
                                <option value="10" <?= $limit===10?'selected':''; ?>>10</option>
                                <option value="15" <?= $limit===15?'selected':''; ?>>15</option>
                                <option value="20" <?= $limit===20?'selected':''; ?>>20</option>
                            </select>
                        </div>
                    <button type="button" class="btn btn-sm btn-primary-600 d-flex align-items-center gap-2" id="createActivityBtn" data-bs-toggle="modal" data-bs-target="#createActivityModal">
                        <iconify-icon icon="solar:add-circle-outline" class="icon"></iconify-icon>
                        Create Activity
                    </button>
                </div>
                
                <!-- Filter Section -->
                <div class="filter-section">
                    <form method="get" class="filter-form">
                        <div class="filter-row">
                            <div class="filter-group">
                                <label class="filter-label">Search</label>
                        <div class="icon-field">
                                    <input type="text" name="search" class="form-control" placeholder="Search activities..." value="<?= htmlspecialchars($search) ?>">
                            <span class="icon">
                                <iconify-icon icon="ion:search-outline"></iconify-icon>
                            </span>
                        </div>
                            </div>
                            <div class="filter-group">
                                <label class="filter-label">Priority</label>
                                <select class="form-select" name="filter_priority">
                            <option value="">All Priority</option>
                            <option value="Urgent" <?= $filter_priority === 'Urgent' ? 'selected' : '' ?>>Urgent</option>
                            <option value="Normal" <?= $filter_priority === 'Normal' ? 'selected' : '' ?>>Normal</option>
                            <option value="Low" <?= $filter_priority === 'Low' ? 'selected' : '' ?>>Low</option>
                        </select>
                            </div>
                            <div class="filter-group">
                                <label class="filter-label">Department</label>
                                <select class="form-select" name="filter_department">
                                    <option value="">All Department</option>
                                    <option value="Food & Beverage" <?= $filter_department === 'Food & Beverage' ? 'selected' : '' ?>>Food & Beverage</option>
                                    <option value="Kitchen" <?= $filter_department === 'Kitchen' ? 'selected' : '' ?>>Kitchen</option>
                                    <option value="Room Division" <?= $filter_department === 'Room Division' ? 'selected' : '' ?>>Room Division</option>
                                    <option value="Front Office" <?= $filter_department === 'Front Office' ? 'selected' : '' ?>>Front Office</option>
                                    <option value="Housekeeping" <?= $filter_department === 'Housekeeping' ? 'selected' : '' ?>>Housekeeping</option>
                                    <option value="Engineering" <?= $filter_department === 'Engineering' ? 'selected' : '' ?>>Engineering</option>
                                    <option value="Sales & Marketing" <?= $filter_department === 'Sales & Marketing' ? 'selected' : '' ?>>Sales & Marketing</option>
                                    <option value="IT / EDP" <?= $filter_department === 'IT / EDP' ? 'selected' : '' ?>>IT / EDP</option>
                                    <option value="Accounting" <?= $filter_department === 'Accounting' ? 'selected' : '' ?>>Accounting</option>
                                    <option value="Executive Office" <?= $filter_department === 'Executive Office' ? 'selected' : '' ?>>Executive Office</option>
                                </select>
                            </div>
                            <div class="filter-group">
                                <label class="filter-label">Application</label>
                                <select class="form-select" name="filter_application">
                                    <option value="">All Application</option>
                                    <option value="POS" <?= $filter_application === 'POS' ? 'selected' : '' ?>>POS</option>
                                    <option value="PMS" <?= $filter_application === 'PMS' ? 'selected' : '' ?>>PMS</option>
                                    <option value="Back Office" <?= $filter_application === 'Back Office' ? 'selected' : '' ?>>Back Office</option>
                                    <option value="Website" <?= $filter_application === 'Website' ? 'selected' : '' ?>>Website</option>
                                    <option value="Mobile App" <?= $filter_application === 'Mobile App' ? 'selected' : '' ?>>Mobile App</option>
                                </select>
                            </div>
                            <div class="filter-group">
                                <label class="filter-label">Type</label>
                                <select class="form-select" name="filter_type">
                            <option value="">All Type</option>
                            <option value="Setup" <?= $filter_type === 'Setup' ? 'selected' : '' ?>>Setup</option>
                            <option value="Question" <?= $filter_type === 'Question' ? 'selected' : '' ?>>Question</option>
                            <option value="Issue" <?= $filter_type === 'Issue' ? 'selected' : '' ?>>Issue</option>
                            <option value="Report Issue" <?= $filter_type === 'Report Issue' ? 'selected' : '' ?>>Report Issue</option>
                            <option value="Report Request" <?= $filter_type === 'Report Request' ? 'selected' : '' ?>>Report Request</option>
                            <option value="Feature Request" <?= $filter_type === 'Feature Request' ? 'selected' : '' ?>>Feature Request</option>
                        </select>
                            </div>
                            <div class="filter-group">
                                <label class="filter-label">Status</label>
                                <select class="form-select" name="filter_status">
                                    <option value="">All Status</option>
                                    <option value="not_done" <?= ($filter_status === 'not_done' || ($default_status_filter === 'not_done' && !$filter_status)) ? 'selected' : '' ?>>Active (Default)</option>
                                    <option value="Open" <?= $filter_status === 'Open' ? 'selected' : '' ?>>Open</option>
                                    <option value="On Progress" <?= $filter_status === 'On Progress' ? 'selected' : '' ?>>On Progress</option>
                                    <option value="Need Requirement" <?= $filter_status === 'Need Requirement' ? 'selected' : '' ?>>Need Requirement</option>
                                    <option value="Done" <?= $filter_status === 'Done' ? 'selected' : '' ?>>Done</option>
                                </select>
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn-apply">Apply Filters</button>
                            <a href="activity_crud.php" class="btn-reset">Reset</a>
                        </div>
                    </form>
                </div>
                
                <!-- Create Activity Modal - Static HTML -->
                <div class="modal fade" id="createActivityModal" tabindex="-1" aria-labelledby="createActivityModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-scrollable">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="createActivityModalLabel">Add Activity</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form method="post">
                                <div class="modal-body">
                                    <?= csrf_field() ?>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">No</label>
                                            <input type="number" name="no" class="form-control" value="<?= (int)$next_no ?>">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Status *</label>
                                            <select name="status" class="form-select" required>
                                                <option value="Open">Open</option>
                                                <option value="On Progress">On Progress</option>
                                                <option value="Need Requirement">Need Requirement</option>
                                                <option value="Done">Done</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Information Date *</label>
                                            <input type="date" name="information_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Priority *</label>
                                            <select name="priority" class="form-select" required>
                                                <option value="Urgent">Urgent</option>
                                                <option value="Normal" selected>Normal</option>
                                                <option value="Low">Low</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">User Position</label>
                                            <input type="text" name="user_position" class="form-control">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Department</label>
                                            <select name="department" class="form-select">
                                                <option value="Food & Beverage" selected>Food & Beverage</option>
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
                                            <label class="form-label">Type</label>
                                            <select name="type" class="form-select">
                                                <option value="Setup">Setup</option>
                                                <option value="Question">Question</option>
                                                <option value="Issue">Issue</option>
                                                <option value="Report Issue">Report Issue</option>
                                                <option value="Report Request">Report Request</option>
                                                <option value="Feature Request">Feature Request</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Customer</label>
                                            <input type="text" name="customer" class="form-control">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Project</label>
                                            <input type="text" name="project" class="form-control">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Completed Date</label>
                                            <input type="date" name="due_date" class="form-control">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">CNC Number</label>
                                            <input type="text" name="cnc_number" class="form-control">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Description</label>
                                            <textarea name="description" class="form-control" rows="3"></textarea>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Action Solution</label>
                                            <textarea name="action_solution" class="form-control" rows="3"></textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    <button type="submit" name="create" value="1" class="btn btn-primary">Create</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="card-body">
                    <?php if ($default_status_filter === 'not_done' && !$filter_status): ?>
                        <div class="alert alert-info d-flex align-items-center gap-2 mb-3">
                            <iconify-icon icon="solar:info-circle-outline" class="icon text-lg"></iconify-icon>
                            <span>Default menampilkan aktivitas dengan status yang Active (belum selesai).</span>
                        </div>
                    <?php endif; ?>
                    <style>
                        .activity-row { cursor: pointer; }
                        .activity-row:hover { background-color: rgba(102,126,234,0.08); }
                    </style>
<?php if ($message): ?>
                        <div class="alert alert-info"> <?= htmlspecialchars($message) ?> </div>
<?php endif; ?>

                    <div class="card-body">

                    <table class="table sortable-table mb-0">
                        <thead>
                            <tr>
                                <th scope="col">
                                    <a href="?<?= http_build_query(array_merge($_GET, ['sort' => 'no', 'order' => $sort_column === 'no' && $sort_order === 'ASC' ? 'desc' : 'asc'])) ?>" class="sortable-header <?= $sort_column === 'no' ? ($sort_order === 'ASC' ? 'sort-asc' : 'sort-desc') : '' ?>">
                                        <span class="header-text">No</span>
                                        <iconify-icon icon="solar:sorting-bold" class="sort-icon"></iconify-icon>
                                    </a>
                                </th>
                                <th scope="col">
                                    <a href="?<?= http_build_query(array_merge($_GET, ['sort' => 'information_date', 'order' => $sort_column === 'information_date' && $sort_order === 'ASC' ? 'desc' : 'asc'])) ?>" class="sortable-header <?= $sort_column === 'information_date' ? ($sort_order === 'ASC' ? 'sort-asc' : 'sort-desc') : '' ?>">
                                        <span class="header-text">Information Date</span>
                                        <iconify-icon icon="solar:sorting-bold" class="sort-icon"></iconify-icon>
                                    </a>
                                </th>
                                <th scope="col">
                                    <a href="?<?= http_build_query(array_merge($_GET, ['sort' => 'priority', 'order' => $sort_column === 'priority' && $sort_order === 'ASC' ? 'desc' : 'asc'])) ?>" class="sortable-header <?= $sort_column === 'priority' ? ($sort_order === 'ASC' ? 'sort-asc' : 'sort-desc') : '' ?>">
                                        <span class="header-text">Priority</span>
                                        <iconify-icon icon="solar:sorting-bold" class="sort-icon"></iconify-icon>
                                    </a>
                                </th>
                                <th scope="col">
                                    <a href="?<?= http_build_query(array_merge($_GET, ['sort' => 'user_position', 'order' => $sort_column === 'user_position' && $sort_order === 'ASC' ? 'desc' : 'asc'])) ?>" class="sortable-header <?= $sort_column === 'user_position' ? ($sort_order === 'ASC' ? 'sort-asc' : 'sort-desc') : '' ?>">
                                        <span class="header-text">User &amp; Position</span>
                                        <iconify-icon icon="solar:sorting-bold" class="sort-icon"></iconify-icon>
                                    </a>
                                </th>
                                <th scope="col">
                                    <a href="?<?= http_build_query(array_merge($_GET, ['sort' => 'department', 'order' => $sort_column === 'department' && $sort_order === 'ASC' ? 'desc' : 'asc'])) ?>" class="sortable-header <?= $sort_column === 'department' ? ($sort_order === 'ASC' ? 'sort-asc' : 'sort-desc') : '' ?>">
                                        <span class="header-text">Department</span>
                                        <iconify-icon icon="solar:sorting-bold" class="sort-icon"></iconify-icon>
                                    </a>
                                </th>
                                <th scope="col">
                                    <a href="?<?= http_build_query(array_merge($_GET, ['sort' => 'application', 'order' => $sort_column === 'application' && $sort_order === 'ASC' ? 'desc' : 'asc'])) ?>" class="sortable-header <?= $sort_column === 'application' ? ($sort_order === 'ASC' ? 'sort-asc' : 'sort-desc') : '' ?>">
                                        <span class="header-text">Application</span>
                                        <iconify-icon icon="solar:arrow-up-bold" class="sort-icon"></iconify-icon>
                                    </a>
                                </th>
                                <th scope="col">
                                    <a href="?<?= http_build_query(array_merge($_GET, ['sort' => 'type', 'order' => $sort_column === 'type' && $sort_order === 'ASC' ? 'desc' : 'asc'])) ?>" class="sortable-header <?= $sort_column === 'type' ? ($sort_order === 'ASC' ? 'sort-asc' : 'sort-desc') : '' ?>">
                                        <span class="header-text">Type</span>
                                        <iconify-icon icon="solar:sorting-bold" class="sort-icon"></iconify-icon>
                                    </a>
                                </th>
                                <th scope="col">
                                    <a href="?<?= http_build_query(array_merge($_GET, ['sort' => 'description', 'order' => $sort_column === 'description' && $sort_order === 'ASC' ? 'desc' : 'asc'])) ?>" class="sortable-header <?= $sort_column === 'description' ? ($sort_order === 'ASC' ? 'sort-asc' : 'sort-desc') : '' ?>">
                                        <span class="header-text">Description</span>
                                        <iconify-icon icon="solar:sorting-bold" class="sort-icon"></iconify-icon>
                                    </a>
                                </th>
                                <th scope="col">
                                    <a href="?<?= http_build_query(array_merge($_GET, ['sort' => 'action_solution', 'order' => $sort_column === 'action_solution' && $sort_order === 'ASC' ? 'desc' : 'asc'])) ?>" class="sortable-header <?= $sort_column === 'action_solution' ? ($sort_order === 'ASC' ? 'sort-asc' : 'sort-desc') : '' ?>">
                                        <span class="header-text">Action / Solution</span>
                                        <iconify-icon icon="solar:sorting-bold" class="sort-icon"></iconify-icon>
                                    </a>
                                </th>
                                <th scope="col">
                                    <a href="?<?= http_build_query(array_merge($_GET, ['sort' => 'status', 'order' => $sort_column === 'status' && $sort_order === 'ASC' ? 'desc' : 'asc'])) ?>" class="sortable-header <?= $sort_column === 'status' ? ($sort_order === 'ASC' ? 'sort-asc' : 'sort-desc') : '' ?>">
                                        <span class="header-text">Status</span>
                                        <iconify-icon icon="solar:sorting-bold" class="sort-icon"></iconify-icon>
                                    </a>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($activities as $index => $a): ?>
                            <tr class="activity-row"
                                data-id="<?= $a['id'] ?>"
                                data-no="<?= htmlspecialchars($a['no'] ?? '') ?>"
                                data-user-position="<?= htmlspecialchars($a['user_position'] ?? '') ?>"
                                data-department="<?= htmlspecialchars($a['department'] ?? '') ?>"
                                data-application="<?= htmlspecialchars($a['application'] ?? '') ?>"
                                data-type="<?= htmlspecialchars($a['type'] ?? '') ?>"
                                data-description="<?= htmlspecialchars($a['description'] ?? '') ?>"
                                data-action-solution="<?= htmlspecialchars($a['action_solution'] ?? '') ?>"
                                data-status="<?= htmlspecialchars($a['status'] ?? '') ?>"
                                data-priority="<?= htmlspecialchars($a['priority'] ?? '') ?>"
                                data-information-date="<?= htmlspecialchars($a['information_date'] ?? '') ?>">
                                <td data-label="No"><?= htmlspecialchars($a['no'] ?: '-') ?></td>
                                <td data-label="Information Date"><?= $a['information_date'] ? date('d M Y', strtotime($a['information_date'])) : '-' ?></td>
                                <td data-label="Priority">
                                    <?php
                                    $priority_colors = [
                                        'Urgent' => 'bg-danger-focus text-danger-main',
                                        'Normal' => 'bg-info-focus text-info-main',
                                        'Low' => 'bg-neutral-200 text-neutral-600'
                                    ];
                                    $priority_class = $priority_colors[$a['priority'] ?? 'Normal'] ?? 'bg-neutral-200 text-neutral-600';
                                    ?>
                                    <span class="priority-badge <?= $priority_class ?> px-8 py-4 rounded-pill fw-medium text-sm"><?= htmlspecialchars($a['priority'] ?? 'Normal') ?></span>
                                </td>
                                <td data-label="User & Position"><?= htmlspecialchars($a['user_position'] ?: '-') ?></td>
                                <td data-label="Department"><?= htmlspecialchars($a['department'] ?: '-') ?></td>
                                <td data-label="Application"><?= htmlspecialchars($a['application'] ?: '-') ?></td>
                                <td data-label="Type">
                                    <?php
                                    $type_colors = [
                                        'Setup' => 'bg-info-focus text-info-main',
                                        'Question' => 'bg-warning-focus text-warning-main',
                                        'Issue' => 'bg-danger-focus text-danger-main',
                                        'Report Issue' => 'bg-danger-focus text-danger-main',
                                        'Report Request' => 'bg-neutral-200 text-neutral-600',
                                        'Feature Request' => 'bg-success-focus text-success-main'
                                    ];
                                    $color_class = $type_colors[$a['type']] ?? 'bg-neutral-200 text-neutral-600';
                                    ?>
                                    <span class="type-badge <?= $color_class ?> px-8 py-4 rounded-pill fw-medium text-sm"><?= htmlspecialchars($a['type'] ?: '-') ?></span>
                                </td>
                                <td data-label="Description"><?= htmlspecialchars($a['description'] ?: '-') ?></td>
                                <td data-label="Action / Solution"><?= htmlspecialchars($a['action_solution'] ?: '-') ?></td>
                                <td data-label="Status">
                                    <?php
                                    $status_colors = [
                                        'Open' => 'bg-warning-focus text-warning-main',
                                        'On Progress' => 'bg-info-focus text-info-main',
                                        'Need Requirement' => 'bg-secondary-focus text-secondary-main',
                                        'Done' => 'bg-success-focus text-success-main'
                                    ];
                                    $status_class = $status_colors[$a['status'] ?? 'Open'] ?? 'bg-neutral-200 text-neutral-600';
                                    ?>
                                    <span class="status-badge <?= $status_class ?> px-8 py-4 rounded-pill fw-medium text-sm"><?= htmlspecialchars($a['status'] ?? 'Open') ?></span>
                                </td>
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
function showCreateForm() {
    console.log('showCreateForm called - using static modal');
    
    // Get the existing modal element
    const modalEl = document.getElementById('createActivityModal');
    console.log('Modal element found:', modalEl);
    
    if (!modalEl) {
        console.error('Modal element not found!');
        return;
    }
    
    // Check if Bootstrap is available
    if (typeof bootstrap === 'undefined') {
        console.error('Bootstrap is not loaded!');
        alert('Bootstrap tidak tersedia!');
        return;
    }
    
    if (typeof bootstrap.Modal === 'undefined') {
        console.error('Bootstrap Modal class is not available!');
        alert('Bootstrap Modal tidak tersedia!');
        return;
    }
    
    try {
        console.log('Creating Bootstrap modal instance...');
        const modal = new bootstrap.Modal(modalEl);
        console.log('Modal instance created:', modal);
        
        console.log('Showing modal...');
        modal.show();
        console.log('Modal.show() called successfully');
        
    } catch (error) {
        console.error('Error showing modal:', error);
        alert('Error: ' + error.message);
    }
}

// Fallback function using vanilla JavaScript
function showVanillaModal() {
    console.log('showVanillaModal called');
    
    // Remove existing modal if any
    const existingModal = document.getElementById('vanillaCreateModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Create modal container
    const modalContainer = document.createElement('div');
    modalContainer.id = 'vanillaCreateModal';
    modalContainer.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;
    `;
    
    // Create modal content
    const modalContent = document.createElement('div');
    modalContent.style.cssText = `
        background: white;
        border-radius: 8px;
        padding: 20px;
        max-width: 800px;
        width: 90%;
        max-height: 90vh;
        overflow-y: auto;
        position: relative;
        box-shadow: 0 4px 20px rgba(0,0,0,0.3);
    `;
    
    modalContent.innerHTML = `
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 10px;">
            <h4 style="margin: 0; color: #333;">Add Activity</h4>
            <button onclick="closeVanillaModal()" style="background: none; border: none; font-size: 24px; cursor: pointer; color: #666;">&times;</button>
        </div>
        <form method="post">
            <?= csrf_field() ?>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: bold; color: #333;">No</label>
                    <input type="number" name="no" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;" value="<?= (int)$next_no ?>">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: bold; color: #333;">Status *</label>
                    <select name="status" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;" required>
                        <option value="Open">Open</option>
                        <option value="On Progress">On Progress</option>
                        <option value="Need Requirement">Need Requirement</option>
                        <option value="Done">Done</option>
                    </select>
                </div>
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: bold; color: #333;">Information Date *</label>
                    <input type="date" name="information_date" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;" required>
                </div>
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: bold; color: #333;">Priority *</label>
                    <select name="priority" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;" required>
                        <option value="Urgent">Urgent</option>
                        <option value="Normal" selected>Normal</option>
                        <option value="Low">Low</option>
                    </select>
                </div>
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: bold; color: #333;">User Position</label>
                    <input type="text" name="user_position" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: bold; color: #333;">Department</label>
                    <select name="department" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
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
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: bold; color: #333;">Application *</label>
                    <select name="application" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;" required>
                        <option value="">-</option>
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
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: bold; color: #333;">Type</label>
                    <select name="type" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                        <option value="Setup">Setup</option>
                        <option value="Question">Question</option>
                        <option value="Issue">Issue</option>
                        <option value="Report Issue">Report Issue</option>
                        <option value="Report Request">Report Request</option>
                        <option value="Feature Request">Feature Request</option>
                    </select>
                </div>
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: bold; color: #333;">Customer</label>
                    <input type="text" name="customer" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: bold; color: #333;">Project</label>
                    <input type="text" name="project" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: bold; color: #333;">Completed Date</label>
                    <input type="date" name="due_date" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: bold; color: #333;">CNC Number</label>
                    <input type="text" name="cnc_number" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                </div>
                <div style="grid-column: 1 / -1;">
                    <label style="display: block; margin-bottom: 5px; font-weight: bold; color: #333;">Description</label>
                    <textarea name="description" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; height: 80px;"></textarea>
                </div>
                <div style="grid-column: 1 / -1;">
                    <label style="display: block; margin-bottom: 5px; font-weight: bold; color: #333;">Action Solution</label>
                    <textarea name="action_solution" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; height: 80px;"></textarea>
                </div>
            </div>
            <div style="margin-top: 20px; text-align: right; border-top: 1px solid #eee; padding-top: 15px;">
                <button type="button" onclick="closeVanillaModal()" style="padding: 8px 16px; margin-right: 10px; border: 1px solid #ddd; background: #f8f9fa; border-radius: 4px; cursor: pointer;">Close</button>
                <button type="submit" name="create" value="1" style="padding: 8px 16px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer;">Create</button>
            </div>
        </form>
    `;
    
    modalContainer.appendChild(modalContent);
    document.body.appendChild(modalContainer);
    
    console.log('Vanilla modal created and shown');
    
    // Add click outside to close functionality
    modalContainer.addEventListener('click', function(e) {
        if (e.target === modalContainer) {
            closeVanillaModal();
        }
    });
}

function closeVanillaModal() {
    const modal = document.getElementById('vanillaCreateModal');
    if (modal) {
        modal.remove();
        console.log('Vanilla modal closed');
    } else {
        console.log('No vanilla modal found to close');
    }
}

function hideCreateForm() {
    const form = document.getElementById('createActivityForm');
    if (form) {
        form.style.display = 'none';
        console.log('Create form hidden');
    } else {
        console.log('No create form found to hide');
    }
}

// Row click -> open edit modal and populate fields
document.querySelectorAll('.activity-row').forEach(function(row) {
    row.addEventListener('click', function() {
        const id = row.dataset.id || '';
        const noVal = row.dataset.no || '';
        const userPosition = row.dataset.userPosition || '';
        const department = row.dataset.department || '';
        const application = row.dataset.application || '';
        const type = row.dataset.type || '';
        const description = row.dataset.description || '';
        const actionSolution = row.dataset.actionSolution || '';
        const status = row.dataset.status || '';
        const priority = row.dataset.priority || 'Normal';
        const infoDate = row.dataset.informationDate || '';

        // Build a lightweight modal dynamically if not exists
        let modalEl = document.getElementById('editActivityModal');
        if (!modalEl) {
            modalEl = document.createElement('div');
            modalEl.id = 'editActivityModal';
            modalEl.className = 'modal fade';
            modalEl.tabIndex = -1;
            modalEl.innerHTML = `
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title">Edit Activity</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="post" id="editActivityForm">
                  <div class="modal-body">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" id="edit_id">
                    <div class="row g-3">
                      <div class="col-md-6">
                        <label class="form-label">No</label>
                        <input type="number" name="no" id="edit_no" class="form-control">
                      </div>
                      <div class="col-md-6">
                        <label class="form-label">Status *</label>
                        <select name="status" id="edit_status" class="form-select" required>
                          <option value="Open">Open</option>
                          <option value="On Progress">On Progress</option>
                          <option value="Need Requirement">Need Requirement</option>
                          <option value="Done">Done</option>
                        </select>
                      </div>
                      <div class="col-md-6">
                        <label class="form-label">Information Date *</label>
                        <input type="date" name="information_date" id="edit_information_date" class="form-control" required>
                      </div>
                      <div class="col-md-6">
                        <label class="form-label">Priority *</label>
                        <select name="priority" id="edit_priority" class="form-select" required>
                          <option value="Urgent">Urgent</option>
                          <option value="Normal">Normal</option>
                          <option value="Low">Low</option>
                        </select>
                      </div>

                      <div class="col-md-6">
                        <label class="form-label">User Position</label>
                        <input type="text" name="user_position" id="edit_user_position" class="form-control">
                      </div>
                      <div class="col-md-6">
                        <label class="form-label">Department</label>
                        <select name="department" id="edit_department" class="form-select">
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
                        <select name="application" id="edit_application" class="form-select" required>
                          <option value="">-</option>
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
                        <label class="form-label">Type</label>
                        <select name="type" id="edit_type" class="form-select">
                          <option value="Setup">Setup</option>
                          <option value="Question">Question</option>
                          <option value="Issue">Issue</option>
                          <option value="Report Issue">Report Issue</option>
                          <option value="Report Request">Report Request</option>
                          <option value="Feature Request">Feature Request</option>
                        </select>
                      </div>
                      <div class="col-md-6">
                        <label class="form-label">Customer</label>
                        <input type="text" name="customer" id="edit_customer" class="form-control">
                      </div>
                      <div class="col-md-6">
                        <label class="form-label">Project</label>
                        <input type="text" name="project" id="edit_project_name" class="form-control">
                      </div>
                      <div class="col-md-6">
                        <label class="form-label">Completed Date</label>
                        <input type="date" name="due_date" id="edit_due_date" class="form-control">
                      </div>
                      <div class="col-md-6">
                        <label class="form-label">CNC Number</label>
                        <input type="text" name="cnc_number" id="edit_cnc_number" class="form-control">
                      </div>
                      <div class="col-md-6">
                        <label class="form-label">Description</label>
                        <textarea name="description" id="edit_description" class="form-control" rows="3"></textarea>
                      </div>
                      <div class="col-md-6">
                        <label class="form-label">Action Solution</label>
                        <textarea name="action_solution" id="edit_action_solution" class="form-control" rows="3"></textarea>
                      </div>
                    </div>
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="delete" value="1" class="btn btn-danger" onclick="return confirm('Delete this activity?')">Delete</button>
                    <button type="submit" name="update" value="1" class="btn btn-primary">Update</button>
                  </div>
                </form>
              </div>
            </div>`;
            document.body.appendChild(modalEl);
        }

        document.getElementById('edit_id').value = id;
        const noInput = document.getElementById('edit_no');
        if (noInput) noInput.value = noVal;
        document.getElementById('edit_user_position').value = userPosition;
        document.getElementById('edit_department').value = department;
        document.getElementById('edit_application').value = application;
        document.getElementById('edit_type').value = type;
        document.getElementById('edit_status').value = status;
        document.getElementById('edit_information_date').value = infoDate ? infoDate.substring(0,10) : '';
        document.getElementById('edit_description').value = description;
        document.getElementById('edit_action_solution').value = actionSolution;
        document.getElementById('edit_priority').value = priority;

        const modal = new bootstrap.Modal(modalEl);
        modal.show();
    });
});

// Simple event listener to ensure Create Activity button works
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, checking Create Activity button...');
    
    const createBtn = document.getElementById('createActivityBtn');
    if (createBtn) {
        console.log('Create Activity button found and ready');
        
        // Test if Bootstrap is available
        if (typeof bootstrap !== 'undefined' && typeof bootstrap.Modal !== 'undefined') {
            console.log('Bootstrap Modal is available - button should work');
        } else {
            console.error('Bootstrap Modal is NOT available!');
        }
    } else {
        console.error('Create Activity button not found!');
    }
});

function deleteActivity(activityId) {
    if (confirm('Are you sure you want to delete this activity?')) {
        const form = document.createElement('form');
        form.method = 'post';
        form.innerHTML = `
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="${activityId}">
            <input type="hidden" name="delete" value="1">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<!-- Activity Table Enhancement Script -->
<script src="assets/js/activity-table.js"></script>

<?php include './partials/layouts/layoutBottom.php'; ?>
