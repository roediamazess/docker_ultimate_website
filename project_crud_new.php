<?php
session_start();
require_once 'db.php';
require_once 'access_control.php';

// Cek akses
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

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

// Create Project
if (isset($_POST['create'])) {
    if (!csrf_verify()) {
        $message = 'CSRF token tidak valid!';
    } else {
        $stmt = $pdo->prepare('INSERT INTO projects (project_id, pic, assignment, project_info, req_pic, hotel_name, project_name, start_date, end_date, total_days, type, status, handover_report, handover_days, ketertiban_admin, point_ach, point_req, percent_point, month, quarter, week_number, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        
        $start_date = $_POST['start_date'] ?: null;
        $end_date = $_POST['end_date'] ?: null;
        $total_days = null;
        
        if ($start_date && $end_date) {
            $diff = date_diff(date_create($start_date), date_create($end_date));
            $total_days = $diff->days + 1;
        }
        
        $stmt->execute([
            $_POST['project_id'],
            $_POST['pic'],
            $_POST['assignment'],
            $_POST['project_info'],
            $_POST['req_pic'],
            $_POST['hotel_name'],
            $_POST['project_name'],
            $start_date,
            $end_date,
            $total_days,
            $_POST['type'],
            $_POST['status'],
            $_POST['handover_report'],
            $_POST['handover_days'] ?: null,
            $_POST['ketertiban_admin'],
            $_POST['point_ach'] ?: null,
            $_POST['point_req'] ?: null,
            $_POST['percent_point'] ?: null,
            $_POST['month'],
            $_POST['quarter'],
            $_POST['week_number'] ?: null,
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
        $stmt = $pdo->prepare('UPDATE projects SET project_id=?, pic=?, assignment=?, project_info=?, req_pic=?, hotel_name=?, project_name=?, start_date=?, end_date=?, total_days=?, type=?, status=?, handover_report=?, handover_days=?, ketertiban_admin=?, point_ach=?, point_req=?, percent_point=?, month=?, quarter=?, week_number=? WHERE id=?');
        
        $start_date = $_POST['start_date'] ?: null;
        $end_date = $_POST['end_date'] ?: null;
        $total_days = null;
        
        if ($start_date && $end_date) {
            $diff = date_diff(date_create($start_date), date_create($end_date));
            $total_days = $diff->days + 1;
        }
        
        $stmt->execute([
            $_POST['project_id'],
            $_POST['pic'],
            $_POST['assignment'],
            $_POST['project_info'],
            $_POST['req_pic'],
            $_POST['hotel_name'],
            $_POST['project_name'],
            $start_date,
            $end_date,
            $total_days,
            $_POST['type'],
            $_POST['status'],
            $_POST['handover_report'],
            $_POST['handover_days'] ?: null,
            $_POST['ketertiban_admin'],
            $_POST['point_ach'] ?: null,
            $_POST['point_req'] ?: null,
            $_POST['percent_point'] ?: null,
            $_POST['month'],
            $_POST['quarter'],
            $_POST['week_number'] ?: null,
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
// Allow user-controlled page size to match Activity list
$limit = intval($_GET['limit'] ?? 10);
if (!in_array($limit, [10, 15, 20], true)) { $limit = 10; }
$offset = ($page - 1) * $limit;

$search = trim($_GET['search'] ?? '');
$filter_status = $_GET['filter_status'] ?? '';
$filter_type = $_GET['filter_type'] ?? '';

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

// Get distinct project types for filter dropdown (PostgreSQL enum-safe)
$type_rows = [];
try {
    $stmtType = $pdo->query("SELECT DISTINCT CAST(type AS TEXT) AS type FROM projects WHERE type IS NOT NULL AND CAST(type AS TEXT) <> '' ORDER BY type");
    $type_rows = $stmtType->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    $type_rows = [];
}
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
                                        <option value="Planning" <?= $filter_status === 'Planning' ? 'selected' : '' ?>>Planning</option>
                                        <option value="In Progress" <?= $filter_status === 'In Progress' ? 'selected' : '' ?>>In Progress</option>
                                        <option value="Completed" <?= $filter_status === 'Completed' ? 'selected' : '' ?>>Completed</option>
                                        <option value="On Hold" <?= $filter_status === 'On Hold' ? 'selected' : '' ?>>On Hold</option>
                                    </select>
                                </div>
                                <div class="filter-group" style="min-width:200px;">
                                    <label class="filter-label">Type</label>
                                    <select class="form-select" name="filter_type">
                                        <option value="">All Type</option>
                                        <?php foreach ($type_rows as $t): ?>
                                            <option value="<?= htmlspecialchars($t) ?>" <?= $filter_type === $t ? 'selected' : '' ?>><?= htmlspecialchars($t) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="d-flex gap-2" style="margin-top:12px;">
                                <button type="submit" class="btn-apply btn btn-sm btn-primary-600">Apply Filters</button>
                                <a href="project_crud_new.php" class="btn-reset btn btn-sm btn-secondary">Reset</a>
                            </div>
                        </form>
                    </div>

                    <table class="table table-striped mb-0">
                        <thead>
                            <tr>
                                <th scope="col"><div class="table-header">No</div></th>
                                <th scope="col"><div class="table-header">Project</div></th>
                                <th scope="col"><div class="table-header">Hotel Name</div></th>
                                <th scope="col"><div class="table-header">Duration</div></th>
                                <th scope="col"><div class="table-header">Status</div></th>
                                <th scope="col"><div class="table-header">PIC</div></th>
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
                                <td data-label="No"><?= str_pad($index + 1 + $offset, 2, '0', STR_PAD_LEFT) ?></td>
                                <td data-label="Project">
                                    <div class="d-flex align-items-center">
                                        <img src="assets/images/avatar/avatar3.png" alt="" class="flex-shrink-0 me-12 radius-8" style="width:40px;height:40px;">
                                        <div class="flex-grow-1">
                                            <h6 class="text-md mb-0 fw-medium"><?= htmlspecialchars($p['project_name']) ?></h6>
                                            <span class="text-sm text-secondary-light"><?= htmlspecialchars($p['project_id']) ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td data-label="Hotel Name"><?= htmlspecialchars($p['hotel_name'] ?: '-') ?></td>
                                <td data-label="Duration">
                                    <?php if ($p['start_date'] && $p['end_date']): ?>
                                        <?= date('d M', strtotime($p['start_date'])) ?> - <?= date('d M Y', strtotime($p['end_date'])) ?>
                                        <br><small class="text-secondary-light"><?= $p['total_days'] ?> days</small>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td data-label="Status">
                                    <?php
                                    $status_colors = [
                                        'Planning' => 'bg-warning-focus text-warning-main',
                                        'In Progress' => 'bg-info-focus text-info-main',
                                        'Completed' => 'bg-success-focus text-success-main',
                                        'On Hold' => 'bg-danger-focus text-danger-main'
                                    ];
                                    $color_class = $status_colors[$p['status']] ?? 'bg-neutral-200 text-neutral-600';
                                    ?>
                                    <span class="status-badge <?= $color_class ?> px-8 py-4 rounded-pill fw-medium text-sm"><?= htmlspecialchars($p['status']) ?></span>
                                </td>
                                <td data-label="PIC"><?= htmlspecialchars($p['pic'] ?: '-') ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <!-- Edit Project Modal (Bootstrap) -->
                    <div class="modal fade" id="editProjectModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Edit Project</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <form method="post">
                                    <div class="modal-body">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="id" id="edit_id">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label">Project ID</label>
                                                <input type="text" name="project_id" id="edit_project_id" class="form-control">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Project Name</label>
                                                <input type="text" name="project_name" id="edit_project_name" class="form-control">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Hotel Name</label>
                                                <input type="text" name="hotel_name" id="edit_hotel_name" class="form-control">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">PIC</label>
                                                <input type="text" name="pic" id="edit_pic" class="form-control">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Status</label>
                                                <select name="status" id="edit_status" class="form-select">
                                                    <option value="Planning">Planning</option>
                                                    <option value="In Progress">In Progress</option>
                                                    <option value="Completed">Completed</option>
                                                    <option value="On Hold">On Hold</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Type</label>
                                                <select name="type" id="edit_type" class="form-select">
                                                    <option value="">-</option>
                                                    <?php foreach ($type_rows as $t): ?>
                                                        <option value="<?= htmlspecialchars($t) ?>"><?= htmlspecialchars($t) ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Start Date</label>
                                                <input type="date" name="start_date" id="edit_start_date" class="form-control">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">End Date</label>
                                                <input type="date" name="end_date" id="edit_end_date" class="form-control">
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label">Project Info</label>
                                                <textarea name="project_info" id="edit_project_info" class="form-control" rows="3"></textarea>
                                            </div>

                                            <!-- Hidden fields to avoid undefined index on update -->
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
                                    </div>
                                    <div class="modal-footer">
                                        <button type="submit" name="update" value="1" class="btn btn-primary">Save</button>
                                        <button type="button" class="btn btn-danger" id="btnDeleteProject">Delete</button>
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    </div>
                                </form>
                            </div>
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

function deleteProject(projectId) {
    if (confirm('Are you sure you want to delete this project?')) {
        const form = document.createElement('form');
        form.method = 'post';
        form.innerHTML = `
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="${projectId}">
            <input type="hidden" name="delete" value="1">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

// Row click to open edit modal (same behavior as Activity List)
document.addEventListener('DOMContentLoaded', function() {
    const rows = document.querySelectorAll('.project-row');
    const modalEl = document.getElementById('editProjectModal');
    let bsModal = null;
    if (modalEl && window.bootstrap) {
        bsModal = new bootstrap.Modal(modalEl);
    }

    rows.forEach(function(row){
        row.style.cursor = 'pointer';
        row.addEventListener('click', function(){
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
            document.getElementById('edit_project_info').value = get('project_info');
            const hiddenIds = ['assignment','req_pic','handover_report','handover_days','ketertiban_admin','point_ach','point_req','percent_point','month','quarter','week_number'];
            hiddenIds.forEach(id => {
                const el = document.getElementById('edit_' + id);
                if (el) el.value = get(id);
            });

            const btnDelete = document.getElementById('btnDeleteProject');
            if (btnDelete) {
                btnDelete.onclick = function(){
                    deleteProject(get('id'));
                };
            }

            if (bsModal) { bsModal.show(); }
            else if (modalEl) { modalEl.style.display = 'block'; }
        });
    });
});
</script>

<?php include './partials/layouts/layoutBottom.php'; ?>
