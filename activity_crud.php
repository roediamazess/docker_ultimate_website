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
        $stmt = $pdo->prepare('INSERT INTO activities (project_id, user_position, department, application, type, description, action_solution, due_date, status, cnc_number, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            $_POST['project_id'],
            $_POST['user_position'],
            $_POST['department'],
            $_POST['application'],
            $_POST['type'],
            $_POST['description'],
            $_POST['action_solution'],
            $_POST['due_date'] ?: null,
            $_POST['status'],
            $_POST['cnc_number'],
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
        $stmt = $pdo->prepare('UPDATE activities SET project_id=?, user_position=?, department=?, application=?, type=?, description=?, action_solution=?, due_date=?, status=?, cnc_number=? WHERE id=?');
        $stmt->execute([
            $_POST['project_id'],
            $_POST['user_position'],
            $_POST['department'],
            $_POST['application'],
            $_POST['type'],
            $_POST['description'],
            $_POST['action_solution'],
            $_POST['due_date'] ?: null,
            $_POST['status'],
            $_POST['cnc_number'],
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
$limit = 10;
$offset = ($page - 1) * $limit;

$search = trim($_GET['search'] ?? '');
$filter_status = $_GET['filter_status'] ?? '';
$filter_type = $_GET['filter_type'] ?? '';

$where_conditions = [];
$params = [];

if ($search) {
    $where_conditions[] = "(description ILIKE ? OR user_position ILIKE ? OR cnc_number ILIKE ?)";
    $search_term = "%$search%";
    $params = array_merge($params, [$search_term, $search_term, $search_term]);
}

if ($filter_status) {
    $where_conditions[] = "a.status = ?";
    $params[] = $filter_status;
}

if ($filter_type) {
    $where_conditions[] = "a.type = ?";
    $params[] = $filter_type;
}

$where_clause = $where_conditions ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Get total count
$count_sql = "SELECT COUNT(*) FROM activities $where_clause";
$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute($params);
$total_activities = $count_stmt->fetchColumn();
$total_pages = ceil($total_activities / $limit);

// Get activities with pagination
$sql = "SELECT a.*, p.project_name FROM activities a LEFT JOIN projects p ON p.project_id = a.project_id $where_clause ORDER BY a.created_at DESC LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$activities = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get projects for dropdown
$projects = $pdo->query('SELECT project_id, project_name FROM projects ORDER BY project_name')->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include './partials/layouts/layoutTop.php'; ?>

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
                        </select>
                        <a href="#" onclick="showCreateForm()" class="btn btn-sm btn-primary-600"><i class="ri-add-line"></i> Create Activity</a>
                    </div>
                </div>
                <div class="card-body">
<?php if ($message): ?>
                        <div class="alert alert-info"> <?= htmlspecialchars($message) ?> </div>
<?php endif; ?>

                                        <!-- Create Activity Form (Hidden by default) -->
                    <div id="createActivityForm" style="display:none; margin-bottom:24px; padding:20px; border:1px solid #ddd; border-radius:8px; background:#f9f9f9;">
                        <h5 class="mb-3">Add New Activity</h5>
                        <form method="post" class="row g-3">
                            <?= csrf_field() ?>
                            <div class="col-md-6">
                                <label class="form-label">Project *</label>
                                <select name="project_id" class="form-select" required>
                                    <option value="">Select Project</option>
                                    <?php foreach ($projects as $project): ?>
                                        <option value="<?= $project['project_id'] ?>"><?= htmlspecialchars($project['project_name']) ?></option>
        <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">User Position</label>
                                <input type="text" name="user_position" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Department</label>
                                <select name="department" class="form-select">
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
                            <div class="col-md-4">
                                <label class="form-label">Application</label>
                                <select name="application" class="form-select">
                                    <option value="">Select Application</option>
        <option value="Power FO">Power FO</option>
        <option value="My POS">My POS</option>
        <option value="My MGR">My MGR</option>
        <option value="Power AR">Power AR</option>
        <option value="Power INV">Power INV</option>
        <option value="Others">Others</option>
                                </select>
                            </div>
                            <div class="col-md-4">
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
                            <div class="col-md-4">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
        <option value="Open">Open</option>
        <option value="On Progress">On Progress</option>
        <option value="Need Requirement">Need Requirement</option>
        <option value="Done">Done</option>
    </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Due Date</label>
                                <input type="date" name="due_date" class="form-control">
                            </div>
                            <div class="col-md-4">
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
                            <div class="col-12">
                                <button type="submit" name="create" class="btn btn-primary">Add Activity</button>
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
                                <th scope="col">Activity</th>
                                <th scope="col">Project</th>
                                <th scope="col">User Position</th>
                                <th scope="col">Due Date</th>
                                <th scope="col">Type</th>
                                <th scope="col">Status</th>
                                <th scope="col">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($activities as $index => $a): ?>
                            <tr>
                                <td>
                                    <div class="form-check style-check d-flex align-items-center">
                                        <input class="form-check-input" type="checkbox" value="<?= $a['id'] ?>" id="check<?= $a['id'] ?>">
                                        <label class="form-check-label" for="check<?= $a['id'] ?>">
                                            <?= str_pad($index + 1 + $offset, 2, '0', STR_PAD_LEFT) ?>
                                        </label>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="assets/images/avatar/avatar4.png" alt="" class="flex-shrink-0 me-12 radius-8" style="width:40px;height:40px;">
                                        <div class="flex-grow-1">
                                            <h6 class="text-md mb-0 fw-medium"><?= htmlspecialchars($a['type'] ?: 'Activity') ?></h6>
                                            <span class="text-sm text-secondary-light"><?= htmlspecialchars(substr($a['description'] ?: '', 0, 50)) ?><?= strlen($a['description'] ?: '') > 50 ? '...' : '' ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($a['project_name'] ?: '-') ?></td>
                                <td><?= htmlspecialchars($a['user_position'] ?: '-') ?></td>
                                <td><?= $a['due_date'] ? date('d M Y', strtotime($a['due_date'])) : '-' ?></td>
                                <td>
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
                                    <span class="<?= $color_class ?> px-8 py-4 rounded-pill fw-medium text-sm"><?= htmlspecialchars($a['type'] ?: '-') ?></span>
                                </td>
                                <td>
                                    <?php
                                    $status_colors = [
                                        'Open' => 'bg-warning-focus text-warning-main',
                                        'On Progress' => 'bg-info-focus text-info-main',
                                        'Need Requirement' => 'bg-neutral-200 text-neutral-600',
                                        'Done' => 'bg-success-focus text-success-main'
                                    ];
                                    $color_class = $status_colors[$a['status']] ?? 'bg-neutral-200 text-neutral-600';
                                    ?>
                                    <span class="<?= $color_class ?> px-8 py-4 rounded-pill fw-medium text-sm"><?= htmlspecialchars($a['status'] ?: '-') ?></span>
                                </td>
                                <td>
                                    <a href="javascript:void(0)" onclick="editActivity(<?= $a['id'] ?>)" class="w-32-px h-32-px bg-success-focus text-success-main rounded-circle d-inline-flex align-items-center justify-content-center">
                                        <iconify-icon icon="lucide:edit"></iconify-icon>
                                    </a>
                                    <a href="javascript:void(0)" onclick="deleteActivity(<?= $a['id'] ?>)" class="w-32-px h-32-px bg-danger-focus text-danger-main rounded-circle d-inline-flex align-items-center justify-content-center">
                                        <iconify-icon icon="mingcute:delete-2-line"></iconify-icon>
                                    </a>
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
    document.getElementById('createActivityForm').style.display = 'block';
}

function hideCreateForm() {
    document.getElementById('createActivityForm').style.display = 'none';
}

function editActivity(activityId) {
    alert('Edit activity functionality to be implemented');
}

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

<?php include './partials/layouts/layoutBottom.php'; ?>