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
$message_type = '';

// Create Activity
if (isset($_POST['create'])) {
    if (!csrf_verify()) {
        $message = 'CSRF token tidak valid!';
        $message_type = 'error';
        
        // Trigger notifikasi kapsul untuk error
        echo "<script>
            if (window.logoNotificationManager) {
                window.logoNotificationManager.showActivityError('CSRF token tidak valid!', 5000);
            }
        </script>";
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
        $message_type = 'success';
        log_activity('create_activity', 'Activity: ' . $_POST['type']);
        
        // Trigger notifikasi kapsul
        echo "<script>
            if (window.logoNotificationManager) {
                window.logoNotificationManager.showActivityCreated('Activity berhasil dibuat!', 5000);
            }
        </script>";
    }
}

// Update Activity
if (isset($_POST['update'])) {
    if (!csrf_verify()) {
        $message = 'CSRF token tidak valid!';
        $message_type = 'error';
        
        // Trigger notifikasi kapsul untuk error
        echo "<script>
            if (window.logoNotificationManager) {
                window.logoNotificationManager.showActivityError('CSRF token tidak valid!', 5000);
            }
        </script>";
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
        
        // Deteksi perubahan status untuk notifikasi yang sesuai
        $newStatus = $_POST['status'];
        if ($newStatus === 'Cancel') {
            $message = 'Activity canceled!';
            $message_type = 'warning';
            log_activity('cancel_activity', 'Activity ID: ' . $_POST['id'] . ' - Status changed to Cancel');
            
            // Trigger notifikasi kapsul untuk cancel
            echo "<script>
                if (window.logoNotificationManager) {
                    window.logoNotificationManager.showActivityCanceled('Activity berhasil dibatalkan!', 5000);
                }
            </script>";
        } else {
            $message = 'Activity updated!';
            $message_type = 'info';
            log_activity('update_activity', 'Activity ID: ' . $_POST['id']);
            
            // Trigger notifikasi kapsul untuk update biasa
            echo "<script>
                if (window.logoNotificationManager) {
                    window.logoNotificationManager.showActivityUpdated('Activity berhasil diperbarui!', 5000);
                }
            </script>";
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
                    <button type="button" class="btn btn-sm btn-primary-600 d-flex align-items-center gap-2" id="createActivityBtn" onclick="showCreateModal()">
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
                                    <option value="Cancel" <?= $filter_status === 'Cancel' ? 'selected' : '' ?>>Cancel</option>
                                </select>
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn-apply">Apply Filters</button>
                            <a href="activity.php" class="btn-reset">Reset</a>
                        </div>
                    </form>
                </div>
                
                <!-- Create Activity Modal - Custom Modal -->
                <div class="custom-modal-overlay" id="createActivityModal" style="display: none;">
                    <div class="custom-modal">
                        <div class="custom-modal-header">
                            <h5 class="custom-modal-title">Add Activity</h5>
                            <button type="button" class="custom-modal-close" onclick="closeCreateModal()">&times;</button>
                        </div>
                        <form method="post">
                            <div class="custom-modal-body">
                                <?= csrf_field() ?>
                                <div class="custom-modal-row">
                                    <div class="custom-modal-col">
                                        <label class="custom-modal-label">No</label>
                                        <input type="number" name="no" class="custom-modal-input" value="<?= (int)$next_no ?>">
                                    </div>
                                    <div class="custom-modal-col">
                                        <label class="custom-modal-label">Status *</label>
                                        <select name="status" class="custom-modal-select" required>
                                            <option value="Open">Open</option>
                                            <option value="On Progress">On Progress</option>
                                            <option value="Need Requirement">Need Requirement</option>
                                            <option value="Done">Done</option>
                                            <option value="Cancel">Cancel</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="custom-modal-row">
                                    <div class="custom-modal-col">
                                        <label class="custom-modal-label">Information Date *</label>
                                        <input type="date" name="information_date" class="custom-modal-input" value="<?= date('Y-m-d') ?>" required>
                                    </div>
                                    <div class="custom-modal-col">
                                        <label class="custom-modal-label">Priority *</label>
                                        <select name="priority" class="custom-modal-select" required>
                                            <option value="Urgent">Urgent</option>
                                            <option value="Normal" selected>Normal</option>
                                            <option value="Low">Low</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="custom-modal-row">
                                    <div class="custom-modal-col">
                                        <label class="custom-modal-label">User Position</label>
                                        <input type="text" name="user_position" class="custom-modal-input">
                                    </div>
                                    <div class="custom-modal-col">
                                        <label class="custom-modal-label">Department</label>
                                        <select name="department" class="custom-modal-select">
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
                                </div>
                                <div class="custom-modal-row">
                                    <div class="custom-modal-col">
                                        <label class="custom-modal-label">Application *</label>
                                        <select name="application" class="custom-modal-select" required>
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
                                    <div class="custom-modal-col">
                                        <label class="custom-modal-label">Type</label>
                                        <select name="type" class="custom-modal-select">
                                            <option value="Setup">Setup</option>
                                            <option value="Question">Question</option>
                                            <option value="Issue">Issue</option>
                                            <option value="Report Issue">Report Issue</option>
                                            <option value="Report Request">Report Request</option>
                                            <option value="Feature Request">Feature Request</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="custom-modal-row">
                                    <div class="custom-modal-col">
                                        <label class="custom-modal-label">Customer</label>
                                        <input type="text" name="customer" class="custom-modal-input">
                                    </div>
                                    <div class="custom-modal-col">
                                        <label class="custom-modal-label">Project</label>
                                        <input type="text" name="project" class="custom-modal-input">
                                    </div>
                                </div>
                                <div class="custom-modal-row">
                                    <div class="custom-modal-col">
                                        <label class="custom-modal-label">Completed Date</label>
                                        <input type="date" name="due_date" class="custom-modal-input">
                                    </div>
                                    <div class="custom-modal-col">
                                        <label class="custom-modal-label">CNC Number</label>
                                        <input type="text" name="cnc_number" class="custom-modal-input">
                                    </div>
                                </div>
                                <div class="custom-modal-row">
                                    <div class="custom-modal-col">
                                        <label class="custom-modal-label">Description</label>
                                        <textarea name="description" class="custom-modal-textarea" rows="3"></textarea>
                                    </div>
                                    <div class="custom-modal-col">
                                        <label class="custom-modal-label">Action Solution</label>
                                        <textarea name="action_solution" class="custom-modal-textarea" rows="3"></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="custom-modal-footer">
                                <button type="submit" name="create" value="1" class="custom-btn custom-btn-primary">Create</button>
                                <button type="button" class="custom-btn custom-btn-secondary" onclick="closeCreateModal()">Close</button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Edit Activity Modal - Custom Modal -->
                <div class="custom-modal-overlay" id="editActivityModal">
                    <div class="custom-modal">
                        <div class="custom-modal-header">
                            <h5 class="custom-modal-title">Edit Activity</h5>
                            <button type="button" class="custom-modal-close" onclick="closeEditModal()">&times;</button>
                        </div>
                        <form method="post">
                            <div class="custom-modal-body">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id" id="edit_id">
                                <div class="custom-modal-row">
                                    <div class="custom-modal-col">
                                        <label class="custom-modal-label">No</label>
                                        <input type="number" name="no" id="edit_no" class="custom-modal-input" required>
                                    </div>
                                    <div class="custom-modal-col">
                                        <label class="custom-modal-label">Status *</label>
                                        <select name="status" id="edit_status" class="custom-modal-select" required>
                                            <option value="Open">Open</option>
                                            <option value="On Progress">On Progress</option>
                                            <option value="Need Requirement">Need Requirement</option>
                                            <option value="Done">Done</option>
                                            <option value="Cancel">Cancel</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="custom-modal-row">
                                    <div class="custom-modal-col">
                                        <label class="custom-modal-label">Information Date *</label>
                                        <input type="date" name="information_date" id="edit_information_date" class="custom-modal-input" required>
                                    </div>
                                    <div class="custom-modal-col">
                                        <label class="custom-modal-label">Priority *</label>
                                        <select name="priority" id="edit_priority" class="custom-modal-select" required>
                                            <option value="Urgent">Urgent</option>
                                            <option value="Normal">Normal</option>
                                            <option value="Low">Low</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="custom-modal-row">
                                    <div class="custom-modal-col">
                                        <label class="custom-modal-label">User Position</label>
                                        <input type="text" name="user_position" id="edit_user_position" class="custom-modal-input">
                                    </div>
                                    <div class="custom-modal-col">
                                        <label class="custom-modal-label">Department</label>
                                        <select name="department" id="edit_department" class="custom-modal-select">
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
                                </div>
                                <div class="custom-modal-row">
                                    <div class="custom-modal-col">
                                        <label class="custom-modal-label">Application *</label>
                                        <select name="application" id="edit_application" class="custom-modal-select" required>
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
                                    <div class="custom-modal-col">
                                        <label class="custom-modal-label">Type</label>
                                        <select name="type" id="edit_type" class="custom-modal-select">
                                            <option value="Setup">Setup</option>
                                            <option value="Question">Question</option>
                                            <option value="Issue">Issue</option>
                                            <option value="Report Issue">Report Issue</option>
                                            <option value="Report Request">Report Request</option>
                                            <option value="Feature Request">Feature Request</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="custom-modal-row">
                                    <div class="custom-modal-col">
                                        <label class="custom-modal-label">Customer</label>
                                        <input type="text" name="customer" id="edit_customer" class="custom-modal-input">
                                    </div>
                                    <div class="custom-modal-col">
                                        <label class="custom-modal-label">Project</label>
                                        <input type="text" name="project" id="edit_project" class="custom-modal-input">
                                    </div>
                                </div>
                                <div class="custom-modal-row">
                                    <div class="custom-modal-col">
                                        <label class="custom-modal-label">Completed Date</label>
                                        <input type="date" name="due_date" id="edit_due_date" class="custom-modal-input">
                                    </div>
                                    <div class="custom-modal-col">
                                        <label class="custom-modal-label">CNC Number</label>
                                        <input type="text" name="cnc_number" id="edit_cnc_number" class="custom-modal-input">
                                    </div>
                                </div>
                                <div class="custom-modal-row">
                                    <div class="custom-modal-col">
                                        <label class="custom-modal-label">Description</label>
                                        <textarea name="description" id="edit_description" class="custom-modal-textarea" rows="3"></textarea>
                                    </div>
                                    <div class="custom-modal-col">
                                        <label class="custom-modal-label">Action Solution</label>
                                        <textarea name="action_solution" id="edit_action_solution" class="custom-modal-textarea" rows="3"></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="custom-modal-footer">
                                <button type="submit" name="update" value="1" class="custom-btn custom-btn-primary">Update</button>
                                <button type="button" class="custom-btn custom-btn-secondary" onclick="closeEditModal()">Close</button>
                            </div>
                        </form>
                    </div>
                </div>
                

                
                <div class="card-body">
                    <?php if ($message): ?>
                        <?= $message ?>
                    <?php endif; ?>
                    
                    <?php if ($default_status_filter === 'not_done' && !$filter_status): ?>
                        <div class="alert alert-info d-flex align-items-center gap-2 mb-3">
                            <iconify-icon icon="solar:info-circle-outline" class="icon text-lg"></iconify-icon>
                            <span>Default menampilkan aktivitas dengan status yang Active (belum selesai).</span>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Activity Table -->
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th class="table-header">No</th>
                                    <th class="table-header">Information Date</th>
                                    <th class="table-header">Priority</th>
                                    <th class="table-header">User Position</th>
                                    <th class="table-header">Department</th>
                                    <th class="table-header">Application</th>
                                    <th class="table-header">Type</th>
                                    <th class="table-header">Description</th>
                                    <th class="table-header">Action/Solution</th>
                                    <th class="table-header">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($activities)): ?>
                                    <tr>
                                        <td colspan="10" class="text-center py-4">
                                            <div class="d-flex flex-column align-items-center gap-2">
                                                <iconify-icon icon="solar:clipboard-remove-outline" class="icon text-3xl text-muted"></iconify-icon>
                                                <span class="text-muted">Tidak ada aktivitas yang ditemukan</span>
                                            </div>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($activities as $a): ?>
                                        <tr class="activity-row" data-id="<?= $a['id'] ?>">
                                            <td><?= htmlspecialchars($a['no']) ?></td>
                                            <td><?= htmlspecialchars($a['information_date']) ?></td>
                                            <td>
                                                <span class="badge badge-<?= $a['priority'] === 'Urgent' ? 'danger' : ($a['priority'] === 'Normal' ? 'warning' : 'info') ?>">
                                                    <?= htmlspecialchars($a['priority']) ?>
                                                </span>
                                            </td>
                                            <td><?= htmlspecialchars($a['user_position']) ?></td>
                                            <td><?= htmlspecialchars($a['department']) ?></td>
                                            <td><?= htmlspecialchars($a['application']) ?></td>
                                            <td><?= htmlspecialchars($a['type']) ?></td>
                                            <td><?= htmlspecialchars($a['description']) ?></td>
                                            <td><?= htmlspecialchars($a['action_solution']) ?></td>
                                            <td>
                                                <span class="badge badge-<?= $a['status'] === 'Done' ? 'success' : ($a['status'] === 'On Progress' ? 'warning' : ($a['status'] === 'Need Requirement' ? 'info' : ($a['status'] === 'Cancel' ? 'danger' : 'primary'))) ?>">
                                                    <?= htmlspecialchars($a['status']) ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <div class="d-flex justify-content-center mt-4">
                            <nav aria-label="Activity pagination">
                                <ul class="pagination">
                                    <?php if ($page > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">
                                                <iconify-icon icon="solar:arrow-left-outline" class="icon"></iconify-icon>
                                                Previous
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                    
                                    <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                        <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
                                        </li>
                                    <?php endfor; ?>
                                    
                                    <?php if ($page < $total_pages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">
                                                Next
                                                <iconify-icon icon="solar:arrow-right-outline" class="icon"></iconify-icon>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        </div>
                    <?php endif; ?>
                    
                    <style>
                        .activity-row { cursor: pointer; }
                        .activity-row:hover { background-color: rgba(102,126,234,0.08); }
                        
                        /* Custom Modal Styles */
                        .custom-modal-overlay {
                            position: fixed;
                            top: 0;
                            left: 0;
                            width: 100%;
                            height: 100%;
                            background-color: rgba(0, 0, 0, 0.5);
                            display: none;
                            justify-content: center;
                            align-items: center;
                            z-index: 9999;
                            visibility: hidden;
                            opacity: 0;
                            transition: all 0.3s ease;
                        }
                        
                        .custom-modal-overlay.show {
                            display: flex !important;
                            visibility: visible !important;
                            opacity: 1 !important;
                        }
                        
                        .custom-modal {
                            background: white;
                            border-radius: 8px;
                            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
                            width: 90%;
                            max-width: 800px;
                            max-height: 90vh;
                            overflow-y: auto;
                            position: relative;
                        }
                        
                        .custom-modal-header {
                            padding: 20px 24px;
                            border-bottom: 1px solid #dee2e6;
                            background: #f8f9fa;
                            border-radius: 8px 8px 0 0;
                            display: flex;
                            justify-content: space-between;
                            align-items: center;
                        }
                        
                        .custom-modal-title {
                            margin: 0;
                            font-size: 18px;
                            font-weight: 600;
                            color: #333;
                        }
                        
                        /* Modern header styling without sorting */
                        .table-header {
                            padding: 10px 16px;
                            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                            border: none;
                            border-radius: 8px;
                            margin: 0;
                            font-weight: 700;
                            color: white;
                            font-size: 12px;
                            text-transform: uppercase;
                            letter-spacing: 0.5px;
                            text-align: left;
                            box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
                            transition: all 0.3s ease;
                            position: relative;
                            overflow: hidden;
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
                        

                        

                        

                        

                        

                        

                        

                        

                        

                        

                        

                        

                        


                        

                        

                        

                        

                        
                        .table-responsive {
                            overflow-x: auto;
                            overflow-y: hidden;
                            border-radius: 2px;
                            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
                        }
                        
                        /* Modern header styling for all columns */
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
                        
                        /* Column widths - optimized for content */
                        .table th:nth-child(1) {
                            width: 70px;
                            min-width: 70px;
                            max-width: 70px;
                        }
                        
                        .table th:nth-child(2) {
                            width: 140px;
                            min-width: 140px;
                            max-width: 140px;
                        }
                        
                        .table th:nth-child(3) {
                            width: 100px;
                            min-width: 100px;
                            max-width: 100px;
                        }
                        
                        .table th:nth-child(4) {
                            width: 180px;
                            min-width: 180px;
                            max-width: 180px;
                        }
                        
                        .table th:nth-child(5) {
                            width: 150px;
                            min-width: 150px;
                            max-width: 150px;
                        }
                        
                        .table th:nth-child(6) {
                            width: 120px;
                            min-width: 120px;
                            max-width: 120px;
                        }
                        
                        .table th:nth-child(7) {
                            width: 100px;
                            min-width: 100px;
                            max-width: 100px;
                        }
                        
                        .table th:nth-child(8) {
                            width: 200px;
                            min-width: 200px;
                            max-width: 200px;
                        }
                        
                        .table th:nth-child(9) {
                            width: 180px;
                            min-width: 180px;
                            max-width: 180px;
                        }
                        
                        .table th:nth-child(10) {
                            width: 120px;
                            min-width: 120px;
                            max-width: 120px;
                        }
                        
                        /* Center align data in specific columns */
                        .table td:nth-child(2) {
                            text-align: center;
                        }
                        
                        .table td:nth-child(3) {
                            text-align: center;
                        }
                        
                        .table td:nth-child(4) {
                            text-align: left;
                        }
                        
                        .table td:nth-child(5) {
                            text-align: left;
                        }
                        
                        .table td:nth-child(6) {
                            text-align: center;
                        }
                        
                        .table td:nth-child(7) {
                            text-align: center;
                        }
                        
                        .table td:nth-child(10) {
                            text-align: center;
                        }
                        
                        /* Clean and simple vertical alignment */
                        .table.table-striped td {
                            padding: 12px 8px;
                        }
                        
                        /* No column - center aligned */
                        .table.table-striped td:nth-child(1) {
                            text-align: center;
                            vertical-align: middle !important;
                        }
                        
                        /* All other columns - same alignment as No column */
                        .table.table-striped td:nth-child(2),
                        .table.table-striped td:nth-child(3),
                        .table.table-striped td:nth-child(4),
                        .table.table-striped td:nth-child(5),
                        .table.table-striped td:nth-child(6),
                        .table.table-striped td:nth-child(7),
                        .table.table-striped td:nth-child(8),
                        .table.table-striped td:nth-child(9),
                        .table.table-striped td:nth-child(10) {
                            vertical-align: middle !important;
                        }
                        
                        /* Text alignment for specific columns */
                        .table.table-striped td:nth-child(2),
                        .table.table-striped td:nth-child(3),
                        .table.table-striped td:nth-child(6),
                        .table.table-striped td:nth-child(7),
                        .table.table-striped td:nth-child(10) {
                            text-align: center;
                        }
                        
                        .table.table-striped td:nth-child(4),
                        .table.table-striped td:nth-child(5),
                        .table.table-striped td:nth-child(8),
                        .table.table-striped td:nth-child(9) {
                            text-align: left;
                        }
                        
                        /* Modal footer spacing improvements - safer selectors */
                        #createActivityModal .modal-footer,
                        .modal-footer,
                        #editActivityModal .modal-footer {
                            padding: 20px 24px !important;
                            margin-top: 20px !important;
                            border-top: 1px solid #dee2e6 !important;
                            background-color: #f8f9fa !important;
                            display: block !important;
                            visibility: visible !important;
                        }
                        
                        #createActivityModal .modal-footer .btn,
                        .modal-footer .btn,
                        #editActivityModal .modal-footer .btn {
                            margin: 0 5px !important;
                            padding: 10px 20px !important;
                            font-weight: 500 !important;
                            display: inline-block !important;
                            visibility: visible !important;
                            opacity: 1 !important;
                            position: relative !important;
                            z-index: 1 !important;
                        }
                        
                        #createActivityModal .modal-footer .btn:first-child,
                        .modal-footer .btn:first-child,
                        #editActivityModal .modal-footer .btn:first-child {
                            margin-left: 0 !important;
                        }
                        
                        #createActivityModal .modal-footer .btn:last-child,
                        .modal-footer .btn:last-child,
                        #editActivityModal .modal-footer .btn:last-child {
                            margin-right: 0 !important;
                        }
                        
                        /* Modal body bottom spacing */
                        #createActivityModal .modal-body,
                        .modal-body,
                        #editActivityModal .modal-body {
                            padding-bottom: 30px !important;
                        }
                        
                        /* Force show modal footer for dynamically created modals */
                        .modal-content .modal-footer {
                            display: block !important;
                            visibility: visible !important;
                            opacity: 1 !important;
                            position: relative !important;
                            z-index: 10 !important;
                        }
                        
                        /* Ensure buttons are visible in all modals */
                        .modal-content .modal-footer .btn {
                            display: inline-block !important;
                            visibility: visible !important;
                            opacity: 1 !important;
                            position: relative !important;
                            z-index: 11 !important;
                        }
                        
                        /* Nuclear option - force everything visible */
                        .modal-footer,
                        .modal-footer *,
                        .modal-footer button,
                        .modal-footer .btn {
                            display: block !important;
                            visibility: visible !important;
                            opacity: 1 !important;
                            position: static !important;
                            clip: auto !important;
                            overflow: visible !important;
                            height: auto !important;
                            width: auto !important;
                            max-height: none !important;
                            max-width: none !important;
                            min-height: auto !important;
                            min-width: auto !important;
                        }
                        
                        /* Specific button styling */
                        .modal-footer button.btn,
                        .modal-footer .btn {
                            display: inline-block !important;
                            margin: 5px !important;
                            padding: 10px 20px !important;
                            border: 1px solid #ccc !important;
                            background-color: #007bff !important;
                            color: white !important;
                            text-decoration: none !important;
                            border-radius: 4px !important;
                            cursor: pointer !important;
                            font-size: 14px !important;
                            line-height: 1.5 !important;
                        }
                        
                        .modal-footer .btn.btn-danger {
                            background-color: #dc3545 !important;
                        }
                        
                        .modal-footer .btn.btn-secondary {
                            background-color: #6c757d !important;
                        }
                        
                        /* Custom Modal Styles - Completely independent of Bootstrap */
                        /* Note: Using the first definition from above, removing duplicate */
                        
                        .custom-modal-header {
                            padding: 20px 24px;
                            border-bottom: 1px solid #dee2e6;
                            background: #f8f9fa;
                            border-radius: 8px 8px 0 0;
                            display: flex;
                            justify-content: space-between;
                            align-items: center;
                        }
                        
                        .custom-modal-title {
                            margin: 0;
                            font-size: 18px;
                            font-weight: 600;
                        }
                        
                        /* Enhanced Alert Styling */
                        .alert-animated {
                            border: none !important;
                            border-radius: 12px !important;
                            padding: 16px 20px !important;
                            margin-bottom: 20px !important;
                            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1) !important;
                            transition: all 0.3s ease !important;
                            animation: slideInDown 0.5s ease-out !important;
                        }
                        
                        .alert-animated:hover {
                            transform: translateY(-2px) !important;
                            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15) !important;
                        }
                        
                        .alert-content {
                            display: flex !important;
                            align-items: center !important;
                            gap: 12px !important;
                        }
                        
                        .alert-icon {
                            font-size: 20px !important;
                            flex-shrink: 0 !important;
                        }
                        
                        .alert-message {
                            font-weight: 500 !important;
                            font-size: 14px !important;
                        }
                        
                        /* Success Alert */
                        .alert-success {
                            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%) !important;
                            color: #155724 !important;
                            border-left: 4px solid #28a745 !important;
                        }
                        
                        .alert-success .alert-icon {
                            color: #28a745 !important;
                        }
                        
                        /* Info Alert */
                        .alert-info {
                            background: linear-gradient(135deg, #d1ecf1 0%, #bee5eb 100%) !important;
                            color: #0c5460 !important;
                            border-left: 4px solid #17a2b8 !important;
                        }
                        
                        .alert-info .alert-icon {
                            color: #17a2b8 !important;
                        }
                        
                        /* Warning Alert */
                        .alert-warning {
                            background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%) !important;
                            color: #856404 !important;
                            border-left: 4px solid #ffc107 !important;
                        }
                        
                        .alert-warning .alert-icon {
                            color: #ffc107 !important;
                        }
                        
                        /* Animation Keyframes */
                        @keyframes slideInDown {
                            from {
                                transform: translateY(-20px);
                                opacity: 0;
                            }
                            to {
                                transform: translateY(0);
                                opacity: 1;
                            }
                        }
                        
                        /* Auto-hide animation */
                        .alert-animated.fade-out {
                            animation: fadeOutUp 0.5s ease-in forwards !important;
                        }
                        
                        @keyframes fadeOutUp {
                            from {
                                transform: translateY(0);
                                opacity: 1;
                            }
                            to {
                                transform: translateY(-20px);
                                opacity: 0;
                            }
                        }
                        
                        /* Floating Toast Notification */
                        .floating-toast {
                            position: fixed !important;
                            top: 20px !important;
                            right: 20px !important;
                            z-index: 9999 !important;
                            min-width: 320px !important;
                            max-width: 400px !important;
                            padding: 16px 20px !important;
                            border-radius: 12px !important;
                            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.12) !important;
                            backdrop-filter: blur(10px) !important;
                            border: 1px solid rgba(255, 255, 255, 0.2) !important;
                            animation: slideInRight 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55) !important;
                            transition: all 0.3s ease !important;
                            cursor: pointer !important;
                        }
                        
                        .floating-toast:hover {
                            transform: translateX(-5px) !important;
                            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.2) !important;
                        }
                        
                        .floating-toast.success {
                            background: linear-gradient(135deg, #10b981, #059669) !important;
                            color: white !important;
                        }
                        
                        .floating-toast.info {
                            background: linear-gradient(135deg, #3b82f6, #2563eb) !important;
                            color: white !important;
                        }
                        
                        .floating-toast.warning {
                            background: linear-gradient(135deg, #f59e0b, #d97706) !important;
                            color: white !important;
                        }
                        
                        .toast-content {
                            display: flex !important;
                            align-items: center !important;
                            gap: 12px !important;
                        }
                        
                        .toast-icon {
                            font-size: 20px !important;
                            flex-shrink: 0 !important;
                        }
                        
                        .toast-message {
                            font-weight: 500 !important;
                            font-size: 14px !important;
                            line-height: 1.4 !important;
                        }
                        
                        .toast-close {
                            margin-left: auto !important;
                            background: rgba(255, 255, 255, 0.2) !important;
                            border: none !important;
                            color: white !important;
                            width: 24px !important;
                            height: 24px !important;
                            border-radius: 50% !important;
                            cursor: pointer !important;
                            display: flex !important;
                            align-items: center !important;
                            justify-content: center !important;
                            font-size: 14px !important;
                            transition: all 0.2s ease !important;
                        }
                        
                        .toast-close:hover {
                            background: rgba(255, 255, 255, 0.3) !important;
                            transform: scale(1.1) !important;
                        }
                        
                        /* Toast Animations */
                        @keyframes slideInRight {
                            from {
                                transform: translateX(100%) !important;
                                opacity: 0 !important;
                            }
                            to {
                                transform: translateX(0) !important;
                                opacity: 1 !important;
                            }
                        }
                        
                        @keyframes slideOutRight {
                            from {
                                transform: translateX(0) !important;
                                opacity: 1 !important;
                            }
                            to {
                                transform: translateX(100%) !important;
                                opacity: 0 !important;
                            }
                        }
                        
                        @keyframes fadeOut {
                            from {
                                opacity: 1 !important;
                                transform: scale(1) !important;
                            }
                            to {
                                opacity: 0 !important;
                                transform: scale(0.8) !important;
                            }
                        }
                        
                        .toast-fade-out {
                            animation: fadeOut 0.3s ease forwards !important;
                        }
                        
                        /* Responsive adjustments */
                        @media (max-width: 768px) {
                            .floating-toast {
                                right: 10px !important;
                                left: 10px !important;
                                min-width: auto !important;
                                max-width: none !important;
                            }
                        }
                        
                        .custom-modal-close {
                            background: none;
                            border: none;
                            font-size: 24px;
                            cursor: pointer;
                            color: #666;
                            padding: 0;
                            width: 30px;
                            height: 30px;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                        }
                        
                        .custom-modal-close {
                            background: none;
                            border: none;
                            font-size: 24px;
                            cursor: pointer;
                            color: #666;
                            padding: 0;
                            width: 30px;
                            height: 30px;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                        }
                        
                        .custom-modal-close:hover {
                            color: #333;
                        }
                        
                        .custom-modal-body {
                            padding: 24px;
                        }
                        

                        
                        .custom-modal-row {
                            display: flex;
                            gap: 20px;
                            margin-bottom: 20px;
                        }
                        
                        .custom-modal-col {
                            flex: 1;
                        }
                        
                        .custom-modal-label {
                            display: block;
                            margin-bottom: 8px;
                            font-weight: 500;
                            color: #333;
                            font-size: 14px;
                        }
                        
                        .custom-modal-input,
                        .custom-modal-select,
                        .custom-modal-textarea {
                            width: 100%;
                            padding: 10px 12px;
                            border: 1px solid #ddd;
                            border-radius: 4px;
                            font-size: 14px;
                            background: white;
                        }
                        
                        .custom-modal-input:focus,
                        .custom-modal-select:focus,
                        .custom-modal-textarea:focus {
                            outline: none;
                            border-color: #007bff;
                            box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.25);
                        }
                        
                        .custom-modal-footer {
                            padding: 20px 24px;
                            border-top: 1px solid #dee2e6;
                            background: #f8f9fa;
                            border-radius: 0 0 8px 8px;
                            display: flex;
                            gap: 10px;
                            justify-content: flex-end;
                        }
                        
                        .custom-btn {
                            padding: 10px 20px;
                            border: none;
                            border-radius: 4px;
                            font-size: 14px;
                            font-weight: 500;
                            cursor: pointer;
                            transition: all 0.2s ease;
                        }
                        
                        .custom-btn-primary {
                            background-color: #007bff;
                            color: white;
                        }
                        
                        .custom-btn-primary:hover {
                            background-color: #0056b3;
                        }
                        
                        .custom-btn-danger {
                            background-color: #dc3545;
                            color: white;
                        }
                        
                        .custom-btn-danger:hover {
                            background-color: #c82333;
                        }
                        
                        .custom-btn-secondary {
                            background-color: #6c757d;
                            color: white;
                        }
                        
                        .custom-btn-secondary:hover {
                            background-color: #545b62;
                        }
                    </style>
                    
                    <!-- Force vertical alignment with JavaScript -->
                    <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        // Force vertical alignment for all table cells - same as No column
                        const tableCells = document.querySelectorAll('.table.table-striped tbody tr td');
                        tableCells.forEach(function(cell) {
                            cell.style.setProperty('vertical-align', 'middle', 'important');
                        });
                    });
                    </script>


                        switch (type) {
                            case 'success':
                                frontBgColor = 'bg-green-600';
                                frontColor = 'text-white';
                                backBgColor = 'bg-green-50';
                                backColor = 'text-green-800';
                                backBorderColor = 'border-green-200';
                                buttonBgColor = 'bg-green-200';
                                buttonHoverColor = 'hover:bg-green-300';
                                buttonTextColor = 'text-green-800';
                                iconFront = `<svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>`;
                                iconBack = `<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>`;
                                break;
                            case 'info':
                                frontBgColor = 'bg-blue-600';
                                frontColor = 'text-white';
                                backBgColor = 'bg-blue-50';
                                backColor = 'text-blue-800';
                                backBorderColor = 'border-blue-200';
                                buttonBgColor = 'bg-blue-200';
                                buttonHoverColor = 'hover:bg-blue-300';
                                buttonTextColor = 'text-blue-800';
                                iconFront = `<svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>`;
                                iconBack = `<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>`;
                                break;
                            case 'warning':
                                frontBgColor = 'bg-yellow-600';
                                frontColor = 'text-white';
                                backBgColor = 'bg-yellow-50';
                                backColor = 'text-yellow-800';
                                backBorderColor = 'border-yellow-200';
                                buttonBgColor = 'bg-yellow-200';
                                buttonHoverColor = 'hover:bg-yellow-300';
                                buttonTextColor = 'text-yellow-800';
                                iconFront = `<svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>`;
                                iconBack = `<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.732-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>`;
                                break;
                            case 'error':
                                frontBgColor = 'bg-red-600';
                                frontColor = 'text-white';
                                backBgColor = 'bg-red-50';
                                backColor = 'text-red-800';
                                backBorderColor = 'border-red-200';
                                buttonBgColor = 'bg-red-200';
                                buttonHoverColor = 'hover:bg-red-300';
                                buttonTextColor = 'text-red-800';
                                iconFront = `<svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>`;
                                iconBack = `<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>`;
                                break;
                            case 'created':
                                frontBgColor = 'bg-emerald-600';
                                frontColor = 'text-white';
                                backBgColor = 'bg-emerald-50';
                                backColor = 'text-emerald-800';
                                backBorderColor = 'border-emerald-200';
                                buttonBgColor = 'bg-emerald-200';
                                buttonHoverColor = 'hover:bg-emerald-300';
                                buttonTextColor = 'text-emerald-800';
                                iconFront = `<svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>`;
                                iconBack = `<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>`;
                                break;
                            case 'updated':
                                frontBgColor = 'bg-indigo-600';
                                frontColor = 'text-white';
                                backBgColor = 'bg-indigo-50';
                                backColor = 'text-indigo-800';
                                backBorderColor = 'border-indigo-200';
                                buttonBgColor = 'bg-indigo-200';
                                buttonHoverColor = 'hover:bg-indigo-300';
                                buttonTextColor = 'text-indigo-800';
                                iconFront = `<svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>`;
                                iconBack = `<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>`;
                                break;
                            case 'deleted':
                                frontBgColor = 'bg-rose-600';
                                frontColor = 'text-white';
                                backBgColor = 'bg-rose-50';
                                backColor = 'text-rose-800';
                                backBorderColor = 'border-rose-200';
                                buttonBgColor = 'bg-rose-200';
                                buttonHoverColor = 'hover:bg-rose-300';
                                buttonTextColor = 'text-rose-800';
                                iconFront = `<svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>`;
                                iconBack = `<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m0-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>`;
                                break;
                            default:
                                frontBgColor = 'bg-slate-600';
                                frontColor = 'text-white';
                                backBgColor = 'bg-slate-50';
                                backColor = 'text-slate-800';
                                backBorderColor = 'border-slate-200';
                                buttonBgColor = 'bg-slate-200';
                                buttonHoverColor = 'hover:bg-slate-300';
                                buttonTextColor = 'text-slate-800';
                                iconFront = `<svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>`;
                                iconBack = `<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>`;
                        }

                        // Set flip card content
                        container.innerHTML = `
                            <div class="flip-card" style="width: 120px; height: 120px; position: relative;">
                                <div class="flip-card-inner" style="position: absolute; width: 100%; height: 100%; transition: transform 0.6s; transform-style: preserve-3d;">
                                    <!-- SISI DEPAN KARTU -->
                                    <div class="flip-card-front ${frontBgColor} ${frontColor} p-4 flex flex-col items-center justify-center text-center" style="position: absolute; width: 100%; height: 100%; -webkit-backface-visibility: hidden; backface-visibility: hidden; border-radius: 9999px; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);">
                                        ${iconFront}
                                        <p class="text-xs opacity-80 mt-1">Hover</p>
                                    </div>
                                    <!-- SISI BELAKANG KARTU -->
                                    <div class="flip-card-back ${backBgColor} ${backBorderColor} border-2 p-3 flex flex-col justify-center items-center text-center" style="position: absolute; width: 100%; height: 100%; -webkit-backface-visibility: hidden; backface-visibility: hidden; border-radius: 9999px; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); transform: rotateY(180deg);">
                                        <div class="mb-1">${iconBack}</div>
                                        <h4 class="font-bold ${backColor} text-xs">${type.charAt(0).toUpperCase() + type.slice(1)}</h4>
                                        <p class="text-xs ${backColor} mb-2 leading-tight">${message}</p>
                                        <button class="w-full ${buttonBgColor} ${buttonTextColor} text-xs py-1 rounded-md ${buttonHoverColor} transition-colors">Tutup</button>
                                    </div>
                                </div>
                            </div>
                        `;

                        // Add hover effect for flip
                        const flipCard = container.querySelector('.flip-card');
                        flipCard.addEventListener('mouseenter', function() {
                            this.querySelector('.flip-card-inner').style.transform = 'rotateY(180deg)';
                        });
                        flipCard.addEventListener('mouseleave', function() {
                            this.querySelector('.flip-card-inner').style.transform = 'rotateY(0deg)';
                        });

                        // Close button functionality
                        const closeButton = container.querySelector('button');
                        closeButton.onclick = () => {
                            container.style.animation = 'fade-out-anim 0.4s ease-in forwards';
                            setTimeout(() => container.remove(), 400);
                        };

                        // Add to container (no need for stack.prepend)
                        // Container is already added to document.body

                        // Auto-hide after 6 seconds
                        setTimeout(() => {
                            if (container.parentNode) {
                                container.style.animation = 'fade-out-anim 0.4s ease-in forwards';
                                setTimeout(() => {
                                    if (container.parentNode) {
                                        container.remove();
                                    }
                                }, 400);
                            }
                        }, 6000);
                    }

                    // Add CSS animations for flip cards
                    const style = document.createElement('style');
                    style.textContent = `
                        @keyframes emerge-from-logo {
                            from {
                                opacity: 0;
                                transform: translateY(-40px) scale(0.5);
                            }
                            to {
                                opacity: 1;
                                transform: translateY(0) scale(1);
                            }
                        }
                        
                        @keyframes fade-out-anim {
                            from { opacity: 1; transform: scale(1); }
                            to { opacity: 0; transform: scale(0.8); }
                        }
                        
                        /* Enhanced flip card styles */
                        .flip-card {
                            perspective: 1000px;
                            cursor: pointer;
                        }
                        
                        .flip-card-inner {
                            position: relative;
                            width: 100%;
                            height: 100%;
                            text-align: center;
                            transition: transform 0.6s;
                            transform-style: preserve-3d;
                        }
                        
                        .flip-card-front, .flip-card-back {
                            position: absolute;
                            width: 100%;
                            height: 100%;
                            -webkit-backface-visibility: hidden;
                            backface-visibility: hidden;
                            border-radius: 9999px;
                            display: flex;
                            flex-direction: column;
                            align-items: center;
                            justify-content: center;
                            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
                        }
                        
                        .flip-card-back {
                            transform: rotateY(180deg);
                        }
                        
                        /* Hover effects */
                        .flip-card:hover .flip-card-inner {
                            transform: rotateY(180deg);
                        }
                        
                        /* Responsive adjustments */
                        @media (max-width: 768px) {
                            .flip-card {
                                width: 100px !important;
                                height: 100px !important;
                            }
                        }
                    `;
                    document.head.appendChild(style);
                    
                    // Show notification when page loads if message exists
                    <?php if ($message): ?>
                    document.addEventListener('DOMContentLoaded', function() {
                        // Determine notification type and message
                        let notificationType, notificationMessage;
                        
                        switch ('<?= $message_type ?>') {
                            case 'success':
                                if (window.logoNotificationManager) {
                                    window.logoNotificationManager.showActivityCreated('<?= $message ?>', 5000);
                                }
                                break;
                            case 'info':
                                if (window.logoNotificationManager) {
                                    window.logoNotificationManager.showActivityUpdated('<?= $message ?>', 5000);
                                }
                                break;
                            case 'warning':
                                if (window.logoNotificationManager) {
                                    window.logoNotificationManager.showActivityCanceled('<?= $message ?>', 5000);
                                }
                                break;
                            case 'error':
                                if (window.logoNotificationManager) {
                                    window.logoNotificationManager.showActivityError('<?= $message ?>', 5000);
                                }
                                break;
                            default:
                                if (window.logoNotificationManager) {
                                    window.logoNotificationManager.showInfo('<?= $message ?>', 5000);
                                }
                        }
                    });
                    <?php endif; ?>
                    
                    // Enhanced Alert Management
                    document.addEventListener('DOMContentLoaded', function() {
                        // Auto-hide alerts after 5 seconds
                        const alerts = document.querySelectorAll('.alert-animated');
                        alerts.forEach(alert => {
                            setTimeout(() => {
                                alert.classList.add('fade-out');
                                setTimeout(() => {
                                    if (alert.parentNode) {
                                        alert.parentNode.removeChild(alert);
                                    }
                                }, 500);
                            }, 5000);
                            
                            // Add click to dismiss functionality
                            alert.addEventListener('click', function() {
                                this.classList.add('fade-out');
                                setTimeout(() => {
                                    if (this.parentNode) {
                                        this.parentNode.removeChild(this);
                                    }
                                }, 500);
                            });
                            
                            // Add hover effect indicator
                            alert.style.cursor = 'pointer';
                            alert.title = 'Click to dismiss';
                        });
                        
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
                    
                    function closeEditModal() {
                        const modal = document.getElementById('editActivityModal');
                        if (modal) {
                            modal.classList.remove('show');
                        }
                    }
                    
                    // Modal functions
                    function showCreateModal() {
                        const modal = document.getElementById('createActivityModal');
                        if (modal) {
                            modal.style.display = 'flex';
                            modal.style.visibility = 'visible';
                            modal.style.opacity = '1';
                        }
                    }
                    
                    function closeCreateModal() {
                        const modal = document.getElementById('createActivityModal');
                        if (modal) {
                            modal.style.display = 'none';
                            modal.style.visibility = 'hidden';
                            modal.style.opacity = '0';
                        }
                    }
                    
                    function showEditModal(id, no, information_date, priority, user_position, department, application, type, description, action_solution, status) {
                        console.log('Opening edit modal for activity:', { id, no, information_date, priority, user_position, department, application, type, description, action_solution, status });
                        
                        // Populate form fields
                        if (document.getElementById('edit_id')) document.getElementById('edit_id').value = id;
                        if (document.getElementById('edit_no')) document.getElementById('edit_no').value = no;
                        if (document.getElementById('edit_information_date')) document.getElementById('edit_information_date').value = information_date;
                        if (document.getElementById('edit_priority')) document.getElementById('edit_priority').value = priority;
                        if (document.getElementById('edit_user_position')) document.getElementById('edit_user_position').value = user_position;
                        if (document.getElementById('edit_department')) document.getElementById('edit_department').value = department;
                        if (document.getElementById('edit_application')) document.getElementById('edit_application').value = application;
                        if (document.getElementById('edit_type')) document.getElementById('edit_type').value = type;
                        if (document.getElementById('edit_description')) document.getElementById('edit_description').value = description;
                        if (document.getElementById('edit_action_solution')) document.getElementById('edit_action_solution').value = action_solution;
                        if (document.getElementById('edit_status')) document.getElementById('edit_status').value = status;
                        
                        // Show modal
                        const modal = document.getElementById('editActivityModal');
                        if (modal) {
                            modal.classList.add('show');
                            console.log('Modal displayed successfully');
                        } else {
                            console.error('Modal not found!');
                        }
                    }
                    
                    // Add click event listeners to activity rows
                    document.addEventListener('DOMContentLoaded', function() {
                        console.log('Setting up click event listeners for activity rows...');
                        
                        // Wait a bit for the page to fully load
                        setTimeout(() => {
                            const activityRows = document.querySelectorAll('.activity-row');
                            console.log('Found', activityRows.length, 'activity rows');
                            
                            if (activityRows.length === 0) {
                                console.error('No activity rows found!');
                                return;
                            }
                            
                            activityRows.forEach((row, index) => {
                                console.log('Adding click listener to row', index);
                                row.addEventListener('click', function(e) {
                                    console.log('Row clicked:', index);
                                    e.preventDefault();
                                    e.stopPropagation();
                                    
                                    // Get activity data from the row
                                    const cells = this.querySelectorAll('td');
                                    console.log('Found', cells.length, 'cells in row');
                                    
                                    if (cells.length < 10) {
                                        console.error('Row does not have enough cells:', cells.length);
                                        return;
                                    }
                                    
                                    const id = this.getAttribute('data-id') || cells[0].textContent.trim();
                                    const no = cells[0].textContent.trim();
                                    const information_date = cells[1].textContent.trim();
                                    const priority = cells[2].querySelector('.badge') ? cells[2].querySelector('.badge').textContent.trim() : cells[2].textContent.trim();
                                    const user_position = cells[3].textContent.trim();
                                    const department = cells[4].textContent.trim();
                                    const application = cells[5].textContent.trim();
                                    const type = cells[6].textContent.trim();
                                    const description = cells[7].textContent.trim();
                                    const action_solution = cells[8].textContent.trim();
                                    const status = cells[9].querySelector('.badge') ? cells[9].querySelector('.badge').textContent.trim() : cells[9].textContent.trim();
                                    
                                    console.log('Extracted data:', { id, no, information_date, priority, user_position, department, application, type, description, action_solution, status });
                                    
                                    // Call showEditModal with the data
                                    showEditModal(id, no, information_date, priority, user_position, department, application, type, description, action_solution, status);
                                });
                                
                                // Add visual feedback that row is clickable
                                row.style.cursor = 'pointer';
                                row.title = 'Click to edit this activity';
                            });
                        }, 100);
                    });

                    </script>



<!-- Logo Notification Manager -->
<script src="assets/js/logo-notifications.js"></script>

<!-- Activity Table Enhancement Script -->
<script src="assets/js/activity-table.js"></script>

<?php include './partials/layouts/layoutBottom.php'; ?>
