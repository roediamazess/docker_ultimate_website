<?php
require_once 'db.php';

echo "<h1>🔧 Fix Existing Apri Data</h1>";

try {
    echo "<h2>1. Check current Apri data</h2>";
    
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM jobsheet WHERE pic_name = ?");
    $stmt->execute(['Apri']);
    $apriCount = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Total Apri records: {$apriCount['total']}<br>";
    
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM jobsheet WHERE pic_name = ? AND (user_id IS NULL OR user_id = '')");
    $stmt->execute(['Apri']);
    $nullCount = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Apri records with null user_id: {$nullCount['total']}<br>";
    
    if ($nullCount['total'] > 0) {
        echo "<br><h2>2. Fix existing Apri data</h2>";
        
        $pdo->beginTransaction();
        
        $stmt = $pdo->prepare("UPDATE jobsheet SET user_id = ? WHERE pic_name = ? AND (user_id IS NULL OR user_id = '')");
        $stmt->execute(['Apri', 'Apri']);
        $updatedCount = $stmt->rowCount();
        
        echo "✅ Updated $updatedCount Apri records with user_id 'Apri'<br>";
        
        $pdo->commit();
        
        echo "<br><h2>3. Verify fix</h2>";
        
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM jobsheet WHERE pic_name = ? AND user_id = ?");
        $stmt->execute(['Apri', 'Apri']);
        $fixedCount = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "Apri records with correct user_id: {$fixedCount['total']}<br>";
        
        echo "<br>🎉 Existing Apri data fixed!<br>";
    } else {
        echo "<br>✅ Apri data already has correct user_id!<br>";
    }
    
    echo "<br><h2>4. Check all records with null user_id</h2>";
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM jobsheet WHERE user_id IS NULL OR user_id = ''");
    $totalNullCount = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Total records with null user_id: {$totalNullCount['total']}<br>";
    
    if ($totalNullCount['total'] > 0) {
        echo "<h3>Records with null user_id by PIC:</h3>";
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
        
        echo "<br><h3>Auto-fix all null user_id records</h3>";
        
        $pdo->beginTransaction();
        
        $stmt = $pdo->prepare("UPDATE jobsheet SET user_id = pic_name WHERE user_id IS NULL OR user_id = ''");
        $stmt->execute();
        $totalUpdated = $stmt->rowCount();
        
        echo "✅ Auto-fixed $totalUpdated records by setting user_id = pic_name<br>";
        
        $pdo->commit();
        
        echo "<br>🎉 All null user_id records fixed!<br>";
    }
    
    echo "<br><h2>5. Final verification</h2>";
    $stmt = $pdo->query("SELECT pic_name, COUNT(*) as total FROM jobsheet GROUP BY pic_name ORDER BY pic_name");
    $finalSummary = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>PIC Name</th><th>Total Records</th><th>Status</th></tr>";
    foreach ($finalSummary as $row) {
        $picName = $row['pic_name'];
        
        // Check if user exists
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM users WHERE user_id = ?");
        $stmt->execute([$picName]);
        $userExists = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $status = $userExists['count'] > 0 ? '✅ Valid User' : '❌ User Not Found';
        
        echo "<tr>";
        echo "<td>$picName</td>";
        echo "<td>{$row['total']}</td>";
        echo "<td>$status</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<br><h2>6. Summary</h2>";
    echo "<div style='background: #d1fae5; border: 1px solid #10b981; padding: 15px; border-radius: 8px;'>";
    echo "<h3>✅ Perbaikan yang sudah dilakukan:</h3>";
    echo "<ol>";
    echo "<li><strong>Data Apri:</strong> Field user_id sudah diperbaiki</li>";
    echo "<li><strong>Auto-fix:</strong> Semua record dengan null user_id sudah di-fix otomatis</li>";
    echo "<li><strong>jobsheet_save.php:</strong> Sudah diperbaiki agar otomatis mengisi user_id dengan pic_name jika kosong</li>";
    echo "</ol>";
    echo "<p><strong>Note:</strong> Sekarang setiap kali kamu menambah data baru, field user_id akan otomatis terisi dengan pic_name!</p>";
    echo "</div>";
    
} catch (Exception $e) {
    if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
        try { $pdo->rollBack(); } catch (Throwable $__) {}
    }
    echo "❌ Error: " . $e->getMessage() . "<br>";
}
?>


