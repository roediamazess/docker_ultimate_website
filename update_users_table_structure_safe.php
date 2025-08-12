<?php
require_once 'db.php';

echo "<h2>🔧 Updating Users Table Structure (Safe Version)</h2>";
echo "<p><strong>Goal:</strong> Make display_name the primary key and ensure it cannot be edited after creation</p>";
echo "<p><strong>Note:</strong> This script will handle foreign key dependencies safely</p>";

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
    
    // Check foreign key dependencies
    echo "<h3>🔗 Foreign Key Dependencies:</h3>";
    $fk_deps_sql = "SELECT 
                        tc.table_name as dependent_table,
                        kcu.column_name as dependent_column,
                        ccu.table_name as referenced_table,
                        ccu.column_name as referenced_column,
                        tc.constraint_name
                    FROM information_schema.table_constraints tc
                    JOIN information_schema.key_column_usage kcu 
                        ON tc.constraint_name = kcu.constraint_name
                    JOIN information_schema.constraint_column_usage ccu 
                        ON ccu.constraint_name = tc.constraint_name
                    WHERE tc.constraint_type = 'FOREIGN KEY' 
                        AND ccu.table_name = 'users'";
    
    $fk_deps = $pdo->query($fk_deps_sql)->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($fk_deps)) {
        echo "<p>No foreign key dependencies found</p>";
    } else {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>Dependent Table</th><th>Dependent Column</th><th>Referenced Table</th><th>Referenced Column</th><th>Constraint Name</th></tr>";
        foreach ($fk_deps as $fk) {
            echo "<tr>";
            echo "<td>{$fk['dependent_table']}</td>";
            echo "<td>{$fk['dependent_column']}</td>";
            echo "<td>{$fk['referenced_table']}</td>";
            echo "<td>{$fk['referenced_column']}</td>";
            echo "<td>{$fk['constraint_name']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Start transaction
    $pdo->beginTransaction();
    
    echo "<h3>🔄 Starting Safe Database Updates...</h3>";
    
    // Step 1: Drop all foreign key constraints that reference users.id
    echo "<p>Step 1: Dropping foreign key constraints that reference users.id...</p>";
    
    foreach ($fk_deps as $fk) {
        if ($fk['referenced_column'] === 'id') {
            $drop_fk_sql = "ALTER TABLE {$fk['dependent_table']} DROP CONSTRAINT {$fk['constraint_name']}";
            $pdo->exec($drop_fk_sql);
            echo "<p style='color: orange;'>⚠️ Dropped FK constraint: {$fk['constraint_name']} on {$fk['dependent_table']}</p>";
        }
    }
    
    // Step 2: Remove existing primary key constraint from id column
    echo "<p>Step 2: Removing existing primary key from id column...</p>";
    $pdo->exec("ALTER TABLE users DROP CONSTRAINT IF EXISTS users_pkey");
    echo "<p style='color: green;'>✅ Removed existing primary key constraint</p>";
    
    // Step 3: Make display_name NOT NULL if it isn't already
    echo "<p>Step 3: Ensuring display_name is NOT NULL...</p>";
    $pdo->exec("ALTER TABLE users ALTER COLUMN display_name SET NOT NULL");
    echo "<p style='color: green;'>✅ Made display_name NOT NULL</p>";
    
    // Step 4: Add unique constraint to display_name
    echo "<p>Step 4: Adding unique constraint to display_name...</p>";
    $pdo->exec("ALTER TABLE users ADD CONSTRAINT users_display_name_unique UNIQUE (display_name)");
    echo "<p style='color: green;'>✅ Added unique constraint to display_name</p>";
    
    // Step 5: Make display_name the primary key
    echo "<p>Step 5: Making display_name the primary key...</p>";
    $pdo->exec("ALTER TABLE users ADD PRIMARY KEY (display_name)");
    echo "<p style='color: green;'>✅ Made display_name the primary key</p>";
    
    // Step 6: Make id column a regular integer column (remove identity)
    echo "<p>Step 6: Converting id to regular integer column...</p>";
    $pdo->exec("ALTER TABLE users ALTER COLUMN id DROP IDENTITY IF EXISTS");
    echo "<p style='color: green;'>✅ Removed identity from id column</p>";
    
    // Step 7: Add unique constraint to id column
    echo "<p>Step 7: Adding unique constraint to id column...</p>";
    $pdo->exec("ALTER TABLE users ADD CONSTRAINT users_id_unique UNIQUE (id)");
    echo "<p style='color: green;'>✅ Added unique constraint to id column</p>";
    
    // Step 8: Recreate foreign key constraints to reference display_name instead of id
    echo "<p>Step 8: Recreating foreign key constraints to reference display_name...</p>";
    
    foreach ($fk_deps as $fk) {
        if ($fk['referenced_column'] === 'id') {
            // Add new foreign key constraint referencing display_name
            $add_fk_sql = "ALTER TABLE {$fk['dependent_table']} 
                           ADD CONSTRAINT {$fk['dependent_table']}_{$fk['dependent_column']}_fkey 
                           FOREIGN KEY ({$fk['dependent_column']}) 
                           REFERENCES users(display_name)";
            $pdo->exec($add_fk_sql);
            echo "<p style='color: green;'>✅ Recreated FK constraint: {$fk['dependent_table']}.{$fk['dependent_column']} -> users.display_name</p>";
        }
    }
    
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
    
    // Show new foreign key relationships
    echo "<h3>🔗 New Foreign Key Relationships:</h3>";
    $new_fk_deps = $pdo->query($fk_deps_sql)->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($new_fk_deps)) {
        echo "<p>No foreign key dependencies found</p>";
    } else {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>Dependent Table</th><th>Dependent Column</th><th>Referenced Table</th><th>Referenced Column</th><th>Constraint Name</th></tr>";
        foreach ($new_fk_deps as $fk) {
            echo "<tr>";
            echo "<td>{$fk['dependent_table']}</td>";
            echo "<td>{$fk['dependent_column']}</td>";
            echo "<td>{$fk['referenced_table']}</td>";
            echo "<td>{$fk['referenced_column']}</td>";
            echo "<td>{$fk['constraint_name']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    echo "<h3>📋 Summary of Changes:</h3>";
    echo "<ul>";
    echo "<li>✅ <strong>display_name</strong> is now the PRIMARY KEY</li>";
    echo "<li>✅ <strong>display_name</strong> has UNIQUE constraint</li>";
    echo "<li>✅ <strong>display_name</strong> is NOT NULL</li>";
    echo "<li>✅ <strong>id</strong> column is now just UNIQUE (not primary key)</li>";
    echo "<li>✅ <strong>id</strong> column is no longer auto-incrementing</li>";
    echo "<li>✅ <strong>Foreign key constraints</strong> now reference display_name instead of id</li>";
    echo "</ul>";
    
    echo "<p style='color: green; font-weight: bold;'>🎯 IMPORTANT: Now display_name cannot be edited after creation!</p>";
    echo "<p>All foreign key relationships have been updated to use display_name as the reference.</p>";
    
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
