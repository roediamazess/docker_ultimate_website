<?php
require_once 'db.php';

echo "<pre>";

try {
    $email_to_fix = 'admin@test.com';
    $correct_role = 'Administrator';

    echo "Mencari user dengan email: $email_to_fix...\n";

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email_to_fix]);
    $user = $stmt->fetch();

    if ($user) {
        echo "User ditemukan. Role saat ini: " . ($user['role'] ?: '[KOSONG]')."\n";
        if ($user['role'] === $correct_role) {
            echo "Role sudah benar. Tidak ada yang perlu diubah.\n";
        } else {
            echo "Role salah. Memperbaiki menjadi '$correct_role'...
";
            $update_stmt = $pdo->prepare("UPDATE users SET role = ? WHERE email = ?");
            $update_stmt->execute([$correct_role, $email_to_fix]);
            if ($update_stmt->rowCount() > 0) {
                echo "BERHASIL: Role untuk $email_to_fix telah diperbaiki.\n";
            } else {
                echo "GAGAL: Tidak ada baris yang terupdate. Mungkin ada masalah lain.\n";
            }
        }
    } else {
        echo "User dengan email $email_to_fix tidak ditemukan.\n";
    }

} catch (PDOException $e) {
    die("Koneksi atau query database gagal: " . $e->getMessage());
}

echo "</pre>";
?>