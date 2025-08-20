<?php
require_once 'db.php';

echo "<!DOCTYPE html>
<html>
<head>
    <title>Fix Constraint Name</title>
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
    <h1>Fix Constraint Name</h1>";

try {
    // Check if the constraint with the correct name exists
    echo "<h2>Checking for constraint 'uq_projects_project_id'...</h2>";
    $stmt = $pdo->query("SELECT constraint_name FROM information_schema.table_constraints WHERE table_name = 'projects' AND constraint_name = 'uq_projects_project_id'");
    $constraint = $stmt->fetchColumn();
    
    if ($constraint) {
        echo "<p class='success'>✅ Constraint 'uq_projects_project_id' already exists</p>";
    } else {
        echo "<p class='warning'>⚠️ Constraint 'uq_projects_project_id' does not exist</p>";
        
        // Check if there's another constraint on project_id
        $stmt = $pdo->query("SELECT constraint_name FROM information_schema.table_constraints WHERE table_name = 'projects' AND constraint_type = 'UNIQUE'");
        $constraints = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        if (!empty($constraints)) {
            echo "<p class='info'>Found unique constraints: " . implode(', ', $constraints) . "</p>";
            
            // Try to rename the existing constraint
            foreach ($constraints as $constraint_name) {
                if (stripos($constraint_name, 'project_id') !== false) {
                    echo "<p class='info'>Renaming constraint '$constraint_name' to 'uq_projects_project_id'...</p>";
                    try {
                        $pdo->exec("ALTER TABLE projects RENAME CONSTRAINT $constraint_name TO uq_projects_project_id");
                        echo "<p class='success'>✅ Successfully renamed constraint</p>";
                        break;
                    } catch (Exception $e) {
                        echo "<p class='warning'>⚠️ Could not rename constraint: " . $e->getMessage() . "</p>";
                    }
                }
            }
        } else {
            echo "<p class='info'>No unique constraints found, creating new one...</p>";
            
            // Create the constraint
            try {
                $pdo->exec("ALTER TABLE projects ADD CONSTRAINT uq_projects_project_id UNIQUE (project_id)");
                echo "<p class='success'>✅ Successfully created constraint 'uq_projects_project_id'</p>";
            } catch (Exception $e) {
                echo "<p class='error'>❌ Could not create constraint: " . $e->getMessage() . "</p>";
            }
        }
    }
    
    // Test the constraint
    echo "<h2>Testing constraint...</h2>";
    
    // Check if project ID '100' already exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM projects WHERE project_id = ?");
    $stmt->execute(['100']);
    $count = $stmt->fetchColumn();
    
    if ($count == 0) {
        // Insert a project with ID '100' for testing
        $stmt = $pdo->prepare("INSERT INTO projects (project_id, project_name, hotel_name, start_date, type, status) VALUES (?, 'Test Project', 1, '2023-01-01', 'Maintenance', 'Scheduled')");
        $stmt->execute(['100']);
        echo "<p class='success'>✅ Inserted test project with ID '100'</p>";
    }
    
    // Try to insert a duplicate project
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
    
} catch (Exception $e) {
    echo "<p class='error'>Error: " . $e->getMessage() . "</p>";
}

echo "</body>
</html>";