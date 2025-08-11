<?php
session_start();
require_once 'db.php';
require_once 'access_control.php';
require_login();

header('Content-Type: application/json');

try {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if ($id <= 0) { throw new Exception('Invalid ID'); }

    $stmt = $pdo->prepare("SELECT id, no, information_date, priority, user_position, department, application, type, description, action_solution, customer, project, due_date, cnc_number, status FROM activities WHERE id = ?");
    $stmt->execute([$id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) { throw new Exception('Data tidak ditemukan'); }

    echo json_encode(['success' => true, 'activity' => $row]);
} catch (Throwable $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>


