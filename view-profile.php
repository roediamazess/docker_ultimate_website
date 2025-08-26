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
        $max_size = 2 * 1024 * 1024; // 2MB (reduced from 5MB)
        
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
                                    </div>

                                    <div class="row">
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
                                    </div>

                                    <div class="row">
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
                                            <div class="form-text text-muted">
                                                <i class="fas fa-edit me-1"></i>
                                                Tanggal mulai kerja dapat diubah
                                            </div>
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
                                            <div class="form-text text-muted">
                                                <i class="fas fa-edit me-1"></i>
                                                Kosongkan jika tidak ingin mengubah password
                                            </div>
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
     
     <!-- Logo Notification System -->
     <script src="assets/js/logo-notifications.js"></script>

         <style>
     .profile-photo-container {
         position: relative;
         display: inline-block;
     }
     
     .profile-photo {
         width: 150px;
         height: 150px;
         border: 3px solid #e9ecef;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
            object-fit: cover;
        }
        
        .profile-photo:hover {
            transform: scale(1.05);
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
     
        [data-theme="dark"] input[type="file"] {
         background-color: #4a5568 !important;
            border-color: #718096 !important;
         color: #e2e8f0 !important;
     }
     
        [data-theme="dark"] input[type="file"]:hover {
         background-color: #2d3748 !important;
            border-color: #667eea !important;
        }
        
        
        
        /* File Input Styling */
        input[type="file"] {
            padding: 8px 12px;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            background-color: #ffffff;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        input[type="file"]:hover {
            border-color: #667eea;
            background-color: #f8f9fa;
        }
        
        input[type="file"]:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
            outline: none;
        }
        
        /* Custom file input styling */
        input[type="file"]::-webkit-file-upload-button {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 8px;
            margin-right: 10px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        input[type="file"]::-webkit-file-upload-button:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
        }
        
        /* Firefox file input styling */
        input[type="file"]::file-selector-button {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 8px;
            margin-right: 10px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        input[type="file"]::file-selector-button:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
        }
        
        /* Default Avatars Grid Styling */
        .default-avatars-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 12px;
            margin-bottom: 20px;
        }
        
        .default-avatar-item {
            text-align: center;
            cursor: pointer;
            padding: 10px;
            border-radius: 10px;
            transition: all 0.3s ease;
            border: 2px solid transparent;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        
        .default-avatar-item:hover {
            background-color: #f8f9fa;
            border-color: #dee2e6;
            transform: translateY(-2px);
        }
        
        .default-avatar-item.selected {
            background-color: #e3f2fd;
            border-color: #2196f3;
        }
        
        .default-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
            font-weight: bold;
            margin: 0 auto;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        }
        
        .default-avatar-img {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #ffffff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
            transition: transform 0.3s ease;
            margin-bottom: 8px;
        }
        
        .default-avatar-img:hover {
            transform: scale(1.1);
        }
        
        .default-avatar-item small {
            font-size: 11px;
            font-weight: 500;
            color: #6c757d;
            line-height: 1.2;
            text-align: center;
            margin-top: 4px;
        }
        
        @media (max-width: 768px) {
            .default-avatars-grid {
                grid-template-columns: repeat(4, 1fr);
                gap: 8px;
            }
            
            .default-avatar {
                width: 45px;
                height: 45px;
                font-size: 18px;
                margin-bottom: 6px;
            }
            
            .default-avatar-img {
                width: 45px;
                height: 45px;
                margin-bottom: 6px;
            }
            
            .default-avatar-item small {
                font-size: 10px;
                margin-top: 2px;
            }
        }
        
        @media (max-width: 480px) {
            .default-avatars-grid {
                grid-template-columns: repeat(3, 1fr);
                gap: 6px;
            }
            
            .default-avatar {
                width: 40px;
                height: 40px;
                font-size: 16px;
                margin-bottom: 5px;
            }
            
            .default-avatar-img {
                width: 40px;
                height: 40px;
                margin-bottom: 5px;
            }
            
            .default-avatar-item small {
                font-size: 9px;
                margin-top: 2px;
            }
        }
        
        @media (max-width: 768px) {
            .profile-photo {
                width: 120px;
                height: 120px;
            }
     }
     </style>

    <script>
        // Function to select default avatar
        function selectDefaultAvatar(path, name) {
            // Remove previous selection
            document.querySelectorAll('.default-avatar-item').forEach(item => {
                item.classList.remove('selected');
            });
            
            // Add selection to clicked item
            event.currentTarget.classList.add('selected');
            
            // Set hidden input value
            document.getElementById('selectedDefaultAvatar').value = path;
            
            // Show select button
            document.getElementById('selectDefaultBtn').style.display = 'block';
        }
        
        // Function to show message
        function showMessage(message, type = 'info') {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type === 'info' ? 'info' : 'success'} alert-dismissible fade show`;
            alertDiv.innerHTML = `
                <i class="fas fa-info-circle me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            // Insert at the top of card-body
            const cardBody = document.querySelector('.card-body');
            cardBody.insertBefore(alertDiv, cardBody.firstChild);
            
            // Auto-hide after 3 seconds
            setTimeout(() => {
                if (alertDiv.parentNode) {
                    alertDiv.remove();
                }
            }, 3000);
        }
        
                 // Show notifications from PHP when page loads
         document.addEventListener('DOMContentLoaded', function() {
             // Check if logoNotificationManager is available
             if (typeof logoNotificationManager !== 'undefined' && logoNotificationManager.isAvailable()) {
                 // Show notifications based on PHP variables
                 <?php if ($message): ?>
                     logoNotificationManager.showSuccess('<?= addslashes($message) ?>', 5000);
                 <?php endif; ?>
                 
                 <?php if ($error): ?>
                     logoNotificationManager.showError('<?= addslashes($error) ?>', 5000);
                 <?php endif; ?>
             }
         });
    </script>
</body>
</html>

