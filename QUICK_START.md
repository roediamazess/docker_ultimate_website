# 🚀 Quick Start Guide - Ultimate Website

## Cara Menjalankan Website

### Opsi 1: Double Click (Paling Mudah)
1. **Start Website**: Double click file `START_WEBSITE.bat`
2. **Stop Website**: Double click file `STOP_WEBSITE.bat`

### Opsi 2: PowerShell Script
1. **Start Website**: Jalankan `.\start_website.ps1`
2. **Stop Website**: Jalankan `.\stop_website.ps1`

### Opsi 3: Docker Desktop
1. Buka Docker Desktop
2. Klik tombol "Play" pada container `ultimate-website-web`

## Akses Website

Setelah website berjalan, akses melalui:

- **🚀 Quick Access**: http://localhost:8080/quick_access.php (Auto-login)
- **🌐 Website Utama**: http://localhost:8080 (Perlu login)
- **🔐 Login Page**: http://localhost:8080/login.php
- **🗄️ Database Admin**: http://localhost:8081
  - Email: `admin@admin.com`
  - Password: `admin`
- **📧 Email Testing**: http://localhost:8025

### Login Credentials:
- **Test User**: test@test.com / test123
- **Admin**: admin@test.com / (password dari database)
- **PMS**: pms@ppsolution.com / (password dari database)

## Troubleshooting

### Jika website tidak bisa diakses setelah restart:
1. **Double click** `RECOVERY_WEBSITE.bat` (Cara paling mudah)
2. Atau jalankan `.\recovery_website.ps1`

### Jika website tidak bisa diakses:
1. Pastikan Docker Desktop sudah berjalan
2. Jalankan `.\manage_website.ps1 -Action test` untuk cek status
3. Jalankan `.\manage_website.ps1 -Action logs` untuk lihat error

### Jika ada error:
1. Jalankan `.\manage_website.ps1 -Action cleanup`
2. Jalankan ulang `.\start_website.ps1`
3. Jika masih bermasalah, jalankan `.\recovery_website.ps1`

## File Penting

- `START_WEBSITE.bat` - Start website dengan double click
- `STOP_WEBSITE.bat` - Stop website dengan double click
- `RECOVERY_WEBSITE.bat` - Recovery website setelah restart (PENTING!)
- `start_website.ps1` - Script PowerShell untuk start
- `stop_website.ps1` - Script PowerShell untuk stop
- `recovery_website.ps1` - Script recovery lengkap
- `manage_website.ps1` - Script management lengkap

## Tips

- Website akan otomatis terbuka di browser setelah start
- Semua data tersimpan di Docker volume, tidak akan hilang saat restart
- Gunakan Database Admin untuk mengelola database PostgreSQL
- Email Testing untuk test fitur email aplikasi
