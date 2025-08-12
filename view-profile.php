<?php
session_start();
require_once 'db.php';
require_once 'access_control.php';

// Cek login
require_login();

$user_id = $_SESSION['user_id'] ?? null;
$message = '';
$error = '';

// Handle photo upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_photo'])) {
    if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['profile_photo'];
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $max_size = 5 * 1024 * 1024; // 5MB
        
        if (!in_array($file['type'], $allowed_types)) {
            $error = 'Hanya file JPG, PNG, atau GIF yang diperbolehkan.';
        } elseif ($file['size'] > $max_size) {
            $error = 'Ukuran file maksimal 5MB.';
        } else {
            $upload_dir = 'uploads/profile_photos/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'user_' . $user_id . '_' . time() . '.' . $file_extension;
            $filepath = $upload_dir . $filename;
            
            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                // Update database with photo path
                $stmt = $pdo->prepare("UPDATE users SET profile_photo = ? WHERE user_id = ?");
                if ($stmt->execute([$filepath, $user_id])) {
                    $message = 'Foto profil berhasil diupload!';
                } else {
                    $error = 'Gagal menyimpan data foto ke database.';
                }
            } else {
                $error = 'Gagal mengupload file.';
            }
        }
    } else {
        $error = 'Pilih file foto terlebih dahulu.';
    }
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $display_name = trim($_POST['display_name'] ?? '');
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $tier = $_POST['tier'] ?? null;
    $role = $_POST['role'] ?? null;
    $start_work = $_POST['start_work'] ?? null;
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validate required fields
    if (empty($full_name) || empty($email)) {
        $error = 'Nama lengkap dan email wajib diisi.';
    } else {
        // Check if email already exists for other users
        $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ? AND user_id != ?");
        $stmt->execute([$email, $user_id]);
        if ($stmt->fetch()) {
            $error = 'Email sudah digunakan oleh user lain.';
        } else {
            // Update basic info (excluding display_name - only admin can change it)
            $stmt = $pdo->prepare("UPDATE users SET full_name = ?, email = ?, tier = ?, role = ?, start_work = ? WHERE user_id = ?");
            if ($stmt->execute([$full_name, $email, $tier, $role, $start_work, $user_id])) {
                $message = 'Profil berhasil diupdate!';
                
                // Update session data (keep existing display_name)
                $_SESSION['user_email'] = $email;
            } else {
                $error = 'Gagal mengupdate profil.';
            }
        }
    }
    
    // Handle password change
    if (!empty($current_password) && !empty($new_password)) {
        if ($new_password !== $confirm_password) {
            $error = 'Password baru dan konfirmasi password tidak cocok.';
        } else {
            // Verify current password
            $stmt = $pdo->prepare("SELECT password FROM users WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($current_password, $user['password'])) {
                $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE user_id = ?");
                if ($stmt->execute([$new_password_hash, $user_id])) {
                    $message .= ' Password berhasil diubah!';
                } else {
                    $error = 'Gagal mengubah password.';
                }
            } else {
                $error = 'Password saat ini salah.';
            }
        }
    }
}

// Get user data
        $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header('Location: logout.php');
    exit;
}

// Get tier options
$tier_options = ['New Born', 'Tier 1', 'Tier 2', 'Tier 3'];
$role_options = ['Administrator', 'Management', 'Admin Office', 'User', 'Client'];

include './partials/head.php';
?>

