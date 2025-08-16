<?php
echo "<h2>🧪 Test API Endpoint</h2>";

// Test dengan project ID yang sudah ada
$test_project_id = 'PRJ999';

echo "<h3>Testing Project ID: $test_project_id</h3>";

// Simulate POST request
$_POST['project_id'] = $test_project_id;

// Include the API file
ob_start();
include 'check_project_id_uniqueness.php';
$output = ob_get_clean();

echo "<h4>Raw Output:</h4>";
echo "<pre>" . htmlspecialchars($output) . "</pre>";

// Try to decode JSON
$decoded = json_decode($output, true);
if ($decoded) {
    echo "<h4>Decoded JSON:</h4>";
    echo "<pre>" . print_r($decoded, true) . "</pre>";
    
    if ($decoded['exists']) {
        echo "<p style='color: red;'>❌ Project ID sudah ada di database</p>";
        if (isset($decoded['project_info'])) {
            echo "<h5>Project Info:</h5>";
            echo "<ul>";
            foreach ($decoded['project_info'] as $key => $value) {
                echo "<li><strong>$key:</strong> " . htmlspecialchars($value ?: 'N/A') . "</li>";
            }
            echo "</ul>";
        }
    } else {
        echo "<p style='color: green;'>✅ Project ID tersedia</p>";
    }
} else {
    echo "<p style='color: red;'>❌ Gagal decode JSON</p>";
    echo "<p>JSON Error: " . json_last_error_msg() . "</p>";
}

echo "<hr>";

// Test dengan project ID yang tidak ada
$test_project_id_2 = 'PRJ_NEW_001';
echo "<h3>Testing Project ID: $test_project_id_2</h3>";

$_POST['project_id'] = $test_project_id_2;

ob_start();
include 'check_project_id_uniqueness.php';
$output2 = ob_get_clean();

echo "<h4>Raw Output:</h4>";
echo "<pre>" . htmlspecialchars($output2) . "</pre>";

$decoded2 = json_decode($output2, true);
if ($decoded2) {
    echo "<h4>Decoded JSON:</h4>";
    echo "<pre>" . print_r($decoded2, true) . "</pre>";
    
    if ($decoded2['exists']) {
        echo "<p style='color: red;'>❌ Project ID sudah ada di database</p>";
    } else {
        echo "<p style='color: green;'>✅ Project ID tersedia</p>";
    }
} else {
    echo "<p style='color: red;'>❌ Gagal decode JSON</p>";
    echo "<p>JSON Error: " . json_last_error_msg() . "</p>";
}
?>

