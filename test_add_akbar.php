<?php
session_start();
require_once 'db.php';
require_once 'access_control.php';
require_login();

header('Content-Type: text/html; charset=utf-8');

echo "<h2>Test Add Akbar Data</h2>";

try {
    $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    echo "<p>Database driver: $driver</p>";
    
    // Tambah data Akbar
    $picName = 'Akbar';
    $day = '01-09-25';
    $value = 'D';
    $ontime = false;
    $late = false;
    $note = '';
    
    if ($driver === 'mysql') {
        $stmt = $pdo->prepare('INSERT INTO jobsheet (user_id, pic_name, day, value, ontime, late, note) VALUES (?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE value = VALUES(value), ontime = VALUES(ontime), late = VALUES(late), note = VALUES(note)');
        $stmt->execute([null, $picName, $day, $value, $ontime ? 1 : 0, $late ? 1 : 0, $note]);
    } else {
        $stmt = $pdo->prepare('INSERT INTO jobsheet (user_id, pic_name, day, value, ontime, late, note) VALUES (?, ?, ?, ?, ?, ?, ?)
            ON CONFLICT (pic_name, day) DO UPDATE SET value = EXCLUDED.value, ontime = EXCLUDED.ontime, late = EXCLUDED.late, note = EXCLUDED.note');
        $stmt->bindValue(1, null, PDO::PARAM_NULL);
        $stmt->bindValue(2, $picName, PDO::PARAM_STR);
        $stmt->bindValue(3, $day, PDO::PARAM_STR);
        $stmt->bindValue(4, $value, PDO::PARAM_STR);
        $stmt->bindValue(5, $ontime, PDO::PARAM_BOOL);
        $stmt->bindValue(6, $late, PDO::PARAM_BOOL);
        $stmt->bindValue(7, $note, PDO::PARAM_STR);
        $stmt->execute();
    }
    
    echo "<p style='color: green;'>Data Akbar berhasil ditambahkan!</p>";
    
    // Cek data yang baru ditambahkan
    $stmt = $pdo->prepare("SELECT * FROM jobsheet WHERE pic_name = ? AND day = ?");
    $stmt->execute([$picName, $day]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($row) {
        echo "<p>Data yang baru ditambahkan:</p>";
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>pic_name</th><th>day</th><th>value</th><th>ontime</th><th>late</th></tr>";
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['pic_name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['day']) . "</td>";
        echo "<td>" . htmlspecialchars($row['value']) . "</td>";
        echo "<td>" . ($row['ontime'] ? 'true' : 'false') . "</td>";
        echo "<td>" . ($row['late'] ? 'true' : 'false') . "</td>";
        echo "</tr>";
        echo "</table>";
    }
    
} catch (Throwable $e) {
    echo "<p style='color: red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
