<?php
session_start();
require_once 'db.php';
require_once 'access_control.php';
require_login();

header('Content-Type: application/json; charset=utf-8');

try {
    $body = json_decode(file_get_contents('php://input'), true) ?: [];
    $start = isset($body['start']) ? trim((string)$body['start']) : '';
    $end   = isset($body['end']) ? trim((string)$body['end']) : '';
    $pics  = isset($body['pics']) && is_array($body['pics']) ? array_values(array_filter(array_map('strval', $body['pics']))) : [];
    if ($start === '' || $end === '') { echo json_encode([]); exit; }

    // Untuk sementara, ambil semua data tanpa filter tanggal
    $sql = "SELECT COALESCE(user_id,'') AS user_id, pic_name, day, value, 
            CASE WHEN ontime IS NULL THEN false ELSE ontime END as ontime,
            CASE WHEN late IS NULL THEN false ELSE late END as late,
            note FROM jobsheet";
    if (count($pics) > 0) {
        $in = implode(',', array_fill(0, count($pics), '?'));
        $sql .= " WHERE pic_name IN ($in)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($pics);
    } else {
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
    }
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (function_exists('ob_get_length') && ob_get_length()) { ob_clean(); }
    echo json_encode($rows, JSON_UNESCAPED_UNICODE);
    exit;
} catch (Throwable $e) {
    http_response_code(200);
    if (function_exists('ob_get_length') && ob_get_length()) { ob_clean(); }
    echo json_encode(['error' => $e->getMessage()]);
}
?>


