<?php
header('Content-Type: application/json; charset=utf-8');

try {
    // Check if we're on Windows
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        // Try to restart Apache using XAMPP commands
        $xamppPath = 'C:\xampp';
        
        if (is_dir($xamppPath)) {
            // Check if Apache is running
            $output = [];
            exec('tasklist /FI "IMAGENAME eq httpd.exe" 2>NUL', $output, $returnCode);
            
            if ($returnCode === 0 && count($output) > 1) {
                // Apache is running, try to stop it
                exec('taskkill /F /IM httpd.exe 2>NUL', $output, $stopCode);
                
                // Wait a moment
                sleep(2);
                
                // Try to start Apache
                $startCmd = '"' . $xamppPath . '\apache\bin\httpd.exe" -k start';
                exec($startCmd, $output, $startCode);
                
                if ($startCode === 0) {
                    echo json_encode([
                        'ok' => true,
                        'message' => 'Apache restarted successfully',
                        'details' => 'Stopped and started Apache service'
                    ]);
                } else {
                    echo json_encode([
                        'ok' => false,
                        'error' => 'Failed to start Apache after stopping',
                        'startCode' => $startCode
                    ]);
                }
            } else {
                // Apache is not running, try to start it
                $startCmd = '"' . $xamppPath . '\apache\bin\httpd.exe" -k start';
                exec($startCmd, $output, $startCode);
                
                if ($startCode === 0) {
                    echo json_encode([
                        'ok' => true,
                        'message' => 'Apache started successfully',
                        'details' => 'Apache was not running, now started'
                    ]);
                } else {
                    echo json_encode([
                        'ok' => false,
                        'error' => 'Failed to start Apache',
                        'startCode' => $startCode
                    ]);
                }
            }
        } else {
            echo json_encode([
                'ok' => false,
                'error' => 'XAMPP directory not found at ' . $xamppPath
            ]);
        }
    } else {
        // Linux/Unix systems
        exec('sudo systemctl restart apache2 2>&1', $output, $returnCode);
        
        if ($returnCode === 0) {
            echo json_encode([
                'ok' => true,
                'message' => 'Apache restarted successfully on Linux/Unix',
                'output' => $output
            ]);
        } else {
            echo json_encode([
                'ok' => false,
                'error' => 'Failed to restart Apache on Linux/Unix',
                'output' => $output,
                'returnCode' => $returnCode
            ]);
        }
    }
    
} catch (Exception $e) {
    echo json_encode([
        'ok' => false,
        'error' => $e->getMessage()
    ]);
}
?>

