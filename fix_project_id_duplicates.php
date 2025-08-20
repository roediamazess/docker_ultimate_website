<?php
require_once 'db.php';

echo "<!DOCTYPE html>
<html>
<head>
    <title>Fix Project ID Duplicates</title>
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
    <h1>Fix Project ID Duplicates</h1>";

try {
    if (isset($_POST['action']) && $_POST['action'] === 'fix') {
        // Start transaction
        $pdo->beginTransaction();
        
        // Get all duplicate project IDs
        $stmt = $pdo->query('SELECT project_id, COUNT(*) as count FROM projects GROUP BY project_id HAVING COUNT(*) > 1');
        $duplicates = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($duplicates)) {
            echo "<p class='success'>No duplicates found. Database is clean.</p>";
        } else {
            echo "<p class='info'>Found " . count($duplicates) . " duplicate project IDs. Cleaning up...</p>";
            
            foreach ($duplicates as $duplicate) {
                $project_id = $duplicate['project_id'];
                echo "<p class='info'>Processing duplicate project ID: $project_id</p>";
                
                // Get all records with this project_id
                $stmt = $pdo->prepare('SELECT id, created_at FROM projects WHERE project_id = ? ORDER BY created_at ASC');
                $stmt->execute([$project_id]);
                $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Keep the first record (oldest) and delete the rest
                if (count($records) > 1) {
                    echo "<p>Found " . count($records) . " records with project ID: $project_id</p>";
                    
                    // Keep the first record (index 0) and delete the rest
                    for ($i = 1; $i < count($records); $i++) {
                        $record_id = $records[$i]['id'];
                        echo "<p>Deleting duplicate record ID: $record_id</p>";
                        
                        // Delete from projects_detail first (foreign key constraint)
                        $stmt = $pdo->prepare('DELETE FROM projects_detail WHERE project_id = ?');
                        $stmt->execute([$project_id]);
                        
                        // Delete from projects
                        $stmt = $pdo->prepare('DELETE FROM projects WHERE id = ?');
                        $stmt->execute([$record_id]);
                    }
                }
            }
            
            // Commit transaction
            $pdo->commit();
            
            echo "<p class='success'>Duplicate cleanup completed successfully!</p>";
            
            // Now add the unique constraint
            echo "<p class='info'>Adding unique constraint on project_id...</p>";
            try {
                $pdo->exec("ALTER TABLE projects ADD CONSTRAINT uk_projects_project_id UNIQUE (project_id)");
                echo "<p class='success'>Unique constraint added successfully!</p>";
            } catch (Exception $e) {
                // Constraint might already exist
                if (strpos($e->getMessage(), 'already exists') !== false) {
                    echo "<p class='success'>Unique constraint already exists</p>";
                } else {
                    echo "<p class='warning'>Could not add unique constraint: " . $e->getMessage() . "</p>";
                    echo "<p class='info'>You may need to add it manually with: ALTER TABLE projects ADD CONSTRAINT uk_projects_project_id UNIQUE (project_id);</p>";
                }
            }
        }
    } else {
        // Show form to start the process
        echo "<p class='warning'>This script will remove duplicate project IDs, keeping only the oldest record for each duplicate.</p>";
        echo "<p class='warning'>All associated project details will also be removed for the duplicates.</p>";
        echo "<p class='warning'>Make sure to backup your database before proceeding!</p>";
        
        echo "<form method='post'>
                <input type='hidden' name='action' value='fix'>
                <button type='submit' class='button' onclick='return confirm(\"Are you sure you want to proceed? This will delete duplicate projects.\")'>Fix Duplicates and Add Constraint</button>
              </form>";
    }
} catch (Exception $e) {
    // Rollback transaction if needed
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "<p class='error'>Error: " . $e->getMessage() . "</p>";
}

echo "</body>
</html>";