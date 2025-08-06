<?php
session_start();
require_once 'db.php';
require_once 'csrf.php';
require_once 'log.php';
require_once 'send_email.php';

// Proteksi akses: hanya user login dengan role Administrator
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
if ($_SESSION['user_role'] !== 'Administrator') {
    header('Location: dashboard.php');
    exit;
}

// Pesan notifikasi
$message = '';

// CREATE USER
date_default_timezone_set('Asia/Jakarta');
if (isset($_POST['create'])) {
    csrf_check();
    $display_name = trim($_POST['display_name'] ?? '');
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $tier = $_POST['tier'] ?? null;
    $role = $_POST['role'] ?? null;
    $start_work = $_POST['start_work'] ?? null;
    $password_raw = $_POST['password'] ?? '';

    // Validasi mandatory
    if (!$full_name || !$email || !$password_raw) {
        $message = 'Full Name, Email, dan Password wajib diisi!';
    } else {
        // Cek email unik
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetchColumn() > 0) {
            $message = 'Email sudah terdaftar!';
        } else {
            $password = password_hash($password_raw, PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (display_name, full_name, email, tier, role, start_work, password) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$display_name, $full_name, $email, $tier, $role, $start_work, $password]);
            $message = 'User created!';
            log_activity('create_user', 'User: ' . $email);
            // Notifikasi email ke admin
            $admin_email = 'your_admin_email@gmail.com'; // Ganti dengan email admin
            $subject = 'User Baru Didaftarkan';
            $body = '<b>User baru telah dibuat:</b><br>Email: ' . htmlspecialchars($email) . '<br>Nama: ' . htmlspecialchars($full_name) . '<br>Role: ' . htmlspecialchars($role);
            send_email($admin_email, $subject, $body);
        }
    }
}

// SEARCH & FILTER
$search = trim($_GET['search'] ?? '');
$filter_role = $_GET['filter_role'] ?? '';
$filter_tier = $_GET['filter_tier'] ?? '';

$where = [];
$params = [];
if ($search) {
    $where[] = "(display_name ILIKE :search OR full_name ILIKE :search OR email ILIKE :search)";
    $params['search'] = "%$search%";
}
if ($filter_role) {
    $where[] = "role = :role";
    $params['role'] = $filter_role;
}
if ($filter_tier) {
    $where[] = "tier = :tier";
    $params['tier'] = $filter_tier;
}
$where_sql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

// Pagination
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Total data
$sql_count = "SELECT COUNT(*) FROM users $where_sql";
$stmt_count = $pdo->prepare($sql_count);
$stmt_count->execute($params);
$total_data = $stmt_count->fetchColumn();
$total_pages = max(1, ceil($total_data / $per_page));

// Data page
$sql = "SELECT id, display_name, full_name, email, tier, role, start_work FROM users $where_sql ORDER BY id DESC LIMIT $per_page OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// UPDATE USER
if (isset($_POST['update'])) {
    csrf_check();
    $id = $_POST['id'];
    $display_name = trim($_POST['display_name'] ?? '');
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $tier = $_POST['tier'] ?? null;
    $role = $_POST['role'] ?? null;
    $start_work = $_POST['start_work'] ?? null;
    $password_raw = $_POST['password'] ?? '';

    // Cek jika password diisi, update password juga
    if ($password_raw) {
        $password = password_hash($password_raw, PASSWORD_DEFAULT);
        $sql = "UPDATE users SET display_name=?, full_name=?, email=?, tier=?, role=?, start_work=?, password=? WHERE id=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$display_name, $full_name, $email, $tier, $role, $start_work, $password, $id]);
    } else {
        $sql = "UPDATE users SET display_name=?, full_name=?, email=?, tier=?, role=?, start_work=? WHERE id=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$display_name, $full_name, $email, $tier, $role, $start_work, $id]);
    }
    $message = 'User updated!';
    log_activity('update_user', 'User: ' . $email);
}

// DELETE USER
if (isset($_POST['delete'])) {
    csrf_check();
    $id = $_POST['id'];
    $sql = "DELETE FROM users WHERE id=?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    $message = 'User deleted!';
    log_activity('delete_user', 'User ID: ' . $id);
}
?>

<?php include './partials/layouts/layoutTop.php'; ?>

<div class="container">
<?php if ($message): ?>
    <div style="color: red; font-weight: bold;"> <?= htmlspecialchars($message) ?> </div>
<?php endif; ?>

