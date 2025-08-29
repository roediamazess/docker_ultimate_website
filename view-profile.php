<?php
session_start();
require_once 'db.php';
require_once 'access_control.php';

// Cek login
require_login();

$user_id = $_SESSION['user_id'] ?? null;
$message = '';
$error = '';

// Function to compress and resize image
function compressImage($source, $destination, $quality = 80, $max_width = 400, $max_height = 400) {
    // Check if GD extension is available
    if (!extension_loaded('gd')) {
        // If GD not available, just copy the file
        return copy($source, $destination);
    }
    
    $info = getimagesize($source);
    
    if ($info === false) {
        return false;
    }
    
    $width = $info[0];
    $height = $info[1];
    $type = $info[2];
    
    // Calculate new dimensions maintaining aspect ratio
    $ratio = min($max_width / $width, $max_height / $height);
    $new_width = round($width * $ratio);
    $new_height = round($height * $ratio);
    
    // Create new image
    $new_image = imagecreatetruecolor($new_width, $new_height);
    
    // Handle transparency for PNG
    if ($type === IMAGETYPE_PNG) {
        imagealphablending($new_image, false);
        imagesavealpha($new_image, true);
        $transparent = imagecolorallocatealpha($new_image, 255, 255, 255, 127);
        imagefilledrectangle($new_image, 0, 0, $new_width, $new_height, $transparent);
    }
    
    // Load source image
    switch ($type) {
        case IMAGETYPE_JPEG:
            $source_image = imagecreatefromjpeg($source);
            break;
        case IMAGETYPE_PNG:
            $source_image = imagecreatefrompng($source);
            break;
        case IMAGETYPE_GIF:
            $source_image = imagecreatefromgif($source);
            break;
        default:
            return false;
    }
    
    // Resize image
    imagecopyresampled($new_image, $source_image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
    
    // Save compressed image
    $success = false;
    switch ($type) {
        case IMAGETYPE_JPEG:
            $success = imagejpeg($new_image, $destination, $quality);
            break;
        case IMAGETYPE_PNG:
            $success = imagepng($new_image, $destination, round($quality / 10));
            break;
        case IMAGETYPE_GIF:
            $success = imagegif($new_image, $destination);
            break;
    }
    
    // Clean up
    imagedestroy($source_image);
    imagedestroy($new_image);
    
    return $success;
}

// Handle photo upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_photo'])) {
    if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['profile_photo'];
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $max_size = 2 * 1024 * 1024; // 2MB
        
        // Enhanced validation
        if (!in_array($file['type'], $allowed_types)) {
            $error = 'Only JPG, PNG, or GIF files are allowed.';
        } elseif ($file['size'] > $max_size) {
            $error = 'Maximum file size is 2MB.';
        } elseif ($file['size'] === 0) {
            $error = 'File is empty or corrupted.';
        } else {
            // Additional security check
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime_type = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            
            if (!in_array($mime_type, $allowed_types)) {
                $error = 'Invalid file type.';
            } else {
                $upload_dir = 'uploads/profile_photos/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                
                $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $filename = 'user_' . $user_id . '_' . time() . '.' . $file_extension;
                $filepath = $upload_dir . $filename;
                
                // Delete old profile photo if exists
                if (!empty($user['profile_photo']) && file_exists($user['profile_photo'])) {
                    unlink($user['profile_photo']);
                }
                
                if (move_uploaded_file($file['tmp_name'], $filepath)) {
                    // Compress and resize image
                    $compressed_filepath = $upload_dir . 'compressed_' . $filename;
                    if (compressImage($filepath, $compressed_filepath, 80, 400, 400)) {
                        // Remove original file and use compressed version
                        unlink($filepath);
                        $filepath = $compressed_filepath;
                        $message = 'Profile photo uploaded and compressed successfully!';
                    } else {
                        $message = 'Profile photo uploaded successfully! (compression failed)';
                    }
                    
                    // Update database with photo path
                    $stmt = $pdo->prepare("UPDATE users SET profile_photo = ? WHERE id = ?");
                    if ($stmt->execute([$filepath, $user_id])) {
                        // Refresh user data
                        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
                        $stmt->execute([$user_id]);
                        $user = $stmt->fetch(PDO::FETCH_ASSOC);
                    } else {
                        $error = 'Failed to save photo data to database.';
                    }
                } else {
                    $error = 'Failed to upload file.';
                }
            }
        }
    } else {
        $error = 'Please select a photo file first or upload error occurred.';
    }
}

