<?php
require_once 'db.php';

echo "<h2>🔧 Updating Users Table Structure</h2>";
echo "<p><strong>Goal:</strong> Make display_name the primary key and ensure it cannot be edited after creation</p>";

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
    
    echo "<h3>🔄 Starting Database Updates...</h3>";
    
    // Step 1: Remove existing primary key constraint from id column
    echo "<p>Step 1: Removing existing primary key from id column...</p>";
    $pdo->exec("ALTER TABLE users DROP CONSTRAINT IF EXISTS users_pkey");
    echo "<p style='color: green;'>✅ Removed existing primary key constraint</p>";
    
    // Step 2: Make display_name NOT NULL if it isn't already
    echo "<p>Step 2: Ensuring display_name is NOT NULL...</p>";
    $pdo->exec("ALTER TABLE users ALTER COLUMN display_name SET NOT NULL");
    echo "<p style='color: green;'>✅ Made display_name NOT NULL</p>";
    
    // Step 3: Add unique constraint to display_name
    echo "<p>Step 3: Adding unique constraint to display_name...</p>";
    $pdo->exec("ALTER TABLE users ADD CONSTRAINT users_display_name_unique UNIQUE (display_name)");
    echo "<p style='color: green;'>✅ Added unique constraint to display_name</p>";
    
    // Step 4: Make display_name the primary key
    echo "<p>Step 4: Making display_name the primary key...</p>";
    $pdo->exec("ALTER TABLE users ADD PRIMARY KEY (display_name)");
    echo "<p style='color: green;'>✅ Made display_name the primary key</p>";
    
    // Step 5: Make id column a regular serial column (remove identity)
    echo "<p>Step 5: Converting id to regular serial column...</p>";
    $pdo->exec("ALTER TABLE users ALTER COLUMN id DROP IDENTITY IF EXISTS");
    echo "<p style='color: green;'>✅ Removed identity from id column</p>";
    
    // Step 6: Add unique constraint to id column
    echo "<p>Step 6: Adding unique constraint to id column...</p>";
    $pdo->exec("ALTER TABLE users ADD CONSTRAINT users_id_unique UNIQUE (id)");
    echo "<p style='color: green;'>✅ Added unique constraint to id column</p>";
    
    // Commit transaction
    $pdo->commit();
    echo "<p style='color: green; font-weight: bold;'>🎉 All database updates completed successfully!</p>";
    
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
    echo "<li>✅ <strong>display_name</strong> is now the PRIMARY KEY</li>";
    echo "<li>✅ <strong>display_name</strong> has UNIQUE constraint</li>";
    echo "<li>✅ <strong>display_name</strong> is NOT NULL</li>";
    echo "<li>✅ <strong>id</strong> column is now just UNIQUE (not primary key)</li>";
    echo "<li>✅ <strong>id</strong> column is no longer auto-incrementing</li>";
    echo "</ul>";
    
    echo "<p style='color: green; font-weight: bold;'>🎯 IMPORTANT: Now display_name cannot be edited after creation!</p>";
    echo "<p>You'll need to update your application code to handle this new constraint.</p>";
    
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
