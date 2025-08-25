<?php
require_once 'db.php';

echo "Checking users table structure...\n";

try {
    $stmt = $pdo->query("SELECT column_name FROM information_schema.columns WHERE table_name = 'users' ORDER BY ordinal_position");
    while($row = $stmt->fetch()) {
        echo $row['column_name'] . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>

