<?php
session_start();
require_once 'db.php';
require_once 'access_control.php';
require_once 'user_utils.php';

// Cek akses menggunakan utility function
require_login();

// Helper functions to update group counts
function updateGroupCount($pdo, $group_name, $direction) {
    if (empty($group_name)) {
        return;
    }
    $operator = $direction === 'increment' ? '+' : '-';
    try {
        $sql = "UPDATE hotel_groups SET customer_count = customer_count {$operator} 1 WHERE name = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$group_name]);
    } catch (PDOException $e) {
        // Log error or handle it silently
    }
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
$notification_type = '';

// Create Customer
if (isset($_POST['create'])) {
    if (csrf_verify()) {
        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare('INSERT INTO customers (customer_id, name, star, room, outlet, type, "group", zone, address, billing, status, email_gm, email_executive, email_hr, email_acc_head, email_chief_acc, email_cost_control, email_ap, email_ar, email_fb, email_fo, email_hk, email_engineering, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())');
            $stmt->execute([
                $_POST['customer_id'], $_POST['name'], $_POST['star'] ?: null, $_POST['room'], $_POST['outlet'] ?: 1,
                $_POST['type'] ?: null, $_POST['group'], $_POST['zone'], $_POST['address'], $_POST['billing'] ?: null,
                $_POST['status'] ?: 'Active', $_POST['email_gm'] ?: null, $_POST['email_executive'] ?: null,
                $_POST['email_hr'] ?: null, $_POST['email_acc_head'] ?: null, $_POST['email_chief_acc'] ?: null,
                $_POST['email_cost_control'] ?: null, $_POST['email_ap'] ?: null, $_POST['email_ar'] ?: null,
                $_POST['email_fb'] ?: null, $_POST['email_fo'] ?: null, $_POST['email_hk'] ?: null,
                $_POST['email_engineering'] ?: null
            ]);
            
            updateGroupCount($pdo, $_POST['group'], 'increment');
            
            $pdo->commit();
            $message = 'Customer berhasil dibuat!';
            $notification_type = 'created';
            log_activity('create_customer', 'Customer ID: ' . $_POST['customer_id']);
        } catch (PDOException $e) {
            $pdo->rollBack();
            $message = 'Error: ' . $e->getMessage();
            $notification_type = 'error';
        }
    }
}

// Update Customer
if (isset($_POST['update'])) {
    if (csrf_verify()) {
        try {
            $pdo->beginTransaction();

            $old_group_stmt = $pdo->prepare('SELECT "group" FROM customers WHERE id = ?');
            $old_group_stmt->execute([$_POST['id']]);
            $old_group = $old_group_stmt->fetchColumn();

            $stmt = $pdo->prepare('UPDATE customers SET customer_id=?, name=?, star=?, room=?, outlet=?, type=?, "group"=?, zone=?, address=?, billing=?, status=?, email_gm=?, email_executive=?, email_hr=?, email_acc_head=?, email_chief_acc=?, email_cost_control=?, email_ap=?, email_ar=?, email_fb=?, email_fo=?, email_hk=?, email_engineering=? WHERE id=?');
            $stmt->execute([
                $_POST['customer_id'], $_POST['name'], $_POST['star'] ?: null, $_POST['room'], $_POST['outlet'] ?: 1,
                $_POST['type'] ?: null, $_POST['group'], $_POST['zone'], $_POST['address'], $_POST['billing'] ?: null,
                $_POST['status'] ?: 'Active', $_POST['email_gm'] ?: null, $_POST['email_executive'] ?: null,
                $_POST['email_hr'] ?: null, $_POST['email_acc_head'] ?: null, $_POST['email_chief_acc'] ?: null,
                $_POST['email_cost_control'] ?: null, $_POST['email_ap'] ?: null, $_POST['email_ar'] ?: null,
                $_POST['email_fb'] ?: null, $_POST['email_fo'] ?: null, $_POST['email_hk'] ?: null,
                $_POST['email_engineering'] ?: null, $_POST['id']
            ]);

            $new_group = $_POST['group'];
            if ($old_group !== $new_group) {
                updateGroupCount($pdo, $old_group, 'decrement');
                updateGroupCount($pdo, $new_group, 'increment');
            }
            
            $pdo->commit();
            $message = 'Customer berhasil diperbarui!';
            $notification_type = 'updated';
            log_activity('update_customer', 'Customer ID: ' . $_POST['customer_id']);
        } catch (PDOException $e) {
            $pdo->rollBack();
            $message = 'Error: ' . $e->getMessage();
            $notification_type = 'error';
        }
    }
}

// Delete Customer
if (isset($_POST['delete'])) {
    if (csrf_verify()) {
        try {
            $pdo->beginTransaction();
            
            $old_group_stmt = $pdo->prepare('SELECT "group" FROM customers WHERE id = ?');
            $old_group_stmt->execute([$_POST['id']]);
            $old_group = $old_group_stmt->fetchColumn();

            $stmt = $pdo->prepare('DELETE FROM customers WHERE id = ?');
            $stmt->execute([$_POST['id']]);

            updateGroupCount($pdo, $old_group, 'decrement');

            $pdo->commit();
            $message = 'Customer berhasil dihapus!';
            $notification_type = 'deleted';
            log_activity('delete_customer', 'Customer ID: ' . $_POST['id']);
        } catch (PDOException $e) {
            $pdo->rollBack();
            $message = 'Error: ' . $e->getMessage();
            $notification_type = 'error';
        }
    }
}

