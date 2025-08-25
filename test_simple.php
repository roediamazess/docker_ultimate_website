<?php
require_once 'db.php';

echo "Testing query yang sebelumnya error...\n\n";

try {
    // Test query yang sebelumnya error di layoutHorizontal.php
    $user_id = 1;
    $stmt = $pdo->prepare("SELECT profile_photo FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $result = $stmt->fetch();
    
    echo "✅ Query berhasil!\n";
    echo "User ID: $user_id\n";
    echo "Profile photo: " . ($result['profile_photo'] ?? 'NULL') . "\n";
    
} catch (Exception $e) {
    echo "❌ Query masih error: " . $e->getMessage() . "\n";
}

echo "\nTest selesai. Jika tidak ada error, website sudah bisa diakses.\n";
?>
