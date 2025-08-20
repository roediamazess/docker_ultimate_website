<?php
header('Content-Type: application/json; charset=utf-8');

try {
    $results = [];
    
    if (!file_exists('jobsheet.php')) {
        echo json_encode([
            'ok' => false,
            'error' => 'jobsheet.php file not found'
        ]);
        exit;
    }
    
    // Read current content
    $content = file_get_contents('jobsheet.php');
    $results['original_size'] = strlen($content);
    
    // Check if file starts with PHP tag
    if (strpos($content, '<?php') !== 0) {
        // Add PHP opening tag if missing
        $content = "<?php\n" . $content;
        $results['added_php_tag'] = true;
    }
    
    // Check for common issues and fix them
    $fixes = [];
    
    // Fix 1: Check for BOM (Byte Order Mark)
    if (substr($content, 0, 3) === "\xEF\xBB\xBF") {
        $content = substr($content, 3);
        $fixes[] = 'Removed BOM (Byte Order Mark)';
    }
    
    // Fix 2: Check for hidden characters
    $content = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $content);
    if ($content !== file_get_contents('jobsheet.php')) {
        $fixes[] = 'Removed hidden characters';
    }
    
    // Fix 3: Check for encoding issues
    if (!mb_check_encoding($content, 'UTF-8')) {
        $content = mb_convert_encoding($content, 'UTF-8', 'auto');
        $fixes[] = 'Fixed encoding issues';
    }
    
    // Fix 4: Check for line ending issues
    $content = str_replace(["\r\n", "\r"], "\n", $content);
    $fixes[] = 'Normalized line endings';
    
    // Fix 5: Check for extra whitespace
    $content = trim($content);
    $fixes[] = 'Trimmed whitespace';
    
    // Create backup
    $backupFile = 'jobsheet_backup_' . date('Y-m-d_H-i-s') . '.php';
    if (file_put_contents($backupFile, file_get_contents('jobsheet.php'))) {
        $results['backup_created'] = $backupFile;
    }
    
    // Write fixed content
    if (file_put_contents('jobsheet.php', $content)) {
        $results['fixed'] = true;
        $results['new_size'] = strlen($content);
        $results['fixes_applied'] = $fixes;
        
        // Test syntax
        $tempFile = tempnam(sys_get_temp_dir(), 'php_check_');
        file_put_contents($tempFile, $content);
        
        $output = [];
        $returnCode = 0;
        exec("php -l \"$tempFile\" 2>&1", $output, $returnCode);
        
        if ($returnCode === 0) {
            $results['syntax_check'] = 'Syntax OK after fixes';
        } else {
            $results['syntax_check'] = 'Syntax Error after fixes: ' . implode(' ', $output);
        }
        
        unlink($tempFile);
        
        echo json_encode([
            'ok' => true,
            'message' => 'Jobsheet.php file fixed successfully',
            'results' => $results
        ]);
        
    } else {
        echo json_encode([
            'ok' => false,
            'error' => 'Failed to write fixed content to jobsheet.php'
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'ok' => false,
        'error' => $e->getMessage()
    ]);
}
?>

