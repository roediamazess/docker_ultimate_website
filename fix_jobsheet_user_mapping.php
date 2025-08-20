<?php
require_once 'db.php';

echo "<h1>🔧 Fix Jobsheet User ID Mapping</h1>";

try {
    echo "<h2>1. Check current jobsheet data with null user_id</h2>";
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM jobsheet WHERE user_id IS NULL OR user_id = ''");
    $nullCount = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Records with null user_id: {$nullCount['total']}<br>";
    
    if ($nullCount['total'] > 0) {
        echo "<h3>Sample records with null user_id:</h3>";
        $stmt = $pdo->query("SELECT * FROM jobsheet WHERE user_id IS NULL OR user_id = '' LIMIT 5");
        $nullRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "<pre>" . print_r($nullRecords, true) . "</pre>";
    }
    
    echo "<br><h2>2. Check users table for PIC names</h2>";
    $stmt = $pdo->query("SELECT user_id, full_name FROM users ORDER BY full_name");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Total users: " . count($users) . "<br>";
    
    if (count($users) > 0) {
        echo "<h3>Available users:</h3>";
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>user_id</th><th>full_name</th></tr>";
        foreach ($users as $user) {
            echo "<tr>";
            echo "<td>{$user['user_id']}</td>";
            echo "<td>{$user['full_name']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    echo "<br><h2>3. Create mapping for PIC names to user_id</h2>";
    
    // Buat mapping berdasarkan nama yang cocok
    $picToUserId = [];
    foreach ($users as $user) {
        $picToUserId[$user['user_id']] = $user['user_id']; // user_id sebagai PIC name
        $picToUserId[$user['full_name']] = $user['user_id']; // full_name sebagai PIC name
        
        // Tambahkan mapping untuk nama pendek (first name)
        $firstName = explode(' ', $user['full_name'])[0];
        if ($firstName !== $user['full_name']) {
            $picToUserId[$firstName] = $user['user_id'];
        }
    }
    
    echo "<h3>PIC to User ID mapping:</h3>";
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>PIC Name</th><th>User ID</th></tr>";
    foreach ($picToUserId as $picName => $userId) {
        echo "<tr>";
        echo "<td>$picName</td>";
        echo "<td>$userId</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<br><h2>4. Update jobsheet records with correct user_id</h2>";
    $pdo->beginTransaction();
    
    $updateCount = 0;
    $stmt = $pdo->prepare("UPDATE jobsheet SET user_id = ? WHERE pic_name = ? AND (user_id IS NULL OR user_id = '')");
    
    foreach ($picToUserId as $picName => $userId) {
        $updateStmt = $pdo->prepare("UPDATE jobsheet SET user_id = ? WHERE pic_name = ? AND (user_id IS NULL OR user_id = '')");
        $updateStmt->execute([$userId, $picName]);
        $affected = $updateStmt->rowCount();
        if ($affected > 0) {
            echo "✅ Updated $affected records for PIC '$picName' with user_id '$userId'<br>";
            $updateCount += $affected;
        }
    }
    
    $pdo->commit();
    echo "<br>🎉 Total updated records: $updateCount<br>";
    
    echo "<br><h2>5. Verify final results</h2>";
    $stmt = $pdo->query("SELECT pic_name, COUNT(*) as total, 
                         COUNT(CASE WHEN user_id IS NOT NULL AND user_id != '' THEN 1 END) as with_user_id,
                         COUNT(CASE WHEN user_id IS NULL OR user_id = '' THEN 1 END) as without_user_id
                         FROM jobsheet 
                         GROUP BY pic_name 
                         ORDER BY pic_name");
    $summary = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>PIC</th><th>Total</th><th>With User ID</th><th>Without User ID</th></tr>";
    foreach ($summary as $row) {
        $status = $row['without_user_id'] > 0 ? '⚠️' : '✅';
        echo "<tr>";
        echo "<td>{$row['pic_name']} $status</td>";
        echo "<td>{$row['total']}</td>";
        echo "<td>{$row['with_user_id']}</td>";
        echo "<td>{$row['without_user_id']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<br><h2>6. Create JavaScript mapping for frontend</h2>";
    echo "<div style='background: #f0f9ff; border: 1px solid #0ea5e9; padding: 15px; border-radius: 8px;'>";
    echo "<h3>📋 Add this JavaScript code to jobsheet.php:</h3>";
    echo "<pre style='background: #1e293b; color: #e2e8f0; padding: 15px; border-radius: 8px; overflow-x: auto;'>";
    echo "// PIC to User ID mapping\n";
    echo "const picNameToUserId = {\n";
    foreach ($picToUserId as $picName => $userId) {
        echo "    '$picName': '$userId',\n";
    }
    echo "};\n";
    echo "</pre>";
    echo "<p><strong>Note:</strong> Tambahkan kode ini sebelum fungsi <code>getCellMeta</code> di file jobsheet.php</p>";
    echo "</div>";
    
} catch (Exception $e) {
    if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
        try { $pdo->rollBack(); } catch (Throwable $__) {}
    }
    echo "❌ Error: " . $e->getMessage() . "<br>";
    echo "Stack trace: <pre>" . $e->getTraceAsString() . "</pre>";
}
?>


