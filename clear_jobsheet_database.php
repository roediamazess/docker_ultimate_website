<?php
session_start();
require_once 'db.php';
require_once 'access_control.php';
require_login();

header('Content-Type: application/json; charset=utf-8');

try {
    $pdo->beginTransaction();
    
    // Delete all records from jobsheet
    $stmt = $pdo->prepare("DELETE FROM jobsheet");
    $stmt->execute();
    $deletedCount = $stmt->rowCount();
    
    $pdo->commit();
    
    echo json_encode([
        'ok' => true,
        'message' => "Successfully deleted $deletedCount records from jobsheet table",
        'deletedCount' => $deletedCount
    ]);
    
} catch (Exception $e) {
    if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
        try { $pdo->rollBack(); } catch (Throwable $__) {}
    }
    
    echo json_encode([
        'ok' => false,
        'error' => $e->getMessage()
    ]);
}
?>


