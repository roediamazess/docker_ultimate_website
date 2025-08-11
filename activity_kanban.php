<?php
session_start();
require_once 'db.php';
require_once 'access_control.php';
require_login();

// Ambil activities per status
$stmt = $pdo->query("SELECT id, no, information_date, priority, department, application, type, description, status FROM activities ORDER BY no ASC");
$activities = $stmt->fetchAll(PDO::FETCH_ASSOC);

$columns = [
  'Open' => [],
  'On Progress' => [],
  'Need Requirement' => [],
  'Done' => [],
  'Cancel' => [],
];
foreach ($activities as $a) {
  $status = $a['status'] ?? 'Open';
  if (!isset($columns[$status])) { $columns['Open'][] = $a; } else { $columns[$status][] = $a; }
}

// Script tambahan di footer (HEREDOC untuk menghindari escape)
$script = ($script ?? '') . <<<'SCRIPT'
<script>
// Drag & Drop sederhana (HTML5)
document.addEventListener('DOMContentLoaded', function(){
  document.querySelectorAll('.kanban-card').forEach(function(card){
    card.addEventListener('dragstart', function(e){ e.dataTransfer.setData('text/plain', this.dataset.id); });
  });
  document.querySelectorAll('.kanban-column').forEach(function(col){
    col.addEventListener('dragover', function(e){ e.preventDefault(); this.classList.add('drag-over');});
    col.addEventListener('dragleave', function(){ this.classList.remove('drag-over');});
    col.addEventListener('drop', async function(e){
      e.preventDefault(); this.classList.remove('drag-over');
      const id = e.dataTransfer.getData('text/plain');
      const newStatus = this.dataset.status;
      try {
        const res = await fetch('update_activity_status.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({id, status:newStatus})});
        const ok = res.ok; if(ok){ this.querySelector('.kanban-cards').prepend(document.querySelector('[data-id="'+id+'"]')); }
        if(window.logoNotificationManager&&ok){ logoNotificationManager.showActivityUpdated('Status dipindah ke '+newStatus, 3000); }
      } catch(err){ console.error(err); }
    });
  });
});
</script>
SCRIPT;
?>
<?php include './partials/layouts/layoutHorizontal.php'; ?>

<style>
.kanban-board{display:grid;grid-template-columns:repeat(5,1fr);gap:16px}
.kanban-column{background:var(--glass-bg,rgba(255,255,255,.95));backdrop-filter:blur(10px);border:1px solid rgba(0,0,0,.06);border-radius:12px;overflow:hidden;min-height:60vh;display:flex;flex-direction:column}
.kanban-header{padding:12px 14px;font-weight:700;background:linear-gradient(135deg,var(--brand-accent-strong,#6BB2C8),var(--brand-accent,#90C5D8));color:#fff}
.kanban-cards{padding:12px;display:flex;flex-direction:column;gap:12px}
.kanban-card{position:relative;background:linear-gradient(180deg,#ffffff, #f8fafc);border:1px solid #e5e7eb;border-radius:14px;padding:12px 12px 10px 16px;cursor:grab;box-shadow:0 10px 24px rgba(2,6,23,.06);transition:transform .18s ease, box-shadow .18s ease}
.kanban-card::before{content:"";position:absolute;left:0;top:0;bottom:0;width:4px;border-top-left-radius:14px;border-bottom-left-radius:14px;background:var(--accent,#90C5D8)}
.kanban-card:hover{transform:translateY(-2px);box-shadow:0 14px 32px rgba(2,6,23,.12)}
.kanban-title{display:flex;align-items:center;gap:8px;font-weight:700;color:#0f172a}
.badge{display:inline-block;font-size:11px;line-height:1;padding:4px 8px;border-radius:9999px;border:1px solid rgba(2,6,23,.08);background:#eef2ff;color:#3730a3}
.badge.app{background:#ecfeff;color:#155e75;border-color:#a5f3fc}
.badge.type{background:#f0fdf4;color:#14532d;border-color:#bbf7d0}
.badge.pri{background:#eff6ff;color:#1e40af;border-color:#bfdbfe}
.meta{font-size:12px;color:#64748b;display:flex;gap:8px;flex-wrap:wrap;margin-top:6px}
.accent-urgent{--accent:#ef4444}
.accent-normal{--accent:#3b82f6}
.accent-low{--accent:#f59e0b}
.kanban-column.drag-over{outline:2px dashed var(--brand-accent-strong,#6BB2C8);outline-offset:-6px}
[data-theme="dark"] .kanban-column{background:#1f2937;border-color:rgba(148,163,184,.18)}
[data-theme="dark"] .kanban-card{background:linear-gradient(180deg,#111827,#0b1220);border-color:#374151;color:#e5e7eb}
[data-theme="dark"] .kanban-title{color:#e5e7eb}
[data-theme="dark"] .badge{border-color:#334155}
[data-theme="dark"] .badge.app{background:#0e7490;color:#ecfeff}
[data-theme="dark"] .badge.type{background:#14532d;color:#ecfdf5}
[data-theme="dark"] .badge.pri{background:#1e3a8a;color:#dbeafe}
[data-theme="dark"] .meta{color:#9ca3af}
@media(max-width:1200px){.kanban-board{grid-template-columns:repeat(3,1fr)}}
@media(max-width:768px){.kanban-board{grid-template-columns:1fr}}
</style>

<div class="dashboard-main-body">
  <div class="d-flex align-items-center justify-content-between gap-3 mb-24">
    <h6 class="fw-semibold mb-0">Activity Kanban</h6>
    <div class="d-flex gap-2">
      <a href="activity.php" class="btn btn-secondary">List View</a>
      <a href="activity_kanban.php" class="btn btn-primary">Kanban View</a>
      <a href="#" class="btn btn-secondary" title="Next">Gantt Chart (Soon)</a>
    </div>
  </div>

  <div class="kanban-board">
    <?php foreach($columns as $status => $cards): ?>
      <div class="kanban-column" data-status="<?= htmlspecialchars($status) ?>" draggable="false">
        <div class="kanban-header"><?= htmlspecialchars($status) ?></div>
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
              <div class="meta"><span><?= htmlspecialchars($c['department']) ?></span><span><?= $c['information_date']?date('d M Y',strtotime($c['information_date'])):'-' ?></span></div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<?php include './partials/layouts/layoutBottom.php'; ?>

