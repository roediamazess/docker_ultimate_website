<?php
require_once 'db.php';
require_once 'send_email.php';
require_once 'send_n8n_webhook.php';

$admin_email = 'your_admin_email@gmail.com'; // Ganti dengan email admin

// Cek activity overdue
$overdue_activities = $pdo->query("SELECT * FROM activities WHERE due_date < CURRENT_DATE AND status != 'Done'")->fetchAll(PDO::FETCH_ASSOC);
if ($overdue_activities) {
    foreach ($overdue_activities as $oa) {
        $subject = 'Activity Overdue (Scheduler)';
        $body = '<b>Activity overdue:</b><br>Project ID: ' . htmlspecialchars($oa['project_id']) . '<br>No: ' . htmlspecialchars($oa['no']) . '<br>Due Date: ' . htmlspecialchars($oa['due_date']) . '<br>Status: ' . htmlspecialchars($oa['status']) . '<br>Description: ' . htmlspecialchars($oa['description']);
        send_email($admin_email, $subject, $body);
        send_n8n_webhook('activity_overdue', $oa);
    }
}

// (Opsional) Cek project overdue
$overdue_projects = $pdo->query("SELECT * FROM projects WHERE end_date < CURRENT_DATE AND status != 'Done'")->fetchAll(PDO::FETCH_ASSOC);
if ($overdue_projects) {
    foreach ($overdue_projects as $op) {
        $subject = 'Project Overdue (Scheduler)';
        $body = '<b>Project overdue:</b><br>Project ID: ' . htmlspecialchars($op['project_id']) . '<br>Nama: ' . htmlspecialchars($op['project_name']) . '<br>End Date: ' . htmlspecialchars($op['end_date']) . '<br>Status: ' . htmlspecialchars($op['status']);
        send_email($admin_email, $subject, $body);
        send_n8n_webhook('project_overdue', $op);
    }
}

echo "Scheduler run completed.\n";
