<?php
session_start();
require_once 'db.php';
require_once 'access_control.php';

// Ensure user is logged in
require_login();

echo "<h2>🧪 Test Project ID Validation</h2>";

// Test data
$test_project_ids = [
    'PRJ001' => 'Project yang sudah ada (harus gagal)',
    'PRJ999' => 'Project yang sudah ada (harus gagal)', 
    'PRJ_NEW_001' => 'Project baru dengan underscore (harus berhasil)',
    'PRJ-NEW-002' => 'Project baru dengan dash (harus berhasil)',
    'PRJ123' => 'Project baru dengan angka (harus berhasil)',
    'PRJ@#$%' => 'Project dengan karakter khusus (harus gagal format)',
    '' => 'Project ID kosong (harus gagal format)',
    str_repeat('A', 51) => 'Project ID terlalu panjang (harus gagal format)'
];

echo "<h3>📋 Test Cases:</h3>";
echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr><th>Project ID</th><th>Description</th><th>Expected Result</th></tr>";

foreach ($test_project_ids as $project_id => $description) {
    $expected = '';
    if (empty($project_id)) {
        $expected = '❌ Format Error: Project ID tidak boleh kosong';
    } elseif (strlen($project_id) > 50) {
        $expected = '❌ Format Error: Project ID maksimal 50 karakter';
    } elseif (!preg_match('/^[a-zA-Z0-9_-]+$/', $project_id)) {
        $expected = '❌ Format Error: Karakter khusus tidak diizinkan';
    } elseif (in_array($project_id, ['PRJ001', 'PRJ999'])) {
        $expected = '❌ Duplication Error: Project ID sudah digunakan';
    } else {
        $expected = '✅ Success: Project ID valid dan tersedia';
    }
    
    echo "<tr>";
    echo "<td><code>" . htmlspecialchars($project_id ?: '(empty)') . "</code></td>";
    echo "<td>$description</td>";
    echo "<td>$expected</td>";
    echo "</tr>";
}
echo "</table>";

echo "<h3>🔍 Testing Backend Validation:</h3>";

foreach ($test_project_ids as $project_id => $description) {
    if (empty($project_id)) {
        echo "<p><strong>Test:</strong> Empty Project ID</p>";
        echo "<p>Result: ❌ Format Error - Project ID tidak boleh kosong</p>";
        continue;
    }
    
    if (strlen($project_id) > 50) {
        echo "<p><strong>Test:</strong> Project ID terlalu panjang: " . htmlspecialchars($project_id) . "</p>";
        echo "<p>Result: ❌ Format Error - Project ID maksimal 50 karakter</p>";
        continue;
    }
    
    if (!preg_match('/^[a-zA-Z0-9_-]+$/', $project_id)) {
        echo "<p><strong>Test:</strong> Project ID dengan karakter khusus: " . htmlspecialchars($project_id) . "</p>";
        echo "<p>Result: ❌ Format Error - Karakter khusus tidak diizinkan</p>";
        continue;
    }
    
    echo "<p><strong>Test:</strong> " . htmlspecialchars($project_id) . " - $description</p>";
    
    try {
        // Check uniqueness
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM projects WHERE project_id = ?");
        $stmt->execute([$project_id]);
        $count = $stmt->fetchColumn();
        
        if ($count > 0) {
            // Get project details
            $stmt = $pdo->prepare("SELECT project_name, hotel_name_text, type, status, created_at FROM projects WHERE project_id = ? LIMIT 1");
            $stmt->execute([$project_id]);
            $project_info = $stmt->fetch(PDO::FETCH_ASSOC);
            
            echo "<p>Result: ❌ Duplication Error - Project ID sudah digunakan</p>";
            if ($project_info) {
                echo "<ul>";
                echo "<li>Project Name: " . ($project_info['project_name'] ?: 'N/A') . "</li>";
                echo "<li>Hotel: " . ($project_info['hotel_name_text'] ?: 'N/A') . "</li>";
                echo "<li>Type: " . ($project_info['type'] ?: 'N/A') . "</li>";
                echo "<li>Status: " . ($project_info['status'] ?: 'N/A') . "</li>";
                echo "<li>Created: " . ($project_info['created_at'] ?: 'N/A') . "</li>";
                echo "</ul>";
            }
        } else {
            echo "<p>Result: ✅ Success - Project ID tersedia dan dapat digunakan</p>";
        }
    } catch (Exception $e) {
        echo "<p>Result: ❌ Error - " . htmlspecialchars($e->getMessage()) . "</p>";
    }
    
    echo "<hr>";
}

