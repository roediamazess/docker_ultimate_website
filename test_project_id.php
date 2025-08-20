<?php
require_once 'db.php';

// Test if we can insert a duplicate project ID
try {
    // First, let's see what project IDs already exist
    $stmt = $pdo->query("SELECT project_id FROM projects LIMIT 5");
    $existing_projects = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (!empty($existing_projects)) {
        echo "Existing project IDs:\n";
        foreach ($existing_projects as $project_id) {
            echo "  - $project_id\n";
        }
        
        // Try to insert a duplicate
        $test_id = $existing_projects[0]; // Use the first existing project ID
        echo "\nTrying to insert duplicate project ID: $test_id\n";
        
        $stmt = $pdo->prepare("INSERT INTO projects (project_id, project_name, hotel_name, start_date, type, status) VALUES (?, 'Test Project', 1, '2023-01-01', 'Maintenance', 'Scheduled')");
        
        try {
            $stmt->execute([$test_id]);
            echo "❌ ERROR: Successfully inserted duplicate project ID (this should not happen)\n";
        } catch (Exception $e) {
            echo "✅ SUCCESS: Database prevented duplicate project ID insertion\n";
            echo "Error message: " . $e->getMessage() . "\n";
        }
    } else {
        echo "No existing projects found in database\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}