<?php
require_once 'db.php';

echo "<h2>🔍 Checking Activities Table Database Sync</h2>";

try {
    // Check if activities table exists
    $check_table = "SELECT EXISTS (SELECT FROM information_schema.tables WHERE table_name = 'activities')";
    $table_exists = $pdo->query($check_table)->fetchColumn();
    
    if (!$table_exists) {
        echo "<p style='color: red;'>❌ Activities table does not exist!</p>";
        exit;
    }
    
    echo "<p style='color: green;'>✅ Activities table exists</p>";
    
    // Get table structure
    $structure_sql = "SELECT column_name, data_type, is_nullable, column_default 
                      FROM information_schema.columns 
                      WHERE table_name = 'activities' 
                      ORDER BY ordinal_position";
    $structure = $pdo->query($structure_sql)->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>📋 Current Table Structure:</h3>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Column</th><th>Type</th><th>Nullable</th><th>Default</th></tr>";
    
    $required_columns = [
        'id' => 'SERIAL PRIMARY KEY',
        'project_id' => 'VARCHAR(50)',
        'no' => 'INTEGER',
        'information_date' => 'DATE',
        'priority' => 'VARCHAR(20) DEFAULT Normal',
        'user_position' => 'VARCHAR(100)',
        'department' => 'department_type ENUM',
        'application' => 'application_type ENUM',
        'type' => 'activity_type ENUM',
        'description' => 'TEXT',
        'action_solution' => 'TEXT',
        'customer' => 'VARCHAR(100)',
        'project' => 'VARCHAR(100)',
        'completed_date' => 'DATE',
        'due_date' => 'DATE',
        'status' => 'activity_status ENUM',
        'cnc_number' => 'VARCHAR(50)',
        'created_by' => 'INTEGER REFERENCES users(id)',
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
    
    // Check activity_type ENUM
    $type_enum_sql = "SELECT unnest(enum_range(NULL::activity_type)) as type_value";
    try {
        $type_values = $pdo->query($type_enum_sql)->fetchAll(PDO::FETCH_COLUMN);
        echo "<p style='color: green;'>✅ activity_type ENUM values: " . implode(', ', $type_values) . "</p>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ activity_type ENUM error: " . $e->getMessage() . "</p>";
    }
    
    // Check activity_status ENUM
    $status_enum_sql = "SELECT unnest(enum_range(NULL::activity_status)) as status_value";
    try {
        $status_values = $pdo->query($status_enum_sql)->fetchAll(PDO::FETCH_COLUMN);
        echo "<p style='color: green;'>✅ activity_status ENUM values: " . implode(', ', $status_values) . "</p>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ activity_status ENUM error: " . $e->getMessage() . "</p>";
    }
    
    // Check department_type ENUM
    $dept_enum_sql = "SELECT unnest(enum_range(NULL::department_type)) as dept_value";
    try {
        $dept_values = $pdo->query($dept_enum_sql)->fetchAll(PDO::FETCH_COLUMN);
        echo "<p style='color: green;'>✅ department_type ENUM values: " . implode(', ', $dept_values) . "</p>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ department_type ENUM error: " . $e->getMessage() . "</p>";
    }
    
    // Check application_type ENUM
    $app_enum_sql = "SELECT unnest(enum_range(NULL::application_type)) as app_value";
    try {
        $app_values = $pdo->query($app_enum_sql)->fetchAll(PDO::FETCH_COLUMN);
        echo "<p style='color: green;'>✅ application_type ENUM values: " . implode(', ', $app_values) . "</p>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ application_type ENUM error: " . $e->getMessage() . "</p>";
    }
    
    // Check sample data
    echo "<h3>📊 Sample Data Check:</h3>";
    $sample_sql = "SELECT COUNT(*) as total_activities FROM activities";
    $total_activities = $pdo->query($sample_sql)->fetchColumn();
    echo "<p>Total activities in database: <strong>{$total_activities}</strong></p>";
    
    if ($total_activities > 0) {
        $sample_data_sql = "SELECT id, project_id, type, status, priority, description, due_date, created_at 
                            FROM activities LIMIT 3";
        $sample_data = $pdo->query($sample_data_sql)->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h4>Sample Activities:</h4>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Project ID</th><th>Type</th><th>Status</th><th>Priority</th><th>Description</th><th>Due Date</th><th>Created</th></tr>";
        
        foreach ($sample_data as $activity) {
            echo "<tr>";
            echo "<td>{$activity['id']}</td>";
            echo "<td>" . htmlspecialchars($activity['project_id'] ?? '-') . "</td>";
            echo "<td>" . htmlspecialchars($activity['type'] ?? '-') . "</td>";
            echo "<td>" . htmlspecialchars($activity['status'] ?? '-') . "</td>";
            echo "<td>" . htmlspecialchars($activity['priority'] ?? '-') . "</td>";
            echo "<td>" . htmlspecialchars(substr($activity['description'] ?? '-', 0, 50)) . (strlen($activity['description'] ?? '') > 50 ? '...' : '') . "</td>";
            echo "<td>" . ($activity['due_date'] ? date('Y-m-d', strtotime($activity['due_date'])) : '-') . "</td>";
            echo "<td>" . date('Y-m-d H:i', strtotime($activity['created_at'])) . "</td>";
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
