<?php
// Set timezone ke Asia/Jakarta agar tanggal default mengikuti WIB
date_default_timezone_set('Asia/Jakarta');

$host = 'localhost';
$db   = 'ultimate_website';
$user = 'postgres'; // Ganti dengan user PostgreSQL Anda jika berbeda
$pass = 'password'; // Ganti dengan password PostgreSQL Anda
$dsn = "pgsql:host=$host;dbname=$db";
try {
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
