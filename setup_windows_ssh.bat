
@echo off
echo Setting up SSH for Windows...

REM Create .ssh directory if it doesn't exist
if not exist "%USERPROFILE%\.ssh" mkdir "%USERPROFILE%\.ssh"

REM Copy config file
copy "windows_ssh_config.txt" "%USERPROFILE%\.ssh\config"

REM Copy public key
copy "id_rsa_pub_windows.txt" "%USERPROFILE%\.ssh\id_rsa.pub"

echo SSH setup complete!
echo You can now connect using: ssh powerpro-vps
pause
