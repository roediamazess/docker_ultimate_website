<?php
session_start();
require_once 'db.php';
require_once 'user_utils.php';

// Proteksi akses: hanya user login yang bisa mengakses dashboard
require_login();

// Statistik utama
$user_count = $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
$customer_count = $pdo->query('SELECT COUNT(*) FROM customers')->fetchColumn();
$project_count = $pdo->query('SELECT COUNT(*) FROM projects')->fetchColumn();
$activity_count = $pdo->query('SELECT COUNT(*) FROM activities')->fetchColumn();

// Data untuk grafik project per status
$project_status = $pdo->query('SELECT status, COUNT(*) as total FROM projects GROUP BY status')->fetchAll(PDO::FETCH_ASSOC);

// Data untuk grafik activity per status
$activity_status = $pdo->query('SELECT status, COUNT(*) as total FROM activities GROUP BY status')->fetchAll(PDO::FETCH_ASSOC);

// 5 activity terakhir
$last_activities = $pdo->query('SELECT * FROM activities ORDER BY id DESC LIMIT 5')->fetchAll(PDO::FETCH_ASSOC);
// 5 project terakhir
$last_projects = $pdo->query('SELECT * FROM projects ORDER BY id DESC LIMIT 5')->fetchAll(PDO::FETCH_ASSOC);
// 10 log terbaru
$recent_logs = $pdo->query('SELECT * FROM logs ORDER BY id DESC LIMIT 10')->fetchAll(PDO::FETCH_ASSOC);

// Grafik tren user/project/activity per bulan (12 bulan terakhir)
$trend_months = [];
$trend_user = [];
$trend_project = [];
$trend_activity = [];
for ($i=11; $i>=0; $i--) {
    $month = date('Y-m', strtotime("-$i months"));
    $trend_months[] = date('M Y', strtotime($month.'-01'));
    // User
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE to_char(created_at, 'YYYY-MM') = ?");
    $stmt->execute([$month]);
    $trend_user[] = $stmt->fetchColumn();
    // Project
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM projects WHERE to_char(created_at, 'YYYY-MM') = ?");
    $stmt->execute([$month]);
    $trend_project[] = $stmt->fetchColumn();
    // Activity
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM activities WHERE to_char(created_at, 'YYYY-MM') = ?");
    $stmt->execute([$month]);
    $trend_activity[] = $stmt->fetchColumn();
}

