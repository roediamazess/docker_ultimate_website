<?php
session_start();
require_once 'db.php';
require_once 'access_control.php';
require_login();

// Fetch activities from database
$stmt = $pdo->query("SELECT id, no, information_date, COALESCE(due_date, information_date) AS due_date, priority, department, application, type, description, status FROM activities ORDER BY information_date ASC, no ASC");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Prepare data for JavaScript
$tasks = [];
foreach ($rows as $r) {
    $start = $r['information_date'] ?: date('Y-m-d');
    $end = $r['due_date'] ?: $start;
    if (strtotime($end) < strtotime($start)) { $end = $start; }
    if ($end === $start) { $end = date('Y-m-d', strtotime($start . ' +1 day')); }
    
    $desc = trim((string)($r['description'] ?? ''));
    $descShort = $desc !== '' ? (strlen($desc) > 80 ? substr($desc, 0, 80) . '…' : $desc) : '-';
    
    $tasks[] = [
        'id' => (string)$r['id'],
        'no' => (string)$r['no'],
        'name' => $descShort,
        'start' => $start,
        'end' => $end,
        'status' => $r['status'] ?? 'Open',
        'priority' => $r['priority'] ?? 'Normal',
        'department' => $r['department'] ?? '-',
        'application' => $r['application'] ?? '-',
        'type' => $r['type'] ?? '-'
    ];
}
?>
<?php include './partials/layouts/layoutHorizontal.php'; ?>

<style>
.gantt-container {
  display: flex;
  flex-direction: column;
  height: calc(100vh - 150px);
  background: #fff;
  border-radius: 8px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  overflow: hidden;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.gantt-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 20px;
  border-bottom: 1px solid #e0e0e0;
  background: #fafafa;
}

.gantt-header h2 {
  margin: 0;
  font-size: 1.5rem;
  color: #333;
}

.gantt-toolbar {
  display: flex;
  gap: 10px;
}

.gantt-toolbar button {
  padding: 6px 12px;
  background: #f0f0f0;
  border: 1px solid #d0d0d0;
  border-radius: 3px;
  cursor: pointer;
  font-size: 13px;
  transition: all 0.2s;
  color: #333;
}

.gantt-toolbar button:hover {
  background: #f0f0f0;
}

.gantt-toolbar button.active {
  background: #007bff;
  color: white;
  border-color: #007bff;
}

.gantt-toolbar button:hover {
  background: #e0e0e0;
  border-color: #c0c0c0;
}

.gantt-toolbar button.active:hover {
  background: #0069d9;
  border-color: #0062cc;
}

.gantt-body {
  display: flex;
  flex: 1;
  overflow: hidden;
}

.gantt-sidebar {
  width: 350px;
  border-right: 1px solid #e0e0e0;
  overflow-y: auto;
  background: #fff;
}

.gantt-task-list {
  list-style: none;
  padding: 0;
  margin: 0;
}

.gantt-task-item {
  padding: 18px 12px;
  border-bottom: 1px solid #eee;
  cursor: pointer;
  transition: background 0.2s;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  display: flex;
  flex-direction: column;
  justify-content: center;
}

.gantt-task-item:hover {
  background: #f5f9ff;
}

.gantt-task-item.active {
  background: #e3f2fd;
}

.gantt-task-item:hover {
  background: #f0f8ff;
}

.gantt-task-item.active {
  background: #e3f2fd;
}

.gantt-task-header {
  display: flex;
  justify-content: space-between;
  margin-bottom: 5px;
}

.gantt-task-no {
  font-weight: bold;
  color: #007bff;
}

.gantt-task-name {
  font-weight: 500;
  margin: 0 0 4px 0;
  font-size: 13px;
  color: #333;
}

.gantt-task-meta {
  display: flex;
  gap: 15px;
  font-size: 11px;
  color: #666;
}

.gantt-task-status {
  padding: 2px 6px;
  border-radius: 3px;
  font-size: 11px;
  font-weight: 500;
}

.status-open {
  background: #fff3cd;
  color: #856404;
}

.status-on-progress {
  background: #cce5ff;
  color: #004085;
}

.status-done {
  background: #d4edda;
  color: #155724;
}

.status-cancel {
  background: #f8d7da;
  color: #721c24;
}

.status-need-requirement {
  background: #e2d9f3;
  color: #4b2e83;
}

