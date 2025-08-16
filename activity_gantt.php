<?php
session_start();
require_once 'db.php';
require_once 'access_control.php';
require_login();

// Ambil data dari database -> mapping ke tasks untuk Gantt
$stmt = $pdo->query("SELECT id, no, description, status, type, priority, information_date, COALESCE(due_date, information_date) AS due_date FROM activities ORDER BY no ASC");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$tasks = array_map(function($r){
    $start = $r['information_date'] ?: date('Y-m-d');
    $end = $r['due_date'] ?: $start;
    if (strtotime($end) < strtotime($start)) { $end = $start; }
  return [
    'id' => (int)$r['id'],
    'no' => (int)$r['no'],
    'description' => (string)($r['description'] ?? '-'),
    'status' => (string)($r['status'] ?? 'Open'),
    'type' => (string)($r['type'] ?? 'Issue'),
    'priority' => (string)($r['priority'] ?? 'Normal'),
        'start' => $start,
        'end' => $end,
    ];
}, $rows);
?>
<?php include './partials/layouts/layoutHorizontal.php'; ?>
<style>
        body { font-family: 'Inter', sans-serif; }
        /* Force page background match list (fix outer light area) */
        html[data-theme="light"] body { background-color:#f8fafc !important; }
        html[data-theme="dark"] body { background-color:#0b1220 !important; }
        html[data-theme="dark"] .dashboard-main-body,
        html[data-theme="dark"] .content-wrapper,
        html[data-theme="dark"] .main-content,
        html[data-theme="dark"] .content {
            background-color:#0b1220 !important;
        }
        .gantt-grid { display: grid; grid-template-columns: 450px 1fr; }
        @media (max-width: 768px) { .gantt-grid { grid-template-columns: 350px 1fr; } }
        .timeline-grid-bg { display: grid; grid-template-columns: repeat(var(--total-days), minmax(40px, 1fr)); }
        .gantt-bar { position: absolute; height: 65%; top: 50%; transform: translateY(-50%); border-radius: 0.375rem; display: flex; align-items: center; box-shadow: 0 2px 4px rgba(0,0,0,0.1); transition: filter 0.2s; cursor: move; }
        .gantt-bar:hover { filter: brightness(0.95); }
        /* Status colors (seragam dengan list view: warning/info/secondary/success/danger) */
        .status-open{ background:linear-gradient(180deg,#f59e0b 0%,#d97706 100%); box-shadow:0 2px 6px rgba(245,158,11,.25); }
        .status-onprogress{ background:linear-gradient(180deg,#3b82f6 0%,#2563eb 100%); box-shadow:0 2px 6px rgba(59,130,246,.25); }
        .status-need{ background:linear-gradient(180deg,#7c3aed 0%,#6d28d9 100%); box-shadow:0 2px 6px rgba(124,58,237,.25); }
        .status-done{ background:linear-gradient(180deg,#10b981 0%,#059669 100%); box-shadow:0 2px 6px rgba(16,185,129,.25); }
        .status-cancel{ background:linear-gradient(180deg,#ef4444 0%,#dc2626 100%); box-shadow:0 2px 6px rgba(239,68,68,.25); }
        .status-default{ background-color:#94a3b8; }
        [data-theme="dark"] .status-open{ background:linear-gradient(180deg,#d97706 0%,#92400e 100%) !important; box-shadow:0 2px 10px rgba(245,158,11,.35) !important; outline:1px solid rgba(245,158,11,.25); }
        [data-theme="dark"] .status-onprogress{ background:linear-gradient(180deg,#1d4ed8 0%,#1e40af 100%) !important; box-shadow:0 2px 10px rgba(59,130,246,.35) !important; outline:1px solid rgba(59,130,246,.25); }
        [data-theme="dark"] .status-need{ background:linear-gradient(180deg,#6d28d9 0%,#4c1d95 100%) !important; box-shadow:0 2px 10px rgba(124,58,237,.35) !important; outline:1px solid rgba(124,58,237,.25); }
        .resize-handle { position: absolute; top: 0; bottom: 0; width: 8px; cursor: ew-resize; z-index: 10; }
        .resize-handle-left { left: 0; }
        .resize-handle-right { right: 0; }
        .gantt-row:hover .task-cell, .gantt-row:hover .timeline-cell { background-color: #f8fafc; }
        .tooltip { visibility: hidden; opacity: 0; transition: opacity 0.3s; }
        .has-tooltip:hover .tooltip { visibility: visible; opacity: 1; }
        .timeline-container::-webkit-scrollbar { height: 8px; }
        .timeline-container::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 10px; }
        .timeline-container::-webkit-scrollbar-thumb { background: #94a3b8; border-radius: 10px; }
        .timeline-container::-webkit-scrollbar-thumb:hover { background: #64748b; }
        /* Gantt container card (follow outer .card; internal border/shadow removed to match list) */
        .gantt-card { background:transparent; border:0; box-shadow:none; }
        /* soften grid borders to avoid bright edges */
        .timeline-grid-bg > div { border-left:1px solid #e5e7eb !important; }
        .saturday-bg { background-color: #fce7f3; }
        .sunday-bg { background-color: #fee2e2; }
        /* Today highlight */
        .today-col { background-color: rgba(59,130,246,.12) !important; }
        .today-line { position:absolute; top:0; bottom:0; width:2px; background:#3b82f6; opacity:.55; pointer-events:none; }
        .today-badge { display:inline-block; margin-top:2px; font-size:10px; font-weight:700; color:#fff; padding:2px 6px; border-radius:9999px; background:linear-gradient(135deg,#667eea 0%, #764ba2 100%); }
        /* Dark mode support (ikuti data-theme) */
        [data-theme="dark"] .bg-white { background-color: #111827 !important; }
        [data-theme="dark"] .text-slate-900 { color: #e5e7eb !important; }
        [data-theme="dark"] .text-slate-800 { color: #e5e7eb !important; }
        [data-theme="dark"] .text-slate-700 { color: #d1d5db !important; }
        [data-theme="dark"] .text-slate-600 { color: #cbd5e1 !important; }
        [data-theme="dark"] .text-slate-500 { color: #94a3b8 !important; }
        [data-theme="dark"] .bg-slate-50 { background-color: #0b1220 !important; }
        [data-theme="dark"] .bg-slate-100 { background-color: #0e1526 !important; }
        [data-theme="dark"] .border-slate-200 { border-color: #1f2937 !important; }
        [data-theme="dark"] .saturday-bg { background-color: rgba(236,72,153,.08); }
        [data-theme="dark"] .sunday-bg { background-color: rgba(239,68,68,.08); }
        [data-theme="dark"] .gantt-bar { box-shadow: 0 2px 6px rgba(0,0,0,.5); }
        [data-theme="dark"] .status-done{ background:linear-gradient(180deg,#059669 0%,#065f46 100%) !important; box-shadow:0 2px 10px rgba(16,185,129,.35) !important; outline:1px solid rgba(16,185,129,.25); }
        [data-theme="dark"] .status-cancel{ background:linear-gradient(180deg,#b91c1c 0%,#7f1d1d 100%) !important; box-shadow:0 2px 10px rgba(239,68,68,.35) !important; outline:1px solid rgba(239,68,68,.22); }
        [data-theme="dark"] .today-col { background-color: rgba(59,130,246,.18) !important; }
        /* Dark theme: remove inner border/shadow; use outer .card like list */
        [data-theme="dark"] .gantt-card { background:transparent !important; border-color:transparent !important; box-shadow:none !important; }
        /* Redupkan garis grid agar tidak menyala */
        [data-theme="dark"] .timeline-grid-bg > div { border-left:1px solid #152133 !important; }
        [data-theme="dark"] .timeline-container::-webkit-scrollbar-track { background:#111827; }
        [data-theme="dark"] .timeline-container::-webkit-scrollbar-thumb { background:#374151; }
        [data-theme="dark"] .timeline-container::-webkit-scrollbar-thumb:hover { background:#4b5563; }
        [data-theme="dark"] .gantt-row:hover .task-cell, 
        [data-theme="dark"] .gantt-row:hover .timeline-cell { background-color: rgba(255,255,255,.04); }
        /* Quick Edit modal dark-mode tweaks */
        [data-theme="dark"] .qe-card { background-color:#0f172a !important; border-color:#1e293b !important; color:#e5e7eb !important; box-shadow:0 24px 60px rgba(2,6,23,.7) !important; }
        [data-theme="dark"] .qe-card select { background-color:#111827 !important; border-color:#374151 !important; color:#e5e7eb !important; }
        [data-theme="dark"] .qe-card button { box-shadow:0 8px 16px rgba(0,0,0,.4) !important; }
        [data-theme="dark"] .qe-card button#qe_save { background:linear-gradient(135deg,#3b82f6 0%,#1d4ed8 100%) !important; }
        [data-theme="dark"] .qe-card button#qe_cancel { background:#4b5563 !important; }
        /* Wrap toggle styling */
        .wrap-toggle-wrap { display: inline-block; }
        .wrap-toggle-checkbox { display: none; }
        .wrap-toggle-track { 
            display: inline-block; 
            width: 40px; 
            height: 20px; 
            background: #d1d5db; 
            border-radius: 10px; 
            position: relative; 
            cursor: pointer; 
            transition: background 0.3s; 
        }
        .wrap-toggle-checkbox:checked + .wrap-toggle-track { background: #3b82f6; }
        .wrap-toggle-track::after { 
            content: ''; 
            position: absolute; 
            top: 2px; 
            left: 2px; 
            width: 16px; 
            height: 16px; 
            background: white; 
            border-radius: 50%; 
            transition: transform 0.3s; 
        }
        .wrap-toggle-checkbox:checked + .wrap-toggle-track::after { transform: translateX(20px); }
        .wrap-badge { 
            display: inline-block; 
            padding: 2px 8px; 
            background: #6b7280; 
            color: white; 
            border-radius: 12px; 
            font-size: 10px; 
            font-weight: 600; 
            text-transform: uppercase; 
        }
        .wrap-badge.on { background: #10b981; }
        [data-theme="dark"] .wrap-toggle-track { background: #4b5563; }
        [data-theme="dark"] .wrap-toggle-checkbox:checked + .wrap-toggle-track { background: #1d4ed8; }
        [data-theme="dark"] .wrap-badge { background: #6b7280; }
        [data-theme="dark"] .wrap-badge.on { background: #059669; }
        
        /* Quick edit button styling */
        .quick-edit-btn {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            color: white;
            border: none;
            padding: 4px 12px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(59, 130, 246, 0.2);
        }
        .quick-edit-btn:hover {
            background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(59, 130, 246, 0.3);
        }
        [data-theme="dark"] .quick-edit-btn {
            background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);
            box-shadow: 0 2px 4px rgba(29, 78, 216, 0.4);
        }
        [data-theme="dark"] .quick-edit-btn:hover {
            background: linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%);
            box-shadow: 0 4px 8px rgba(29, 78, 216, 0.5);
        }
        
        /* Info trigger and tooltip styling */
        .info-trigger{ 
            position:relative; 
            display:none !important; 
            align-items:center; 
            justify-content:center; 
            width:18px; 
            height:18px; 
            border-radius:9999px; 
            background:#334155; 
            color:#fff; 
            font-size:12px; 
            cursor:default; 
        }
        .info-trigger:hover .info-tip{ 
            opacity:1; 
            visibility:visible; 
            transform:translateY(-2px); 
        }
        .info-tip{ 
            position:absolute; 
            bottom:125%; 
            left:50%; 
            transform:translateX(-50%); 
            background:#111827; 
            color:#e5e7eb; 
            font-size:11px; 
            padding:6px 8px; 
            border-radius:6px; 
            white-space:nowrap; 
            opacity:0; 
            visibility:hidden; 
            transition:all .15s ease; 
            pointer-events:none; 
            box-shadow:0 6px 16px rgba(0,0,0,.3); 
        }
        [data-theme="light"] .info-tip{ 
            background:#0f172a; 
            color:#e5e7eb; 
        }
        .collapse-icon { 
            transition: transform 0.2s ease-in-out; 
        }
        .collapsed .collapse-icon { 
            transform: rotate(-90deg); 
        }
        /* Task cell height to match header */
        .task-cell { min-height: 80px; }
        .timeline-cell { min-height: 80px; }
    </style>
    <style>
    /* Page-level card tone override to match Activity List */
    [data-theme="light"] .card { background-color:#f8fafc !important; border:1px solid #e5e7eb !important; box-shadow:0 1px 2px rgba(0,0,0,.06) !important; }
    [data-theme="dark"] .card { background-color:#0f172a !important; border:1px solid #1e293b !important; box-shadow:0 1px 2px rgba(0,0,0,.35) !important; }
</style>
    <script>const SERVER_TASKS = <?php echo json_encode($tasks, JSON_UNESCAPED_UNICODE); ?>;</script>

    <div class="dashboard-main-body" id="gantt-root">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
    <h6 class="fw-semibold mb-0">Activity Gantt</h6>
            <ul class="d-flex align-items-center gap-2">
                <li class="fw-medium">
                    <a href="index.php" class="d-flex align-items-center gap-1 hover-text-primary">
                        <iconify-icon icon="solar:home-smile-angle-outline" class="icon text-lg"></iconify-icon>
                        Dashboard
                    </a>
                </li>
                <li>-</li>
                <li class="fw-medium">Activity Gantt</li>
            </ul>
        </div>

        <div class="card">
            <div class="d-flex justify-content-end p-3"><div class="d-flex gap-2">
      <a href="activity.php" class="btn btn-secondary">List View</a>
      <a href="activity_kanban.php" class="btn btn-secondary">Kanban View</a>
      <a href="activity_gantt.php" class="btn btn-primary">Gantt Chart</a>
            </div></div>
            
            <!-- Header Section -->
            <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-2">
                    <span class="fw-semibold">Show</span>
                    <select class="form-select form-select-sm w-auto" id="gantt-limit">
                        <option value="10">10</option>
                        <option value="15">15</option>
                        <option value="20">20</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
            </div>
            
            <!-- Filter Section -->
            <div class="filter-section">
                <form method="get" class="filter-form" id="gantt-filter-form">
                    <div class="filter-row">
                        <div class="filter-group">
                            <label class="filter-label">Search</label>
                            <div class="icon-field">
                                <input type="text" name="search" id="gantt-search" class="form-control" placeholder="Search activities...">
                                <span class="icon">
                                    <iconify-icon icon="ion:search-outline"></iconify-icon>
                                </span>
                            </div>
                        </div>
                        <div class="filter-group">
                            <label class="filter-label">Priority</label>
                            <select class="form-select" name="filter_priority" id="gantt-filter-priority">
                                <option value="">All Priority</option>
                                <option value="Urgent">Urgent</option>
                                <option value="Normal">Normal</option>
                                <option value="Low">Low</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label class="filter-label">Department</label>
                            <select class="form-select" name="filter_department" id="gantt-filter-department">
                                <option value="">All Department</option>
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
                        <div class="filter-group">
                            <label class="filter-label">Application</label>
                            <select class="form-select" name="filter_application" id="gantt-filter-application">
                                <option value="">All Application</option>
                                <option value="POS">POS</option>
                                <option value="PMS">PMS</option>
                                <option value="Back Office">Back Office</option>
                                <option value="Website">Website</option>
                                <option value="Mobile App">Mobile App</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label class="filter-label">Type</label>
                            <select class="form-select" name="filter_type" id="gantt-filter-type">
                                <option value="">All Type</option>
                                <option value="Setup">Setup</option>
                                <option value="Question">Question</option>
                                <option value="Issue">Issue</option>
                                <option value="Report Issue">Report Issue</option>
                                <option value="Report Request">Report Request</option>
                                <option value="Feature Request">Feature Request</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label class="filter-label">Status</label>
                            <select class="form-select" name="filter_status" id="gantt-filter-status">
                                <option value="">All Status</option>
                                <option value="not_done">Active (Default)</option>
                                <option value="Open">Open</option>
                                <option value="On Progress">On Progress</option>
                                <option value="Need Requirement">Need Requirement</option>
                                <option value="Done">Done</option>
                                <option value="Cancel">Cancel</option>
                            </select>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn-apply" id="gantt-apply-filters">Apply Filters</button>
                        <button type="button" class="btn-reset" id="gantt-reset-filters">Reset</button>
                    </div>
                </form>
            </div>
            
            <div class="card-body">
        
        <div class="gantt-card rounded-xl overflow-hidden">
            <div class="p-3 border-b border-slate-200">
                <div class="w-full text-center mb-2 select-none">
                    <span id="month-year-display" class="text-sm font-semibold text-slate-600"></span>
                </div>
                <div class="flex items-start justify-between flex-wrap gap-3">
                    <div class="flex flex-col gap-2">
                        <div class="flex items-center gap-2 select-none">
                            <label for="wrap-toggle" class="text-sm font-medium text-slate-600 select-none cursor-pointer">Wrap Description</label>
                            <div class="wrap-toggle-wrap mr-2 align-middle select-none">
                                <input type="checkbox" role="switch" aria-checked="false" aria-label="Toggle wrap description" tabindex="0" name="wrap-toggle" id="wrap-toggle" class="wrap-toggle-checkbox cursor-pointer"/>
                                <label for="wrap-toggle" class="wrap-toggle-track cursor-pointer"></label>
                            </div>
                            <span id="wrap-status" class="wrap-badge">OFF</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <button id="today-btn" class="px-3 py-1.5 text-sm font-medium bg-white border border-slate-300 rounded-md hover:bg-slate-100 transition leading-none">Hari Ini</button>
                            <button id="prev-month-btn" class="px-3 py-1.5 text-sm font-medium bg-white border border-slate-300 rounded-md hover:bg-slate-100 transition">&lt;</button>
                            <button id="next-month-btn" class="px-3 py-1.5 text-sm font-medium bg-white border border-slate-300 rounded-md hover:bg-slate-100 transition">&gt;</button>
    </div>
  </div>
      </div>
    </div>
    
            <div>
                <div class="gantt-grid grid w-full sticky top-0 bg-slate-100 z-10 border-b border-slate-200">
                    <div class="flex items-center h-12 px-4 border-r border-slate-200">
                        <h3 class="font-semibold text-slate-600 uppercase text-sm">Description</h3>
            </div>
                    <div class="timeline-container overflow-x-auto">
                        <div id="timeline-dates" class="timeline-grid-bg h-20"></div>
          </div>
        </div>
                <div id="gantt-body"></div>
        </div>
          </div>
        </div>
      </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            let allTasks = (Array.isArray(SERVER_TASKS) ? SERVER_TASKS : []).map(t => ({
                id: t.id,
                no: t.no,
                description: t.description || '-',
                status: t.status || 'Open',
                type: t.type || 'Issue',
                priority: t.priority || 'Normal',
                start: t.start,
                end: t.end
            }));

            const timelineDatesEl = document.getElementById('timeline-dates');
            const ganttBodyEl = document.getElementById('gantt-body');
            const monthYearDisplay = document.getElementById('month-year-display');
            const wrapToggle = document.getElementById('wrap-toggle');
            const wrapStatus = document.getElementById('wrap-status');
            const todayBtn = document.getElementById('today-btn');
            const prevMonthBtn = document.getElementById('prev-month-btn');
            const nextMonthBtn = document.getElementById('next-month-btn');

            let currentDate = new Date();

            // Filter and search functionality
            let filteredTasks = [...allTasks];
            
            function applyFilters() {
                const searchTerm = document.getElementById('gantt-search').value.toLowerCase();
                const priority = document.getElementById('gantt-filter-priority').value;
                const department = document.getElementById('gantt-filter-department').value;
                const application = document.getElementById('gantt-filter-application').value;
                const type = document.getElementById('gantt-filter-type').value;
                const status = document.getElementById('gantt-filter-status').value;
                const limit = parseInt(document.getElementById('gantt-limit').value);
                
                filteredTasks = allTasks.filter(task => {
                    // Search filter
                    if (searchTerm && !task.description.toLowerCase().includes(searchTerm)) {
                        return false;
                    }
                    
                    // Priority filter
                    if (priority && task.priority !== priority) {
                        return false;
                    }
                    
                    // Status filter
                    if (status) {
                        if (status === 'not_done' && task.status === 'Done') {
                            return false;
                        } else if (status !== 'not_done' && task.status !== status) {
                            return false;
                        }
                    }
                    
                    // Type filter
                    if (type && task.type !== type) {
                        return false;
                    }
                    
                    return true;
                });
                
                // Apply limit
                if (limit > 0) {
                    filteredTasks = filteredTasks.slice(0, limit);
                }
                
                // Re-render gantt with filtered tasks
                renderGantt(currentDate);
            }
            
            function resetFilters() {
                document.getElementById('gantt-search').value = '';
                document.getElementById('gantt-filter-priority').value = '';
                document.getElementById('gantt-filter-department').value = '';
                document.getElementById('gantt-filter-application').value = '';
                document.getElementById('gantt-filter-type').value = '';
                document.getElementById('gantt-filter-status').value = '';
                document.getElementById('gantt-limit').value = '10';
                
                filteredTasks = [...allTasks];
                renderGantt(currentDate);
            }
            
            // Event listeners for filters
            document.getElementById('gantt-apply-filters').addEventListener('click', applyFilters);
            document.getElementById('gantt-reset-filters').addEventListener('click', resetFilters);
            document.getElementById('gantt-search').addEventListener('input', applyFilters);
            document.getElementById('gantt-limit').addEventListener('change', applyFilters);
            
            // Real-time search with debounce
            let searchTimeout;
            document.getElementById('gantt-search').addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(applyFilters, 300);
            });
            
            // Modal functions
            function openQuickEditModal(task) {
                // Redirect to activity.php for editing
                window.location.href = `activity.php?edit=${task.id}`;
            }

            const renderGantt = (date) => {
                timelineDatesEl.innerHTML = '';
                ganttBodyEl.innerHTML = '';

                const timelineStartDate = new Date(date.getFullYear(), date.getMonth(), 1);
                const timelineEndDate = new Date(date.getFullYear(), date.getMonth() + 1, 0);

                const statusColors = {
                    'Open': 'status-open',
                    'On Progress': 'status-onprogress',
                    'Need Requirement': 'status-need',
                    'Done': 'status-done',
                    'Cancel': 'status-cancel'
                };
                const priorityPills = { 'Low': 'bg-slate-200 text-slate-600', 'Normal': 'bg-sky-200 text-sky-700', 'Urgent': 'bg-red-200 text-red-700' };
                const typeIcons = {
                    'Issue': `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-bug-fill text-red-500" viewBox="0 0 16 16"><path d="M4.978.855a.5.5 0 1 0-.956.29l.41 1.352A4.985 4.985 0 0 0 3 6h10a4.985 4.985 0 0 0-1.432-3.503l.41-1.352a.5.5 0 1 0-.956-.29l-.291.956A4.978 4.978 0 0 0 8 1a4.979 4.979 0 0 0-2.731.811l-.29-.956z"/><path d="M13 6v1H8.5v8.975A5 5 0 0 0 13 11h.5a.5.5 0 0 1 .5.5v.5a.5.5 0 1 0 1 0v-.5a1.5 1.5 0 0 0-1.5-1.5H13V9h1.5a.5.5 0 0 0 0-1H13V7h.5A1.5 1.5 0 0 0 15 5.5V5a.5.5 0 0 0-1 0v.5a.5.5 0 0 1-.5.5H13zm-5.5 9.975V7H3V6h-.5a.5.5 0 0 1-.5-.5V5a.5.5 0 0 0-1 0v.5A1.5 1.5 0 0 0 2.5 7H3v1H1.5a.5.5 0 0 0 0 1H3v2h-.5A1.5 1.5 0 0 0 1 11.5V12a.5.5 0 0 0 1 0v-.5a.5.5 0 0 1 .5-.5H3a5 5 0 0 0 4.5 4.975z"/></svg>`,
                    'Setup': `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-gear-fill text-blue-500" viewBox="0 0 16 16"><path d="M9.405 1.05c-.413-1.4-2.397-1.4-2.81 0l-.1.34a1.464 1.464 0 0 1-2.105.872l-.31-.17c-1.283-.698-2.686.705-1.987 1.987l.169.311a1.464 1.464 0 0 1-.872 2.105l-.34.1c-1.4.413-1.4 2.397 0 2.81l.34.1a1.464 1.464 0 0 1 .872 2.105l-.17.31c-.698 1.283.705 2.686 1.987 1.987l.311-.169a1.464 1.464 0 0 1 2.105.872l.1.34c.413 1.4 2.397 1.4 2.81 0l.1-.34a1.464 1.464 0 0 1 2.105-.872l.31.17c1.283.698 2.686-.705-1.987-1.987l-.169-.311a1.464 1.464 0 0 1 .872-2.105l.34-.1c-1.4-.413-1.4-2.397 0-2.81l.34-.1a1.464 1.464 0 0 1 .872-2.105l.17-.31c.698-1.283-.705-2.686-1.987-1.987l-.311.169a1.464 1.464 0 0 1-2.105-.872l-.1-.34zM8 10.93a2.929 2.929 0 1 1 0-5.858 2.929 2.929 0 0 1 0 5.858z"/></svg>`,
                    'Question': `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-question-circle-fill text-gray-500" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM5.496 6.033h.825c.138 0 .248-.113.266-.25.09-.656.54-1.134 1.342-1.134.686 0 1.314.343 1.314 1.168 0 .635-.374.927-.965 1.371-.673.489-1.206 1.06-1.168 1.987l.003.217a.25.25 0 0 0 .25.246h.811a.25.25 0 0 0 .25-.25v-.105c0-.718.273-.927 1.01-1.486.609-.463 1.244-.977 1.244-2.056 0-1.511-1.276-2.241-2.673-2.241-1.267 0-2.655.59-2.75 2.286a.237.237 0 0 0 .241.247zm2.325 6.443c.61 0 1.029-.394 1.029-.927 0-.552-.42-.94-1.029-.94-.584 0-1.009.388-1.009.94 0 .533.425.927 1.01.927z"/></svg>`
                };
                const groupOrder = ['Issue', 'Setup', 'Question'];

                const getDayDiff = (startDate, endDate) => {
                    const msPerDay = 1000 * 60 * 60 * 24;
                    const startUTC = Date.UTC(startDate.getFullYear(), startDate.getMonth(), startDate.getDate());
                    const endUTC = Date.UTC(endDate.getFullYear(), endDate.getMonth(), endDate.getDate());
                    return Math.floor((endUTC - startUTC) / msPerDay);
                };

                const formatDate = (dateString) => new Date(dateString + 'T00:00:00').toLocaleDateString('id-ID', { day: '2-digit', month: 'short' });
                const toISODateString = (date) => date.toISOString().split('T')[0];

                const totalDays = getDayDiff(timelineStartDate, timelineEndDate) + 1;
                document.documentElement.style.setProperty('--total-days', totalDays);
                
                monthYearDisplay.textContent = timelineStartDate.toLocaleDateString('id-ID', { month: 'long', year: 'numeric' });

                let todayIndex = -1;
                const todayISO = new Date(); todayISO.setHours(0,0,0,0);
                for (let i = 0; i < totalDays; i++) {
                    const date = new Date(timelineStartDate);
                    date.setDate(date.getDate() + i);
                    const dayOfWeek = date.getDay();
                    const day = date.getDate();
                    const dayName = date.toLocaleDateString('id-ID', { weekday: 'short' }).charAt(0);
                    
                    const dateCell = document.createElement('div');
                    dateCell.className = 'flex flex-col items-center justify-center h-20 text-center border-l border-slate-200';
                    // mark today
                    const checkDate = new Date(date); checkDate.setHours(0,0,0,0);
                    if (checkDate.getTime() === todayISO.getTime()) {
                        dateCell.classList.add('today-col');
                        todayIndex = i;
                    }
                    if (dayOfWeek === 6) { dateCell.classList.add('saturday-bg'); } 
                    else if (dayOfWeek === 0) { dateCell.classList.add('sunday-bg'); }

                    dateCell.innerHTML = `<span class="text-xs text-slate-500">${dayName}</span><span class="font-semibold text-slate-700 mt-1">${day}</span>` + (checkDate.getTime()===todayISO.getTime()?`<span class="today-badge">Today</span>`:'');
                    timelineDatesEl.appendChild(dateCell);
                }

                const groupedTasks = filteredTasks.reduce((acc, task) => {
                    (acc[task.type] = acc[task.type] || []).push(task);
                    return acc;
                }, {});
                
                groupOrder.forEach(groupName => {
                    if (!groupedTasks[groupName]) return;

                    const groupHeaderRow = document.createElement('div');
                    groupHeaderRow.className = 'group-header gantt-grid grid w-full bg-slate-100 border-t border-b border-slate-200 cursor-pointer';
                    groupHeaderRow.innerHTML = `
                        <div class="px-4 py-2 font-bold text-slate-700 flex items-center gap-2 border-r border-slate-200">
                            <svg class="collapse-icon w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            ${typeIcons[groupName] || ''} ${groupName}
                        </div>
                        <div class="timeline-container overflow-x-auto"></div>`;
                    ganttBodyEl.appendChild(groupHeaderRow);

                    const taskRowsContainer = document.createElement('div');
                    taskRowsContainer.dataset.group = groupName;
                    ganttBodyEl.appendChild(taskRowsContainer);

                    groupedTasks[groupName].forEach(task => {
                        const fullRow = document.createElement('div');
                        fullRow.className = 'gantt-row gantt-grid grid w-full border-t border-slate-200';

                        const taskCell = document.createElement('div');
                        taskCell.className = 'task-cell flex items-start gap-3 px-4 py-3 border-r border-slate-200 transition-colors duration-200 has-tooltip relative';
                        taskCell.innerHTML = `
                            <div class="flex-shrink-0 pt-1">${typeIcons[task.type] || ''}</div>
                            <div class="flex-grow overflow-hidden">
                                <p class="task-description font-medium text-slate-800 ${wrapToggle.checked ? 'whitespace-normal' : 'truncate'}" title="${task.description}">${task.description}</p>
                                <div class="flex items-center flex-wrap gap-x-3 gap-y-1 mt-2 text-xs text-slate-500">
                                    <span class="inline-flex items-center font-semibold px-1.5 py-0.5 rounded-full text-[11px] ${priorityPills[task.priority] || ''}">${task.priority}</span>
                                    <span class="inline-flex items-center">${task.status}</span>
                                    <span class="inline-flex items-center">${formatDate(task.start)}</span>
                                    <span class="inline-flex items-center">${formatDate(task.end)}</span>
                                </div>
                            </div>`;

                        // Quick edit button
                        const quickBtn = document.createElement('button');
                        quickBtn.textContent = 'Edit';
                        quickBtn.className = 'ml-2 quick-edit-btn';
                        quickBtn.addEventListener('click', (ev) => {
                            ev.stopPropagation();
                            openQuickEditModal(task);
                        });
                        taskCell.appendChild(quickBtn);

                        const timelineCell = document.createElement('div');
                        timelineCell.className = 'timeline-cell relative transition-colors duration-200';
                        
                        const timelineGridBg = document.createElement('div');
                        timelineGridBg.className = 'absolute inset-0 timeline-grid-bg';
                        for (let i = 0; i < totalDays; i++) {
                            const date = new Date(timelineStartDate);
                            date.setDate(date.getDate() + i);
                            const dayOfWeek = date.getDay();
                            const gridCell = document.createElement('div');
                            gridCell.className = 'border-l border-slate-200/70 h-full';
                            const checkDate = new Date(date); checkDate.setHours(0,0,0,0);
                            if (todayIndex === i) { gridCell.classList.add('today-col'); }
                            if (dayOfWeek === 6) { gridCell.classList.add('saturday-bg'); } 
                            else if (dayOfWeek === 0) { gridCell.classList.add('sunday-bg'); }
                            timelineGridBg.appendChild(gridCell);
                        }
                        timelineCell.appendChild(timelineGridBg);

                        // today vertical line overlay
                        if (todayIndex >= 0) {
                            const line = document.createElement('div');
                            line.className = 'today-line';
                            const leftPercent = (todayIndex / totalDays) * 100;
                            line.style.left = `calc(${leftPercent}% + 1px)`;
                            timelineCell.appendChild(line);
                        }

                        const taskStartDate = new Date(task.start + 'T00:00:00');
                        const taskEndDate = new Date(task.end + 'T00:00:00');
                        const startOffset = getDayDiff(timelineStartDate, taskStartDate);
                        const duration = getDayDiff(taskStartDate, taskEndDate) + 1;

                        if (startOffset >= 0 && startOffset < totalDays) {
                            const ganttBar = document.createElement('div');
                            ganttBar.className = `gantt-bar has-tooltip ${statusColors[task.status] || 'status-default'}`;
                            ganttBar.style.left = `calc(${startOffset / totalDays * 100}% + 1px)`;
                            ganttBar.style.width = `calc(${duration / totalDays * 100}% - 2px)`;
                            
                            const handleLeft = document.createElement('div');
                            handleLeft.className = 'resize-handle resize-handle-left';
                            ganttBar.appendChild(handleLeft);

                            const handleRight = document.createElement('div');
                            handleRight.className = 'resize-handle resize-handle-right';
                            ganttBar.appendChild(handleRight);

                        const dayWidth = timelineDatesEl.querySelector('div')?.offsetWidth || 40;
                        const originalTask = allTasks.find(t => t.no === task.no);

                            const startDrag = (e, action) => {
                                e.preventDefault(); e.stopPropagation();
                                const initialX = e.clientX;
                                const originalStartDate = new Date(originalTask.start + 'T00:00:00');
                                const originalEndDate = new Date(originalTask.end + 'T00:00:00');

                                const handleMouseMove = (moveEvent) => {
                                    const deltaX = moveEvent.clientX - initialX;
                                    const dayOffset = Math.round(deltaX / dayWidth);

                                    if (action === 'move') {
                                        const taskDuration = getDayDiff(originalStartDate, originalEndDate);
                                        const newStartDate = new Date(originalStartDate);
                                        newStartDate.setDate(newStartDate.getDate() + dayOffset);
                                        const newEndDate = new Date(newStartDate);
                                        newEndDate.setDate(newEndDate.getDate() + taskDuration);
                                        originalTask.start = toISODateString(newStartDate);
                                        originalTask.end = toISODateString(newEndDate);
                                    } else if (action === 'resize-left') {
                                        const newStartDate = new Date(originalStartDate);
                                        newStartDate.setDate(newStartDate.getDate() + dayOffset);
                                        if (newStartDate <= originalEndDate) { originalTask.start = toISODateString(newStartDate); }
                                    } else if (action === 'resize-right') {
                                        const newEndDate = new Date(originalEndDate);
                                        newEndDate.setDate(newEndDate.getDate() + dayOffset);
                                        if (newEndDate >= originalStartDate) { originalTask.end = toISODateString(newEndDate); }
                                    }
                                    renderGantt(currentDate);
                                };

                                const handleMouseUp = () => {
                                    document.removeEventListener('mousemove', handleMouseMove);
                                    document.removeEventListener('mouseup', handleMouseUp);
                                };

                                document.addEventListener('mousemove', handleMouseMove);
                                document.addEventListener('mouseup', handleMouseUp);
                            };

                            ganttBar.addEventListener('mousedown', (e) => startDrag(e, 'move'));
                            handleLeft.addEventListener('mousedown', (e) => startDrag(e, 'resize-left'));
                            handleRight.addEventListener('mousedown', (e) => startDrag(e, 'resize-right'));

                            timelineCell.appendChild(ganttBar);
                        }
                        
                        fullRow.appendChild(taskCell);
                        fullRow.appendChild(timelineCell);
                        taskRowsContainer.appendChild(fullRow);
                    });
                });

                document.querySelectorAll('.group-header').forEach((header) => {
                    header.addEventListener('click', () => {
                        header.classList.toggle('collapsed');
                        const body = header.nextElementSibling;
                        if (body) body.classList.toggle('hidden');
                    });
                });
            };

            // Force theme from localStorage on load (avoid stale cookie/body attribute conflicts)
            try {
                const savedTheme = localStorage.getItem('theme');
                if (savedTheme === 'dark' || savedTheme === 'light') {
                    document.documentElement.setAttribute('data-theme', savedTheme);
                }
            } catch (_) {}

            // Restore saved preference
            try {
                const savedWrap = localStorage.getItem('gantt_wrap_desc');
                if (savedWrap !== null) {
                    wrapToggle.checked = savedWrap === 'true';
                    wrapToggle.setAttribute('aria-checked', String(wrapToggle.checked));
                }
            } catch (_) {}

            function updateWrapStatusVisual(){
                if (!wrapStatus) return;
                if (wrapToggle.checked) {
                    wrapStatus.textContent = 'ON';
                    wrapStatus.classList.add('on');
                } else {
                    wrapStatus.textContent = 'OFF';
                    wrapStatus.classList.remove('on');
                }
            }
            updateWrapStatusVisual();

            // Keyboard accessibility
            wrapToggle.addEventListener('keydown', function(e){
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    wrapToggle.checked = !wrapToggle.checked;
                    wrapToggle.dispatchEvent(new Event('change'));
                }
            });

            wrapToggle.addEventListener('change', function() {
                const descriptions = document.querySelectorAll('.task-description');
                descriptions.forEach(desc => {
                    desc.classList.toggle('truncate', !this.checked);
                    desc.classList.toggle('whitespace-normal', this.checked);
                });
                wrapToggle.setAttribute('aria-checked', String(wrapToggle.checked));
                updateWrapStatusVisual();
                try { localStorage.setItem('gantt_wrap_desc', String(wrapToggle.checked)); } catch(_) {}
            });

            todayBtn.addEventListener('click', () => { currentDate = new Date(); renderGantt(currentDate); });
            prevMonthBtn.addEventListener('click', () => { currentDate.setMonth(currentDate.getMonth() - 1); renderGantt(currentDate); });
            nextMonthBtn.addEventListener('click', () => { currentDate.setMonth(currentDate.getMonth() + 1); renderGantt(currentDate); });

            // Listen to global theme changes (e.g., navbar/footer toggle) and re-render for grid tone adjustments
            const themeObserver = new MutationObserver(() => { renderGantt(currentDate); });
            themeObserver.observe(document.documentElement, { attributes: true, attributeFilter: ['data-theme'] });

            // Debounce + only-diff + batch persist
            const originalMap = new Map();
            allTasks.forEach(t => { originalMap.set(t.id, { start: t.start, end: t.end }); });

            let persistTimer = null;
            function schedulePersist() {
                if (persistTimer) clearTimeout(persistTimer);
                persistTimer = setTimeout(() => {
                    const changes = [];
                    allTasks.forEach(t => {
                        const o = originalMap.get(t.id) || {};
                        if (o.start !== t.start || o.end !== t.end) {
                            changes.push({ id: t.id, start: t.start, end: t.end });
                        }
                    });
                    if (changes.length === 0) return;
                    fetch('update_activity_dates_batch.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        credentials: 'same-origin',
                        body: JSON.stringify({ changes })
                    }).then(async r => {
                        // Perlakukan 2xx sebagai sukses walau parsing JSON gagal
                        let res = null;
                        try { res = await r.clone().json(); } catch (_) {}
                        if (r.ok && (!res || res.success !== false)) {
                            // Sync snapshot
                            changes.forEach(c => originalMap.set(c.id, { start: c.start, end: c.end }));
                            const hasLogoNotif = (window.logoNotificationManager && typeof window.logoNotificationManager.isAvailable === 'function' && window.logoNotificationManager.isAvailable());
                            if (!hasLogoNotif && window.showActivityToast) window.showActivityToast('Tanggal berhasil disimpan', 'success', 2500);
                        } else {
                            if (window.showActivityToast) window.showActivityToast('Gagal menyimpan tanggal', 'error', 3500);
                        }
                    }).catch((err) => {
                        console.warn('Persist error:', err);
                        if (window.showActivityToast) window.showActivityToast('Jaringan bermasalah saat menyimpan', 'error', 3500);
                    });
                }, 400);
            }

            // Schedule persist only after actual drag/resize inside chart
            document.getElementById('gantt-body').addEventListener('mouseup', schedulePersist);

            renderGantt(currentDate);
});
</script>
    <script src="assets/js/activity-notifications.js"></script>

<?php include './partials/layouts/layoutBottom.php'; ?>


