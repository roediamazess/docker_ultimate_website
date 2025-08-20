<?php
header('Content-Type: application/json; charset=utf-8');

try {
    $results = [];
    
    // Check if jobsheet.php exists
    if (file_exists('jobsheet.php')) {
        $results['exists'] = true;
        $results['size'] = filesize('jobsheet.php');
        $results['permissions'] = substr(sprintf('%o', fileperms('jobsheet.php')), -4);
        $results['readable'] = is_readable('jobsheet.php');
        $results['writable'] = is_writable('jobsheet.php');
        
        // Read file content
        $content = file_get_contents('jobsheet.php');
        $results['content_length'] = strlen($content);
        $results['starts_with_php'] = strpos($content, '<?php') === 0;
        $results['has_session_start'] = strpos($content, 'session_start()') !== false;
        $results['has_require_once'] = strpos($content, 'require_once') !== false;
        $results['has_include'] = strpos($content, 'include') !== false;
        
        // Check for common issues
        $results['has_syntax_error'] = false;
        $results['syntax_check'] = 'Syntax check not performed';
        
        // Try to check PHP syntax
        $tempFile = tempnam(sys_get_temp_dir(), 'php_check_');
        file_put_contents($tempFile, $content);
        
        $output = [];
        $returnCode = 0;
        exec("php -l \"$tempFile\" 2>&1", $output, $returnCode);
        
        if ($returnCode === 0) {
            $results['syntax_check'] = 'Syntax OK';
        } else {
            $results['syntax_check'] = 'Syntax Error: ' . implode(' ', $output);
            $results['has_syntax_error'] = true;
        }
        
        unlink($tempFile);
        
        // Check include files
        $results['include_files'] = [];
        $includeFiles = ['db.php', 'access_control.php', './partials/layouts/layoutHorizontal.php'];
        
        foreach ($includeFiles as $file) {
            if (file_exists($file)) {
                $results['include_files'][$file] = [
                    'exists' => true,
                    'size' => filesize($file),
                    'readable' => is_readable($file)
                ];
            } else {
                $results['include_files'][$file] = ['exists' => false];
            }
        }
        
        // Check first few lines
        $lines = explode("\n", $content);
        $results['first_lines'] = array_slice($lines, 0, 10);
        
    } else {
        $results['exists'] = false;
        $results['error'] = 'jobsheet.php file not found';
    }
    
    echo json_encode([
        'ok' => true,
        'results' => $results
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'ok' => false,
        'error' => $e->getMessage()
    ]);
}
?>

