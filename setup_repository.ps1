# Setup Repository Script
Write-Host "Setting up Docker Ultimate Website Repository..." -ForegroundColor Green

# Stop containers if running
Write-Host "`n1. Stopping containers..." -ForegroundColor Cyan
docker-compose down

# Clean up unnecessary files
Write-Host "`n2. Cleaning up files..." -ForegroundColor Cyan

# Remove backup folders
if (Test-Path "backup_2025-08-27_02-20-59") {
    Remove-Item -Recurse -Force "backup_2025-08-27_02-20-59"
    Write-Host "✅ Removed backup folder" -ForegroundColor Green
}

if (Test-Path "docker_backup") {
    Remove-Item -Recurse -Force "docker_backup"
    Write-Host "✅ Removed docker backup folder" -ForegroundColor Green
}

# Remove test files
if (Test-Path "test_db.php") {
    Remove-Item "test_db.php"
    Write-Host "✅ Removed test_db.php" -ForegroundColor Green
}

# Remove documentation files (optional)
$removeDocs = Read-Host "`nRemove documentation files? (y/n)"
if ($removeDocs -eq "y" -or $removeDocs -eq "Y") {
    if (Test-Path "WEBSITE_STATUS.md") { Remove-Item "WEBSITE_STATUS.md" }
    if (Test-Path "FINAL_SUCCESS.md") { Remove-Item "FINAL_SUCCESS.md" }
    if (Test-Path "RESTORE_GUIDE.md") { Remove-Item "RESTORE_GUIDE.md" }
    Write-Host "✅ Removed documentation files" -ForegroundColor Green
}

# Create .env from example
Write-Host "`n3. Setting up environment..." -ForegroundColor Cyan
if (!(Test-Path ".env")) {
    Copy-Item "env.example" ".env"
    Write-Host "✅ Created .env file" -ForegroundColor Green
}

# Initialize git repository
Write-Host "`n4. Setting up Git repository..." -ForegroundColor Cyan
git init
git add .
git commit -m "Initial commit: Docker Ultimate Website setup"

# Add remote repository
$repoUrl = "https://github.com/roediamazess/docker_ultimate_website.git"
git remote add origin $repoUrl

Write-Host "`n5. Repository setup completed!" -ForegroundColor Green
Write-Host "`nNext steps:" -ForegroundColor Yellow
Write-Host "1. Push to GitHub: git push -u origin main" -ForegroundColor Cyan
Write-Host "2. Start website: .\start_website_simple.ps1" -ForegroundColor Cyan
Write-Host "3. Access at: http://localhost:8080" -ForegroundColor Cyan

Write-Host "`n✅ Repository setup completed successfully!" -ForegroundColor Green
