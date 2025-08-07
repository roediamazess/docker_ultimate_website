<?php
// add-user-form.php - Form untuk tambah user baru
session_start();
require_once 'db.php';

$error = '';
$success = '';

if (isset($_POST['add_user'])) {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $display_name = $_POST['display_name'] ?? '';
    $full_name = $_POST['full_name'] ?? '';
    $role = $_POST['role'] ?? '';
    $tier = $_POST['tier'] ?? '';
    
    // Validasi
    if (empty($email) || empty($password) || empty($display_name) || empty($full_name) || empty($role) || empty($tier)) {
        $error = 'Semua field harus diisi!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid!';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter!';
    } elseif ($password !== $confirm_password) {
        $error = 'Konfirmasi password tidak cocok!';
    } else {
        // Cek apakah email sudah ada
        $check_sql = "SELECT id FROM users WHERE email = ?";
        $check_stmt = $pdo->prepare($check_sql);
        $check_stmt->execute([$email]);
        
        if ($check_stmt->fetch()) {
            $error = 'Email sudah terdaftar!';
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert user baru
            $insert_sql = "INSERT INTO users (email, password, display_name, full_name, role, tier, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())";
            $insert_stmt = $pdo->prepare($insert_sql);
            
            if ($insert_stmt->execute([$email, $hashed_password, $display_name, $full_name, $role, $tier])) {
                $success = 'User berhasil ditambahkan!';
                // Reset form
                $email = $display_name = $full_name = $role = $tier = '';
            } else {
                $error = 'Gagal menambahkan user!';
            }
        }
    }
}

// Default values untuk background
$timeOfDay = 'Gaes!';
$bgClass = 'morning';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add User - Ultimate Website</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <link href="assets/css/login-backgrounds.css" rel="stylesheet">
    <script src="https://unpkg.com/iconify-icon@1.0.8/dist/iconify-icon.min.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            overflow: hidden;
            height: 100vh;
        }

        .login-container {
            position: relative;
            width: 100vw;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .background-animation {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            transition: all 1s ease-in-out;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            padding: 48px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.2);
            max-width: 500px;
            width: 90%;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 10;
            max-height: 90vh;
            overflow-y: auto;
        }

        .login-header {
            text-align: center;
            margin-bottom: 32px;
        }

        .login-logo {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
        }

        .login-title {
            font-size: 28px;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 8px;
        }

        .time-greeting {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 24px;
            border-radius: 50px;
            font-size: 14px;
            font-weight: 600;
            display: inline-block;
            margin-bottom: 24px;
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }

        .form-group {
            margin-bottom: 20px;
            position: relative;
        }

        .form-input {
            width: 100%;
            padding: 16px 20px;
            border: 2px solid #e1e5e9;
            border-radius: 12px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.8);
        }

        .form-input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
            background: white;
        }

        .form-select {
            width: 100%;
            padding: 16px 20px;
            border: 2px solid #e1e5e9;
            border-radius: 12px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.8);
        }

        .form-select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
            background: white;
        }

        .login-btn {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }

        .back-btn {
            width: 100%;
            padding: 12px;
            background: transparent;
            color: #667eea;
            border: 2px solid #667eea;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 16px;
        }

        .back-btn:hover {
            background: #667eea;
            color: white;
            transform: translateY(-2px);
        }

        .error-message {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%);
            color: white;
            padding: 12px 16px;
            border-radius: 12px;
            font-size: 14px;
            margin-bottom: 24px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(255, 107, 107, 0.3);
        }

        .success-message {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 12px 16px;
            border-radius: 12px;
            font-size: 14px;
            margin-bottom: 24px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
        }
    </style>
    
    <script>
        // Function untuk mengupdate waktu berdasarkan waktu lokal PC
        function updateTimeBasedContent() {
            const now = new Date();
            const hour = now.getHours();
            const backgroundElement = document.querySelector('.background-animation');
            const timeOfDayElement = document.getElementById('timeOfDay');
            
            let timeOfDay = '';
            let bgClass = '';
            
            if (hour >= 3 && hour < 10) {
                timeOfDay = 'Pagi Gaes!';
                bgClass = 'morning';
            } else if (hour >= 10 && hour < 15) {
                timeOfDay = 'Siang Gaes!';
                bgClass = 'afternoon';
            } else if (hour >= 15 && hour < 18) {
                timeOfDay = 'Sore Gaes!';
                bgClass = 'evening';
            } else {
                timeOfDay = 'Malam Gaes!';
                bgClass = 'night';
            }
            
            // Update greeting text
            if (timeOfDayElement) {
                timeOfDayElement.textContent = timeOfDay;
            }
            
            // Update background
            if (backgroundElement) {
                // Remove all time-based classes
                backgroundElement.classList.remove('morning', 'afternoon', 'evening', 'night');
                // Add new class
                backgroundElement.classList.add(bgClass);
            }
        }
        
        // Update saat halaman dimuat
        document.addEventListener('DOMContentLoaded', function() {
            updateTimeBasedContent();
            
            // Update setiap menit untuk memastikan akurasi
            setInterval(updateTimeBasedContent, 60000);
        });
    </script>
