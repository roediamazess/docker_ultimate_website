# Auto Start Website Script
# Jalankan script ini untuk memulai website secara otomatis

Write-Host "Starting Ultimate Website..." -ForegroundColor Green
Write-Host "================================" -ForegroundColor Green

# Check if Docker is running
try {
    docker version | Out-Null
    Write-Host "Docker is running" -ForegroundColor Green
} catch {
    Write-Host "Docker is not running. Please start Docker Desktop first." -ForegroundColor Red
    Write-Host "   Then run this script again." -ForegroundColor Yellow
    exit 1
}

# Navigate to project directory
$projectPath = "C:\xampp\htdocs\ultimate_website"
if (Test-Path $projectPath) {
    Set-Location $projectPath
    Write-Host "Project directory: $projectPath" -ForegroundColor Cyan
} else {
    Write-Host "Project directory not found: $projectPath" -ForegroundColor Red
    exit 1
}

# Start containers
Write-Host "Starting Docker containers..." -ForegroundColor Yellow
docker-compose up -d

# Wait a moment for containers to start
Start-Sleep -Seconds 5

# Check container status
Write-Host "Checking container status..." -ForegroundColor Yellow
docker-compose ps

# Test website accessibility
Write-Host "Testing website accessibility..." -ForegroundColor Yellow
try {
    $response = Invoke-WebRequest -Uri "http://localhost:8080" -TimeoutSec 10 -UseBasicParsing
    Write-Host "Website is accessible at: http://localhost:8080" -ForegroundColor Green
} catch {
    Write-Host "Website not accessible yet. Please wait a moment and try again." -ForegroundColor Red
}

Write-Host ""
Write-Host "Website startup complete!" -ForegroundColor Green
Write-Host "Access your website at: http://localhost:8080" -ForegroundColor Cyan
Write-Host "Database admin at: http://localhost:8081" -ForegroundColor Cyan
Write-Host "Email testing at: http://localhost:8025" -ForegroundColor Cyan
