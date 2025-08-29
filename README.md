# 🚀 Ultimate Website - Docker Edition

Website management system yang modern dengan teknologi Docker, PostgreSQL, dan PHP 8.1.

## 🌟 Fitur Utama

- **Modern Stack**: Docker, PostgreSQL, Redis, Mailpit
- **User Management**: Multi-role system (Administrator, Management, Admin Office, User, Client)
- **Project Management**: Complete project lifecycle management
- **Activity Tracking**: Real-time activity monitoring
- **Customer Management**: Comprehensive customer database
- **Responsive Design**: Modern UI dengan Bootstrap 5
- **Auto-Recovery**: Sistem recovery otomatis setelah restart
- **Easy Management**: One-click start, stop, dan recovery

## 🛠️ Tech Stack

- **Backend**: PHP 8.1 + Apache
- **Database**: PostgreSQL 15
- **Cache**: Redis 7
- **Email Testing**: Mailpit
- **Database Admin**: PgAdmin 4
- **Containerization**: Docker & Docker Compose
- **Health Monitoring**: Auto-health checks dan recovery

## 🚀 Quick Start

### Prerequisites
- Docker Desktop
- Git

### 1. Clone Repository
```bash
git clone https://github.com/roediamazess/docker_ultimate_website.git
cd docker_ultimate_website
```

### 2. Setup Environment
```bash
# Copy environment file
cp env.example .env
```

### 3. Start Application
```bash
# Double click START_WEBSITE.bat (Paling Mudah)
# Atau menggunakan PowerShell
.\start_website.ps1

# Atau menggunakan Docker Compose
docker-compose up -d
```

### 4. Access Application
- **Quick Access**: http://localhost:8080/quick_access.php (Auto-login)
- **Website**: http://localhost:8080
- **Database Admin**: http://localhost:8081
  - Email: `admin@admin.com`
  - Password: `admin`
- **Email Testing**: http://localhost:8025

### 5. Login Credentials
- **Test User**: `test@test.com` / `test123`
- **Admin**: `admin@test.com` / (password dari database)
- **PMS**: `pms@ppsolution.com` / (password dari database)

## 📁 Project Structure

```
docker_ultimate_website/
├── 🚀 START_WEBSITE.bat        # One-click start (Double click)
├── 🛑 STOP_WEBSITE.bat         # One-click stop (Double click)
├── 🔧 RECOVERY_WEBSITE.bat     # Recovery after restart (Double click)
├── 🧹 CLEANUP_DOCKER.bat       # Docker maintenance (Double click)
├── assets/                     # CSS, JS, Images
├── partials/                   # PHP includes
├── uploads/                    # User uploads
├── Dockerfile                  # PHP/Apache image
├── docker-compose.yml          # Multi-service setup
├── docker-entrypoint.sh        # Container startup script
├── postgres_schema.sql         # Database schema
├── .env                        # Environment variables
├── quick_access.php            # Auto-login interface
├── health.php                  # Health check endpoint
├── start_website.ps1           # Start script with health checks
├── stop_website.ps1            # Stop script
├── recovery_website.ps1        # Recovery script
├── manage_website.ps1          # Management script
└── cleanup_docker.ps1          # Cleanup script
```

## 🔧 Management Scripts

### 🚀 Quick Start (Recommended)
```bash
# Double click START_WEBSITE.bat
# Atau
.\start_website.ps1
```

### 🛑 Stop Website
```bash
# Double click STOP_WEBSITE.bat
# Atau
.\stop_website.ps1
```

### 🔧 Recovery After Restart
```bash
# Double click RECOVERY_WEBSITE.bat
# Atau
.\recovery_website.ps1
```

### 🧹 Docker Maintenance
```bash
# Double click CLEANUP_DOCKER.bat
# Atau
.\cleanup_docker.ps1
```

### 📊 Advanced Management
```bash
# Test all services
.\manage_website.ps1 -Action test

# View logs
.\manage_website.ps1 -Action logs

# Full cleanup
.\manage_website.ps1 -Action cleanup
```

## 🗄️ Database

### Default Credentials
- **Host**: `db` (Docker service)
- **Port**: `5432`
- **Database**: `ultimate_website`
- **Username**: `postgres`
- **Password**: `password`

### Schema
- **users**: User management
- **customers**: Customer database
- **projects**: Project management
- **activities**: Activity tracking

## 🛠️ Development

### Making Changes
1. Edit files in the project directory
2. Changes are automatically reflected (volume mounting)
3. Restart containers if needed: `docker-compose restart`

### Rebuilding Containers
```bash
docker-compose up -d --build
```

### View Logs
```bash
# All services
docker-compose logs

# Specific service
docker-compose logs web
docker-compose logs db
```

## 🔒 Security

- Environment variables for sensitive data
- Database password protection
- Session management
- Role-based access control

## 🚨 Troubleshooting

### Website Tidak Bisa Diakses Setelah Restart
```bash
# Double click RECOVERY_WEBSITE.bat
# Atau
.\recovery_website.ps1
```

### Docker Issues
```bash
# Cleanup Docker
.\CLEANUP_DOCKER.bat

# Restart Docker Desktop
# Kemudian jalankan recovery
```

### Database Issues
```bash
# Check database connection
.\manage_website.ps1 -Action test

# View logs
.\manage_website.ps1 -Action logs
```

## 📊 Monitoring

### Container Status
```bash
docker-compose ps
```

### Health Check
```bash
# Check website health
curl http://localhost:8080/health.php

# Or visit in browser
http://localhost:8080/health.php
```

### Resource Usage
```bash
docker stats
```

### Logs
```bash
# All services
docker-compose logs

# Specific service
docker-compose logs web
docker-compose logs db
```

### Database Backup
```bash
docker exec ultimate-website-db pg_dump -U postgres ultimate_website > backup_$(date +%Y-%m-%d).sql
```

## 🐛 Troubleshooting

### Website Not Accessible
1. Check Docker Desktop is running
2. Verify containers: `docker-compose ps`
3. Check logs: `docker-compose logs web`

### Database Connection Issues
1. Ensure database container is running
2. Check environment variables in `.env`
3. Restart database: `docker-compose restart db`

### Permission Issues
1. Check file permissions in container
2. Rebuild container: `docker-compose up -d --build`

## 🤝 Contributing

1. Fork the repository
2. Create feature branch: `git checkout -b feature-name`
3. Commit changes: `git commit -am 'Add feature'`
4. Push to branch: `git push origin feature-name`
5. Submit pull request

## 📝 License

This project is licensed under the MIT License.

## 🆘 Support

For support and questions:
- Create an issue on GitHub
- Check the troubleshooting section
- Review the documentation

---

**Made with ❤️ using Docker & Modern Web Technologies** 
