<?php
// One-off script: backfill missing user passwords with a default value
// SECURITY: Jalankan HANYA di lokal/dev. Hapus file ini setelah selesai.

require_once __DIR__ . '/db.php';

// Parse CLI args like key=value
if (PHP_SAPI === 'cli' && !empty($argv)) {
    foreach (array_slice($argv, 1) as $arg) {
        if (strpos($arg, '=') !== false) {
            [$k, $v] = explode('=', $arg, 2);
            $_REQUEST[$k] = $v;
        }
    }
}

$defaultPassword = isset($_REQUEST['password']) ? (string)$_REQUEST['password'] : 'pps88';
$hash = password_hash($defaultPassword, PASSWORD_DEFAULT);

$updated = 0;
$byEmail = isset($_REQUEST['email']) ? trim($_REQUEST['email']) : '';
$force = isset($_REQUEST['force']) && (string)$_REQUEST['force'] !== '' ? true : false;

try {
    if ($byEmail !== '') {
        if ($force) {
            $stmt = $pdo->prepare("UPDATE users SET password = :pwd WHERE LOWER(email) = LOWER(:email)");
        } else {
            $stmt = $pdo->prepare("UPDATE users SET password = :pwd WHERE LOWER(email) = LOWER(:email) AND (password IS NULL OR password = '')");
        }
        $stmt->execute(['pwd' => $hash, 'email' => $byEmail]);
        $updated = $stmt->rowCount();
    } else {
        if ($force) {
            // Force set ALL users to the default hash
            $stmt = $pdo->prepare("UPDATE users SET password = :pwd");
            $stmt->execute(['pwd' => $hash]);
            $updated = $stmt->rowCount();
        } else {
            // Backfill hanya yang kosong/NULL
            $stmt = $pdo->prepare("UPDATE users SET password = :pwd WHERE password IS NULL OR password = ''");
            $stmt->execute(['pwd' => $hash]);
            $updated = $stmt->rowCount();
        }
    }
} catch (Throwable $e) {
    @header('Content-Type: text/plain');
    echo 'Error: ' . $e->getMessage();
    exit;
}

@header('Content-Type: text/plain');
echo 'Backfill done. Updated rows: ' . (int)$updated . "\n";
if ($byEmail) { echo 'Target email: ' . $byEmail . "\n"; }


