<?php
session_start();
require_once 'db.php';
require_once 'access_control.php';
require_login();

header('Content-Type: text/html; charset=utf-8');

echo "<h2>Test Simple API Response</h2>";

try {
    // Simulasi input yang sama seperti JavaScript
    $input = ['start' => '01-09-25', 'end' => '30-09-25'];
    
    echo "<p>Input: " . json_encode($input) . "</p>";
    
    // Simulasi logika dari jobsheet_get_period.php
    $start = $input['start'];
    $end = $input['end'];
    
    if ($start === '' || $end === '') { 
        echo "<p>Empty start or end</p>";
        exit;
    }
    
    // Query database
    $sql = "SELECT COALESCE(user_id,'') AS user_id, pic_name, day, value, 
            CASE WHEN ontime IS NULL THEN false ELSE ontime END as ontime,
            CASE WHEN late IS NULL THEN false ELSE late END as late,
            note FROM jobsheet";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p>Total records from database: " . count($rows) . "</p>";
    
    // Simulasi JSON response
    $jsonResponse = json_encode($rows, JSON_UNESCAPED_UNICODE);
    
    echo "<p>JSON Response length: " . strlen($jsonResponse) . "</p>";
    echo "<p>JSON Response preview:</p>";
    echo "<pre>" . htmlspecialchars(substr($jsonResponse, 0, 500)) . "...</pre>";
    
    // Cek data Akbar
    $akbarData = array_filter($rows, function($row) {
        return $row['pic_name'] === 'Akbar';
    });
    
    echo "<p>Akbar records found: " . count($akbarData) . "</p>";
    
    if (count($akbarData) > 0) {
        echo "<p>Akbar data:</p>";
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>pic_name</th><th>day</th><th>value</th><th>ontime</th><th>late</th></tr>";
        
        foreach ($akbarData as $row) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['pic_name']) . "</td>";
            echo "<td>" . htmlspecialchars($row['day']) . "</td>";
            echo "<td>" . htmlspecialchars($row['value']) . "</td>";
            echo "<td>" . ($row['ontime'] ? 'true' : 'false') . "</td>";
            echo "<td>" . ($row['late'] ? 'true' : 'false') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
} catch (Throwable $e) {
    echo "<p style='color: red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
