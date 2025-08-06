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

// Create Customer
if (isset($_POST['create'])) {
    if (!csrf_verify()) {
        $message = 'CSRF token tidak valid!';
    } else {
        $stmt = $pdo->prepare('INSERT INTO customers (customer_id, name, star, room, outlet, type, "group", zone, address, billing, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            $_POST['customer_id'],
            $_POST['name'],
            $_POST['star'] ?: null,
            $_POST['room'],
            $_POST['outlet'],
            $_POST['type'] ?: null,
            $_POST['group'],
            $_POST['zone'],
            $_POST['address'],
            $_POST['billing'] ?: null,
            date('Y-m-d H:i:s')
        ]);
        $message = 'Customer created!';
        log_activity('create_customer', 'Customer ID: ' . $_POST['customer_id']);
    }
}

// Update Customer
if (isset($_POST['update'])) {
    if (!csrf_verify()) {
        $message = 'CSRF token tidak valid!';
    } else {
        $stmt = $pdo->prepare('UPDATE customers SET customer_id=?, name=?, star=?, room=?, outlet=?, type=?, "group"=?, zone=?, address=?, billing=? WHERE id=?');
        $stmt->execute([
            $_POST['customer_id'],
            $_POST['name'],
            $_POST['star'] ?: null,
            $_POST['room'],
            $_POST['outlet'],
            $_POST['type'] ?: null,
            $_POST['group'],
            $_POST['zone'],
            $_POST['address'],
            $_POST['billing'] ?: null,
            $_POST['id']
        ]);
        $message = 'Customer updated!';
        log_activity('update_customer', 'Customer ID: ' . $_POST['id']);
    }
}

// Delete Customer
if (isset($_POST['delete'])) {
    if (!csrf_verify()) {
        $message = 'CSRF token tidak valid!';
    } else {
        $id = $_POST['id'];
        $stmt = $pdo->prepare('DELETE FROM customers WHERE id = ?');
        $stmt->execute([$id]);
        $message = 'Customer deleted!';
        log_activity('delete_customer', 'Customer ID: ' . $id);
    }
}

// Pagination dan filtering
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 10;
$offset = ($page - 1) * $limit;

$search = trim($_GET['search'] ?? '');
$filter_type = $_GET['filter_type'] ?? '';
$filter_billing = $_GET['filter_billing'] ?? '';

$where_conditions = [];
$params = [];

if ($search) {
    $where_conditions[] = "(customer_id ILIKE ? OR name ILIKE ?)";
    $search_term = "%$search%";
    $params = array_merge($params, [$search_term, $search_term]);
}

if ($filter_type) {
    $where_conditions[] = "type = ?";
    $params[] = $filter_type;
}

if ($filter_billing) {
    $where_conditions[] = "billing = ?";
    $params[] = $filter_billing;
}

$where_clause = $where_conditions ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Get total count
$count_sql = "SELECT COUNT(*) FROM customers $where_clause";
$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute($params);
$total_customers = $count_stmt->fetchColumn();
$total_pages = ceil($total_customers / $limit);

// Get customers with pagination
$sql = "SELECT * FROM customers $where_clause ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include './partials/layouts/layoutTop.php'; ?>

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
                        <select class="form-select form-select-sm w-auto" name="filter_type">
                            <option value="">All Types</option>
                            <option value="Hotel" <?= $filter_type === 'Hotel' ? 'selected' : '' ?>>Hotel</option>
                            <option value="Restaurant" <?= $filter_type === 'Restaurant' ? 'selected' : '' ?>>Restaurant</option>
                            <option value="Office" <?= $filter_type === 'Office' ? 'selected' : '' ?>>Office</option>
                        </select>
                        <a href="#" onclick="showCreateForm()" class="btn btn-sm btn-primary-600"><i class="ri-add-line"></i> Create Customer</a>
                    </div>
                </div>
                <div class="card-body">
<?php if ($message): ?>
                        <div class="alert alert-info"> <?= htmlspecialchars($message) ?> </div>
