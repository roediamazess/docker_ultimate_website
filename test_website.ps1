# Test Website Script
Write-Host "Testing Ultimate Website..." -ForegroundColor Green

# Test website accessibility
Write-Host "`n1. Testing website accessibility..." -ForegroundColor Cyan
try {
    $response = Invoke-WebRequest -Uri "http://localhost:8080" -UseBasicParsing
    Write-Host "✅ Website accessible - Status: $($response.StatusCode)" -ForegroundColor Green
} catch {
    Write-Host "❌ Website not accessible: $($_.Exception.Message)" -ForegroundColor Red
}

# Test database connection
Write-Host "`n2. Testing database connection..." -ForegroundColor Cyan
try {
    $dbResponse = Invoke-WebRequest -Uri "http://localhost:8080/test_db.php" -UseBasicParsing
    if ($dbResponse.Content -like "*Database connection successful*") {
        Write-Host "✅ Database connection successful" -ForegroundColor Green
    } else {
        Write-Host "❌ Database connection failed" -ForegroundColor Red
    }
} catch {
    Write-Host "❌ Database test failed: $($_.Exception.Message)" -ForegroundColor Red
}

# Check Docker containers
Write-Host "`n3. Checking Docker containers..." -ForegroundColor Cyan
$containers = docker-compose ps --format "table {{.Name}}\t{{.Status}}\t{{.Ports}}"
Write-Host $containers -ForegroundColor Yellow

Write-Host "`n✅ Website testing completed!" -ForegroundColor Green
Write-Host "🌐 Access website at: http://localhost:8080" -ForegroundColor Cyan
