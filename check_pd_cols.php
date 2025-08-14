<?php
session_start();
require_once 'db.php';

try {
    $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    echo "Driver: $driver\n";
    $stmt = $pdo->query("SELECT column_name, data_type FROM information_schema.columns WHERE table_name='projects_detail' ORDER BY ordinal_position");
    while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo $r['column_name'] . ' => ' . $r['data_type'] . "\n";
    }
} catch (Throwable $e) {
    echo 'ERR: ' . $e->getMessage();
}
