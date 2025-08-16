<?php
require_once 'db.php';

try {
    // Check if the constraint already exists
    $stmt = $pdo->prepare("SELECT constraint_name FROM information_schema.table_constraints WHERE table_name = 'projects' AND constraint_name = 'uk_projects_project_id'");
    $stmt->execute();
    $constraintExists = $stmt->fetch();
    
    if ($constraintExists) {
        echo "Unique constraint on project_id already exists.\n";
    } else {
        // Add unique constraint to project_id
        $pdo->exec("ALTER TABLE projects ADD CONSTRAINT uk_projects_project_id UNIQUE (project_id)");
        echo "Unique constraint on project_id added successfully.\n";
    }
    
    // Also add an index for better performance
    try {
        $pdo->exec("CREATE INDEX IF NOT EXISTS idx_projects_project_id ON projects(project_id)");
        echo "Index on project_id created successfully.\n";
    } catch (Exception $e) {
        echo "Index already exists or error creating index: " . $e->getMessage() . "\n";
    }
    
} catch (Exception $e) {
    echo "Error adding unique constraint: " . $e->getMessage() . "\n";
    
    // For MySQL, we would use:
    // $pdo->exec("ALTER TABLE projects ADD UNIQUE KEY uk_projects_project_id (project_id)");
}
?>