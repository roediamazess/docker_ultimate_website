<?php
require_once 'db.php';

echo "<h2>🔍 Checking Users Table Database Sync</h2>";

try {
    // Check if users table exists
    $check_table = "SELECT EXISTS (SELECT FROM information_schema.tables WHERE table_name = 'users')";
    $table_exists = $pdo->query($check_table)->fetchColumn();
    
    if (!$table_exists) {
        echo "<p style='color: red;'>❌ Users table does not exist!</p>";
        exit;
    }
    
    echo "<p style='color: green;'>✅ Users table exists</p>";
    
    // Get table structure
    $structure_sql = "SELECT column_name, data_type, is_nullable, column_default 
                      FROM information_schema.columns 
                      WHERE table_name = 'users' 
                      ORDER BY ordinal_position";
    $structure = $pdo->query($structure_sql)->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>📋 Current Table Structure:</h3>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Column</th><th>Type</th><th>Nullable</th><th>Default</th></tr>";
    
    $required_columns = [
        'id' => 'SERIAL PRIMARY KEY',
        'display_name' => 'VARCHAR(100)',
        'full_name' => 'VARCHAR(100) NOT NULL',
        'email' => 'VARCHAR(100) NOT NULL UNIQUE',
        'password' => 'VARCHAR(255) NOT NULL',
        'tier' => 'user_tier ENUM',
        'role' => 'user_role ENUM',
        'start_work' => 'DATE',
        'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP'
    ];
    
    $existing_columns = [];
    foreach ($structure as $col) {
        $existing_columns[] = $col['column_name'];
        echo "<tr>";
        echo "<td>{$col['column_name']}</td>";
        echo "<td>{$col['data_type']}</td>";
        echo "<td>{$col['is_nullable']}</td>";
        echo "<td>{$col['column_default']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Check for missing columns
    echo "<h3>🔍 Checking Required Columns:</h3>";
    $missing_columns = [];
    foreach ($required_columns as $col_name => $col_spec) {
        if (!in_array($col_name, $existing_columns)) {
            $missing_columns[] = $col_name;
            echo "<p style='color: red;'>❌ Missing: {$col_name} ({$col_spec})</p>";
        } else {
            echo "<p style='color: green;'>✅ Found: {$col_name}</p>";
        }
    }
    
    // Check ENUM types
    echo "<h3>🔍 Checking ENUM Types:</h3>";
    
    // Check user_tier ENUM
    $tier_enum_sql = "SELECT unnest(enum_range(NULL::user_tier)) as tier_value";
    try {
        $tier_values = $pdo->query($tier_enum_sql)->fetchAll(PDO::FETCH_COLUMN);
        echo "<p style='color: green;'>✅ user_tier ENUM values: " . implode(', ', $tier_values) . "</p>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ user_tier ENUM error: " . $e->getMessage() . "</p>";
    }
    
    // Check user_role ENUM
    $role_enum_sql = "SELECT unnest(enum_range(NULL::user_role)) as role_value";
    try {
        $role_values = $pdo->query($role_enum_sql)->fetchAll(PDO::FETCH_COLUMN);
        echo "<p style='color: green;'>✅ user_role ENUM values: " . implode(', ', $role_values) . "</p>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ user_role ENUM error: " . $e->getMessage() . "</p>";
    }
    
    // Check sample data
    echo "<h3>📊 Sample Data Check:</h3>";
    $sample_sql = "SELECT COUNT(*) as total_users FROM users";
    $total_users = $pdo->query($sample_sql)->fetchColumn();
    echo "<p>Total users in database: <strong>{$total_users}</strong></p>";
    
    if ($total_users > 0) {
        $sample_data_sql = "SELECT id, display_name, full_name, email, tier, role, start_work, created_at 
                            FROM users LIMIT 3";
        $sample_data = $pdo->query($sample_data_sql)->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h4>Sample Users:</h4>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Display Name</th><th>Full Name</th><th>Email</th><th>Tier</th><th>Role</th><th>Start Work</th><th>Created</th></tr>";
        
        foreach ($sample_data as $user) {
            echo "<tr>";
            echo "<td>{$user['id']}</td>";
            echo "<td>" . htmlspecialchars($user['display_name'] ?? '-') . "</td>";
            echo "<td>" . htmlspecialchars($user['full_name']) . "</td>";
            echo "<td>" . htmlspecialchars($user['email']) . "</td>";
            echo "<td>" . htmlspecialchars($user['tier'] ?? '-') . "</td>";
            echo "<td>" . htmlspecialchars($user['role'] ?? '-') . "</td>";
            echo "<td>" . ($user['start_work'] ? date('Y-m-d', strtotime($user['start_work'])) : '-') . "</td>";
            echo "<td>" . date('Y-m-d H:i', strtotime($user['created_at'])) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Summary
    echo "<h3>📋 Summary:</h3>";
    if (empty($missing_columns)) {
        echo "<p style='color: green; font-weight: bold;'>🎉 All required columns are present! Database is fully synced.</p>";
    } else {
        echo "<p style='color: red; font-weight: bold;'>⚠️ Missing columns detected. Database needs to be updated.</p>";
        echo "<p>Missing columns: " . implode(', ', $missing_columns) . "</p>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Database Error: " . $e->getMessage() . "</p>";
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
table { margin: 10px 0; }
th { background: #f0f0f0; padding: 8px; }
td { padding: 8px; }
h2, h3, h4 { color: #333; }
</style>
