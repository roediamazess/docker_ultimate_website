<?php
// test_email_fixed.php - Test email dengan konfigurasi yang diperbaiki
require_once 'email_config.php';

echo "📧 Email Test dengan Konfigurasi yang Diperbaiki\n";
echo "===============================================\n\n";

// Test konfigurasi
if (!testEmailConfig()) {
    echo "\n❌ Konfigurasi email belum lengkap!\n";
    exit;
}

echo "\n✅ Konfigurasi email sudah benar!\n";
echo "🚀 Sistem email siap digunakan\n\n";

// Test kirim email dengan konfigurasi yang diperbaiki
echo "📤 Testing email sending dengan SSL bypass...\n";
require_once 'send_email.php';

$test_email = 'rudiantoap@gmail.com';
$test_token = 'test_token_' . time();
$test_name = 'Rudianto';

echo "Sending test email to: {$test_email}\n";
echo "Using SMTP: " . SMTP_HOST . ":" . SMTP_PORT . "\n";
echo "Username: " . SMTP_USERNAME . "\n";

if (sendPasswordResetEmail($test_email, $test_token, $test_name)) {
    echo "✅ Email sent successfully!\n";
    echo "📧 Check your email inbox for the password reset link\n";
    echo "🔗 Demo link: http://localhost/ultimate-website/reset-password.php?token={$test_token}\n";
} else {
    echo "❌ Failed to send email\n";
    echo "💡 Possible issues:\n";
    echo "   - Password bukan App Password Gmail\n";
    echo "   - 2-Step Verification belum aktif\n";
    echo "   - Email domain tidak support SMTP\n";
    echo "   - Firewall blocking port 587\n";
}

// Alternative: Test dengan Gmail yang benar
echo "\n\n🔧 Alternative: Test dengan Gmail yang benar\n";
echo "==========================================\n";

echo "Untuk menggunakan Gmail yang benar:\n";
echo "1. Aktifkan 2-Step Verification di https://myaccount.google.com/security\n";
echo "2. Generate App Password di https://myaccount.google.com/apppasswords\n";
echo "3. Update email_config.php dengan:\n";
echo "   define('SMTP_USERNAME', 'rudiantoap@gmail.com');\n";
echo "   define('SMTP_PASSWORD', 'your-app-password-here');\n";
echo "   define('SMTP_FROM_EMAIL', 'rudiantoap@gmail.com');\n";
?> 
