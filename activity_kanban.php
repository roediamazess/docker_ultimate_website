<?php
session_start();
require_once 'db.php';
require_once 'access_control.php';
require_login();

// --- Start of Filter Logic ---
$search = trim($_GET['search'] ?? '');
$filter_status = $_GET['filter_status'] ?? '';
$filter_type = $_GET['filter_type'] ?? '';
$filter_priority = $_GET['filter_priority'] ?? '';
$filter_department = $_GET['filter_department'] ?? '';
$filter_application = $_GET['filter_application'] ?? '';

$where_conditions = [];
$params = [];

if ($search) {
    $where_conditions[] = "(description ILIKE ? OR user_position ILIKE ? OR cnc_number ILIKE ? OR no::text ILIKE ?)";
    $search_term = "%$search%";
    $params = array_merge($params, [$search_term, $search_term, $search_term, $search_term]);
}

if ($filter_status && $filter_status !== 'not_done') {
    $where_conditions[] = "status = ?";
    $params[] = $filter_status;
}

if ($filter_type) {
    $where_conditions[] = "type = ?";
    $params[] = $filter_type;
}

if ($filter_priority) {
    $where_conditions[] = "priority = ?";
    $params[] = $filter_priority;
}

if ($filter_department) {
    $where_conditions[] = "department = ?";
    $params[] = $filter_department;
}

if ($filter_application) {
    $where_conditions[] = "application = ?";
    $params[] = $filter_application;
}

$where_clause = $where_conditions ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Fetch activities with the applied filters
$sql = "SELECT id, no, information_date, priority, user_position, department, application, type, description, action_solution, customer, project, due_date, cnc_number, status FROM activities $where_clause ORDER BY no ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$activities = $stmt->fetchAll(PDO::FETCH_ASSOC);
// --- End of Filter Logic ---

$columns = [
  'Open' => [],
  'On Progress' => [],
  'Need Requirement' => [],
  'Done' => [],
  'Cancel' => [],
];
foreach ($activities as $a) {
  $status = $a['status'] ?? 'Open';
  if (isset($columns[$status])) {
      $columns[$status][] = $a;
  }
}

// Generate a CSRF token if one doesn't exist
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$csrf_token = $_SESSION['csrf_token'];

?>
<?php include './partials/layouts/layoutHorizontal.php'; ?>

<link rel="stylesheet" href="assets/css/modal.css">

