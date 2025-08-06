<?php
session_start();
require_once 'db.php';

// Proteksi akses: hanya user login dengan role Administrator/Management
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
if (!in_array($_SESSION['user_role'], ['Administrator', 'Management'])) {
    header('Location: dashboard.php');
    exit;
}

$message = '';

// Ambil data project untuk dropdown
$projects = $pdo->query("SELECT id, project_id, project_name FROM projects ORDER BY project_id")->fetchAll(PDO::FETCH_ASSOC);

// CREATE ACTIVITY
if (isset($_POST['create'])) {
    $project_id = trim($_POST['project_id'] ?? '');
    $no = $_POST['no'] ?? null;
    $information_date = $_POST['information_date'] ?? null;
    $user_position = trim($_POST['user_position'] ?? '');
    $department = $_POST['department'] ?? null;
    $application = $_POST['application'] ?? null;
    $type = $_POST['type'] ?? null;
    $description = trim($_POST['description'] ?? '');
    $action_solution = trim($_POST['action_solution'] ?? '');
    $due_date = $_POST['due_date'] ?? null;
    $status = $_POST['status'] ?? null;
    $cnc_number = trim($_POST['cnc_number'] ?? '');

    if (!$project_id || !$no || !$information_date || !$status) {
        $message = 'Project ID, No, Information Date, dan Status wajib diisi!';
    } else {
        $stmt = $pdo->prepare('INSERT INTO activities (project_id, no, information_date, user_position, department, application, type, description, action_solution, due_date, status, cnc_number) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)');
        $stmt->execute([$project_id, $no, $information_date, $user_position, $department, $application, $type, $description, $action_solution, $due_date, $status, $cnc_number]);
        $message = 'Activity created!';
    }
}

// SEARCH & FILTER
$search = trim($_GET['search'] ?? '');
$filter_department = $_GET['filter_department'] ?? '';
$filter_status = $_GET['filter_status'] ?? '';

$where = [];
$params = [];
if ($search) {
    $where[] = "(project_id ILIKE :search OR description ILIKE :search OR user_position ILIKE :search)";
    $params['search'] = "%$search%";
}
if ($filter_department) {
    $where[] = "department = :department";
    $params['department'] = $filter_department;
}
if ($filter_status) {
    $where[] = "status = :status";
    $params['status'] = $filter_status;
}
$where_sql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

// Pagination
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Total data
$sql_count = "SELECT COUNT(*) FROM activities $where_sql";
$stmt_count = $pdo->prepare($sql_count);
$stmt_count->execute($params);
$total_data = $stmt_count->fetchColumn();
$total_pages = max(1, ceil($total_data / $per_page));

// Data page
$sql = "SELECT * FROM activities $where_sql ORDER BY id DESC LIMIT $per_page OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$activities = $stmt->fetchAll(PDO::FETCH_ASSOC);

// UPDATE ACTIVITY
if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $project_id = trim($_POST['project_id'] ?? '');
    $no = $_POST['no'] ?? null;
    $information_date = $_POST['information_date'] ?? null;
    $user_position = trim($_POST['user_position'] ?? '');
    $department = $_POST['department'] ?? null;
    $application = $_POST['application'] ?? null;
    $type = $_POST['type'] ?? null;
    $description = trim($_POST['description'] ?? '');
    $action_solution = trim($_POST['action_solution'] ?? '');
    $due_date = $_POST['due_date'] ?? null;
    $status = $_POST['status'] ?? null;
    $cnc_number = trim($_POST['cnc_number'] ?? '');
    $stmt = $pdo->prepare('UPDATE activities SET project_id=?, no=?, information_date=?, user_position=?, department=?, application=?, type=?, description=?, action_solution=?, due_date=?, status=?, cnc_number=? WHERE id=?');
    $stmt->execute([$project_id, $no, $information_date, $user_position, $department, $application, $type, $description, $action_solution, $due_date, $status, $cnc_number, $id]);
    $message = 'Activity updated!';
}

// DELETE ACTIVITY
if (isset($_POST['delete'])) {
    $id = $_POST['id'];
    $stmt = $pdo->prepare('DELETE FROM activities WHERE id=?');
    $stmt->execute([$id]);
    $message = 'Activity deleted!';
}

