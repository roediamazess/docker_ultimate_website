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

// Create Activity
if (isset($_POST['create'])) {
    if (!csrf_verify()) {
        $message = 'CSRF token tidak valid!';
    } else {
        $stmt = $pdo->prepare('INSERT INTO activities (project_id, activity_name, description, assigned_to, status, priority, due_date, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            $_POST['project_id'],
            $_POST['activity_name'],
            $_POST['description'],
            $_POST['assigned_to'],
            $_POST['status'],
            $_POST['priority'],
            $_POST['due_date'] ?: null,
            date('Y-m-d H:i:s')
        ]);
        $message = 'Activity created!';
        log_activity('create_activity', 'Activity: ' . $_POST['activity_name']);
    }
}

// Update Activity
if (isset($_POST['update'])) {
    if (!csrf_verify()) {
        $message = 'CSRF token tidak valid!';
    } else {
        $stmt = $pdo->prepare('UPDATE activities SET project_id=?, activity_name=?, description=?, assigned_to=?, status=?, priority=?, due_date=? WHERE id=?');
        $stmt->execute([
            $_POST['project_id'],
            $_POST['activity_name'],
            $_POST['description'],
            $_POST['assigned_to'],
            $_POST['status'],
            $_POST['priority'],
            $_POST['due_date'] ?: null,
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
$filter_priority = $_GET['filter_priority'] ?? '';

$where_conditions = [];
$params = [];

if ($search) {
    $where_conditions[] = "(activity_name ILIKE ? OR description ILIKE ? OR assigned_to ILIKE ?)";
    $search_term = "%$search%";
    $params = array_merge($params, [$search_term, $search_term, $search_term]);
}

if ($filter_status) {
    $where_conditions[] = "status = ?";
    $params[] = $filter_status;
}

if ($filter_priority) {
    $where_conditions[] = "priority = ?";
    $params[] = $filter_priority;
}

$where_clause = $where_conditions ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Get total count
$count_sql = "SELECT COUNT(*) FROM activities $where_clause";
$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute($params);
$total_activities = $count_stmt->fetchColumn();
$total_pages = ceil($total_activities / $limit);

// Get activities with pagination
$sql = "SELECT a.*, p.project_name FROM activities a LEFT JOIN projects p ON a.project_id = p.id $where_clause ORDER BY a.created_at DESC LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$activities = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get projects for dropdown
$projects = $pdo->query('SELECT id, project_name FROM projects ORDER BY project_name')->fetchAll(PDO::FETCH_ASSOC);
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
                            <option value="Pending" <?= $filter_status === 'Pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="In Progress" <?= $filter_status === 'In Progress' ? 'selected' : '' ?>>In Progress</option>
                            <option value="Completed" <?= $filter_status === 'Completed' ? 'selected' : '' ?>>Completed</option>
                            <option value="On Hold" <?= $filter_status === 'On Hold' ? 'selected' : '' ?>>On Hold</option>
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
                                        <option value="<?= $project['id'] ?>"><?= htmlspecialchars($project['project_name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Activity Name *</label>
                                <input type="text" name="activity_name" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Assigned To</label>
                                <input type="text" name="assigned_to" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="Pending">Pending</option>
                                    <option value="In Progress">In Progress</option>
                                    <option value="Completed">Completed</option>
                                    <option value="On Hold">On Hold</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Priority</label>
                                <select name="priority" class="form-select">
                                    <option value="Low">Low</option>
                                    <option value="Medium">Medium</option>
                                    <option value="High">High</option>
                                    <option value="Critical">Critical</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Due Date</label>
                                <input type="date" name="due_date" class="form-control">
                            </div>
                            <div class="col-md-6"></div>
                            <div class="col-md-12">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" rows="3"></textarea>
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
                                <th scope="col">Assigned To</th>
                                <th scope="col">Due Date</th>
                                <th scope="col">Priority</th>
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
                                            <h6 class="text-md mb-0 fw-medium"><?= htmlspecialchars($a['activity_name']) ?></h6>
                                            <span class="text-sm text-secondary-light"><?= htmlspecialchars(substr($a['description'], 0, 50)) ?><?= strlen($a['description']) > 50 ? '...' : '' ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($a['project_name'] ?: '-') ?></td>
                                <td><?= htmlspecialchars($a['assigned_to'] ?: '-') ?></td>
                                <td><?= $a['due_date'] ? date('d M Y', strtotime($a['due_date'])) : '-' ?></td>
                                <td>
                                    <?php
                                    $priority_colors = [
                                        'Low' => 'bg-success-focus text-success-main',
                                        'Medium' => 'bg-warning-focus text-warning-main',
                                        'High' => 'bg-danger-focus text-danger-main',
                                        'Critical' => 'bg-danger-600 text-white'
                                    ];
                                    $color_class = $priority_colors[$a['priority']] ?? 'bg-neutral-200 text-neutral-600';
                                    ?>
                                    <span class="<?= $color_class ?> px-8 py-4 rounded-pill fw-medium text-sm"><?= htmlspecialchars($a['priority']) ?></span>
                                </td>
                                <td>
                                    <?php
                                    $status_colors = [
                                        'Pending' => 'bg-warning-focus text-warning-main',
                                        'In Progress' => 'bg-info-focus text-info-main',
                                        'Completed' => 'bg-success-focus text-success-main',
                                        'On Hold' => 'bg-danger-focus text-danger-main'
                                    ];
                                    $color_class = $status_colors[$a['status']] ?? 'bg-neutral-200 text-neutral-600';
                                    ?>
                                    <span class="<?= $color_class ?> px-8 py-4 rounded-pill fw-medium text-sm"><?= htmlspecialchars($a['status']) ?></span>
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