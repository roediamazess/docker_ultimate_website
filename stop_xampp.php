<?php
header('Content-Type: application/json; charset=utf-8');

try {
    // Check if we're on Windows
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        // Stop Apache
        exec('taskkill /F /IM httpd.exe 2>NUL', $output, $stopApacheCode);
        
        // Stop MySQL
        exec('taskkill /F /IM mysqld.exe 2>NUL', $output, $stopMysqlCode);
        
        // Wait a moment
        sleep(2);
        
        // Check if services are stopped
        $apacheStopped = true;
        $mysqlStopped = true;
        
        exec('tasklist /FI "IMAGENAME eq httpd.exe" 2>NUL', $output, $returnCode);
        if ($returnCode === 0 && count($output) > 1) {
            $apacheStopped = false;
        }
        
        exec('tasklist /FI "IMAGENAME eq mysqld.exe" 2>NUL', $output, $returnCode);
        if ($returnCode === 0 && count($output) > 1) {
            $mysqlStopped = false;
        }
        
        echo json_encode([
            'ok' => true,
            'message' => 'XAMPP stop commands executed',
            'apache' => [
                'stopped' => $apacheStopped,
                'stopCode' => $stopApacheCode
            ],
            'mysql' => [
                'stopped' => $mysqlStopped,
                'stopCode' => $stopMysqlCode
            ],
            'note' => 'Services stopped. Use XAMPP Control Panel to start them again.'
        ]);
        
    } else {
        // Linux/Unix systems
        exec('sudo systemctl stop apache2 2>&1', $apacheOutput, $apacheCode);
        exec('sudo systemctl stop mysql 2>&1', $mysqlOutput, $mysqlCode);
        
        echo json_encode([
            'ok' => true,
            'message' => 'XAMPP stop commands executed on Linux/Unix',
            'apache' => [
                'code' => $apacheCode,
                'output' => $apacheOutput
            ],
            'mysql' => [
                'code' => $mysqlCode,
                'output' => $mysqlOutput
            ]
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'ok' => false,
        'error' => $e->getMessage()
    ]);
}
?>