<style>
/* Kanban Styles */
.kanban-board{display:grid;grid-template-columns:repeat(5,1fr);gap:16px}
.kanban-column{background:#f8fafc;border:1px solid #e5e7eb;border-radius:12px;overflow:hidden;min-height:60vh;display:flex;flex-direction:column;box-shadow:0 1px 2px rgba(0,0,0,.06)}
.kanban-header{padding:12px 14px;font-weight:700}
.kanban-cards{padding:12px;display:flex;flex-direction:column;gap:12px; flex-grow: 1;}
.kanban-card{position:relative;background:linear-gradient(180deg,#ffffff, #f8fafc);border:1px solid #e5e7eb;border-radius:14px;padding:12px 12px 10px 16px;cursor:grab;box-shadow:0 10px 24px rgba(2,6,23,.06);transition:transform .18s ease, box-shadow .18s ease}
.kanban-card{user-select:none;-webkit-user-select:none;-ms-user-select:none}
.kanban-card::before{content:"";position:absolute;left:0;top:0;bottom:0;width:4px;border-top-left-radius:14px;border-bottom-left-radius:14px;background:var(--accent,#90C5D8)}
.kanban-card:hover{transform:translateY(-2px);box-shadow:0 14px 32px rgba(2,6,23,.12)}
.kanban-title{display:flex;align-items:center;gap:8px;font-weight:700;color:#0f172a}
.badge{display:inline-block;font-size:11px;line-height:1;padding:4px 8px;border-radius:9999px;border:1px solid rgba(2,6,23,.08);background:#eef2ff;color:#3730a3}
.badge.app{background:#ecfeff;color:#155e75;border-color:#a5f3fc}
.badge.type{background:#f0fdf4;color:#14532d;border-color:#bbf7d0}
.badge.pri{background:#eff6ff;color:#1e40af;border-color:#bfdbfe}
.meta{font-size:12px;color:#64748b;display:flex;gap:8px;flex-wrap:wrap;margin-top:6px}
.action-solution{font-size:12px;color:#475569;margin-top:6px}
.accent-urgent{--accent:#ef4444}
.accent-normal{--accent:#3b82f6}
.accent-low{--accent:#f59e0b}
.kanban-column.drag-over{outline:2px dashed var(--brand-accent-strong,#6BB2C8);outline-offset:-6px}

/* Filter Section Styles */
.filter-section{padding:1rem;margin-bottom:1rem;background:#f8fafc;border:1px solid #e5e7eb;border-radius:.5rem}
.filter-form .filter-row{display:flex;flex-wrap:wrap;gap:1rem;margin-bottom:1rem}
.filter-form .filter-group{flex:1;min-width:150px}
.filter-form .filter-label{font-weight:600;font-size:12px;color:#374151;margin-bottom:6px;display:block}
.filter-form .filter-buttons{display:flex;gap:.5rem}

/* Dark Theme */
[data-theme="dark"] .kanban-column{background:#0f1220;border-color:#0b1220}
[data-theme="dark"] .kanban-card{background:linear-gradient(180deg,#111827,#0b1220);border-color:#374151;color:#e5e7eb}
[data-theme="dark"] .kanban-title{color:#e5e7eb}
[data-theme="dark"] .badge{border-color:#334155}
[data-theme="dark"] .badge.app{background:#0e7490;color:#ecfeff}
[data-theme="dark"] .badge.type{background:#14532d;color:#ecfdf5}
[data-theme="dark"] .badge.pri{background:#1e3a8a;color:#dbeafe}
[data-theme="dark"] .meta{color:#9ca3af}
[data-theme="dark"] .filter-section{background:#1f2937;border-color:#374151}
[data-theme="dark"] .filter-label{color:#e5e7eb}
[data-theme="dark"] .form-control, [data-theme="dark"] .form-select{background-color:#111827;border-color:#374151;color:#e5e7eb}

@media(max-width:1200px){.kanban-board{grid-template-columns:repeat(3,1fr)}}
@media(max-width:768px){.kanban-board{grid-template-columns:1fr}}
</style>

<div class="dashboard-main-body">
  <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
    <h6 class="fw-semibold mb-0">Activity Kanban</h6>
    <ul class="d-flex align-items-center gap-2">
      <li class="fw-medium">
        <a href="index.php" class="d-flex align-items-center gap-1 hover-text-primary">
          <iconify-icon icon="solar:home-smile-angle-outline" class="icon text-lg"></iconify-icon>
          Dashboard
        </a>
      </li>
      <li>-</li>
      <li class="fw-medium">Activity Kanban</li>
    </ul>
  </div>

  <div class="card">
    <div class="d-flex justify-content-end p-3"><div class="d-flex gap-2">
      <a href="activity.php" class="btn btn-secondary">List View</a>
      <a href="activity_kanban.php" class="btn btn-primary">Kanban View</a>
      <a href="activity_gantt.php" class="btn btn-secondary">Gantt Chart</a>
    </div></div>
    <div class="card-body">
      <!-- Filter Section -->
      <div class="filter-section">
          <form method="get" class="filter-form" action="activity_kanban.php">
              <div class="filter-row">
                  <div class="filter-group">
                      <label class="filter-label">Search</label>
                      <input type="text" name="search" class="form-control" placeholder="Search activities..." value="<?= htmlspecialchars($search) ?>">
                  </div>
                  <div class="filter-group">
                      <label class="filter-label">Priority</label>
                      <select class="form-select" name="filter_priority">
                          <option value="">All Priority</option>
                          <option value="Urgent" <?= $filter_priority === 'Urgent' ? 'selected' : '' ?>>Urgent</option>
                          <option value="Normal" <?= $filter_priority === 'Normal' ? 'selected' : '' ?>>Normal</option>
                          <option value="Low" <?= $filter_priority === 'Low' ? 'selected' : '' ?>>Low</option>
                      </select>
                  </div>
                  <div class="filter-group">
                      <label class="filter-label">Status</label>
                      <select class="form-select" name="filter_status">
                          <option value="">All Status</option>
                          <option value="Open" <?= $filter_status === 'Open' ? 'selected' : '' ?>>Open</option>
                          <option value="On Progress" <?= $filter_status === 'On Progress' ? 'selected' : '' ?>>On Progress</option>
                          <option value="Need Requirement" <?= $filter_status === 'Need Requirement' ? 'selected' : '' ?>>Need Requirement</option>
                          <option value="Done" <?= $filter_status === 'Done' ? 'selected' : '' ?>>Done</option>
                          <option value="Cancel" <?= $filter_status === 'Cancel' ? 'selected' : '' ?>>Cancel</option>
                      </select>
                  </div>
              </div>
              <div class="filter-buttons">
                  <button type="submit" class="btn btn-primary">Apply Filters</button>
                  <a href="activity_kanban.php" class="btn btn-secondary">Reset</a>
              </div>
          </form>
      </div>
      <div class="kanban-board">
        <?php
        $status_colors = [
            'Open' => 'bg-warning-focus text-warning-main',
            'On Progress' => 'bg-info-focus text-info-main',
            'Need Requirement' => 'bg-secondary-focus text-secondary-main',
            'Done' => 'bg-success-focus text-success-main',
            'Cancel' => 'bg-danger-focus text-danger-main'
        ];
        ?>
        <?php foreach($columns as $status => $cards): ?>
          <?php
            $status_class = $status_colors[$status] ?? 'bg-neutral-200 text-neutral-600';
          ?>
          <div class="kanban-column" data-status="<?= htmlspecialchars($status) ?>" draggable="false">
            <div class="kanban-header <?= $status_class ?>"><?= htmlspecialchars($status) ?></div>
            <div class="kanban-cards">
              <?php foreach($cards as $c): ?>
                <?php 
                  $pri = strtolower($c['priority'] ?? 'normal');
                  $accent = in_array($pri,['urgent','normal','low']) ? 'accent-'.$pri : 'accent-normal';
                ?>
                <div class="kanban-card <?= $accent ?>" draggable="true" data-id="<?= (int)$c['id'] ?>">
                  <div class="kanban-title">
                    <span><?= htmlspecialchars($c['no']) ?></span>
                    <span class="badge type" title="Type"><?= htmlspecialchars($c['type']) ?></span>
                    <span class="badge app" title="Application"><?= htmlspecialchars($c['application']) ?></span>
                    <span class="badge pri" title="Priority"><?= htmlspecialchars($c['priority']) ?></span>
                  </div>
                  <div class="text-truncate mt-1" title="<?= htmlspecialchars($c['description']) ?>"><?= htmlspecialchars($c['description']) ?></div>
                  <?php $as = $c['action_solution'] ?? ''; if($as !== ''): ?>
                  <div class="action-solution text-truncate" title="<?= htmlspecialchars($as) ?>">Action / Solution: <?= htmlspecialchars($as) ?></div>
                  <?php endif; ?>
                  <div class="meta"><span><?= htmlspecialchars($c['user_position'] ?? '-') ?></span><span><?= htmlspecialchars($c['department']) ?></span><span><?= $c['information_date']?date('d M Y',strtotime($c['information_date'])):'-' ?></span></div>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>

<script>
  window.csrfToken = '<?= $csrf_token ?>';
</script>
<script src="assets/js/activity_kanban.js"></script>

<?php include './partials/layouts/layoutBottom.php'; ?>