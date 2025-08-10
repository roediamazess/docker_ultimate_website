<?php
require_once 'db.php';
header('Content-Type: application/json');

// (Opsional) Tambahkan autentikasi token di sini
// if ($_GET['token'] !== 'your_api_token') { http_response_code(401); echo json_encode(['error'=>'Unauthorized']); exit; }

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error'=>'Method Not Allowed']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
if (!$data) {
    http_response_code(400);
    echo json_encode(['error'=>'Invalid JSON']);
    exit;
}

$fields = ['project_id','no','information_date','user_position','department','application','type','description','action_solution','due_date','status','cnc_number','priority','customer','project'];
$values = [];
foreach ($fields as $f) {
    $values[] = $data[$f] ?? null;
}

$data['created_by'] = $_SESSION['user_id'] ?? null;
$stmt = $pdo->prepare('INSERT INTO activities (project_id, no, information_date, user_position, department, application, type, description, action_solution, due_date, status, cnc_number, priority, customer, project, created_by) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)');
$values_with_user = array_merge($values, [$data['created_by']]);
$stmt->execute($values_with_user);

http_response_code(201);
echo json_encode(['success'=>true, 'id'=>$pdo->lastInsertId()]);
