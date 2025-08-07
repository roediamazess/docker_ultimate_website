<?php
// Set timezone untuk UTC (database consistency)
date_default_timezone_set('UTC');

// debug_token_detailed.php - Debug token secara detail
require_once 'db.php';

// Function untuk convert UTC ke local time
function convertUTCToLocal($utc_time, $timezone = 'Asia/Jakarta') {
    $utc = new DateTime($utc_time, new DateTimeZone('UTC'));
    $local = $utc->setTimezone(new DateTimeZone($timezone));
    return $local->format('Y-m-d H:i:s');
}

echo "🔍 Detailed Token Debug\n";
echo "======================\n\n";

// 1. Cek semua token yang ada
echo "1️⃣ All tokens in database:\n";
$sql = "SELECT id, email, display_name, reset_token, reset_expires FROM users WHERE reset_token IS NOT NULL";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$all_tokens = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($all_tokens)) {
    echo "   ❌ No tokens found in database\n\n";
} else {
    foreach ($all_tokens as $token_data) {
        echo "   - ID: {$token_data['id']}\n";
        echo "   - Email: {$token_data['email']}\n";
        echo "   - Name: {$token_data['display_name']}\n";
        echo "   - Token: {$token_data['reset_token']}\n";
        echo "   - Expires (UTC): {$token_data['reset_expires']}\n";
        
        // Check if expired
        $current_time = time();
        $expires_time = strtotime($token_data['reset_expires']);
        $is_expired = $expires_time <= $current_time;
        
        echo "   - Current Time (UTC): " . date('Y-m-d H:i:s', $current_time) . "\n";
        echo "   - Expires Time (UTC): " . date('Y-m-d H:i:s', $expires_time) . "\n";
        echo "   - Is Expired: " . ($is_expired ? "YES" : "NO") . "\n";
        echo "   - Time Left: " . ($expires_time - $current_time) . " seconds\n";
        echo "\n";
    }
}

// 2. Test specific token from URL
$test_token = '22f31fc19a778583bd7da26683145eaf7108e947c0168960c7f3d50587d36d4b';
echo "2️⃣ Testing specific token: {$test_token}\n";

$sql = "SELECT id, email, display_name, reset_token, reset_expires FROM users WHERE reset_token = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$test_token]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {
    echo "   ✅ Token found in database\n";
    echo "   - Email: {$user['email']}\n";
    echo "   - Name: {$user['display_name']}\n";
    echo "   - Expires (UTC): {$user['reset_expires']}\n";
    
    // Check expiry
    $current_time = time();
    $expires_time = strtotime($user['reset_expires']);
    $is_expired = $expires_time <= $current_time;
    
    echo "   - Current Time (UTC): " . date('Y-m-d H:i:s', $current_time) . "\n";
    echo "   - Expires Time (UTC): " . date('Y-m-d H:i:s', $expires_time) . "\n";
    echo "   - Is Expired: " . ($is_expired ? "YES" : "NO") . "\n";
    echo "   - Time Left: " . ($expires_time - $current_time) . " seconds\n";
    
    if (!$is_expired) {
        echo "   ✅ Token is VALID\n";
    } else {
        echo "   ❌ Token is EXPIRED\n";
    }
} else {
    echo "   ❌ Token NOT found in database\n";
}

echo "\n3️⃣ Database query test:\n";
echo "   Testing: SELECT * FROM users WHERE reset_token = ? AND reset_expires > NOW()\n";

$sql = "SELECT id, email, display_name, reset_token, reset_expires FROM users WHERE reset_token = ? AND reset_expires > NOW()";
$stmt = $pdo->prepare($sql);
$stmt->execute([$test_token]);
$valid_user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($valid_user) {
    echo "   ✅ Query returns valid user\n";
} else {
    echo "   ❌ Query returns no results (token expired or invalid)\n";
}

echo "\n4️⃣ Generate fresh token:\n";
$fresh_token = bin2hex(random_bytes(32));
$fresh_expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

echo "   Fresh Token: {$fresh_token}\n";
echo "   Fresh Expires: {$fresh_expires}\n";

// Save fresh token
$update_sql = "UPDATE users SET reset_token = ?, reset_expires = ? WHERE email = ?";
$update_stmt = $pdo->prepare($update_sql);

if ($update_stmt->execute([$fresh_token, $fresh_expires, 'rudiantoap@gmail.com'])) {
    echo "   ✅ Fresh token saved successfully\n";
    
    echo "\n5️⃣ Fresh Reset Password Link:\n";
    echo "http://localhost/ultimate-website/reset-password.php?token={$fresh_token}\n";
} else {
    echo "   ❌ Failed to save fresh token\n";
}

echo "\n🎯 Current Time Info:\n";
echo "====================\n";
echo "Current Time (UTC): " . date('Y-m-d H:i:s') . "\n";
echo "Current Time (WIB): " . convertUTCToLocal(date('Y-m-d H:i:s'), 'Asia/Jakarta') . "\n";
echo "Current Time (WITA): " . convertUTCToLocal(date('Y-m-d H:i:s'), 'Asia/Makassar') . "\n";
echo "Current Time (WIT): " . convertUTCToLocal(date('Y-m-d H:i:s'), 'Asia/Jayapura') . "\n";

?> 