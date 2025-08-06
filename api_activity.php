<?php
require_once 'db.php';
header('Content-Type: application/json');

// (Opsional) Tambahkan autentikasi token di sini
// if ($_GET['token'] !== 'your_api_token') { http_response_code(401); echo json_encode(['error'=>'Unauthorized']); exit; }

$stmt = $pdo->query('SELECT id, project_id, no, information_date, user_position, department, application, type, description, action_solution, due_date, status, cnc_number, created_at FROM activities ORDER BY id DESC');
$activities = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($activities);
