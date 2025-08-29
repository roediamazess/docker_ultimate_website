<?php
// Health check endpoint for Docker
header('Content-Type: application/json');

$status = [
    'status' => 'healthy',
    'timestamp' => date('Y-m-d H:i:s'),
    'php_version' => PHP_VERSION,
    'database' => 'unknown'
];

try {
    require_once 'db.php';
    $stmt = $pdo->query('SELECT 1');
    $stmt->fetch();
    $status['database'] = 'connected';
} catch (Exception $e) {
    $status['database'] = 'error: ' . $e->getMessage();
    $status['status'] = 'unhealthy';
}

echo json_encode($status, JSON_PRETTY_PRINT);
?>
