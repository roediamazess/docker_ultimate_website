<?php
header('Content-Type: application/json; charset=utf-8');

try {
    $testContent = '<?php
echo "<h1>🧪 PHP Test File</h1>";
echo "<p>PHP Version: " . phpversion() . "</p>";
echo "<p>Server: " . $_SERVER["SERVER_SOFTWARE"] . "</p>";
echo "<p>Time: " . date("Y-m-d H:i:s") . "</p>";
echo "<p>✅ If you see this, PHP is working!</p>";
?>';
    
    if (file_put_contents('test.php', $testContent)) {
        echo json_encode([
            'ok' => true,
            'message' => 'test.php created successfully'
        ]);
    } else {
        echo json_encode([
            'ok' => false,
            'error' => 'Failed to write test.php file'
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'ok' => false,
        'error' => $e->getMessage()
    ]);
}
?>

