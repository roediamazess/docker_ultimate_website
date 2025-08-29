@echo off
title Ultimate Website Stopper
echo Stopping Ultimate Website...
echo.

cd /d "C:\xampp\htdocs\ultimate_website"
powershell -ExecutionPolicy Bypass -File "stop_website.ps1"

pause
