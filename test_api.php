<?php
session_start();
require_once 'db.php';
require_once 'access_control.php';
require_login();

header('Content-Type: text/html; charset=utf-8');

echo "<h2>Test Direct Database Query</h2>";

try {
    // Query langsung ke database
    $sql = "SELECT COALESCE(user_id,'') AS user_id, pic_name, day, value, ontime, late, note FROM jobsheet ORDER BY pic_name, day";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p>Total records: " . count($rows) . "</p>";
    
    if (count($rows) > 0) {
        echo "<p>All Data:</p>";
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>pic_name</th><th>day</th><th>value</th><th>ontime</th><th>late</th></tr>";
        
        foreach ($rows as $row) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['pic_name']) . "</td>";
            echo "<td>" . htmlspecialchars($row['day']) . "</td>";
            echo "<td>" . htmlspecialchars($row['value']) . "</td>";
            echo "<td>" . ($row['ontime'] ? 'true' : 'false') . "</td>";
            echo "<td>" . ($row['late'] ? 'true' : 'false') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Cek data Akbar
        $akbarData = array_filter($rows, function($row) {
            return $row['pic_name'] === 'Akbar';
        });
        
        echo "<p>Akbar data count: " . count($akbarData) . "</p>";
        if (count($akbarData) > 0) {
            echo "<p>Akbar data:</p>";
            echo "<pre>" . print_r($akbarData, true) . "</pre>";
        } else {
            echo "<p style='color: red;'>No Akbar data found!</p>";
        }
    } else {
        echo "<p style='color: red;'>No data found in database</p>";
    }
    
} catch (Throwable $e) {
    echo "<p style='color: red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
