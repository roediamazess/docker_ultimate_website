# 🐳 Docker VPS Deployment Guide - Ultimate Website

## 📋 Prerequisites

### VPS Requirements
- Ubuntu 20.04+ atau 22.04 LTS
- Minimum 2GB RAM, 2 CPU cores
- 20GB+ storage space
- Root access atau sudo privileges

### Software yang Diperlukan
- Docker Engine
- Docker Compose
- Git
- Nginx (opsional, untuk reverse proxy)

## 🚀 Step 1: Setup VPS

### 1.1 Update System
```bash
sudo apt update && sudo apt upgrade -y
```

### 1.2 Install Docker
```bash
# Install Docker
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh

# Add user to docker group
sudo usermod -aG docker $USER

# Install Docker Compose
sudo curl -L "https://github.com/docker/compose/releases/latest/download/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
sudo chmod +x /usr/local/bin/docker-compose

# Verify installation
docker --version
docker-compose --version
```

### 1.3 Install Git
```bash
sudo apt install git -y
```

## 📁 Step 2: Clone Repository dari GitHub

### 2.1 Clone Project
```bash
# Buat direktori untuk project
mkdir -p /opt/ultimate-website
cd /opt/ultimate-website

# Clone dari GitHub (ganti dengan URL repository Anda)
git clone https://github.com/YOUR_USERNAME/ultimate-website.git .

# Atau jika sudah ada, pull latest changes
git pull origin main
```

## ⚙️ Step 3: Konfigurasi Environment

### 3.1 Setup Environment File
```bash
# Copy environment template
cp env.production .env

# Edit environment file
nano .env
```

### 3.2 Konfigurasi Environment Variables
Edit file `.env` dengan nilai yang sesuai:

```env
APP_NAME="Ultimate Website"
APP_ENV=production
APP_KEY=base64:YOUR_APP_KEY_HERE
APP_DEBUG=false
APP_URL=http://YOUR_VPS_IP:8080

DB_CONNECTION=pgsql
DB_HOST=db
DB_PORT=5432
DB_DATABASE=ultimate_website
DB_USERNAME=postgres
DB_PASSWORD=YOUR_STRONG_PASSWORD_HERE

CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

REDIS_HOST=redis
REDIS_PORT=6379

# Email configuration
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
```

### 3.3 Generate Application Key
```bash
# Generate APP_KEY
docker run --rm -v $(pwd):/app -w /app php:8.2-cli php -r "echo 'base64:' . base64_encode(random_bytes(32)) . PHP_EOL;"
```

## 🔧 Step 4: Setup SSL (Opsional)

### 4.1 Buat Direktori SSL
```bash
mkdir -p ssl
```

### 4.2 Generate Self-Signed Certificate (untuk testing)
```bash
openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
    -keyout ssl/key.pem \
    -out ssl/cert.pem \
    -subj "/C=ID/ST=Jakarta/L=Jakarta/O=Ultimate Website/CN=YOUR_VPS_IP"
```

## 🐳 Step 5: Deploy dengan Docker

### 5.1 Build dan Start Containers
```bash
# Build dan start semua services
docker-compose -f docker-compose.prod.yml up -d --build

# Check status containers
docker-compose -f docker-compose.prod.yml ps
```

### 5.2 Setup Laravel Application
```bash
# Masuk ke container web
docker-compose -f docker-compose.prod.yml exec web bash

# Di dalam container, jalankan:
php artisan key:generate
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan migrate --force
php artisan db:seed --force

# Exit container
exit
```

## 🔍 Step 6: Verifikasi Deployment

### 6.1 Check Container Status
```bash
docker-compose -f docker-compose.prod.yml ps
docker-compose -f docker-compose.prod.yml logs web
```

### 6.2 Test Application
```bash
# Test health check
curl http://YOUR_VPS_IP:8080/health.php

# Test main application
curl http://YOUR_VPS_IP:8080
```

## 🔄 Step 7: Setup Auto-Start

### 7.1 Enable Docker Service
```bash
sudo systemctl enable docker
sudo systemctl start docker
```

