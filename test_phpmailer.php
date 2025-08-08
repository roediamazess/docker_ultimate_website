<?php
echo "=== Testing PHPMailer Installation ===\n";

// Test autoload
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
    echo "✅ Autoload file found\n";
} else {
    echo "❌ Autoload file not found\n";
    exit(1);
}

// Test PHPMailer class
try {
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    echo "✅ PHPMailer class loaded successfully\n";
    echo "✅ PHPMailer version: " . PHPMailer\PHPMailer\PHPMailer::VERSION . "\n";
    
    // Test send_email function
    require_once 'send_email.php';
    echo "✅ send_email.php loaded successfully\n";
    echo "🎉 PHPMailer is ready to use!\n";
    
} catch (Exception $e) {
    echo "❌ Error loading PHPMailer: " . $e->getMessage() . "\n";
}

echo "\n=== Installation Complete ===\n";
echo "You can now use email features in the website!\n";
?>
