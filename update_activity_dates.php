<?php
session_start();
require_once 'db.php';
require_once 'access_control.php';
require_login();

header('Content-Type: application/json');

try {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) { throw new Exception('Invalid payload'); }
    $id = isset($input['id']) ? (int)$input['id'] : 0;
    if ($id <= 0) { throw new Exception('Invalid ID'); }
    $start = isset($input['start']) ? trim($input['start']) : null;
    $end = isset($input['end']) ? trim($input['end']) : null;
    if ($start && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $start)) { $start = date('Y-m-d', strtotime($start)); }
    if ($end && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $end)) { $end = date('Y-m-d', strtotime($end)); }

    $stmt = $pdo->prepare("UPDATE activities SET information_date = COALESCE(?, information_date), due_date = COALESCE(?, due_date) WHERE id = ?");
    $stmt->execute([$start, $end, $id]);

    echo json_encode(['success' => true]);
} catch (Throwable $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>


