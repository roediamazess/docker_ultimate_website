<?php
require_once 'db.php';
header('Content-Type: application/json');

// (Opsional) Tambahkan autentikasi token di sini
// if ($_GET['token'] !== 'your_api_token') { http_response_code(401); echo json_encode(['error'=>'Unauthorized']); exit; }

$stmt = $pdo->query('SELECT id, project_id, project_name, start_date, end_date, type, status, created_at FROM projects ORDER BY id DESC');
$projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($projects);
