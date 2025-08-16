<?php
require_once 'db.php';

echo "<h2>🔒 Menambahkan Unique Constraint pada Project ID</h2>";

try {
    // Detect database driver
    $driver = '';
    try { 
        $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME); 
    } catch (Throwable $e) {}
    
    echo "<p>Database driver: <strong>$driver</strong></p>";
    
    // Check if projects table exists
    $check_table = "SELECT EXISTS (SELECT FROM information_schema.tables WHERE table_name = 'projects')";
    $table_exists = $pdo->query($check_table)->fetchColumn();
    
    if (!$table_exists) {
        echo "<p style='color: red;'>❌ Tabel projects tidak ditemukan!</p>";
        exit;
    }
    
    echo "<p style='color: green;'>✅ Tabel projects ditemukan</p>";
    
    // Check current constraints on project_id column
    echo "<h3>🔍 Memeriksa Constraint yang Ada:</h3>";
    
    if ($driver === 'pgsql') {
        // PostgreSQL
        $constraints_sql = "SELECT conname, contype, pg_get_constraintdef(oid) as definition 
                           FROM pg_constraint 
                           WHERE conrelid = 'projects'::regclass 
                           AND contype = 'u'";
    } else {
        // MySQL
        $constraints_sql = "SELECT CONSTRAINT_NAME as conname, 'u' as contype, 
                           CONSTRAINT_TYPE as definition
                           FROM information_schema.TABLE_CONSTRAINTS 
                           WHERE TABLE_NAME = 'projects' 
                           AND CONSTRAINT_TYPE = 'UNIQUE'";
    }
    
    try {
        $constraints = $pdo->query($constraints_sql)->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($constraints)) {
            echo "<p>Tidak ada unique constraint yang ditemukan</p>";
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
    } catch (Exception $e) {
        echo "<p style='color: orange;'>⚠️ Tidak dapat memeriksa constraint: " . $e->getMessage() . "</p>";
    }
    
    // Check if project_id column exists and its current properties
    echo "<h3>📋 Memeriksa Kolom Project ID:</h3>";
    
    $column_sql = "SELECT column_name, data_type, is_nullable, column_default, 
                   CASE WHEN is_nullable = 'NO' THEN 'NOT NULL' ELSE 'NULL' END as nullable_status
                   FROM information_schema.columns 
                   WHERE table_name = 'projects' AND column_name = 'project_id'";
    
    $column_info = $pdo->query($column_sql)->fetch(PDO::FETCH_ASSOC);
    
    if (!$column_info) {
        echo "<p style='color: red;'>❌ Kolom project_id tidak ditemukan!</p>";
        exit;
    }
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Property</th><th>Value</th></tr>";
    echo "<tr><td>Column Name</td><td>{$column_info['column_name']}</td></tr>";
    echo "<tr><td>Data Type</td><td>{$column_info['data_type']}</td></tr>";
    echo "<tr><td>Nullable</td><td>{$column_info['nullable_status']}</td></tr>";
    echo "<tr><td>Default</td><td>{$column_info['column_default'] ?: 'None'}</td></tr>";
    echo "</table>";
    
    // Check for duplicate project_id values before adding constraint
    echo "<h3>🔍 Memeriksa Duplikasi Project ID:</h3>";
    
    $duplicates_sql = "SELECT project_id, COUNT(*) as count 
                       FROM projects 
                       GROUP BY project_id 
                       HAVING COUNT(*) > 1 
                       ORDER BY count DESC";
    
    $duplicates = $pdo->query($duplicates_sql)->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($duplicates)) {
        echo "<p style='color: green;'>✅ Tidak ada duplikasi project_id yang ditemukan</p>";
    } else {
        echo "<p style='color: red;'>❌ Ditemukan duplikasi project_id:</p>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>Project ID</th><th>Count</th></tr>";
        foreach ($duplicates as $duplicate) {
            echo "<tr>";
            echo "<td>{$duplicate['project_id']}</td>";
            echo "<td>{$duplicate['count']}</td>";
            echo "</tr>";
        }
        echo "</table>";
        echo "<p style='color: red;'>⚠️ Harap hapus atau perbaiki duplikasi sebelum menambahkan unique constraint!</p>";
        exit;
    }
    
    // Add unique constraint
    echo "<h3>🔒 Menambahkan Unique Constraint:</h3>";
    
    try {
        if ($driver === 'pgsql') {
            // PostgreSQL
            $pdo->exec("ALTER TABLE projects ADD CONSTRAINT projects_project_id_unique UNIQUE (project_id)");
        } else {
            // MySQL
            $pdo->exec("ALTER TABLE projects ADD UNIQUE KEY projects_project_id_unique (project_id)");
        }
        
        echo "<p style='color: green;'>✅ Unique constraint berhasil ditambahkan!</p>";
        
        // Verify the constraint was added
        echo "<h3>✅ Verifikasi Constraint:</h3>";
        
        if ($driver === 'pgsql') {
            $verify_sql = "SELECT conname, contype, pg_get_constraintdef(oid) as definition 
                          FROM pg_constraint 
                          WHERE conrelid = 'projects'::regclass 
                          AND contype = 'u'";
        } else {
            $verify_sql = "SELECT CONSTRAINT_NAME as conname, 'u' as contype, 
                          CONSTRAINT_TYPE as definition
                          FROM information_schema.TABLE_CONSTRAINTS 
                          WHERE TABLE_NAME = 'projects' 
                          AND CONSTRAINT_TYPE = 'UNIQUE'";
        }
        
        $new_constraints = $pdo->query($verify_sql)->fetchAll(PDO::FETCH_ASSOC);
        
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
        
        echo "<h3>🎉 Selesai!</h3>";
        echo "<p style='color: green; font-weight: bold;'>Project ID sekarang memiliki unique constraint dan tidak dapat diduplikasi!</p>";
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Gagal menambahkan unique constraint: " . $e->getMessage() . "</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?>

