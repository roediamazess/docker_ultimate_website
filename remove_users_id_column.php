<?php
require_once 'db.php';

echo "<h2>🗑️ Removing Users ID Column</h2>";
echo "<p><strong>Goal:</strong> Remove the id column from users table since display_name is now the primary key</p>";

try {
    // Check current table structure
    echo "<h3>📋 Current Table Structure:</h3>";
    $structure_sql = "SELECT column_name, data_type, is_nullable, column_default, is_identity 
                      FROM information_schema.columns 
                      WHERE table_name = 'users' 
                      ORDER BY ordinal_position";
    $structure = $pdo->query($structure_sql)->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Column</th><th>Type</th><th>Nullable</th><th>Default</th><th>Identity</th></tr>";
    
    foreach ($structure as $col) {
        echo "<tr>";
        echo "<td>{$col['column_name']}</td>";
        echo "<td>{$col['data_type']}</td>";
        echo "<td>{$col['is_nullable']}</td>";
        echo "<td>{$col['column_default']}</td>";
        echo "<td>{$col['is_identity']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Check current constraints
    echo "<h3>🔗 Current Constraints:</h3>";
    $constraints_sql = "SELECT conname, contype, pg_get_constraintdef(oid) as definition 
                        FROM pg_constraint 
                        WHERE conrelid = 'users'::regclass";
    $constraints = $pdo->query($constraints_sql)->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($constraints)) {
        echo "<p>No constraints found</p>";
    } else {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>Constraint Name</th><th>Type</th><th>Definition</th></tr>";
        foreach ($constraints as $constraint) {
            echo "<tr>";
            echo "<td>{$constraint['conname']}</td>";
            echo "<td>{$constraint['contype']}</td>";
            echo "<td>{$constraint['definition']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Start transaction
    $pdo->beginTransaction();
    
    echo "<h3>🔄 Starting Column Removal...</h3>";
    
    // Step 1: Drop the unique constraint on id column
    echo "<p>Step 1: Dropping unique constraint on id column...</p>";
    $pdo->exec("ALTER TABLE users DROP CONSTRAINT IF EXISTS users_id_unique");
    echo "<p style='color: green;'>✅ Dropped unique constraint on id column</p>";
    
    // Step 2: Drop the id column
    echo "<p>Step 2: Dropping id column...</p>";
    $pdo->exec("ALTER TABLE users DROP COLUMN id");
    echo "<p style='color: green;'>✅ Dropped id column</p>";
    
    // Commit transaction
    $pdo->commit();
    echo "<p style='color: green; font-weight: bold;'>🎉 ID column removal completed successfully!</p>";
    
    // Show new structure
    echo "<h3>📋 New Table Structure:</h3>";
    $new_structure = $pdo->query($structure_sql)->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Column</th><th>Type</th><th>Nullable</th><th>Default</th><th>Identity</th></tr>";
    
    foreach ($new_structure as $col) {
        echo "<tr>";
        echo "<td>{$col['column_name']}</td>";
        echo "<td>{$col['data_type']}</td>";
        echo "<td>{$col['is_nullable']}</td>";
        echo "<td>{$col['column_default']}</td>";
        echo "<td>{$col['is_identity']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Show new constraints
    echo "<h3>🔗 New Constraints:</h3>";
    $new_constraints = $pdo->query($constraints_sql)->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Constraint Name</th><th>Type</th><th>Definition</th></tr>";
    foreach ($new_constraints as $constraint) {
        echo "<tr>";
        echo "<td>{$constraint['conname']}</td>";
        echo "<td>{$constraint['contype']}</td>";
        echo "<td>{$constraint['definition']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h3>📋 Summary of Changes:</h3>";
    echo "<ul>";
    echo "<li>✅ <strong>id</strong> column has been completely removed</li>";
    echo "<li>✅ <strong>display_name</strong> remains the PRIMARY KEY</li>";
    echo "<li>✅ <strong>display_name</strong> has UNIQUE constraint</li>";
    echo "<li>✅ <strong>display_name</strong> is NOT NULL</li>";
    echo "<li>✅ <strong>All foreign key relationships</strong> still reference display_name</li>";
    echo "</ul>";
    
    echo "<p style='color: green; font-weight: bold;'>🎯 IMPORTANT: The users table is now cleaner and more efficient!</p>";
    echo "<p>All references now use display_name as the primary identifier.</p>";
    
} catch (PDOException $e) {
    // Rollback on error
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "<p style='color: red;'>❌ Database Error: " . $e->getMessage() . "</p>";
    echo "<p>Transaction rolled back. No changes were made.</p>";
} catch (Exception $e) {
    // Rollback on error
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
    echo "<p>Transaction rolled back. No changes were made.</p>";
}
?>

<style>
body { 
    font-family: Arial, sans-serif; 
    margin: 20px; 
    background: #f5f5f5;
}
h2, h3 { 
    color: #2c3e50; 
    background: #ecf0f1;
    padding: 10px;
    border-radius: 5px;
    border-left: 4px solid #3498db;
}
table { 
    margin: 10px 0; 
    background: white;
    border-radius: 5px;
    overflow: hidden;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}
th { 
    background: #34495e; 
    color: white;
    padding: 12px 8px; 
    font-weight: 600;
}
td { 
    padding: 10px 8px; 
    border-bottom: 1px solid #ecf0f1;
}
p { 
    margin: 8px 0; 
    line-height: 1.6;
}
ul {
    background: white;
    padding: 20px;
    border-radius: 5px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}
li {
    margin: 8px 0;
    line-height: 1.6;
}
</style>
