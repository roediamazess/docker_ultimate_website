# 🚀 Ultimate Website - Docker Edition

Website management system yang modern dengan teknologi Docker, PostgreSQL, dan PHP 8.1.

## 🌟 Fitur Utama

- **Modern Stack**: Docker, PostgreSQL, Redis, Mailpit
- **User Management**: Multi-role system (Administrator, Management, Admin Office, User, Client)
- **Project Management**: Complete project lifecycle management
- **Activity Tracking**: Real-time activity monitoring
- **Customer Management**: Comprehensive customer database
- **Responsive Design**: Modern UI dengan Bootstrap 5

## 🛠️ Tech Stack

- **Backend**: PHP 8.1 + Apache
- **Database**: PostgreSQL 15
- **Cache**: Redis 7
- **Email Testing**: Mailpit
- **Database Admin**: PgAdmin 4
- **Containerization**: Docker & Docker Compose

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
# Using PowerShell (Windows)
.\start_website_simple.ps1

# Using Docker Compose
docker-compose up -d
```

### 4. Access Application
- **Website**: http://localhost:8080
- **Database Admin**: http://localhost:8081
  - Email: `admin@admin.com`
  - Password: `admin`
- **Email Testing**: http://localhost:8025

## 📁 Project Structure

```
docker_ultimate_website/
├── assets/                 # CSS, JS, Images
├── partials/              # PHP includes
├── uploads/               # User uploads
├── Dockerfile             # PHP/Apache image
├── docker-compose.yml     # Multi-service setup
├── docker-entrypoint.sh   # Container startup script
├── postgres_schema.sql    # Database schema
├── .env                   # Environment variables
├── start_website_simple.ps1  # Start script
├── stop_website.ps1       # Stop script
└── test_website.ps1       # Test script
```

## 🔧 Management Scripts

### Start Website
```powershell
.\start_website_simple.ps1
```

### Stop Website
```powershell
.\stop_website.ps1
```

### Test Website
```powershell
.\test_website.ps1
```

### Setup Auto Startup
```powershell
.\setup_auto_startup.ps1
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

## 📊 Monitoring

### Container Status
```bash
docker-compose ps
```

### Resource Usage
```bash
docker stats
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
