<?php
// test_email_debug.php - Debug email configuration
require_once 'email_config.php';

echo "🔍 Email Configuration Debug\n";
echo "============================\n\n";

echo "📧 Current Configuration:\n";
echo "   Host: " . SMTP_HOST . "\n";
echo "   Port: " . SMTP_PORT . "\n";
echo "   Username: " . SMTP_USERNAME . "\n";
echo "   From: " . SMTP_FROM_EMAIL . "\n";
echo "   Password: " . substr(SMTP_PASSWORD, 0, 4) . "****\n\n";

// Test 1: Current configuration
echo "🧪 Test 1: Current Configuration (pms@ppsolution.com)\n";
echo "==================================================\n";

require_once 'send_email.php';

$test_email = 'rudiantoap@gmail.com';
$test_token = 'test_token_' . time();
$test_name = 'Rudianto';

echo "Sending test email to: {$test_email}\n";

if (sendPasswordResetEmail($test_email, $test_token, $test_name)) {
    echo "✅ Email sent successfully!\n";
} else {
    echo "❌ Failed to send email\n";
    echo "💡 Issue: pms@ppsolution.com tidak bisa menggunakan Gmail App Password\n\n";
}

// Test 2: Gmail configuration
echo "\n🧪 Test 2: Gmail Configuration (rudiantoap@gmail.com)\n";
echo "====================================================\n";

// Temporary Gmail config
$original_username = SMTP_USERNAME;
$original_from = SMTP_FROM_EMAIL;

// Override for test
define('SMTP_USERNAME_TEST', 'rudiantoap@gmail.com');
define('SMTP_FROM_EMAIL_TEST', 'rudiantoap@gmail.com');

echo "Testing with Gmail account: " . SMTP_USERNAME_TEST . "\n";

// Create temporary send function
function sendPasswordResetEmailTest($email, $token, $user_name) {
    require_once 'db.php';
    require 'vendor/autoload.php';
    
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\SMTP;
    use PHPMailer\PHPMailer\Exception;
    
    try {
        $mail = new PHPMailer(true);
        
        // Server settings
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USERNAME_TEST;
        $mail->Password   = SMTP_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = SMTP_PORT;
        $mail->CharSet    = 'UTF-8';
        
        // SSL/TLS settings untuk development
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
        
        // Recipients
        $mail->setFrom(SMTP_FROM_EMAIL_TEST, SMTP_FROM_NAME);
        $mail->addAddress($email, $user_name);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Reset Password - Ultimate Website';
        
        // Handle both web and CLI environments
        $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
        $script_name = isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : 'ultimate-website';
        $base_path = dirname($script_name);
        
        $reset_link = "http://" . $host . $base_path . "/reset-password.php?token=" . $token;
        
        $mail->Body = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <div style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0;'>
                <h1 style='margin: 0; font-size: 28px;'>Ultimate Website</h1>
                <p style='margin: 10px 0 0 0; opacity: 0.9;'>Reset Password Request</p>
            </div>
            
            <div style='background: white; padding: 40px; border-radius: 0 0 10px 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);'>
                <h2 style='color: #333; margin-bottom: 20px;'>Halo {$user_name}!</h2>
                
                <p style='color: #666; line-height: 1.6; margin-bottom: 30px;'>
                    Kami menerima permintaan untuk reset password akun Anda. 
                    Jika Anda tidak melakukan permintaan ini, abaikan email ini.
                </p>
                
                <div style='text-align: center; margin: 40px 0;'>
                    <a href='{$reset_link}' style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 15px 30px; text-decoration: none; border-radius: 25px; display: inline-block; font-weight: bold;'>
                        Reset Password
                    </a>
                </div>
                
                <p style='color: #666; font-size: 14px; margin-bottom: 20px;'>
                    Atau copy link berikut ke browser Anda:
                </p>
                
                <p style='background: #f8f9fa; padding: 15px; border-radius: 5px; word-break: break-all; color: #667eea; font-size: 14px;'>
                    {$reset_link}
                </p>
                
                <div style='margin-top: 40px; padding-top: 20px; border-top: 1px solid #eee;'>
                    <p style='color: #999; font-size: 12px; margin: 0;'>
                        <strong>⚠️ Keamanan:</strong> Link ini berlaku selama 1 jam. 
                        Jangan bagikan link ini kepada siapapun.
                    </p>
                </div>
            </div>
            
            <div style='text-align: center; margin-top: 20px; color: #999; font-size: 12px;'>
                <p>© 2024 Ultimate Website. All rights reserved.</p>
                <p>Jika ada pertanyaan, hubungi support kami.</p>
            </div>
        </div>";
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        echo "Email sending failed: " . $mail->ErrorInfo . "\n";
        return false;
    }
}

if (sendPasswordResetEmailTest($test_email, $test_token, $test_name)) {
    echo "✅ Email sent successfully with Gmail!\n";
    echo "💡 Solution: Gunakan Gmail account untuk SMTP\n";
} else {
    echo "❌ Failed to send email with Gmail\n";
}

echo "\n🎯 Rekomendasi:\n";
echo "==============\n";
echo "1. Gunakan Gmail account (rudiantoap@gmail.com) untuk SMTP\n";
echo "2. Atau setup Gmail for Business untuk pms@ppsolution.com\n";
echo "3. Atau gunakan provider email lain yang support custom domain\n";

?> 
