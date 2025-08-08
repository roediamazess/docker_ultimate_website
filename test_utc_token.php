<?php
// Set timezone untuk UTC (database consistency)
date_default_timezone_set('UTC');

// test_utc_token.php - Test generate token dengan sistem UTC
require_once 'db.php';

// Function untuk convert UTC ke local time
function convertUTCToLocal($utc_time, $timezone = 'Asia/Jakarta') {
    $utc = new DateTime($utc_time, new DateTimeZone('UTC'));
    $local = $utc->setTimezone(new DateTimeZone($timezone));
    return $local->format('Y-m-d H:i:s');
}

echo "🌏 Test UTC Token System\n";
echo "========================\n\n";

// Generate token baru dengan UTC
$reset_token = bin2hex(random_bytes(32));
$reset_expires_utc = date('Y-m-d H:i:s', strtotime('+1 hour')); // UTC time

echo "1️⃣ Token Info:\n";
echo "   Token: {$reset_token}\n";
echo "   Expires (UTC): {$reset_expires_utc}\n";
echo "   Expires (WIB): " . convertUTCToLocal($reset_expires_utc, 'Asia/Jakarta') . "\n";
echo "   Expires (WITA): " . convertUTCToLocal($reset_expires_utc, 'Asia/Makassar') . "\n";
echo "   Expires (WIT): " . convertUTCToLocal($reset_expires_utc, 'Asia/Jayapura') . "\n\n";

// Simpan ke database
$update_sql = "UPDATE users SET reset_token = ?, reset_expires = ? WHERE email = ?";
$update_stmt = $pdo->prepare($update_sql);

if ($update_stmt->execute([$reset_token, $reset_expires_utc, 'rudiantoap@gmail.com'])) {
    echo "2️⃣ Token saved to database successfully!\n\n";
    
    echo "3️⃣ Reset Password Link:\n";
    echo "=======================\n";
    echo "http://localhost/ultimate-website/reset-password.php?token={$reset_token}\n\n";
    
    echo "4️⃣ Timezone Comparison:\n";
    echo "=======================\n";
    echo "Current Time (UTC): " . date('Y-m-d H:i:s') . "\n";
    echo "Current Time (WIB): " . convertUTCToLocal(date('Y-m-d H:i:s'), 'Asia/Jakarta') . "\n";
    echo "Current Time (WITA): " . convertUTCToLocal(date('Y-m-d H:i:s'), 'Asia/Makassar') . "\n";
    echo "Current Time (WIT): " . convertUTCToLocal(date('Y-m-d H:i:s'), 'Asia/Jayapura') . "\n\n";
    
    echo "5️⃣ Benefits:\n";
    echo "=============\n";
    echo "✅ Token berlaku sama untuk semua timezone\n";
    echo "✅ User dari Jakarta, Bali, Papua bisa menggunakan token yang sama\n";
    echo "✅ Tidak ada konflik timezone di database\n";
    echo "✅ Display waktu sesuai timezone user\n";
    
} else {
    echo "❌ Failed to save token\n";
}

?> 
