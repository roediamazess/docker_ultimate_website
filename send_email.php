<?php
// Konfigurasi SMTP dan fungsi send_email menggunakan PHPMailer
// Pastikan sudah install: composer require phpmailer/phpmailer

define('VENDOR_AUTOLOAD', __DIR__ . '/vendor/autoload.php');
if (file_exists(VENDOR_AUTOLOAD)) {
    require VENDOR_AUTOLOAD;
} else {
    die('PHPMailer belum terinstall. Jalankan: composer require phpmailer/phpmailer');
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function send_email($to, $subject, $body, $altBody = '') {
    $mail = new PHPMailer(true);
    try {
        // Konfigurasi SMTP
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // Ganti dengan SMTP server Anda
        $mail->SMTPAuth = true;
        $mail->Username = 'your_email@gmail.com'; // Ganti dengan email Anda
        $mail->Password = 'your_password'; // Ganti dengan password/email app password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('your_email@gmail.com', 'Ultimate Website');
        $mail->addAddress($to);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->AltBody = $altBody ?: strip_tags($body);

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('Email gagal: ' . $mail->ErrorInfo);
        return false;
    }
}