// Pagination dan filtering
$page = max(1, intval($_GET['page'] ?? 1));
$limit = intval($_GET['limit'] ?? 10);
$offset = ($page - 1) * $limit;

$search = trim($_GET['search'] ?? '');
$filter_type = $_GET['filter_type'] ?? '';
$filter_billing = $_GET['filter_billing'] ?? '';
$filter_star = $_GET['filter_star'] ?? '';

$where_conditions = [];
$params = [];

if ($search) {
    $where_conditions[] = "(customer_id ILIKE ? OR name ILIKE ? OR address ILIKE ?)";
    $search_term = "%$search%";
    $params = array_merge($params, [$search_term, $search_term, $search_term]);
}

if ($filter_type) {
    $where_conditions[] = "type = ?";
    $params[] = $filter_type;
}

if ($filter_billing) {
    $where_conditions[] = "billing = ?";
    $params[] = $filter_billing;
}

if ($filter_star) {
    $where_conditions[] = "star = ?";
    $params[] = $filter_star;
}

$where_clause = $where_conditions ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Get total count
$count_sql = "SELECT COUNT(*) FROM customers $where_clause";
$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute($params);
$total_customers = $count_stmt->fetchColumn();
$total_pages = ceil($total_customers / $limit);

// Get customers with pagination
$sql = "SELECT c.* FROM customers c 
        $where_clause ORDER BY c.created_at DESC LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

$provinces = [
    'Aceh', 'Bali', 'Bangka Belitung', 'Banten', 'Bengkulu', 'DI Yogyakarta', 
    'DKI Jakarta', 'Gorontalo', 'Jambi', 'Jawa Barat', 'Jawa Tengah', 'Jawa Timur', 
    'Kalimantan Barat', 'Kalimantan Selatan', 'Kalimantan Tengah', 'Kalimantan Timur', 
    'Kalimantan Utara', 'Kepulauan Riau', 'Lampung', 'Maluku', 'Maluku Utara', 
    'Nusa Tenggara Barat', 'Nusa Tenggara Timur', 'Papua', 'Papua Barat', 
    'Papua Barat Daya', 'Papua Pegunungan', 'Papua Selatan', 'Papua Tengah', 'Riau', 
    'Sulawesi Barat', 'Sulawesi Selatan', 'Sulawesi Tengah', 'Sulawesi Tenggara', 
    'Sulawesi Utara', 'Sumatera Barat', 'Sumatera Selatan', 'Sumatera Utara'
];
sort($provinces);

// Fetch Hotel Groups
try {
    $hotel_groups_stmt = $pdo->query('SELECT * FROM hotel_groups ORDER BY name ASC');
    $hotel_groups = $hotel_groups_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $hotel_groups = []; // Default to empty array on error
}

