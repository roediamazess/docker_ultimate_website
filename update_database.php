<?php
// update_database.php - Script untuk update database
require_once 'db.php';

echo "🔄 Updating database for reset password functionality...\n\n";

try {
    // Add reset password columns
    $sql1 = "ALTER TABLE users ADD COLUMN IF NOT EXISTS reset_token VARCHAR(64) NULL";
    $stmt1 = $pdo->prepare($sql1);
    $stmt1->execute();
    echo "✅ Added reset_token column\n";
    
    $sql2 = "ALTER TABLE users ADD COLUMN IF NOT EXISTS reset_expires TIMESTAMP NULL";
    $stmt2 = $pdo->prepare($sql2);
    $stmt2->execute();
    echo "✅ Added reset_expires column\n";
    
    // Add indexes for better performance
    $sql3 = "CREATE INDEX IF NOT EXISTS idx_users_reset_token ON users(reset_token)";
    $stmt3 = $pdo->prepare($sql3);
    $stmt3->execute();
    echo "✅ Added reset_token index\n";
    
    $sql4 = "CREATE INDEX IF NOT EXISTS idx_users_reset_expires ON users(reset_expires)";
    $stmt4 = $pdo->prepare($sql4);
    $stmt4->execute();
    echo "✅ Added reset_expires index\n";
    
    echo "\n🎉 Database updated successfully!\n";
    echo "📧 Reset password functionality is now ready!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?> 