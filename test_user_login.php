<?php
// test_user_login.php - Test login dengan email user yang diupdate
require_once 'db.php';

echo "🔐 Testing login dengan email user yang diupdate...\n\n";

try {
    // Test dengan email user yang baru
    $user_email = 'rudiantoap@gmail.com';
    $test_password = 'admin123'; // Coba dengan password default
    
    echo "📧 Testing login dengan email: {$user_email}\n";
    
    // Cek apakah user ada
    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        echo "✅ User ditemukan!\n";
        echo "👤 Display Name: {$user['display_name']}\n";
        echo "🎭 Role: {$user['role']}\n";
        echo "🏆 Tier: {$user['tier']}\n";
        echo "📅 Created: {$user['created_at']}\n";
        
        // Test password verification
        if (password_verify($test_password, $user['password'])) {
            echo "✅ Password verification berhasil!\n";
            echo "🎉 Login berhasil dengan:\n";
            echo "   Email: {$user_email}\n";
            echo "   Password: {$test_password}\n";
        } else {
            echo "❌ Password verification gagal!\n";
            echo "💡 Password yang dicoba: {$test_password}\n";
            echo "🔍 Stored hash: " . substr($user['password'], 0, 20) . "...\n";
            
            // Coba password lain yang mungkin
            $possible_passwords = ['password', '123456', 'user123', 'rudianto', 'admin'];
            echo "\n🔄 Mencoba password lain...\n";
            
            foreach ($possible_passwords as $pwd) {
                if (password_verify($pwd, $user['password'])) {
                    echo "✅ Password ditemukan: {$pwd}\n";
                    echo "🎉 Login berhasil dengan:\n";
                    echo "   Email: {$user_email}\n";
                    echo "   Password: {$pwd}\n";
                    break;
                }
            }
        }
    } else {
        echo "❌ User tidak ditemukan dengan email: {$user_email}\n";
    }
    
    // Test juga dengan admin default
    echo "\n🔐 Testing admin login:\n";
    $admin_email = 'admin@example.com';
    $admin_password = 'admin123';
    
    $admin_sql = "SELECT * FROM users WHERE email = ?";
    $admin_stmt = $pdo->prepare($admin_sql);
    $admin_stmt->execute([$admin_email]);
    $admin_user = $admin_stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($admin_user) {
        echo "✅ Admin user ditemukan: {$admin_user['email']}\n";
        if (password_verify($admin_password, $admin_user['password'])) {
            echo "✅ Admin password verification berhasil!\n";
            echo "🎉 Admin login berhasil dengan:\n";
            echo "   Email: {$admin_email}\n";
            echo "   Password: {$admin_password}\n";
        } else {
            echo "❌ Admin password verification gagal!\n";
        }
    } else {
        echo "❌ Admin user tidak ditemukan!\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?> 