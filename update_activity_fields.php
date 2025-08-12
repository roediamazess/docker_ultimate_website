<?php
session_start();
require_once 'db.php';
require_once 'access_control.php';
require_login();

header('Content-Type: application/json');

try {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!is_array($input)) { throw new Exception('Invalid payload'); }

    $id = isset($input['id']) ? (int)$input['id'] : 0;
    if ($id <= 0) { throw new Exception('Invalid ID'); }

    $allowedStatus   = ['Open','On Progress','Need Requirement','Done','Cancel'];
    $allowedPriority = ['Urgent','Normal','Low'];
    $allowedType     = ['Setup','Question','Issue','Report Issue','Report Request','Feature Request'];

    $status   = isset($input['status'])   ? trim((string)$input['status'])   : null;
    $priority = isset($input['priority']) ? trim((string)$input['priority']) : null;
    $type     = isset($input['type'])     ? trim((string)$input['type'])     : null;

    // Build dynamic update list with whitelist validation
    $sets = [];
    $params = [];

    if ($status !== null) {
        if (!in_array($status, $allowedStatus, true)) { throw new Exception('Invalid status'); }
        $sets[] = 'status = ?';
        $params[] = $status;
    }
    if ($priority !== null) {
        if (!in_array($priority, $allowedPriority, true)) { throw new Exception('Invalid priority'); }
        $sets[] = 'priority = ?';
        $params[] = $priority;
    }
    if ($type !== null) {
        if (!in_array($type, $allowedType, true)) { throw new Exception('Invalid type'); }
        $sets[] = 'type = ?';
        $params[] = $type;
    }

    if (empty($sets)) { throw new Exception('No changes'); }

    $params[] = $id;
    $sql = 'UPDATE activities SET '.implode(', ', $sets).' WHERE id = ?';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    echo json_encode(['success' => true, 'updated' => $stmt->rowCount()]);
} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>


