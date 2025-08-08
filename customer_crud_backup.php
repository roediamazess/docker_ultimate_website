<?php
session_start();
require_once 'db.php';
require_once 'csrf.php';
require_once 'log.php';

// Proteksi akses hanya untuk Administrator & Management
if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['Administrator', 'Management'])) {
    header('Location: login.php');
    exit;
}

$message = '';

// CREATE CUSTOMER
if (isset($_POST['create'])) {
    csrf_check();
    $customer_id = trim($_POST['customer_id'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $star = $_POST['star'] ?? null;
    $room = trim($_POST['room'] ?? '');
    $outlet = trim($_POST['outlet'] ?? '');
    $type = $_POST['type'] ?? null;
    $group = trim($_POST['group'] ?? '');
    $zone = trim($_POST['zone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $billing = $_POST['billing'] ?? null;

    if (!$customer_id) {
        $message = 'Customer ID wajib diisi!';
    } else {
        $stmt = $pdo->prepare('INSERT INTO customers (customer_id, name, star, room, outlet, type, "group", zone, address, billing) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([$customer_id, $name, $star, $room, $outlet, $type, $group, $zone, $address, $billing]);
        $message = 'Customer created!';
        log_activity('create_customer', 'Customer ID: ' . $customer_id);
    }
}

// SEARCH & FILTER
$search = trim($_GET['search'] ?? '');
$filter_type = $_GET['filter_type'] ?? '';
$filter_billing = $_GET['filter_billing'] ?? '';

$where = [];
$params = [];
if ($search) {
    $where[] = "(customer_id ILIKE :search OR name ILIKE :search)";
    $params['search'] = "%$search%";
}
if ($filter_type) {
    $where[] = "type = :type";
    $params['type'] = $filter_type;
}
if ($filter_billing) {
    $where[] = "billing = :billing";
    $params['billing'] = $filter_billing;
}
$where_sql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

// Pagination
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Total data
$sql_count = "SELECT COUNT(*) FROM customers $where_sql";
$stmt_count = $pdo->prepare($sql_count);
$stmt_count->execute($params);
$total_data = $stmt_count->fetchColumn();
$total_pages = max(1, ceil($total_data / $per_page));

// Data page
$sql = "SELECT * FROM customers $where_sql ORDER BY id DESC LIMIT $per_page OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// UPDATE CUSTOMER
if (isset($_POST['update'])) {
    csrf_check();
    $id = $_POST['id'];
    $customer_id = trim($_POST['customer_id'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $star = $_POST['star'] ?? null;
    $room = trim($_POST['room'] ?? '');
    $outlet = trim($_POST['outlet'] ?? '');
    $type = $_POST['type'] ?? null;
    $group = trim($_POST['group'] ?? '');
    $zone = trim($_POST['zone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $billing = $_POST['billing'] ?? null;
    $stmt = $pdo->prepare('UPDATE customers SET customer_id=?, name=?, star=?, room=?, outlet=?, type=?, "group"=?, zone=?, address=?, billing=? WHERE id=?');
    $stmt->execute([$customer_id, $name, $star, $room, $outlet, $type, $group, $zone, $address, $billing, $id]);
    $message = 'Customer updated!';
    log_activity('update_customer', 'Customer ID: ' . $customer_id);
}

// DELETE CUSTOMER
if (isset($_POST['delete'])) {
    csrf_check();
    $id = $_POST['id'];
    $stmt = $pdo->prepare('DELETE FROM customers WHERE id=?');
    $stmt->execute([$id]);
    $message = 'Customer deleted!';
    log_activity('delete_customer', 'Customer ID: ' . $id);
}
?>

<?php include './partials/layouts/layoutHorizontal.php'; ?>

        <div class="dashboard-main-body">

            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
                <h6 class="fw-semibold mb-0">Customer List</h6>
                <ul class="d-flex align-items-center gap-2">
                    <li class="fw-medium">
                        <a href="index.php" class="d-flex align-items-center gap-1 hover-text-primary">
                            <iconify-icon icon="solar:home-smile-angle-outline" class="icon text-lg"></iconify-icon>
                            Dashboard
                        </a>
                    </li>
                    <li>-</li>
                    <li class="fw-medium">Customer List</li>
                </ul>
            </div>

            <div class="card">
                <div class="card-body">
                    <?php if ($message): ?>
                        <div class="alert alert-info"> <?= htmlspecialchars($message) ?> </div>
                    <?php endif; ?>

<h2>Tambah Customer</h2>
<form method="post">
    <?= csrf_field() ?>
    Customer ID: <input type="text" name="customer_id" required><br>
    Name: <input type="text" name="name"><br>
    Star: <select name="star">
        <option value="">-</option>
        <?php for ($i=1; $i<=6; $i++): ?>
            <option value="<?= $i ?>"><?= $i ?></option>
        <?php endfor; ?>
    </select><br>
    Room: <input type="text" name="room"><br>
    Outlet: <input type="text" name="outlet"><br>
    Type: <select name="type">
        <option value="">-</option>
        <option value="Hotel">Hotel</option>
        <option value="Restaurant">Restaurant</option>
        <option value="Head Quarter">Head Quarter</option>
        <option value="Education">Education</option>
    </select><br>
    Group: <input type="text" name="group"><br>
    Zone: <input type="text" name="zone"><br>
    Address: <input type="text" name="address"><br>
    Billing: <select name="billing">
        <option value="">-</option>
        <option value="Contract Maintenance">Contract Maintenance</option>
        <option value="Subscription">Subscription</option>
    </select><br>
    <button type="submit" name="create">Create</button>
</form>

<h2>Daftar Customer</h2>
<a href="export_customer_excel.php" style="display:inline-block;margin-bottom:8px;padding:6px 16px;background:#4caf50;color:#fff;text-decoration:none;border-radius:4px;">Export Excel</a>
<form method="get" style="margin-bottom:16px;display:inline-block;margin-left:16px;">
    <input type="text" name="search" placeholder="Cari Customer ID/Name..." value="<?= htmlspecialchars($search) ?>">
    <select name="filter_type">
        <option value="">Semua Type</option>
        <option value="Hotel" <?= $filter_type==='Hotel'?'selected':'' ?>>Hotel</option>
        <option value="Restaurant" <?= $filter_type==='Restaurant'?'selected':'' ?>>Restaurant</option>
        <option value="Head Quarter" <?= $filter_type==='Head Quarter'?'selected':'' ?>>Head Quarter</option>
        <option value="Education" <?= $filter_type==='Education'?'selected':'' ?>>Education</option>
    </select>
    <select name="filter_billing">
        <option value="">Semua Billing</option>
        <option value="Contract Maintenance" <?= $filter_billing==='Contract Maintenance'?'selected':'' ?>>Contract Maintenance</option>
        <option value="Subscription" <?= $filter_billing==='Subscription'?'selected':'' ?>>Subscription</option>
    </select>
    <button type="submit">Cari</button>
    <a href="customer_crud.php">Reset</a>
</form>
yes <div style="overflow-x:auto; max-width:100vw; margin-bottom:16px;">
<table border="1" cellpadding="4" style="min-width:700px;">
<tr><th>ID</th><th>Customer ID</th><th>Name</th><th>Star</th><th>Room</th><th>Outlet</th><th>Type</th><th>Group</th><th>Zone</th><th>Address</th><th>Billing</th><th>Aksi</th></tr>
<?php foreach ($customers as $c): ?>
<tr>
    <form method="post">
    <?= csrf_field() ?>
    <td><?= $c['id'] ?><input type="hidden" name="id" value="<?= $c['id'] ?>"></td>
    <td><input type="text" name="customer_id" value="<?= htmlspecialchars($c['customer_id']) ?>" required></td>
    <td><input type="text" name="name" value="<?= htmlspecialchars($c['name']) ?>"></td>
    <td>
        <select name="star">
            <option value="">-</option>
            <?php for ($i=1; $i<=6; $i++): ?>
                <option value="<?= $i ?>" <?= $c['star']==$i?'selected':'' ?>><?= $i ?></option>
            <?php endfor; ?>
        </select>
    </td>
    <td><input type="text" name="room" value="<?= htmlspecialchars($c['room']) ?>"></td>
    <td><input type="text" name="outlet" value="<?= htmlspecialchars($c['outlet']) ?>"></td>
    <td>
        <select name="type">
            <option value="">-</option>
            <option value="Hotel" <?= $c['type']==='Hotel'?'selected':'' ?>>Hotel</option>
            <option value="Restaurant" <?= $c['type']==='Restaurant'?'selected':'' ?>>Restaurant</option>
            <option value="Head Quarter" <?= $c['type']==='Head Quarter'?'selected':'' ?>>Head Quarter</option>
            <option value="Education" <?= $c['type']==='Education'?'selected':'' ?>>Education</option>
        </select>
    </td>
    <td><input type="text" name="group" value="<?= htmlspecialchars($c['group']) ?>"></td>
    <td><input type="text" name="zone" value="<?= htmlspecialchars($c['zone']) ?>"></td>
    <td><input type="text" name="address" value="<?= htmlspecialchars($c['address']) ?>"></td>
    <td>
        <select name="billing">
            <option value="">-</option>
            <option value="Contract Maintenance" <?= $c['billing']==='Contract Maintenance'?'selected':'' ?>>Contract Maintenance</option>
            <option value="Subscription" <?= $c['billing']==='Subscription'?'selected':'' ?>>Subscription</option>
        </select>
    </td>
    <td>
        <button type="submit" name="update">Update</button>
        <button type="submit" name="delete" onclick="return confirm('Delete customer?')">Delete</button>
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
