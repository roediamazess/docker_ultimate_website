# 🤝 Contributing to Ultimate Website

Terima kasih atas minat Anda untuk berkontribusi pada Ultimate Website! 

## 🚀 Getting Started

### Prerequisites
- Docker Desktop
- Git
- Basic knowledge of PHP, PostgreSQL, and Docker

### Setup Development Environment
1. Fork repository ini
2. Clone fork Anda:
   ```bash
   git clone https://github.com/YOUR_USERNAME/docker_ultimate_website.git
   cd docker_ultimate_website
   ```
3. Setup environment:
   ```bash
   cp env.example .env
   ```
4. Start development environment:
   ```bash
   .\start_website_simple.ps1
   ```

## 📝 Development Guidelines

### Code Style
- Gunakan PSR-12 coding standards untuk PHP
- Gunakan meaningful variable names
- Tambahkan comments untuk complex logic
- Keep functions small and focused

### Database Changes
- Buat migration script untuk perubahan database
- Test schema changes thoroughly
- Update `postgres_schema.sql` jika ada perubahan struktur

### Testing
- Test semua fitur sebelum submit
- Pastikan website berjalan tanpa error
- Test di berbagai browser jika ada perubahan UI

## 🔄 Workflow

### 1. Create Feature Branch
```bash
git checkout -b feature/your-feature-name
```

### 2. Make Changes
- Edit files sesuai kebutuhan
- Test perubahan Anda
- Commit dengan message yang jelas

### 3. Commit Guidelines
```bash
git add .
git commit -m "feat: add new user management feature"
git commit -m "fix: resolve database connection issue"
git commit -m "docs: update README with new instructions"
```

### 4. Push and Create Pull Request
```bash
git push origin feature/your-feature-name
```

## 🐛 Bug Reports

Saat melaporkan bug, mohon sertakan:
- Deskripsi bug yang jelas
- Steps to reproduce
- Expected vs actual behavior
- Environment details (OS, Docker version, etc.)
- Screenshots jika relevan

## 💡 Feature Requests

Untuk feature requests:
- Jelaskan fitur yang diinginkan
- Berikan use case yang jelas
- Sertakan mockup/design jika ada
- Diskusikan dengan maintainer sebelum implementasi

## 📋 Pull Request Guidelines

### Before Submitting PR
- [ ] Code follows project standards
- [ ] All tests pass
- [ ] Documentation updated
- [ ] No sensitive data exposed
- [ ] Docker containers work properly

### PR Template
```markdown
## Description
Brief description of changes

## Type of Change
- [ ] Bug fix
- [ ] New feature
- [ ] Documentation update
- [ ] Performance improvement

## Testing
- [ ] Tested locally
- [ ] All containers start properly
- [ ] Database migrations work
- [ ] UI changes tested

## Screenshots (if applicable)
Add screenshots here
```

## 🏷️ Issue Labels

- `bug` - Something isn't working
- `enhancement` - New feature or request
- `documentation` - Improvements or additions to documentation
- `good first issue` - Good for newcomers
- `help wanted` - Extra attention is needed

## 📞 Getting Help

Jika Anda membutuhkan bantuan:
- Check existing issues dan discussions
- Create new issue dengan label yang sesuai
- Join community discussions

## 🎉 Recognition

Kontributor akan diakui di:
- README.md contributors section
- Release notes
- Project documentation

---

**Terima kasih telah berkontribusi pada Ultimate Website! 🚀**