<body>
    <?php include './partials/layouts/layoutHorizontal.php'; ?>

    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Profile Settings</h4>
                        <p class="card-subtitle">Kelola informasi profil dan foto Anda</p>
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
                            <!-- Profile Photo Section -->
                            <div class="col-md-4">
                                <div class="text-center mb-4">
                                    <div class="profile-photo-container">
                                        <?php if (!empty($user['profile_photo']) && file_exists($user['profile_photo'])): ?>
                                            <img src="<?= htmlspecialchars($user['profile_photo']) ?>" 
                                                 alt="Profile Photo" 
                                                 class="profile-photo img-fluid rounded-circle mb-3"
                                                 style="width: 150px; height: 150px; object-fit: cover;">
                                        <?php else: ?>
                                            <div class="profile-photo-placeholder mb-3">
                                                <i class="fas fa-user fa-4x text-muted"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <!-- Photo Upload Form -->
                                    <form method="POST" enctype="multipart/form-data" class="mb-3">
                                        <div class="mb-3">
                                            <label for="profile_photo" class="form-label">Upload Foto Profil</label>
                                            <input type="file" 
                                                   class="form-control" 
                                                   id="profile_photo" 
                                                   name="profile_photo" 
                                                   accept="image/*"
                                                   required>
                                            <div class="form-text">
                                                Format: JPG, PNG, GIF. Maksimal 5MB.
                                            </div>
                                        </div>
                                        <button type="submit" name="upload_photo" class="btn btn-primary">
                                            <i class="fas fa-upload me-2"></i>Upload Foto
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <!-- Profile Information Section -->
                            <div class="col-md-8">
                                <form method="POST">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="display_name" class="form-label">Display Name</label>
                                            <input type="text" 
                                                   class="form-control" 
                                                   id="display_name" 
                                                   name="display_name" 
                                                   value="<?= htmlspecialchars($user['display_name'] ?? '') ?>"
                                                   placeholder="Nama yang ditampilkan"
                                                   readonly
                                                   style="background-color: #f8f9fa; cursor: not-allowed;">
                                            <div class="form-text text-muted">
                                                <i class="fas fa-lock me-1"></i>
                                                Display name hanya dapat diubah oleh administrator
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6 mb-3">
                                            <label for="full_name" class="form-label">Full Name *</label>
                                            <input type="text" 
                                                   class="form-control" 
                                                   id="full_name" 
                                                   name="full_name" 
                                                   value="<?= htmlspecialchars($user['full_name'] ?? '') ?>"
                                                   required>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="email" class="form-label">Email *</label>
                                            <input type="email" 
                                                   class="form-control" 
                                                   id="email" 
                                                   name="email" 
                                                   value="<?= htmlspecialchars($user['email'] ?? '') ?>"
                                                   required>
                                        </div>
                                        
                                        <div class="col-md-6 mb-3">
                                            <label for="tier" class="form-label">Tier</label>
                                            <select class="form-select" id="tier" name="tier">
                                                <option value="">Pilih Tier</option>
                                                <?php foreach ($tier_options as $tier_option): ?>
                                                    <option value="<?= $tier_option ?>" 
                                                            <?= ($user['tier'] === $tier_option) ? 'selected' : '' ?>>
                                                        <?= $tier_option ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="role" class="form-label">Role</label>
                                            <select class="form-select" id="role" name="role">
                                                <option value="">Pilih Role</option>
                                                <?php foreach ($role_options as $role_option): ?>
                                                    <option value="<?= $role_option ?>" 
                                                            <?= ($user['role'] === $role_option) ? 'selected' : '' ?>>
                                                        <?= $role_option ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        
                                        <div class="col-md-6 mb-3">
                                            <label for="start_work" class="form-label">Start Work Date</label>
                                            <input type="date" 
                                                   class="form-control" 
                                                   id="start_work" 
                                                   name="start_work" 
                                                   value="<?= htmlspecialchars($user['start_work'] ?? '') ?>">
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label for="current_password" class="form-label">Current Password</label>
                                            <input type="password" 
                                                   class="form-control" 
                                                   id="current_password" 
                                                   name="current_password"
                                                   placeholder="Password saat ini">
                                        </div>
                                        
                                        <div class="col-md-4 mb-3">
                                            <label for="new_password" class="form-label">New Password</label>
                                            <input type="password" 
                                                   class="form-control" 
                                                   id="new_password" 
                                                   name="new_password"
                                                   placeholder="Password baru">
                                        </div>
                                        
                                        <div class="col-md-4 mb-3">
                                            <label for="confirm_password" class="form-label">Confirm Password</label>
                                            <input type="password" 
                                                   class="form-control" 
                                                   id="confirm_password" 
                                                   name="confirm_password"
                                                   placeholder="Konfirmasi password">
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-12">
                                            <button type="submit" name="update_profile" class="btn btn-primary">
                                                <i class="fas fa-save me-2"></i>Update Profile
                                            </button>
                                            <a href="index.php" class="btn btn-secondary">
                                                <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                                            </a>
                                        </div>
                                    </div>
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
     .profile-photo-container {
         position: relative;
         display: inline-block;
     }
     
     .profile-photo {
         border: 3px solid #e9ecef;
         box-shadow: 0 4px 8px rgba(0,0,0,0.1);
     }
     
     .profile-photo-placeholder {
         width: 150px;
         height: 150px;
         border: 3px solid #e9ecef;
         border-radius: 50%;
         display: flex;
         align-items: center;
         justify-content: center;
         background-color: #f8f9fa;
         margin: 0 auto;
     }
     
     .card {
         border: none;
         box-shadow: 0 0 20px rgba(0,0,0,0.1);
         border-radius: 15px;
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
     
     /* Dark Theme Adjustments */
     [data-theme="dark"] .card {
         background-color: #2d3748 !important;
         color: #e2e8f0 !important;
     }
     
     [data-theme="dark"] .card-header {
         background: linear-gradient(135deg, #4a5568 0%, #2d3748 100%) !important;
         color: #e2e8f0 !important;
     }
     
     [data-theme="dark"] .form-control,
     [data-theme="dark"] .form-select {
         background-color: #4a5568 !important;
         border-color: #718096 !important;
         color: #e2e8f0 !important;
     }
     
     [data-theme="dark"] .form-control:focus,
     [data-theme="dark"] .form-select:focus {
         background-color: #4a5568 !important;
         border-color: #667eea !important;
         color: #e2e8f0 !important;
         box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25) !important;
     }
     
     [data-theme="dark"] .form-control[readonly] {
         background-color: #2d3748 !important;
         color: #a0aec0 !important;
         border-color: #4a5568 !important;
     }
     
     [data-theme="dark"] .form-text {
         color: #a0aec0 !important;
     }
     
     [data-theme="dark"] .profile-photo-placeholder {
         background-color: #4a5568 !important;
         border-color: #718096 !important;
         color: #a0aec0 !important;
     }
     
     [data-theme="dark"] .profile-photo {
         border-color: #718096 !important;
     }
     
     [data-theme="dark"] .btn-secondary {
         background-color: #4a5568 !important;
         border-color: #718096 !important;
         color: #e2e8f0 !important;
     }
     
     [data-theme="dark"] .btn-secondary:hover {
         background-color: #2d3748 !important;
         border-color: #4a5568 !important;
     }
     
     [data-theme="dark"] .alert-success {
         background: linear-gradient(135deg, #22543d 0%, #1a4731 100%) !important;
         color: #9ae6b4 !important;
     }
     
     [data-theme="dark"] .alert-danger {
         background: linear-gradient(135deg, #742a2a 0%, #5a1a1a 100%) !important;
         color: #feb2b2 !important;
     }
     
     [data-theme="dark"] .text-muted {
         color: #a0aec0 !important;
     }
     
     [data-theme="dark"] .form-label {
         color: #e2e8f0 !important;
     }
     </style>
</body>
</html>
