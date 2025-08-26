<?php
session_start();
require_once 'db.php';
require_once 'access_control.php';
require_once 'user_utils.php';

require_login();

// CSRF Protection
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function csrf_field() {
    return '<input type="hidden" name="csrf_token" value="' . $_SESSION['csrf_token'] . '">';
}

function csrf_verify() {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        return false;
    }
    return true;
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_verify()) {
    if (isset($_POST['create'])) {
        try {
            $stmt = $pdo->prepare('INSERT INTO hotel_groups (name) VALUES (?)');
            $stmt->execute([$_POST['name']]);
            $_SESSION['notification'] = ['type' => 'success', 'message' => 'Group created successfully.'];
        } catch (PDOException $e) {
            $_SESSION['notification'] = ['type' => 'error', 'message' => 'Error: ' . $e->getMessage()];
        }
    } elseif (isset($_POST['update'])) {
        try {
            $stmt = $pdo->prepare('UPDATE hotel_groups SET name = ? WHERE id = ?');
            $stmt->execute([$_POST['name'], $_POST['id']]);
            $_SESSION['notification'] = ['type' => 'success', 'message' => 'Group updated successfully.'];
        } catch (PDOException $e) {
            $_SESSION['notification'] = ['type' => 'error', 'message' => 'Error: ' . $e->getMessage()];
        }
    }
    header("Location: group.php");
    exit;
}

// Fetch all groups
$stmt = $pdo->query('SELECT * FROM hotel_groups ORDER BY name ASC');
$groups = $stmt->fetchAll(PDO::FETCH_ASSOC);

include './partials/layouts/layoutHorizontal.php';
?>

<div class="dashboard-main-body">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
        <h6 class="fw-semibold mb-0">Hotel Groups</h6>
        <ul class="d-flex align-items-center gap-2">
            <li class="fw-medium"><a href="index.php" class="d-flex align-items-center gap-1 hover-text-primary"><iconify-icon icon="solar:home-smile-angle-outline" class="icon text-lg"></iconify-icon> Dashboard</a></li>
            <li>-</li>
            <li class="fw-medium">Hotel Groups</li>
        </ul>
    </div>

    <div class="card h-100 radius-12">
        <div class="card-header">
            <button type="button" class="btn btn-sm btn-primary-600" onclick="showGroupModal()">Create Group</button>
        </div>
        <div class="card-body">
            <?php 
            if (isset($_SESSION['notification'])) {
                $notification = $_SESSION['notification'];
                $alert_class = $notification['type'] === 'error' ? 'danger' : 'success';
                echo '<div class="alert alert-' . $alert_class . '" role="alert">';
                echo htmlspecialchars($notification['message']);
                echo '</div>';
                unset($_SESSION['notification']);
            }
            ?>
            <div class="table-responsive">
                <table class="table table-striped mb-0">
                    <thead>
                        <tr>
                            <th>Group Name</th>
                            <th>Customer Count</th>
                            <th>Created At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($groups as $group): ?>
                        <tr class="group-row" 
                            data-group='<?= htmlspecialchars(json_encode($group), ENT_QUOTES, 'UTF-8') ?>'
                            style="cursor: pointer;">
                            <td><?= htmlspecialchars($group['name']) ?></td>
                            <td><?= htmlspecialchars($group['customer_count']) ?></td>
                            <td><?= date('d M Y, H:i', strtotime($group['created_at'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Universal Group Modal -->
<div class="custom-modal-overlay" id="groupModal">
    <div class="custom-modal">
        <div class="custom-modal-header">
            <h5 class="custom-modal-title" id="groupModalTitle"></h5>
            <button type="button" class="custom-modal-close" onclick="hideGroupModal()">&times;</button>
        </div>
        <form id="groupForm" method="post">
            <div class="custom-modal-body">
                <?= csrf_field() ?>
                <input type="hidden" name="id" id="group_id">
                <label class="custom-modal-label">Group Name *</label>
                <input type="text" name="name" id="group_name" class="custom-modal-input" required>
            </div>
            <div class="custom-modal-footer">
                <button type="submit" id="saveButton" name="save" class="custom-btn custom-btn-primary"></button>
                <button type="button" class="custom-btn custom-btn-secondary" onclick="hideGroupModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<style>
.custom-modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,.5);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 1050;
}
.custom-modal-overlay.show {
    display: flex;
}
.custom-modal {
    width: min(500px, 96vw);
    background: #fff;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 20px 60px rgba(0,0,0,.2);
}
.custom-modal-header {
    padding: 16px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-bottom: 1px solid #e5e7eb;
}
.custom-modal-title { margin: 0; font-size: 18px; font-weight: 600; }
.custom-modal-close { background: transparent; border: 0; font-size: 22px; cursor: pointer; line-height: 1; }
.custom-modal-body { padding: 20px; }
.custom-modal-footer {
    padding: 14px 20px;
    background: #f8fafc;
    display: flex;
    gap: 10px;
    justify-content: flex-end;
}
.custom-modal-label { font-weight: 600; font-size: 12px; margin-bottom: 6px; display: block; }
.custom-modal-input { width: 100%; padding: 10px 12px; border-radius: 8px; border: 1px solid #e5e7eb; }
.custom-btn { padding: 10px 14px; border-radius: 8px; border: 0; cursor: pointer; font-weight: 600; }
.custom-btn-primary { background: #2563eb; color: #fff; }
.custom-btn-secondary { background: #6b7280; color: #fff; }
</style>

<script>
function showGroupModal(group = null) {
    const modal = document.getElementById('groupModal');
    const title = document.getElementById('groupModalTitle');
    const form = document.getElementById('groupForm');
    const idInput = document.getElementById('group_id');
    const nameInput = document.getElementById('group_name');
    const saveButton = document.getElementById('saveButton');

    if (group) {
        // Edit mode
        title.textContent = 'Edit Group';
        idInput.value = group.id;
        nameInput.value = group.name;
        saveButton.textContent = 'Update';
        saveButton.name = 'update';
    } else {
        // Create mode
        title.textContent = 'Create Group';
        form.reset();
        idInput.value = '';
        saveButton.textContent = 'Create';
        saveButton.name = 'create';
    }
    modal.classList.add('show');
}

function hideGroupModal() {
    document.getElementById('groupModal').classList.remove('show');
}

document.addEventListener('DOMContentLoaded', function() {
    const rows = document.querySelectorAll('.group-row');
    rows.forEach(row => {
        row.addEventListener('click', function() {
            const groupData = JSON.parse(this.dataset.group);
            showGroupModal(groupData);
        });
    });

    // Also hide modal on overlay click or escape key
    document.getElementById('groupModal').addEventListener('click', function(e) {
        if (e.target === this) {
            hideGroupModal();
        }
    });
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            hideGroupModal();
        }
    });
});
</script>

<?php include './partials/layouts/layoutBottom.php'; ?>