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
        $message_type = 'success';
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
        $message_type = 'info';
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
        $message_type = 'warning';
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
                                </select>
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn-apply">Apply Filters</button>
                            <a href="activity_crud.php" class="btn-reset">Reset</a>
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
                        

                        

                        

                        

                        
                        /* Center align NO column header */
                        .table th:nth-child(1) .table-header {
                            text-align: center;
                        }
                        
                        /* Center align Information Date header */
                        .table th:nth-child(2) .table-header {
                            text-align: center;
                        }
                        
                        /* Center align Status header */
                        .table th:nth-child(10) .table-header {
                            text-align: center;
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
                        .custom-modal-overlay {
                            position: fixed;
                            top: 0;
                            left: 0;
                            width: 100%;
                            height: 100%;
                            background-color: rgba(0, 0, 0, 0.5);
                            z-index: 9999;
                            display: none;
                            visibility: hidden;
                            opacity: 0;
                            transition: opacity 0.3s ease;
                        }
                        
                        .custom-modal {
                            position: fixed;
                            top: 50%;
                            left: 50%;
                            transform: translate(-50%, -50%);
                            background: white;
                            border-radius: 8px;
                            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
                            width: 90%;
                            max-width: 800px;
                            max-height: 90vh;
                            overflow-y: auto;
                            z-index: 10000;
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
                            color: #333;
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
<?php if ($message): ?>
                        <div class="alert alert-<?= $message_type ?? 'info' ?> alert-animated" role="alert">
                            <div class="alert-content">
                                <i class="alert-icon ri-<?= $message_type === 'success' ? 'check-line' : ($message_type === 'warning' ? 'error-warning-line' : 'information-line') ?>"></i>
                                <span class="alert-message"><?= htmlspecialchars($message) ?></span>
                            </div>
                        </div>
<?php endif; ?>

                    <div class="card-body">

                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                        <thead>
                            <tr>
                                <th scope="col">
                                    <div class="table-header">No</div>
                                </th>
                                <th scope="col">
                                    <div class="table-header">Information Date</div>
                                </th>
                                <th scope="col">
                                    <div class="table-header">Priority</div>
                                </th>
                                <th scope="col">
                                    <div class="table-header">User &amp; Position</div>
                                </th>
                                <th scope="col">
                                    <div class="table-header">Department</div>
                                </th>
                                <th scope="col">
                                    <div class="table-header">Application</div>
                                </th>
                                <th scope="col">
                                    <div class="table-header">Type</div>
                                </th>
                                <th scope="col">
                                    <div class="table-header">Description</div>
                                </th>
                                <th scope="col">
                                    <div class="table-header">Action / Solution</div>
                                </th>
                                <th scope="col">
                                    <div class="table-header">Status</div>
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
                    </div>

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
function showCreateModal() {
    console.log('showCreateModal called - using custom modal');
    
    // Get the existing modal element
    const modalEl = document.getElementById('createActivityModal');
    console.log('Modal element found:', modalEl);
    
    if (!modalEl) {
        console.error('Modal element not found!');
        return;
    }
    
    // Show custom modal
    modalEl.style.display = 'block';
    modalEl.style.visibility = 'visible';
    modalEl.style.opacity = '1';
    
    console.log('Custom modal shown successfully');
}

function closeCreateModal() {
    const modal = document.getElementById('createActivityModal');
    if (modal) {
        modal.style.display = 'none';
        modal.style.visibility = 'hidden';
        modal.style.opacity = '0';
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

        // Build a custom modal without Bootstrap dependencies
        let modalEl = document.getElementById('editActivityModal');
        if (!modalEl) {
            modalEl = document.createElement('div');
            modalEl.id = 'editActivityModal';
            modalEl.className = 'custom-modal-overlay';
            modalEl.innerHTML = `
            <div class="custom-modal">
              <div class="custom-modal-header">
                <h5 class="custom-modal-title">Edit Activity</h5>
                <button type="button" class="custom-modal-close" onclick="closeEditModal()">&times;</button>
                </div>
                <form method="post" id="editActivityForm">
                <div class="custom-modal-body">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" id="edit_id">
                  <div class="custom-modal-row">
                    <div class="custom-modal-col">
                      <label class="custom-modal-label">No</label>
                      <input type="number" name="no" id="edit_no" class="custom-modal-input">
                      </div>
                    <div class="custom-modal-col">
                      <label class="custom-modal-label">Status *</label>
                      <select name="status" id="edit_status" class="custom-modal-select" required>
                          <option value="Open">Open</option>
                          <option value="On Progress">On Progress</option>
                          <option value="Need Requirement">Need Requirement</option>
                          <option value="Done">Done</option>
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
                    <div class="custom-col">
                      <label class="custom-modal-label">User Position</label>
                      <input type="text" name="user_position" id="edit_user_position" class="custom-modal-input">
                    </div>
                    <div class="custom-modal-col">
                      <label class="custom-modal-label">Department</label>
                      <select name="department" id="edit_department" class="custom-modal-select">
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
                  </div>
                  <div class="custom-modal-row">
                    <div class="custom-modal-col">
                      <label class="custom-modal-label">Application *</label>
                      <select name="application" id="edit_application" class="custom-modal-select" required>
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
                      <input type="text" name="project" id="edit_project_name" class="custom-modal-input">
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
                  <button type="submit" name="delete" value="1" class="custom-btn custom-btn-danger" onclick="return confirm('Delete this activity?')">Delete</button>
                  <button type="button" class="custom-btn custom-btn-secondary" onclick="closeEditModal()">Close</button>
                </div>
              </form>
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

        // Show custom modal
        modalEl.style.display = 'block';
        modalEl.style.visibility = 'visible';
        modalEl.style.opacity = '1';
        
        // Modal is now visible with custom styling
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

function closeEditModal() {
    const modal = document.getElementById('editActivityModal');
    if (modal) {
        modal.style.display = 'none';
        modal.style.visibility = 'hidden';
        modal.style.opacity = '0';
    }
}

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
});


</script>

<!-- Activity Table Enhancement Script -->
<script src="assets/js/activity-table.js"></script>

<?php include './partials/layouts/layoutBottom.php'; ?>
