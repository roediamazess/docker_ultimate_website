<?php
require_once 'db.php';
header('Content-Type: application/json');

// (Opsional) Tambahkan autentikasi token di sini
// if ($_GET['token'] !== 'your_api_token') { http_response_code(401); echo json_encode(['error'=>'Unauthorized']); exit; }

$stmt = $pdo->query('SELECT id, display_name, full_name, email, tier, role, start_work, created_at FROM users ORDER BY id DESC');
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($users);
