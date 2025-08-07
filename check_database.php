<?php
// check_database.php - Script untuk cek database dan tambah user
require_once 'db.php';

echo "🔍 Checking database structure and users...\n\n";

try {
    // Cek struktur tabel users
    echo "📋 Checking users table structure:\n";
    $sql = "SELECT column_name, data_type, is_nullable FROM information_schema.columns WHERE table_name = 'users' ORDER BY ordinal_position";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($columns as $column) {
        echo "  - {$column['column_name']} ({$column['data_type']}) - Nullable: {$column['is_nullable']}\n";
    }
    
    echo "\n👥 Current users in database:\n";
    $sql = "SELECT id, email, display_name, role, created_at FROM users ORDER BY id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($users)) {
        echo "  ❌ No users found in database!\n";
    } else {
        foreach ($users as $user) {
            echo "  - ID: {$user['id']} | Email: {$user['email']} | Name: {$user['display_name']} | Role: {$user['role']} | Created: {$user['created_at']}\n";
        }
    }
    
    // Coba tambah user test
    echo "\n➕ Adding test user...\n";
    
    $test_email = 'test@example.com';
    $test_password = 'test123';
    $test_name = 'Test User';
    $test_role = 'user';
    
    // Cek apakah email sudah ada
    $check_sql = "SELECT id FROM users WHERE email = ?";
    $check_stmt = $pdo->prepare($check_sql);
    $check_stmt->execute([$test_email]);
    $existing_user = $check_stmt->fetch();
    
    if ($existing_user) {
        echo "  ⚠️  User with email {$test_email} already exists!\n";
    } else {
        // Hash password
        $hashed_password = password_hash($test_password, PASSWORD_DEFAULT);
        
        // Insert user baru
        $insert_sql = "INSERT INTO users (email, password, display_name, role, created_at) VALUES (?, ?, ?, ?, NOW())";
        $insert_stmt = $pdo->prepare($insert_sql);
        
        if ($insert_stmt->execute([$test_email, $hashed_password, $test_name, $test_role])) {
            echo "  ✅ Test user added successfully!\n";
            echo "  📧 Email: {$test_email}\n";
            echo "  🔑 Password: {$test_password}\n";
            echo "  👤 Name: {$test_name}\n";
            echo "  🎭 Role: {$test_role}\n";
        } else {
            echo "  ❌ Failed to add test user!\n";
            print_r($insert_stmt->errorInfo());
        }
    }
    
    // Test login dengan user yang ada
    echo "\n🔐 Testing login functionality:\n";
    $test_login_email = 'admin@example.com';
    $test_login_password = 'admin123';
    
    $login_sql = "SELECT * FROM users WHERE email = ?";
    $login_stmt = $pdo->prepare($login_sql);
    $login_stmt->execute([$test_login_email]);
    $login_user = $login_stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($login_user) {
        echo "  ✅ User found: {$login_user['email']}\n";
        if (password_verify($test_login_password, $login_user['password'])) {
            echo "  ✅ Password verification successful!\n";
        } else {
            echo "  ❌ Password verification failed!\n";
            echo "  🔍 Stored hash: " . substr($login_user['password'], 0, 20) . "...\n";
        }
    } else {
        echo "  ❌ User not found: {$test_login_email}\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?> 