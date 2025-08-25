<?php
require_once 'db.php';

echo "🔧 Memperbaiki struktur tabel users...\n\n";

try {
    // 1. Buat ENUM types jika belum ada
    echo "1. Membuat ENUM types...\n";
    
    // Buat user_tier ENUM
    try {
        $pdo->exec("CREATE TYPE user_tier AS ENUM ('New Born', 'Tier 1', 'Tier 2', 'Tier 3')");
        echo "✅ user_tier ENUM berhasil dibuat\n";
    } catch (Exception $e) {
        echo "ℹ️  user_tier ENUM sudah ada atau error: " . $e->getMessage() . "\n";
    }
    
    // Buat user_role ENUM
    try {
        $pdo->exec("CREATE TYPE user_role AS ENUM ('Administrator', 'Management', 'Admin Office', 'User', 'Client')");
        echo "✅ user_role ENUM berhasil dibuat\n";
    } catch (Exception $e) {
        echo "ℹ️  user_role ENUM sudah ada atau error: " . $e->getMessage() . "\n";
    }
    
    // 2. Hapus kolom yang tidak diperlukan
    echo "\n2. Menghapus kolom yang tidak diperlukan...\n";
    
    $columns_to_remove = ['name', 'profile_photo', 'user_role'];
    
    foreach ($columns_to_remove as $col) {
        try {
            $pdo->exec("ALTER TABLE users DROP COLUMN IF EXISTS $col");
            echo "✅ Kolom $col berhasil dihapus\n";
        } catch (Exception $e) {
            echo "ℹ️  Kolom $col tidak ada atau error: " . $e->getMessage() . "\n";
        }
    }
    
    // 3. Tambah kolom yang diperlukan jika belum ada
    echo "\n3. Menambahkan kolom yang diperlukan...\n";
    
    // Tambah display_name jika belum ada
    try {
        $pdo->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS display_name VARCHAR(100)");
        echo "✅ Kolom display_name berhasil ditambahkan\n";
    } catch (Exception $e) {
        echo "ℹ️  display_name error: " . $e->getMessage() . "\n";
    }
    
    // 4. Ubah tipe data kolom tier dan role menjadi ENUM
    echo "\n4. Mengubah tipe data kolom tier dan role...\n";
    
    try {
        // Backup data tier dan role
        $pdo->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS tier_backup VARCHAR(40)");
        $pdo->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS role_backup VARCHAR(40)");
        
        // Copy data ke backup
        $pdo->exec("UPDATE users SET tier_backup = tier::text, role_backup = role::text");
        
        // Drop kolom lama
        $pdo->exec("ALTER TABLE users DROP COLUMN IF EXISTS tier");
        $pdo->exec("ALTER TABLE users DROP COLUMN IF EXISTS role");
        
        // Buat kolom baru dengan ENUM
        $pdo->exec("ALTER TABLE users ADD COLUMN tier user_tier");
        $pdo->exec("ALTER TABLE users ADD COLUMN role user_role");
        
        // Restore data dari backup
        $pdo->exec("UPDATE users SET tier = tier_backup::user_tier, role = role_backup::user_role");
        
        // Hapus kolom backup
        $pdo->exec("ALTER TABLE users DROP COLUMN tier_backup");
        $pdo->exec("ALTER TABLE users DROP COLUMN role_backup");
        
        echo "✅ Kolom tier dan role berhasil diubah menjadi ENUM\n";
    } catch (Exception $e) {
        echo "⚠️  Error mengubah tipe data: " . $e->getMessage() . "\n";
    }
    
    // 5. Verifikasi struktur akhir
    echo "\n5. Verifikasi struktur tabel...\n";
    
    $stmt = $pdo->query("SELECT column_name, data_type FROM information_schema.columns WHERE table_name = 'users' ORDER BY ordinal_position");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Struktur tabel users setelah perbaikan:\n";
    foreach ($columns as $col) {
        echo "- {$col['column_name']} ({$col['data_type']})\n";
    }
    
    echo "\n✅ Struktur tabel users berhasil diperbaiki!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>

