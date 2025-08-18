<?php
session_start();
require_once 'db.php';
require_once 'access_control.php';
require_login();

header('Content-Type: application/json; charset=utf-8');

try {
    $body = json_decode(file_get_contents('php://input'), true);
    $days = [];
    if (is_array($body) && isset($body['days']) && is_array($body['days'])) {
        foreach ($body['days'] as $d) {
            $d = trim((string)$d);
            if ($d !== '') $days[] = $d;
        }
    }
    $days = array_values(array_unique($days));
    if (count($days) === 0) {
        echo json_encode([]);
        exit;
    }

    $placeholders = implode(',', array_fill(0, count($days), '?'));
    $stmt = $pdo->prepare("SELECT COALESCE(user_id,'') AS user_id, pic_name, day, value, ontime, late, note FROM jobsheet WHERE day IN ($placeholders)");
    $stmt->execute($days);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (function_exists('ob_get_length') && ob_get_length()) { ob_clean(); }
    echo json_encode($rows, JSON_UNESCAPED_UNICODE);
    exit;
} catch (Throwable $e) {
    http_response_code(200);
    if (function_exists('ob_get_length') && ob_get_length()) { ob_clean(); }
    echo json_encode([]);
}
?>


