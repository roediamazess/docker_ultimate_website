<?php
session_start();
require_once 'db.php';

echo "<h2>🐛 Debug Project ID Validation</h2>";

// Check session
echo "<h3>🔐 Session Check:</h3>";
if (isset($_SESSION['user_id'])) {
    echo "<p style='color: green;'>✅ Session user_id: " . htmlspecialchars($_SESSION['user_id']) . "</p>";
} else {
    echo "<p style='color: red;'>❌ Session user_id tidak ada</p>";
}

if (isset($_SESSION['email'])) {
    echo "<p style='color: green;'>✅ Session email: " . htmlspecialchars($_SESSION['email']) . "</p>";
} else {
    echo "<p style='color: red;'>❌ Session email tidak ada</p>";
}

// Check database connection
echo "<h3>🗄️ Database Connection:</h3>";
try {
    $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    echo "<p style='color: green;'>✅ Database driver: $driver</p>";
    
    // Test query
    $stmt = $pdo->query("SELECT COUNT(*) FROM projects");
    $count = $stmt->fetchColumn();
    echo "<p style='color: green;'>✅ Total projects in database: $count</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Database error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Check if PRJ999 exists
echo "<h3>🔍 Check Project PRJ999:</h3>";
try {
    $stmt = $pdo->prepare("SELECT * FROM projects WHERE project_id = ?");
    $stmt->execute(['PRJ999']);
    $project = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($project) {
        echo "<p style='color: green;'>✅ Project PRJ999 ditemukan:</p>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>Field</th><th>Value</th></tr>";
        foreach ($project as $key => $value) {
            echo "<tr>";
            echo "<td>$key</td>";
            echo "<td>" . htmlspecialchars($value ?: 'NULL') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: red;'>❌ Project PRJ999 tidak ditemukan</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error querying project: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Test the API endpoint directly
echo "<h3>🧪 Test API Endpoint:</h3>";

// Simulate POST request
$_POST['project_id'] = 'PRJ999';

// Capture output
ob_start();
include 'check_project_id_uniqueness.php';
$api_output = ob_get_clean();

echo "<h4>API Output:</h4>";
echo "<pre>" . htmlspecialchars($api_output) . "</pre>";

// Try to decode JSON
$json_result = json_decode($api_output, true);
if ($json_result) {
    echo "<h4>Decoded Result:</h4>";
    echo "<pre>" . print_r($json_result, true) . "</pre>";
    
    if ($json_result['exists']) {
        echo "<p style='color: green;'>✅ API berfungsi - Project ID sudah ada</p>";
    } else {
        echo "<p style='color: red;'>❌ API error - Project ID seharusnya ada</p>";
    }
} else {
    echo "<p style='color: red;'>❌ Gagal decode JSON</p>";
    echo "<p>JSON Error: " . json_last_error_msg() . "</p>";
}

// Check if there are any PHP errors
echo "<h3>⚠️ PHP Error Check:</h3>";
$error_log = error_get_last();
if ($error_log) {
    echo "<p style='color: red;'>❌ PHP Error: " . htmlspecialchars($error_log['message']) . "</p>";
    echo "<p>File: " . htmlspecialchars($error_log['file']) . "</p>";
    echo "<p>Line: " . $error_log['line'] . "</p>";
} else {
    echo "<p style='color: green;'>✅ Tidak ada PHP error</p>";
}

// Check if access_control.php exists and works
echo "<h3>🔒 Access Control Check:</h3>";
if (file_exists('access_control.php')) {
    echo "<p style='color: green;'>✅ File access_control.php ada</p>";
    
    // Check if require_login function exists
    if (function_exists('require_login')) {
        echo "<p style='color: green;'>✅ Function require_login tersedia</p>";
    } else {
        echo "<p style='color: red;'>❌ Function require_login tidak tersedia</p>";
    }
} else {
    echo "<p style='color: red;'>❌ File access_control.php tidak ada</p>";
}

echo "<h3>🎯 Next Steps:</h3>";
echo "<ol>";
echo "<li>Jalankan file ini untuk melihat debug info</li>";
echo "<li>Periksa apakah ada error di console browser</li>";
echo "<li>Periksa Network tab di DevTools untuk melihat AJAX request</li>";
echo "<li>Pastikan session user sudah login</li>";
echo "</ol>";
?>

