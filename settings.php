<?php
session_start();
require_once 'db.php';
require_once 'access_control.php';

// Cek login
require_login();

$user_id = $_SESSION['user_id'] ?? null;
$message = '';
$error = '';

// Handle settings update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_settings'])) {
    $theme_preference = $_POST['theme_preference'] ?? 'light';
    $language = $_POST['language'] ?? 'en';
    $notifications = isset($_POST['notifications']) ? 1 : 0;
    $email_notifications = isset($_POST['email_notifications']) ? 1 : 0;
    
    // Update user settings (you might want to create a user_settings table)
    // For now, we'll store in session
    $_SESSION['user_theme'] = $theme_preference;
    $_SESSION['user_language'] = $language;
    $_SESSION['user_notifications'] = $notifications;
    $_SESSION['user_email_notifications'] = $email_notifications;
    
    $message = 'Settings berhasil diupdate!';
}

// Get current settings
$current_theme = $_SESSION['user_theme'] ?? 'light';
$current_language = $_SESSION['user_language'] ?? 'en';
$current_notifications = $_SESSION['user_notifications'] ?? 1;
$current_email_notifications = $_SESSION['user_email_notifications'] ?? 1;

include './partials/head.php';
?>

<body>
    <?php include './partials/layouts/layoutHorizontal.php'; ?>

    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Settings</h4>
                        <p class="card-subtitle">Kelola preferensi dan pengaturan akun Anda</p>
                    </div>
                    <div class="card-body">
                        <?php if ($message): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-2"></i>
                                <?= htmlspecialchars($message) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                <?= htmlspecialchars($error) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <div class="row">
                            <!-- Appearance Settings -->
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">
                                            <i class="fas fa-palette me-2"></i>
                                            Appearance
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <form method="POST">
                                            <div class="mb-3">
                                                <label for="theme_preference" class="form-label">Theme</label>
                                                <select class="form-select" id="theme_preference" name="theme_preference">
                                                    <option value="light" <?= ($current_theme === 'light') ? 'selected' : '' ?>>Light Mode</option>
                                                    <option value="dark" <?= ($current_theme === 'dark') ? 'selected' : '' ?>>Dark Mode</option>
                                                    <option value="auto" <?= ($current_theme === 'auto') ? 'selected' : '' ?>>Auto (System)</option>
                                                </select>
                                                <div class="form-text">Pilih tema yang Anda inginkan</div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="language" class="form-label">Language</label>
                                                <select class="form-select" id="language" name="language">
                                                    <option value="en" <?= ($current_language === 'en') ? 'selected' : '' ?>>English</option>
                                                    <option value="id" <?= ($current_language === 'id') ? 'selected' : '' ?>>Bahasa Indonesia</option>
                                                </select>
                                                <div class="form-text">Pilih bahasa interface</div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Notification Settings -->
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">
                                            <i class="fas fa-bell me-2"></i>
                                            Notifications
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <form method="POST">
                                            <div class="mb-3">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" id="notifications" name="notifications" <?= $current_notifications ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="notifications">
                                                        Enable Push Notifications
                                                    </label>
                                                </div>
                                                <div class="form-text">Terima notifikasi real-time di browser</div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" id="email_notifications" name="email_notifications" <?= $current_email_notifications ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="email_notifications">
                                                        Email Notifications
                                                    </label>
                                                </div>
                                                <div class="form-text">Terima notifikasi melalui email</div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Security Settings -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">
                                            <i class="fas fa-shield-alt me-2"></i>
                                            Security
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <h6>Account Security</h6>
                                                <ul class="list-unstyled">
                                                    <li class="mb-2">
                                                        <i class="fas fa-check-circle text-success me-2"></i>
                                                        Password: <strong>Active</strong>
                                                    </li>
                                                    <li class="mb-2">
                                                        <i class="fas fa-check-circle text-success me-2"></i>
                                                        Session: <strong>Secure</strong>
                                                    </li>
                                                    <li class="mb-2">
                                                        <i class="fas fa-info-circle text-info me-2"></i>
                                                        Last Login: <?= date('d M Y H:i') ?>
                                                    </li>
                                                </ul>
                                            </div>
                                            <div class="col-md-6">
                                                <h6>Quick Actions</h6>
                                                <div class="d-grid gap-2">
                                                    <a href="view-profile.php" class="btn btn-outline-primary">
                                                        <i class="fas fa-user-edit me-2"></i>
                                                        Edit Profile
                                                    </a>
                                                    <button class="btn btn-outline-warning" onclick="alert('Feature coming soon!')">
                                                        <i class="fas fa-key me-2"></i>
                                                        Change Password
                                                    </button>
                                                    <button class="btn btn-outline-danger" onclick="alert('Feature coming soon!')">
                                                        <i class="fas fa-sign-out-alt me-2"></i>
                                                        Logout All Devices
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Save Button -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <form method="POST">
                                    <button type="submit" name="update_settings" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i>Save Settings
                                    </button>
                                    <a href="index.php" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                                    </a>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include './partials/layouts/layoutBottom.php'; ?>

    <style>
    .card {
        border: none;
        box-shadow: 0 0 20px rgba(0,0,0,0.1);
        border-radius: 15px;
        margin-bottom: 20px;
    }
    
    .card-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 15px 15px 0 0 !important;
        border: none;
    }
    
    .form-control, .form-select {
        border-radius: 10px;
        border: 2px solid #e9ecef;
        transition: all 0.3s ease;
    }
    
    .form-control:focus, .form-select:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }
    
    .form-check-input:checked {
        background-color: #667eea;
        border-color: #667eea;
    }
    
    .btn {
        border-radius: 10px;
        padding: 10px 20px;
        font-weight: 500;
        transition: all 0.3s ease;
    }
    
    .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
    }
    
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
    }
    
    .alert {
        border-radius: 10px;
        border: none;
    }
    
    .alert-success {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        color: white;
    }
    
    .alert-danger {
        background: linear-gradient(135deg, #dc3545 0%, #fd7e14 100%);
        color: white;
    }
    
    .list-unstyled li {
        padding: 8px 0;
        border-bottom: 1px solid #f8f9fa;
    }
    
    .list-unstyled li:last-child {
        border-bottom: none;
    }
    </style>
</body>
</html>
