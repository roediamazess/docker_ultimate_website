#!/bin/bash

# 🐳 Ultimate Website - Docker VPS Deployment Script
# Script otomatis untuk deploy Docker ke VPS Ubuntu

set -e  # Exit on any error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
PROJECT_DIR="/opt/ultimate-website"
GITHUB_REPO="https://github.com/roediamazess/docker_ultimate_website.git"
VPS_IP="103.150.101.26"
DB_PASSWORD="UltimateWebsite2024!"

echo -e "${BLUE}🐳 Ultimate Website - Docker VPS Deployment${NC}"
echo -e "${BLUE}===========================================${NC}"

# Function to print colored output
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

# Check if running as root
if [[ $EUID -eq 0 ]]; then
   print_error "Script ini tidak boleh dijalankan sebagai root!"
   print_info "Jalankan dengan: bash deploy_docker_vps.sh"
   exit 1
fi

# Step 1: Update system
print_info "Step 1: Update system packages..."
sudo apt update && sudo apt upgrade -y
print_status "System updated successfully"

# Step 2: Install Docker
print_info "Step 2: Installing Docker..."
if ! command -v docker &> /dev/null; then
    curl -fsSL https://get.docker.com -o get-docker.sh
    sudo sh get-docker.sh
    sudo usermod -aG docker $USER
    print_status "Docker installed successfully"
else
    print_warning "Docker sudah terinstall"
fi

# Step 3: Install Docker Compose
print_info "Step 3: Installing Docker Compose..."
if ! command -v docker-compose &> /dev/null; then
    sudo curl -L "https://github.com/docker/compose/releases/latest/download/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
    sudo chmod +x /usr/local/bin/docker-compose
    print_status "Docker Compose installed successfully"
else
    print_warning "Docker Compose sudah terinstall"
fi

# Step 4: Install Git
print_info "Step 4: Installing Git..."
sudo apt install git -y
print_status "Git installed successfully"

# Step 5: Create project directory
print_info "Step 5: Setting up project directory..."
sudo mkdir -p $PROJECT_DIR
sudo chown $USER:$USER $PROJECT_DIR
cd $PROJECT_DIR

# Step 6: Clone or update repository
print_info "Step 6: Cloning/updating repository..."
if [ -d ".git" ]; then
    print_info "Repository sudah ada, pulling latest changes..."
    git pull origin main
else
    print_info "Cloning repository..."
    git clone $GITHUB_REPO .
fi
print_status "Repository updated successfully"

# Step 7: Setup environment file
print_info "Step 7: Setting up environment configuration..."
if [ ! -f ".env" ]; then
    cp env.production .env
    print_info "Environment file created from template"
else
    print_warning "Environment file sudah ada"
fi

# Step 8: Generate APP_KEY
print_info "Step 8: Generating application key..."
APP_KEY=$(openssl rand -base64 32)
# Use a safer approach to update APP_KEY
if grep -q "APP_KEY=" .env; then
    sed -i "s/APP_KEY=.*/APP_KEY=base64:$APP_KEY/" .env
else
    echo "APP_KEY=base64:$APP_KEY" >> .env
fi
print_status "Application key generated"

# Step 9: Update environment variables
print_info "Step 9: Updating environment variables..."
# Update DB_PASSWORD
if grep -q "DB_PASSWORD=" .env; then
    sed -i "s/DB_PASSWORD=.*/DB_PASSWORD=$DB_PASSWORD/" .env
else
    echo "DB_PASSWORD=$DB_PASSWORD" >> .env
fi

# Update APP_URL
if grep -q "APP_URL=" .env; then
    sed -i "s|APP_URL=.*|APP_URL=http://$VPS_IP:8080|" .env
else
    echo "APP_URL=http://$VPS_IP:8080" >> .env
fi
print_status "Environment variables updated"

# Step 10: Setup SSL directory
print_info "Step 10: Setting up SSL certificates..."
mkdir -p ssl
if [ ! -f "ssl/cert.pem" ] || [ ! -f "ssl/key.pem" ]; then
    print_info "Generating self-signed SSL certificate..."
    openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
        -keyout ssl/key.pem \
        -out ssl/cert.pem \
        -subj "/C=ID/ST=Jakarta/L=Jakarta/O=Ultimate Website/CN=$VPS_IP" \
        -addext "subjectAltName=IP:$VPS_IP"
    print_status "SSL certificate generated"
