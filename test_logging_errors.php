<?php
session_start();
require_once 'db.php';

echo "<h2>Logging Error Test</h2>";

// Set test user session
$_SESSION['user_id'] = 1;
$_SESSION['user_email'] = 'test@example.com';
$_SESSION['user_role'] = 'Administrator';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Capture error log output
ob_start();

// Test logging with error capture
echo "<h3>Testing log_user_activity function with error capture...</h3>";

// Define the log_user_activity function with error capture
function log_user_activity($action, $description = '') {
    global $pdo;
    
    echo "<p>Inside log_user_activity function</p>";
    
    try {
        if (!$pdo) {
            echo "<p style='color: red;'>ERROR: No database connection in log_user_activity.</p>";
            return;
        }
        
        echo "<p>Database connection available</p>";
        
        $user_id = $_SESSION['user_id'] ?? null;
        $user_email = $_SESSION['user_email'] ?? null;
        
        echo "<p>User ID: " . ($user_id ?? 'null') . "</p>";
        echo "<p>User Email: " . ($user_email ?? 'null') . "</p>";
        
        // Improved IP address detection
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        }
        
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        echo "<p>IP: " . $ip . "</p>";
        echo "<p>User Agent: " . $ua . "</p>";
        echo "<p>Action: " . $action . "</p>";
        echo "<p>Description: " . $description . "</p>";
        
        if ($pdo) {
            echo "<p>Preparing INSERT statement...</p>";
            $stmt = $pdo->prepare('INSERT INTO logs (user_id, user_email, action, description, ip, user_agent, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())');
            echo "<p>Executing INSERT statement...</p>";
            $result = $stmt->execute([$user_id, $user_email, $action, $description, $ip, $ua]);
            
            if ($result) {
                echo "<p style='color: green;'>SUCCESS: Log entry inserted.</p>";
            } else {
                echo "<p style='color: red;'>ERROR: Log entry insert failed.</p>";
            }
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>ERROR: Exception in log_user_activity: " . $e->getMessage() . "</p>";
        echo "<p>Stack trace: " . $e->getTraceAsString() . "</p>";
    }
}

// Call the logging function directly
log_user_activity('error_test', 'This is an error test entry');

echo "<p>Log entry attempted.</p>";

// Get the error log output
$errorLog = ob_get_contents();
ob_end_clean();

if (!empty($errorLog)) {
    echo "<h3>Error Log Output:</h3>";
    echo "<pre>" . htmlspecialchars($errorLog) . "</pre>";
} else {
    echo "<p>No error log output captured.</p>";
}

// Check if the log entry was inserted
echo "<h3>Checking Database for Log Entry</h3>";
try {
    $stmt = $pdo->prepare("SELECT * FROM logs WHERE action = ? ORDER BY id DESC LIMIT 1");
    $stmt->execute(['error_test']);
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