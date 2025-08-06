<?php
require_once 'db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

function log_activity($action, $description = '') {
    global $pdo;
    $user_id = $_SESSION['user_id'] ?? null;
    $user_email = $_SESSION['user_email'] ?? null;
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $stmt = $pdo->prepare('INSERT INTO logs (user_id, user_email, action, description, ip, user_agent, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())');
    $stmt->execute([$user_id, $user_email, $action, $description, $ip, $ua]);
}
