#!/bin/bash

# 🚀 DIRECT DEPLOYMENT SCRIPT
# Ultimate Website - Docker Migration via GitHub

set -e

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

echo -e "${BLUE}🚀 Starting Direct Deployment...${NC}"

# Configuration
PROJECT_NAME="ultimate_website"
GITHUB_REPO="https://github.com/roediamazess/docker_ultimate_website.git"

# Functions
print_status() { echo -e "${GREEN}✅ $1${NC}"; }
print_info() { echo -e "${BLUE}ℹ️  $1${NC}"; }
print_error() { echo -e "${RED}❌ $1${NC}"; exit 1; }

# Step 1: Update system
print_info "Updating system packages..."
sudo apt update && sudo apt upgrade -y
print_status "System updated"

# Step 2: Install dependencies
print_info "Installing dependencies..."
sudo apt install -y curl wget git unzip software-properties-common apt-transport-https ca-certificates gnupg lsb-release
print_status "Dependencies installed"

# Step 3: Install Docker
print_info "Installing Docker..."
if ! command -v docker &> /dev/null; then
    curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo gpg --dearmor -o /usr/share/keyrings/docker-archive-keyring.gpg
    echo "deb [arch=$(dpkg --print-architecture) signed-by=/usr/share/keyrings/docker-archive-keyring.gpg] https://download.docker.com/linux/ubuntu $(lsb_release -cs) stable" | sudo tee /etc/apt/sources.list.d/docker.list > /dev/null
    sudo apt update
    sudo apt install -y docker-ce docker-ce-cli containerd.io docker-compose-plugin
    sudo systemctl start docker
    sudo systemctl enable docker
    sudo usermod -aG docker $USER
    print_status "Docker installed"
else
    print_status "Docker already installed"
fi

# Step 4: Install Docker Compose
print_info "Installing Docker Compose..."
if ! command -v docker-compose &> /dev/null; then
    sudo curl -L "https://github.com/docker/compose/releases/latest/download/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
    sudo chmod +x /usr/local/bin/docker-compose
    print_status "Docker Compose installed"
else
    print_status "Docker Compose already installed"
fi

# Step 5: Create project directory
print_info "Creating project directory..."
mkdir -p /home/roediamazess/$PROJECT_NAME
cd /home/roediamazess/$PROJECT_NAME
print_status "Project directory created"

# Step 6: Clone repository
print_info "Cloning GitHub repository..."
if [ -d ".git" ]; then
    git pull origin main
else
    git clone $GITHUB_REPO .
fi
print_status "Repository ready"

# Step 7: Create docker-compose.prod.yml
print_info "Creating production config..."
cat > docker-compose.prod.yml << 'EOF'
services:
  web:
    build: .
    container_name: ultimate-website-web-prod
    restart: unless-stopped
    ports:
      - "8080:80"
    volumes:
      - .:/var/www/html
    depends_on:
      db:
        condition: service_healthy
    environment:
      - DB_HOST=db
      - DB_PORT=5432
      - DB_DATABASE=ultimate_website
      - DB_USERNAME=postgres
      - DB_PASSWORD=UltimateWebsite2024!
      - APP_ENV=production
      - APP_DEBUG=false
      - CACHE_DRIVER=redis
      - SESSION_DRIVER=redis
      - REDIS_HOST=redis
      - REDIS_PORT=6379
    networks:
      - ultimate-network

  db:
    image: postgres:15-alpine
    container_name: ultimate-website-db-prod
    restart: unless-stopped
    environment:
      POSTGRES_DB: ultimate_website
      POSTGRES_USER: postgres
      POSTGRES_PASSWORD: UltimateWebsite2024!
    volumes:
      - postgres_data:/var/lib/postgresql/data
    networks:
      - ultimate-network
    healthcheck:
      test: ["CMD-SHELL", "pg_isready -U postgres -d ultimate_website"]
      interval: 30s
      timeout: 10s
      retries: 3
      start_period: 30s

  redis:
    image: redis:7-alpine
    container_name: ultimate-website-redis-prod
    restart: unless-stopped
    volumes:
      - redis_data:/data
    networks:
      - ultimate-network

volumes:
  postgres_data:
  redis_data:

networks:
  ultimate-network:
    driver: bridge
EOF

# Step 8: Create .env file
cat > .env << 'EOF'
APP_NAME="Ultimate Website"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=http://103.150.101.26:8080

