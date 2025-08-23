<?php
require_once 'db.php';

echo "<pre>
";
echo "Running migration: Add 'updated_by' column to activities table...\n\n";

try {
    // Check if column already exists
    $stmt = $pdo->query("SELECT column_name FROM information_schema.columns WHERE table_name = 'activities' AND column_name = 'updated_by'");
    
    if ($stmt->fetch()) {
        echo "✅ Column 'updated_by' already exists in 'activities' table. No action needed.\n";
    } else {
        echo "- Column 'updated_by' not found. Attempting to add it...\n";
        $pdo->exec("ALTER TABLE activities ADD COLUMN updated_by VARCHAR(255) NULL");
        echo "✅ Successfully added the 'updated_by' column.\n";
    }
} catch (PDOException $e) {
    die("❌ Error: " . $e->getMessage() . "\n");
}

echo "\nMigration script finished.\n</pre>";

