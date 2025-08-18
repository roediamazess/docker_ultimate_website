<?php
session_start();
require_once 'db.php';
require_once 'access_control.php';
require_login();

header('Content-Type: application/json; charset=utf-8');

try {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!is_array($input)) $input = [];

    $pdo->beginTransaction();
    $stmtByUser = $pdo->prepare('DELETE FROM jobsheet WHERE user_id = :user_id AND day = :day');
    $stmtByPic  = $pdo->prepare('DELETE FROM jobsheet WHERE pic_name = :pic_name AND day = :day');
    foreach ($input as $row) {
        $userId = isset($row['user_id']) ? trim((string)$row['user_id']) : '';
        $pic    = isset($row['pic_name']) ? trim((string)$row['pic_name']) : (isset($row['pic']) ? trim((string)$row['pic']) : '');
        $day    = isset($row['day']) ? trim((string)$row['day']) : '';
        if ($day === '') continue;
        if ($userId !== '') $stmtByUser->execute([':user_id' => $userId, ':day' => $day]);
        else if ($pic !== '') $stmtByPic->execute([':pic_name' => $pic, ':day' => $day]);
    }
    $pdo->commit();
    echo json_encode(['ok' => true]);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['ok' => false]);
}
?>




