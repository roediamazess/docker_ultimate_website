<?php
session_start();
require_once 'db.php';
require_once 'access_control.php';
require_login();

header('Content-Type: application/json; charset=utf-8');

try {
    $stmt = $pdo->query('SELECT user_id, full_name FROM users ORDER BY full_name');
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (function_exists('ob_get_length') && ob_get_length()) { ob_clean(); }
    echo json_encode($rows, JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(200);
    if (function_exists('ob_get_length') && ob_get_length()) { ob_clean(); }
    echo json_encode([]);
}
?>




