<?php
require_once 'db.php';

try {
    // Check if there are any duplicate project_id values
    $stmt = $pdo->query('SELECT project_id, COUNT(*) as count FROM projects GROUP BY project_id HAVING COUNT(*) > 1');
    $duplicates = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($duplicates)) {
        echo "✅ No duplicate project IDs found\n";
    } else {
        echo "❌ Duplicate project IDs found:\n";
        foreach ($duplicates as $duplicate) {
            echo "  Project ID: {$duplicate['project_id']}, Count: {$duplicate['count']}\n";
        }
    }
    
    // Check if unique constraint exists
    $stmt = $pdo->query("SELECT constraint_name FROM information_schema.table_constraints WHERE table_name = 'projects' AND constraint_type = 'UNIQUE'");
    $constraints = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($constraints)) {
        echo "❌ No unique constraints found on projects table\n";
    } else {
        echo "✅ Unique constraints found: " . implode(', ', $constraints) . "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}