<h2>Tambah User</h2>
<form method="post">
    <?= csrf_field() ?>
    Display Name: <input type="text" name="display_name"><br>
    Full Name: <input type="text" name="full_name" required><br>
    Email: <input type="email" name="email" required><br>
    Tier: <select name="tier">
        <option value="New Born">New Born</option>
        <option value="Tier 1">Tier 1</option>
        <option value="Tier 2">Tier 2</option>
        <option value="Tier 3">Tier 3</option>
    </select><br>
    Role: <select name="role">
        <option value="Administrator">Administrator</option>
        <option value="Management">Management</option>
        <option value="Admin Office">Admin Office</option>
        <option value="User">User</option>
        <option value="Client">Client</option>
    </select><br>
    Start Work: <input type="date" name="start_work"><br>
    Password: <input type="password" name="password" required><br>
    <button type="submit" name="create">Create</button>
</form>

<h2>Daftar User</h2>
<a href="export_user_excel.php" style="display:inline-block;margin-bottom:8px;padding:6px 16px;background:#4caf50;color:#fff;text-decoration:none;border-radius:4px;">Export Excel</a>
<form method="get" style="margin-bottom:16px;display:inline-block;margin-left:16px;">
    <input type="text" name="search" placeholder="Cari nama/email..." value="<?= htmlspecialchars($search) ?>">
    <select name="filter_role">
        <option value="">Semua Role</option>
        <option value="Administrator" <?= $filter_role==='Administrator'?'selected':'' ?>>Administrator</option>
        <option value="Management" <?= $filter_role==='Management'?'selected':'' ?>>Management</option>
        <option value="Admin Office" <?= $filter_role==='Admin Office'?'selected':'' ?>>Admin Office</option>
        <option value="User" <?= $filter_role==='User'?'selected':'' ?>>User</option>
        <option value="Client" <?= $filter_role==='Client'?'selected':'' ?>>Client</option>
    </select>
    <select name="filter_tier">
        <option value="">Semua Tier</option>
        <option value="New Born" <?= $filter_tier==='New Born'?'selected':'' ?>>New Born</option>
        <option value="Tier 1" <?= $filter_tier==='Tier 1'?'selected':'' ?>>Tier 1</option>
        <option value="Tier 2" <?= $filter_tier==='Tier 2'?'selected':'' ?>>Tier 2</option>
        <option value="Tier 3" <?= $filter_tier==='Tier 3'?'selected':'' ?>>Tier 3</option>
    </select>
    <button type="submit">Cari</button>
    <a href="user_crud.php">Reset</a>
</form>
<div style="overflow-x:auto; max-width:100vw; margin-bottom:16px;">
<table border="1" cellpadding="4" style="min-width:700px;">
<tr><th>ID</th><th>Display Name</th><th>Full Name</th><th>Email</th><th>Tier</th><th>Role</th><th>Start Work</th><th>Password</th><th>Aksi</th></tr>
<?php foreach ($users as $u): ?>
<tr>
    <form method="post">
    <?= csrf_field() ?>
    <td><?= $u['id'] ?><input type="hidden" name="id" value="<?= $u['id'] ?>"></td>
    <td><input type="text" name="display_name" value="<?= htmlspecialchars($u['display_name']) ?>"></td>
    <td><input type="text" name="full_name" value="<?= htmlspecialchars($u['full_name']) ?>" required></td>
    <td><input type="email" name="email" value="<?= htmlspecialchars($u['email']) ?>" required></td>
    <td>
        <select name="tier">
            <option value="New Born" <?= $u['tier']==='New Born'?'selected':'' ?>>New Born</option>
            <option value="Tier 1" <?= $u['tier']==='Tier 1'?'selected':'' ?>>Tier 1</option>
            <option value="Tier 2" <?= $u['tier']==='Tier 2'?'selected':'' ?>>Tier 2</option>
            <option value="Tier 3" <?= $u['tier']==='Tier 3'?'selected':'' ?>>Tier 3</option>
        </select>
    </td>
    <td>
        <select name="role">
            <option value="Administrator" <?= $u['role']==='Administrator'?'selected':'' ?>>Administrator</option>
            <option value="Management" <?= $u['role']==='Management'?'selected':'' ?>>Management</option>
            <option value="Admin Office" <?= $u['role']==='Admin Office'?'selected':'' ?>>Admin Office</option>
            <option value="User" <?= $u['role']==='User'?'selected':'' ?>>User</option>
            <option value="Client" <?= $u['role']==='Client'?'selected':'' ?>>Client</option>
        </select>
    </td>
    <td><input type="date" name="start_work" value="<?= htmlspecialchars($u['start_work']) ?>"></td>
    <td><input type="password" name="password" placeholder="Isi jika ingin ganti"></td>
    <td>
        <button type="submit" name="update">Update</button>
        <button type="submit" name="delete" onclick="return confirm('Delete user?')">Delete</button>
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

<?php include './partials/layouts/layoutBottom.php'; ?>
