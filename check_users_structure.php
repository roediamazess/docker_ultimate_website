<?php
require_once 'db.php';

echo "Struktur tabel users:\n";
echo "=====================\n\n";

try {
    $stmt = $pdo->query("SELECT column_name, data_type, is_nullable FROM information_schema.columns WHERE table_name = 'users' ORDER BY ordinal_position");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($columns as $col) {
        echo "- {$col['column_name']} ({$col['data_type']}) - Nullable: {$col['is_nullable']}\n";
    }
    
    echo "\nData sample:\n";
    echo "============\n";
    
    $stmt = $pdo->query("SELECT * FROM users LIMIT 3");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($users as $user) {
        echo "\nUser:\n";
        foreach ($user as $key => $value) {
            echo "  $key: " . ($value ?? 'NULL') . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
