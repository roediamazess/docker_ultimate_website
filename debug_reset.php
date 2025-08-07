<?php
// debug_reset.php - Debug reset password process
require_once 'db.php';

echo "🔍 Debug Reset Password Process\n";
echo "==============================\n\n";

// 1. Cek token yang diterima
$token = $_GET['token'] ?? 'NO_TOKEN_PROVIDED';
echo "1️⃣ Token yang diterima:\n";
echo "   Token: {$token}\n";
echo "   Length: " . strlen($token) . " characters\n\n";

// 2. Cek token di database
$sql = "SELECT id, email, display_name, reset_token, reset_expires FROM users WHERE reset_token = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$token]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

echo "2️⃣ Cek token di database:\n";
if ($user) {
    echo "   ✅ Token ditemukan di database\n";
    echo "   - ID: {$user['id']}\n";
    echo "   - Email: {$user['email']}\n";
    echo "   - Name: {$user['display_name']}\n";
    echo "   - Token: {$user['reset_token']}\n";
    echo "   - Expires: {$user['reset_expires']}\n";
    
    // Cek apakah token sudah expired
    $current_time = time();
    $expires_time = strtotime($user['reset_expires']);
    $is_expired = $expires_time <= $current_time;
    
    echo "   - Current Time: " . date('Y-m-d H:i:s', $current_time) . "\n";
    echo "   - Expires Time: " . date('Y-m-d H:i:s', $expires_time) . "\n";
    echo "   - Is Expired: " . ($is_expired ? "YES" : "NO") . "\n";
    
} else {
    echo "   ❌ Token TIDAK ditemukan di database\n";
}

echo "\n3️⃣ Cek semua token yang ada:\n";
$sql = "SELECT id, email, display_name, reset_token, reset_expires FROM users WHERE reset_token IS NOT NULL";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$all_tokens = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($all_tokens)) {
    echo "   ❌ Tidak ada token di database\n";
} else {
    foreach ($all_tokens as $token_data) {
        echo "   - Email: {$token_data['email']}\n";
        echo "   - Token: {$token_data['reset_token']}\n";
        echo "   - Expires: {$token_data['reset_expires']}\n";
        echo "   - Status: " . (strtotime($token_data['reset_expires']) > time() ? "VALID" : "EXPIRED") . "\n";
        echo "\n";
    }
}

echo "4️⃣ Generate test link:\n";
if ($user && !$is_expired) {
    $test_link = "http://localhost/ultimate-website/reset-password.php?token=" . $user['reset_token'];
    echo "   ✅ Test Link: {$test_link}\n";
} else {
    echo "   ❌ Tidak bisa generate test link (token tidak valid atau expired)\n";
}

echo "\n5️⃣ Troubleshooting:\n";
echo "   - Pastikan token di URL sama dengan token di database\n";
echo "   - Pastikan token belum expired\n";
echo "   - Pastikan tidak ada spasi atau karakter khusus di URL\n";
echo "   - Coba request reset password baru jika token expired\n";

?> 