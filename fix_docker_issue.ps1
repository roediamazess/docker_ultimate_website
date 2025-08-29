# Fix Docker Issue Script
Write-Host "Fixing Docker issues..." -ForegroundColor Green

# Check if Docker Desktop is running
Write-Host "`n1. Checking Docker Desktop status..." -ForegroundColor Cyan
$dockerProcess = Get-Process -Name "Docker Desktop" -ErrorAction SilentlyContinue

if ($dockerProcess) {
    Write-Host "✅ Docker Desktop is running" -ForegroundColor Green
} else {
    Write-Host "❌ Docker Desktop is not running" -ForegroundColor Red
    Write-Host "Starting Docker Desktop..." -ForegroundColor Yellow
    Start-Process "Docker Desktop"
    Write-Host "Please wait for Docker Desktop to fully start (about 1-2 minutes)" -ForegroundColor Yellow
    Write-Host "Then run this script again" -ForegroundColor Yellow
    exit
}

# Wait for Docker to be ready
Write-Host "`n2. Waiting for Docker to be ready..." -ForegroundColor Cyan
$maxAttempts = 30
$attempt = 0

do {
    $attempt++
    try {
        $null = docker info 2>$null
        Write-Host "✅ Docker is ready!" -ForegroundColor Green
        break
    } catch {
        Write-Host "⏳ Waiting for Docker... (attempt $attempt/$maxAttempts)" -ForegroundColor Yellow
        Start-Sleep -Seconds 2
    }
} while ($attempt -lt $maxAttempts)

if ($attempt -eq $maxAttempts) {
    Write-Host "❌ Docker is not responding after $maxAttempts attempts" -ForegroundColor Red
    Write-Host "Please restart Docker Desktop manually and try again" -ForegroundColor Yellow
    exit
}

# Test Docker commands
Write-Host "`n3. Testing Docker commands..." -ForegroundColor Cyan
try {
    $version = docker --version
    Write-Host "✅ $version" -ForegroundColor Green
    
    $info = docker info 2>$null
    Write-Host "✅ Docker info retrieved successfully" -ForegroundColor Green
} catch {
    Write-Host "❌ Docker commands failed" -ForegroundColor Red
    exit
}

# Start website
Write-Host "`n4. Starting website..." -ForegroundColor Cyan
try {
    docker-compose up -d
    Write-Host "✅ Website started successfully!" -ForegroundColor Green
} catch {
    Write-Host "❌ Failed to start website" -ForegroundColor Red
    Write-Host "Error: $($_.Exception.Message)" -ForegroundColor Red
    exit
}

# Check containers
Write-Host "`n5. Checking containers..." -ForegroundColor Cyan
docker-compose ps

Write-Host "`n✅ Docker issue fixed!" -ForegroundColor Green
Write-Host "🌐 Access website at: http://localhost:8080" -ForegroundColor Cyan

