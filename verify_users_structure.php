<?php
require_once 'db.php';

echo "Verifikasi struktur tabel users:\n";
echo "================================\n\n";

try {
    $stmt = $pdo->query("SELECT column_name, data_type, is_nullable FROM information_schema.columns WHERE table_name = 'users' ORDER BY ordinal_position");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Kolom yang tersedia:\n";
    foreach ($columns as $col) {
        echo "- {$col['column_name']} ({$col['data_type']}) - Nullable: {$col['is_nullable']}\n";
    }
    
    echo "\nTest query sederhana:\n";
    $stmt = $pdo->query("SELECT id, display_name, full_name, email, tier, role FROM users LIMIT 1");
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        echo "✅ Query berhasil! Data user pertama:\n";
        foreach ($user as $key => $value) {
            echo "  $key: " . ($value ?? 'NULL') . "\n";
        }
    } else {
        echo "ℹ️  Tidak ada data user\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>

