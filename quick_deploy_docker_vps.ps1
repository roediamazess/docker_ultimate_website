# 🐳 Ultimate Website - Quick Docker VPS Deployment (PowerShell)
# Script untuk Windows yang akan menjalankan deployment di VPS

param(
    [Parameter(Mandatory=$true)]
    [string]$VpsIp,
    
    [Parameter(Mandatory=$true)]
    [string]$SshUser,
    
    [Parameter(Mandatory=$false)]
    [string]$SshKey = "",
    
    [Parameter(Mandatory=$false)]
    [string]$GitHubRepo = "https://github.com/YOUR_USERNAME/ultimate-website.git",
    
    [Parameter(Mandatory=$false)]
    [string]$DbPassword = "UltimateWebsite2024!"
)

# Colors for output
$Red = "`e[31m"
$Green = "`e[32m"
$Yellow = "`e[33m"
$Blue = "`e[34m"
$Reset = "`e[0m"

function Write-ColorOutput {
    param([string]$Message, [string]$Color = $Reset)
    Write-Host "$Color$Message$Reset"
}

function Write-Status {
    param([string]$Message)
    Write-ColorOutput "✅ $Message" $Green
}

function Write-Warning {
    param([string]$Message)
    Write-ColorOutput "⚠️  $Message" $Yellow
}

function Write-Error {
    param([string]$Message)
    Write-ColorOutput "❌ $Message" $Red
}

function Write-Info {
    param([string]$Message)
    Write-ColorOutput "ℹ️  $Message" $Blue
}

Write-ColorOutput "🐳 Ultimate Website - Quick Docker VPS Deployment" $Blue
Write-ColorOutput "===============================================" $Blue

