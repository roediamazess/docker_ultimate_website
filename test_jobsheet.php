<?php
session_start();
require_once 'db.php';
require_once 'access_control.php';
require_login();

header('Content-Type: text/html; charset=utf-8');

echo "<h2>Test Jobsheet Data</h2>";

try {
    $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    echo "<p>Database driver: $driver</p>";
    
    // Cek semua data di tabel jobsheet
    $stmt = $pdo->query("SELECT * FROM jobsheet ORDER BY pic_name, day");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>All data in jobsheet table:</h3>";
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>user_id</th><th>pic_name</th><th>day</th><th>value</th><th>ontime</th><th>late</th><th>note</th></tr>";
    
    foreach ($rows as $row) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['user_id'] ?? 'NULL') . "</td>";
        echo "<td>" . htmlspecialchars($row['pic_name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['day']) . "</td>";
        echo "<td>" . htmlspecialchars($row['value']) . "</td>";
        echo "<td>" . ($row['ontime'] ? 'true' : 'false') . "</td>";
        echo "<td>" . ($row['late'] ? 'true' : 'false') . "</td>";
        echo "<td>" . htmlspecialchars($row['note'] ?? '') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Test query dengan tanggal spesifik
    echo "<h3>Test query for period 01-09-25 to 30-09-25:</h3>";
    
    if ($driver === 'mysql') {
        $sql = "SELECT * FROM jobsheet WHERE STR_TO_DATE(day,'%d-%m-%y') BETWEEN STR_TO_DATE('01-09-25', '%d-%m-%y') AND STR_TO_DATE('30-09-25', '%d-%m-%y')";
    } else {
        $sql = "SELECT * FROM jobsheet WHERE to_date(day, 'DD-MM-YY') BETWEEN to_date('01-09-25', 'DD-MM-YY') AND to_date('30-09-25', 'DD-MM-YY')";
    }
    
    $stmt = $pdo->query($sql);
    $periodRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p>Found " . count($periodRows) . " records for this period:</p>";
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>pic_name</th><th>day</th><th>value</th></tr>";
    
    foreach ($periodRows as $row) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['pic_name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['day']) . "</td>";
        echo "<td>" . htmlspecialchars($row['value']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
} catch (Throwable $e) {
    echo "<p style='color: red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
