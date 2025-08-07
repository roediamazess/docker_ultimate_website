<?php
// Set timezone untuk UTC (database consistency)
date_default_timezone_set('UTC');

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
