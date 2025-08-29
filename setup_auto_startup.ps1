# Setup Auto Startup Script
Write-Host "Setting up auto startup for Ultimate Website..." -ForegroundColor Green

# Get current directory
$currentDir = Get-Location
$scriptPath = Join-Path $currentDir "manage_website.ps1"

# Create startup script
$startupScript = @"
# Auto startup script for Ultimate Website
cd '$currentDir'
.\manage_website.ps1 -Action start
"@

$startupScriptPath = Join-Path $currentDir "startup_website.ps1"
$startupScript | Out-File -FilePath $startupScriptPath -Encoding UTF8

# Create batch file for startup
$batchContent = @"
@echo off
cd /d "$currentDir"
powershell.exe -ExecutionPolicy Bypass -File "startup_website.ps1"
"@

$batchPath = Join-Path $currentDir "startup_website.bat"
$batchContent | Out-File -FilePath $batchPath -Encoding ASCII

# Add to Windows startup
$startupFolder = "$env:APPDATA\Microsoft\Windows\Start Menu\Programs\Startup"
$shortcutPath = Join-Path $startupFolder "Ultimate Website.lnk"

# Create shortcut
$WshShell = New-Object -comObject WScript.Shell
$Shortcut = $WshShell.CreateShortcut($shortcutPath)
$Shortcut.TargetPath = $batchPath
$Shortcut.WorkingDirectory = $currentDir
$Shortcut.Description = "Start Ultimate Website on Windows startup"
$Shortcut.Save()

Write-Host "Auto startup setup completed!" -ForegroundColor Green
Write-Host "Website will start automatically when Windows starts" -ForegroundColor Yellow
Write-Host "Startup script: $startupScriptPath" -ForegroundColor Cyan
Write-Host "Batch file: $batchPath" -ForegroundColor Cyan
Write-Host "Shortcut: $shortcutPath" -ForegroundColor Cyan

# Test the startup script
Write-Host "`nTesting startup script..." -ForegroundColor Cyan
try {
    & $startupScriptPath
    Write-Host "Startup script test successful!" -ForegroundColor Green
} catch {
    Write-Host "Startup script test failed: $($_.Exception.Message)" -ForegroundColor Red
}
