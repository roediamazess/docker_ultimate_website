# Setup Auto Startup Script
# Script ini akan mengatur website agar start otomatis saat Windows boot

Write-Host "Setting up Auto Startup..." -ForegroundColor Green
Write-Host "=============================" -ForegroundColor Green

# Get current script directory
$scriptPath = Split-Path -Parent $MyInvocation.MyCommand.Path
$startupScript = Join-Path $scriptPath "start_website.ps1"

# Create startup folder path
$startupFolder = "$env:APPDATA\Microsoft\Windows\Start Menu\Programs\Startup"
$shortcutPath = Join-Path $startupFolder "Ultimate Website.lnk"

Write-Host "Startup folder: $startupFolder" -ForegroundColor Cyan
Write-Host "Startup script: $startupScript" -ForegroundColor Cyan

# Create PowerShell command for startup
$psCommand = "powershell.exe -ExecutionPolicy Bypass -File `"$startupScript`""

# Create shortcut
$WshShell = New-Object -comObject WScript.Shell
$Shortcut = $WshShell.CreateShortcut($shortcutPath)
$Shortcut.TargetPath = "powershell.exe"
$Shortcut.Arguments = "-ExecutionPolicy Bypass -File `"$startupScript`""
$Shortcut.WorkingDirectory = $scriptPath
$Shortcut.Description = "Start Ultimate Website Docker containers"
$Shortcut.Save()

Write-Host "Auto startup configured!" -ForegroundColor Green
Write-Host "Shortcut created at: $shortcutPath" -ForegroundColor Cyan

# Test the startup script
Write-Host ""
Write-Host "Testing startup script..." -ForegroundColor Yellow
& $startupScript

Write-Host ""
Write-Host "Setup complete! Website will start automatically on next boot." -ForegroundColor Green
Write-Host "To disable auto startup, delete the shortcut from:" -ForegroundColor Yellow
Write-Host "   $startupFolder" -ForegroundColor Cyan
