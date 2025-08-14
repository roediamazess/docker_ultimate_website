<?php
session_start();
require_once 'db.php';
require_once 'access_control.php';
require_login();

header('Content-Type: application/json; charset=utf-8');

try {
    $q = isset($_GET['q']) ? trim((string)$_GET['q']) : '';
    $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    if ($q !== '') {
        if ($driver === 'mysql') {
            $stmt = $pdo->prepare("SELECT user_id, full_name FROM users WHERE user_id LIKE ? OR full_name LIKE ? ORDER BY full_name LIMIT 50");
        } else {
            $stmt = $pdo->prepare("SELECT user_id, full_name FROM users WHERE user_id ILIKE ? OR full_name ILIKE ? ORDER BY full_name LIMIT 50");
        }
        $like = "%$q%";
        $stmt->execute([$like, $like]);
    } else {
        $stmt = $pdo->query("SELECT user_id, full_name FROM users ORDER BY full_name LIMIT 50");
    }
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (function_exists('ob_get_length') && ob_get_length()) { ob_clean(); }
    echo json_encode($rows, JSON_UNESCAPED_UNICODE);
    exit;
} catch (Throwable $e) {
    http_response_code(200);
    if (function_exists('ob_get_length') && ob_get_length()) { ob_clean(); }
    echo json_encode([]);
    exit;
}


