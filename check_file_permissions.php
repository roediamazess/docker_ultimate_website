<?php
header('Content-Type: application/json; charset=utf-8');

try {
    $files = [
        'jobsheet.php',
        'db.php',
        'access_control.php'
    ];
    
    $results = [];
    $allOk = true;
    
    foreach ($files as $file) {
        if (file_exists($file)) {
            $perms = fileperms($file);
            $readable = is_readable($file);
            $writable = is_writable($file);
            
            $results[$file] = [
                'exists' => true,
                'permissions' => substr(sprintf('%o', $perms), -4),
                'readable' => $readable,
                'writable' => $writable,
                'size' => filesize($file)
            ];
            
            if (!$readable || !$writable) {
                $allOk = false;
            }
        } else {
            $results[$file] = ['exists' => false];
            $allOk = false;
        }
    }
    
    echo json_encode([
        'ok' => $allOk,
        'files' => $results,
        'message' => $allOk ? 'All file permissions are correct' : 'Some file permission issues found'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'ok' => false,
        'error' => $e->getMessage()
    ]);
}
?>

