<?php
require_once 'db.php';

// Set content type to JSON
header('Content-Type: application/json');

try {
    // Get project_id from POST request
    $project_id = trim($_POST['project_id'] ?? '');
    
    if (empty($project_id)) {
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
        
        echo json_encode([
            'success' => true,
            'exists' => true,
            'message' => '❌ Project ID "' . htmlspecialchars($project_id) . '" sudah digunakan!' . $additional_info,
            'count' => $count,
            'project_info' => $project_info
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'exists' => false,
            'message' => '✅ Project ID "' . htmlspecialchars($project_id) . '" tersedia dan dapat digunakan',
            'count' => 0
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'exists' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>

