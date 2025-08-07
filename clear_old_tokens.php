<?php
// Set timezone untuk Asia/Jakarta
date_default_timezone_set('Asia/Jakarta');

// clear_old_tokens.php - Bersihkan token lama dan request yang baru
require_once 'db.php';

echo "🧹 Clear Old Tokens & Request New One\n";
echo "====================================\n\n";

// 1. Clear semua token lama
echo "1️⃣ Clearing old tokens...\n";
$clear_sql = "UPDATE users SET reset_token = NULL, reset_expires = NULL WHERE reset_token IS NOT NULL";
$clear_stmt = $pdo->prepare($clear_sql);
$clear_stmt->execute();

echo "   ✅ All old tokens cleared\n\n";

// 2. Generate token baru untuk rudiantoap@gmail.com
echo "2️⃣ Generating new token for rudiantoap@gmail.com...\n";

// Cek apakah user ada
$check_sql = "SELECT id, email, display_name FROM users WHERE email = ?";
$check_stmt = $pdo->prepare($check_sql);
$check_stmt->execute(['rudiantoap@gmail.com']);
$user = $check_stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {
    // Generate reset token baru dengan timezone Asia/Jakarta
    $reset_token = bin2hex(random_bytes(32));
    $reset_expires = date('Y-m-d H:i:s', strtotime('+1 hour')); // Sekarang menggunakan Asia/Jakarta
    
    // Simpan token baru ke database
    $update_sql = "UPDATE users SET reset_token = ?, reset_expires = ? WHERE email = ?";
    $update_stmt = $pdo->prepare($update_sql);
    
    if ($update_stmt->execute([$reset_token, $reset_expires, 'rudiantoap@gmail.com'])) {
        echo "   ✅ New token generated successfully!\n";
        echo "   - Email: {$user['email']}\n";
        echo "   - Name: {$user['display_name']}\n";
        echo "   - Token: {$reset_token}\n";
        echo "   - Expires: {$reset_expires} (Asia/Jakarta time)\n";
        echo "   - Current Time: " . date('Y-m-d H:i:s') . " (Asia/Jakarta time)\n\n";
        
        // Generate link reset password
        $reset_link = "http://localhost/ultimate-website/reset-password.php?token=" . $reset_token;
        
        echo "3️⃣ Reset Password Link:\n";
        echo "=======================\n";
        echo $reset_link . "\n\n";
        
        echo "4️⃣ Instructions:\n";
        echo "================\n";
        echo "1. Copy link di atas\n";
        echo "2. Paste di browser\n";
        echo "3. Set password baru\n";
        echo "4. Login dengan password baru\n\n";
        
        echo "✅ Token baru sudah siap dengan timezone Asia/Jakarta!\n";
        
    } else {
        echo "   ❌ Failed to save new token\n";
    }
} else {
    echo "   ❌ User rudiantoap@gmail.com not found\n";
    echo "   💡 Please add user first\n";
}

echo "\n🎯 Timezone Info:\n";
echo "================\n";
echo "Current Timezone: " . date_default_timezone_get() . "\n";
echo "Current Time: " . date('Y-m-d H:i:s') . "\n";
echo "UTC Time: " . gmdate('Y-m-d H:i:s') . "\n";

?> 