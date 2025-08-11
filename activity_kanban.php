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
// Drag & Drop + Edit Modal (HTML5)
document.addEventListener('DOMContentLoaded', function(){
  // Delegasi: tangkap dblclick di board agar lebih konsisten
  var boardEl = document.querySelector('.kanban-board');
  if(boardEl){
    boardEl.addEventListener('dblclick', function(e){
      var card = e.target.closest('.kanban-card');
      if(card){ e.preventDefault(); openKanbanEditModal(card.dataset.id, card); }
    });
  }

  document.querySelectorAll('.kanban-card').forEach(function(card){
    card.addEventListener('dragstart', function(e){ e.dataTransfer.setData('text/plain', this.dataset.id); });
    // Double click -> open edit modal directly on Kanban
    card.addEventListener('dblclick', function(){
      var id = this.dataset.id;
      openKanbanEditModal(id, this);
    });
    // Fallback: detect double-click via two quick clicks (<300ms)
    let lastClick = 0;
    card.addEventListener('click', function(e){
      const now = Date.now();
      if (now - lastClick < 300) {
        e.preventDefault();
        openKanbanEditModal(this.dataset.id, this);
      }
      lastClick = now;
    });
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

async function openKanbanEditModal(id, cardEl){
  try{
    const res = await fetch('get_activity.php?id=' + encodeURIComponent(id));
    if(!res.ok){ throw new Error('Gagal mengambil data'); }
    const data = await res.json();
    if(!data || !data.success){ throw new Error(data && data.message ? data.message : 'Gagal mengambil data'); }
    const a = data.activity || {};

    let modalEl = document.getElementById('editActivityModal');
    if(!modalEl){
      modalEl = document.createElement('div');
      modalEl.id = 'editActivityModal';
      modalEl.className = 'custom-modal-overlay';
      modalEl.innerHTML = `
      <div class="custom-modal">
        <div class="custom-modal-header">
          <h5 class="custom-modal-title">Edit Activity</h5>
          <button type="button" class="custom-modal-close" onclick="closeKanbanEditModal()">&times;</button>
        </div>
        <form id="kanbanEditForm">
        <div class="custom-modal-body">
          <input type="hidden" name="id" id="k_edit_id">
          <div class="custom-modal-row">
            <div class="custom-modal-col">
              <label class="custom-modal-label">No</label>
              <input type="number" name="no" id="k_edit_no" class="custom-modal-input">
            </div>
            <div class="custom-modal-col">
              <label class="custom-modal-label">Status *</label>
              <select name="status" id="k_edit_status" class="custom-modal-select" required>
                <option value="Open">Open</option>
                <option value="On Progress">On Progress</option>
                <option value="Need Requirement">Need Requirement</option>
                <option value="Done">Done</option>
                <option value="Cancel">Cancel</option>
              </select>
            </div>
          </div>
          <div class="custom-modal-row">
            <div class="custom-modal-col">
              <label class="custom-modal-label">Information Date *</label>
              <input type="date" name="information_date" id="k_edit_information_date" class="custom-modal-input" required>
            </div>
            <div class="custom-modal-col">
              <label class="custom-modal-label">Priority *</label>
              <select name="priority" id="k_edit_priority" class="custom-modal-select" required>
                <option value="Urgent">Urgent</option>
                <option value="Normal">Normal</option>
                <option value="Low">Low</option>
              </select>
            </div>
          </div>
          <div class="custom-modal-row">
            <div class="custom-modal-col">
              <label class="custom-modal-label">Department</label>
              <select name="department" id="k_edit_department" class="custom-modal-select">
                <option value="">Select Department</option>
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
              </select>
            </div>
            <div class="custom-modal-col">
              <label class="custom-modal-label">Application *</label>
              <select name="application" id="k_edit_application" class="custom-modal-select" required>
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
              </select>
            </div>
          </div>
          <div class="custom-modal-row">
            <div class="custom-modal-col">
              <label class="custom-modal-label">Type</label>
              <select name="type" id="k_edit_type" class="custom-modal-select">
                <option value="Setup">Setup</option>
                <option value="Question">Question</option>
                <option value="Issue">Issue</option>
                <option value="Report Issue">Report Issue</option>
                <option value="Report Request">Report Request</option>
                <option value="Feature Request">Feature Request</option>
              </select>
            </div>
            <div class="custom-modal-col">
              <label class="custom-modal-label">Description</label>
              <textarea name="description" id="k_edit_description" class="custom-modal-textarea" rows="3"></textarea>
            </div>
          </div>
        </div>
        <div class="custom-modal-footer">
          <button type="submit" class="custom-btn custom-btn-primary">Update</button>
          <button type="button" class="custom-btn custom-btn-secondary" onclick="closeKanbanEditModal()">Close</button>
        </div>
        </form>
      </div>`;
      document.body.appendChild(modalEl);
      document.getElementById('kanbanEditForm').addEventListener('submit', async function(ev){
        ev.preventDefault();
        const payload = {
          id: document.getElementById('k_edit_id').value,
          no: document.getElementById('k_edit_no').value,
          status: document.getElementById('k_edit_status').value,
          information_date: document.getElementById('k_edit_information_date').value,
          priority: document.getElementById('k_edit_priority').value,
          department: document.getElementById('k_edit_department').value,
          application: document.getElementById('k_edit_application').value,
          type: document.getElementById('k_edit_type').value,
          description: document.getElementById('k_edit_description').value
        };
        try{
          const resp = await fetch('update_activity_detail.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(payload)});
          const out = await resp.json();
          if(!out.success){ throw new Error(out.message||'Gagal update'); }
          // Update UI on card
          if(cardEl){
            const title = cardEl.querySelector('.kanban-title');
            if(title){
              const numberSpan = title.querySelector('span');
              if(numberSpan && payload.no){ numberSpan.textContent = payload.no; }
              const badges = title.querySelectorAll('.badge');
              if(badges[0]) badges[0].textContent = payload.type;
              if(badges[1]) badges[1].textContent = payload.application;
              if(badges[2]) badges[2].textContent = payload.priority;
            }
            const descEl = cardEl.querySelector('.text-truncate');
            if(descEl){ descEl.textContent = payload.description; }
            cardEl.classList.remove('accent-urgent','accent-normal','accent-low');
            const pri = (payload.priority||'Normal').toLowerCase();
            const accent = ['urgent','normal','low'].includes(pri) ? 'accent-'+pri : 'accent-normal';
            cardEl.classList.add(accent);
            const parentCol = cardEl.closest('.kanban-column');
            const currentStatus = parentCol ? parentCol.dataset.status : '';
            if(currentStatus !== payload.status){
              const targetCol = document.querySelector('.kanban-column[data-status="' + CSS.escape(payload.status) + '"] .kanban-cards');
              if(targetCol){ targetCol.prepend(cardEl); }
            }
          }
          if(window.logoNotificationManager){ window.logoNotificationManager.showActivityUpdated('Activity berhasil diperbarui!', 3000); }
          closeKanbanEditModal();
        }catch(err){
          console.error(err);
          if(window.logoNotificationManager){ window.logoNotificationManager.showActivityError('Gagal memperbarui activity!', 4000); }
        }
      });
    }

    document.getElementById('k_edit_id').value = a.id||id;
    document.getElementById('k_edit_no').value = a.no||'';
    document.getElementById('k_edit_status').value = a.status||'Open';
    document.getElementById('k_edit_information_date').value = a.information_date ? (a.information_date.substring(0,10)) : '';
    document.getElementById('k_edit_priority').value = a.priority||'Normal';
    if(a.department) document.getElementById('k_edit_department').value = a.department;
    if(a.application) document.getElementById('k_edit_application').value = a.application;
    if(a.type) document.getElementById('k_edit_type').value = a.type;
    document.getElementById('k_edit_description').value = a.description||'';

    modalEl.style.display = 'block';
    modalEl.style.visibility = 'visible';
    modalEl.style.opacity = '1';
  }catch(e){
    console.error(e);
    if(window.logoNotificationManager){ window.logoNotificationManager.showActivityError('Gagal membuka form edit!', 4000); }
  }
}

function closeKanbanEditModal(){
  const modal = document.getElementById('editActivityModal');
  if(modal){
    modal.style.display = 'none';
    modal.style.visibility = 'hidden';
    modal.style.opacity = '0';
  }
}

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

