<?php
session_start();
require_once 'db.php';
require_once 'user_utils.php';

echo "<h2>Full Activity Creation Process Test</h2>";

// Set up test session variables (as if user is logged in)
$_SESSION['user_id'] = 1;
$_SESSION['user_email'] = 'test@example.com';
$_SESSION['user_role'] = 'Administrator';
$_SESSION['user_display_name'] = 'Test User';

echo "<h3>Session Variables Set</h3>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

// Simulate the CSRF token generation
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
echo "<p>CSRF Token: " . $_SESSION['csrf_token'] . "</p>";

// Simulate the activity creation process
echo "<h3>Simulating Activity Creation Process</h3>";

// This is what happens in activity.php
function log_activity($action, $description) {
    log_user_activity($action, $description);
}

// Simulate form data
$_POST['create'] = true;
$_POST['csrf_token'] = $_SESSION['csrf_token'];
$_POST['project_id'] = null;
$_POST['no'] = 100;
$_POST['information_date'] = date('Y-m-d');
$_POST['user_position'] = 'Test Position';
$_POST['department'] = 'IT / EDP';
$_POST['application'] = 'Test App';
$_POST['type'] = 'Issue';
$_POST['description'] = 'Test activity creation';
$_POST['action_solution'] = 'Test solution';
$_POST['due_date'] = null;
$_POST['status'] = 'Open';
$_POST['cnc_number'] = '';
$_POST['priority'] = 'Normal';
$_POST['customer'] = null;
$_POST['project'] = null;

// CSRF verification (simplified)
function csrf_verify() {
    return isset($_POST['csrf_token']) && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']);
}

// Simulate the create activity process
if (isset($_POST['create'])) {
    echo "<p>Create activity process started...</p>";
    
    if (!csrf_verify()) {
        echo "<p style='color: red;'>ERROR: CSRF token tidak valid!</p>";
    } else {
        echo "<p style='color: green;'>SUCCESS: CSRF token verified.</p>";
        
        // Default Information Date ke hari ini jika kosong (berlaku hanya untuk CREATE)
        $informationDate = !empty($_POST['information_date']) ? $_POST['information_date'] : date('Y-m-d');
        $typeVal = $_POST['type'] ?? '';
        $dueDateInput = isset($_POST['due_date']) ? trim((string)$_POST['due_date']) : '';
        // Edit: jangan override due date; jika kosong biarkan NULL (tetap sesuai terakhir tersimpan jika tidak diubah)
        $dueDate = $dueDateInput !== '' ? $dueDateInput : null;
        if (!empty($informationDate) && !empty($dueDate)) {
            try {
                $inf = new DateTime($informationDate);
                $due = new DateTime($dueDate);
                if ($due < $inf) { $dueDate = $inf->format('Y-m-d'); }
            } catch (Exception $e) { }
        }
        
        echo "<p>Information Date: " . $informationDate . "</p>";
        echo "<p>Type: " . $typeVal . "</p>";
        echo "<p>Due Date: " . ($dueDate ?? 'null') . "</p>";
        
        // Log the activity creation (this is what should happen)
        echo "<p>Logging activity creation...</p>";
        log_activity('create_activity', 'Activity: ' . $_POST['type']);
        
        echo "<p style='color: green;'>SUCCESS: Activity creation logged.</p>";
        
        // Check if the log entry was inserted
        echo "<h3>Checking Database for Log Entry</h3>";
        try {
            $stmt = $pdo->prepare("SELECT * FROM logs WHERE action = ? ORDER BY id DESC LIMIT 1");
            $stmt->execute(['create_activity']);
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
    }
} else {
    echo "<p>No create activity request.</p>";
}
?>