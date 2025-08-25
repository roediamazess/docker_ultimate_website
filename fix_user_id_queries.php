<?php
// fix_user_id_queries.php - Script untuk memperbaiki query yang salah menggunakan user_id sebagai nama kolom
require_once 'db.php';

echo "🔧 Memperbaiki query yang menggunakan user_id sebagai nama kolom...\n\n";

// Daftar file yang perlu diperbaiki
$files_to_fix = [
    'view-profile.php',
    'users.php',
    'reset-password.php',
    'global_password_migration.php',
    'login_process.php',
    'lookup_users.php',
    'get_project_data.php',
    'fix_existing_apri_data.php'
];

$total_fixed = 0;

foreach ($files_to_fix as $file) {
    if (!file_exists($file)) {
        echo "⚠️  File $file tidak ditemukan, skip...\n";
        continue;
    }
    
    echo "📁 Memproses file: $file\n";
    
    $content = file_get_contents($file);
    $original_content = $content;
    
    // Pattern untuk mencari query yang salah
    $patterns = [
        // WHERE user_id = ? atau WHERE user_id = :user_id
        '/WHERE\s+user_id\s*=\s*\?/i' => 'WHERE id = ?',
        '/WHERE\s+user_id\s*=\s*:user_id/i' => 'WHERE id = :user_id',
        '/WHERE\s+user_id\s*=\s*:uid/i' => 'WHERE id = :uid',
        
        // SELECT user_id FROM users
        '/SELECT\s+user_id\s+FROM\s+users/i' => 'SELECT id FROM users',
        
        // UPDATE users SET ... WHERE user_id
        '/UPDATE\s+users\s+SET.*WHERE\s+user_id/i' => 'UPDATE users SET ... WHERE id',
        
        // INSERT INTO users (user_id, ...)
        '/INSERT\s+INTO\s+users\s*\(\s*user_id/i' => 'INSERT INTO users (id',
        
        // Referensi kolom user_id dalam query
        '/\buser_id\b(?=\s*FROM\s+users)/i' => 'id',
    ];
    
    $fixed = false;
    foreach ($patterns as $pattern => $replacement) {
        if (preg_match($pattern, $content)) {
            $content = preg_replace($pattern, $replacement, $content);
            $fixed = true;
        }
    }
    
    if ($fixed) {
        // Tulis kembali file yang sudah diperbaiki
        file_put_contents($file, $content);
        echo "✅ File $file berhasil diperbaiki\n";
        $total_fixed++;
    } else {
        echo "ℹ️  File $file tidak memerlukan perbaikan\n";
    }
}

echo "\n🎉 Selesai! Total file yang diperbaiki: $total_fixed\n";
echo "⚠️  Catatan: Beberapa file mungkin masih memerlukan perbaikan manual\n";
echo "🔍 Periksa file-file berikut untuk query yang mungkin terlewat:\n";
echo "   - view-profile.php\n";
echo "   - users.php\n";
echo "   - reset-password.php\n";
echo "   - Dan file lainnya yang menggunakan query database\n";
?>
