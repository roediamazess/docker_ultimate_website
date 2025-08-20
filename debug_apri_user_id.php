<?php
require_once 'db.php';

echo "<h1>🔍 Debug Apri User ID Issue</h1>";

try {
    echo "<h2>1. Check Apri user in users table</h2>";
    $stmt = $pdo->prepare("SELECT user_id, full_name FROM users WHERE user_id = ?");
    $stmt->execute(['Apri']);
    $apriUser = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($apriUser) {
        echo "✅ Found user Apri:<br>";
        echo "- user_id: {$apriUser['user_id']}<br>";
        echo "- full_name: {$apriUser['full_name']}<br>";
    } else {
        echo "❌ User Apri not found in users table<br>";
    }
    
    echo "<br><h2>2. Check Apri jobsheet records</h2>";
    $stmt = $pdo->prepare("SELECT * FROM jobsheet WHERE pic_name = ? ORDER BY day");
    $stmt->execute(['Apri']);
    $apriJobsheets = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Total Apri jobsheet records: " . count($apriJobsheets) . "<br>";
    
    if (count($apriJobsheets) > 0) {
        echo "<h3>Apri jobsheet data:</h3>";
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>user_id</th><th>pic_name</th><th>day</th><th>value</th><th>ontime</th><th>late</th><th>note</th></tr>";
        foreach ($apriJobsheets as $row) {
            echo "<tr>";
            echo "<td>" . ($row['user_id'] ?: 'NULL') . "</td>";
            echo "<td>{$row['pic_name']}</td>";
            echo "<td>{$row['day']}</td>";
            echo "<td>{$row['value']}</td>";
            echo "<td>" . ($row['ontime'] ? 'true' : 'false') . "</td>";
            echo "<td>" . ($row['late'] ? 'true' : 'false') . "</td>";
            echo "<td>{$row['note']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    echo "<br><h2>3. Check all records with null user_id</h2>";
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM jobsheet WHERE user_id IS NULL OR user_id = ''");
    $nullCount = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Total records with null user_id: {$nullCount['total']}<br>";
    
    if ($nullCount['total'] > 0) {
        echo "<h3>Records with null user_id:</h3>";
        $stmt = $pdo->query("SELECT pic_name, COUNT(*) as count FROM jobsheet WHERE user_id IS NULL OR user_id = '' GROUP BY pic_name ORDER BY pic_name");
        $nullRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>PIC Name</th><th>Count</th></tr>";
        foreach ($nullRecords as $row) {
            echo "<tr>";
            echo "<td>{$row['pic_name']}</td>";
            echo "<td>{$row['count']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    echo "<br><h2>4. Check jobsheet_save.php logic</h2>";
    echo "<div style='background: #fef3c7; border: 1px solid #f59e0b; padding: 15px; border-radius: 8px;'>";
    echo "<h3>🔍 Masalah yang ditemukan:</h3>";
    echo "<p>Field <code>user_id</code> masih <code>null</code> untuk user Apri. Ini bisa disebabkan oleh:</p>";
    echo "<ol>";
    echo "<li><strong>JavaScript tidak mengirim user_id:</strong> Fungsi <code>getCellMeta</code> tidak mengirim user_id dengan benar</li>";
    echo "<li><strong>PHP tidak mengisi user_id:</strong> File <code>jobsheet_save.php</code> tidak mengisi user_id jika kosong</li>";
    echo "<li><strong>Mapping tidak lengkap:</strong> <code>picNameToUserId</code> tidak include Apri</li>";
    echo "</ol>";
    echo "</div>";
    
    echo "<br><h2>5. Fix Apri user_id issue</h2>";
    $pdo->beginTransaction();
    
    // Update semua record Apri dengan user_id yang benar
    $stmt = $pdo->prepare("UPDATE jobsheet SET user_id = ? WHERE pic_name = ? AND (user_id IS NULL OR user_id = '')");
    $stmt->execute(['Apri', 'Apri']);
    $updatedCount = $stmt->rowCount();
    
    echo "✅ Updated $updatedCount Apri records with user_id 'Apri'<br>";
    
    $pdo->commit();
    
    echo "<br><h2>6. Verify fix results</h2>";
    $stmt = $pdo->prepare("SELECT * FROM jobsheet WHERE pic_name = ? ORDER BY day");
    $stmt->execute(['Apri']);
    $fixedApriJobsheets = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($fixedApriJobsheets) > 0) {
        echo "<h3>Fixed Apri jobsheet data:</h3>";
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>user_id</th><th>pic_name</th><th>day</th><th>value</th><th>ontime</th><th>late</th><th>note</th></tr>";
        foreach ($fixedApriJobsheets as $row) {
            echo "<tr>";
            echo "<td>" . ($row['user_id'] ?: 'NULL') . "</td>";
            echo "<td>{$row['pic_name']}</td>";
            echo "<td>{$row['day']}</td>";
            echo "<td>{$row['value']}</td>";
            echo "<td>" . ($row['ontime'] ? 'true' : 'false') . "</td>";
            echo "<td>" . ($row['late'] ? 'true' : 'false') . "</td>";
            echo "<td>{$row['note']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    echo "<br><h2>7. Check remaining null user_id records</h2>";
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM jobsheet WHERE user_id IS NULL OR user_id = ''");
    $remainingNullCount = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Remaining records with null user_id: {$remainingNullCount['total']}<br>";
    
    if ($remainingNullCount['total'] > 0) {
        echo "<h3>Remaining records with null user_id:</h3>";
        $stmt = $pdo->query("SELECT pic_name, COUNT(*) as count FROM jobsheet WHERE user_id IS NULL OR user_id = '' GROUP BY pic_name ORDER BY pic_name");
        $remainingNullRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>PIC Name</th><th>Count</th></tr>";
        foreach ($remainingNullRecords as $row) {
            echo "<tr>";
            echo "<td>{$row['pic_name']}</td>";
            echo "<td>{$row['count']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
} catch (Exception $e) {
    if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
        try { $pdo->rollBack(); } catch (Throwable $__) {}
    }
    echo "❌ Error: " . $e->getMessage() . "<br>";
    echo "Stack trace: <pre>" . $e->getTraceAsString() . "</pre>";
}
?>


