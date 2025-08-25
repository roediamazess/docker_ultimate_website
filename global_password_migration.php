<?php
// Global password fixer: pastikan semua user memiliki password hash yang valid.
// - Jika kolom password kosong/NULL: set ke default (pps88) hashed
// - Jika kolom password plaintext (tidak diawali '$'): hash-kan in-place dengan nilai plaintext yang sama
// Jalankan sekali, hapus file setelah selesai.

require_once __DIR__ . '/db.php';

$default = 'pps88';
$fixed = 0; $setDefault = 0; $hashedPlain = 0; $errors = [];

try {
    $stmt = $pdo->query("SELECT user_id, email, password FROM users");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($users as $u) {
        $uid = $u['user_id'];
        $pwd = (string)($u['password'] ?? '');
        try {
            if ($pwd === '' || $pwd === null) {
                // kosong -> set default hashed
                $hash = password_hash($default, PASSWORD_DEFAULT);
                $up = $pdo->prepare('UPDATE users SET password = :pwd WHERE id = :uid');
                $up->execute(['pwd' => $hash, 'uid' => $uid]);
                $fixed++; $setDefault++;
            } elseif (strpos($pwd, '$') !== 0) {
                // plaintext -> hash-kan in-place ke hash yang sama nilainya
                $hash = password_hash($pwd, PASSWORD_DEFAULT);
                $up = $pdo->prepare('UPDATE users SET password = :pwd WHERE id = :uid');
                $up->execute(['pwd' => $hash, 'uid' => $uid]);
                $fixed++; $hashedPlain++;
            }
        } catch (Throwable $e) { $errors[] = $uid . ': ' . $e->getMessage(); }
    }
} catch (Throwable $e) { $errors[] = $e->getMessage(); }

@header('Content-Type: text/plain');
echo "Global password migration done\n";
echo "fixed: $fixed\n";
echo "set_default: $setDefault\n";
echo "hashed_plaintext: $hashedPlain\n";
if ($errors) { echo 'errors: ' . implode(' | ', $errors) . "\n"; }



