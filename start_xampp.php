<?php
header('Content-Type: application/json; charset=utf-8');

try {
    // Check if we're on Windows
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        $xamppPath = 'C:\xampp';
        
        if (is_dir($xamppPath)) {
            // Start Apache
            $apacheCmd = '"' . $xamppPath . '\apache\bin\httpd.exe" -k start';
            exec($apacheCmd, $apacheOutput, $apacheCode);
            
            // Start MySQL
            $mysqlCmd = '"' . $xamppPath . '\mysql\bin\mysqld.exe" --defaults-file="' . $xamppPath . '\mysql\bin\my.ini"';
            exec($mysqlCmd, $mysqlOutput, $mysqlCode);
            
            // Wait a moment for services to start
            sleep(3);
            
            // Check if services are running
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
                'message' => 'XAMPP start commands executed',
                'apache' => [
                    'started' => $apacheRunning,
                    'code' => $apacheCode,
                    'output' => $apacheOutput
                ],
                'mysql' => [
                    'started' => $mysqlRunning,
                    'code' => $mysqlCode,
                    'output' => $mysqlOutput
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
        exec('sudo systemctl start apache2 2>&1', $apacheOutput, $apacheCode);
        exec('sudo systemctl start mysql 2>&1', $mysqlOutput, $mysqlCode);
        
        echo json_encode([
            'ok' => true,
            'message' => 'XAMPP start commands executed on Linux/Unix',
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

