<?php
require_once 'db.php';

echo "<h2>Check User: admin@example.com</h2>";

// Cek apakah user ada
$email = 'admin@example.com';
$sql = "SELECT * FROM users WHERE email = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {
    echo "<p>✅ User ditemukan:</p>";
    echo "<ul>";
    echo "<li>ID: " . $user['id'] . "</li>";
    echo "<li>Email: " . $user['email'] . "</li>";
    echo "<li>Display Name: " . $user['display_name'] . "</li>";
    echo "<li>Role: " . $user['role'] . "</li>";
    echo "<li>Password Hash: " . substr($user['password'], 0, 20) . "...</li>";
    echo "</ul>";
    
    // Test password
    $test_password = 'admin123';
    if (password_verify($test_password, $user['password'])) {
        echo "<p>✅ Password 'admin123' BENAR!</p>";
    } else {
        echo "<p>❌ Password 'admin123' SALAH!</p>";
        echo "<p>Mencoba memperbaiki password...</p>";
        
        // Update password
        $new_password_hash = password_hash($test_password, PASSWORD_DEFAULT);
        $update_sql = "UPDATE users SET password = ? WHERE email = ?";
        $update_stmt = $pdo->prepare($update_sql);
        $update_stmt->execute([$new_password_hash, $email]);
        
        echo "<p>✅ Password berhasil diupdate!</p>";
    }
} else {
    echo "<p>❌ User admin@example.com tidak ditemukan!</p>";
    echo "<p>Membuat user baru...</p>";
    
    // Buat user baru
    $new_password_hash = password_hash('admin123', PASSWORD_DEFAULT);
    $insert_sql = "INSERT INTO users (display_name, full_name, email, password, tier, role, start_work) 
                   VALUES (?, ?, ?, ?, ?, ?, ?)";
    $insert_stmt = $pdo->prepare($insert_sql);
    $insert_stmt->execute([
        'Admin Example',
        'Administrator Example',
        'admin@example.com',
        $new_password_hash,
        'Tier 3',
        'Administrator',
        '2024-01-01'
    ]);
    
    echo "<p>✅ User admin@example.com berhasil dibuat!</p>";
}

// Tampilkan semua user untuk referensi
echo "<h3>Daftar Semua User:</h3>";
$all_users = $pdo->query("SELECT id, email, display_name, role FROM users ORDER BY id")->fetchAll();
echo "<table border='1' style='border-collapse: collapse;'>";
echo "<tr><th>ID</th><th>Email</th><th>Display Name</th><th>Role</th></tr>";
foreach ($all_users as $u) {
    echo "<tr>";
    echo "<td>" . $u['id'] . "</td>";
    echo "<td>" . $u['email'] . "</td>";
    echo "<td>" . $u['display_name'] . "</td>";
    echo "<td>" . $u['role'] . "</td>";
    echo "</tr>";
}
echo "</table>";

echo "<h3>Login Test:</h3>";
echo "<p>Email: admin@example.com</p>";
echo "<p>Password: admin123</p>";
echo "<p><a href='login.php'>Coba Login Sekarang</a></p>";
?> 
