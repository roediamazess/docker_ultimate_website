<?php
session_start();
require_once 'db.php';

// Rate limiting: max 5 attempts per 10 minutes per IP
$ip = $_SERVER['REMOTE_ADDR'] ?? '';
$limit = 5;
$window = 10; // minutes
$stmt = $pdo->prepare('SELECT COUNT(*) FROM login_attempts WHERE ip = ? AND attempt_time > (NOW() - INTERVAL \'$window minutes\')');
$stmt->execute([$ip]);
$attempts = $stmt->fetchColumn();
if ($attempts >= $limit) {
    $_SESSION['login_error'] = 'Terlalu banyak percobaan login. Silakan coba lagi dalam 10 menit.';
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        // Catat attempt gagal
        $pdo->prepare('INSERT INTO login_attempts (ip) VALUES (?)')->execute([$ip]);
        $_SESSION['login_error'] = 'Email dan password wajib diisi.';
        header('Location: login.php');
        exit;
    }

    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        // Set session
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_display_name'] = $user['display_name'];
        // Hapus attempt login sukses dari IP ini (opsional, tidak wajib)
        $pdo->prepare('DELETE FROM login_attempts WHERE ip = ?')->execute([$ip]);
        // Redirect sesuai role
        header('Location: index.php');
        exit;
    } else {
        // Catat attempt gagal
        $pdo->prepare('INSERT INTO login_attempts (ip) VALUES (?)')->execute([$ip]);
        $_SESSION['login_error'] = 'Email atau password salah.';
        header('Location: login.php');
        exit;
    }
} else {
    header('Location: login.php');
    exit;
}