.gantt-main {
  flex: 1;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.gantt-chart-header {
  display: flex;
  padding: 10px 0;
  border-bottom: 1px solid #e0e0e0;
  background: #f8f9fa;
}

.gantt-time-header {
  display: flex;
  flex: 1;
}

.gantt-month {
  flex: 1;
  text-align: center;
  font-weight: 500;
  font-size: 14px;
  color: #333;
  border-right: 1px solid #e0e0e0;
}

.gantt-month:last-child {
  border-right: none;
}

.gantt-chart-area {
  flex: 1;
  overflow: auto;
  position: relative;
  background: #fff;
}

.gantt-grid {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-image: linear-gradient(to right, #f0f0f0 1px, transparent 1px);
  background-size: 20px 100%;
}

.gantt-timeline {
  display: flex;
  height: 40px;
  border-bottom: 1px solid #e0e0e0;
  background: #f8f9fa;
}

.gantt-timeline-item {
  flex: 1;
  text-align: center;
  font-size: 12px;
  color: #666;
  border-right: 1px solid #e0e0e0;
  display: flex;
  align-items: center;
  justify-content: center;
}

.gantt-timeline-item:last-child {
  border-right: none;
}

.gantt-chart-content {
  position: relative;
  min-height: 100%;
}

.gantt-task-bar {
  position: absolute;
  height: 24px;
  border-radius: 3px;
  display: flex;
  align-items: center;
  padding: 0 8px;
  font-size: 12px;
  color: white;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
}

.gantt-task-bar:hover {
  opacity: 0.9;
  transform: translateY(-1px);
}

.bar-open {
  background: #ffc107;
}

.bar-on-progress {
  background: #007bff;
}

.bar-done {
  background: #28a745;
}

.bar-cancel {
  background: #dc3545;
}

.bar-need-requirement {
  background: #6f42c1;
}

.gantt-footer {
  padding: 10px 20px;
  border-top: 1px solid #e0e0e0;
  background: #f5f7fa;
  display: flex;
  justify-content: space-between;
  font-size: 13px;
  color: #666;
}

/* Responsive design */
@media (max-width: 768px) {
  .gantt-body {
    flex-direction: column;
  }
  
  .gantt-sidebar {
    width: 100%;
    height: 300px;
    border-right: none;
    border-bottom: 1px solid #e0e0e0;
  }
}
</style>

<div class="dashboard-main-body">
  <div class="d-flex align-items-center justify-content-between gap-3 mb-24">
    <h6 class="fw-semibold mb-0">Activity Gantt</h6>
    <div class="d-flex gap-2">
      <a href="activity.php" class="btn btn-secondary">List View</a>
      <a href="activity_kanban.php" class="btn btn-secondary">Kanban View</a>
      <a href="activity_gantt.php" class="btn btn-primary">Gantt Chart</a>
    </div>
  </div>

  <div class="gantt-container">
    <div class="gantt-header">
      <h2>Project Timeline</h2>
      <div class="gantt-toolbar">
        <button id="zoomIn">Zoom In</button>
        <button id="zoomOut">Zoom Out</button>
        <button id="today">Today</button>
        <button id="addTask">Add Task</button>
      </div>
    </div>
    
    <div class="gantt-body">
      <div class="gantt-sidebar">
        <ul class="gantt-task-list" id="taskList">
          <?php foreach ($tasks as $task): ?>
          <li class="gantt-task-item" data-id="<?= htmlspecialchars($task['id']) ?>">
            <div class="gantt-task-header">
              <span class="gantt-task-no">#<?= htmlspecialchars($task['no']) ?></span>
              <span class="gantt-task-status status-<?= strtolower(str_replace(' ', '-', $task['status'])) ?>">
                <?= htmlspecialchars($task['status']) ?>
              </span>
            </div>
            <h4 class="gantt-task-name"><?= htmlspecialchars($task['name']) ?></h4>
            <div class="gantt-task-meta">
              <span><?= htmlspecialchars($task['application']) ?></span>
              <span><?= htmlspecialchars($task['type']) ?></span>
              <span><?= htmlspecialchars($task['priority']) ?></span>
            </div>
          </li>
          <?php endforeach; ?>
        </ul>
      </div>
      
      <div class="gantt-main">
        <div class="gantt-chart-header">
          <div class="gantt-time-header" id="timeHeader">
            <!-- Months will be populated by JavaScript -->
          </div>
        </div>
        
        <div class="gantt-timeline" id="timeline">
          <!-- Days will be populated by JavaScript -->
        </div>
        
        <div class="gantt-chart-area">
          <div class="gantt-grid" id="grid"></div>
          <div class="gantt-chart-content" id="chartContent">
            <!-- Task bars will be populated by JavaScript -->
          </div>
        </div>
      </div>
    </div>
    
    <div class="gantt-footer">
      <div>Total Tasks: <?= count($tasks) ?></div>
      <div>Powered by Tom's Planner Clone</div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Convert PHP tasks to JavaScript
  const tasks = <?= json_encode($tasks) ?>;
  
  // Get DOM elements
  const timeHeader = document.getElementById('timeHeader');
  const timeline = document.getElementById('timeline');
  const chartContent = document.getElementById('chartContent');
  const taskList = document.getElementById('taskList');
  
  // Set up date range (3 months by default)
  const startDate = new Date();
  startDate.setDate(1); // First day of current month
  startDate.setMonth(startDate.getMonth() - 1); // Start from previous month
  
  const endDate = new Date();
  endDate.setMonth(endDate.getMonth() + 2); // End 2 months from now
  endDate.setDate(1);
  endDate.setMonth(endDate.getMonth() + 1);
  endDate.setDate(0); // Last day of that month
  
  // Current zoom level (days per pixel)
  let zoomLevel = 2;
  
  // Render timeline
  function renderTimeline() {
    // Clear existing content
    timeHeader.innerHTML = '';
    timeline.innerHTML = '';
    chartContent.innerHTML = '';
    
    // Calculate months to display
    const months = [];
    const current = new Date(startDate);
    
    while (current <= endDate) {
      months.push(new Date(current));
      current.setMonth(current.getMonth() + 1);
    }
    
    // Render month headers
    months.forEach(month => {
      const monthElement = document.createElement('div');
      monthElement.className = 'gantt-month';
      monthElement.textContent = month.toLocaleString('default', { month: 'long', year: 'numeric' });
      timeHeader.appendChild(monthElement);
    });
    
    // Render days
    const days = [];
    const dayCursor = new Date(startDate);
    
    while (dayCursor <= endDate) {
      days.push(new Date(dayCursor));
      dayCursor.setDate(dayCursor.getDate() + 1);
    }
    
    // Render timeline days
    days.forEach(day => {
      const dayElement = document.createElement('div');
      dayElement.className = 'gantt-timeline-item';
      dayElement.textContent = day.getDate();
      timeline.appendChild(dayElement);
    });
    
    // Render task bars
    tasks.forEach((task, index) => {
      const start = new Date(task.start);
      const end = new Date(task.end);
      
      // Calculate position and width
      const totalDays = Math.ceil((endDate - startDate) / (1000 * 60 * 60 * 24));
      const dayWidth = 100 / totalDays;
      
      const startOffset = Math.ceil((start - startDate) / (1000 * 60 * 60 * 24));
      const duration = Math.ceil((end - start) / (1000 * 60 * 60 * 24)) + 1;
      
      const left = startOffset * dayWidth;
      const width = duration * dayWidth;
      
      // Create task bar
      const bar = document.createElement('div');
      bar.className = `gantt-task-bar bar-${task.status.toLowerCase().replace(' ', '-')}`;
      bar.style.left = left + '%';
      bar.style.width = width + '%';
      bar.style.top = (index * 116 + 82) + 'px';
      bar.textContent = task.name;
      bar.dataset.id = task.id;
      
      // Add click event
      bar.addEventListener('click', () => {
        selectTask(task.id);
      });
      
      chartContent.appendChild(bar);
    });
  }
  
  // Select a task
  function selectTask(taskId) {
    // Remove active class from all tasks
    document.querySelectorAll('.gantt-task-item').forEach(item => {
      item.classList.remove('active');
    });
    
    // Add active class to selected task
    const selectedItem = document.querySelector(`.gantt-task-item[data-id="${taskId}"]`);
    if (selectedItem) {
      selectedItem.classList.add('active');
    }
    
    // Scroll to task bar
    const taskBar = document.querySelector(`.gantt-task-bar[data-id="${taskId}"]`);
    if (taskBar) {
      taskBar.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
  }
  
  // Zoom functions
  document.getElementById('zoomIn').addEventListener('click', () => {
    zoomLevel = Math.max(0.5, zoomLevel / 1.5);
    renderTimeline();
  });
  
  document.getElementById('zoomOut').addEventListener('click', () => {
    zoomLevel = Math.min(10, zoomLevel * 1.5);
    renderTimeline();
  });
  
  document.getElementById('today').addEventListener('click', () => {
    // Scroll to today
    const today = new Date();
    // Implementation would depend on how the timeline is rendered
  });
  
  document.getElementById('addTask').addEventListener('click', () => {
    alert('Add task functionality would be implemented here');
  });
  
  // Task item click handler
  taskList.addEventListener('click', (e) => {
    const taskItem = e.target.closest('.gantt-task-item');
    if (taskItem) {
      const taskId = taskItem.dataset.id;
      selectTask(taskId);
    }
  });
  
  // Initial render
  renderTimeline();
});
</script>

<?php include './partials/layouts/layoutBottom.php'; ?>