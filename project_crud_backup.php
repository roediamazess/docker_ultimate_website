<?php
session_start();
require_once 'db.php';
require_once 'csrf.php';
require_once 'log.php';
require_once 'send_email.php';

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

// Ambil data user (Display Name) dan customer (Name)
$users = $pdo->query("SELECT id, display_name FROM users ORDER BY display_name")->fetchAll(PDO::FETCH_ASSOC);
$customers = $pdo->query("SELECT id, name FROM customers ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

// Helper fungsi
function getMonthName($date) {
    return $date ? date('F', strtotime($date)) : '';
}
function getQuarter($date) {
    if (!$date) return '';
    $m = (int)date('n', strtotime($date));
    return 'Quarter ' . ceil($m/3);
}
function getWeek($date) {
    return $date ? 'Week ' . date('W', strtotime($date)) : '';
}

// CREATE PROJECT
if (isset($_POST['create'])) {
    csrf_check();
    $project_id = trim($_POST['project_id'] ?? '');
    $pic = $_POST['pic'] ?? null;
    $assignment = $_POST['assignment'] ?? null;
    $project_information = $_POST['project_information'] ?? null;
    $req_pic = $_POST['req_pic'] ?? null;
    $hotel_name = $_POST['hotel_name'] ?? null;
    $project_name = trim($_POST['project_name'] ?? '');
    $start_date = $_POST['start_date'] ?? null;
    $end_date = $_POST['end_date'] ?? null;
    $type = $_POST['type'] ?? null;
    $status = $_POST['status'] ?? null;
    $handover_official_report = $_POST['handover_official_report'] ?? null;
    $point_ach = $_POST['point_ach'] ?? null;
    $point_req = $_POST['point_req'] ?? null;
    $s1_estimation_kpi2 = $_POST['s1_estimation_kpi2'] ?? null;
    $s1_over_days = $_POST['s1_over_days'] ?? null;
    $s1_count_of_emails_sent = $_POST['s1_count_of_emails_sent'] ?? null;
    $s2_email_sent = $_POST['s2_email_sent'] ?? null;
    $s3_email_sent = $_POST['s3_email_sent'] ?? null;

    // Perhitungan otomatis
    $total_days = ($start_date && $end_date) ? (strtotime($end_date) - strtotime($start_date))/86400 + 1 : null;
    $handover_days = ($end_date && $handover_official_report) ? (strtotime($handover_official_report) - strtotime($end_date))/86400 : null;
    // Ketertiban Admin
    if ($handover_days !== null) {
        if ($handover_days <= 3) $ketertiban_admin = 'Excellent';
        elseif ($handover_days <= 7) $ketertiban_admin = 'Good';
        elseif ($handover_days <= 14) $ketertiban_admin = 'Average';
        elseif ($handover_days <= 30) $ketertiban_admin = 'Poor';
        else $ketertiban_admin = 'Bad';
    } else {
        $ketertiban_admin = '';
    }
    $percent_point = ($point_ach && $point_req && $point_req != 0) ? round($point_ach/$point_req*100,2) : null;
    $month = getMonthName($start_date);
    $quarter = getQuarter($start_date);
    $week_no = $start_date ? (int)date('W', strtotime($start_date)) : null;

    if (!$project_id || !$hotel_name || !$start_date || !$type || !$status) {
        $message = 'Project ID, Hotel Name, Start Date, Type, dan Status wajib diisi!';
    } else {
        $stmt = $pdo->prepare('INSERT INTO projects (project_id, pic, assignment, project_information, req_pic, hotel_name, project_name, start_date, end_date, total_days, type, status, handover_official_report, handover_days, ketertiban_admin, point_ach, point_req, percent_point, month, quarter, week_no, s1_estimation_kpi2, s1_over_days, s1_count_of_emails_sent, s2_email_sent, s3_email_sent) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)');
        $stmt->execute([$project_id, $pic, $assignment, $project_information, $req_pic, $hotel_name, $project_name, $start_date, $end_date, $total_days, $type, $status, $handover_official_report, $handover_days, $ketertiban_admin, $point_ach, $point_req, $percent_point, $month, $quarter, $week_no, $s1_estimation_kpi2, $s1_over_days, $s1_count_of_emails_sent, $s2_email_sent, $s3_email_sent]);
        $message = 'Project created!';
        log_activity('create_project', 'Project ID: ' . $project_id);
    }
}

// SEARCH & FILTER
$search = trim($_GET['search'] ?? '');
$filter_type = $_GET['filter_type'] ?? '';
$filter_status = $_GET['filter_status'] ?? '';

$where = [];
$params = [];
if ($search) {
    $where[] = "(p.project_id ILIKE :search OR p.project_name ILIKE :search)";
    $params['search'] = "%$search%";
}
if ($filter_type) {
    $where[] = "p.type = :type";
    $params['type'] = $filter_type;
}
if ($filter_status) {
    $where[] = "p.status = :status";
    $params['status'] = $filter_status;
}
$where_sql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

// Pagination
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Total data
$sql_count = "SELECT COUNT(*) FROM projects p $where_sql";
$stmt_count = $pdo->prepare($sql_count);
$stmt_count->execute($params);
$total_data = $stmt_count->fetchColumn();
$total_pages = max(1, ceil($total_data / $per_page));

// Data page
$sql = "SELECT p.*, u.display_name as pic_name, c.name as hotel_name_disp FROM projects p LEFT JOIN users u ON p.pic=u.id LEFT JOIN customers c ON p.hotel_name=c.id $where_sql ORDER BY p.id DESC LIMIT $per_page OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

// UPDATE PROJECT
if (isset($_POST['update'])) {
    csrf_check();
    $id = $_POST['id'];
    $project_id = trim($_POST['project_id'] ?? '');
    $pic = $_POST['pic'] ?? null;
    $assignment = $_POST['assignment'] ?? null;
    $project_information = $_POST['project_information'] ?? null;
    $req_pic = $_POST['req_pic'] ?? null;
    $hotel_name = $_POST['hotel_name'] ?? null;
    $project_name = trim($_POST['project_name'] ?? '');
    $start_date = $_POST['start_date'] ?? null;
    $end_date = $_POST['end_date'] ?? null;
    $type = $_POST['type'] ?? null;
    $status = $_POST['status'] ?? null;
    $handover_official_report = $_POST['handover_official_report'] ?? null;
    $point_ach = $_POST['point_ach'] ?? null;
    $point_req = $_POST['point_req'] ?? null;
    $s1_estimation_kpi2 = $_POST['s1_estimation_kpi2'] ?? null;
    $s1_over_days = $_POST['s1_over_days'] ?? null;
    $s1_count_of_emails_sent = $_POST['s1_count_of_emails_sent'] ?? null;
    $s2_email_sent = $_POST['s2_email_sent'] ?? null;
    $s3_email_sent = $_POST['s3_email_sent'] ?? null;

    $total_days = ($start_date && $end_date) ? (strtotime($end_date) - strtotime($start_date))/86400 + 1 : null;
    $handover_days = ($end_date && $handover_official_report) ? (strtotime($handover_official_report) - strtotime($end_date))/86400 : null;
    if ($handover_days !== null) {
        if ($handover_days <= 3) $ketertiban_admin = 'Excellent';
        elseif ($handover_days <= 7) $ketertiban_admin = 'Good';
        elseif ($handover_days <= 14) $ketertiban_admin = 'Average';
        elseif ($handover_days <= 30) $ketertiban_admin = 'Poor';
        else $ketertiban_admin = 'Bad';
    } else {
        $ketertiban_admin = '';
    }
    $percent_point = ($point_ach && $point_req && $point_req != 0) ? round($point_ach/$point_req*100,2) : null;
    $month = getMonthName($start_date);
    $quarter = getQuarter($start_date);
    $week_no = $start_date ? (int)date('W', strtotime($start_date)) : null;

    $stmt = $pdo->prepare('UPDATE projects SET project_id=?, pic=?, assignment=?, project_information=?, req_pic=?, hotel_name=?, project_name=?, start_date=?, end_date=?, total_days=?, type=?, status=?, handover_official_report=?, handover_days=?, ketertiban_admin=?, point_ach=?, point_req=?, percent_point=?, month=?, quarter=?, week_no=?, s1_estimation_kpi2=?, s1_over_days=?, s1_count_of_emails_sent=?, s2_email_sent=?, s3_email_sent=? WHERE id=?');
    $stmt->execute([$project_id, $pic, $assignment, $project_information, $req_pic, $hotel_name, $project_name, $start_date, $end_date, $total_days, $type, $status, $handover_official_report, $handover_days, $ketertiban_admin, $point_ach, $point_req, $percent_point, $month, $quarter, $week_no, $s1_estimation_kpi2, $s1_over_days, $s1_count_of_emails_sent, $s2_email_sent, $s3_email_sent, $id]);
    $message = 'Project updated!';
    log_activity('update_project', 'Project ID: ' . $project_id);
    // Notifikasi email jika status project menjadi Done
    if ($status === 'Done') {
        $admin_email = 'your_admin_email@gmail.com'; // Ganti dengan email admin
        $subject = 'Project Selesai';
        $body = '<b>Project selesai:</b><br>Project ID: ' . htmlspecialchars($project_id) . '<br>Nama: ' . htmlspecialchars($project_name) . '<br>Hotel: ' . htmlspecialchars($hotel_name) . '<br>Tanggal Selesai: ' . htmlspecialchars($end_date);
        send_email($admin_email, $subject, $body);
    }
}

// DELETE PROJECT
if (isset($_POST['delete'])) {
    csrf_check();
    $id = $_POST['id'];
    $stmt = $pdo->prepare('DELETE FROM projects WHERE id=?');
    $stmt->execute([$id]);
    $message = 'Project deleted!';
    log_activity('delete_project', 'Project ID: ' . $id);
}
?>

<?php include './partials/layouts/layoutTop.php'; ?>

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
                <div class="card-body">
                    <?php if ($message): ?>
                        <div class="alert alert-info"> <?= htmlspecialchars($message) ?> </div>
                    <?php endif; ?>

<h2>Tambah Project</h2>
<form method="post">
    <?= csrf_field() ?>
    Project ID: <input type="text" name="project_id" required><br>
    PIC: <select name="pic">
        <option value="">-</option>
        <?php foreach ($users as $u): ?>
            <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['display_name']) ?></option>
        <?php endforeach; ?>
    </select><br>
    Assignment: <select name="assignment">
        <option value="">-</option>
        <option value="Leader">Leader</option>
        <option value="Assist">Assist</option>
    </select><br>
    Project Information: <select name="project_information">
        <option value="">-</option>
        <option value="Request">Request</option>
        <option value="Submission">Submission</option>
    </select><br>
    Req PIC: <select name="req_pic">
        <option value="">-</option>
        <option value="Request">Request</option>
        <option value="Assignment">Assignment</option>
    </select><br>
    Hotel Name: <select name="hotel_name" required>
        <option value="">-</option>
        <?php foreach ($customers as $c): ?>
            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
        <?php endforeach; ?>
    </select><br>
    Project Name: <input type="text" name="project_name"><br>
    Start Date: <input type="date" name="start_date" required><br>
    End Date: <input type="date" name="end_date"><br>
    Type: <select name="type" required>
        <option value="">-</option>
        <option value="Implementation">Implementation</option>
        <option value="Upgrade">Upgrade</option>
        <option value="Maintenance">Maintenance</option>
        <option value="Retraining">Retraining</option>
        <option value="On Line Training">On Line Training</option>
        <option value="On Line Maintenance">On Line Maintenance</option>
        <option value="Remote Installation">Remote Installation</option>
        <option value="In House Training">In House Training</option>
        <option value="Special Request">Special Request</option>
        <option value="2nd Implementation">2nd Implementation</option>
        <option value="Jakarta Support">Jakarta Support</option>
        <option value="Bali Support">Bali Support</option>
        <option value="Others">Others</option>
    </select><br>
    Status: <select name="status" required>
        <option value="">-</option>
        <option value="Scheduled">Scheduled</option>
        <option value="Running">Running</option>
        <option value="Document">Document</option>
        <option value="Document Check">Document Check</option>
        <option value="Done">Done</option>
        <option value="Cancel">Cancel</option>
        <option value="Rejected">Rejected</option>
    </select><br>
    Handover Official Report: <input type="date" name="handover_official_report"><br>
    Point Ach: <input type="number" name="point_ach"><br>
    Point Req: <input type="number" name="point_req"><br>
    S1: Estimation KPI.2: <input type="text" name="s1_estimation_kpi2"><br>
    S1: Over Day(s): <input type="text" name="s1_over_days"><br>
    S1: Count of Email(s) Sent: <input type="text" name="s1_count_of_emails_sent"><br>
    S2: Email Sent: <input type="text" name="s2_email_sent"><br>
    S3: Email Sent: <input type="text" name="s3_email_sent"><br>
    <button type="submit" name="create">Create</button>
