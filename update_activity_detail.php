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

    // Helper to normalize date to YYYY-MM-DD or NULL
    $normalizeDate = function ($value) {
        if ($value === null) { return null; }
        $value = trim((string)$value);
        if ($value === '') { return null; }
        // dd/mm/yyyy -> yyyy-mm-dd
        if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $value, $m)) {
            return $m[3] . '-' . $m[2] . '-' . $m[1];
        }
        // yyyy-mm-dd (already normalized)
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return $value;
        }
        // Try to parse any other human format
        $ts = strtotime($value);
        if ($ts !== false) {
            return date('Y-m-d', $ts);
        }
        return null; // fallback to NULL if cannot parse
    };

    // Allowed fields
    $fields = [
        'no','status','information_date','priority','user_position','department','application','type','description','action_solution','customer','project','due_date','cnc_number'
    ];
    $sets = [];
    $values = [];
    foreach ($fields as $f) {
        if (array_key_exists($f, $input)) {
            $value = $input[$f];
            if ($f === 'no') {
                $value = (int)$value;
            }
            if ($f === 'information_date' || $f === 'due_date') {
                $value = $normalizeDate($value);
            }
            $sets[] = "$f = ?";
            $values[] = $value;
        }
    }

    if (empty($sets)) { throw new Exception('No fields to update'); }

    $values[] = $id;
    $sql = "UPDATE activities SET ".implode(', ', $sets)." WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($values);

    echo json_encode(['success' => true]);
} catch (Throwable $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>


