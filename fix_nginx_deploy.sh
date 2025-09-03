#!/bin/bash

echo "=== Fixing Nginx Configuration and Deploying Laravel App ==="

# Fix nginx.conf
echo "Creating correct nginx.conf..."
cat > nginx.conf << 'NGINX_EOF'
events {
    worker_connections 1024;
}

http {
    upstream web {
        server web:80;
    }

    server {
        listen 80;
        server_name _;
        root /var/www/html/public;
        index index.php index.html;

        location / {
            try_files $uri $uri/ /index.php?$query_string;
        }

        location ~ \.php$ {
            fastcgi_pass web:80;
            fastcgi_index index.php;
            fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
            include fastcgi_params;
        }

        location ~ /\.ht {
            deny all;
        }
    }
}
NGINX_EOF

echo "nginx.conf created successfully!"

# Stop all containers
echo "Stopping all containers..."
sudo docker-compose -f docker-compose.prod.yml down

# Remove conflicting containers
echo "Removing conflicting containers..."
sudo docker rm -f ultimate-website-db-prod || true
sudo docker rm -f ultimate-website-redis-prod || true
sudo docker rm -f ultimate-website-nginx-prod || true
sudo docker rm -f ultimate-website-web-prod || true

# Clean up
echo "Cleaning up Docker resources..."
sudo docker container prune -f
sudo docker network prune -f

# Start services
echo "Starting all services..."
sudo docker-compose -f docker-compose.prod.yml up -d

# Wait for services to start
echo "Waiting for services to start..."
sleep 30

# Check status
echo "Checking service status..."
sudo docker-compose -f docker-compose.prod.yml ps

# Test website access
echo "Testing website access..."
curl -I http://localhost:8080 || echo "Website not accessible yet"

echo "=== Deployment Complete ==="
echo "Check the status above. If nginx is still restarting, check logs with:"
echo "sudo docker-compose -f docker-compose.prod.yml logs nginx --tail=50"
