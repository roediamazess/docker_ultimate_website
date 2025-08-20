<?php
require_once 'db.php';

try {
    // Check if unique constraint exists on project_id
    echo "Checking for unique constraint on project_id...\n";
    
    // For PostgreSQL
    $stmt = $pdo->query("SELECT constraint_name FROM information_schema.table_constraints WHERE table_name = 'projects' AND constraint_type = 'UNIQUE'");
    $constraints = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $project_id_constraint_found = false;
    if (empty($constraints)) {
        echo "No unique constraints found on projects table\n";
    } else {
        echo "Unique constraints found: " . implode(', ', $constraints) . "\n";
        foreach ($constraints as $constraint) {
            if (stripos($constraint, 'project_id') !== false) {
                $project_id_constraint_found = true;
                echo "Found constraint related to project_id: $constraint\n";
            }
        }
    }
    
    // If no constraint found, try to add it
    if (!$project_id_constraint_found) {
        echo "Attempting to add unique constraint on project_id...\n";
        try {
            $pdo->exec("ALTER TABLE projects ADD CONSTRAINT uk_projects_project_id UNIQUE (project_id)");
            echo "Successfully added unique constraint on project_id\n";
        } catch (Exception $e) {
            echo "Failed to add unique constraint: " . $e->getMessage() . "\n";
        }
    } else {
        echo "Unique constraint on project_id already exists\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}