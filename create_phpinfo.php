<?php
header('Content-Type: application/json; charset=utf-8');

try {
    $phpinfoContent = '<?php
phpinfo();
?>';
    
    if (file_put_contents('phpinfo.php', $phpinfoContent)) {
        echo json_encode([
            'ok' => true,
            'message' => 'phpinfo.php created successfully'
        ]);
    } else {
        echo json_encode([
            'ok' => false,
            'error' => 'Failed to write phpinfo.php file'
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'ok' => false,
        'error' => $e->getMessage()
    ]);
}
?>

