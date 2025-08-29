# Website Management Script
param(
    [Parameter(Mandatory=$false)]
    [ValidateSet("start", "stop", "restart", "status", "logs", "test", "cleanup")]
    [string]$Action = "status"
)

Write-Host "Ultimate Website Management Script" -ForegroundColor Green
Write-Host "===================================" -ForegroundColor Green

switch ($Action) {
    "start" {
        Write-Host "`nStarting website..." -ForegroundColor Cyan
        docker-compose up -d
        Write-Host "Website started successfully!" -ForegroundColor Green
        Write-Host "Access at: http://localhost:8080" -ForegroundColor Yellow
        Write-Host "Database Admin: http://localhost:8081" -ForegroundColor Yellow
        Write-Host "Email Testing: http://localhost:8025" -ForegroundColor Yellow
    }
    
    "stop" {
        Write-Host "`nStopping website..." -ForegroundColor Cyan
        docker-compose down
        Write-Host "Website stopped successfully!" -ForegroundColor Green
    }
    
    "restart" {
        Write-Host "`nRestarting website..." -ForegroundColor Cyan
        docker-compose down
        Start-Sleep -Seconds 2
        docker-compose up -d
        Write-Host "Website restarted successfully!" -ForegroundColor Green
        Write-Host "Access at: http://localhost:8080" -ForegroundColor Yellow
    }
    
    "status" {
        Write-Host "`nWebsite Status:" -ForegroundColor Cyan
        docker-compose ps
        
        Write-Host "`nAccess URLs:" -ForegroundColor Cyan
        Write-Host "   Website: http://localhost:8080" -ForegroundColor Yellow
        Write-Host "   Database Admin: http://localhost:8081" -ForegroundColor Yellow
        Write-Host "   Email Testing: http://localhost:8025" -ForegroundColor Yellow
        
        # Test website accessibility
        try {
            $response = Invoke-WebRequest -Uri "http://localhost:8080" -TimeoutSec 5 -ErrorAction Stop
            Write-Host "   Website is accessible" -ForegroundColor Green
        } catch {
            Write-Host "   Website is not accessible" -ForegroundColor Red
        }
    }
    
    "logs" {
        Write-Host "`nWebsite Logs:" -ForegroundColor Cyan
        docker-compose logs --tail=20
    }
    
    "test" {
        Write-Host "`nTesting website..." -ForegroundColor Cyan
        
        # Test website
        try {
            $response = Invoke-WebRequest -Uri "http://localhost:8080" -TimeoutSec 5
            Write-Host "Website: Accessible (Status: $($response.StatusCode))" -ForegroundColor Green
        } catch {
            Write-Host "Website: Not accessible" -ForegroundColor Red
        }
        
        # Test database admin
        try {
            $response = Invoke-WebRequest -Uri "http://localhost:8081" -TimeoutSec 5
            Write-Host "Database Admin: Accessible (Status: $($response.StatusCode))" -ForegroundColor Green
        } catch {
            Write-Host "Database Admin: Not accessible" -ForegroundColor Red
        }
        
        # Test email testing
        try {
            $response = Invoke-WebRequest -Uri "http://localhost:8025" -TimeoutSec 5
            Write-Host "Email Testing: Accessible (Status: $($response.StatusCode))" -ForegroundColor Green
        } catch {
            Write-Host "Email Testing: Not accessible" -ForegroundColor Red
        }
        
        # Check containers
        Write-Host "`nContainer Status:" -ForegroundColor Cyan
        docker-compose ps
    }
    
    "cleanup" {
        Write-Host "`nCleaning up Docker resources..." -ForegroundColor Cyan
        docker-compose down
        docker system prune -f
        Write-Host "Cleanup completed!" -ForegroundColor Green
    }
}

Write-Host "`nUsage:" -ForegroundColor Cyan
Write-Host "   .\manage_website.ps1 start     - Start website" -ForegroundColor White
Write-Host "   .\manage_website.ps1 stop      - Stop website" -ForegroundColor White
Write-Host "   .\manage_website.ps1 restart   - Restart website" -ForegroundColor White
Write-Host "   .\manage_website.ps1 status    - Show status" -ForegroundColor White
Write-Host "   .\manage_website.ps1 logs      - Show logs" -ForegroundColor White
Write-Host "   .\manage_website.ps1 test      - Test all services" -ForegroundColor White
Write-Host "   .\manage_website.ps1 cleanup   - Clean up resources" -ForegroundColor White