// Get distinct values for filters
try {
    $types = $pdo->query('SELECT unnest(enum_range(NULL::customer_type)) ORDER BY 1')->fetchAll(PDO::FETCH_COLUMN);
    $billings = $pdo->query('SELECT unnest(enum_range(NULL::billing_type)) ORDER BY 1')->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    $types = [];
    $billings = [];
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
                <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div class="d-flex align-items-center gap-2">
                        <span class="fw-semibold">Show</span>
                        <select class="form-select form-select-sm w-auto" name="limit" onchange="this.form.submit()">
                            <option value="10" <?= $limit===10?'selected':''; ?>>10</option>
                            <option value="15" <?= $limit===15?'selected':''; ?>>15</option>
                            <option value="20" <?= $limit===20?'selected':''; ?>>20</option>
                        </select>
                    </div>
                    <button type="button" class="btn btn-sm btn-primary-600 d-flex align-items-center gap-2" id="createCustomerBtn" onclick="showCreateModal()">
                        <iconify-icon icon="solar:add-circle-outline" class="icon"></iconify-icon>
                        Create Customer
                    </button>
                </div>
                
                <!-- Filter Section -->
                <div class="filter-section">
                    <form method="get" class="filter-form">
                        <div class="filter-row">
                            <div class="filter-group">
                                <label class="filter-label">Search</label>
                                <div class="icon-field">
                                    <input type="text" name="search" class="form-control" placeholder="Search customers..." value="<?= htmlspecialchars($search) ?>">
                                    <span class="icon">
                                        <iconify-icon icon="ion:search-outline"></iconify-icon>
                                    </span>
                                </div>
                            </div>
                            <div class="filter-group">
                                <label class="filter-label">Type</label>
                                <select class="form-select" name="filter_type">
                                    <option value="">All Types</option>
                                    <?php foreach ($types as $type): ?>
                                        <option value="<?= htmlspecialchars($type) ?>" <?= $filter_type === $type ? 'selected' : '' ?>><?= htmlspecialchars($type) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="filter-group">
                                <label class="filter-label">Star</label>
                                <select class="form-select" name="filter_star">
                                    <option value="">All Stars</option>
                                    <option value="1" <?= $filter_star === '1' ? 'selected' : '' ?>>1 Star</option>
                                    <option value="2" <?= $filter_star === '2' ? 'selected' : '' ?>>2 Star</option>
                                    <option value="3" <?= $filter_star === '3' ? 'selected' : '' ?>>3 Star</option>
                                    <option value="4" <?= $filter_star === '4' ? 'selected' : '' ?>>4 Star</option>
                                    <option value="5" <?= $filter_star === '5' ? 'selected' : '' ?>>5 Star</option>
                                </select>
                            </div>
                            <div class="filter-group">
                                <label class="filter-label">Billing</label>
                                <select class="form-select" name="filter_billing">
                                    <option value="">All Billing</option>
                                    <?php foreach ($billings as $billing): ?>
                                        <option value="<?= htmlspecialchars($billing) ?>" <?= $filter_billing === $billing ? 'selected' : '' ?>><?= htmlspecialchars($billing) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn-apply">Apply Filters</button>
                            <a href="customer.php" class="btn-reset">Reset</a>
                        </div>
                    </form>
                </div>
                
                <!-- Create Customer Modal - Custom Modal -->
                <div class="custom-modal-overlay" id="createCustomerModal" style="display: none;">
                    <div class="custom-modal">
                        <div class="custom-modal-header">
                            <h5 class="custom-modal-title">Add Customer</h5>
                            <button type="button" class="custom-modal-close" onclick="closeCreateModal()">&times;</button>
                        </div>
                        <form method="post">
                            <div class="custom-modal-body">
                                <?= csrf_field() ?>
                                
                                <!-- Tab Navigation -->
                                <div class="tab-container">
                                    <div class="tab-buttons">
                                        <button type="button" class="tab-button active" onclick="switchTab('general', this)">General Info</button>
                                        <button type="button" class="tab-button" onclick="switchTab('email', this)">Email Contacts</button>
                                    </div>
                                    
                                    <!-- General Info Tab -->
                                    <div id="general-tab" class="tab-content active">
                                        <div class="custom-modal-row">
                                            <div class="custom-modal-col">
                                                <label class="custom-modal-label">Customer ID *</label>
                                                <input type="text" name="customer_id" class="custom-modal-input" required>
                                            </div>
                                            <div class="custom-modal-col">
                                                <label class="custom-modal-label">Name *</label>
                                                <input type="text" name="name" class="custom-modal-input" required>
                                            </div>
                                        </div>
                                        <div class="custom-modal-row">
                                            <div class="custom-modal-col">
                                                <label class="custom-modal-label">Star</label>
                                                <select name="star" class="custom-modal-select">
                                                    <option value="">-</option>
                                                    <option value="1">1 Star</option>
                                                    <option value="2">2 Star</option>
                                                    <option value="3">3 Star</option>
                                                    <option value="4">4 Star</option>
                                                    <option value="5">5 Star</option>
                                                </select>
                                            </div>
                                            <div class="custom-modal-col">
                                                <label class="custom-modal-label">Room</label>
                                                <input type="text" name="room" class="custom-modal-input">
                                            </div>
                                        </div>
                                        <div class="custom-modal-row">
                                            <div class="custom-modal-col">
                                                <label class="custom-modal-label">Outlet Count</label>
                                                <input type="number" name="outlet" class="custom-modal-input" min="1" value="1">
                                            </div>
                                            <div class="custom-modal-col">
                                                <label class="custom-modal-label">Type</label>
                                                <select name="type" class="custom-modal-select">
                                                    <option value="">-</option>
                                                    <?php foreach ($types as $type): ?>
                                                        <option value="<?= htmlspecialchars($type) ?>"><?= htmlspecialchars($type) ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="custom-modal-row">
                                            <div class="custom-modal-col">
                                                <label class="custom-modal-label">Group</label>
                                                <select name="group" class="custom-modal-select">
                                                    <option value="">- Pilih Grup -</option>
                                                    <?php foreach ($hotel_groups as $group): ?>
                                                        <option value="<?= htmlspecialchars($group['name']) ?>"><?= htmlspecialchars($group['name']) ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="custom-modal-col">
                                                <label class="custom-modal-label">Zone</label>
                                                <select name="zone" class="custom-modal-select">
                                                    <option value="">- Pilih Provinsi -</option>
                                                    <?php foreach ($provinces as $province): ?>
                                                        <option value="<?= htmlspecialchars($province) ?>"><?= htmlspecialchars($province) ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="custom-modal-row">
                                            <div class="custom-modal-col">
                                                <label class="custom-modal-label">Address</label>
                                                <textarea name="address" class="custom-modal-textarea" rows="2"></textarea>
                                            </div>
                                        </div>
                                        <div class="custom-modal-row">
                                            <div class="custom-modal-col">
                                                <label class="custom-modal-label">Billing</label>
                                                <select name="billing" class="custom-modal-select">
                                                    <option value="">-</option>
                                                    <?php foreach ($billings as $billing): ?>
                                                        <option value="<?= htmlspecialchars($billing) ?>"><?= htmlspecialchars($billing) ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="custom-modal-col">
                                                <label class="custom-modal-label">Status *</label>
                                                <select name="status" class="custom-modal-select" required>
                                                    <option value="Active">Active</option>
                                                    <option value="Inactive">Inactive</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Email Contacts Tab -->
                                    <div id="email-tab" class="tab-content">
                                        <div class="email-fields-grid">
                                            <div class="email-field-group">
                                                <label class="email-field-label">General Manager</label>
                                                <input type="email" name="email_gm" class="email-field-input" placeholder="gm@hotelname.com">
                                            </div>
                                            <div class="email-field-group">
                                                <label class="email-field-label">Executive Office</label>
                                                <input type="email" name="email_executive" class="email-field-input" placeholder="executive@hotelname.com">
                                            </div>
                                            <div class="email-field-group">
                                                <label class="email-field-label">HR Department Head</label>
                                                <input type="email" name="email_hr" class="email-field-input" placeholder="hr@hotelname.com">
                                            </div>
                                            <div class="email-field-group">
                                                <label class="email-field-label">Accounting Department Head</label>
                                                <input type="email" name="email_acc_head" class="email-field-input" placeholder="accounting@hotelname.com">
                                            </div>
                                            <div class="email-field-group">
                                                <label class="email-field-label">Chief Accounting</label>
                                                <input type="email" name="email_chief_acc" class="email-field-input" placeholder="chief.acc@hotelname.com">
                                            </div>
                                            <div class="email-field-group">
                                                <label class="email-field-label">Cost Control</label>
                                                <input type="email" name="email_cost_control" class="email-field-input" placeholder="cost.control@hotelname.com">
                                            </div>
                                            <div class="email-field-group">
                                                <label class="email-field-label">Accounting Payable</label>
                                                <input type="email" name="email_ap" class="email-field-input" placeholder="ap@hotelname.com">
                                            </div>
                                            <div class="email-field-group">
                                                <label class="email-field-label">Accounting Receivable</label>
                                                <input type="email" name="email_ar" class="email-field-input" placeholder="ar@hotelname.com">
                                            </div>
                                            <div class="email-field-group">
                                                <label class="email-field-label">F&B Department Head</label>
                                                <input type="email" name="email_fb" class="email-field-input" placeholder="f&b@hotelname.com">
                                            </div>
                                            <div class="email-field-group">
                                                <label class="email-field-label">Front Office Department Head</label>
                                                <input type="email" name="email_fo" class="email-field-input" placeholder="front.office@hotelname.com">
                                            </div>
                                            <div class="email-field-group">
                                                <label class="email-field-label">Housekeeping Department Head</label>
                                                <input type="email" name="email_hk" class="email-field-input" placeholder="housekeeping@hotelname.com">
                                            </div>
                                            <div class="email-field-group">
                                                <label class="email-field-label">Engineering Department Head</label>
                                                <input type="email" name="email_engineering" class="email-field-input" placeholder="engineering@hotelname.com">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="custom-modal-footer">
                                <button type="submit" name="create" class="custom-btn custom-btn-primary">Add Customer</button>
                                <button type="button" onclick="closeCreateModal()" class="custom-btn custom-btn-secondary">Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Edit Customer Modal - Custom Modal -->
                <div class="custom-modal-overlay" id="editCustomerModal" style="display: none;">
                    <div class="custom-modal">
                        <div class="custom-modal-header">
                            <h5 class="custom-modal-title">Edit Customer</h5>
                            <button type="button" class="custom-modal-close" onclick="closeEditModal()">&times;</button>
                        </div>
                        <form method="post">
                            <div class="custom-modal-body">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id" id="edit_id">
                                
                                <!-- Tab Navigation -->
                                <div class="tab-container">
                                    <div class="tab-buttons">
                                        <button type="button" class="tab-button active" onclick="switchTab('edit-general', this)">General Info</button>
                                        <button type="button" class="tab-button" onclick="switchTab('edit-email', this)">Email Contacts</button>
                                    </div>
                                    
                                    <!-- General Info Tab -->
                                    <div id="edit-general-tab" class="tab-content active">
                                        <div class="custom-modal-row">
                                            <div class="custom-modal-col">
                                                <label class="custom-modal-label">Customer ID *</label>
                                                <input type="text" name="customer_id" id="edit_customer_id" class="custom-modal-input" required>
                                            </div>
                                            <div class="custom-modal-col">
                                                <label class="custom-modal-label">Name *</label>
                                                <input type="text" name="name" id="edit_name" class="custom-modal-input" required>
                                            </div>
                                        </div>
                                        <div class="custom-modal-row">
                                            <div class="custom-modal-col">
                                                <label class="custom-modal-label">Star</label>
                                                <select name="star" id="edit_star" class="custom-modal-select">
                                                    <option value="">-</option>
                                                    <option value="1">1 Star</option>
                                                    <option value="2">2 Star</option>
                                                    <option value="3">3 Star</option>
                                                    <option value="4">4 Star</option>
                                                    <option value="5">5 Star</option>
                                                </select>
                                            </div>
                                            <div class="custom-modal-col">
                                                <label class="custom-modal-label">Room</label>
                                                <input type="text" name="room" id="edit_room" class="custom-modal-input">
                                            </div>
                                        </div>
                                        <div class="custom-modal-row">
                                            <div class="custom-modal-col">
                                                <label class="custom-modal-label">Outlet Count</label>
                                                <input type="number" name="outlet" id="edit_outlet" class="custom-modal-input" min="1">
                                            </div>
                                            <div class="custom-modal-col">
                                                <label class="custom-modal-label">Type</label>
                                                <select name="type" id="edit_type" class="custom-modal-select">
                                                    <option value="">-</option>
                                                    <?php foreach ($types as $type): ?>
                                                        <option value="<?= htmlspecialchars($type) ?>"><?= htmlspecialchars($type) ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="custom-modal-row">
                                            <div class="custom-modal-col">
                                                <label class="custom-modal-label">Group</label>
                                                <select name="group" id="edit_group" class="custom-modal-select">
                                                    <option value="">- Pilih Grup -</option>
                                                    <?php foreach ($hotel_groups as $group): ?>
                                                        <option value="<?= htmlspecialchars($group['name']) ?>"><?= htmlspecialchars($group['name']) ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="custom-modal-col">
                                                <label class="custom-modal-label">Zone</label>
                                                <select name="zone" id="edit_zone" class="custom-modal-select">
                                                    <option value="">- Pilih Provinsi -</option>
                                                    <?php foreach ($provinces as $province): ?>
                                                        <option value="<?= htmlspecialchars($province) ?>"><?= htmlspecialchars($province) ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="custom-modal-row">
                                            <div class="custom-modal-col">
                                                <label class="custom-modal-label">Address</label>
                                                <textarea name="address" id="edit_address" class="custom-modal-textarea" rows="2"></textarea>
                                            </div>
                                        </div>
                                        <div class="custom-modal-row">
                                            <div class="custom-modal-col">
                                                <label class="custom-modal-label">Billing</label>
                                                <select name="billing" id="edit_billing" class="custom-modal-select">
                                                    <option value="">-</option>
                                                    <?php foreach ($billings as $type): ?>
                                                        <option value="<?= htmlspecialchars($type) ?>"><?= htmlspecialchars($type) ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="custom-modal-col">
                                                <label class="custom-modal-label">Status *</label>
                                                <select name="status" id="edit_status" class="custom-modal-select" required>
                                                    <option value="Active">Active</option>
                                                    <option value="Inactive">Inactive</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Email Contacts Tab -->
                                    <div id="edit-email-tab" class="tab-content">
                                        <div class="email-fields-grid">
                                            <div class="email-field-group">
                                                <label class="email-field-label">General Manager</label>
                                                <input type="email" name="email_gm" id="edit_email_gm" class="email-field-input" placeholder="gm@hotelname.com">
                                            </div>
                                            <div class="email-field-group">
                                                <label class="email-field-label">Executive Office</label>
                                                <input type="email" name="email_executive" id="edit_email_executive" class="email-field-input" placeholder="executive@hotelname.com">
                                            </div>
                                            <div class="email-field-group">
                                                <label class="email-field-label">HR Department Head</label>
                                                <input type="email" name="email_hr" id="edit_email_hr" class="email-field-input" placeholder="hr@hotelname.com">
                                            </div>
                                            <div class="email-field-group">
                                                <label class="email-field-label">Accounting Department Head</label>
                                                <input type="email" name="email_acc_head" id="edit_email_acc_head" class="email-field-input" placeholder="accounting@hotelname.com">
                                            </div>
                                            <div class="email-field-group">
                                                <label class="email-field-label">Chief Accounting</label>
                                                <input type="email" name="email_chief_acc" id="edit_email_chief_acc" class="email-field-input" placeholder="chief.acc@hotelname.com">
                                            </div>
                                            <div class="email-field-group">
                                                <label class="email-field-label">Cost Control</label>
                                                <input type="email" name="email_cost_control" id="edit_email_cost_control" class="email-field-input" placeholder="cost.control@hotelname.com">
                                            </div>
                                            <div class="email-field-group">
                                                <label class="email-field-label">Accounting Payable</label>
                                                <input type="email" name="email_ap" id="edit_email_ap" class="email-field-input" placeholder="ap@hotelname.com">
                                            </div>
                                            <div class="email-field-group">
                                                <label class="email-field-label">Accounting Receivable</label>
                                                <input type="email" name="email_ar" id="edit_email_ar" class="email-field-input" placeholder="ar@hotelname.com">
                                            </div>
                                            <div class="email-field-group">
                                                <label class="email-field-label">F&B Department Head</label>
                                                <input type="email" name="email_fb" id="edit_email_fb" class="email-field-input" placeholder="f&b@hotelname.com">
                                            </div>
                                            <div class="email-field-group">
                                                <label class="email-field-label">Front Office Department Head</label>
                                                <input type="email" name="email_fo" id="edit_email_fo" class="email-field-input" placeholder="front.office@hotelname.com">
                                            </div>
                                            <div class="email-field-group">
                                                <label class="email-field-label">Housekeeping Department Head</label>
                                                <input type="email" name="email_hk" id="edit_email_hk" class="email-field-input" placeholder="housekeeping@hotelname.com">
                                            </div>
                                            <div class="email-field-group">
                                                <label class="email-field-label">Engineering Department Head</label>
                                                <input type="email" name="email_engineering" id="edit_email_engineering" class="email-field-input" placeholder="engineering@hotelname.com">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="custom-modal-footer">
                                <button type="submit" name="update" class="custom-btn custom-btn-primary">Update Customer</button>
                                <button type="button" onclick="closeEditModal()" class="custom-btn custom-btn-secondary">Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th scope="col" class="text-center-important">
                                        <div class="table-header text-center-important">Customer ID</div>
                                    </th>
                                    <th scope="col">
                                        <div class="table-header">Name</div>
                                    </th>
                                    <th scope="col">
                                        <div class="table-header">Group</div>
                                    </th>
                                    <th scope="col">
                                        <div class="table-header">Star</div>
                                    </th>
                                    <th scope="col" class="text-center-important">
                                        <div class="table-header text-center-important">Type</div>
                                    </th>
                                    <th scope="col" class="text-center-important">
                                        <div class="table-header text-center-important">Room</div>
                                    </th>
                                    <th scope="col" class="text-center-important">
                                        <div class="table-header text-center-important">Outlet</div>
                                    </th>
                                    <th scope="col" class="text-center-important">
                                        <div class="table-header text-center-important">Zone</div>
                                    </th>
                                    <th scope="col" class="text-center-important">
                                        <div class="table-header text-center-important">Billing</div>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($customers as $index => $c): ?>
                                <tr class="customer-row"
                                    data-id="<?= $c['id'] ?>"
                                    data-customer-id="<?= htmlspecialchars($c['customer_id'] ?? '') ?>"
                                    data-name="<?= htmlspecialchars($c['name'] ?? '') ?>"
                                    data-star="<?= htmlspecialchars($c['star'] ?? '') ?>"
                                    data-room="<?= htmlspecialchars($c['room'] ?? '') ?>"
                                    data-outlet="<?= htmlspecialchars($c['outlet'] ?? '') ?>"
                                    data-type="<?= htmlspecialchars($c['type'] ?? '') ?>"
                                    data-group="<?= htmlspecialchars($c['group'] ?? '') ?>"
                                    data-zone="<?= htmlspecialchars($c['zone'] ?? '') ?>"
                                    data-address="<?= htmlspecialchars($c['address'] ?? '') ?>"
                                    data-email-gm="<?= htmlspecialchars($c['email_gm'] ?? '') ?>"
                                    data-email-executive="<?= htmlspecialchars($c['email_executive'] ?? '') ?>"
                                    data-email-hr="<?= htmlspecialchars($c['email_hr'] ?? '') ?>"
                                    data-email-acc-head="<?= htmlspecialchars($c['email_acc_head'] ?? '') ?>"
                                    data-email-chief-acc="<?= htmlspecialchars($c['email_chief_acc'] ?? '') ?>"
                                    data-email-cost-control="<?= htmlspecialchars($c['email_cost_control'] ?? '') ?>"
                                    data-email-ap="<?= htmlspecialchars($c['email_ap'] ?? '') ?>"
                                    data-email-ar="<?= htmlspecialchars($c['email_ar'] ?? '') ?>"
                                    data-email-fb="<?= htmlspecialchars($c['email_fb'] ?? '') ?>"
                                    data-email-fo="<?= htmlspecialchars($c['email_fo'] ?? '') ?>"
                                    data-email-hk="<?= htmlspecialchars($c['email_hk'] ?? '') ?>"
                                    data-email-engineering="<?= htmlspecialchars($c['email_engineering'] ?? '') ?>"
                                    data-billing="<?= htmlspecialchars($c['billing'] ?? '') ?>"
                                    onclick="editCustomer(<?= $c['id'] ?>)">
                                    <td data-label="Customer ID" class="text-center-important"><?= htmlspecialchars($c['customer_id'] ?: '-') ?></td>
                                    <td data-label="Name"><?= htmlspecialchars($c['name'] ?: '-') ?></td>
                                    <td data-label="Group"><?= htmlspecialchars($c['group'] ?: '-') ?></td>
                                    <td data-label="Star">
                                        <?php if ($c['star']): ?>
                                            <span class="star-badge bg-warning-focus text-warning-main px-8 py-4 rounded-pill fw-medium text-sm"><?= str_repeat('⭐', $c['star']) ?></span>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td data-label="Type" class="text-center-important">
                                        <?php if ($c['type']): ?>
                                            <span class="type-badge bg-info-focus text-info-main px-8 py-4 rounded-pill fw-medium text-sm"><?= htmlspecialchars($c['type']) ?></span>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td data-label="Room" class="text-center-important"><?= htmlspecialchars($c['room'] ?: '-') ?></td>
                                    <td data-label="Outlet" class="text-center-important"><?= htmlspecialchars($c['outlet'] ?: '-') ?></td>
                                    <td data-label="Zone" class="text-center-important"><?= htmlspecialchars($c['zone'] ?: '-') ?></td>
                                    <td data-label="Billing" class="text-center-important">
                                        <?php if ($c['billing']): ?>
                                            <span class="billing-badge bg-success-focus text-success-main px-8 py-4 rounded-pill fw-medium text-sm"><?= htmlspecialchars($c['billing']) ?></span>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

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

<style>
/* Filter Section Styling */
.filter-section {
    background: #f8f9fa;
    padding: 20px;
    border-bottom: 1px solid #dee2e6;
}

.filter-form {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.filter-row {
    display: flex;
    flex-wrap: wrap;
    gap: 16px;
    align-items: end;
}

.filter-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
    min-width: 200px;
}

.filter-label {
    font-weight: 600;
    color: #374151;
    font-size: 14px;
}

.btn-apply {
    background: #4f46e5;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 8px;
    font-weight: 500;
    cursor: pointer;
    transition: background-color 0.3s;
}

.btn-apply:hover {
    background: #4338ca;
}

.btn-reset {
    background: #6b7280;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 8px;
    font-weight: 500;
    text-decoration: none;
    display: inline-block;
    transition: background-color 0.3s;
}

.btn-reset:hover {
    background: #4b5563;
    color: white;
}

/* Custom Modal Styling */
.custom-modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 1050;
}

