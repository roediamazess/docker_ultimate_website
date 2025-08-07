<?php
// test_reset_link.php - Generate link reset password yang benar
require_once 'db.php';

echo "🔗 Generate Reset Password Link\n";
echo "==============================\n\n";

// Ambil token yang valid dari database
$sql = "SELECT id, email, display_name, reset_token, reset_expires FROM users WHERE email = ? AND reset_token IS NOT NULL AND reset_expires > NOW()";
$stmt = $pdo->prepare($sql);
$stmt->execute(['rudiantoap@gmail.com']);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {
    echo "✅ User found with valid token:\n";
    echo "   - Email: {$user['email']}\n";
    echo "   - Name: {$user['display_name']}\n";
    echo "   - Token: {$user['reset_token']}\n";
    echo "   - Expires: {$user['reset_expires']}\n\n";
    
    // Generate link reset password yang benar
    $reset_link = "http://localhost/ultimate-website/reset-password.php?token=" . $user['reset_token'];
    
    echo "🔗 Reset Password Link:\n";
    echo "======================\n";
    echo $reset_link . "\n\n";
    
    echo "📋 Instructions:\n";
    echo "================\n";
    echo "1. Copy link di atas\n";
    echo "2. Paste di browser\n";
    echo "3. Set password baru\n";
    echo "4. Login dengan password baru\n";
    
} else {
    echo "❌ Tidak ada token valid untuk rudiantoap@gmail.com\n";
    echo "💡 Request reset password baru melalui forgot-password.php\n";
}

?> 