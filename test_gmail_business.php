<?php
// test_gmail_business.php - Test Gmail for Business untuk pms@ppsolution.com
require_once 'email_config.php';

echo "📧 Gmail for Business Test untuk pms@ppsolution.com\n";
echo "==================================================\n\n";

echo "📧 Current Configuration:\n";
echo "   Host: " . SMTP_HOST . "\n";
echo "   Port: " . SMTP_PORT . "\n";
echo "   Username: " . SMTP_USERNAME . "\n";
echo "   From: " . SMTP_FROM_EMAIL . "\n";
echo "   From Name: " . SMTP_FROM_NAME . "\n";
echo "   Password: " . substr(SMTP_PASSWORD, 0, 4) . "****\n\n";

// Test connection
echo "🔗 Testing SMTP Connection...\n";
$connection = @fsockopen(SMTP_HOST, SMTP_PORT, $errno, $errstr, 10);

if ($connection) {
    echo "✅ Connection: SUCCESS\n";
    fclose($connection);
} else {
    echo "❌ Connection: FAILED ($errstr)\n";
}

// Test email configuration
if (!testEmailConfig()) {
    echo "\n❌ Konfigurasi email belum lengkap!\n";
    echo "💡 Update SMTP_PASSWORD dengan App Password dari Gmail for Business\n";
    exit;
}

echo "\n✅ Konfigurasi email sudah benar!\n";
echo "🚀 Sistem email siap digunakan\n\n";

// Test kirim email
echo "📤 Testing email sending...\n";
require_once 'send_email.php';

$test_email = 'rudiantoap@gmail.com';
$test_token = 'test_token_' . time();
$test_name = 'Rudianto';

echo "Sending test email to: {$test_email}\n";
echo "From: " . SMTP_FROM_EMAIL . "\n";

if (sendPasswordResetEmail($test_email, $test_token, $test_name)) {
    echo "✅ Email sent successfully!\n";
    echo "📧 Check your email inbox for the password reset link\n";
    echo "🔗 Demo link: http://localhost/ultimate-website/reset-password.php?token={$test_token}\n";
} else {
    echo "❌ Failed to send email\n";
    echo "💡 Possible issues:\n";
    echo "   - App Password belum diupdate\n";
    echo "   - 2-Step Verification belum aktif\n";
    echo "   - Gmail for Business belum setup dengan benar\n";
}

echo "\n🎯 Setup Checklist:\n";
echo "==================\n";
echo "✅ [ ] 2-Step Verification aktif di pms@ppsolution.com\n";
echo "✅ [ ] App Password generated untuk 'Ultimate Website'\n";
echo "✅ [ ] SMTP_PASSWORD diupdate dengan App Password\n";
echo "✅ [ ] Test email berhasil dikirim\n";

?> 