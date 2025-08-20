<?php
require_once 'db.php';

echo "<h1>🔧 Fix Apri User ID</h1>";

try {
    echo "<h2>1. Check current Apri data</h2>";
    
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM jobsheet WHERE pic_name = ?");
    $stmt->execute(['Apri']);
    $apriCount = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Total Apri records: {$apriCount['total']}<br>";
    
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM jobsheet WHERE pic_name = ? AND (user_id IS NULL OR user_id = '')");
    $stmt->execute(['Apri']);
    $nullCount = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Apri records with null user_id: {$nullCount['total']}<br>";
    
    echo "<br><h2>2. Fix Apri user_id</h2>";
    
    $pdo->beginTransaction();
    
    $stmt = $pdo->prepare("UPDATE jobsheet SET user_id = ? WHERE pic_name = ? AND (user_id IS NULL OR user_id = '')");
    $stmt->execute(['Apri', 'Apri']);
    $updatedCount = $stmt->rowCount();
    
    echo "✅ Updated $updatedCount Apri records with user_id 'Apri'<br>";
    
    $pdo->commit();
    
    echo "<br><h2>3. Verify fix</h2>";
    
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM jobsheet WHERE pic_name = ? AND user_id = ?");
    $stmt->execute(['Apri', 'Apri']);
    $fixedCount = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Apri records with correct user_id: {$fixedCount['total']}<br>";
    
    echo "<br>🎉 Apri user_id issue fixed!<br>";
    
} catch (Exception $e) {
    if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
        try { $pdo->rollBack(); } catch (Throwable $__) {}
    }
    echo "❌ Error: " . $e->getMessage() . "<br>";
}
?>


