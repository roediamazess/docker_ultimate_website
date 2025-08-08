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
$limit = 10;
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
                            <option value="Planning" <?= $filter_status === 'Planning' ? 'selected' : '' ?>>Planning</option>
                            <option value="In Progress" <?= $filter_status === 'In Progress' ? 'selected' : '' ?>>In Progress</option>
                            <option value="Completed" <?= $filter_status === 'Completed' ? 'selected' : '' ?>>Completed</option>
                            <option value="On Hold" <?= $filter_status === 'On Hold' ? 'selected' : '' ?>>On Hold</option>
                        </select>
                        <a href="#" onclick="showCreateForm()" class="btn btn-sm btn-primary-600"><i class="ri-add-line"></i> Create Project</a>
                    </div>
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
                                <th scope="col">Project</th>
                                <th scope="col">Hotel Name</th>
                                <th scope="col">Duration</th>
                                <th scope="col">Status</th>
                                <th scope="col">PIC</th>
                                <th scope="col">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($projects as $index => $p): ?>
                            <tr>
                                <td>
                                    <div class="form-check style-check d-flex align-items-center">
                                        <input class="form-check-input" type="checkbox" value="<?= $p['id'] ?>" id="check<?= $p['id'] ?>">
                                        <label class="form-check-label" for="check<?= $p['id'] ?>">
                                            <?= str_pad($index + 1 + $offset, 2, '0', STR_PAD_LEFT) ?>
                                        </label>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="assets/images/avatar/avatar3.png" alt="" class="flex-shrink-0 me-12 radius-8" style="width:40px;height:40px;">
                                        <div class="flex-grow-1">
                                            <h6 class="text-md mb-0 fw-medium"><?= htmlspecialchars($p['project_name']) ?></h6>
                                            <span class="text-sm text-secondary-light"><?= htmlspecialchars($p['project_id']) ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($p['hotel_name'] ?: '-') ?></td>
                                <td>
                                    <?php if ($p['start_date'] && $p['end_date']): ?>
                                        <?= date('d M', strtotime($p['start_date'])) ?> - <?= date('d M Y', strtotime($p['end_date'])) ?>
                                        <br><small class="text-secondary-light"><?= $p['total_days'] ?> days</small>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $status_colors = [
                                        'Planning' => 'bg-warning-focus text-warning-main',
                                        'In Progress' => 'bg-info-focus text-info-main',
                                        'Completed' => 'bg-success-focus text-success-main',
                                        'On Hold' => 'bg-danger-focus text-danger-main'
                                    ];
                                    $color_class = $status_colors[$p['status']] ?? 'bg-neutral-200 text-neutral-600';
                                    ?>
                                    <span class="<?= $color_class ?> px-8 py-4 rounded-pill fw-medium text-sm"><?= htmlspecialchars($p['status']) ?></span>
                                </td>
                                <td><?= htmlspecialchars($p['pic'] ?: '-') ?></td>
                                <td>
                                    <a href="javascript:void(0)" onclick="editProject(<?= $p['id'] ?>)" class="w-32-px h-32-px bg-success-focus text-success-main rounded-circle d-inline-flex align-items-center justify-content-center">
                                        <iconify-icon icon="lucide:edit"></iconify-icon>
                                    </a>
                                    <a href="javascript:void(0)" onclick="deleteProject(<?= $p['id'] ?>)" class="w-32-px h-32-px bg-danger-focus text-danger-main rounded-circle d-inline-flex align-items-center justify-content-center">
                                        <iconify-icon icon="mingcute:delete-2-line"></iconify-icon>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

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

<script>
function showCreateForm() {
    document.getElementById('createProjectForm').style.display = 'block';
}

function hideCreateForm() {
    document.getElementById('createProjectForm').style.display = 'none';
}

function editProject(projectId) {
    alert('Edit project functionality to be implemented');
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
</script>

<?php include './partials/layouts/layoutBottom.php'; ?>
