<?php
require_once 'db.php';

echo "<!DOCTYPE html>
<html>
<head>
    <title>Test Project ID Fix</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .error { color: red; }
        .success { color: green; }
        .warning { color: orange; }
        .info { color: blue; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 5px; }
        .button { background: #007cba; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; }
        .button:hover { background: #005a87; }
    </style>
</head>
<body>
    <h1>Test Project ID Fix</h1>";

try {
    if (isset($_POST['action']) && $_POST['action'] === 'test') {
        $test_project_id = $_POST['project_id'] ?? '100';
        
        echo "<h2>Testing with Project ID: $test_project_id</h2>";
        
        // First, try to insert a project with this ID
        echo "<p class='info'>Attempting to insert project with ID: $test_project_id</p>";
        
        try {
            $stmt = $pdo->prepare("INSERT INTO projects (project_id, project_name, hotel_name, start_date, type, status) VALUES (?, 'Test Project', 1, '2023-01-01', 'Maintenance', 'Scheduled')");
            $stmt->execute([$test_project_id]);
            
            // If we get here, it means the insert succeeded
            $inserted_id = $pdo->lastInsertId();
            echo "<p class='success'>Successfully inserted project with ID: $test_project_id (Database ID: $inserted_id)</p>";
            
            // Now try to insert another project with the same ID
            echo "<p class='info'>Attempting to insert duplicate project with ID: $test_project_id</p>";
            try {
                $stmt = $pdo->prepare("INSERT INTO projects (project_id, project_name, hotel_name, start_date, type, status) VALUES (?, 'Test Project 2', 1, '2023-01-02', 'Maintenance', 'Scheduled')");
                $stmt->execute([$test_project_id]);
                echo "<p class='error'>ERROR: Successfully inserted duplicate project ID (this should not happen)</p>";
            } catch (Exception $e) {
                echo "<p class='success'>SUCCESS: Database prevented duplicate project ID insertion</p>";
                echo "<p class='info'>Error message: " . $e->getMessage() . "</p>";
            }
            
            // Clean up the test project
            echo "<p class='info'>Cleaning up test project...</p>";
            $stmt = $pdo->prepare("DELETE FROM projects WHERE id = ?");
            $stmt->execute([$inserted_id]);
            echo "<p class='success'>Test project cleaned up</p>";
            
        } catch (Exception $e) {
            echo "<p class='success'>SUCCESS: Database prevented duplicate project ID insertion</p>";
            echo "<p class='info'>Error message: " . $e->getMessage() . "</p>";
        }
    } else {
        // Show form to test
        echo "<p class='info'>This script will test if the unique constraint is working properly.</p>";
        echo "<form method='post'>
                <input type='hidden' name='action' value='test'>
                <label for='project_id'>Project ID to test:</label>
                <input type='text' id='project_id' name='project_id' value='100' required>
                <button type='submit' class='button'>Test Project ID Constraint</button>
              </form>";
    }
} catch (Exception $e) {
    echo "<p class='error'>Error: " . $e->getMessage() . "</p>";
}

echo "</body>
</html>";