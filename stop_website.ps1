# Stop Website Script
# Jalankan script ini untuk menghentikan website

Write-Host "🛑 Stopping Ultimate Website..." -ForegroundColor Red
Write-Host "===============================" -ForegroundColor Red

# Navigate to project directory
$projectPath = "C:\xampp\htdocs\ultimate_website"
if (Test-Path $projectPath) {
    Set-Location $projectPath
    Write-Host "📁 Project directory: $projectPath" -ForegroundColor Cyan
} else {
    Write-Host "❌ Project directory not found: $projectPath" -ForegroundColor Red
    exit 1
}

# Stop containers
Write-Host "🐳 Stopping Docker containers..." -ForegroundColor Yellow
docker-compose down

Write-Host ""
Write-Host "✅ Website stopped successfully!" -ForegroundColor Green
Write-Host "💡 To start again, run: .\start_website.ps1" -ForegroundColor Cyan