</head>
<body>
    <div class="login-container">
        <!-- Dynamic Background -->
        <div class="background-animation <?= $bgClass ?>"></div>
        
        <!-- Login Card -->
        <div class="login-card">
            <div class="login-header">
                <div class="login-logo">
                    <iconify-icon icon="solar:user-plus-outline" style="font-size: 40px; color: white;"></iconify-icon>
                </div>
                <h1 class="login-title">Add New User 👤</h1>
                <div class="time-greeting" id="timeGreeting">
                    Selamat <span id="timeOfDay">Gaes!</span>
                </div>
            </div>

            <?php if ($error): ?>
            <div class="error-message">
                <iconify-icon icon="solar:danger-triangle-outline" style="margin-right: 8px;"></iconify-icon>
                <?= htmlspecialchars($error) ?>
            </div>
            <?php endif; ?>

            <?php if ($success): ?>
            <div class="success-message">
                <iconify-icon icon="solar:check-circle-outline" style="margin-right: 8px;"></iconify-icon>
                <?= htmlspecialchars($success) ?>
            </div>
            <?php endif; ?>

            <form method="post">
                <div class="form-group">
                    <input type="email" name="email" class="form-input" placeholder="Email" required value="<?= htmlspecialchars($email ?? '') ?>">
                </div>

                <div class="form-group">
                    <input type="text" name="display_name" class="form-input" placeholder="Display Name" required value="<?= htmlspecialchars($display_name ?? '') ?>">
                </div>

                <div class="form-group">
                    <input type="text" name="full_name" class="form-input" placeholder="Full Name" required value="<?= htmlspecialchars($full_name ?? '') ?>">
                </div>

                <div class="form-group">
                    <select name="role" class="form-select" required>
                        <option value="">Select Role</option>
                        <option value="Administrator" <?= ($role ?? '') === 'Administrator' ? 'selected' : '' ?>>Administrator</option>
                        <option value="Management" <?= ($role ?? '') === 'Management' ? 'selected' : '' ?>>Management</option>
                        <option value="Admin Office" <?= ($role ?? '') === 'Admin Office' ? 'selected' : '' ?>>Admin Office</option>
                        <option value="User" <?= ($role ?? '') === 'User' ? 'selected' : '' ?>>User</option>
                        <option value="Client" <?= ($role ?? '') === 'Client' ? 'selected' : '' ?>>Client</option>
                    </select>
                </div>

                <div class="form-group">
                    <select name="tier" class="form-select" required>
                        <option value="">Select Tier</option>
                        <option value="New Born" <?= ($tier ?? '') === 'New Born' ? 'selected' : '' ?>>New Born</option>
                        <option value="Tier 1" <?= ($tier ?? '') === 'Tier 1' ? 'selected' : '' ?>>Tier 1</option>
                        <option value="Tier 2" <?= ($tier ?? '') === 'Tier 2' ? 'selected' : '' ?>>Tier 2</option>
                        <option value="Tier 3" <?= ($tier ?? '') === 'Tier 3' ? 'selected' : '' ?>>Tier 3</option>
                    </select>
                </div>

                <div class="form-group">
                    <input type="password" name="password" class="form-input" placeholder="Password" required>
                </div>

                <div class="form-group">
                    <input type="password" name="confirm_password" class="form-input" placeholder="Confirm Password" required>
                </div>

                <button type="submit" name="add_user" class="login-btn">
                    Add User
                </button>
            </form>

            <button onclick="window.location.href='login_simple.php'" class="back-btn">
                <iconify-icon icon="solar:arrow-left-outline" style="margin-right: 8px;"></iconify-icon>
                Back to Login
            </button>
        </div>
    </div>
</body>
</html> 