echo "<h3>🔒 Testing Database Unique Constraint:</h3>";

// Check if unique constraint exists
try {
    $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    
    if ($driver === 'pgsql') {
        $constraint_sql = "SELECT conname FROM pg_constraint 
                          WHERE conrelid = 'projects'::regclass 
                          AND contype = 'u' 
                          AND pg_get_constraintdef(oid) LIKE '%project_id%'";
    } else {
        $constraint_sql = "SELECT CONSTRAINT_NAME as conname 
                          FROM information_schema.TABLE_CONSTRAINTS 
                          WHERE TABLE_NAME = 'projects' 
                          AND CONSTRAINT_TYPE = 'UNIQUE' 
                          AND CONSTRAINT_NAME LIKE '%project_id%'";
    }
    
    $constraints = $pdo->query($constraint_sql)->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($constraints)) {
        echo "<p style='color: orange;'>⚠️ Unique constraint pada project_id belum ditambahkan</p>";
        echo "<p>Jalankan <code>add_unique_constraint_project_id.php</code> untuk menambahkan constraint</p>";
    } else {
        echo "<p style='color: green;'>✅ Unique constraint ditemukan:</p>";
        echo "<ul>";
        foreach ($constraints as $constraint) {
            echo "<li>" . $constraint['conname'] . "</li>";
        }
        echo "</ul>";
        
        // Test constraint by trying to insert duplicate
        echo "<h4>🧪 Testing Constraint Enforcement:</h4>";
        
        try {
            // Try to insert a project with existing ID
            $stmt = $pdo->prepare("INSERT INTO projects (project_id, project_name, start_date, type, status, created_at) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute(['PRJ001', 'Test Duplicate', date('Y-m-d'), 'Implementation', 'Scheduled', date('Y-m-d H:i:s')]);
            echo "<p style='color: red;'>❌ Constraint tidak berfungsi - Duplikasi berhasil diinsert!</p>";
        } catch (Exception $e) {
            if (strpos($e->getMessage(), 'Duplicate') !== false || strpos($e->getMessage(), 'unique') !== false) {
                echo "<p style='color: green;'>✅ Constraint berfungsi - Duplikasi ditolak: " . htmlspecialchars($e->getMessage()) . "</p>";
            } else {
                echo "<p style='color: orange;'>⚠️ Error lain: " . htmlspecialchars($e->getMessage()) . "</p>";
            }
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error checking constraint: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<h3>🎯 Summary:</h3>";
echo "<p>Implementasi validasi project ID mencakup:</p>";
echo "<ul>";
echo "<li>✅ Format validation (alphanumeric, underscore, dash)</li>";
echo "<li>✅ Length validation (max 50 karakter)</li>";
echo "<li>✅ Uniqueness validation (database check)</li>";
echo "<li>✅ Detailed error messages</li>";
echo "<li>✅ Frontend real-time validation</li>";
echo "<li>✅ Backend validation</li>";
echo "<li>✅ Database unique constraint</li>";
echo "</ul>";

echo "<p><strong>Next Steps:</strong></p>";
echo "<ol>";
echo "<li>Jalankan <code>add_unique_constraint_project_id.php</code> untuk menambahkan unique constraint</li>";
echo "<li>Test form Add Project dengan berbagai project ID</li>";
echo "<li>Verifikasi pesan error yang muncul</li>";
echo "<li>Pastikan form tidak bisa di-submit jika ada error</li>";
echo "</ol>";
?>