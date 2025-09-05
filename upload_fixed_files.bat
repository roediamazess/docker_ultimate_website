@echo off
echo ========================================
echo Upload Fixed Activity Files to VPS
echo ========================================
echo.

REM Konfigurasi VPS
set VPS_HOST=powerpro.cloud
set VPS_USER=root
set VPS_PATH=/var/www/html/

echo VPS Host: %VPS_HOST%
echo VPS Path: %VPS_PATH%
echo.

REM File yang akan diupload
set FILES=activity.php access_control.php user_utils.php db.php login.php logout.php index.php

echo Uploading files...
echo.

for %%f in (%FILES%) do (
    echo Uploading %%f...
    scp -o StrictHostKeyChecking=no %%f %VPS_USER%@%VPS_HOST%:%VPS_PATH%
    if !errorlevel! equ 0 (
        echo [OK] %%f uploaded successfully
    ) else (
        echo [ERROR] Failed to upload %%f
    )
    echo.
)

echo ========================================
echo Upload Complete
echo ========================================
echo.
echo Next steps:
echo 1. SSH to VPS: ssh %VPS_USER%@%VPS_HOST%
echo 2. Set permissions: chmod 644 /var/www/html/*.php
echo 3. Restart services: systemctl restart nginx php8.2-fpm
echo 4. Check website: https://powerpro.cloud/activity.php
echo.
pause
