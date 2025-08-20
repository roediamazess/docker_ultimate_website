<?php
require_once 'db.php';

echo "<!DOCTYPE html>
<html>
<head>
    <title>Check Duplicates</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .error { color: red; }
        .success { color: green; }
        .warning { color: orange; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 5px; }
    </style>
</head>
<body>
    <h1>Project ID Duplicate Check</h1>";

try {
    // Check if there are any duplicate project_id values
    echo "<h2>Checking for duplicate project IDs...</h2>";
    $stmt = $pdo->query('SELECT project_id, COUNT(*) as count FROM projects GROUP BY project_id HAVING COUNT(*) > 1');
    $duplicates = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($duplicates)) {
        echo "<p class='success'>No duplicate project IDs found</p>";
    } else {
        echo "<p class='error'>Duplicate project IDs found:</p>";
        echo "<pre>";
        foreach ($duplicates as $duplicate) {
            echo "Project ID: {$duplicate['project_id']}, Count: {$duplicate['count']}\n";
        }
        echo "</pre>";
        
        // This is the problem - we have duplicates in the database
        echo "<div class='warning'>
                <h3>Issue Identified:</h3>
                <p>This explains why you can still create projects with existing IDs.</p>
                <p>The database constraint is not preventing duplicates because there are already duplicates in the table.</p>
                <p>We need to clean up the duplicates first before adding the constraint.</p>
              </div>";
    }
    
    // Check if unique constraint exists
    echo "<h2>Checking for unique constraint on project_id...</h2>";
    $stmt = $pdo->query("SELECT constraint_name FROM information_schema.table_constraints WHERE table_name = 'projects' AND constraint_type = 'UNIQUE'");
    $constraints = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $project_id_constraint_found = false;
    if (empty($constraints)) {
        echo "<p class='warning'>No unique constraints found on projects table</p>";
    } else {
        echo "<p class='success'>Unique constraints found: " . implode(', ', $constraints) . "</p>";
        foreach ($constraints as $constraint) {
            if (stripos($constraint, 'project_id') !== false) {
                $project_id_constraint_found = true;
                echo "<p class='success'>Found constraint related to project_id: $constraint</p>";
            }
        }
    }
    
    // If no constraint found, suggest adding it
    if (!$project_id_constraint_found) {
        echo "<div class='warning'>
                <h3>Recommendation:</h3>
                <p>After cleaning up duplicates, add a unique constraint on project_id:</p>
                <pre>ALTER TABLE projects ADD CONSTRAINT uk_projects_project_id UNIQUE (project_id);</pre>
              </div>";
    } else {
        echo "<p class='success'>Unique constraint on project_id already exists</p>";
    }
} catch (Exception $e) {
    echo "<p class='error'>Error: " . $e->getMessage() . "</p>";
}

echo "</body>
</html>";