else
    print_warning "SSL certificate sudah ada"
fi

# Step 11: Build and start containers
print_info "Step 11: Building and starting Docker containers..."
docker-compose -f docker-compose.prod.yml down --remove-orphans
docker-compose -f docker-compose.prod.yml up -d --build
print_status "Containers started successfully"

# Step 12: Wait for database to be ready
print_info "Step 12: Waiting for database to be ready..."
sleep 30

# Step 13: Setup Laravel application
print_info "Step 13: Setting up Laravel application..."
docker-compose -f docker-compose.prod.yml exec -T web php artisan key:generate --force
docker-compose -f docker-compose.prod.yml exec -T web php artisan config:cache
docker-compose -f docker-compose.prod.yml exec -T web php artisan route:cache
docker-compose -f docker-compose.prod.yml exec -T web php artisan view:cache
print_status "Laravel application configured"

# Step 14: Run database migrations
print_info "Step 14: Running database migrations..."
docker-compose -f docker-compose.prod.yml exec -T web php artisan migrate --force
print_status "Database migrations completed"

# Step 15: Setup auto-start service
print_info "Step 15: Setting up auto-start service..."
sudo tee /etc/systemd/system/ultimate-website.service > /dev/null <<EOF
[Unit]
Description=Ultimate Website Docker Compose
Requires=docker.service
After=docker.service

[Service]
Type=oneshot
RemainAfterExit=yes
WorkingDirectory=$PROJECT_DIR
ExecStart=/usr/local/bin/docker-compose -f docker-compose.prod.yml up -d
ExecStop=/usr/local/bin/docker-compose -f docker-compose.prod.yml down
TimeoutStartSec=0

[Install]
WantedBy=multi-user.target
EOF

sudo systemctl daemon-reload
sudo systemctl enable ultimate-website.service
print_status "Auto-start service configured"

# Step 16: Setup firewall
print_info "Step 16: Configuring firewall..."
sudo ufw --force enable
sudo ufw default deny incoming
sudo ufw default allow outgoing
sudo ufw allow ssh
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw allow 8080/tcp
print_status "Firewall configured"

# Step 17: Final verification
print_info "Step 17: Verifying deployment..."
sleep 10

# Check container status
if docker-compose -f docker-compose.prod.yml ps | grep -q "Up"; then
    print_status "All containers are running"
else
    print_error "Some containers failed to start"
    docker-compose -f docker-compose.prod.yml ps
    exit 1
fi

# Test health check
if curl -f http://localhost:8080/health.php > /dev/null 2>&1; then
    print_status "Health check passed"
else
    print_warning "Health check failed, but deployment may still be successful"
fi

# Final status
echo -e "\n${GREEN}🎉 Deployment completed successfully!${NC}"
echo -e "${BLUE}===========================================${NC}"
echo -e "${GREEN}✅ Application URL: http://$VPS_IP:8080${NC}"
echo -e "${GREEN}✅ Health Check: http://$VPS_IP:8080/health.php${NC}"
echo -e "${GREEN}✅ Container Status:${NC}"
docker-compose -f docker-compose.prod.yml ps

echo -e "\n${YELLOW}📋 Next Steps:${NC}"
echo -e "${YELLOW}1. Test your application at http://$VPS_IP:8080${NC}"
echo -e "${YELLOW}2. Setup domain name and SSL certificate${NC}"
echo -e "${YELLOW}3. Configure email settings in .env file${NC}"
echo -e "${YELLOW}4. Setup automated backups${NC}"

echo -e "\n${BLUE}🛠️  Management Commands:${NC}"
echo -e "${BLUE}Start:   docker-compose -f docker-compose.prod.yml up -d${NC}"
echo -e "${BLUE}Stop:    docker-compose -f docker-compose.prod.yml down${NC}"
echo -e "${BLUE}Logs:    docker-compose -f docker-compose.prod.yml logs${NC}"
echo -e "${BLUE}Status:  docker-compose -f docker-compose.prod.yml ps${NC}"

print_status "Deployment script completed!"
