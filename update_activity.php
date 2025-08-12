<?php
session_start();
require_once 'db.php';
require_once 'access_control.php';
require_login();

header('Content-Type: application/json');

try {
    // Get form data
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $no = isset($_POST['no']) ? (int)$_POST['no'] : 0;
    $status = trim($_POST['status'] ?? '');
    $information_date = trim($_POST['information_date'] ?? '');
    $priority = trim($_POST['priority'] ?? '');
    $user_position = trim($_POST['user_position'] ?? '');
    $department = trim($_POST['department'] ?? '');
    $application = trim($_POST['application'] ?? '');
    $type = trim($_POST['type'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $action_solution = trim($_POST['action_solution'] ?? '');
    $customer = trim($_POST['customer'] ?? '');
    $project = trim($_POST['project'] ?? '');
    $due_date = trim($_POST['due_date'] ?? '');
    $cnc_number = trim($_POST['cnc_number'] ?? '');
    
    // Validation
    if ($id <= 0) {
        throw new Exception('Invalid ID');
    }
    
    if (empty($status) || empty($information_date) || empty($priority) || empty($application)) {
        throw new Exception('Required fields cannot be empty');
    }
    
    // Validate status
    $allowed_statuses = ['Open', 'On Progress', 'Need Requirement', 'Done', 'Cancel'];
    if (!in_array($status, $allowed_statuses, true)) {
        throw new Exception('Invalid status value');
    }
    
    // Validate priority
    $allowed_priorities = ['Low', 'Normal', 'Urgent'];
    if (!in_array($priority, $allowed_priorities, true)) {
        throw new Exception('Invalid priority value');
    }
    
    // Handle empty date fields - convert to NULL for database
    $due_date_db = !empty($due_date) ? $due_date : null;
    
    // Update the activity
    $stmt = $pdo->prepare("
        UPDATE activities 
        SET status = ?, information_date = ?, priority = ?, user_position = ?, 
            department = ?, application = ?, type = ?, description = ?, 
            action_solution = ?, customer = ?, project = ?, due_date = ?, cnc_number = ?
        WHERE id = ?
    ");
    
    $stmt->execute([
        $status, $information_date, $priority, $user_position,
        $department, $application, $type, $description, 
        $action_solution, $customer, $project, $due_date_db, $cnc_number, $id
    ]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode([
            'success' => true, 
            'message' => 'Activity updated successfully'
        ]);
    } else {
        echo json_encode([
            'success' => false, 
            'message' => 'No changes made or activity not found'
        ]);
    }
    
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage()
    ]);
}
?>

