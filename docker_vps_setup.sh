#!/bin/bash

# 🐳 Ultimate Website - Docker VPS Setup Script
# Script untuk setup awal VPS sebelum deployment

set -e

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

print_status() {
    echo -e "${GREEN}✅ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

print_error() {
    echo -e "${RED}❌ $1${NC}"
}

print_info() {
    echo -e "${BLUE}ℹ️  $1${NC}"
}

echo -e "${BLUE}🐳 Ultimate Website - VPS Setup${NC}"
echo -e "${BLUE}===============================${NC}"

# Check if running as root
if [[ $EUID -eq 0 ]]; then
   print_error "Script ini tidak boleh dijalankan sebagai root!"
   print_info "Jalankan dengan: bash docker_vps_setup.sh"
   exit 1
fi

# Get VPS information
print_info "Gathering VPS information..."
VPS_IP=$(curl -s ifconfig.me)
VPS_HOSTNAME=$(hostname)
VPS_USER=$(whoami)

print_status "VPS IP: $VPS_IP"
print_status "Hostname: $VPS_HOSTNAME"
print_status "User: $VPS_USER"

# Update system
print_info "Updating system packages..."
sudo apt update && sudo apt upgrade -y
print_status "System updated"

# Install essential packages
print_info "Installing essential packages..."
sudo apt install -y curl wget git unzip software-properties-common apt-transport-https ca-certificates gnupg lsb-release
print_status "Essential packages installed"

# Install Docker
print_info "Installing Docker..."
if ! command -v docker &> /dev/null; then
    curl -fsSL https://get.docker.com -o get-docker.sh
    sudo sh get-docker.sh
    sudo usermod -aG docker $USER
    print_status "Docker installed successfully"
    print_warning "Anda perlu logout dan login kembali untuk menggunakan Docker tanpa sudo"
else
    print_warning "Docker sudah terinstall"
fi

# Install Docker Compose
print_info "Installing Docker Compose..."
if ! command -v docker-compose &> /dev/null; then
    sudo curl -L "https://github.com/docker/compose/releases/latest/download/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
    sudo chmod +x /usr/local/bin/docker-compose
    print_status "Docker Compose installed successfully"
else
    print_warning "Docker Compose sudah terinstall"
fi

# Install additional tools
print_info "Installing additional tools..."
sudo apt install -y htop tree nano vim ufw fail2ban
print_status "Additional tools installed"

# Setup firewall
print_info "Configuring firewall..."
sudo ufw --force enable
sudo ufw default deny incoming
sudo ufw default allow outgoing
sudo ufw allow ssh
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw allow 8080/tcp
print_status "Firewall configured"

# Setup fail2ban
print_info "Configuring fail2ban..."
sudo systemctl enable fail2ban
sudo systemctl start fail2ban
print_status "Fail2ban configured"

# Create project directory
print_info "Creating project directory..."
sudo mkdir -p /opt/ultimate-website
sudo chown $USER:$USER /opt/ultimate-website
print_status "Project directory created"

# Setup swap (if needed)
print_info "Checking swap configuration..."
if [ $(free -m | awk 'NR==2{printf "%.0f", $3/$2*100}') -gt 80 ]; then
    print_warning "Memory usage is high, setting up swap..."
    sudo fallocate -l 2G /swapfile
    sudo chmod 600 /swapfile
    sudo mkswap /swapfile
    sudo swapon /swapfile
    echo '/swapfile none swap sw 0 0' | sudo tee -a /etc/fstab
    print_status "Swap configured"
else
    print_status "Memory usage is normal, no swap needed"
fi

# Setup log rotation
print_info "Setting up log rotation..."
sudo tee /etc/logrotate.d/docker-containers > /dev/null <<EOF
/var/lib/docker/containers/*/*.log {
    rotate 7
    daily
    compress
    size=1M
    missingok
    delaycompress
    copytruncate
}
EOF
print_status "Log rotation configured"

# Create management scripts
print_info "Creating management scripts..."

# Create start script
sudo tee /usr/local/bin/ultimate-website-start > /dev/null <<EOF
#!/bin/bash
cd /opt/ultimate-website
docker-compose -f docker-compose.prod.yml up -d
echo "Ultimate Website started"
EOF

# Create stop script
sudo tee /usr/local/bin/ultimate-website-stop > /dev/null <<EOF
#!/bin/bash
cd /opt/ultimate-website
docker-compose -f docker-compose.prod.yml down
echo "Ultimate Website stopped"
EOF

# Create status script
sudo tee /usr/local/bin/ultimate-website-status > /dev/null <<EOF
#!/bin/bash
cd /opt/ultimate-website
docker-compose -f docker-compose.prod.yml ps
EOF

# Create logs script
sudo tee /usr/local/bin/ultimate-website-logs > /dev/null <<EOF
#!/bin/bash
cd /opt/ultimate-website
docker-compose -f docker-compose.prod.yml logs -f
EOF

# Make scripts executable
sudo chmod +x /usr/local/bin/ultimate-website-*

print_status "Management scripts created"

# Create backup script
print_info "Creating backup script..."
sudo tee /usr/local/bin/ultimate-website-backup > /dev/null <<EOF
#!/bin/bash
BACKUP_DIR="/opt/backups"
DATE=\$(date +%Y%m%d_%H%M%S)
mkdir -p \$BACKUP_DIR

cd /opt/ultimate-website

# Backup database
docker-compose -f docker-compose.prod.yml exec -T db pg_dump -U postgres ultimate_website > \$BACKUP_DIR/db_backup_\$DATE.sql

# Backup uploads
tar -czf \$BACKUP_DIR/uploads_backup_\$DATE.tar.gz uploads/

# Keep only last 7 days of backups
find \$BACKUP_DIR -name "*.sql" -mtime +7 -delete
find \$BACKUP_DIR -name "*.tar.gz" -mtime +7 -delete

echo "Backup completed: \$DATE"
EOF

sudo chmod +x /usr/local/bin/ultimate-website-backup
print_status "Backup script created"

# Setup cron job for backups
print_info "Setting up automated backups..."
(crontab -l 2>/dev/null; echo "0 2 * * * /usr/local/bin/ultimate-website-backup") | crontab -
print_status "Automated backups configured (daily at 2 AM)"

# Create monitoring script
print_info "Creating monitoring script..."
sudo tee /usr/local/bin/ultimate-website-monitor > /dev/null <<EOF
#!/bin/bash
cd /opt/ultimate-website

# Check if containers are running
if ! docker-compose -f docker-compose.prod.yml ps | grep -q "Up"; then
    echo "ALERT: Some containers are not running!"
    docker-compose -f docker-compose.prod.yml ps
    exit 1
fi

# Check disk space
DISK_USAGE=\$(df / | awk 'NR==2 {print \$5}' | sed 's/%//')
if [ \$DISK_USAGE -gt 80 ]; then
    echo "ALERT: Disk usage is high: \$DISK_USAGE%"
fi

# Check memory usage
MEM_USAGE=\$(free | awk 'NR==2{printf "%.0f", \$3*100/\$2}')
if [ \$MEM_USAGE -gt 80 ]; then
    echo "ALERT: Memory usage is high: \$MEM_USAGE%"
fi

echo "System is healthy"
EOF

sudo chmod +x /usr/local/bin/ultimate-website-monitor
print_status "Monitoring script created"

# Setup monitoring cron job
(crontab -l 2>/dev/null; echo "*/5 * * * * /usr/local/bin/ultimate-website-monitor") | crontab -
print_status "Monitoring configured (every 5 minutes)"

# Final system information
echo -e "\n${GREEN}🎉 VPS Setup completed successfully!${NC}"
echo -e "${BLUE}===========================================${NC}"
echo -e "${GREEN}✅ VPS IP: $VPS_IP${NC}"
echo -e "${GREEN}✅ Hostname: $VPS_HOSTNAME${NC}"
echo -e "${GREEN}✅ User: $VPS_USER${NC}"
echo -e "${GREEN}✅ Docker: $(docker --version)${NC}"
echo -e "${GREEN}✅ Docker Compose: $(docker-compose --version)${NC}"

echo -e "\n${YELLOW}📋 Next Steps:${NC}"
echo -e "${YELLOW}1. Clone your repository to /opt/ultimate-website${NC}"
echo -e "${YELLOW}2. Run deployment script: bash deploy_docker_vps.sh${NC}"
echo -e "${YELLOW}3. Or use quick deployment from Windows${NC}"

echo -e "\n${BLUE}🛠️  Management Commands:${NC}"
echo -e "${BLUE}Start:   ultimate-website-start${NC}"
echo -e "${BLUE}Stop:    ultimate-website-stop${NC}"
echo -e "${BLUE}Status:  ultimate-website-status${NC}"
echo -e "${BLUE}Logs:    ultimate-website-logs${NC}"
echo -e "${BLUE}Backup:  ultimate-website-backup${NC}"
echo -e "${BLUE}Monitor: ultimate-website-monitor${NC}"

echo -e "\n${YELLOW}⚠️  Important Notes:${NC}"
echo -e "${YELLOW}1. Logout and login again to use Docker without sudo${NC}"
echo -e "${YELLOW}2. Make sure to configure your .env file properly${NC}"
echo -e "${YELLOW}3. Test your application after deployment${NC}"

print_status "VPS setup completed!"
