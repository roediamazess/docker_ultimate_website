<?php
require_once 'db.php';

try {
    // Check if there are any duplicate project_id values
    echo "Checking for duplicate project IDs...\n";
    $stmt = $pdo->query('SELECT project_id, COUNT(*) as count FROM projects GROUP BY project_id HAVING COUNT(*) > 1');
    $duplicates = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($duplicates)) {
        echo "No duplicate project IDs found\n";
    } else {
        echo "Duplicate project IDs found:\n";
        foreach ($duplicates as $duplicate) {
            echo "  Project ID: {$duplicate['project_id']}, Count: {$duplicate['count']}\n";
        }
        
        // This is the problem - we have duplicates in the database
        echo "\nThis explains why you can still create projects with existing IDs.\n";
        echo "The database constraint is not preventing duplicates because there are already duplicates in the table.\n";
        echo "We need to clean up the duplicates first before adding the constraint.\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}