<?php
header('Content-Type: application/json');
$status = ['app' => 'OK', 'db' => 'OK'];
try {
    require_once 'db.php';
    $pdo->query('SELECT 1');
} catch (Exception $e) {
    $status['db'] = 'ERROR';
    $status['error'] = $e->getMessage();
}
echo json_encode($status);
