<?php
session_start();
require_once 'db.php';
require_once 'user_utils.php';

echo "<h2>Session Variables Test</h2>";

// Check if we have session variables
echo "<h3>Current Session Variables</h3>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

// Check if we have the specific session variables we need
echo "<h3>Required Session Variables</h3>";
echo "<p>User ID: " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'Not set') . "</p>";
echo "<p>User Email: " . (isset($_SESSION['user_email']) ? $_SESSION['user_email'] : 'Not set') . "</p>";
echo "<p>User Role: " . (isset($_SESSION['user_role']) ? $_SESSION['user_role'] : 'Not set') . "</p>";

// Test the get_current_user_* functions
echo "<h3>Testing get_current_user_* functions</h3>";
echo "<p>get_current_user_id(): " . get_current_user_id() . "</p>";
echo "<p>get_current_user_email(): " . get_current_user_email() . "</p>";
echo "<p>get_current_user_display_name(): " . get_current_user_display_name() . "</p>";

// Test logging with these values
echo "<h3>Testing Logging with Current Session Values</h3>";
$user_id = get_current_user_id();
$user_email = get_current_user_email();

echo "<p>Logging with User ID: " . ($user_id ?? 'null') . "</p>";
echo "<p>Logging with User Email: " . ($user_email ?? 'null') . "</p>";

// Try to log an entry
log_user_activity('session_test', 'Testing with current session values');

echo "<p>Log entry attempted. Checking database...</p>";

// Check if the log entry was inserted
try {
    $stmt = $pdo->prepare("SELECT * FROM logs WHERE action = ? ORDER BY id DESC LIMIT 1");
    $stmt->execute(['session_test']);
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
?>