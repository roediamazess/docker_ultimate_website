@echo off
title Docker Cleanup
echo Docker Cleanup Tool
echo.

cd /d "C:\xampp\htdocs\ultimate_website"
powershell -ExecutionPolicy Bypass -File "cleanup_docker.ps1"

pause
