# Docker Cleanup Script
Write-Host "Docker Cleanup Script" -ForegroundColor Green
Write-Host "=====================" -ForegroundColor Green

# Function to calculate size in GB
function Format-Size {
    param([long]$bytes)
    if ($bytes -gt 1GB) {
        return [math]::Round($bytes / 1GB, 2).ToString() + " GB"
    } elseif ($bytes -gt 1MB) {
        return [math]::Round($bytes / 1MB, 2).ToString() + " MB"
    } else {
        return [math]::Round($bytes / 1KB, 2).ToString() + " KB"
    }
}

# Show current disk usage
Write-Host "`n1. Current Docker Disk Usage:" -ForegroundColor Cyan
try {
    $diskUsage = docker system df --format json | ConvertFrom-Json
    $totalSize = $diskUsage.Images.Size + $diskUsage.Containers.Size + $diskUsage.Volumes.Size + $diskUsage.BuildCache.Size
    
    Write-Host "   Images: $(Format-Size $diskUsage.Images.Size)" -ForegroundColor White
    Write-Host "   Containers: $(Format-Size $diskUsage.Containers.Size)" -ForegroundColor White
    Write-Host "   Volumes: $(Format-Size $diskUsage.Volumes.Size)" -ForegroundColor White
    Write-Host "   Build Cache: $(Format-Size $diskUsage.BuildCache.Size)" -ForegroundColor White
    Write-Host "   Total: $(Format-Size $totalSize)" -ForegroundColor Yellow
} catch {
    Write-Host "   Could not get disk usage information" -ForegroundColor Red
}

# Show current images
Write-Host "`n2. Current Docker Images:" -ForegroundColor Cyan
$images = docker images --format "table {{.Repository}}\t{{.Tag}}\t{{.Size}}\t{{.CreatedAt}}"
Write-Host $images -ForegroundColor White

# Ask user what to clean
Write-Host "`n3. Cleanup Options:" -ForegroundColor Cyan
Write-Host "   1. Remove unused images (dangling images)" -ForegroundColor White
Write-Host "   2. Remove stopped containers" -ForegroundColor White
Write-Host "   3. Remove unused volumes" -ForegroundColor White
Write-Host "   4. Remove build cache" -ForegroundColor White
Write-Host "   5. Full cleanup (all unused resources)" -ForegroundColor Yellow
Write-Host "   6. Exit" -ForegroundColor Red

$choice = Read-Host "`nSelect option (1-6)"

switch ($choice) {
    "1" {
        Write-Host "`nRemoving unused images..." -ForegroundColor Yellow
        docker image prune -f
        Write-Host "Unused images removed!" -ForegroundColor Green
    }
    "2" {
        Write-Host "`nRemoving stopped containers..." -ForegroundColor Yellow
        docker container prune -f
        Write-Host "Stopped containers removed!" -ForegroundColor Green
    }
    "3" {
        Write-Host "`nRemoving unused volumes..." -ForegroundColor Yellow
        docker volume prune -f
        Write-Host "Unused volumes removed!" -ForegroundColor Green
    }
    "4" {
        Write-Host "`nRemoving build cache..." -ForegroundColor Yellow
        docker builder prune -f
        Write-Host "Build cache removed!" -ForegroundColor Green
    }
    "5" {
        Write-Host "`nPerforming full cleanup..." -ForegroundColor Yellow
        Write-Host "This will remove ALL unused Docker resources!" -ForegroundColor Red
        $confirm = Read-Host "Are you sure? (y/N)"
        if ($confirm -eq "y" -or $confirm -eq "Y") {
            docker system prune -a -f --volumes
            Write-Host "Full cleanup completed!" -ForegroundColor Green
        } else {
            Write-Host "Cleanup cancelled." -ForegroundColor Yellow
        }
    }
    "6" {
        Write-Host "Exiting..." -ForegroundColor Yellow
        exit
    }
    default {
        Write-Host "Invalid option!" -ForegroundColor Red
    }
}

# Show final disk usage
Write-Host "`n4. Final Docker Disk Usage:" -ForegroundColor Cyan
try {
    $diskUsage = docker system df --format json | ConvertFrom-Json
    $totalSize = $diskUsage.Images.Size + $diskUsage.Containers.Size + $diskUsage.Volumes.Size + $diskUsage.BuildCache.Size
    
    Write-Host "   Total: $(Format-Size $totalSize)" -ForegroundColor Yellow
} catch {
    Write-Host "   Could not get final disk usage" -ForegroundColor Red
}

Write-Host "`nCleanup completed!" -ForegroundColor Green
Write-Host "Press any key to exit..."
$null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")
