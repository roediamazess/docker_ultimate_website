<?php
require_once 'db.php';

echo "<h2>🔍 Checking Projects Table Database Sync</h2>";

try {
    // Check if projects table exists
    $check_table = "SELECT EXISTS (SELECT FROM information_schema.tables WHERE table_name = 'projects')";
    $table_exists = $pdo->query($check_table)->fetchColumn();
    
    if (!$table_exists) {
        echo "<p style='color: red;'>❌ Projects table does not exist!</p>";
        exit;
    }
    
    echo "<p style='color: green;'>✅ Projects table exists</p>";
    
    // Get table structure
    $structure_sql = "SELECT column_name, data_type, is_nullable, column_default 
                      FROM information_schema.columns 
                      WHERE table_name = 'projects' 
                      ORDER BY ordinal_position";
    $structure = $pdo->query($structure_sql)->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>📋 Current Table Structure:</h3>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Column</th><th>Type</th><th>Nullable</th><th>Default</th></tr>";
    
    $required_columns = [
        'id' => 'SERIAL PRIMARY KEY',
        'project_id' => 'VARCHAR(50) NOT NULL',
        'pic' => 'INTEGER REFERENCES users(id)',
        'assignment' => 'assignment_type ENUM',
        'project_information' => 'project_info_type ENUM',
        'req_pic' => 'req_pic_type ENUM',
        'hotel_name' => 'INTEGER NOT NULL REFERENCES customers(id)',
        'project_name' => 'VARCHAR(100)',
        'start_date' => 'DATE NOT NULL',
        'end_date' => 'DATE',
        'total_days' => 'INTEGER',
        'type' => 'project_type ENUM NOT NULL',
        'status' => 'project_status ENUM NOT NULL',
        'handover_official_report' => 'DATE',
        'handover_days' => 'INTEGER',
        'ketertiban_admin' => 'VARCHAR(20)',
        'point_ach' => 'INTEGER',
        'point_req' => 'INTEGER',
        'percent_point' => 'FLOAT',
        'month' => 'VARCHAR(20)',
        'quarter' => 'VARCHAR(20)',
        'week_no' => 'INTEGER',
        's1_estimation_kpi2' => 'TEXT',
        's1_over_days' => 'TEXT',
        's1_count_of_emails_sent' => 'TEXT',
        's2_email_sent' => 'TEXT',
        's3_email_sent' => 'TEXT',
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
    
    // Check project_type ENUM
    $type_enum_sql = "SELECT unnest(enum_range(NULL::project_type)) as type_value";
    try {
        $type_values = $pdo->query($type_enum_sql)->fetchAll(PDO::FETCH_COLUMN);
        echo "<p style='color: green;'>✅ project_type ENUM values: " . implode(', ', $type_values) . "</p>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ project_type ENUM error: " . $e->getMessage() . "</p>";
    }
    
    // Check project_status ENUM
    $status_enum_sql = "SELECT unnest(enum_range(NULL::project_status)) as status_value";
    try {
        $status_values = $pdo->query($status_enum_sql)->fetchAll(PDO::FETCH_COLUMN);
        echo "<p style='color: green;'>✅ project_status ENUM values: " . implode(', ', $status_values) . "</p>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ project_status ENUM error: " . $e->getMessage() . "</p>";
    }
    
    // Check project_info_type ENUM
    $info_enum_sql = "SELECT unnest(enum_range(NULL::project_info_type)) as info_value";
    try {
        $info_values = $pdo->query($info_enum_sql)->fetchAll(PDO::FETCH_COLUMN);
        echo "<p style='color: green;'>✅ project_info_type ENUM values: " . implode(', ', $info_values) . "</p>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ project_info_type ENUM error: " . $e->getMessage() . "</p>";
    }
    
    // Check sample data
    echo "<h3>📊 Sample Data Check:</h3>";
    $sample_sql = "SELECT COUNT(*) as total_projects FROM projects";
    $total_projects = $pdo->query($sample_sql)->fetchColumn();
    echo "<p>Total projects in database: <strong>{$total_projects}</strong></p>";
    
    if ($total_projects > 0) {
        $sample_data_sql = "SELECT id, project_id, project_name, type, status, start_date, end_date, total_days 
                            FROM projects LIMIT 3";
        $sample_data = $pdo->query($sample_data_sql)->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h4>Sample Projects:</h4>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Project ID</th><th>Project Name</th><th>Type</th><th>Status</th><th>Start Date</th><th>End Date</th><th>Total Days</th></tr>";
        
        foreach ($sample_data as $project) {
            echo "<tr>";
            echo "<td>{$project['id']}</td>";
            echo "<td>" . htmlspecialchars($project['project_id']) . "</td>";
            echo "<td>" . htmlspecialchars($project['project_name'] ?? '-') . "</td>";
            echo "<td>" . htmlspecialchars($project['type'] ?? '-') . "</td>";
            echo "<td>" . htmlspecialchars($project['status'] ?? '-') . "</td>";
            echo "<td>" . ($project['start_date'] ? date('Y-m-d', strtotime($project['start_date'])) : '-') . "</td>";
            echo "<td>" . ($project['end_date'] ? date('Y-m-d', strtotime($project['end_date'])) : '-') . "</td>";
            echo "<td>" . ($project['total_days'] ?? '-') . "</td>";
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
