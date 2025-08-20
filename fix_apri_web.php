<!DOCTYPE html>
<html>
<head>
    <title>Fix Apri User ID</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .success { background: #d1fae5; border: 1px solid #10b981; padding: 15px; border-radius: 8px; margin: 10px 0; }
        .warning { background: #fef3c7; border: 1px solid #f59e0b; padding: 15px; border-radius: 8px; margin: 10px 0; }
        .error { background: #fee2e2; border: 1px solid #ef4444; padding: 15px; border-radius: 8px; margin: 10px 0; }
        table { border-collapse: collapse; margin: 10px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .btn { background: #3b82f6; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; margin: 5px; }
        .btn:hover { background: #2563eb; }
    </style>
</head>
<body>
    <h1>🔧 Fix Apri User ID Issue</h1>
    
    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        require_once 'db.php';
        
        try {
            echo "<div class='success'>";
            echo "<h2>🔄 Processing...</h2>";
            
            echo "<h3>1. Checking current Apri data...</h3>";
            
            $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM jobsheet WHERE pic_name = ?");
            $stmt->execute(['Apri']);
            $apriCount = $stmt->fetch(PDO::FETCH_ASSOC);
            echo "Total Apri records: {$apriCount['total']}<br>";
            
            $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM jobsheet WHERE pic_name = ? AND (user_id IS NULL OR user_id = '')");
            $stmt->execute(['Apri']);
            $nullCount = $stmt->fetch(PDO::FETCH_ASSOC);
            echo "Apri records with null user_id: {$nullCount['total']}<br>";
            
            if ($nullCount['total'] > 0) {
                echo "<h3>2. Fixing Apri user_id...</h3>";
                
                $pdo->beginTransaction();
                
                $stmt = $pdo->prepare("UPDATE jobsheet SET user_id = ? WHERE pic_name = ? AND (user_id IS NULL OR user_id = '')");
                $stmt->execute(['Apri', 'Apri']);
                $updatedCount = $stmt->rowCount();
                
                echo "✅ Updated $updatedCount Apri records with user_id 'Apri'<br>";
                
                $pdo->commit();
                echo "✅ Fix completed!<br>";
            } else {
                echo "✅ Apri data already has correct user_id!<br>";
            }
            
            echo "<h3>3. Checking all null user_id records...</h3>";
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM jobsheet WHERE user_id IS NULL OR user_id = ''");
            $totalNullCount = $stmt->fetch(PDO::FETCH_ASSOC);
            echo "Total records with null user_id: {$totalNullCount['total']}<br>";
            
            if ($totalNullCount['total'] > 0) {
                echo "<h3>4. Auto-fixing all null user_id records...</h3>";
                
                $pdo->beginTransaction();
                
                $stmt = $pdo->prepare("UPDATE jobsheet SET user_id = pic_name WHERE user_id IS NULL OR user_id = ''");
                $stmt->execute();
                $totalUpdated = $stmt->rowCount();
                
                echo "✅ Auto-fixed $totalUpdated records<br>";
                
                $pdo->commit();
                echo "✅ All records fixed!<br>";
            }
            
            echo "<h3>5. Final verification...</h3>";
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM jobsheet WHERE user_id IS NULL OR user_id = ''");
            $finalNullCount = $stmt->fetch(PDO::FETCH_ASSOC);
            echo "Remaining null user_id records: {$finalNullCount['total']}<br>";
            
            if ($finalNullCount['total'] == 0) {
                echo "<div class='success'>🎉 SUCCESS: All user_id issues fixed!</div>";
            } else {
                echo "<div class='warning'>⚠️ WARNING: Some records still have null user_id</div>";
            }
            
            echo "<h3>6. Summary</h3>";
            echo "<div class='success'>";
            echo "<h4>✅ Perbaikan yang sudah dilakukan:</h4>";
            echo "<ol>";
            echo "<li><strong>Data Apri:</strong> Field user_id sudah diperbaiki</li>";
            echo "<li><strong>Auto-fix:</strong> Semua record dengan null user_id sudah di-fix otomatis</li>";
            echo "<li><strong>jobsheet_save.php:</strong> Sudah diperbaiki agar otomatis mengisi user_id dengan pic_name jika kosong</li>";
            echo "</ol>";
            echo "<p><strong>Note:</strong> Sekarang setiap kali kamu menambah data baru, field user_id akan otomatis terisi dengan pic_name!</p>";
            echo "</div>";
            
            echo "</div>";
            
        } catch (Exception $e) {
            if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
                try { $pdo->rollBack(); } catch (Throwable $__) {}
            }
            echo "<div class='error'>❌ ERROR: " . $e->getMessage() . "</div>";
        }
    }
    ?>
    
    <form method="POST">
        <button type="submit" class="btn">🔧 Fix Apri User ID Issue</button>
    </form>
    
    <div class='warning'>
        <h3>📋 Penjelasan Masalah:</h3>
        <p>Field <code>user_id</code> untuk user Apri masih tersimpan sebagai <code>null</code> di database. Ini akan di-fix dengan:</p>
        <ol>
            <li>Update semua record Apri yang user_id-nya null</li>
            <li>Auto-fix semua record lain yang user_id-nya null</li>
            <li>Pastikan jobsheet_save.php sudah diperbaiki</li>
        </ol>
    </div>
    
    <div class='success'>
        <h3>✅ Status Perbaikan:</h3>
        <ul>
            <li><strong>jobsheet_save.php:</strong> ✅ Sudah diperbaiki dengan auto-fill user_id</li>
            <li><strong>Data Apri:</strong> ⏳ Akan di-fix saat tombol ditekan</li>
            <li><strong>Auto-fix:</strong> ⏳ Akan dijalankan otomatis</li>
        </ul>
    </div>
</body>
</html>


