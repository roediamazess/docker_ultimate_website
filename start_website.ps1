# Start Website Script - One Click
Write-Host "Starting Ultimate Website..." -ForegroundColor Green

# Check if Docker is running
try {
    $null = docker info 2>$null
    Write-Host "Docker is running" -ForegroundColor Green
} catch {
    Write-Host "Docker is not running. Please start Docker Desktop first." -ForegroundColor Red
    Write-Host "Press any key to exit..."
    $null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")
    exit
}

# Check if containers are already running
Write-Host "Checking existing containers..." -ForegroundColor Cyan
$containers = docker-compose ps --format json 2>$null | ConvertFrom-Json

if ($containers -and $containers.Count -gt 0) {
    $running = $containers | Where-Object { $_.State -eq "running" }
    if ($running.Count -eq $containers.Count) {
        Write-Host "All containers are already running!" -ForegroundColor Green
    } else {
        Write-Host "Some containers are not running. Starting all containers..." -ForegroundColor Yellow
        docker-compose up -d
    }
} else {
    Write-Host "Starting containers..." -ForegroundColor Cyan
    docker-compose up -d
}

# Wait for containers to be ready
Write-Host "Waiting for containers to be ready..." -ForegroundColor Cyan
Start-Sleep -Seconds 10

# Check status
Write-Host "`nWebsite Status:" -ForegroundColor Cyan
docker-compose ps

# Show access URLs
Write-Host "`nAccess URLs:" -ForegroundColor Yellow
Write-Host "Website: http://localhost:8080" -ForegroundColor White
Write-Host "Database Admin: http://localhost:8081" -ForegroundColor White
Write-Host "Email Testing: http://localhost:8025" -ForegroundColor White

# Test website health
Write-Host "`nTesting website health..." -ForegroundColor Cyan
try {
    $response = Invoke-WebRequest -Uri "http://localhost:8080/health.php" -TimeoutSec 10
    $health = $response.Content | ConvertFrom-Json
    
    if ($health.status -eq "healthy") {
        Write-Host "Website is healthy! Opening in browser..." -ForegroundColor Green
        Start-Process "http://localhost:8080/quick_access.php"
    } else {
        Write-Host "Website is unhealthy: $($health.database)" -ForegroundColor Red
        Write-Host "Please run RECOVERY_WEBSITE.bat to fix issues" -ForegroundColor Yellow
    }
} catch {
    Write-Host "Website is starting, please wait a moment..." -ForegroundColor Yellow
    Write-Host "Then open: http://localhost:8080/quick_access.php" -ForegroundColor White
    Write-Host "If problems persist, run RECOVERY_WEBSITE.bat" -ForegroundColor Yellow
}

Write-Host "`nPress any key to exit..."
$null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")
