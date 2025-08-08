<?php
// Set timezone untuk UTC (database consistency)
date_default_timezone_set('UTC');

// generate_correct_link.php - Generate link yang benar berdasarkan token di database
require_once 'db.php';

// Function untuk convert UTC ke local time
function convertUTCToLocal($utc_time, $timezone = 'Asia/Jakarta') {
    $utc = new DateTime($utc_time, new DateTimeZone('UTC'));
    $local = $utc->setTimezone(new DateTimeZone($timezone));
    return $local->format('Y-m-d H:i:s');
}

echo "🔗 Generate Correct Reset Password Link\n";
echo "======================================\n\n";

// Ambil token yang valid dari database
$sql = "SELECT id, email, display_name, reset_token, reset_expires FROM users WHERE email = ? AND reset_token IS NOT NULL AND reset_expires > NOW()";
$stmt = $pdo->prepare($sql);
$stmt->execute(['rudiantoap@gmail.com']);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {
    echo "✅ Valid token found in database:\n";
    echo "   - Email: {$user['email']}\n";
    echo "   - Name: {$user['display_name']}\n";
    echo "   - Token: {$user['reset_token']}\n";
    echo "   - Expires (UTC): {$user['reset_expires']}\n";
    echo "   - Expires (WIB): " . convertUTCToLocal($user['reset_expires'], 'Asia/Jakarta') . "\n";
    echo "   - Expires (WITA): " . convertUTCToLocal($user['reset_expires'], 'Asia/Makassar') . "\n";
    echo "   - Expires (WIT): " . convertUTCToLocal($user['reset_expires'], 'Asia/Jayapura') . "\n\n";
    
    // Generate link reset password yang benar
    $reset_link = "http://localhost/ultimate-website/reset-password.php?token=" . $user['reset_token'];
    
    echo "🔗 Correct Reset Password Link:\n";
    echo "===============================\n";
    echo $reset_link . "\n\n";
    
    echo "📋 Instructions:\n";
    echo "================\n";
    echo "1. Copy link di atas\n";
    echo "2. Paste di browser\n";
    echo "3. Set password baru\n";
    echo "4. Login dengan password baru\n\n";
    
    echo "✅ Token masih valid dan siap digunakan!\n";
    
} else {
    echo "❌ Tidak ada token valid untuk rudiantoap@gmail.com\n";
    echo "💡 Request reset password baru melalui forgot-password.php\n";
}

echo "\n🎯 Current Time Info:\n";
echo "====================\n";
echo "Current Time (UTC): " . date('Y-m-d H:i:s') . "\n";
echo "Current Time (WIB): " . convertUTCToLocal(date('Y-m-d H:i:s'), 'Asia/Jakarta') . "\n";
echo "Current Time (WITA): " . convertUTCToLocal(date('Y-m-d H:i:s'), 'Asia/Makassar') . "\n";
echo "Current Time (WIT): " . convertUTCToLocal(date('Y-m-d H:i:s'), 'Asia/Jayapura') . "\n";

?> 
