<?php
session_start();
require_once 'db.php';
require_once 'user_utils.php';

// Set a test user session for testing
$_SESSION['user_id'] = 1;
$_SESSION['user_email'] = 'test@example.com';
$_SESSION['user_role'] = 'Administrator';

// Test logging
log_user_activity('test_action', 'This is a test log entry');

echo "Test log entry created successfully.<br>";

// Check if the log entry was inserted
$stmt = $pdo->query("SELECT * FROM logs WHERE action = 'test_action' ORDER BY id DESC LIMIT 1");
$log = $stmt->fetch(PDO::FETCH_ASSOC);

if ($log) {
    echo "Log entry found in database:<br>";
    echo "<pre>";
    print_r($log);
    echo "</pre>";
} else {
    echo "Log entry not found in database.<br>";
}

// Also check the last few log entries
echo "<h3>Last 5 log entries:</h3>";
$stmt = $pdo->query("SELECT * FROM logs ORDER BY id DESC LIMIT 5");
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<pre>";
print_r($logs);
echo "</pre>";
?>