.custom-modal {
    background: white;
    border-radius: 12px;
    width: 90%;
    max-width: 800px;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

.custom-modal-header {
    padding: 20px 24px;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.custom-modal-title {
    margin: 0;
    font-size: 18px;
    font-weight: 600;
    color: #111827;
}

.custom-modal-close {
    background: none;
    border: none;
    font-size: 24px;
    cursor: pointer;
    color: #6b7280;
    padding: 0;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 6px;
    transition: all 0.2s;
}

.custom-modal-close:hover {
    background: #f3f4f6;
    color: #374151;
}

.custom-modal-body {
    padding: 24px;
}

.custom-modal-row {
    display: flex;
    gap: 16px;
    margin-bottom: 16px;
}

.custom-modal-col {
    flex: 1;
    min-width: 0;
}

.custom-modal-label {
    display: block;
    margin-bottom: 6px;
    font-weight: 500;
    color: #374151;
    font-size: 14px;
}

.custom-modal-input,
.custom-modal-select,
.custom-modal-textarea {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 14px;
    transition: border-color 0.2s;
}

.custom-modal-input:focus,
.custom-modal-select:focus,
.custom-modal-textarea:focus {
    outline: none;
    border-color: #4f46e5;
    box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
}

.custom-modal-textarea {
    resize: vertical;
    min-height: 80px;
}

.custom-modal-footer {
    padding: 20px 24px;
    border-top: 1px solid #e5e7eb;
    display: flex;
    gap: 12px;
    justify-content: flex-end;
}

.custom-btn {
    padding: 10px 20px;
    border: none;
    border-radius: 6px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
    font-size: 14px;
}

.custom-btn-primary {
    background: #4f46e5;
    color: white;
}

.custom-btn-primary:hover {
    background: #4338ca;
}

.custom-btn-secondary {
    background: #6b7280;
    color: white;
}

.custom-btn-secondary:hover {
    background: #4b5563;
}

/* Tab System Styling */
.tab-container {
    margin-bottom: 20px;
}

.tab-buttons {
    display: flex;
    border-bottom: 2px solid #e5e7eb;
    margin-bottom: 20px;
}

.tab-button {
    background: none;
    border: none;
    padding: 12px 24px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 500;
    color: #6b7280;
    border-bottom: 2px solid transparent;
    transition: all 0.3s ease;
}

.tab-button.active {
    color: #3b82f6;
    border-bottom-color: #3b82f6;
    background-color: #eff6ff;
}

.tab-button:hover:not(.active) {
    color: #374151;
    background-color: #f9fafb;
}

.tab-content {
    display: none;
}

.tab-content.active {
    display: block;
}

/* Email Fields Grid */
.email-fields-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}

.email-field-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.email-field-label {
    font-weight: 600;
    color: #374151;
    font-size: 14px;
}

.email-field-input {
    padding: 8px 12px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 14px;
    transition: border-color 0.3s ease;
}

.email-field-input:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.email-field-input::placeholder {
    color: #9ca3af;
}

/* Table Header Styling */
.table-header {
    padding: 12px 16px;
    background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
    border: none;
    border-radius: 8px;
    margin: 0;
    font-weight: 600;
    color: white;
    font-size: 14px;
    text-align: center;
    box-shadow: 0 2px 8px rgba(79, 70, 229, 0.3);
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    height: 100%;
    min-height: 56px;
    line-height: 1.15;
}

/* Utility: force centered text where needed */
.text-center-important { text-align: center !important; }

.table-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,.2), transparent);
    transition: left .5s;
}

