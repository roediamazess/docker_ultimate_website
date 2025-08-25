# Ultimate Website - Complete Profile Management System

A modern, feature-rich web application built with PHP, PostgreSQL, and Bootstrap 5, featuring advanced user profile management, role-based access control, and a sophisticated notification system.

## 🚀 Features

### ✨ User Profile Management
- **Profile Photo Upload**: Support for JPG, PNG, GIF with automatic compression
- **Default Avatar Selection**: 10 pre-uploaded professional avatars
- **Photo Management**: Upload, select default, and remove profile photos
- **Profile Information**: Display name, full name, email, tier, and role management
- **Editable Fields**: Start work date and password changes
- **Read-only Fields**: Admin-only fields (display name, full name, email, tier, role)

### 🔔 Advanced Notification System
- **Logo Notification Manager**: Notifications emerge from logo area with smooth animations
- **Modern Styling**: Pill-shaped capsules with glassmorphism effects
- **Progress Bar Animation**: Auto-dismiss with visual progress indicators
- **Theme Support**: Complete light/dark mode compatibility
- **Consistent Spacing**: Ideal 8px spacing between text and progress bars
- **Balanced Icons**: 32px for success/error, 24px for info/warning notifications

### 🗄️ Database & Security
- **Unique Constraints**: display_name and email are unique keys
- **Required Fields**: tier and role have default values and are mandatory
- **Immutable Fields**: Critical user information protected from unauthorized changes
- **Data Validation**: Proper ENUM types for tier and role
- **Password Security**: bcrypt encryption with secure validation
- **CSRF Protection**: Token-based form validation
- **File Upload Security**: MIME type validation and size limits

### 👥 User Management System
- **User Creation**: Add new users with comprehensive validation
- **User Editing**: Update user information with role-based restrictions
- **Role-based Access**: Administrator, Management, Admin Office, User, Client
- **Tier System**: New Born, Tier 1, Tier 2, Tier 3 progression

## 🛠️ Technology Stack

- **Backend**: PHP 7.4+ with PDO
- **Database**: PostgreSQL with ENUM support
- **Frontend**: Bootstrap 5, Custom CSS, Vanilla JavaScript
- **Image Processing**: GD extension with fallback support
- **Security**: bcrypt, CSRF tokens, input sanitization
- **Environment**: XAMPP/WAMP compatible

## 📁 Project Structure

```
ultimate_website/
├── assets/
│   ├── js/
│   │   └── logo-notifications.js     # Advanced notification system
│   └── images/
│       └── default_avatars/          # 10 default avatar images
├── partials/
│   ├── layouts/
│   │   ├── layoutHorizontal.php      # Main navigation layout
│   │   └── layoutBottom.php          # Footer layout
│   └── head.php                      # Common head elements
├── uploads/
│   └── profile_photos/               # User photo storage
├── view-profile.php                  # Complete profile management
├── users.php                         # User management system
├── index.php                         # Dashboard
├── db.php                           # Database connection
├── access_control.php               # Security & permissions
├── user_utils.php                   # User utility functions
└── database_schema_postgres.sql     # Database schema
```

## 🚀 Installation

### Prerequisites
- PHP 7.4+ with GD extension
- PostgreSQL database
- XAMPP/WAMP environment
- Modern web browser

### Setup Steps
1. **Clone Repository**
   ```bash
   git clone https://github.com/roediamazess/ultimate_website.git
   cd ultimate_website
   ```

2. **Database Setup**
   ```bash
   # Import database schema
   psql -U your_username -d your_database -f database_schema_postgres.sql
   ```

3. **Configuration**
   - Update database connection in `db.php`
   - Set proper permissions for `uploads/` directory
   - Upload default avatar images to `assets/images/default_avatars/`

4. **Web Server**
   - Place files in your web server directory
   - Ensure PHP GD extension is enabled
   - Configure proper file permissions

## 🎨 Features in Detail

### Profile Photo Management
- **Upload Support**: JPG, PNG, GIF formats
- **Auto-compression**: 80% quality with 400x400 max dimensions
- **File Validation**: MIME type and size verification
- **Storage Management**: Automatic cleanup of old photos

### Notification System
- **Emergence Animation**: Smooth appear-from-logo effect
- **Progress Indicators**: Visual countdown to auto-dismiss
- **Theme Integration**: Seamless light/dark mode support
- **Responsive Design**: Optimized for all screen sizes

### User Interface
- **Modern Design**: Clean, professional appearance
- **Responsive Layout**: Mobile-first design approach
- **Dark Theme**: Complete dark mode support
- **Accessibility**: ARIA labels and keyboard navigation

## 🔒 Security Features

- **Password Hashing**: bcrypt with cost factor 12
- **Session Management**: Secure login/logout handling
- **Input Validation**: Comprehensive sanitization
- **File Upload Security**: Type and size restrictions
- **CSRF Protection**: Token-based form validation
- **Role-based Access**: Granular permission system

## 📱 Responsive Design

- **Mobile Optimized**: Touch-friendly interface
- **Tablet Support**: Adaptive layouts
- **Desktop Experience**: Full-featured interface
- **Breakpoints**: 480px, 768px, 1024px, 1200px

## 🧪 Testing

The application includes comprehensive testing for:
- Database connectivity and queries
- File upload functionality
- User authentication and authorization
- Form validation and submission
- Notification system performance
- Responsive design across devices

## 🚀 Performance

- **Page Load**: < 2 seconds average
- **Image Processing**: Optimized compression algorithms
- **Database Queries**: Prepared statements with indexing
- **File Uploads**: Efficient handling with progress feedback
- **Caching**: Browser-level optimization

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 📞 Support

For support and questions:
- Create an issue in the GitHub repository
- Contact the development team
- Check the [VERSION_HISTORY.md](VERSION_HISTORY.md) for detailed changelog

## 🎉 Acknowledgments

- Bootstrap team for the excellent CSS framework
- PostgreSQL community for robust database support
- PHP community for continuous improvements
- All contributors and testers

---

**Built with ❤️ for modern web development** 