// Kanban grouping
$kanban = [];
foreach ($activities as $a) {
    $kanban[$a['status']][] = $a;
}
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
                <div class="card-body">
                    <?php if ($message): ?>
                        <div class="alert alert-info"> <?= htmlspecialchars($message) ?> </div>
                    <?php endif; ?>

<h2>Tambah Activity</h2>
<form method="post">
    Project ID: <select name="project_id" required>
        <option value="">-</option>
        <?php foreach ($projects as $p): ?>
            <option value="<?= htmlspecialchars($p['project_id']) ?>">[<?= htmlspecialchars($p['project_id']) ?>] <?= htmlspecialchars($p['project_name']) ?></option>
        <?php endforeach; ?>
    </select><br>
    No: <input type="number" name="no" required><br>
    Information Date: <input type="date" name="information_date" required><br>
    User & Position: <input type="text" name="user_position"><br>
    Department: <select name="department">
        <option value="">-</option>
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
    </select><br>
    Application: <select name="application">
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
    </select><br>
    Type: <select name="type">
        <option value="">-</option>
        <option value="Setup">Setup</option>
        <option value="Question">Question</option>
        <option value="Issue">Issue</option>
        <option value="Report Issue">Report Issue</option>
        <option value="Report Request">Report Request</option>
        <option value="Feature Request">Feature Request</option>
    </select><br>
    Description: <input type="text" name="description"><br>
    Action / Solution: <input type="text" name="action_solution"><br>
    Due Date: <input type="date" name="due_date"><br>
    Status: <select name="status" required>
        <option value="">-</option>
        <option value="Open">Open</option>
        <option value="On Progress">On Progress</option>
        <option value="Need Requirement">Need Requirement</option>
        <option value="Done">Done</option>
    </select><br>
    CNC Number: <input type="text" name="cnc_number"><br>
    <button type="submit" name="create">Create</button>
</form>

<h2>List View Activity</h2>
<a href="export_activity_excel.php" style="display:inline-block;margin-bottom:8px;padding:6px 16px;background:#4caf50;color:#fff;text-decoration:none;border-radius:4px;">Export Excel</a>
<form method="get" style="margin-bottom:16px;display:inline-block;margin-left:16px;">
    <input type="text" name="search" placeholder="Cari Project ID/Desc/User..." value="<?= htmlspecialchars($search) ?>">
    <select name="filter_department">
        <option value="">Semua Department</option>
        <option value="Food & Beverage" <?= $filter_department==='Food & Beverage'?'selected':'' ?>>Food & Beverage</option>
        <option value="Kitchen" <?= $filter_department==='Kitchen'?'selected':'' ?>>Kitchen</option>
        <option value="Room Division" <?= $filter_department==='Room Division'?'selected':'' ?>>Room Division</option>
        <option value="Front Office" <?= $filter_department==='Front Office'?'selected':'' ?>>Front Office</option>
        <option value="Housekeeping" <?= $filter_department==='Housekeeping'?'selected':'' ?>>Housekeeping</option>
        <option value="Engineering" <?= $filter_department==='Engineering'?'selected':'' ?>>Engineering</option>
        <option value="Sales & Marketing" <?= $filter_department==='Sales & Marketing'?'selected':'' ?>>Sales & Marketing</option>
        <option value="IT / EDP" <?= $filter_department==='IT / EDP'?'selected':'' ?>>IT / EDP</option>
        <option value="Accounting" <?= $filter_department==='Accounting'?'selected':'' ?>>Accounting</option>
        <option value="Executive Office" <?= $filter_department==='Executive Office'?'selected':'' ?>>Executive Office</option>
    </select>
    <select name="filter_status">
        <option value="">Semua Status</option>
        <option value="Open" <?= $filter_status==='Open'?'selected':'' ?>>Open</option>
        <option value="On Progress" <?= $filter_status==='On Progress'?'selected':'' ?>>On Progress</option>
        <option value="Need Requirement" <?= $filter_status==='Need Requirement'?'selected':'' ?>>Need Requirement</option>
        <option value="Done" <?= $filter_status==='Done'?'selected':'' ?>>Done</option>
    </select>
    <button type="submit">Cari</button>
    <a href="activity_crud.php">Reset</a>