### 7.2 Create Systemd Service (Opsional)
```bash
sudo nano /etc/systemd/system/ultimate-website.service
```

Isi dengan:
```ini
[Unit]
Description=Ultimate Website Docker Compose
Requires=docker.service
After=docker.service

[Service]
Type=oneshot
RemainAfterExit=yes
WorkingDirectory=/opt/ultimate-website
ExecStart=/usr/local/bin/docker-compose -f docker-compose.prod.yml up -d
ExecStop=/usr/local/bin/docker-compose -f docker-compose.prod.yml down
TimeoutStartSec=0

[Install]
WantedBy=multi-user.target
```

Enable service:
```bash
sudo systemctl enable ultimate-website.service
sudo systemctl start ultimate-website.service
```

## 🛠️ Management Commands

### Start/Stop Services
```bash
# Start all services
docker-compose -f docker-compose.prod.yml up -d

# Stop all services
docker-compose -f docker-compose.prod.yml down

# Restart specific service
docker-compose -f docker-compose.prod.yml restart web
```

### View Logs
```bash
# View all logs
docker-compose -f docker-compose.prod.yml logs

# View specific service logs
docker-compose -f docker-compose.prod.yml logs web
docker-compose -f docker-compose.prod.yml logs db
```

### Update Application
```bash
# Pull latest changes
git pull origin main

# Rebuild and restart
docker-compose -f docker-compose.prod.yml up -d --build

# Run migrations
docker-compose -f docker-compose.prod.yml exec web php artisan migrate --force
```

### Backup Database
```bash
# Create backup
docker-compose -f docker-compose.prod.yml exec db pg_dump -U postgres ultimate_website > backup_$(date +%Y%m%d_%H%M%S).sql

# Restore backup
docker-compose -f docker-compose.prod.yml exec -T db psql -U postgres ultimate_website < backup_file.sql
```

## 🔒 Security Considerations

### 1. Firewall Setup
```bash
# Install UFW
sudo apt install ufw -y

# Configure firewall
sudo ufw default deny incoming
sudo ufw default allow outgoing
sudo ufw allow ssh
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw allow 8080/tcp  # Jika menggunakan port 8080

# Enable firewall
sudo ufw enable
```

### 2. Database Security
- Gunakan password yang kuat untuk database
- Jangan expose port database ke public
- Regular backup database

### 3. Application Security
- Set APP_DEBUG=false di production
- Gunakan HTTPS jika memungkinkan
- Regular update dependencies

## 🚨 Troubleshooting

### Common Issues

#### 1. Container tidak bisa start
```bash
# Check logs
docker-compose -f docker-compose.prod.yml logs

# Check disk space
df -h

# Check memory
free -h
```

#### 2. Database connection error
```bash
# Check database container
docker-compose -f docker-compose.prod.yml logs db

# Test database connection
docker-compose -f docker-compose.prod.yml exec web php artisan tinker
# Di tinker: DB::connection()->getPdo();
```

#### 3. Permission issues
```bash
# Fix storage permissions
docker-compose -f docker-compose.prod.yml exec web chown -R www-data:www-data storage bootstrap/cache
docker-compose -f docker-compose.prod.yml exec web chmod -R 775 storage bootstrap/cache
```

## 📊 Monitoring

### 1. Container Health
```bash
# Check container health
docker ps --format "table {{.Names}}\t{{.Status}}\t{{.Ports}}"

# Check resource usage
docker stats
```

### 2. Application Logs
```bash
# Laravel logs
docker-compose -f docker-compose.prod.yml exec web tail -f storage/logs/laravel.log

# Nginx logs
docker-compose -f docker-compose.prod.yml logs nginx
```

## 🎯 Next Steps

1. Setup domain name dan SSL certificate (Let's Encrypt)
2. Configure monitoring (Prometheus, Grafana)
3. Setup automated backups
4. Configure log rotation
5. Setup CI/CD pipeline

---

**Catatan Penting:**
- Ganti `YOUR_VPS_IP` dengan IP VPS Anda
- Ganti `YOUR_USERNAME` dengan username GitHub Anda
- Pastikan semua password menggunakan nilai yang kuat
- Test semua functionality setelah deployment