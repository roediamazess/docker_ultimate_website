<?php
session_start();
require_once 'db.php';
require_once 'access_control.php';
require_login();

header('Content-Type: application/json; charset=utf-8');

try {
    // Check total records
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM jobsheet");
    $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Check last updated record
    $stmt = $pdo->query("SELECT MAX(day) as last_day FROM jobsheet");
    $lastDay = $stmt->fetch(PDO::FETCH_ASSOC)['last_day'];
    
    // Check sample data
    $stmt = $pdo->query("SELECT pic_name, day, value FROM jobsheet ORDER BY day DESC LIMIT 5");
    $sampleData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'ok' => true,
        'total' => $total,
        'lastUpdated' => $lastDay,
        'sampleData' => $sampleData
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'ok' => false,
        'error' => $e->getMessage()
    ]);
}
?>