// Statistik performa
$project_overdue = $pdo->query("SELECT COUNT(*) FROM projects WHERE end_date < CURRENT_DATE AND status != 'Done'")->fetchColumn();
$activity_overdue = $pdo->query("SELECT COUNT(*) FROM activities WHERE due_date < CURRENT_DATE AND status != 'Done'")->fetchColumn();
$project_done_this_month = $pdo->query("SELECT COUNT(*) FROM projects WHERE status = 'Done' AND to_char(end_date, 'YYYY-MM') = to_char(CURRENT_DATE, 'YYYY-MM')")->fetchColumn();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .stat-box { display:inline-block; margin:16px; padding:16px; border:1px solid #ccc; border-radius:8px; min-width:120px; text-align:center; }
        .drill-link { color:blue; text-decoration:underline; cursor:pointer; }
    </style>
</head>
<body>
<?php include './partials/layouts/layoutHorizontal.php'; ?>
<h2>Dashboard</h2>
<div>
    <div class="stat-box"><span class="drill-link" onclick="location.href='user_crud.php'">User<br><b><?= $user_count ?></b></span></div>
    <div class="stat-box"><span class="drill-link" onclick="location.href='customer_crud.php'">Customer<br><b><?= $customer_count ?></b></span></div>
    <div class="stat-box"><span class="drill-link" onclick="location.href='project_crud.php'">Project<br><b><?= $project_count ?></b></span></div>
    <div class="stat-box"><span class="drill-link" onclick="location.href='activity_crud.php'">Activity<br><b><?= $activity_count ?></b></span></div>
</div>

<style>
@media (max-width: 900px) {
    .dashboard-flex { flex-direction: column !important; gap: 24px !important; }
    .dashboard-flex > div { min-width:0 !important; max-width:100vw !important; }
}
</style>
<div class="dashboard-flex" style="display:flex; gap:48px; flex-wrap:wrap; align-items:flex-start;">
    <div style="overflow-x:auto; max-width:100vw;">
        <h3>Project per Status</h3>
        <canvas id="projectStatusChart" width="300" height="200"></canvas>
    </div>
    <div style="overflow-x:auto; max-width:100vw;">
        <h3>Activity per Status</h3>
        <canvas id="activityStatusChart" width="300" height="200"></canvas>
    </div>
    <div style="min-width:320px;max-width:400px;overflow-x:auto;">
        <h3>Notifikasi Aktivitas Terbaru</h3>
        <ul style="list-style:none;padding:0;max-height:260px;overflow-y:auto;">
        <?php foreach ($recent_logs as $log): ?>
            <li style="margin-bottom:8px;border-bottom:1px solid #eee;padding-bottom:4px;">
                <b><?= htmlspecialchars($log['action']) ?></b> - <?= htmlspecialchars($log['user_email']) ?><br>
                <span style="font-size:90%;color:#555;"> <?= htmlspecialchars($log['description']) ?> </span><br>
                <span style="font-size:80%;color:#888;"> <?= htmlspecialchars($log['created_at']) ?> </span>
            </li>
        <?php endforeach; ?>
        </ul>
        <a href="log_view.php" style="font-size:90%;color:#1976d2;">Lihat semua log &raquo;</a>
    </div>
</div>

<!-- Statistik Performa -->
<div style="margin:32px 0 24px 0;display:flex;gap:32px;">
    <div style="background:#f8f8f8;padding:16px 24px;border-radius:8px;">
        <b>Project Overdue:</b> <?= $project_overdue ?><br>
        <b>Project Selesai Bulan Ini:</b> <?= $project_done_this_month ?><br>
    </div>
    <div style="background:#f8f8f8;padding:16px 24px;border-radius:8px;">
        <b>Activity Overdue:</b> <?= $activity_overdue ?><br>
    </div>
</div>

<!-- Grafik Tren -->
<div style="margin-bottom:32px;overflow-x:auto;max-width:100vw;">
    <h3>Tren User, Project, Activity per Bulan (12 Bulan Terakhir)</h3>
    <canvas id="trendChart" width="900" height="320" style="min-width:600px;"></canvas>
</div>

<div style="display:flex; gap:48px; margin-top:32px; flex-wrap:wrap;" class="dashboard-flex">
    <div style="overflow-x:auto; max-width:100vw;">
        <h3>5 Activity Terakhir</h3>
        <table border="1" cellpadding="4" style="min-width:600px;">
            <tr><th>ID</th><th>Project ID</th><th>No</th><th>Information Date</th><th>Status</th><th>Description</th></tr>
            <?php foreach ($last_activities as $a): ?>
            <tr>
                <td><?= $a['id'] ?></td>
                <td><?= htmlspecialchars($a['project_id']) ?></td>
                <td><?= htmlspecialchars($a['no']) ?></td>
                <td><?= htmlspecialchars($a['information_date']) ?></td>
                <td><?= htmlspecialchars($a['status']) ?></td>
                <td><?= htmlspecialchars($a['description']) ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
    <div style="overflow-x:auto; max-width:100vw;">
        <h3>5 Project Terakhir</h3>
        <table border="1" cellpadding="4" style="min-width:600px;">
            <tr><th>ID</th><th>Project ID</th><th>Hotel Name</th><th>Start</th><th>Status</th><th>Type</th></tr>
            <?php foreach ($last_projects as $p): ?>
            <tr>
                <td><?= $p['id'] ?></td>
                <td><?= htmlspecialchars($p['project_id']) ?></td>
                <td><?= htmlspecialchars($p['hotel_name']) ?></td>
                <td><?= htmlspecialchars($p['start_date']) ?></td>
                <td><?= htmlspecialchars($p['status']) ?></td>
                <td><?= htmlspecialchars($p['type']) ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
</div>

<script>
// Grafik Tren User/Project/Activity
const trendMonths = <?= json_encode($trend_months) ?>;
const trendUser = <?= json_encode($trend_user) ?>;
const trendProject = <?= json_encode($trend_project) ?>;
const trendActivity = <?= json_encode($trend_activity) ?>;
new Chart(document.getElementById('trendChart'), {
    type: 'line',
    data: {
        labels: trendMonths,
        datasets: [
            {label:'User', data:trendUser, borderColor:'#1976d2', backgroundColor:'rgba(25,118,210,0.1)', fill:true},
            {label:'Project', data:trendProject, borderColor:'#43a047', backgroundColor:'rgba(67,160,71,0.1)', fill:true},
            {label:'Activity', data:trendActivity, borderColor:'#fbc02d', backgroundColor:'rgba(251,192,45,0.1)', fill:true}
        ]
    },
    options: {responsive:true, plugins:{legend:{position:'top'}}, scales:{y:{beginAtZero:true}}}
});

// Project per Status
const projectStatusData = {
    labels: [<?php foreach ($project_status as $ps) { echo "'".addslashes($ps['status'])."',"; } ?>],
    datasets: [{
        label: 'Project',
        data: [<?php foreach ($project_status as $ps) { echo $ps['total'].","; } ?>],
        backgroundColor: 'rgba(54, 162, 235, 0.5)'
    }]
};
new Chart(document.getElementById('projectStatusChart'), {
    type: 'bar',
    data: projectStatusData,
    options: {responsive:true, plugins:{legend:{display:false}}}
});
// Activity per Status
const activityStatusData = {
    labels: [<?php foreach ($activity_status as $as) { echo "'".addslashes($as['status'])."',"; } ?>],
    datasets: [{
        label: 'Activity',
        data: [<?php foreach ($activity_status as $as) { echo $as['total'].","; } ?>],
        backgroundColor: 'rgba(255, 99, 132, 0.5)'
    }]
};
new Chart(document.getElementById('activityStatusChart'), {
    type: 'pie',
    data: activityStatusData,
    options: {responsive:true, plugins:{legend:{position:'bottom'}}}
});
</script>
<?php include './partials/layouts/layoutBottom.php'; ?>
</body>
</html>