# Check if SSH key is provided
$sshCommand = ""
if ($SshKey -ne "") {
    if (Test-Path $SshKey) {
        $sshCommand = "-i `"$SshKey`""
        Write-Status "SSH key found: $SshKey"
    } else {
        Write-Error "SSH key file not found: $SshKey"
        exit 1
    }
} else {
    Write-Warning "No SSH key provided, will use password authentication"
}

# Create deployment script content
$deploymentScript = @"
#!/bin/bash
set -e

# Configuration
PROJECT_DIR="/opt/ultimate-website"
GITHUB_REPO="$GitHubRepo"
VPS_IP="$VpsIp"
DB_PASSWORD="$DbPassword"

echo "🐳 Ultimate Website - Docker VPS Deployment"
echo "==========================================="

# Update system
echo "Step 1: Update system packages..."
sudo apt update && sudo apt upgrade -y

# Install Docker
echo "Step 2: Installing Docker..."
if ! command -v docker &> /dev/null; then
    curl -fsSL https://get.docker.com -o get-docker.sh
    sudo sh get-docker.sh
    sudo usermod -aG docker `$USER
    echo "✅ Docker installed successfully"
else
    echo "⚠️  Docker sudah terinstall"
fi

# Install Docker Compose
echo "Step 3: Installing Docker Compose..."
if ! command -v docker-compose &> /dev/null; then
    sudo curl -L "https://github.com/docker/compose/releases/latest/download/docker-compose-`$(uname -s)-`$(uname -m)" -o /usr/local/bin/docker-compose
    sudo chmod +x /usr/local/bin/docker-compose
    echo "✅ Docker Compose installed successfully"
else
    echo "⚠️  Docker Compose sudah terinstall"
fi

# Install Git
echo "Step 4: Installing Git..."
sudo apt install git -y

# Create project directory
echo "Step 5: Setting up project directory..."
sudo mkdir -p `$PROJECT_DIR
sudo chown `$USER:`$USER `$PROJECT_DIR
cd `$PROJECT_DIR

# Clone repository
echo "Step 6: Cloning repository..."
if [ -d ".git" ]; then
    echo "Repository sudah ada, pulling latest changes..."
    git pull origin main
else
    echo "Cloning repository..."
    git clone `$GITHUB_REPO .
fi

# Setup environment
echo "Step 7: Setting up environment configuration..."
if [ ! -f ".env" ]; then
    cp env.production .env
fi

# Generate APP_KEY
echo "Step 8: Generating application key..."
APP_KEY=`$(openssl rand -base64 32)
sed -i "s/APP_KEY=.*/APP_KEY=base64:`$APP_KEY/" .env

# Update environment variables
echo "Step 9: Updating environment variables..."
sed -i "s/DB_PASSWORD=.*/DB_PASSWORD=`$DB_PASSWORD/" .env
sed -i "s/APP_URL=.*/APP_URL=http:\/\/`$VPS_IP:8080/" .env

# Setup SSL
echo "Step 10: Setting up SSL certificates..."
mkdir -p ssl
if [ ! -f "ssl/cert.pem" ] || [ ! -f "ssl/key.pem" ]; then
    openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
        -keyout ssl/key.pem \
        -out ssl/cert.pem \
        -subj "/C=ID/ST=Jakarta/L=Jakarta/O=Ultimate Website/CN=`$VPS_IP" \
        -addext "subjectAltName=IP:`$VPS_IP"
fi

# Build and start containers
echo "Step 11: Building and starting Docker containers..."
docker-compose -f docker-compose.prod.yml down --remove-orphans
docker-compose -f docker-compose.prod.yml up -d --build

# Wait for database
echo "Step 12: Waiting for database to be ready..."
sleep 30

# Setup Laravel
echo "Step 13: Setting up Laravel application..."
docker-compose -f docker-compose.prod.yml exec -T web php artisan key:generate --force
docker-compose -f docker-compose.prod.yml exec -T web php artisan config:cache
docker-compose -f docker-compose.prod.yml exec -T web php artisan route:cache
docker-compose -f docker-compose.prod.yml exec -T web php artisan view:cache

# Run migrations
echo "Step 14: Running database migrations..."
docker-compose -f docker-compose.prod.yml exec -T web php artisan migrate --force

# Setup auto-start
echo "Step 15: Setting up auto-start service..."
sudo tee /etc/systemd/system/ultimate-website.service > /dev/null <<EOF
[Unit]
Description=Ultimate Website Docker Compose
Requires=docker.service
After=docker.service

[Service]
Type=oneshot
RemainAfterExit=yes
WorkingDirectory=`$PROJECT_DIR
ExecStart=/usr/local/bin/docker-compose -f docker-compose.prod.yml up -d
ExecStop=/usr/local/bin/docker-compose -f docker-compose.prod.yml down
TimeoutStartSec=0

[Install]
WantedBy=multi-user.target
EOF

sudo systemctl daemon-reload
sudo systemctl enable ultimate-website.service

# Setup firewall
echo "Step 16: Configuring firewall..."
sudo ufw --force enable
sudo ufw default deny incoming
sudo ufw default allow outgoing
sudo ufw allow ssh
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw allow 8080/tcp

# Final verification
echo "Step 17: Verifying deployment..."
sleep 10

echo ""
echo "🎉 Deployment completed successfully!"
echo "==========================================="
echo "✅ Application URL: http://`$VPS_IP:8080"
echo "✅ Health Check: http://`$VPS_IP:8080/health.php"
echo ""
echo "📋 Next Steps:"
echo "1. Test your application at http://`$VPS_IP:8080"
echo "2. Setup domain name and SSL certificate"
echo "3. Configure email settings in .env file"
echo "4. Setup automated backups"
echo ""
echo "🛠️  Management Commands:"
echo "Start:   docker-compose -f docker-compose.prod.yml up -d"
echo "Stop:    docker-compose -f docker-compose.prod.yml down"
echo "Logs:    docker-compose -f docker-compose.prod.yml logs"
echo "Status:  docker-compose -f docker-compose.prod.yml ps"
"@

# Save deployment script to temporary file
$tempScript = "deploy_temp.sh"
$deploymentScript | Out-File -FilePath $tempScript -Encoding UTF8

Write-Info "Step 1: Uploading deployment script to VPS..."

# Upload script to VPS
$uploadCommand = "scp $sshCommand `"$tempScript`" ${SshUser}@${VpsIp}:/tmp/deploy.sh"
Write-Info "Executing: $uploadCommand"

try {
    Invoke-Expression $uploadCommand
    Write-Status "Deployment script uploaded successfully"
} catch {
    Write-Error "Failed to upload deployment script: $_"
    Remove-Item $tempScript -Force
    exit 1
}

Write-Info "Step 2: Executing deployment on VPS..."

# Execute deployment script on VPS
$executeCommand = "ssh $sshCommand ${SshUser}@${VpsIp} 'chmod +x /tmp/deploy.sh && /tmp/deploy.sh'"
Write-Info "Executing deployment on VPS..."

try {
    Invoke-Expression $executeCommand
    Write-Status "Deployment executed successfully"
} catch {
    Write-Error "Deployment failed: $_"
    exit 1
}

Write-Info "Step 3: Cleaning up temporary files..."

# Clean up
Remove-Item $tempScript -Force
$cleanupCommand = "ssh $sshCommand ${SshUser}@${VpsIp} 'rm -f /tmp/deploy.sh'"
Invoke-Expression $cleanupCommand

Write-Status "Temporary files cleaned up"

Write-ColorOutput "" $Reset
Write-ColorOutput "🎉 Deployment completed successfully!" $Green
Write-ColorOutput "===========================================" $Blue
Write-ColorOutput "✅ Application URL: http://$VpsIp:8080" $Green
Write-ColorOutput "✅ Health Check: http://$VpsIp:8080/health.php" $Green
Write-ColorOutput "" $Reset

Write-ColorOutput "📋 Next Steps:" $Yellow
Write-ColorOutput "1. Test your application at http://$VpsIp:8080" $Yellow
Write-ColorOutput "2. Setup domain name and SSL certificate" $Yellow
Write-ColorOutput "3. Configure email settings in .env file" $Yellow
Write-ColorOutput "4. Setup automated backups" $Yellow

Write-ColorOutput "" $Reset
Write-ColorOutput "🛠️  Management Commands:" $Blue
Write-ColorOutput "SSH to VPS: ssh $sshCommand ${SshUser}@${VpsIp}" $Blue
Write-ColorOutput "Start:      docker-compose -f docker-compose.prod.yml up -d" $Blue
Write-ColorOutput "Stop:       docker-compose -f docker-compose.prod.yml down" $Blue
Write-ColorOutput "Logs:       docker-compose -f docker-compose.prod.yml logs" $Blue
Write-ColorOutput "Status:     docker-compose -f docker-compose.prod.yml ps" $Blue

Write-Status "Quick deployment completed!"