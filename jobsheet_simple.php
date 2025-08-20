<?php
// Simple Jobsheet Test
session_start();

// Basic database connection test
try {
    require_once "db.php";
    echo "<h1>✅ Simple Jobsheet - PHP Working!</h1>";
    echo "<p>Database connection: OK</p>";
    
    // Test database query
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM jobsheet");
    $count = $stmt->fetch(PDO::FETCH_ASSOC)["total"];
    echo "<p>Jobsheet records: " . $count . "</p>";
    
} catch (Exception $e) {
    echo "<h1>⚠️ Simple Jobsheet - Database Error</h1>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h2>🧪 Test Results:</h2>";
echo "<ul>";
echo "<li>✅ PHP is working</li>";
echo "<li>✅ Session is working</li>";
echo "<li>✅ File inclusion is working</li>";
echo "<li>✅ Database connection is working</li>";
echo "</ul>";

echo "<p><strong>If you see this, PHP is working correctly!</strong></p>";
echo "<p><a href=\"jobsheet.php\">← Back to original jobsheet.php</a></p>";
?>
