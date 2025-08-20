<?php
header('Content-Type: application/json; charset=utf-8');

try {
    // Check if we're on Windows
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        $xamppPath = 'C:\xampp';
        
        if (is_dir($xamppPath)) {
            // Stop Apache
            exec('taskkill /F /IM httpd.exe 2>NUL', $output, $stopApacheCode);
            
            // Stop MySQL
            exec('taskkill /F /IM mysqld.exe 2>NUL', $output, $stopMysqlCode);
            
            // Wait a moment
            sleep(3);
            
            // Start Apache
            $apacheCmd = '"' . $xamppPath . '\apache\bin\httpd.exe" -k start';
            exec($apacheCmd, $apacheOutput, $startApacheCode);
            
            // Start MySQL
            $mysqlCmd = '"' . $xamppPath . '\mysql\bin\mysqld.exe" --defaults-file="' . $xamppPath . '\mysql\bin\my.ini"';
            exec($mysqlCmd, $mysqlOutput, $startMysqlCode);
            
            // Wait for services to start
            sleep(5);
            
            // Check status
            $apacheRunning = false;
            $mysqlRunning = false;
            
            exec('tasklist /FI "IMAGENAME eq httpd.exe" 2>NUL', $output, $returnCode);
            if ($returnCode === 0 && count($output) > 1) {
                $apacheRunning = true;
            }
            
            exec('tasklist /FI "IMAGENAME eq mysqld.exe" 2>NUL', $output, $returnCode);
            if ($returnCode === 0 && count($output) > 1) {
                $mysqlRunning = true;
            }
            
            echo json_encode([
                'ok' => true,
                'message' => 'XAMPP restart completed',
                'apache' => [
                    'stopped' => ($stopApacheCode === 0),
                    'started' => $apacheRunning,
                    'startCode' => $startApacheCode
                ],
                'mysql' => [
                    'stopped' => ($stopMysqlCode === 0),
                    'started' => $mysqlRunning,
                    'startCode' => $startMysqlCode
                ],
                'note' => 'If services are not running, start them manually from XAMPP Control Panel'
            ]);
            
        } else {
            echo json_encode([
                'ok' => false,
                'error' => 'XAMPP directory not found at ' . $xamppPath
            ]);
        }
    } else {
        // Linux/Unix systems
        exec('sudo systemctl restart apache2 2>&1', $apacheOutput, $apacheCode);
        exec('sudo systemctl restart mysql 2>&1', $mysqlOutput, $mysqlCode);
        
        echo json_encode([
            'ok' => true,
            'message' => 'XAMPP restart completed on Linux/Unix',
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

