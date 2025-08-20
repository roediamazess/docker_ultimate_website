<?php
require_once 'db.php';

try {
    $pdo->beginTransaction();
    
    $stmt = $pdo->prepare("UPDATE jobsheet SET user_id = ? WHERE pic_name = ? AND (user_id IS NULL OR user_id = '')");
    $stmt->execute(['Apri', 'Apri']);
    $updatedCount = $stmt->rowCount();
    
    $stmt = $pdo->prepare("UPDATE jobsheet SET user_id = pic_name WHERE user_id IS NULL OR user_id = ''");
    $stmt->execute();
    $totalUpdated = $stmt->rowCount();
    
    $pdo->commit();
    
    echo "SUCCESS: Fixed $updatedCount Apri records and $totalUpdated total records\n";
    
} catch (Exception $e) {
    if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
        try { $pdo->rollBack(); } catch (Throwable $__) {}
    }
    echo "ERROR: " . $e->getMessage() . "\n";
}
?>