.table-header:hover::before {
    left: 100%;
}

/* Table Styling */
.table.table-striped tbody tr:nth-child(odd) {
    background-color: #f8f9fa;
}

.table.table-striped tbody tr:hover {
    background-color: #e9ecef;
    cursor: pointer;
}

.table.table-striped td {
    padding: 12px 8px;
    vertical-align: middle !important;
}

/* Badge Styling */
.type-badge,
.star-badge,
.outlet-badge,
.billing-badge,
.creator-badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 500;
    text-align: center;
}

/* Responsive */
@media (max-width: 768px) {
    .custom-modal {
        width: 95%;
        margin: 20px;
    }
    
    .custom-modal-row {
        flex-direction: column;
        gap: 12px;
    }
    
    .filter-row {
        flex-direction: column;
        gap: 12px;
    }
    
    .filter-group {
        min-width: 100%;
    }

    .email-fields-grid {
        grid-template-columns: 1fr;
    }
    
    .tab-buttons {
        flex-direction: column;
    }
    
    .tab-button {
        text-align: left;
        border-bottom: none;
        border-right: 2px solid transparent;
    }
    
    .tab-button.active {
        border-right-color: #3b82f6;
        border-bottom-color: transparent;
    }
}
</style>

<script>
function showCreateModal() {
    document.getElementById('createCustomerModal').style.display = 'flex';
}

