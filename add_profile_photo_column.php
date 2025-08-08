<?php
// add_profile_photo_column.php - Script untuk menambahkan kolom profile_photo ke tabel users
require_once 'db.php';

echo "🔄 Adding profile_photo column to users table...\n\n";

try {
    // Check if column already exists
    $check_sql = "SELECT column_name FROM information_schema.columns WHERE table_name = 'users' AND column_name = 'profile_photo'";
    $check_stmt = $pdo->prepare($check_sql);
    $check_stmt->execute();
    $column_exists = $check_stmt->fetch();
    
    if ($column_exists) {
        echo "✅ Column 'profile_photo' already exists in users table\n";
    } else {
        // Add profile_photo column
        $sql = "ALTER TABLE users ADD COLUMN profile_photo VARCHAR(255) NULL";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        echo "✅ Added profile_photo column to users table\n";
    }
    
    // Create uploads directory if it doesn't exist
    $upload_dir = 'uploads/profile_photos/';
    if (!is_dir($upload_dir)) {
        if (mkdir($upload_dir, 0755, true)) {
            echo "✅ Created uploads/profile_photos/ directory\n";
        } else {
            echo "⚠️  Could not create uploads/profile_photos/ directory\n";
        }
    } else {
        echo "✅ uploads/profile_photos/ directory already exists\n";
    }
    
    // Show current table structure
    echo "\n📋 Current users table structure:\n";
    $structure_sql = "SELECT column_name, data_type, is_nullable FROM information_schema.columns WHERE table_name = 'users' ORDER BY ordinal_position";
    $structure_stmt = $pdo->prepare($structure_sql);
    $structure_stmt->execute();
    $columns = $structure_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($columns as $column) {
        echo "  - {$column['column_name']} ({$column['data_type']}) - Nullable: {$column['is_nullable']}\n";
    }
    
    echo "\n🎉 Profile photo functionality is now ready!\n";
    echo "📁 Upload directory: uploads/profile_photos/\n";
    echo "🔗 Profile page: view-profile.php\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
