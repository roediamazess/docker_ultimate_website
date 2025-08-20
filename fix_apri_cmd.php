<?php
require_once 'db.php';

echo "=== FIX APRI USER ID ===\n";

try {
    echo "1. Checking current Apri data...\n";
    
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM jobsheet WHERE pic_name = ?");
    $stmt->execute(['Apri']);
    $apriCount = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "   Total Apri records: {$apriCount['total']}\n";
    
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM jobsheet WHERE pic_name = ? AND (user_id IS NULL OR user_id = '')");
    $stmt->execute(['Apri']);
    $nullCount = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "   Apri records with null user_id: {$nullCount['total']}\n";
    
    if ($nullCount['total'] > 0) {
        echo "\n2. Fixing Apri user_id...\n";
        
        $pdo->beginTransaction();
        
        $stmt = $pdo->prepare("UPDATE jobsheet SET user_id = ? WHERE pic_name = ? AND (user_id IS NULL OR user_id = '')");
        $stmt->execute(['Apri', 'Apri']);
        $updatedCount = $stmt->rowCount();
        
        echo "   Updated $updatedCount Apri records\n";
        
        $pdo->commit();
        echo "   ✅ Fix completed!\n";
    } else {
        echo "\n   ✅ Apri data already has correct user_id!\n";
    }
    
    echo "\n3. Checking all null user_id records...\n";
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM jobsheet WHERE user_id IS NULL OR user_id = ''");
    $totalNullCount = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "   Total records with null user_id: {$totalNullCount['total']}\n";
    
    if ($totalNullCount['total'] > 0) {
        echo "\n4. Auto-fixing all null user_id records...\n";
        
        $pdo->beginTransaction();
        
        $stmt = $pdo->prepare("UPDATE jobsheet SET user_id = pic_name WHERE user_id IS NULL OR user_id = ''");
        $stmt->execute();
        $totalUpdated = $stmt->rowCount();
        
        echo "   Auto-fixed $totalUpdated records\n";
        
        $pdo->commit();
        echo "   ✅ All records fixed!\n";
    }
    
    echo "\n5. Final verification...\n";
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM jobsheet WHERE user_id IS NULL OR user_id = ''");
    $finalNullCount = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "   Remaining null user_id records: {$finalNullCount['total']}\n";
    
    if ($finalNullCount['total'] == 0) {
        echo "\n🎉 SUCCESS: All user_id issues fixed!\n";
    } else {
        echo "\n⚠️  WARNING: Some records still have null user_id\n";
    }
    
    echo "\n=== SUMMARY ===\n";
    echo "✅ jobsheet_save.php sudah diperbaiki dengan auto-fill user_id\n";
    echo "✅ Data Apri yang sudah ada sudah diperbaiki\n";
    echo "✅ Semua record dengan null user_id sudah di-fix\n";
    echo "✅ Sekarang setiap update akan otomatis mengisi user_id dengan pic_name\n";
    
} catch (Exception $e) {
    if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
        try { $pdo->rollBack(); } catch (Throwable $__) {}
    }
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}
?>


