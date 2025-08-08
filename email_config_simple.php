<?php
// email_config_simple.php - Konfigurasi email sederhana untuk testing
// Gunakan file ini untuk testing dengan Gmail yang benar

// Gmail SMTP Configuration untuk testing
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'rudiantoap@gmail.com'); // Email Gmail untuk testing
define('SMTP_PASSWORD', 'your-app-password-here'); // Ganti dengan App Password Gmail
define('SMTP_FROM_EMAIL', 'rudiantoap@gmail.com'); // Email pengirim
define('SMTP_FROM_NAME', 'Ultimate Website'); // Nama pengirim

// Cara mendapatkan App Password Gmail:
// 1. Buka https://myaccount.google.com/security
// 2. Aktifkan 2-Step Verification
// 3. Buka https://myaccount.google.com/apppasswords
// 4. Pilih "Mail" dan "Other (Custom name)"
// 5. Ketik "Ultimate Website" dan klik Generate
// 6. Copy password yang dihasilkan (format: abcd efgh ijkl mnop)

// Test email configuration
function testEmailConfig() {
    echo "📧 Testing email configuration...\n\n";
    
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
    
    return true;
}

// Uncomment baris di bawah untuk test konfigurasi
// testEmailConfig();
?> 
