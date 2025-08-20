<?php
require_once 'db.php';

try {
    // List all projects
    echo "Listing all projects:\n";
    $stmt = $pdo->query("SELECT id, project_id, project_name FROM projects ORDER BY created_at DESC LIMIT 10");
    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($projects)) {
        echo "No projects found in database\n";
    } else {
        foreach ($projects as $project) {
            echo "ID: {$project['id']}, Project ID: {$project['project_id']}, Name: {$project['project_name']}\n";
        }
    }
    
    // Check for duplicates
    echo "\nChecking for duplicate project IDs:\n";
    $stmt = $pdo->query("SELECT project_id, COUNT(*) as count FROM projects GROUP BY project_id HAVING COUNT(*) > 1");
    $duplicates = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($duplicates)) {
        echo "No duplicate project IDs found\n";
    } else {
        echo "Duplicate project IDs found:\n";
        foreach ($duplicates as $duplicate) {
            echo "  Project ID: {$duplicate['project_id']}, Count: {$duplicate['count']}\n";
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}