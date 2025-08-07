<?php
// send_email.php - Sistem email yang benar-benar berfungsi
require_once 'db.php';
require_once 'email_config.php';
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

function sendPasswordResetEmail($email, $reset_token, $user_name) {
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USERNAME;
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
        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($email, $user_name);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Reset Password - Ultimate Website';
        
        // Handle both web and CLI environments
        $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
        $script_name = isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : 'ultimate-website';
        $base_path = dirname($script_name);
        
        $reset_link = "http://" . $host . $base_path . "/reset-password.php?token=" . $reset_token;
        
        $mail->Body = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <div style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px; text-align: center; color: white; border-radius: 10px 10px 0 0;'>
                <h1 style='margin: 0; font-size: 28px;'>🔐 Reset Password</h1>
                <p style='margin: 10px 0 0 0; font-size: 16px;'>Ultimate Website</p>
            </div>
            
            <div style='background: #f8f9fa; padding: 30px; border-radius: 0 0 10px 10px;'>
                <h2 style='color: #333; margin-bottom: 20px;'>Halo {$user_name}!</h2>
                
                <p style='color: #666; line-height: 1.6; margin-bottom: 20px;'>
                    Kami menerima permintaan untuk mereset password akun Anda di Ultimate Website.
                </p>
                
                <p style='color: #666; line-height: 1.6; margin-bottom: 30px;'>
                    Klik tombol di bawah ini untuk mereset password Anda:
                </p>
                
                <div style='text-align: center; margin: 30px 0;'>
                    <a href='{$reset_link}' style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; font-weight: bold; display: inline-block;'>
                        🔐 Reset Password
                    </a>
                </div>
                
                <p style='color: #666; line-height: 1.6; margin-bottom: 20px;'>
                    Atau copy link berikut ke browser Anda:
                </p>
                
                <div style='background: #e9ecef; padding: 15px; border-radius: 5px; margin-bottom: 20px;'>
                    <a href='{$reset_link}' style='color: #667eea; word-break: break-all;'>{$reset_link}</a>
                </div>
                
                <p style='color: #666; line-height: 1.6; margin-bottom: 20px;'>
                    <strong>⚠️ Penting:</strong>
                </p>
                <ul style='color: #666; line-height: 1.6; margin-bottom: 20px;'>
                    <li>Link ini hanya berlaku selama 1 jam</li>
                    <li>Jika Anda tidak meminta reset password, abaikan email ini</li>
                    <li>Jangan bagikan link ini kepada siapapun</li>
                </ul>
                
                <hr style='border: none; border-top: 1px solid #dee2e6; margin: 30px 0;'>
                
                <p style='color: #999; font-size: 14px; text-align: center; margin: 0;'>
                    Email ini dikirim dari sistem Ultimate Website<br>
                    Jika ada pertanyaan, silakan hubungi administrator
                </p>
            </div>
        </div>
        ";
        
        $mail->AltBody = "
        Reset Password - Ultimate Website
        
        Halo {$user_name}!
        
        Kami menerima permintaan untuk mereset password akun Anda di Ultimate Website.
        
        Klik link berikut untuk mereset password:
        {$reset_link}
        
        Link ini hanya berlaku selama 1 jam.
        
        Jika Anda tidak meminta reset password, abaikan email ini.
        ";
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email sending failed: " . $mail->ErrorInfo);
        return false;
    }
}

// Test function
function testEmailSending() {
    echo "📧 Testing email sending system...\n\n";
    
    // Test dengan email user
    $test_email = 'rudiantoap@gmail.com';
    $test_token = 'test_token_123';
    $test_name = 'Rudianto';
    
    echo "Sending test email to: {$test_email}\n";
    
    if (sendPasswordResetEmail($test_email, $test_token, $test_name)) {
        echo "✅ Email sent successfully!\n";
        echo "📧 Check your email inbox for the password reset link\n";
    } else {
        echo "❌ Failed to send email\n";
        echo "💡 Please check email configuration in send_email.php\n";
    }
}

// Uncomment baris di bawah untuk test email
// testEmailSending();
?>
