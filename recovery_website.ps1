# Recovery Website Script
Write-Host "Recovery Ultimate Website..." -ForegroundColor Green
Write-Host "=============================" -ForegroundColor Green

# Function to check if Docker is running
function Test-Docker {
    try {
        $null = docker info 2>$null
        return $true
    } catch {
        return $false
    }
}

# Function to check if containers are healthy
function Test-Containers {
    try {
        $containers = docker-compose ps --format json | ConvertFrom-Json
        $healthy = $true
        
        foreach ($container in $containers) {
            if ($container.State -ne "running") {
                Write-Host "Container $($container.Name) is not running: $($container.State)" -ForegroundColor Red
                $healthy = $false
            }
        }
        
        return $healthy
    } catch {
        return $false
    }
}

# Function to fix common issues
function Repair-Website {
    Write-Host "`nRepairing website..." -ForegroundColor Cyan
    
    # Stop all containers
    Write-Host "Stopping containers..." -ForegroundColor Yellow
    docker-compose down
    
    # Clean up any orphaned containers
    Write-Host "Cleaning up orphaned containers..." -ForegroundColor Yellow
    docker container prune -f
    
    # Clean up networks
    Write-Host "Cleaning up networks..." -ForegroundColor Yellow
    docker network prune -f
    
    # Start containers with rebuild
    Write-Host "Starting containers with rebuild..." -ForegroundColor Yellow
    docker-compose up -d --build
    
    # Wait for containers to be ready
    Write-Host "Waiting for containers to be ready..." -ForegroundColor Yellow
    Start-Sleep -Seconds 30
    
    # Test website
    Write-Host "Testing website..." -ForegroundColor Yellow
    try {
        $response = Invoke-WebRequest -Uri "http://localhost:8080/health.php" -TimeoutSec 10
        $health = $response.Content | ConvertFrom-Json
        
        if ($health.status -eq "healthy") {
            Write-Host "Website is healthy!" -ForegroundColor Green
            return $true
        } else {
            Write-Host "Website is unhealthy: $($health.database)" -ForegroundColor Red
            return $false
        }
    } catch {
        Write-Host "Website test failed: $($_.Exception.Message)" -ForegroundColor Red
        return $false
    }
}

# Main recovery process
Write-Host "1. Checking Docker status..." -ForegroundColor Cyan
if (-not (Test-Docker)) {
    Write-Host "Docker is not running. Please start Docker Desktop first." -ForegroundColor Red
    Write-Host "Press any key to exit..."
    $null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")
    exit
}
Write-Host "Docker is running" -ForegroundColor Green

Write-Host "`n2. Checking container status..." -ForegroundColor Cyan
if (Test-Containers) {
    Write-Host "All containers are running" -ForegroundColor Green
} else {
    Write-Host "Some containers are not running. Starting repair..." -ForegroundColor Yellow
    if (Repair-Website) {
        Write-Host "Repair successful!" -ForegroundColor Green
    } else {
        Write-Host "Repair failed. Please check manually." -ForegroundColor Red
        Write-Host "Press any key to exit..."
        $null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")
        exit
    }
}

Write-Host "`n3. Testing website accessibility..." -ForegroundColor Cyan
try {
    $response = Invoke-WebRequest -Uri "http://localhost:8080/quick_access.php" -TimeoutSec 10
    Write-Host "Website is accessible!" -ForegroundColor Green
    
    # Open website in browser
    Write-Host "Opening website in browser..." -ForegroundColor Yellow
    Start-Process "http://localhost:8080/quick_access.php"
    
} catch {
    Write-Host "Website is not accessible. Starting repair..." -ForegroundColor Red
    if (Repair-Website) {
        Write-Host "Repair successful! Opening website..." -ForegroundColor Green
        Start-Process "http://localhost:8080/quick_access.php"
    } else {
        Write-Host "Repair failed. Please check manually." -ForegroundColor Red
    }
}

Write-Host "`nRecovery completed!" -ForegroundColor Green
Write-Host "Press any key to exit..."
$null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")
