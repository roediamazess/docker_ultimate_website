<?php
session_start();
require_once 'db.php';
require_once 'access_control.php';
require_login();

header('Content-Type: application/json; charset=utf-8');

try {
    $input = json_decode(file_get_contents('php://input'), true) ?: [];
    $pic = isset($input['pic']) ? trim((string)$input['pic']) : '';
    if ($pic === '') { echo json_encode(['user_id' => null]); exit; }

    $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    if ($driver === 'mysql') {
        $stmt = $pdo->prepare("SELECT user_id FROM users WHERE UPPER(full_name) LIKE UPPER(CONCAT('%', ?, '%')) OR UPPER(user_id) = UPPER(?) ORDER BY CHAR_LENGTH(full_name) ASC LIMIT 1");
        $stmt->execute([$pic, $pic]);
    } else { // pgsql
        $stmt = $pdo->prepare("SELECT user_id FROM users WHERE UPPER(full_name) LIKE UPPER('%' || :q || '%') OR UPPER(user_id) = UPPER(:q) ORDER BY LENGTH(full_name) ASC LIMIT 1");
        $stmt->execute([':q' => $pic]);
    }
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo json_encode(['user_id' => $row['user_id'] ?? null]);
} catch (Throwable $e) {
    http_response_code(200);
    echo json_encode(['user_id' => null]);
}
?>