<?php endif; ?>

                    <!-- Create Customer Form (Hidden by default) -->
                    <div id="createCustomerForm" style="display:none; margin-bottom:24px; padding:20px; border:1px solid #ddd; border-radius:8px; background:#f9f9f9;">
                        <h5 class="mb-3">Add New Customer</h5>
                        <form method="post" class="row g-3">
    <?= csrf_field() ?>
                            <div class="col-md-6">
                                <label class="form-label">Customer ID *</label>
                                <input type="text" name="customer_id" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Name *</label>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Star</label>
                                <select name="star" class="form-select">
        <option value="">-</option>
                                    <option value="1">1 Star</option>
                                    <option value="2">2 Star</option>
                                    <option value="3">3 Star</option>
                                    <option value="4">4 Star</option>
                                    <option value="5">5 Star</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Room</label>
                                <input type="text" name="room" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Outlet</label>
                                <input type="text" name="outlet" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Type</label>
                                <select name="type" class="form-select">
        <option value="">-</option>
        <option value="Hotel">Hotel</option>
        <option value="Restaurant">Restaurant</option>
                                    <option value="Office">Office</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Group</label>
                                <input type="text" name="group" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Zone</label>
                                <input type="text" name="zone" class="form-control">
                            </div>
                            <div class="col-md-9">
                                <label class="form-label">Address</label>
                                <textarea name="address" class="form-control" rows="2"></textarea>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Billing</label>
                                <select name="billing" class="form-select">
        <option value="">-</option>
                                    <option value="Monthly">Monthly</option>
                                    <option value="Quarterly">Quarterly</option>
                                    <option value="Annually">Annually</option>
    </select>
                            </div>
                            <div class="col-12">
                                <button type="submit" name="create" class="btn btn-primary">Add Customer</button>
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
                                <th scope="col">Customer</th>
                                <th scope="col">Type</th>
                                <th scope="col">Star</th>
                                <th scope="col">Address</th>
                                <th scope="col">Billing</th>
                                <th scope="col">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($customers as $index => $c): ?>
                            <tr>
                                <td>
                                    <div class="form-check style-check d-flex align-items-center">
                                        <input class="form-check-input" type="checkbox" value="<?= $c['id'] ?>" id="check<?= $c['id'] ?>">
                                        <label class="form-check-label" for="check<?= $c['id'] ?>">
                                            <?= str_pad($index + 1 + $offset, 2, '0', STR_PAD_LEFT) ?>
                                        </label>
                                    </div>
    </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="assets/images/avatar/avatar2.png" alt="" class="flex-shrink-0 me-12 radius-8" style="width:40px;height:40px;">
                                        <div class="flex-grow-1">
                                            <h6 class="text-md mb-0 fw-medium"><?= htmlspecialchars($c['name']) ?></h6>
                                            <span class="text-sm text-secondary-light"><?= htmlspecialchars($c['customer_id']) ?></span>
                                        </div>
                                    </div>
    </td>
                                <td><?= $c['type'] ? '<span class="bg-info-focus text-info-main px-8 py-4 rounded-pill fw-medium text-sm">' . htmlspecialchars($c['type']) . '</span>' : '-' ?></td>
                                <td><?= $c['star'] ? str_repeat('⭐', $c['star']) : '-' ?></td>
                                <td><?= htmlspecialchars($c['address'] ?: '-') ?></td>
                                <td><?= $c['billing'] ? '<span class="bg-success-focus text-success-main px-8 py-4 rounded-pill fw-medium text-sm">' . htmlspecialchars($c['billing']) . '</span>' : '-' ?></td>
                                <td>
                                    <a href="javascript:void(0)" onclick="editCustomer(<?= $c['id'] ?>)" class="w-32-px h-32-px bg-success-focus text-success-main rounded-circle d-inline-flex align-items-center justify-content-center">
                                        <iconify-icon icon="lucide:edit"></iconify-icon>
                                    </a>
                                    <a href="javascript:void(0)" onclick="deleteCustomer(<?= $c['id'] ?>)" class="w-32-px h-32-px bg-danger-focus text-danger-main rounded-circle d-inline-flex align-items-center justify-content-center">
                                        <iconify-icon icon="mingcute:delete-2-line"></iconify-icon>
                                    </a>
    </td>
</tr>
<?php endforeach; ?>
                        </tbody>
</table>

<!-- Pagination -->
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mt-24">
                        <span class="text-md text-secondary-light fw-normal">Showing <?= count($customers) ?> of <?= $total_customers ?> results</span>
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
    document.getElementById('createCustomerForm').style.display = 'block';
}

function hideCreateForm() {
    document.getElementById('createCustomerForm').style.display = 'none';
}

function editCustomer(customerId) {
    alert('Edit customer functionality to be implemented');
}

function deleteCustomer(customerId) {
    if (confirm('Are you sure you want to delete this customer?')) {
        const form = document.createElement('form');
        form.method = 'post';
        form.innerHTML = `
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="${customerId}">
            <input type="hidden" name="delete" value="1">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<?php include './partials/layouts/layoutBottom.php'; ?>