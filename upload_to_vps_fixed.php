<?php
/**
 * Upload Fixed Activity Files to VPS
 * Script untuk mengupload file activity.php yang sudah diperbaiki ke VPS
 */

// Konfigurasi VPS
$vps_host = 'powerpro.cloud';
$vps_username = 'root'; // Ganti dengan username VPS Anda
$vps_password = 'your_password'; // Ganti dengan password VPS Anda
$vps_path = '/var/www/html/'; // Path di VPS

// File yang akan diupload
$files_to_upload = [
    'activity.php',
    'access_control.php',
    'user_utils.php',
    'db.php',
    'login.php',
    'logout.php',
    'index.php'
];

echo "=== Upload Fixed Activity Files to VPS ===\n";
echo "VPS Host: $vps_host\n";
echo "VPS Path: $vps_path\n";
echo "Files to upload: " . implode(', ', $files_to_upload) . "\n\n";

// Fungsi untuk upload file via SCP
function uploadFile($localFile, $remoteFile, $host, $username, $password, $remotePath) {
    if (!file_exists($localFile)) {
        echo "❌ File $localFile tidak ditemukan!\n";
        return false;
    }
    
    echo "📤 Uploading $localFile...\n";
    
    // Gunakan SCP untuk upload
    $command = "scp -o StrictHostKeyChecking=no $localFile $username@$host:$remotePath$remoteFile";
    
    // Set password untuk SCP (tidak aman untuk production)
    $env = "SSHPASS=$password";
    $command = "sshpass -e scp -o StrictHostKeyChecking=no $localFile $username@$host:$remotePath$remoteFile";
    
    $output = [];
    $return_code = 0;
    
    putenv($env);
    exec($command, $output, $return_code);
    
    if ($return_code === 0) {
        echo "✅ $localFile uploaded successfully!\n";
        return true;
    } else {
        echo "❌ Failed to upload $localFile\n";
        echo "Error: " . implode("\n", $output) . "\n";
        return false;
    }
}

// Upload semua file
$success_count = 0;
$total_files = count($files_to_upload);

foreach ($files_to_upload as $file) {
    if (uploadFile($file, $file, $vps_host, $vps_username, $vps_password, $vps_path)) {
        $success_count++;
    }
    echo "\n";
}

echo "=== Upload Summary ===\n";
echo "Successfully uploaded: $success_count/$total_files files\n";

if ($success_count === $total_files) {
    echo "🎉 All files uploaded successfully!\n";
    echo "🌐 Your website should now have the same styling as the local Docker version.\n";
    echo "🔗 Check: https://powerpro.cloud/activity.php\n";
} else {
    echo "⚠️  Some files failed to upload. Please check the errors above.\n";
}

echo "\n=== Next Steps ===\n";
echo "1. Set proper file permissions on VPS:\n";
echo "   chmod 644 /var/www/html/*.php\n";
echo "   chmod 755 /var/www/html/\n";
echo "\n";
echo "2. Restart web server if needed:\n";
echo "   systemctl restart nginx\n";
echo "   systemctl restart php8.2-fpm\n";
echo "\n";
echo "3. Check website: https://powerpro.cloud/activity.php\n";
?>
