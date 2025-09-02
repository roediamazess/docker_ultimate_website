# 🚀 Docker VPS Quick Start Guide

## 📋 File yang Perlu di-Commit ke GitHub

Sebelum deployment, pastikan file-file berikut sudah di-commit ke GitHub:

### ✅ File Docker Configuration
- `docker-compose.yml` (untuk development)
- `docker-compose.prod.yml` (untuk production)
- `Dockerfile`
- `docker-entrypoint.sh`
- `nginx.conf`

### ✅ File Environment
- `env.production` (template environment untuk production)

### ✅ File Deployment Scripts
- `deploy_docker_vps.sh` (script deployment untuk VPS)
- `docker_vps_setup.sh` (script setup awal VPS)
- `quick_deploy_docker_vps.ps1` (script PowerShell untuk Windows)

### ✅ File Documentation
- `DOCKER_VPS_DEPLOYMENT_GUIDE.md`
- `DOCKER_VPS_QUICK_START.md`

## 🎯 Cara Deployment

### Opsi 1: Deployment Manual (Recommended)

#### Step 1: Setup VPS
```bash
# SSH ke VPS
ssh your_user@your_vps_ip

# Download dan jalankan setup script
wget https://raw.githubusercontent.com/YOUR_USERNAME/ultimate-website/main/docker_vps_setup.sh
chmod +x docker_vps_setup.sh
./docker_vps_setup.sh
```

#### Step 2: Deploy Application
```bash
# Download dan jalankan deployment script
wget https://raw.githubusercontent.com/YOUR_USERNAME/ultimate-website/main/deploy_docker_vps.sh
chmod +x deploy_docker_vps.sh

# Edit konfigurasi sebelum menjalankan
nano deploy_docker_vps.sh
# Ganti:
# - GITHUB_REPO="https://github.com/YOUR_USERNAME/ultimate-website.git"
# - VPS_IP="YOUR_VPS_IP"
# - DB_PASSWORD="YOUR_STRONG_PASSWORD_HERE"

# Jalankan deployment
./deploy_docker_vps.sh
```

### Opsi 2: Quick Deployment dari Windows

#### Step 1: Install PowerShell (jika belum ada)
```powershell
# Download dan install PowerShell
# https://github.com/PowerShell/PowerShell/releases
```

#### Step 2: Jalankan Quick Deployment
```powershell
# Buka PowerShell sebagai Administrator
# Navigate ke folder project
cd C:\xampp\htdocs\ultimate_website

# Jalankan script deployment
.\quick_deploy_docker_vps.ps1 -VpsIp "YOUR_VPS_IP" -SshUser "your_username" -GitHubRepo "https://github.com/YOUR_USERNAME/ultimate-website.git" -DbPassword "YOUR_STRONG_PASSWORD"
```

## ⚙️ Konfigurasi yang Perlu Diubah

### 1. GitHub Repository URL
Ganti `YOUR_USERNAME` dengan username GitHub Anda di:
- `deploy_docker_vps.sh`
- `quick_deploy_docker_vps.ps1`

### 2. VPS IP Address
Ganti `YOUR_VPS_IP` dengan IP VPS Anda di:
- `deploy_docker_vps.sh`
- `quick_deploy_docker_vps.ps1`

### 3. Database Password
Ganti `YOUR_STRONG_PASSWORD_HERE` dengan password yang kuat di:
- `deploy_docker_vps.sh`
- `quick_deploy_docker_vps.ps1`
- `env.production`

### 4. Email Configuration
Edit file `env.production` untuk konfigurasi email:
```env
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
```

## 🔧 Management Commands

Setelah deployment, Anda bisa menggunakan command berikut:

### Basic Commands
```bash
# Start application
ultimate-website-start

# Stop application
ultimate-website-stop

# Check status
ultimate-website-status

# View logs
ultimate-website-logs

# Create backup
ultimate-website-backup

# Check system health
ultimate-website-monitor
```

### Docker Commands
```bash
# Masuk ke direktori project
cd /opt/ultimate-website

# Start containers
docker-compose -f docker-compose.prod.yml up -d

# Stop containers
docker-compose -f docker-compose.prod.yml down

# View logs
docker-compose -f docker-compose.prod.yml logs

# Check status
docker-compose -f docker-compose.prod.yml ps

# Restart specific service
docker-compose -f docker-compose.prod.yml restart web
```

## 🔍 Troubleshooting

### Container tidak bisa start
```bash
# Check logs
docker-compose -f docker-compose.prod.yml logs

# Check disk space
df -h

# Check memory
free -h
```

### Database connection error
```bash
# Check database container
docker-compose -f docker-compose.prod.yml logs db

# Test database connection
docker-compose -f docker-compose.prod.yml exec web php artisan tinker
```

### Permission issues
```bash
# Fix storage permissions
docker-compose -f docker-compose.prod.yml exec web chown -R www-data:www-data storage bootstrap/cache
docker-compose -f docker-compose.prod.yml exec web chmod -R 775 storage bootstrap/cache
```

## 📊 Monitoring

### Health Check
```bash
# Test application
curl http://YOUR_VPS_IP:8080/health.php

# Test main page
curl http://YOUR_VPS_IP:8080
```

### System Monitoring
```bash
# Check container status
docker ps

# Check resource usage
docker stats

# Check system resources
htop
```

## 🔄 Update Application

### Update dari GitHub
```bash
cd /opt/ultimate-website
git pull origin main
docker-compose -f docker-compose.prod.yml up -d --build
docker-compose -f docker-compose.prod.yml exec web php artisan migrate --force
```

## 🔒 Security Checklist

- ✅ Firewall configured (UFW)
- ✅ Fail2ban installed
- ✅ Strong database password
- ✅ SSL certificate (self-signed for testing)
- ✅ Regular backups configured
- ✅ Log rotation configured
- ✅ Monitoring configured

## 📞 Support

Jika mengalami masalah:

1. Check logs: `ultimate-website-logs`
2. Check status: `ultimate-website-status`
3. Check system health: `ultimate-website-monitor`
4. Review deployment guide: `DOCKER_VPS_DEPLOYMENT_GUIDE.md`

---

**Catatan:** Pastikan semua file sudah di-commit ke GitHub sebelum melakukan deployment!
