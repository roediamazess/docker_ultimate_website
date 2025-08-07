<?php
// test_ppsolution_smtp.php - Test berbagai konfigurasi SMTP untuk ppsolution.com
require_once 'email_config.php';

echo "🔧 SMTP Configuration Test untuk ppsolution.com\n";
echo "==============================================\n\n";

// Daftar konfigurasi SMTP yang umum digunakan
$smtp_configs = [
    [
        'name' => 'PPSolution Custom SMTP',
        'host' => 'smtp.ppsolution.com',
        'port' => 587,
        'secure' => 'tls'
    ],
    [
        'name' => 'Gmail for Business',
        'host' => 'smtp.gmail.com',
        'port' => 587,
        'secure' => 'tls'
    ],
    [
        'name' => 'Microsoft 365',
        'host' => 'smtp.office365.com',
        'port' => 587,
        'secure' => 'tls'
    ],
    [
        'name' => 'Zoho Mail',
        'host' => 'smtp.zoho.com',
        'port' => 587,
        'secure' => 'tls'
    ],
    [
        'name' => 'Yandex Mail',
        'host' => 'smtp.yandex.com',
        'port' => 587,
        'secure' => 'tls'
    ],
    [
        'name' => 'PPSolution SSL',
        'host' => 'smtp.ppsolution.com',
        'port' => 465,
        'secure' => 'ssl'
    ],
    [
        'name' => 'PPSolution Non-SSL',
        'host' => 'smtp.ppsolution.com',
        'port' => 25,
        'secure' => 'none'
    ]
];

echo "📧 Testing berbagai konfigurasi SMTP...\n\n";

foreach ($smtp_configs as $config) {
    echo "🔍 Testing: {$config['name']}\n";
    echo "   Host: {$config['host']}:{$config['port']}\n";
    echo "   Security: {$config['secure']}\n";
    
    // Test connection
    $connection = @fsockopen($config['host'], $config['port'], $errno, $errstr, 10);
    
    if ($connection) {
        echo "   ✅ Connection: SUCCESS\n";
        fclose($connection);
    } else {
        echo "   ❌ Connection: FAILED ($errstr)\n";
    }
    
    echo "\n";
}

echo "🎯 Rekomendasi Konfigurasi:\n";
echo "==========================\n\n";

echo "1️⃣ **Jika menggunakan hosting sendiri**:\n";
echo "   - Host: smtp.ppsolution.com\n";
echo "   - Port: 587 (TLS) atau 465 (SSL)\n";
echo "   - Hubungi provider hosting untuk konfirmasi\n\n";

echo "2️⃣ **Jika menggunakan Gmail for Business**:\n";
echo "   - Host: smtp.gmail.com\n";
echo "   - Port: 587\n";
echo "   - Aktifkan 2-Step Verification dan App Password\n\n";

echo "3️⃣ **Jika menggunakan Microsoft 365**:\n";
echo "   - Host: smtp.office365.com\n";
echo "   - Port: 587\n";
echo "   - Gunakan password email biasa\n\n";

echo "4️⃣ **Jika menggunakan Zoho Mail**:\n";
echo "   - Host: smtp.zoho.com\n";
echo "   - Port: 587\n";
echo "   - Aktifkan App Password di Zoho\n\n";

echo "🔧 Langkah Selanjutnya:\n";
echo "=====================\n";
echo "1. Pilih konfigurasi yang sesuai dengan provider email Anda\n";
echo "2. Update email_config.php dengan konfigurasi yang benar\n";
echo "3. Test dengan script: php test_email_fixed.php\n";
echo "4. Jika masih gagal, hubungi provider email untuk konfirmasi SMTP settings\n";

?> 