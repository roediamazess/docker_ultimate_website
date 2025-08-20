<?php
require_once 'db.php';

echo "<!DOCTYPE html>
<html>
<head>
    <title>Verify Constraint and Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .error { color: red; }
        .success { color: green; }
        .warning { color: orange; }
        .info { color: blue; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 5px; }
        .button { background: #007cba; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; margin: 10px 0; }
        .button:hover { background: #005a87; }
        table { border-collapse: collapse; width: 100%; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h1>Verify Constraint and Test</h1>";

try {
    // Check if unique constraint exists
    echo "<h2>Checking for unique constraint on project_id...</h2>";
    $stmt = $pdo->query("SELECT constraint_name FROM information_schema.table_constraints WHERE table_name = 'projects' AND constraint_type = 'UNIQUE'");
    $constraints = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $project_id_constraint_found = false;
    if (empty($constraints)) {
        echo "<p class='warning'>⚠️ No unique constraints found on projects table</p>";
    } else {
        echo "<p class='info'>Unique constraints found: " . implode(', ', $constraints) . "</p>";
        foreach ($constraints as $constraint) {
            if (stripos($constraint, 'project_id') !== false) {
                $project_id_constraint_found = true;
                echo "<p class='success'>✅ Found constraint related to project_id: $constraint</p>";
            }
        }
    }
    
    if (!$project_id_constraint_found) {
        echo "<p class='error'>❌ Unique constraint on project_id is missing</p>";
    } else {
        echo "<p class='success'>✅ Unique constraint on project_id exists</p>";
    }
    
    // Test the constraint with a direct database insert
    echo "<h2>Testing constraint with direct database insert...</h2>";
    
    // First, check if project ID '100' already exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM projects WHERE project_id = ?");
    $stmt->execute(['100']);
    $count = $stmt->fetchColumn();
    
    if ($count > 0) {
        echo "<p class='info'>Project ID '100' already exists in database</p>";
    } else {
        echo "<p class='info'>Project ID '100' does not exist in database</p>";
    }
    
    // Try to insert a project with ID '100'
    echo "<p class='info'>Attempting to insert project with ID '100'...</p>";
    try {
        $stmt = $pdo->prepare("INSERT INTO projects (project_id, project_name, hotel_name, start_date, type, status) VALUES (?, 'Test Project', 1, '2023-01-01', 'Maintenance', 'Scheduled')");
        $stmt->execute(['100']);
        
        // If we get here, it means the insert succeeded
        $inserted_id = $pdo->lastInsertId();
        echo "<p class='success'>✅ Successfully inserted project with ID '100' (Database ID: $inserted_id)</p>";
        
        // Clean up
        $stmt = $pdo->prepare("DELETE FROM projects WHERE id = ?");
        $stmt->execute([$inserted_id]);
        echo "<p class='info'>Cleaned up test project</p>";
    } catch (Exception $e) {
        // Check if this is a duplicate key error
        if (strpos($e->getMessage(), 'duplicate') !== false || strpos($e->getMessage(), 'unique') !== false) {
            echo "<p class='success'>✅ Database prevented duplicate project ID insertion</p>";
            echo "<p class='info'>Error message: " . $e->getMessage() . "</p>";
        } else {
            echo "<p class='error'>❌ Unexpected error: " . $e->getMessage() . "</p>";
        }
    }
    
    // Show some sample projects
    echo "<h2>Sample projects in database:</h2>";
    $stmt = $pdo->query('SELECT id, project_id, project_name FROM projects ORDER BY created_at DESC LIMIT 10');
    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($projects)) {
        echo "<p class='info'>No projects found in database</p>";
    } else {
        echo "<table><tr><th>ID</th><th>Project ID</th><th>Project Name</th></tr>";
        foreach ($projects as $project) {
            echo "<tr><td>{$project['id']}</td><td>{$project['project_id']}</td><td>{$project['project_name']}</td></tr>";
        }
        echo "</table>";
    }
    
} catch (Exception $e) {
    echo "<p class='error'>Error: " . $e->getMessage() . "</p>";
}

echo "</body>
</html>";