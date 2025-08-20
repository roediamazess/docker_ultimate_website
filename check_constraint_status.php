<?php
require_once 'db.php';

echo "<!DOCTYPE html>
<html>
<head>
    <title>Check Constraint Status</title>
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
    <h1>Check Constraint Status</h1>";

try {
    // Check if unique constraint exists
    echo "<h2>Checking for unique constraint on project_id...</h2>";
    $stmt = $pdo->query("SELECT constraint_name, constraint_type FROM information_schema.table_constraints WHERE table_name = 'projects' AND constraint_type = 'UNIQUE'");
    $constraints = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($constraints)) {
        echo "<p class='warning'>⚠️ No unique constraints found on projects table</p>";
    } else {
        echo "<p class='info'>Unique constraints found:</p>";
        echo "<table><tr><th>Constraint Name</th><th>Constraint Type</th></tr>";
        foreach ($constraints as $constraint) {
            echo "<tr><td>{$constraint['constraint_name']}</td><td>{$constraint['constraint_type']}</td></tr>";
            
            if (stripos($constraint['constraint_name'], 'project_id') !== false) {
                echo "<p class='success'>✅ Found constraint related to project_id: {$constraint['constraint_name']}</p>";
            }
        }
        echo "</table>";
    }
    
    // Check the specific constraint name
    $stmt = $pdo->query("SELECT constraint_name FROM information_schema.table_constraints WHERE table_name = 'projects' AND constraint_name LIKE '%project_id%'");
    $specific_constraints = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($specific_constraints)) {
        echo "<p class='warning'>⚠️ No constraint with 'project_id' in the name found</p>";
    } else {
        echo "<p class='info'>Constraints with 'project_id' in name: " . implode(', ', $specific_constraints) . "</p>";
    }
    
    // Check if project ID '100' already exists
    echo "<h2>Checking if project ID '100' exists...</h2>";
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM projects WHERE project_id = ?");
    $stmt->execute(['100']);
    $count = $stmt->fetchColumn();
    
    if ($count > 0) {
        echo "<p class='info'>✅ Project ID '100' already exists in database</p>";
    } else {
        echo "<p class='info'>❌ Project ID '100' does not exist in database</p>";
        
        // Insert a project with ID '100' for testing
        $stmt = $pdo->prepare("INSERT INTO projects (project_id, project_name, hotel_name, start_date, type, status) VALUES (?, 'Test Project', 1, '2023-01-01', 'Maintenance', 'Scheduled')");
        $stmt->execute(['100']);
        echo "<p class='success'>✅ Inserted test project with ID '100'</p>";
    }
    
    // Try to insert a duplicate project
    echo "<h2>Testing duplicate insertion...</h2>";
    try {
        $stmt = $pdo->prepare("INSERT INTO projects (project_id, project_name, hotel_name, start_date, type, status) VALUES (?, 'Test Duplicate Project', 1, '2023-02-01', 'Maintenance', 'Scheduled')");
        $stmt->execute(['100']);
        echo "<p class='error'>❌ Duplicate project was inserted (this should not happen)</p>";
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'duplicate') !== false || strpos($e->getMessage(), 'unique') !== false) {
            echo "<p class='success'>✅ Database prevented duplicate project insertion</p>";
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