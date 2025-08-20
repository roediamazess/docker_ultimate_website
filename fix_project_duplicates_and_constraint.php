<?php
require_once 'db.php';

echo "<!DOCTYPE html>
<html>
<head>
    <title>Fix Project Duplicates and Constraint</title>
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
    <h1>Fix Project Duplicates and Add Constraint</h1>";

try {
    if (isset($_POST['action']) && $_POST['action'] === 'fix') {
        echo "<h2>Starting fix process...</h2>";
        
        // Start transaction
        $pdo->beginTransaction();
        
        // Check for duplicates first
        echo "<p class='info'>Checking for duplicate project IDs...</p>";
        $stmt = $pdo->query('SELECT project_id, COUNT(*) as count FROM projects GROUP BY project_id HAVING COUNT(*) > 1');
        $duplicates = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($duplicates)) {
            echo "<p class='success'>✅ No duplicates found. Database is clean.</p>";
        } else {
            echo "<p class='warning'>⚠️ Found " . count($duplicates) . " duplicate project IDs. Cleaning up...</p>";
            
            $total_deleted = 0;
            foreach ($duplicates as $duplicate) {
                $project_id = $duplicate['project_id'];
                echo "<p class='info'>Processing duplicate project ID: $project_id</p>";
                
                // Get all records with this project_id, ordered by created_at
                $stmt = $pdo->prepare('SELECT id, created_at FROM projects WHERE project_id = ? ORDER BY created_at ASC');
                $stmt->execute([$project_id]);
                $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Keep the first record (oldest) and delete the rest
                if (count($records) > 1) {
                    echo "<p>Found " . count($records) . " records with project ID: $project_id</p>";
                    
                    // Delete all but the first record
                    for ($i = 1; $i < count($records); $i++) {
                        $record_id = $records[$i]['id'];
                        echo "<p>Deleting duplicate record ID: $record_id</p>";
                        
                        // Delete from projects_detail first (foreign key constraint)
                        $stmt_detail = $pdo->prepare('DELETE FROM projects_detail WHERE project_id = ?');
                        $stmt_detail->execute([$project_id]);
                        $deleted_details = $stmt_detail->rowCount();
                        echo "<p>Deleted $deleted_details project detail records</p>";
                        
                        // Delete from projects
                        $stmt_project = $pdo->prepare('DELETE FROM projects WHERE id = ?');
                        $stmt_project->execute([$record_id]);
                        echo "<p>Deleted project record</p>";
                        
                        $total_deleted++;
                    }
                }
            }
            
            echo "<p class='success'>✅ Deleted $total_deleted duplicate records</p>";
        }
        
        // Commit transaction
        $pdo->commit();
        echo "<p class='success'>✅ Transaction committed successfully!</p>";
        
        // Now add the unique constraint
        echo "<h2>Adding unique constraint on project_id...</h2>";
        try {
            $pdo->exec("ALTER TABLE projects ADD CONSTRAINT uk_projects_project_id UNIQUE (project_id)");
            echo "<p class='success'>✅ Unique constraint added successfully!</p>";
        } catch (Exception $e) {
            // Constraint might already exist
            if (strpos($e->getMessage(), 'already exists') !== false || strpos($e->getMessage(), 'duplicate') !== false) {
                echo "<p class='success'>✅ Unique constraint already exists</p>";
            } else {
                echo "<p class='warning'>⚠️ Could not add unique constraint: " . $e->getMessage() . "</p>";
                echo "<p class='info'>You may need to add it manually with: ALTER TABLE projects ADD CONSTRAINT uk_projects_project_id UNIQUE (project_id);</p>";
            }
        }
        
        echo "<p class='success'><strong>✅ Fix process completed!</strong></p>";
        echo "<p class='info'>Now you should not be able to create projects with duplicate IDs.</p>";
        
    } else {
        // Show form to start the process
        echo "<p class='warning'>⚠️ This script will remove duplicate project IDs, keeping only the oldest record for each duplicate.</p>";
        echo "<p class='warning'>⚠️ All associated project details will also be removed for the duplicates.</p>";
        echo "<p class='warning'>⚠️ Make sure to backup your database before proceeding!</p>";
        
        echo "<form method='post'>
                <input type='hidden' name='action' value='fix'>
                <button type='submit' class='button' onclick='return confirm(\"⚠️ Are you sure you want to proceed? This will delete duplicate projects.\\n\\nClick OK to continue or Cancel to abort.\")'>Fix Duplicates and Add Constraint</button>
              </form>";
    }
} catch (Exception $e) {
    // Rollback transaction if needed
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
        echo "<p class='error'>❌ Transaction rolled back due to error.</p>";
    }
    echo "<p class='error'>❌ Error: " . $e->getMessage() . "</p>";
    echo "<p class='info'>Please check the error and try again.</p>";
}

echo "</body>
</html>";