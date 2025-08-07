<?php
// email_config.php - Konfigurasi email untuk sistem
// Update file ini dengan kredensial email Anda

// Gmail SMTP Configuration
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'pms@ppsolution.com'); // Gmail for Business
define('SMTP_PASSWORD', 'koyf msqo qrcb qqwh'); // App Password dari Gmail for Business
define('SMTP_FROM_EMAIL', 'pms@ppsolution.com'); // Email pengirim
define('SMTP_FROM_NAME', 'Access Validation'); // Nama pengirim

// Cara mendapatkan App Password Gmail:
// 1. Buka Google Account Settings
// 2. Security > 2-Step Verification (aktifkan dulu)
// 3. Security > App passwords
// 4. Generate password untuk "Mail"
// 5. Copy password yang dihasilkan ke SMTP_PASSWORD

// Contoh konfigurasi yang sudah diisi:
// define('SMTP_USERNAME', 'rudiantoap@gmail.com');
// define('SMTP_PASSWORD', 'abcd efgh ijkl mnop'); // App Password dari Google
// define('SMTP_FROM_EMAIL', 'rudiantoap@gmail.com');

// Test email configuration
function testEmailConfig() {
    echo "📧 Testing email configuration...\n\n";
    
    if (SMTP_USERNAME === 'your-email@gmail.com') {
        echo "❌ Email belum dikonfigurasi!\n";
        echo "💡 Update file email_config.php dengan kredensial email Anda\n";
        echo "📝 Contoh:\n";
        echo "   SMTP_USERNAME = 'rudiantoap@gmail.com'\n";
        echo "   SMTP_PASSWORD = 'your-app-password'\n";
        return false;
    }
    
    echo "✅ Email configuration loaded:\n";
    echo "   Host: " . SMTP_HOST . "\n";
    echo "   Port: " . SMTP_PORT . "\n";
    echo "   Username: " . SMTP_USERNAME . "\n";
    echo "   From: " . SMTP_FROM_EMAIL . "\n";
    
    return true;
}

// Uncomment baris di bawah untuk test konfigurasi
// testEmailConfig();
?> 