<?php
// test_email_simple.php - Simple email test
require_once 'email_config.php';

echo "🔍 Simple Email Test\n";
echo "===================\n\n";

echo "📧 Current Configuration:\n";
echo "   Host: " . SMTP_HOST . "\n";
echo "   Port: " . SMTP_PORT . "\n";
echo "   Username: " . SMTP_USERNAME . "\n";
echo "   From: " . SMTP_FROM_EMAIL . "\n";
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

echo "\n🎯 Analysis:\n";
echo "===========\n";

if (strpos(SMTP_USERNAME, '@gmail.com') !== false) {
    echo "✅ Username: Gmail account detected\n";
    echo "✅ Password: App Password format detected\n";
    echo "💡 This should work with Gmail SMTP\n";
} else {
    echo "⚠️ Username: Custom domain detected (" . SMTP_USERNAME . ")\n";
    echo "❌ Issue: Custom domain cannot use Gmail App Password\n";
    echo "💡 Solution: Use Gmail account or setup proper SMTP for custom domain\n";
}

echo "\n🔧 Quick Fix Options:\n";
echo "===================\n";
echo "1. Use Gmail account: rudiantoap@gmail.com\n";
echo "2. Setup Gmail for Business for pms@ppsolution.com\n";
echo "3. Use different email provider (Microsoft 365, Zoho)\n";

?> 