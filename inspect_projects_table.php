<?php
session_start();
require_once 'db.php';

header('Content-Type: text/plain; charset=utf-8');
try {
    $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    echo "Driver: $driver\n\n";
    echo "Columns for projects (order):\n";
    $stmt = $pdo->query("SELECT column_name, data_type, is_nullable FROM information_schema.columns WHERE table_name='projects' ORDER BY ordinal_position");
    $i = 1;
    while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo str_pad($i++, 3, ' ', STR_PAD_LEFT) . '. ' . $r['column_name'] . ' | ' . $r['data_type'] . ' | nullable=' . $r['is_nullable'] . "\n";
    }
} catch (Throwable $e) {
    echo 'ERR: ' . $e->getMessage();
}
