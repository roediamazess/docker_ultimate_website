<?php
// add_user_properly.php - Script untuk tambah user dengan enum yang benar
require_once 'db.php';

echo "➕ Adding user with correct enum values...\n\n";

try {
    // Data user baru
    $new_email = 'newuser@example.com';
    $new_password = 'newuser123';
    $new_name = 'New User';
    $new_role = 'User';  // Dengan U kapital
    $new_tier = 'New Born';
    
    // Cek apakah email sudah ada
    $check_sql = "SELECT id FROM users WHERE email = ?";
    $check_stmt = $pdo->prepare($check_sql);
    $check_stmt->execute([$new_email]);
    $existing_user = $check_stmt->fetch();
    
    if ($existing_user) {
        echo "⚠️  User with email {$new_email} already exists!\n";
        echo "🔍 Testing login with existing user...\n";
        
        // Test login
        $login_sql = "SELECT * FROM users WHERE email = ?";
        $login_stmt = $pdo->prepare($login_sql);
        $login_stmt->execute([$new_email]);
        $user = $login_stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            echo "✅ User found in database!\n";
            echo "📧 Email: {$user['email']}\n";
            echo "👤 Name: {$user['display_name']}\n";
            echo "🎭 Role: {$user['role']}\n";
            echo "🏆 Tier: {$user['tier']}\n";
            
            // Test password verification
            if (password_verify($new_password, $user['password'])) {
                echo "✅ Password verification successful!\n";
                echo "🎉 Login should work with:\n";
                echo "   Email: {$new_email}\n";
                echo "   Password: {$new_password}\n";
            } else {
                echo "❌ Password verification failed!\n";
                echo "💡 Try different password or reset password\n";
            }
        }
    } else {
        // Hash password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        
        // Insert user baru dengan enum yang benar
        $insert_sql = "INSERT INTO users (email, password, display_name, full_name, role, tier, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())";
        $insert_stmt = $pdo->prepare($insert_sql);
        
        if ($insert_stmt->execute([$new_email, $hashed_password, $new_name, $new_name, $new_role, $new_tier])) {
            echo "✅ New user added successfully!\n";
            echo "📧 Email: {$new_email}\n";
            echo "🔑 Password: {$new_password}\n";
            echo "👤 Name: {$new_name}\n";
            echo "🎭 Role: {$new_role}\n";
            echo "🏆 Tier: {$new_tier}\n";
            echo "\n🎉 You can now login with these credentials!\n";
        } else {
            echo "❌ Failed to add user!\n";
            print_r($insert_stmt->errorInfo());
        }
    }
    
    // Test login dengan admin yang sudah ada
    echo "\n🔐 Testing admin login:\n";
    $admin_email = 'admin@example.com';
    $admin_password = 'admin123';
    
    $admin_sql = "SELECT * FROM users WHERE email = ?";
    $admin_stmt = $pdo->prepare($admin_sql);
    $admin_stmt->execute([$admin_email]);
    $admin_user = $admin_stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($admin_user) {
        echo "✅ Admin user found: {$admin_user['email']}\n";
        if (password_verify($admin_password, $admin_user['password'])) {
            echo "✅ Admin password verification successful!\n";
            echo "🎉 Admin login should work with:\n";
            echo "   Email: {$admin_email}\n";
            echo "   Password: {$admin_password}\n";
        } else {
            echo "❌ Admin password verification failed!\n";
            echo "🔍 Stored hash: " . substr($admin_user['password'], 0, 20) . "...\n";
        }
    } else {
        echo "❌ Admin user not found!\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?> 
