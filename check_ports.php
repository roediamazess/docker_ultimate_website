<?php
header('Content-Type: application/json; charset=utf-8');

try {
    $ports = [];
    
    // Check common XAMPP ports
    $commonPorts = [
        80 => 'HTTP (Apache)',
        443 => 'HTTPS (Apache)',
        3306 => 'MySQL',
        8080 => 'Alternative HTTP',
        8443 => 'Alternative HTTPS'
    ];
    
    foreach ($commonPorts as $port => $service) {
        $connection = @fsockopen('localhost', $port, $errno, $errstr, 1);
        if (is_resource($connection)) {
            $ports[$port] = [
                'service' => $service,
                'status' => 'open',
                'error' => null
            ];
            fclose($connection);
        } else {
            $ports[$port] = [
                'service' => $service,
                'status' => 'closed',
                'error' => "$errno: $errstr"
            ];
        }
    }
    
    // Check if Apache and MySQL processes are running
    $processes = [];
    
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        // Windows
        exec('tasklist /FI "IMAGENAME eq httpd.exe" 2>NUL', $output, $returnCode);
        $processes['Apache (httpd.exe)'] = [
            'running' => ($returnCode === 0 && count($output) > 1),
            'output' => $output
        ];
        
        exec('tasklist /FI "IMAGENAME eq mysqld.exe" 2>NUL', $output, $returnCode);
        $processes['MySQL (mysqld.exe)'] = [
            'running' => ($returnCode === 0 && count($output) > 1),
            'output' => $output
        ];
    } else {
        // Linux/Unix
        exec('ps aux | grep httpd 2>/dev/null', $output, $returnCode);
        $processes['Apache (httpd)'] = [
            'running' => (count($output) > 1),
            'output' => $output
        ];
        
        exec('ps aux | grep mysqld 2>/dev/null', $output, $returnCode);
        $processes['MySQL (mysqld)'] = [
            'running' => (count($output) > 1),
            'output' => $output
        ];
    }
    
    echo json_encode([
        'ok' => true,
        'ports' => $ports,
        'processes' => $processes,
        'summary' => [
            'open_ports' => count(array_filter($ports, function($p) { return $p['status'] === 'open'; })),
            'total_ports' => count($ports),
            'running_services' => count(array_filter($processes, function($p) { return $p['running']; })),
            'total_services' => count($processes)
        ]
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'ok' => false,
        'error' => $e->getMessage()
    ]);
}
?>

