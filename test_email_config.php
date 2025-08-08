<?php
// test_email_config.php - Test konfigurasi email
require_once 'email_config.php';

echo "📧 Email Configuration Test\n";
echo "==========================\n\n";

// Test konfigurasi
if (!testEmailConfig()) {
    echo "\n❌ Konfigurasi email belum lengkap!\n";
    echo "📝 Langkah-langkah untuk setup email:\n\n";
    
    echo "1️⃣ Aktifkan 2-Step Verification di Google Account:\n";
    echo "   - Buka https://myaccount.google.com/security\n";
    echo "   - Aktifkan 2-Step Verification\n\n";
    
    echo "2️⃣ Generate App Password:\n";
    echo "   - Buka https://myaccount.google.com/apppasswords\n";
    echo "   - Pilih 'Mail' dan device 'Other'\n";
    echo "   - Copy password yang dihasilkan\n\n";
    
    echo "3️⃣ Update email_config.php:\n";
    echo "   - Ganti SMTP_USERNAME dengan email Gmail Anda\n";
    echo "   - Ganti SMTP_PASSWORD dengan App Password\n";
    echo "   - Ganti SMTP_FROM_EMAIL dengan email Gmail Anda\n\n";
    
    echo "4️⃣ Contoh konfigurasi:\n";
    echo "   define('SMTP_USERNAME', 'rudiantoap@gmail.com');\n";
    echo "   define('SMTP_PASSWORD', 'abcd efgh ijkl mnop');\n";
    echo "   define('SMTP_FROM_EMAIL', 'rudiantoap@gmail.com');\n\n";
    
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

if (sendPasswordResetEmail($test_email, $test_token, $test_name)) {
    echo "✅ Email sent successfully!\n";
    echo "📧 Check your email inbox for the password reset link\n";
    echo "🔗 Demo link: http://localhost/ultimate-website/reset-password.php?token={$test_token}\n";
} else {
    echo "❌ Failed to send email\n";
    echo "💡 Check error logs or email configuration\n";
}
?> 
