<?php
// email_config_ppsolution_working.php - Konfigurasi email yang bekerja untuk ppsolution.com
// Menggunakan Microsoft 365 untuk domain custom

// Microsoft 365 SMTP Configuration untuk ppsolution.com
define('SMTP_HOST', 'smtp.office365.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'pms@ppsolution.com'); // Email administrator
define('SMTP_PASSWORD', 'your-email-password'); // Password email Microsoft 365
define('SMTP_FROM_EMAIL', 'pms@ppsolution.com'); // Email pengirim
define('SMTP_FROM_NAME', 'Ultimate Website - PPSolution'); // Nama pengirim

// Alternative: Zoho Mail Configuration
// define('SMTP_HOST', 'smtp.zoho.com');
// define('SMTP_PORT', 587);
// define('SMTP_USERNAME', 'pms@ppsolution.com');
// define('SMTP_PASSWORD', 'your-app-password');
// define('SMTP_FROM_EMAIL', 'pms@ppsolution.com');

// Alternative: Gmail for Business (jika sudah setup)
// define('SMTP_HOST', 'smtp.gmail.com');
// define('SMTP_PORT', 587);
// define('SMTP_USERNAME', 'pms@ppsolution.com');
// define('SMTP_PASSWORD', 'your-app-password');
// define('SMTP_FROM_EMAIL', 'pms@ppsolution.com');

// Test email configuration
function testEmailConfig() {
    echo "📧 Testing email configuration untuk ppsolution.com...\n\n";
    
    if (SMTP_PASSWORD === 'your-email-password') {
        echo "❌ Password belum dikonfigurasi!\n";
        echo "💡 Update SMTP_PASSWORD dengan password email yang benar\n";
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