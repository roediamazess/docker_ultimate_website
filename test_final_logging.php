<?php
session_start();
require_once 'db.php';
require_once 'user_utils.php';

echo "<h2>Final Logging Test</h2>";

// Set up test session variables
$_SESSION['user_id'] = 1;
$_SESSION['user_email'] = 'test@example.com';
$_SESSION['user_role'] = 'Administrator';

echo "<h3>Session Variables</h3>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

// Test 1: Direct database insert
echo "<h3>Test 1: Direct Database Insert</h3>";
try {
    $stmt = $pdo->prepare('INSERT INTO logs (user_id, user_email, action, description, ip, user_agent, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())');
    $result = $stmt->execute([1, 'test@example.com', 'direct_insert_test', 'Direct database insert test', '127.0.0.1', 'Test User Agent']);
    
    if ($result) {
        echo "<p style='color: green;'>SUCCESS: Direct database insert succeeded.</p>";
        $lastId = $pdo->lastInsertId();
        
        // Retrieve the inserted row
        $stmt = $pdo->prepare("SELECT * FROM logs WHERE id = ?");
        $stmt->execute([$lastId]);
        $log = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($log) {
            echo "<p>Inserted log entry:</p>";
            echo "<pre>";
            print_r($log);
            echo "</pre>";
        }
    } else {
        echo "<p style='color: red;'>ERROR: Direct database insert failed.</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>ERROR: Direct database insert failed with exception: " . $e->getMessage() . "</p>";
}

// Test 2: Using log_user_activity function
echo "<h3>Test 2: Using log_user_activity Function</h3>";
try {
    log_user_activity('function_test', 'Testing log_user_activity function');
    echo "<p>log_user_activity function called.</p>";
    
    // Check if the log entry was inserted
    $stmt = $pdo->prepare("SELECT * FROM logs WHERE action = ? ORDER BY id DESC LIMIT 1");
    $stmt->execute(['function_test']);
    $log = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($log) {
        echo "<p style='color: green;'>SUCCESS: Function test log entry found:</p>";
        echo "<pre>";
        print_r($log);
        echo "</pre>";
    } else {
        echo "<p style='color: red;'>ERROR: Function test log entry not found.</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>ERROR: Function test failed with exception: " . $e->getMessage() . "</p>";
}

// Test 3: Using log_activity function (as in activity.php)
echo "<h3>Test 3: Using log_activity Function (as in activity.php)</h3>";
function log_activity($action, $description) {
    log_user_activity($action, $description);
}

try {
    log_activity('activity_test', 'Testing log_activity function from activity.php');
    echo "<p>log_activity function called.</p>";
    
    // Check if the log entry was inserted
    $stmt = $pdo->prepare("SELECT * FROM logs WHERE action = ? ORDER BY id DESC LIMIT 1");
    $stmt->execute(['activity_test']);
    $log = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($log) {
        echo "<p style='color: green;'>SUCCESS: Activity test log entry found:</p>";
        echo "<pre>";
        print_r($log);
        echo "</pre>";
    } else {
        echo "<p style='color: red;'>ERROR: Activity test log entry not found.</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>ERROR: Activity test failed with exception: " . $e->getMessage() . "</p>";
}

// Test 4: Check all recent log entries
echo "<h3>Test 4: All Recent Log Entries</h3>";
try {
    $stmt = $pdo->query("SELECT * FROM logs ORDER BY id DESC LIMIT 10");
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($logs) {
        echo "<p>Recent log entries:</p>";
        echo "<pre>";
        print_r($logs);
        echo "</pre>";
    } else {
        echo "<p>No log entries found.</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>ERROR: Failed to retrieve log entries: " . $e->getMessage() . "</p>";
}

echo "<h3>Test Completed</h3>";
echo "<p>If you can see log entries above, the logging mechanism is working correctly.</p>";
echo "<p>If you don't see log entries, there may be an issue with the database connection or permissions.</p>";
?>