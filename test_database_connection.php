<?php
echo "<h2>🗄️ Database Connection Test</h2>";

try {
    // Test if we can include db.php
    if (file_exists('db.php')) {
        echo "<p>✅ db.php file exists</p>";
        
        // Try to include it
        require_once 'db.php';
        
        if (isset($pdo)) {
            echo "<p>✅ Database connection object created</p>";
            
            // Test database connection
            $stmt = $pdo->query("SELECT 1 as test");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result && $result['test'] == 1) {
                echo "<p>✅ Database connected successfully!</p>";
                
                // Get database info
                $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
                $serverVersion = $pdo->getAttribute(PDO::ATTR_SERVER_VERSION);
                
                echo "<p><strong>Database Driver:</strong> " . $driver . "</p>";
                echo "<p><strong>Server Version:</strong> " . $serverVersion . "</p>";
                
                // Test jobsheet table
                try {
                    $stmt = $pdo->query("SELECT COUNT(*) as total FROM jobsheet");
                    $count = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
                    echo "<p>✅ jobsheet table accessible - Total records: " . $count . "</p>";
                } catch (Exception $e) {
                    echo "<p>❌ jobsheet table error: " . $e->getMessage() . "</p>";
                }
                
            } else {
                echo "<p>❌ Database query failed</p>";
            }
        } else {
            echo "<p>❌ Database connection object not created</p>";
        }
    } else {
        echo "<p>❌ db.php file not found</p>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><strong>Time:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "<p><strong>PHP Version:</strong> " . phpversion() . "</p>";
?>