// Handle default avatar selection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['select_default_avatar'])) {
    $selected_avatar = $_POST['selected_default_avatar'] ?? '';
    
    if (!empty($selected_avatar) && file_exists($selected_avatar)) {
        // Copy default avatar to user's profile photos folder
        $upload_dir = 'uploads/profile_photos/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $file_extension = strtolower(pathinfo($selected_avatar, PATHINFO_EXTENSION));
        $filename = 'user_' . $user_id . '_default_' . time() . '.' . $file_extension;
        $filepath = $upload_dir . $filename;
        
        // Delete old profile photo if exists
        if (!empty($user['profile_photo']) && file_exists($user['profile_photo'])) {
            unlink($user['profile_photo']);
        }
        
        if (copy($selected_avatar, $filepath)) {
            // Update database with new photo path
            $stmt = $pdo->prepare("UPDATE users SET profile_photo = ? WHERE id = ?");
            if ($stmt->execute([$filepath, $user_id])) {
                $message = 'Default avatar selected successfully!';
                // Refresh user data
                $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
                $stmt->execute([$user_id]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
            } else {
                $error = 'Failed to save default avatar to database.';
            }
        } else {
            $error = 'Failed to copy default avatar.';
        }
    } else {
        $error = 'Default avatar is invalid or not found.';
    }
}

// Handle photo removal
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_photo'])) {
    if (!empty($user['profile_photo']) && file_exists($user['profile_photo'])) {
        // Delete file from server
        if (unlink($user['profile_photo'])) {
            // Update database
            $stmt = $pdo->prepare("UPDATE users SET profile_photo = NULL WHERE id = ?");
            if ($stmt->execute([$user_id])) {
                $message = 'Profile photo removed successfully!';
                // Refresh user data
                $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
                $stmt->execute([$user_id]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
            } else {
                $error = 'Failed to remove photo data from database.';
            }
        } else {
            $error = 'Failed to remove photo file from server.';
        }
    } else {
        $error = 'No profile photo to remove.';
    }
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $start_work = $_POST['start_work'] ?? null;
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Only process start_work and password changes
    $has_changes = false;
    
    // Handle start_work update
    if ($start_work !== ($user['start_work'] ?? '')) {
        // Normalize empty date to NULL to avoid date format errors
        $start_workParam = ($start_work === '' ? null : $start_work);
        
        $stmt = $pdo->prepare("UPDATE users SET start_work = ? WHERE id = ?");
        if ($stmt->execute([$start_workParam, $user_id])) {
            $message = 'Start work date updated successfully!';
            $has_changes = true;
        } else {
            $error = 'Failed to update start work date.';
        }
    }
    
    // Handle password change
    if (!empty($current_password) && !empty($new_password)) {
        if ($new_password !== $confirm_password) {
            $error = 'New password and confirm password do not match.';
        } else {
            // Verify current password
            $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($current_password, $user['password'])) {
                $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                if ($stmt->execute([$new_password_hash, $user_id])) {
                    if ($has_changes) {
                        $message .= ' Password changed successfully!';
                    } else {
                        $message = 'Password changed successfully!';
                    }
                    $has_changes = true;
                } else {
                    $error = 'Failed to change password.';
                }
            } else {
                $error = 'Current password is incorrect.';
            }
        }
    }
    
    // Show message if no changes were made
    if (!$has_changes && empty($error) && empty($current_password) && empty($new_password) && empty($confirm_password)) {
        $message = 'No changes were made.';
    }
}

