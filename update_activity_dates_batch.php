<?php
session_start();
require_once 'db.php';
require_once 'access_control.php';
require_login();

header('Content-Type: application/json');

try {
    $payload = json_decode(file_get_contents('php://input'), true);
    if (!is_array($payload)) {
        throw new Exception('Invalid payload');
    }

    $changes = $payload['changes'] ?? null;
    if (!$changes || !is_array($changes)) {
        throw new Exception('`changes` array is required');
    }

    // Prepare transaction
    $pdo->beginTransaction();
    $stmt = $pdo->prepare('UPDATE activities 
        SET information_date = COALESCE(?, information_date),
            due_date = COALESCE(?, due_date)
        WHERE id = ?');

    $updated = 0;
    foreach ($changes as $c) {
        $id = isset($c['id']) ? (int)$c['id'] : 0;
        if ($id <= 0) { continue; }
        $start = isset($c['start']) ? trim((string)$c['start']) : null;
        $end   = isset($c['end'])   ? trim((string)$c['end'])   : null;

        if ($start !== null && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $start)) {
            $start = date('Y-m-d', strtotime($start));
        }
        if ($end !== null && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $end)) {
            $end = date('Y-m-d', strtotime($end));
        }

        // Enforce rule: due_date >= information_date
        if ($start && $end) {
            if (strtotime($end) < strtotime($start)) {
                $end = $start;
            }
        }

        $stmt->execute([$start, $end, $id]);
        $updated += ($stmt->rowCount() > 0) ? 1 : 0;
    }

    $pdo->commit();
    echo json_encode(['success' => true, 'updated' => $updated, 'action' => 'update']);
} catch (Throwable $e) {
    if ($pdo && $pdo->inTransaction()) { $pdo->rollBack(); }
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>


