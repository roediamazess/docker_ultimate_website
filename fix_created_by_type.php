<?php
session_start();
require_once 'db.php';

echo "<h2>Fix created_by Column Type</h2>";

try {
    $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    echo "<p>Database driver: $driver</p>";

    echo "<h3>Altering projects_detail.created_by to VARCHAR(150)...</h3>";

    if ($driver === 'pgsql') {
        $pdo->exec("ALTER TABLE projects_detail ALTER COLUMN created_by TYPE VARCHAR(150)");
        echo "<p style='color:green'>✓ PostgreSQL: created_by changed to VARCHAR(150)</p>";
    } else {
        $pdo->exec("ALTER TABLE projects_detail MODIFY COLUMN created_by VARCHAR(150) NULL");
        echo "<p style='color:green'>✓ MySQL: created_by changed to VARCHAR(150)</p>";
    }

    echo "<h3>Verify:</h3>";
    if ($driver === 'pgsql') {
        $stmt = $pdo->query("SELECT data_type FROM information_schema.columns WHERE table_name='projects_detail' AND column_name='created_by'");
        $type = $stmt->fetchColumn();
        echo "<p>created_by type: " . htmlspecialchars($type) . "</p>";
    } else {
        $stmt = $pdo->query("DESCRIBE projects_detail");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if ($row['Field'] === 'created_by') {
                echo "<p>created_by type: " . htmlspecialchars($row['Type']) . "</p>";
                break;
            }
        }
    }

    echo "<p style='color:green'><strong>Migration completed.</strong></p>";

} catch (Throwable $e) {
    echo "<p style='color:red'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
