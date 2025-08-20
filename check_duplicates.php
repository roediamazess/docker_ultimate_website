<?php
require_once 'db.php';

$stmt = $pdo->query('SELECT project_id, COUNT(*) as count FROM projects GROUP BY project_id HAVING COUNT(*) > 1');
$duplicates = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($duplicates)) {
    echo "No duplicate project IDs found\n";
} else {
    echo "Duplicate project IDs found:\n";
    print_r($duplicates);
}