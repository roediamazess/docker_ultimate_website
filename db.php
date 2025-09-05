<?php
// Set timezone ke Asia/Jakarta agar tanggal default mengikuti WIB
date_default_timezone_set('Asia/Jakarta');

// Load environment variables from .env file
function loadEnv($path) {
    if (!file_exists($path)) {
        return;
    }
    
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
        }
    }
}

loadEnv(__DIR__ . '/.env');

// Get database configuration from environment variables
// For VPS deployment, use direct configuration
$host = $_ENV['DB_HOST'] ?? 'localhost';
$port = $_ENV['DB_PORT'] ?? '5432';
$db   = $_ENV['DB_DATABASE'] ?? 'ultimate_website';
$user = $_ENV['DB_USERNAME'] ?? 'postgres';
$pass = $_ENV['DB_PASSWORD'] ?? 'password';

// Fallback to direct configuration if .env is not available
if (empty($_ENV['DB_HOST'])) {
    $host = 'localhost';
    $port = '5432';
    $db   = 'ultimate_website';
    $user = 'postgres';
    $pass = 'password';
}

$dsn = "pgsql:host=$host;port=$port;dbname=$db";
try {
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
