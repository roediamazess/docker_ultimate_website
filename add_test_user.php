<?php
require_once 'db.php';

// Data user test
$test_users = [
    [
        'display_name' => 'Admin Test',
        'full_name' => 'Administrator Test',
        'email' => 'admin@test.com',
        'password' => password_hash('admin123', PASSWORD_DEFAULT),
        'tier' => 'Tier 3',
        'role' => 'Administrator',
        'start_work' => '2024-01-01'
    ],
    [
        'display_name' => 'User Test',
        'full_name' => 'User Test',
        'email' => 'user@test.com',
        'password' => password_hash('user123', PASSWORD_DEFAULT),
        'tier' => 'Tier 1',
        'role' => 'User',
        'start_work' => '2024-01-01'
    ]
];

try {
    // Cek apakah tabel users sudah ada
    $stmt = $pdo->query("SELECT COUNT(*) FROM users");
    echo "Tabel users sudah ada dengan " . $stmt->fetchColumn() . " user(s)\n";
    
    // Tambahkan user test jika belum ada
    foreach ($test_users as $user) {
        // Cek apakah email sudah ada
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
        $stmt->execute([$user['email']]);
        
        if ($stmt->fetchColumn() == 0) {
            $sql = "INSERT INTO users (display_name, full_name, email, password, tier, role, start_work) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $user['display_name'],
                $user['full_name'],
                $user['email'],
                $user['password'],
                $user['tier'],
                $user['role'],
                $user['start_work']
            ]);
            echo "User {$user['email']} berhasil ditambahkan!\n";
        } else {
            echo "User {$user['email']} sudah ada.\n";
        }
    }
    
    echo "\n=== Data Login Test ===\n";
    echo "Email: admin@test.com\n";
    echo "Password: admin123\n";
    echo "\nEmail: user@test.com\n";
    echo "Password: user123\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?> 