function closeCreateModal() {
    document.getElementById('createCustomerModal').style.display = 'none';
}

function switchTab(tabName, buttonElement) {
    // Remove active class from all tabs and buttons
    const tabButtons = buttonElement.parentElement.querySelectorAll('.tab-button');
    const tabContents = buttonElement.parentElement.parentElement.querySelectorAll('.tab-content');
    
    tabButtons.forEach(btn => btn.classList.remove('active'));
    tabContents.forEach(content => content.classList.remove('active'));
    
    // Add active class to clicked button and corresponding content
    buttonElement.classList.add('active');
    const targetTab = document.getElementById(tabName + '-tab');
    if (targetTab) {
        targetTab.classList.add('active');
    }
}

function editCustomer(customerId) {
    // Find the row data
    const row = document.querySelector(`tr[data-id="${customerId}"]`);
    if (!row) return;
    
    // Populate modal fields
    document.getElementById('edit_id').value = customerId;
    document.getElementById('edit_customer_id').value = row.dataset.customerId;
    document.getElementById('edit_name').value = row.dataset.name;
    document.getElementById('edit_star').value = row.dataset.star;
    document.getElementById('edit_room').value = row.dataset.room;
    document.getElementById('edit_outlet').value = row.dataset.outlet;
    document.getElementById('edit_type').value = row.dataset.type;
    document.getElementById('edit_group').value = row.dataset.group;
    document.getElementById('edit_zone').value = row.dataset.zone;
    document.getElementById('edit_address').value = row.dataset.address;
    document.getElementById('edit_billing').value = row.dataset.billing;
    const statusEl = document.getElementById('edit_status');
    if (statusEl) { statusEl.value = row.dataset.status || 'Active'; }
    
    // Populate email fields if they exist in data attributes
    const emailFields = [
        { field: 'email_gm', dataAttr: 'email-gm' },
        { field: 'email_executive', dataAttr: 'email-executive' },
        { field: 'email_hr', dataAttr: 'email-hr' },
        { field: 'email_acc_head', dataAttr: 'email-acc-head' },
        { field: 'email_chief_acc', dataAttr: 'email-chief-acc' },
        { field: 'email_cost_control', dataAttr: 'email-cost-control' },
        { field: 'email_ap', dataAttr: 'email-ap' },
        { field: 'email_ar', dataAttr: 'email-ar' },
        { field: 'email_fb', dataAttr: 'email-fb' },
        { field: 'email_fo', dataAttr: 'email-fo' },
        { field: 'email_hk', dataAttr: 'email-hk' },
        { field: 'email_engineering', dataAttr: 'email-engineering' }
    ];
    
    emailFields.forEach(({ field, dataAttr }) => {
        const input = document.getElementById('edit_' + field);
        if (input && row.dataset[dataAttr]) {
            input.value = row.dataset[dataAttr];
        } else if (input) {
            input.value = '';
        }
    });
    
    // Show modal
    document.getElementById('editCustomerModal').style.display = 'flex';
}

function closeEditModal() {
    document.getElementById('editCustomerModal').style.display = 'none';
}

// Close modal when clicking outside
document.addEventListener('DOMContentLoaded', function() {
    const modals = document.querySelectorAll('.custom-modal-overlay');
    modals.forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                modal.style.display = 'none';
            }
        });
    });
    
    // Close modal with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            modals.forEach(modal => {
                modal.style.display = 'none';
            });
        }
    });
});
</script>

<?php include './partials/layouts/layoutBottom.php'; ?>
