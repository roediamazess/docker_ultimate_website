<?php
// Test API endpoint secara langsung tanpa session conflict
require_once 'db.php';

echo "<h2>🧪 Clean API Test</h2>";

// Test dengan project ID yang sudah ada
$test_project_id = 'PRJ999';

echo "<h3>Testing Project ID: $test_project_id</h3>";

// Simulate POST request
$_POST['project_id'] = $test_project_id;

// Capture output without session conflict
ob_start();
include 'check_project_id_uniqueness.php';
$api_output = ob_get_clean();

echo "<h4>Raw API Output:</h4>";
echo "<pre>" . htmlspecialchars($api_output) . "</pre>";

// Try to decode JSON
$json_result = json_decode($api_output, true);
if ($json_result) {
    echo "<h4>✅ Decoded JSON Success:</h4>";
    echo "<pre>" . print_r($json_result, true) . "</pre>";
    
    if ($json_result['exists']) {
        echo "<p style='color: red;'>❌ Project ID sudah ada di database</p>";
        if (isset($json_result['project_info'])) {
            echo "<h5>Project Info:</h5>";
            echo "<ul>";
            foreach ($json_result['project_info'] as $key => $value) {
                echo "<li><strong>$key:</strong> " . htmlspecialchars($value ?: 'N/A') . "</li>";
            }
            echo "</ul>";
        }
    } else {
        echo "<p style='color: green;'>✅ Project ID tersedia</p>";
    }
} else {
    echo "<h4>❌ JSON Decode Failed:</h4>";
    echo "<p>JSON Error: " . json_last_error_msg() . "</p>";
    
    // Check for PHP errors or notices
    $error_log = error_get_last();
    if ($error_log) {
        echo "<p style='color: red;'>PHP Error: " . htmlspecialchars($error_log['message']) . "</p>";
        echo "<p>File: " . htmlspecialchars($error_log['file']) . "</p>";
        echo "<p>Line: " . $error_log['line'] . "</p>";
    }
    
    // Try to find where the JSON starts
    $json_start = strpos($api_output, '{');
    if ($json_start !== false) {
        $json_part = substr($api_output, $json_start);
        echo "<h5>JSON Part (from first {):</h5>";
        echo "<pre>" . htmlspecialchars($json_part) . "</pre>";
        
        // Try to decode just the JSON part
        $clean_json = json_decode($json_part, true);
        if ($clean_json) {
            echo "<p style='color: green;'>✅ Clean JSON decode successful!</p>";
            echo "<pre>" . print_r($clean_json, true) . "</pre>";
        } else {
            echo "<p style='color: red;'>❌ Even clean JSON failed: " . json_last_error_msg() . "</p>";
        }
    }
}

echo "<hr>";

// Test dengan project ID yang tidak ada
$test_project_id_2 = 'PRJ_NEW_001';
echo "<h3>Testing Project ID: $test_project_id_2</h3>";

$_POST['project_id'] = $test_project_id_2;

ob_start();
include 'check_project_id_uniqueness.php';
$api_output2 = ob_get_clean();

echo "<h4>Raw API Output:</h4>";
echo "<pre>" . htmlspecialchars($api_output2) . "</pre>";

$json_result2 = json_decode($api_output2, true);
if ($json_result2) {
    echo "<h4>✅ Decoded JSON Success:</h4>";
    echo "<pre>" . print_r($json_result2, true) . "</pre>";
    
    if ($json_result2['exists']) {
        echo "<p style='color: red;'>❌ Project ID sudah ada di database</p>";
    } else {
        echo "<p style='color: green;'>✅ Project ID tersedia</p>";
    }
} else {
    echo "<h4>❌ JSON Decode Failed:</h4>";
    echo "<p>JSON Error: " . json_last_error_msg() . "</p>";
    
    // Try to find where the JSON starts
    $json_start = strpos($api_output2, '{');
    if ($json_start !== false) {
        $json_part = substr($api_output2, $json_start);
        echo "<h5>JSON Part (from first {):</h5>";
        echo "<pre>" . htmlspecialchars($json_part) . "</pre>";
        
        $clean_json = json_decode($json_part, true);
        if ($clean_json) {
            echo "<p style='color: green;'>✅ Clean JSON decode successful!</p>";
            echo "<pre>" . print_r($clean_json, true) . "</pre>";
        }
    }
}

echo "<h3>🎯 Summary:</h3>";
echo "<p>Jika JSON decode berhasil, maka API endpoint sudah berfungsi dengan baik.</p>";
echo "<p>Jika masih gagal, kemungkinan ada whitespace atau output lain sebelum JSON.</p>";
?>

