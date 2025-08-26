<?php
session_start();
require_once 'db.php';
require_once 'access_control.php';
require_once 'user_utils.php';

// Cek login
if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Handle JSON input
$input = json_decode(file_get_contents('php://input'), true);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($input['action'])) {
    switch ($input['action']) {
        case 'quick_update':
            handleQuickUpdate($input);
            break;
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }
} else {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}

function handleQuickUpdate($data) {
    global $pdo;
    
    try {
        // Validasi input
        if (!isset($data['id']) || !isset($data['status']) || !isset($data['priority'])) {
            throw new Exception('Missing required fields');
        }
        
        $id = (int)$data['id'];
        $status = $data['status'];
        $priority = $data['priority'];
        $information_date = $data['information_date'] ?? null;
        $due_date = $data['due_date'] ?? null;
        $type = $data['type'] ?? null;
        $department = $data['department'] ?? null;
        $description = $data['description'] ?? null;
        $action_solution = $data['action_solution'] ?? null;
        
        // Update activity
        $current_user = get_current_user_id();
        if (!$current_user) {
            $current_user = 'admin'; // Fallback jika user ID tidak tersedia
        }
        
        $sql = "UPDATE activities SET 
                status = ?, 
                priority = ?, 
                edited_by = ?, 
                edited_at = ?";
        $params = [$status, $priority, $current_user, date('Y-m-d H:i:s')];
        
        // Add additional fields if provided
        if ($information_date) {
            $sql .= ", information_date = ?";
            $params[] = $information_date;
        }
        if ($due_date) {
            $sql .= ", due_date = ?";
            $params[] = $due_date;
        }
        if ($type) {
            $sql .= ", type = ?";
            $params[] = $type;
        }
        if ($department) {
            $sql .= ", department = ?";
            $params[] = $department;
        }
        if ($description) {
            $sql .= ", description = ?";
            $params[] = $description;
        }
        if ($action_solution) {
            $sql .= ", action_solution = ?";
            $params[] = $action_solution;
        }
        
        $sql .= " WHERE id = ?";
        $params[] = $id;
        
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute($params);
        
        if ($result) {
            // Log activity (with error handling)
            try {
                log_user_activity('quick_update_activity', "Activity ID: $id - Status: $status, Priority: $priority");
            } catch (Exception $logError) {
                // Ignore logging errors
                error_log("Logging error: " . $logError->getMessage());
            }
            
            echo json_encode([
                'success' => true, 
                'message' => 'Activity updated successfully'
            ]);
        } else {
            throw new Exception('Failed to update activity');
        }
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false, 
            'message' => 'Error updating activity: ' . $e->getMessage()
        ]);
    }
}
?>
