<?php
session_start();
require_once 'db.php';
require_once 'access_control.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

try {
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        $out = ['success' => false, 'error' => 'Not authenticated'];
        if (ob_get_length()) { ob_clean(); }
        echo json_encode($out, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
        exit;
    }

    $project_id = isset($_GET['project_id']) ? trim($_GET['project_id']) : '';
    if ($project_id === '') {
        http_response_code(400);
        $out = ['success' => false, 'error' => 'Project ID required'];
        if (ob_get_length()) { ob_clean(); }
        echo json_encode($out, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
        exit;
    }

    // Get project header
    $stmt = $pdo->prepare("SELECT * FROM projects WHERE project_id = ?");
    $stmt->execute([$project_id]);
    $project = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$project) {
        http_response_code(404);
        $out = ['success' => false, 'error' => 'Project not found'];
        if (ob_get_length()) { ob_clean(); }
        echo json_encode($out, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
        exit;
    }

    // Get project details
    $stmt = $pdo->prepare("SELECT * FROM projects_detail WHERE project_id = ? ORDER BY id ASC");
    $stmt->execute([$project_id]);
    $details = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get user names for details
    foreach ($details as &$detail) {
        if (!empty($detail['user_id'])) {
            $stmt = $pdo->prepare("SELECT full_name FROM users WHERE id = ?");
            $stmt->execute([$detail['user_id']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            $detail['user_name'] = $user['full_name'] ?? '';
        }
    }
    unset($detail);

    $response = [
        'success' => true,
        'project' => $project,
        'details' => $details
    ];

    if (ob_get_length()) { ob_clean(); }
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;

} catch (Throwable $e) {
    http_response_code(500);
    $out = ['success' => false, 'error' => $e->getMessage()];
    if (ob_get_length()) { ob_clean(); }
    echo json_encode($out, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}
