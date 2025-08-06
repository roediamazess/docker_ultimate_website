<?php
session_start();
require_once 'db.php';

// Proteksi akses: hanya admin/management
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['Administrator', 'Management'])) {
    header('Location: login.php');
    exit;
}

// Filter
$search = trim($_GET['search'] ?? '');
$filter_action = $_GET['filter_action'] ?? '';
$filter_user = $_GET['filter_user'] ?? '';
$filter_date = $_GET['filter_date'] ?? '';

$where = [];
$params = [];
if ($search) {
    $where[] = "(description ILIKE :search OR user_email ILIKE :search)";
    $params['search'] = "%$search%";
}
if ($filter_action) {
    $where[] = "action = :action";
    $params['action'] = $filter_action;
}
if ($filter_user) {
    $where[] = "user_email = :user_email";
    $params['user_email'] = $filter_user;
}
if ($filter_date) {
    $where[] = "DATE(created_at) = :date";
    $params['date'] = $filter_date;
}
$where_sql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

// Pagination
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 30;
$offset = ($page - 1) * $per_page;
$sql_count = "SELECT COUNT(*) FROM logs $where_sql";
$stmt_count = $pdo->prepare($sql_count);
$stmt_count->execute($params);
$total_data = $stmt_count->fetchColumn();
$total_pages = max(1, ceil($total_data / $per_page));

$sql = "SELECT * FROM logs $where_sql ORDER BY id DESC LIMIT $per_page OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Data untuk filter user dan action
$user_emails = $pdo->query('SELECT DISTINCT user_email FROM logs WHERE user_email IS NOT NULL ORDER BY user_email')->fetchAll(PDO::FETCH_COLUMN);
$actions = $pdo->query('SELECT DISTINCT action FROM logs ORDER BY action')->fetchAll(PDO::FETCH_COLUMN);
?>
<?php include './partials/layouts/layoutTop.php'; ?>
<div class="container">
<h2>Audit Log</h2>
<form method="get" style="margin-bottom:16px;">
    <input type="text" name="search" placeholder="Cari deskripsi/user..." value="<?= htmlspecialchars($search) ?>">
    <select name="filter_action">
        <option value="">Semua Action</option>
        <?php foreach ($actions as $a): ?>
            <option value="<?= htmlspecialchars($a) ?>" <?= $filter_action===$a?'selected':'' ?>><?= htmlspecialchars($a) ?></option>
        <?php endforeach; ?>
    </select>
    <select name="filter_user">
        <option value="">Semua User</option>
        <?php foreach ($user_emails as $u): ?>
            <option value="<?= htmlspecialchars($u) ?>" <?= $filter_user===$u?'selected':'' ?>><?= htmlspecialchars($u) ?></option>
        <?php endforeach; ?>
    </select>
    <input type="date" name="filter_date" value="<?= htmlspecialchars($filter_date) ?>">
    <button type="submit">Cari</button>
    <a href="log_view.php">Reset</a>
</form>
<table border="1" cellpadding="4">
<tr><th>Waktu</th><th>User</th><th>Action</th><th>Deskripsi</th><th>IP</th><th>User Agent</th></tr>
<?php foreach ($logs as $log): ?>
<tr>
    <td><?= htmlspecialchars($log['created_at']) ?></td>
    <td><?= htmlspecialchars($log['user_email']) ?></td>
    <td><?= htmlspecialchars($log['action']) ?></td>
    <td><?= htmlspecialchars($log['description']) ?></td>
    <td><?= htmlspecialchars($log['ip']) ?></td>
    <td><span title="<?= htmlspecialchars($log['user_agent']) ?>"><?= substr(htmlspecialchars($log['user_agent']),0,40) ?>...</span></td>
</tr>
<?php endforeach; ?>
</table>
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
<?php include './partials/layouts/layoutBottom.php'; ?>