DB_CONNECTION=pgsql
DB_HOST=db
DB_PORT=5432
DB_DATABASE=ultimate_website
DB_USERNAME=postgres
DB_PASSWORD=UltimateWebsite2024!

CACHE_DRIVER=redis
SESSION_DRIVER=redis
REDIS_HOST=redis
REDIS_PORT=6379

APP_TIMEZONE=Asia/Jakarta
APP_LOCALE=id

MAIL_MAILER=smtp
MAIL_HOST=localhost
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="noreply@ultimatewebsite.com"
MAIL_FROM_NAME="${APP_NAME}"
EOF
print_status "Configuration files created"

# Step 9: Set permissions
sudo chown -R $USER:$USER /home/roediamazess/$PROJECT_NAME
chmod -R 755 /home/roediamazess/$PROJECT_NAME
chmod -R 775 storage bootstrap/cache 2>/dev/null || true
print_status "Permissions set"

# Step 10: Stop existing containers
docker-compose -f docker-compose.prod.yml down 2>/dev/null || true
print_status "Existing containers stopped"

# Step 11: Build and start
print_info "Building Docker images..."
docker-compose -f docker-compose.prod.yml build --no-cache
print_status "Docker images built"

print_info "Starting containers..."
docker-compose -f docker-compose.prod.yml up -d
print_status "Containers started"

# Step 12: Wait and setup
print_info "Waiting for services..."
sleep 30

print_info "Generating Laravel key..."
docker-compose -f docker-compose.prod.yml exec -T web php artisan key:generate --force

print_info "Running migrations..."
docker-compose -f docker-compose.prod.yml exec -T web php artisan migrate --force

print_info "Optimizing caches..."
docker-compose -f docker-compose.prod.yml exec -T web php artisan config:clear
docker-compose -f docker-compose.prod.yml exec -T web php artisan cache:clear
docker-compose -f docker-compose.prod.yml exec -T web php artisan view:clear
docker-compose -f docker-compose.prod.yml exec -T web php artisan route:clear
docker-compose -f docker-compose.prod.yml exec -T web php artisan config:cache
docker-compose -f docker-compose.prod.yml exec -T web php artisan route:cache
docker-compose -f docker-compose.prod.yml exec -T web php artisan view:cache

print_info "Setting storage permissions..."
docker-compose -f docker-compose.prod.yml exec -T web chown -R www-data:www-data storage bootstrap/cache
docker-compose -f docker-compose.prod.yml exec -T web chmod -R 775 storage bootstrap/cache
print_status "Laravel setup completed"

# Step 13: Configure firewall
print_info "Configuring firewall..."
sudo ufw allow 22/tcp
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw allow 8080/tcp
sudo ufw --force enable
print_status "Firewall configured"

# Step 14: Final check
print_info "Checking deployment status..."
docker-compose -f docker-compose.prod.yml ps

print_info "Testing health endpoint..."
if curl -f http://localhost:8080/health.php > /dev/null 2>&1; then
    print_status "Health check passed!"
else
    echo -e "${YELLOW}⚠️  Health check failed. Checking logs...${NC}"
    docker-compose -f docker-compose.prod.yml logs web
fi

# Success message
echo ""
echo -e "${GREEN}🎉 DEPLOYMENT COMPLETED SUCCESSFULLY! 🎉${NC}"
echo -e "${GREEN}==========================================${NC}"
echo -e "${GREEN}Website URL: http://103.150.101.26:8080${NC}"
echo -e "${GREEN}Health Check: http://103.150.101.26:8080/health.php${NC}"
echo -e "${GREEN}Database: PostgreSQL on port 5432${NC}"
echo -e "${GREEN}Cache: Redis on port 6379${NC}"
echo ""
echo -e "${BLUE}📋 Management Commands:${NC}"
echo -e "${BLUE}Check status: docker-compose -f docker-compose.prod.yml ps${NC}"
echo -e "${BLUE}View logs: docker-compose -f docker-compose.prod.yml logs -f${NC}"
echo -e "${BLUE}Restart: docker-compose -f docker-compose.prod.yml restart${NC}"
echo -e "${BLUE}Stop: docker-compose -f docker-compose.prod.yml down${NC}"
echo -e "${BLUE}Start: docker-compose -f docker-compose.prod.yml up -d${NC}"
echo -e "${BLUE}Update: git pull origin main${NC}"
echo ""
echo -e "${GREEN}Deployment completed!${NC}"
