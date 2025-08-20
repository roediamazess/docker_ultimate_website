<?php
require_once 'db.php';

echo "<!DOCTYPE html>
<html>
<head>
    <title>Project Status Check</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .error { color: red; }
        .success { color: green; }
        .warning { color: orange; }
        .info { color: blue; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 5px; }
        table { border-collapse: collapse; width: 100%; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h1>Project Status Check</h1>";

try {
    // Check for duplicates
    echo "<h2>Checking for duplicate project IDs...</h2>";
    $stmt = $pdo->query('SELECT project_id, COUNT(*) as count FROM projects GROUP BY project_id HAVING COUNT(*) > 1');
    $duplicates = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($duplicates)) {
        echo "<p class='success'>✅ No duplicate project IDs found</p>";
    } else {
        echo "<p class='error'>❌ Duplicate project IDs found:</p>";
        echo "<table><tr><th>Project ID</th><th>Count</th></tr>";
        foreach ($duplicates as $duplicate) {
            echo "<tr><td>{$duplicate['project_id']}</td><td>{$duplicate['count']}</td></tr>";
        }
        echo "</table>";
    }
    
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
        echo "<p class='warning'>⚠️ Unique constraint on project_id is missing</p>";
        echo "<p class='info'>This explains why you can still create projects with existing IDs.</p>";
    } else {
        echo "<p class='success'>✅ Unique constraint on project_id exists</p>";
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