</form>

<h2>Daftar Project</h2>
<a href="export_project_excel.php" style="display:inline-block;margin-bottom:8px;padding:6px 16px;background:#4caf50;color:#fff;text-decoration:none;border-radius:4px;">Export Excel</a>
<form method="get" style="margin-bottom:16px;display:inline-block;margin-left:16px;">
    <input type="text" name="search" placeholder="Cari Project ID/Name..." value="<?= htmlspecialchars($search) ?>">
    <select name="filter_type">
        <option value="">Semua Type</option>
        <option value="Implementation" <?= $filter_type==='Implementation'?'selected':'' ?>>Implementation</option>
        <option value="Upgrade" <?= $filter_type==='Upgrade'?'selected':'' ?>>Upgrade</option>
        <option value="Maintenance" <?= $filter_type==='Maintenance'?'selected':'' ?>>Maintenance</option>
        <option value="Retraining" <?= $filter_type==='Retraining'?'selected':'' ?>>Retraining</option>
        <option value="On Line Training" <?= $filter_type==='On Line Training'?'selected':'' ?>>On Line Training</option>
        <option value="On Line Maintenance" <?= $filter_type==='On Line Maintenance'?'selected':'' ?>>On Line Maintenance</option>
        <option value="Remote Installation" <?= $filter_type==='Remote Installation'?'selected':'' ?>>Remote Installation</option>
        <option value="In House Training" <?= $filter_type==='In House Training'?'selected':'' ?>>In House Training</option>
        <option value="Special Request" <?= $filter_type==='Special Request'?'selected':'' ?>>Special Request</option>
        <option value="2nd Implementation" <?= $filter_type==='2nd Implementation'?'selected':'' ?>>2nd Implementation</option>
        <option value="Jakarta Support" <?= $filter_type==='Jakarta Support'?'selected':'' ?>>Jakarta Support</option>
        <option value="Bali Support" <?= $filter_type==='Bali Support'?'selected':'' ?>>Bali Support</option>
        <option value="Others" <?= $filter_type==='Others'?'selected':'' ?>>Others</option>
    </select>
    <select name="filter_status">
        <option value="">Semua Status</option>
        <option value="Scheduled" <?= $filter_status==='Scheduled'?'selected':'' ?>>Scheduled</option>
        <option value="Running" <?= $filter_status==='Running'?'selected':'' ?>>Running</option>
        <option value="Document" <?= $filter_status==='Document'?'selected':'' ?>>Document</option>
        <option value="Document Check" <?= $filter_status==='Document Check'?'selected':'' ?>>Document Check</option>
        <option value="Done" <?= $filter_status==='Done'?'selected':'' ?>>Done</option>
        <option value="Cancel" <?= $filter_status==='Cancel'?'selected':'' ?>>Cancel</option>
        <option value="Rejected" <?= $filter_status==='Rejected'?'selected':'' ?>>Rejected</option>
    </select>
    <button type="submit">Cari</button>
    <a href="project_crud.php">Reset</a>
