<?php
// email_config_ppsolution_fixed.php - Konfigurasi email yang benar untuk ppsolution.com
// Menggunakan Gmail for Business untuk domain custom

// Gmail for Business SMTP Configuration untuk ppsolution.com
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'pms@ppsolution.com'); // Email administrator
define('SMTP_PASSWORD', 'your-app-password-here'); // App Password dari Google
define('SMTP_FROM_EMAIL', 'pms@ppsolution.com'); // Email pengirim
define('SMTP_FROM_NAME', 'Ultimate Website - PPSolution'); // Nama pengirim

// Cara setup Gmail for Business untuk ppsolution.com:
// 1. Buka Google Workspace Admin Console
// 2. Tambahkan domain ppsolution.com
// 3. Setup email routing untuk pms@ppsolution.com
// 4. Aktifkan 2-Step Verification
// 5. Generate App Password

// Alternative: Microsoft 365 Configuration
// define('SMTP_HOST', 'smtp.office365.com');
// define('SMTP_PORT', 587);
// define('SMTP_USERNAME', 'pms@ppsolution.com');
// define('SMTP_PASSWORD', 'your-email-password');
// define('SMTP_FROM_EMAIL', 'pms@ppsolution.com');

// Alternative: Zoho Mail Configuration
// define('SMTP_HOST', 'smtp.zoho.com');
// define('SMTP_PORT', 587);
// define('SMTP_USERNAME', 'pms@ppsolution.com');
// define('SMTP_PASSWORD', 'your-app-password');
// define('SMTP_FROM_EMAIL', 'pms@ppsolution.com');

// Test email configuration
function testEmailConfig() {
    echo "📧 Testing email configuration untuk ppsolution.com...\n\n";
    
    if (SMTP_PASSWORD === 'your-app-password-here') {
        echo "❌ App Password belum dikonfigurasi!\n";
        echo "💡 Update SMTP_PASSWORD dengan App Password dari Google\n";
        echo "📝 Contoh: define('SMTP_PASSWORD', 'abcd efgh ijkl mnop');\n";
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