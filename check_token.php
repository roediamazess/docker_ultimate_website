<?php
// Set timezone untuk UTC (database consistency)
date_default_timezone_set('UTC');

// check_token.php - Cek token reset password di database
require_once 'db.php';

// Function untuk convert UTC ke local time
function convertUTCToLocal($utc_time, $timezone = 'Asia/Jakarta') {
    $utc = new DateTime($utc_time, new DateTimeZone('UTC'));
    $local = $utc->setTimezone(new DateTimeZone($timezone));
    return $local->format('Y-m-d H:i:s');
}

echo "🔍 Checking Reset Password Tokens\n";
echo "================================\n\n";

// Cek semua token yang ada
$sql = "SELECT id, email, display_name, reset_token, reset_expires FROM users WHERE reset_token IS NOT NULL";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$tokens = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($tokens)) {
    echo "❌ Tidak ada token reset password di database\n";
    echo "💡 Token dari test email tidak tersimpan karena hanya test\n\n";
} else {
    echo "✅ Tokens found in database:\n";
    foreach ($tokens as $token) {
        echo "   - ID: {$token['id']}\n";
        echo "   - Email: {$token['email']}\n";
        echo "   - Name: {$token['display_name']}\n";
        echo "   - Token: {$token['reset_token']}\n";
        echo "   - Expires (UTC): {$token['reset_expires']}\n";
        echo "   - Expires (WIB): " . convertUTCToLocal($token['reset_expires'], 'Asia/Jakarta') . "\n";
        echo "   - Expires (WITA): " . convertUTCToLocal($token['reset_expires'], 'Asia/Makassar') . "\n";
        echo "   - Expires (WIT): " . convertUTCToLocal($token['reset_expires'], 'Asia/Jayapura') . "\n";
        echo "   - Status: " . (strtotime($token['reset_expires']) > time() ? "VALID" : "EXPIRED") . "\n";
        echo "\n";
    }
}

// Cek token test yang baru saja dibuat
$test_token = 'test_token_' . time();
echo "🔍 Test Token Info:\n";
echo "   Current Time (UTC): " . date('Y-m-d H:i:s') . "\n";
echo "   Current Time (WIB): " . convertUTCToLocal(date('Y-m-d H:i:s'), 'Asia/Jakarta') . "\n";
echo "   Current Time (WITA): " . convertUTCToLocal(date('Y-m-d H:i:s'), 'Asia/Makassar') . "\n";
echo "   Current Time (WIT): " . convertUTCToLocal(date('Y-m-d H:i:s'), 'Asia/Jayapura') . "\n";
echo "   Test Token: {$test_token}\n";
echo "   Token from last test: test_token_1754569759\n\n";

// Cek apakah ada user dengan email rudiantoap@gmail.com
$sql = "SELECT id, email, display_name, reset_token, reset_expires FROM users WHERE email = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute(['rudiantoap@gmail.com']);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {
    echo "✅ User found:\n";
    echo "   - ID: {$user['id']}\n";
    echo "   - Email: {$user['email']}\n";
    echo "   - Name: {$user['display_name']}\n";
    echo "   - Reset Token: " . ($user['reset_token'] ?: 'NULL') . "\n";
    echo "   - Reset Expires (UTC): " . ($user['reset_expires'] ?: 'NULL') . "\n";
    if ($user['reset_expires']) {
        echo "   - Reset Expires (WIB): " . convertUTCToLocal($user['reset_expires'], 'Asia/Jakarta') . "\n";
        echo "   - Reset Expires (WITA): " . convertUTCToLocal($user['reset_expires'], 'Asia/Makassar') . "\n";
        echo "   - Reset Expires (WIT): " . convertUTCToLocal($user['reset_expires'], 'Asia/Jayapura') . "\n";
    }
} else {
    echo "❌ User rudiantoap@gmail.com tidak ditemukan di database\n";
    echo "💡 Perlu tambahkan user ini ke database terlebih dahulu\n";
}

echo "\n🎯 Solution:\n";
echo "============\n";
echo "1. Pastikan user rudiantoap@gmail.com ada di database\n";
echo "2. Request reset password melalui forgot-password.php\n";
echo "3. Token akan tersimpan di database dengan benar\n";
echo "4. Link reset password akan berfungsi\n";

?> 