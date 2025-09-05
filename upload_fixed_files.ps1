# Upload Fixed Activity Files to VPS
# PowerShell script untuk mengupload file activity.php yang sudah diperbaiki

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Upload Fixed Activity Files to VPS" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Konfigurasi VPS
$VPS_HOST = "powerpro.cloud"
$VPS_USER = "root"
$VPS_PATH = "/var/www/html/"

Write-Host "VPS Host: $VPS_HOST" -ForegroundColor Yellow
Write-Host "VPS Path: $VPS_PATH" -ForegroundColor Yellow
Write-Host ""

# File yang akan diupload
$files = @(
    "activity.php",
    "access_control.php", 
    "user_utils.php",
    "db.php",
    "login.php",
    "logout.php",
    "index.php"
)

Write-Host "Files to upload:" -ForegroundColor Green
foreach ($file in $files) {
    Write-Host "  - $file" -ForegroundColor White
}
Write-Host ""

# Fungsi untuk upload file
function Upload-File {
    param(
        [string]$LocalFile,
        [string]$RemoteFile,
        [string]$Host,
        [string]$User,
        [string]$RemotePath
    )
    
    if (-not (Test-Path $LocalFile)) {
        Write-Host "❌ File $LocalFile tidak ditemukan!" -ForegroundColor Red
        return $false
    }
    
    Write-Host "📤 Uploading $LocalFile..." -ForegroundColor Yellow
    
    try {
        # Gunakan SCP untuk upload
        $scpCommand = "scp -o StrictHostKeyChecking=no `"$LocalFile`" ${User}@${Host}:${RemotePath}${RemoteFile}"
        
        # Execute SCP command
        Invoke-Expression $scpCommand
        
        if ($LASTEXITCODE -eq 0) {
            Write-Host "✅ $LocalFile uploaded successfully!" -ForegroundColor Green
            return $true
        } else {
            Write-Host "❌ Failed to upload $LocalFile" -ForegroundColor Red
            return $false
        }
    }
    catch {
        Write-Host "❌ Error uploading $LocalFile : $($_.Exception.Message)" -ForegroundColor Red
        return $false
    }
}

# Upload semua file
$successCount = 0
$totalFiles = $files.Count

foreach ($file in $files) {
    if (Upload-File -LocalFile $file -RemoteFile $file -Host $VPS_HOST -User $VPS_USER -RemotePath $VPS_PATH) {
        $successCount++
    }
    Write-Host ""
}

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Upload Summary" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Successfully uploaded: $successCount/$totalFiles files" -ForegroundColor Yellow

if ($successCount -eq $totalFiles) {
    Write-Host "🎉 All files uploaded successfully!" -ForegroundColor Green
    Write-Host "🌐 Your website should now have the same styling as the local Docker version." -ForegroundColor Green
    Write-Host "🔗 Check: https://powerpro.cloud/activity.php" -ForegroundColor Cyan
} else {
    Write-Host "⚠️  Some files failed to upload. Please check the errors above." -ForegroundColor Red
}

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Next Steps" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "1. SSH to VPS: ssh $VPS_USER@$VPS_HOST" -ForegroundColor White
Write-Host "2. Set permissions: chmod 644 /var/www/html/*.php" -ForegroundColor White
Write-Host "3. Restart services: systemctl restart nginx php8.2-fpm" -ForegroundColor White
Write-Host "4. Check website: https://powerpro.cloud/activity.php" -ForegroundColor White
Write-Host ""

Read-Host "Press Enter to continue"
