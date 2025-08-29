# Stop Website Script - One Click
Write-Host "Stopping Ultimate Website..." -ForegroundColor Green

# Stop containers
Write-Host "Stopping containers..." -ForegroundColor Cyan
docker-compose down

Write-Host "`nWebsite stopped successfully!" -ForegroundColor Green
Write-Host "Press any key to exit..."
$null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")
