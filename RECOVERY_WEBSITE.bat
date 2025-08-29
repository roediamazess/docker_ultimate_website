@echo off
title Ultimate Website Recovery
echo Recovery Ultimate Website...
echo.

cd /d "C:\xampp\htdocs\ultimate_website"
powershell -ExecutionPolicy Bypass -File "recovery_website.ps1"

pause