</form>
<div style="overflow-x:auto; max-width:100vw; margin-bottom:16px;">
<table border="1" cellpadding="4" style="min-width:900px;">
<tr><th>ID</th><th>Project ID</th><th>No</th><th>Information Date</th><th>User & Position</th><th>Department</th><th>Application</th><th>Type</th><th>Description</th><th>Action/Solution</th><th>Due Date</th><th>Status</th><th>CNC Number</th><th>Aksi</th></tr>
<?php foreach ($activities as $a): ?>
<tr>
    <form method="post">
    <td><?= $a['id'] ?><input type="hidden" name="id" value="<?= $a['id'] ?>"></td>
    <td><input type="text" name="project_id" value="<?= htmlspecialchars($a['project_id']) ?>"></td>
    <td><input type="number" name="no" value="<?= htmlspecialchars($a['no']) ?>"></td>
    <td><input type="date" name="information_date" value="<?= htmlspecialchars($a['information_date']) ?>"></td>
    <td><input type="text" name="user_position" value="<?= htmlspecialchars($a['user_position']) ?>"></td>
    <td><input type="text" name="department" value="<?= htmlspecialchars($a['department']) ?>"></td>
    <td><input type="text" name="application" value="<?= htmlspecialchars($a['application']) ?>"></td>
    <td><input type="text" name="type" value="<?= htmlspecialchars($a['type']) ?>"></td>
    <td><input type="text" name="description" value="<?= htmlspecialchars($a['description']) ?>"></td>
    <td><input type="text" name="action_solution" value="<?= htmlspecialchars($a['action_solution']) ?>"></td>
    <td><input type="date" name="due_date" value="<?= htmlspecialchars($a['due_date']) ?>"></td>
    <td><input type="text" name="status" value="<?= htmlspecialchars($a['status']) ?>"></td>
    <td><input type="text" name="cnc_number" value="<?= htmlspecialchars($a['cnc_number']) ?>"></td>
    <td>
        <button type="submit" name="update">Update</button>
        <button type="submit" name="delete" onclick="return confirm('Delete activity?')">Delete</button>
    </td>
    </form>
</tr>
<?php endforeach; ?>
</table>
</div>
<!-- Pagination -->
<div style="margin:16px 0;">
<?php if ($total_pages > 1): ?>
    <div style="display:inline-block;">
    <?php for ($i=1; $i<=$total_pages; $i++): ?>
        <?php if ($i == $page): ?>
            <span style="font-weight:bold;padding:4px 8px;">[<?= $i ?>]</span>
        <?php else: ?>
            <a href="?<?= http_build_query(array_merge($_GET, ['page'=>$i])) ?>" style="padding:4px 8px;"> <?= $i ?> </a>
        <?php endif; ?>
    <?php endfor; ?>
    </div>
<?php endif; ?>
</div>

<h2>Kanban View (by Status)</h2>
<div style="display: flex; gap: 24px;">
<?php foreach ($kanban as $status => $acts): ?>
    <div style="border:1px solid #aaa; padding:8px; min-width:200px;">
        <b><?= htmlspecialchars($status) ?></b><br>
        <?php foreach ($acts as $a): ?>
            <div style="margin:8px 0; border-bottom:1px solid #eee;">
                <b>#<?= $a['id'] ?>:</b> <?= htmlspecialchars($a['description']) ?><br>
                <small><?= htmlspecialchars($a['information_date']) ?> - <?= htmlspecialchars($a['user_position']) ?></small>
            </div>
        <?php endforeach; ?>
    </div>
<?php endforeach; ?>
</div>

<h2>Gantt Chart View (Tabel)</h2>
<table border="1" cellpadding="4">
<tr><th>ID</th><th>Project ID</th><th>Information Date</th><th>Due Date</th><th>Status</th><th>Description</th></tr>
<?php foreach ($activities as $a): ?>
<tr>
    <td><?= $a['id'] ?></td>
    <td><?= htmlspecialchars($a['project_id']) ?></td>
    <td><?= htmlspecialchars($a['information_date']) ?></td>
    <td><?= htmlspecialchars($a['due_date']) ?></td>
    <td><?= htmlspecialchars($a['status']) ?></td>
    <td><?= htmlspecialchars($a['description']) ?></td>
</tr>
<?php endforeach; ?>
                    </table>
                </div>
            </div>
        </div>

<?php include './partials/layouts/layoutBottom.php'; ?>
