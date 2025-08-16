<?php
session_start();
require_once 'db.php';

echo "<h2>Direct Database Insert Test</h2>";

// Check if we have a database connection
echo "<p>Checking database connection...</p>";
if (!$pdo) {
    echo "<p style='color: red;'>ERROR: No database connection.</p>";
    exit;
} else {
    echo "<p style='color: green;'>SUCCESS: Database connection established.</p>";
}

// Try to insert a log entry directly
echo "<h3>Testing Direct Insert into Logs Table</h3>";

try {
    $stmt = $pdo->prepare('INSERT INTO logs (user_id, user_email, action, description, ip, user_agent, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())');
    $result = $stmt->execute([1, 'test@example.com', 'direct_test', 'This is a direct test entry', '127.0.0.1', 'Test User Agent']);
    
    if ($result) {
        echo "<p style='color: green;'>SUCCESS: Direct insert into logs table succeeded.</p>";
        
        // Get the ID of the inserted row
        $lastId = $pdo->lastInsertId();
        echo "<p>Inserted row ID: " . $lastId . "</p>";
        
        // Try to retrieve the inserted row
        $stmt = $pdo->prepare("SELECT * FROM logs WHERE id = ?");
        $stmt->execute([$lastId]);
        $log = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($log) {
            echo "<p style='color: green;'>SUCCESS: Retrieved inserted log entry:</p>";
            echo "<pre>";
            print_r($log);
            echo "</pre>";
        } else {
            echo "<p style='color: red;'>ERROR: Could not retrieve inserted log entry.</p>";
        }
    } else {
        echo "<p style='color: red;'>ERROR: Direct insert into logs table failed.</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>ERROR: Direct insert failed with exception: " . $e->getMessage() . "</p>";
    
    // Let's also check if the logs table exists
    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM logs");
        $count = $stmt->fetchColumn();
        echo "<p>Logs table exists and contains " . $count . " entries.</p>";
    } catch (Exception $e2) {
        echo "<p style='color: red;'>ERROR: Logs table may not exist or is not accessible: " . $e2->getMessage() . "</p>";
    }
}
?>