// Get user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
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

    <!-- Logo Notification System will be handled by JavaScript -->

    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Profile Settings</h4>
                        <p class="card-subtitle">Kelola foto profil, tanggal mulai kerja, dan password Anda</p>
                    </div>
                    <div class="card-body">

                        <div class="row">
                            <!-- Profile Photo Section -->
                            <div class="col-md-4">
                                <div class="text-center mb-4">
                                    <div class="profile-photo-container">
                                        <?php if (!empty($user['profile_photo']) && file_exists($user['profile_photo'])): ?>
                                            <img src="<?= htmlspecialchars($user['profile_photo']) ?>" 
                                                 alt="Profile Photo" 
                                                 class="profile-photo img-fluid rounded-circle mb-3">
                                        <?php else: ?>
                                            <div class="text-center mb-3">
                                                <i class="fas fa-user fa-4x text-muted"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <!-- Photo Upload Form -->
                                    <form method="POST" enctype="multipart/form-data" class="mb-3">
                                        <div class="mb-3">
                                            <label class="form-label">Pilih Avatar Default</label>
                                            <div class="default-avatars-grid mb-3">
                                                <?php
                                                $default_avatars = [
                                                    'assets/images/default_avatars/default_avatar_1.png' => ['name' => 'Avatar 1', 'letter' => '1', 'color' => '#3498db'],
                                                    'assets/images/default_avatars/default_avatar_2.png' => ['name' => 'Avatar 2', 'letter' => '2', 'color' => '#9b59b6'],
                                                    'assets/images/default_avatars/default_avatar_3.png' => ['name' => 'Avatar 3', 'letter' => '3', 'color' => '#2ecc71'],
                                                    'assets/images/default_avatars/default_avatar_4.png' => ['name' => 'Avatar 4', 'letter' => '4', 'color' => '#e67e22'],
                                                    'assets/images/default_avatars/default_avatar_5.png' => ['name' => 'Avatar 5', 'letter' => '5', 'color' => '#e74c3c'],
                                                    'assets/images/default_avatars/default_avatar_6.png' => ['name' => 'Avatar 6', 'letter' => '6', 'color' => '#34495e'],
                                                    'assets/images/default_avatars/default_avatar_7.png' => ['name' => 'Avatar 7', 'letter' => '7', 'color' => '#95a5a6'],
                                                    'assets/images/default_avatars/default_avatar_8.png' => ['name' => 'Avatar 8', 'letter' => '8', 'color' => '#f39c12'],
                                                    'assets/images/default_avatars/default_avatar_9.png' => ['name' => 'Avatar 9', 'letter' => '9', 'color' => '#1abc9c'],
                                                    'assets/images/default_avatars/default_avatar_10.png' => ['name' => 'Avatar 10', 'letter' => '10', 'color' => '#8e44ad']
                                                ];
                                                
                                                foreach ($default_avatars as $path => $avatar): ?>
                                                    <div class="default-avatar-item" onclick="selectDefaultAvatar('<?= $path ?>', '<?= $avatar['name'] ?>')">
                                                        <?php if (file_exists($path)): ?>
                                                            <img src="<?= htmlspecialchars($path) ?>" 
                                                                 alt="<?= $avatar['name'] ?>" 
                                                                 class="default-avatar-img">
                                                        <?php else: ?>
                                                            <div class="default-avatar" style="background: <?= $avatar['color'] ?>">
                                                                <?= $avatar['letter'] ?>
                                                            </div>
                                                        <?php endif; ?>
                                                        <small class="d-block mt-1"><?= $avatar['name'] ?></small>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="profile_photo" class="form-label">Atau Upload Foto Profil</label>
                                            <input type="file" 
                                                   class="form-control" 
                                                   id="profile_photo" 
                                                   name="profile_photo" 
                                                   accept="image/*">
                                            <div class="form-text">
                                                Format: JPG, PNG, GIF. Maksimal 2MB.
                                            </div>
                                        </div>
                                        
                                        <div class="d-grid gap-2">
                                            <button type="submit" name="upload_photo" class="btn btn-primary">
                                                <i class="fas fa-upload me-2"></i>Upload Foto
                                            </button>
                                            
                                            <button type="submit" name="select_default_avatar" class="btn btn-outline-primary" id="selectDefaultBtn" style="display: none;">
                                                <i class="fas fa-user me-2"></i>Pilih Avatar Default
                                            </button>
                                            
                                            <?php if (!empty($user['profile_photo']) && file_exists($user['profile_photo'])): ?>
                                            <button type="submit" name="remove_photo" class="btn btn-outline-danger" 
                                                    onclick="return confirm('Yakin ingin menghapus foto profil?')">
                                                <i class="fas fa-trash me-2"></i>Hapus Foto
                                            </button>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <!-- Hidden input untuk avatar default -->
                                        <input type="hidden" name="selected_default_avatar" id="selectedDefaultAvatar" value="">
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
                                                   readonly style="background-color: #f3f4f6; cursor: not-allowed;">
                                            <div class="form-text text-muted">
                                                <i class="fas fa-lock me-1"></i>
                                                Hanya dapat diubah oleh Administrator
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6 mb-3">
                                            <label for="full_name" class="form-label">Full Name</label>
                                            <input type="text" 
                                                   class="form-control" 
                                                   id="full_name" 
                                                   name="full_name" 
                                                   value="<?= htmlspecialchars($user['full_name'] ?? '') ?>"
                                                   readonly style="background-color: #f3f4f6; cursor: not-allowed;">
                                            <div class="form-text text-muted">
                                                <i class="fas fa-lock me-1"></i>
                                                Hanya dapat diubah oleh Administrator
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6 mb-3">
                                            <label for="email" class="form-label">Email</label>
                                            <input type="email" 
                                                   class="form-control" 
                                                   id="email" 
                                                   name="email" 
                                                   value="<?= htmlspecialchars($user['email'] ?? '') ?>"
                                                   readonly style="background-color: #f3f4f6; cursor: not-allowed;">
                                            <div class="form-text text-muted">
                                                <i class="fas fa-lock me-1"></i>
                                                Hanya dapat diubah oleh Administrator
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6 mb-3">
                                            <label for="tier" class="form-label">Tier</label>
                                            <input type="text" 
                                                   class="form-control" 
                                                   id="tier" 
                                                   name="tier" 
                                                   value="<?= htmlspecialchars($user['tier'] ?? '') ?>"
                                                   readonly style="background-color: #f3f4f6; cursor: not-allowed;">
                                            <div class="form-text text-muted">
                                                <i class="fas fa-lock me-1"></i>
                                                Hanya dapat diubah oleh Administrator
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6 mb-3">
                                            <label for="role" class="form-label">Role</label>
                                            <input type="text" 
                                                   class="form-control" 
                                                   id="role" 
                                                   name="role" 
                                                   value="<?= htmlspecialchars($user['role'] ?? '') ?>"
                                                   readonly style="background-color: #f3f4f6; cursor: not-allowed;">
                                            <div class="form-text text-muted">
                                                <i class="fas fa-lock me-1"></i>
                                                Hanya dapat diubah oleh Administrator
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6 mb-3">
                                            <label for="start_work" class="form-label">Start Work Date</label>
                                            <input type="date" 
                                                   class="form-control" 
                                                   id="start_work" 
                                                   name="start_work" 
                                                   value="<?= htmlspecialchars($user['start_work'] ?? '') ?>">
                                            <div class="form-text">
                                                Tanggal mulai bekerja
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <hr class="my-4">
                                    
                                    <!-- Password Change Section -->
                                    <h5 class="mb-3">Change Password</h5>
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label for="current_password" class="form-label">Current Password</label>
                                            <input type="password" 
                                                   class="form-control" 
                                                   id="current_password" 
                                                   name="current_password">
                                        </div>
                                        
                                        <div class="col-md-4 mb-3">
                                            <label for="new_password" class="form-label">New Password</label>
                                            <input type="password" 
                                                   class="form-control" 
                                                   id="new_password" 
                                                   name="new_password">
                                        </div>
                                        
                                        <div class="col-md-4 mb-3">
                                            <label for="confirm_password" class="form-label">Confirm Password</label>
                                            <input type="password" 
                                                   class="form-control" 
                                                   id="confirm_password" 
                                                   name="confirm_password">
                                        </div>
                                    </div>
                                    
                                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                        <button type="submit" name="update_profile" class="btn btn-primary">
                                            <i class="fas fa-save me-2"></i>Update Profile
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function selectDefaultAvatar(path, name) {
            document.getElementById('selectedDefaultAvatar').value = path;
            document.getElementById('selectDefaultBtn').style.display = 'block';
            
            // Highlight selected avatar
            document.querySelectorAll('.default-avatar-item').forEach(item => {
                item.classList.remove('selected');
            });
            event.target.closest('.default-avatar-item').classList.add('selected');
        }
    </script>

    <style>
        .default-avatars-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 10px;
            max-width: 300px;
            margin: 0 auto;
        }
        
        .default-avatar-item {
            cursor: pointer;
            padding: 5px;
            border: 2px solid transparent;
            border-radius: 8px;
            transition: all 0.3s ease;
            text-align: center;
        }
        
        .default-avatar-item:hover {
            border-color: #007bff;
            background-color: #f8f9fa;
        }
        
        .default-avatar-item.selected {
            border-color: #007bff;
            background-color: #e3f2fd;
        }
        
        .default-avatar-img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }
        
        .default-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            margin: 0 auto;
        }
        
        .profile-photo {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border: 3px solid #dee2e6;
        }
    </style>

    <?php include './partials/layouts/layoutBottom.php'; ?>
</body>
</html>

