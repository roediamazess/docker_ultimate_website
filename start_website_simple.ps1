# Start Website Simple Script
Write-Host "Starting Ultimate Website..." -ForegroundColor Green

# Navigate to project directory
Set-Location "C:\xampp\htdocs\ultimate_website"

# Start containers
docker-compose up -d

Write-Host "Website started!" -ForegroundColor Green
Write-Host "Access at: http://localhost:8080" -ForegroundColor Cyan
Write-Host "PgAdmin at: http://localhost:8081" -ForegroundColor Cyan





