<?php
// email_config_ppsolution.php - Konfigurasi email untuk ppsolution.com
// Konfigurasi khusus untuk domain ppsolution.com

// PPSolution SMTP Configuration
define('SMTP_HOST', 'smtp.ppsolution.com'); // Sesuaikan dengan provider email
define('SMTP_PORT', 587); // Atau 465 untuk SSL, 25 untuk non-SSL
define('SMTP_USERNAME', 'pms@ppsolution.com'); // Email administrator
define('SMTP_PASSWORD', 'Pass@998877'); // Password email
define('SMTP_FROM_EMAIL', 'pms@ppsolution.com'); // Email pengirim
define('SMTP_FROM_NAME', 'Ultimate Website - PPSolution'); // Nama pengirim

// Opsi SMTP yang umum digunakan untuk domain custom:
// 1. smtp.ppsolution.com (jika ada SMTP server sendiri)
// 2. smtp.gmail.com (jika menggunakan Gmail for Business)
// 3. smtp.office365.com (jika menggunakan Microsoft 365)
// 4. smtp.zoho.com (jika menggunakan Zoho Mail)
// 5. smtp.yandex.com (jika menggunakan Yandex)

// Test email configuration
function testEmailConfig() {
    echo "📧 Testing email configuration untuk ppsolution.com...\n\n";
    
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
