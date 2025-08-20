<?php
require_once 'db.php';

echo "Fixing Apri user_id...\n";

try {
    $pdo->beginTransaction();
    
    $stmt = $pdo->prepare("UPDATE jobsheet SET user_id = ? WHERE pic_name = ? AND (user_id IS NULL OR user_id = '')");
    $stmt->execute(['Apri', 'Apri']);
    $updatedCount = $stmt->rowCount();
    
    echo "Updated $updatedCount Apri records\n";
    
    $pdo->commit();
    echo "Done!\n";
    
} catch (Exception $e) {
    if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
        try { $pdo->rollBack(); } catch (Throwable $__) {}
    }
    echo "Error: " . $e->getMessage() . "\n";
}
?>


