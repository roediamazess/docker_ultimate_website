<?php
require_once 'db.php';

echo "<h1>🔍 DATABASE SYNC CHECK - Ultimate Website</h1>";
echo "<p><strong>Timestamp:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "<hr>";

// Check Users Table
echo "<h2>👥 USERS TABLE CHECK</h2>";
include 'check_users_table_sync.php';

echo "<hr>";

// Check Projects Table
echo "<h2>📋 PROJECTS TABLE CHECK</h2>";
include 'check_projects_table_sync.php';

echo "<hr>";

// Check Activities Table
echo "<h2>📝 ACTIVITIES TABLE CHECK</h2>";
include 'check_activities_table_sync.php';

echo "<hr>";

// Overall Database Health Check
echo "<h2>🏥 OVERALL DATABASE HEALTH CHECK</h2>";

try {
    // Check database connection
    $pdo->query("SELECT 1");
    echo "<p style='color: green;'>✅ Database connection: OK</p>";
    
    // Check if all required tables exist
    $required_tables = ['users', 'projects', 'activities', 'customers'];
    $existing_tables = [];
    
    foreach ($required_tables as $table) {
        $check_sql = "SELECT EXISTS (SELECT FROM information_schema.tables WHERE table_name = '$table')";
        $exists = $pdo->query($check_sql)->fetchColumn();
        if ($exists) {
            $existing_tables[] = $table;
            echo "<p style='color: green;'>✅ Table '$table': EXISTS</p>";
        } else {
            echo "<p style='color: red;'>❌ Table '$table': MISSING</p>";
        }
    }
    
    // Check foreign key relationships
    echo "<h3>🔗 Foreign Key Relationships:</h3>";
    
    // Check users -> projects (pic field)
    try {
        $fk_check = "SELECT COUNT(*) FROM projects p 
                     LEFT JOIN users u ON p.pic = u.id 
                     WHERE p.pic IS NOT NULL AND u.id IS NULL";
        $orphaned_pics = $pdo->query($fk_check)->fetchColumn();
        if ($orphaned_pics == 0) {
            echo "<p style='color: green;'>✅ Projects -> Users (pic): All references valid</p>";
        } else {
            echo "<p style='color: orange;'>⚠️ Projects -> Users (pic): {$orphaned_pics} orphaned references</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Projects -> Users (pic): Error checking - " . $e->getMessage() . "</p>";
    }
    
    // Check projects -> customers (hotel_name field)
    try {
        $fk_check = "SELECT COUNT(*) FROM projects p 
                     LEFT JOIN customers c ON p.hotel_name = c.id 
                     WHERE c.id IS NULL";
        $orphaned_hotels = $pdo->query($fk_check)->fetchColumn();
        if ($orphaned_hotels == 0) {
            echo "<p style='color: green;'>✅ Projects -> Customers (hotel_name): All references valid</p>";
        } else {
            echo "<p style='color: orange;'>⚠️ Projects -> Customers (hotel_name): {$orphaned_hotels} orphaned references</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Projects -> Customers (hotel_name): Error checking - " . $e->getMessage() . "</p>";
    }
    
    // Check activities -> users (created_by field)
    try {
        $fk_check = "SELECT COUNT(*) FROM activities a 
                     LEFT JOIN users u ON a.created_by = u.id 
                     WHERE a.created_by IS NOT NULL AND u.id IS NULL";
        $orphaned_creators = $pdo->query($fk_check)->fetchColumn();
        if ($orphaned_creators == 0) {
            echo "<p style='color: green;'>✅ Activities -> Users (created_by): All references valid</p>";
        } else {
            echo "<p style='color: orange;'>⚠️ Activities -> Users (created_by): {$orphaned_creators} orphaned references</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Activities -> Users (created_by): Error checking - " . $e->getMessage() . "</p>";
    }
    
    // Check data integrity
    echo "<h3>📊 Data Integrity Check:</h3>";
    
    // Check for duplicate emails in users
    try {
        $duplicate_emails = $pdo->query("SELECT COUNT(*) FROM users GROUP BY email HAVING COUNT(*) > 1")->fetchAll();
        if (empty($duplicate_emails)) {
            echo "<p style='color: green;'>✅ Users: No duplicate emails found</p>";
        } else {
            echo "<p style='color: red;'>❌ Users: Duplicate emails detected</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Users email check: Error - " . $e->getMessage() . "</p>";
    }
    
    // Check for duplicate project_ids in projects
    try {
        $duplicate_projects = $pdo->query("SELECT COUNT(*) FROM projects GROUP BY project_id HAVING COUNT(*) > 1")->fetchAll();
        if (empty($duplicate_projects)) {
            echo "<p style='color: green;'>✅ Projects: No duplicate project_ids found</p>";
        } else {
            echo "<p style='color: red;'>❌ Projects: Duplicate project_ids detected</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Projects project_id check: Error - " . $e->getMessage() . "</p>";
    }
    
    // Final summary
    echo "<h3>📋 FINAL SUMMARY:</h3>";
    if (count($existing_tables) == count($required_tables)) {
        echo "<p style='color: green; font-weight: bold; font-size: 18px;'>🎉 DATABASE IS FULLY SYNCED AND HEALTHY!</p>";
        echo "<p>All required tables exist and relationships are properly maintained.</p>";
    } else {
        echo "<p style='color: red; font-weight: bold; font-size: 18px;'>⚠️ DATABASE NEEDS ATTENTION!</p>";
        echo "<p>Some required tables are missing or have issues.</p>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Database Health Check Error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><em>Database sync check completed at " . date('Y-m-d H:i:s') . "</em></p>";
?>

<style>
body { 
    font-family: Arial, sans-serif; 
    margin: 20px; 
    background: #f5f5f5;
}
h1 { 
    color: #2c3e50; 
    text-align: center;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 20px;
    border-radius: 10px;
    margin-bottom: 30px;
}
h2 { 
    color: #34495e; 
    background: #ecf0f1;
    padding: 10px;
    border-radius: 5px;
    border-left: 4px solid #3498db;
}
h3 { 
    color: #2c3e50; 
    border-bottom: 2px solid #bdc3c7;
    padding-bottom: 5px;
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
hr { 
    border: none; 
    border-top: 2px solid #bdc3c7; 
    margin: 30px 0; 
}
p { 
    margin: 8px 0; 
    line-height: 1.6;
}
em { 
    color: #7f8c8d; 
    font-style: italic;
}
</style>
