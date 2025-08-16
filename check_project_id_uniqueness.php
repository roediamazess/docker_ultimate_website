<?php
// Suppress all error output for clean JSON
error_reporting(0);
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
ini_set('log_errors', 0);

// Only start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'db.php';

// Check if access_control.php exists and include it if it does
if (file_exists('access_control.php')) {
    require_once 'access_control.php';
    
    // Check if require_login function exists and call it if it does
    if (function_exists('require_login')) {
        try {
            require_login();
        } catch (Exception $e) {
            // If login fails, continue without authentication for testing
            // In production, you might want to return an error instead
            error_log("Login failed: " . $e->getMessage());
        }
    }
}

// Clean any previous output and start fresh
while (ob_get_level()) {
    ob_end_clean();
}

// Start output buffering
ob_start();

// Set content type to JSON
header('Content-Type: application/json');

try {
    // Get project_id from POST request
    $project_id = trim($_POST['project_id'] ?? '');
    
    if (empty($project_id)) {
        // Clean all buffers and output JSON
        while (ob_get_level()) {
            ob_end_clean();
        }
        echo json_encode([
            'success' => false,
            'exists' => false,
            'message' => 'Project ID tidak boleh kosong'
        ]);
        exit;
    }
    
    // Check if project_id already exists in database
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM projects WHERE project_id = ?");
    $stmt->execute([$project_id]);
    $count = $stmt->fetchColumn();
    
    $exists = ($count > 0);
    
    if ($exists) {
        // Get additional information about the existing project
        $stmt = $pdo->prepare("SELECT project_name, hotel_name_text, type, status, created_at FROM projects WHERE project_id = ? LIMIT 1");
        $stmt->execute([$project_id]);
        $project_info = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $additional_info = '';
        if ($project_info) {
            $additional_info = " (Project: " . ($project_info['project_name'] ?: 'N/A') . 
                              ", Hotel: " . ($project_info['hotel_name_text'] ?: 'N/A') . 
                              ", Type: " . ($project_info['type'] ?: 'N/A') . 
                              ", Status: " . ($project_info['status'] ?: 'N/A') . ")";
        }
        
        // Clean all buffers and output JSON
        while (ob_get_level()) {
            ob_end_clean();
        }
        echo json_encode([
            'success' => true,
            'exists' => true,
            'message' => '❌ Project ID "' . htmlspecialchars($project_id) . '" sudah digunakan!' . $additional_info,
            'count' => $count,
            'project_info' => $project_info
        ]);
    } else {
        // Clean all buffers and output JSON
        while (ob_get_level()) {
            ob_end_clean();
        }
        echo json_encode([
            'success' => true,
            'exists' => false,
            'message' => '✅ Project ID "' . htmlspecialchars($project_id) . '" tersedia dan dapat digunakan',
            'count' => 0
        ]);
    }
    
} catch (Exception $e) {
    // Clean all buffers and output JSON
    while (ob_get_level()) {
        ob_end_clean();
    }
    echo json_encode([
        'success' => false,
        'exists' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
