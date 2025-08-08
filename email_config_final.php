<?php
// email_config_final.php - Konfigurasi email final untuk pms@ppsolution.com
// Email Administrator: pms@ppsolution.com (untuk kirim email)
// Email User: rudiantoap@gmail.com (untuk terima email)

// PPSolution SMTP Configuration
define('SMTP_HOST', 'smtp.office365.com'); // Microsoft 365 untuk domain custom
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'pms@ppsolution.com'); // Email administrator
define('SMTP_PASSWORD', 'your-email-password'); // Password email Microsoft 365
define('SMTP_FROM_EMAIL', 'pms@ppsolution.com'); // Email pengirim
define('SMTP_FROM_NAME', 'Ultimate Website - PPSolution'); // Nama pengirim

// Alternative configurations jika Microsoft 365 tidak tersedia:

// Option 1: Zoho Mail
// define('SMTP_HOST', 'smtp.zoho.com');
// define('SMTP_PORT', 587);
// define('SMTP_USERNAME', 'pms@ppsolution.com');
// define('SMTP_PASSWORD', 'your-app-password');
// define('SMTP_FROM_EMAIL', 'pms@ppsolution.com');

// Option 2: Gmail for Business (setelah setup domain)
// define('SMTP_HOST', 'smtp.gmail.com');
// define('SMTP_PORT', 587);
// define('SMTP_USERNAME', 'pms@ppsolution.com');
// define('SMTP_PASSWORD', 'your-app-password');
// define('SMTP_FROM_EMAIL', 'pms@ppsolution.com');

// Option 3: Custom SMTP Server
// define('SMTP_HOST', 'smtp.ppsolution.com');
// define('SMTP_PORT', 587);
// define('SMTP_USERNAME', 'pms@ppsolution.com');
// define('SMTP_PASSWORD', 'your-email-password');
// define('SMTP_FROM_EMAIL', 'pms@ppsolution.com');

// Test email configuration
function testEmailConfig() {
    echo "📧 Testing email configuration untuk pms@ppsolution.com...\n\n";
    
    if (SMTP_PASSWORD === 'your-email-password') {
        echo "❌ Password belum dikonfigurasi!\n";
        echo "💡 Update SMTP_PASSWORD dengan password email yang benar\n";
        echo "📝 Contoh:\n";
        echo "   SMTP_PASSWORD = 'password-email-anda'\n";
        return false;
    }
    
    echo "✅ Email configuration loaded:\n";
    echo "   Host: " . SMTP_HOST . "\n";
    echo "   Port: " . SMTP_PORT . "\n";
    echo "   Username: " . SMTP_USERNAME . "\n";
    echo "   From: " . SMTP_FROM_EMAIL . "\n";
    echo "   From Name: " . SMTP_FROM_NAME . "\n";
    
    return true;
}

// Uncomment baris di bawah untuk test konfigurasi
// testEmailConfig();
?> 