</form>
<div style="overflow-x:auto; max-width:100vw; margin-bottom:16px;">
<table border="1" cellpadding="4" style="min-width:900px;">
<tr><th>ID</th><th>Project ID</th><th>PIC</th><th>Assignment</th><th>Project Info</th><th>Req PIC</th><th>Hotel Name</th><th>Project Name</th><th>Start</th><th>End</th><th>Total Day(s)</th><th>Type</th><th>Status</th><th>Handover Report</th><th>Handover Day(s)</th><th>Ketertiban Admin</th><th>Point Ach</th><th>Point Req</th><th>% Point</th><th>Month</th><th>Quarter</th><th>Week #</th><th>Aksi</th></tr>
<?php foreach ($projects as $p): ?>
<tr>
    <form method="post">
    <?= csrf_field() ?>
    <td><?= $p['id'] ?><input type="hidden" name="id" value="<?= $p['id'] ?>"></td>
    <td><input type="text" name="project_id" value="<?= htmlspecialchars($p['project_id']) ?>" required></td>
    <td>
        <select name="pic">
            <option value="">-</option>
            <?php foreach ($users as $u): ?>
                <option value="<?= $u['id'] ?>" <?= $p['pic']==$u['id']?'selected':'' ?>><?= htmlspecialchars($u['display_name']) ?></option>
            <?php endforeach; ?>
        </select>
    </td>
    <td>
        <select name="assignment">
            <option value="">-</option>
            <option value="Leader" <?= $p['assignment']==='Leader'?'selected':'' ?>>Leader</option>
            <option value="Assist" <?= $p['assignment']==='Assist'?'selected':'' ?>>Assist</option>
        </select>
    </td>
    <td>
        <select name="project_information">
            <option value="">-</option>
            <option value="Request" <?= $p['project_information']==='Request'?'selected':'' ?>>Request</option>
            <option value="Submission" <?= $p['project_information']==='Submission'?'selected':'' ?>>Submission</option>
        </select>
    </td>
    <td>
        <select name="req_pic">
            <option value="">-</option>
            <option value="Request" <?= $p['req_pic']==='Request'?'selected':'' ?>>Request</option>
            <option value="Assignment" <?= $p['req_pic']==='Assignment'?'selected':'' ?>>Assignment</option>
        </select>
    </td>
    <td>
        <select name="hotel_name" required>
            <option value="">-</option>
            <?php foreach ($customers as $c): ?>
                <option value="<?= $c['id'] ?>" <?= $p['hotel_name']==$c['id']?'selected':'' ?>><?= htmlspecialchars($c['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </td>
    <td><input type="text" name="project_name" value="<?= htmlspecialchars($p['project_name']) ?>"></td>
    <td><input type="date" name="start_date" value="<?= htmlspecialchars($p['start_date']) ?>" required></td>
    <td><input type="date" name="end_date" value="<?= htmlspecialchars($p['end_date']) ?>"></td>
    <td><?= htmlspecialchars($p['total_days']) ?></td>
    <td>
        <select name="type" required>
            <option value="">-</option>
            <option value="Implementation" <?= $p['type']==='Implementation'?'selected':'' ?>>Implementation</option>
            <option value="Upgrade" <?= $p['type']==='Upgrade'?'selected':'' ?>>Upgrade</option>
            <option value="Maintenance" <?= $p['type']==='Maintenance'?'selected':'' ?>>Maintenance</option>
            <option value="Retraining" <?= $p['type']==='Retraining'?'selected':'' ?>>Retraining</option>
            <option value="On Line Training" <?= $p['type']==='On Line Training'?'selected':'' ?>>On Line Training</option>
            <option value="On Line Maintenance" <?= $p['type']==='On Line Maintenance'?'selected':'' ?>>On Line Maintenance</option>
            <option value="Remote Installation" <?= $p['type']==='Remote Installation'?'selected':'' ?>>Remote Installation</option>
            <option value="In House Training" <?= $p['type']==='In House Training'?'selected':'' ?>>In House Training</option>
            <option value="Special Request" <?= $p['type']==='Special Request'?'selected':'' ?>>Special Request</option>
            <option value="2nd Implementation" <?= $p['type']==='2nd Implementation'?'selected':'' ?>>2nd Implementation</option>
            <option value="Jakarta Support" <?= $p['type']==='Jakarta Support'?'selected':'' ?>>Jakarta Support</option>
            <option value="Bali Support" <?= $p['type']==='Bali Support'?'selected':'' ?>>Bali Support</option>
            <option value="Others" <?= $p['type']==='Others'?'selected':'' ?>>Others</option>
        </select>
    </td>
    <td>
        <select name="status" required>
            <option value="">-</option>
            <option value="Scheduled" <?= $p['status']==='Scheduled'?'selected':'' ?>>Scheduled</option>
            <option value="Running" <?= $p['status']==='Running'?'selected':'' ?>>Running</option>
            <option value="Document" <?= $p['status']==='Document'?'selected':'' ?>>Document</option>
            <option value="Document Check" <?= $p['status']==='Document Check'?'selected':'' ?>>Document Check</option>
            <option value="Done" <?= $p['status']==='Done'?'selected':'' ?>>Done</option>
            <option value="Cancel" <?= $p['status']==='Cancel'?'selected':'' ?>>Cancel</option>
            <option value="Rejected" <?= $p['status']==='Rejected'?'selected':'' ?>>Rejected</option>
        </select>
    </td>
    <td><input type="date" name="handover_official_report" value="<?= htmlspecialchars($p['handover_official_report']) ?>"></td>
    <td><?= htmlspecialchars($p['handover_days']) ?></td>
    <td><?= htmlspecialchars($p['ketertiban_admin']) ?></td>
    <td><input type="number" name="point_ach" value="<?= htmlspecialchars($p['point_ach']) ?>"></td>
    <td><input type="number" name="point_req" value="<?= htmlspecialchars($p['point_req']) ?>"></td>
    <td><?= htmlspecialchars($p['percent_point']) ?></td>
    <td><?= htmlspecialchars($p['month']) ?></td>
    <td><?= htmlspecialchars($p['quarter']) ?></td>
    <td><?= htmlspecialchars($p['week_no']) ?></td>
    <td>
        <button type="submit" name="update">Update</button>
        <button type="submit" name="delete" onclick="return confirm('Delete project?')">Delete</button>
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
                </div>
            </div>
        </div>

<?php include './partials/layouts/layoutBottom.php'; ?>
