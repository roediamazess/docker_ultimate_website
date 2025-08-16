<?php
session_start();
require_once 'db.php';
require_once 'user_utils.php';

echo "<h2>Debug Logging Test</h2>";

// Check if we have a database connection
echo "<p>Checking database connection...</p>";
if (!$pdo) {
    echo "<p style='color: red;'>ERROR: No database connection.</p>";
    exit;
} else {
    echo "<p style='color: green;'>SUCCESS: Database connection established.</p>";
}

// Check if we have session variables
echo "<h3>Session Variables Check</h3>";
echo "<p>User ID: " . ($_SESSION['user_id'] ?? 'Not set') . "</p>";
echo "<p>User Email: " . ($_SESSION['user_email'] ?? 'Not set') . "</p>";
echo "<p>User Role: " . ($_SESSION['user_role'] ?? 'Not set') . "</p>";

// If we don't have session variables, set them for testing
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1;
    $_SESSION['user_email'] = 'test@example.com';
    $_SESSION['user_role'] = 'Administrator';
    echo "<p>Setting test session variables...</p>";
}

// Test logging
echo "<h3>Testing log_user_activity function...</h3>";

// Call the logging function directly
log_user_activity('debug_test', 'This is a debug test entry');

echo "<p>Log entry attempted. Checking database...</p>";

// Check if the log entry was inserted
try {
    $stmt = $pdo->prepare("SELECT * FROM logs WHERE action = ? ORDER BY id DESC LIMIT 1");
    $stmt->execute(['debug_test']);
    $log = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($log) {
        echo "<p style='color: green;'>SUCCESS: Log entry found in database:</p>";
        echo "<pre>";
        print_r($log);
        echo "</pre>";
    } else {
        echo "<p style='color: red;'>ERROR: Log entry not found in database.</p>";
        
        // Check the last few log entries to see if anything is being logged
        echo "<h3>Last 5 log entries in database:</h3>";
        $stmt = $pdo->query("SELECT * FROM logs ORDER BY id DESC LIMIT 5");
        $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if ($logs) {
            echo "<pre>";
            print_r($logs);
            echo "</pre>";
        } else {
            echo "<p>No log entries found in database.</p>";
        }
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>ERROR: Database query failed: " . $e->getMessage() . "</p>";
}

// Also test the IP detection
echo "<h3>IP Address Detection Test</h3>";
echo "<p>HTTP_CLIENT_IP: " . ($_SERVER['HTTP_CLIENT_IP'] ?? 'Not set') . "</p>";
echo "<p>HTTP_X_FORWARDED_FOR: " . ($_SERVER['HTTP_X_FORWARDED_FOR'] ?? 'Not set') . "</p>";
echo "<p>REMOTE_ADDR: " . ($_SERVER['REMOTE_ADDR'] ?? 'Not set') . "</p>";

// Test the function that's used in activity.php
echo "<h3>Testing log_activity function</h3>";
function log_activity($action, $description) {
    log_user_activity($action, $description);
}

log_activity('test_activity_function', 'Testing the log_activity function from activity.php');

echo "<p>Test of log_activity function completed.</p>";

// Now let's simulate what happens in activity.php
echo "<h3>Simulating Activity Creation Process</h3>";

// Simulate the create process
if (true) { // Simulating the if (isset($_POST['create'])) condition
    echo "<p>Simulating activity creation...</p>";
    
    // This is what happens in activity.php line 93
    log_activity('create_activity', 'Activity: Test Type');
    
    echo "<p>Activity creation logged. Checking database...</p>";
    
    // Check if this log entry was inserted
    try {
        $stmt = $pdo->prepare("SELECT * FROM logs WHERE action = ? ORDER BY id DESC LIMIT 1");
        $stmt->execute(['create_activity']);
        $log = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($log) {
            echo "<p style='color: green;'>SUCCESS: Create activity log entry found in database:</p>";
            echo "<pre>";
            print_r($log);
            echo "</pre>";
        } else {
            echo "<p style='color: red;'>ERROR: Create activity log entry not found in database.</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>ERROR: Database query failed: " . $e->getMessage() . "</p>";
    }
}
?>