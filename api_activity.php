<?php
require_once 'db.php';
header('Content-Type: application/json');

// (Opsional) Tambahkan autentikasi token di sini
// if ($_GET['token'] !== 'your_api_token') { http_response_code(401); echo json_encode(['error'=>'Unauthorized']); exit; }

$stmt = $pdo->query('SELECT a.id, a.project_id, a.no, a.information_date, a.user_position, a.department, a.application, a.type, a.description, a.action_solution, a.due_date, a.status, a.cnc_number, a.priority, a.customer, a.project, a.created_by, u.display_name AS created_by_name, a.created_at FROM activities a LEFT JOIN users u ON u.id = a.created_by ORDER BY a.id DESC');
$activities = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($activities);
