<?php
// Set timezone untuk UTC (database consistency)
date_default_timezone_set('UTC');

// reset-password.php - Halaman reset password
session_start();
require_once 'db.php';

// Function untuk convert UTC ke local time
function convertUTCToLocal($utc_time, $timezone = 'Asia/Jakarta') {
    $utc = new DateTime($utc_time, new DateTimeZone('UTC'));
    $local = $utc->setTimezone(new DateTimeZone($timezone));
    return $local->format('Y-m-d H:i:s');
}

$error = '';
$success = '';
$token = $_GET['token'] ?? '';

// Cek token validity
if (!empty($token)) {
    // Use current timestamp instead of NOW() for better timezone consistency
    $current_time = date('Y-m-d H:i:s');
    // Schema baru: gunakan user_id sebagai id; tidak bergantung pada display_name
    $sql = "SELECT user_id AS id, email, reset_token, reset_expires FROM users WHERE reset_token = ? AND reset_expires > ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$token, $current_time]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        $error = 'Token reset password tidak valid atau sudah expired!';
    }
} else {
    $error = 'Token tidak ditemukan!';
}

// Handle password reset
if (isset($_POST['reset_password']) && $user) {
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($password)) {
        $error = 'Password tidak boleh kosong!';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter!';
    } elseif ($password !== $confirm_password) {
        $error = 'Konfirmasi password tidak cocok!';
    } else {
        // Hash password dan clear token
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                        $clear_sql = "UPDATE users SET password = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?";
        $clear_stmt = $pdo->prepare($clear_sql);
        
        if ($clear_stmt->execute([$hashed_password, $user['id']])) {
            $success = 'Password berhasil diubah! Silakan login dengan password baru.';
            // Redirect ke login setelah 3 detik
            header("refresh:3;url=login.php");
        } else {
            $error = 'Gagal mengubah password. Silakan coba lagi.';
        }
    }
}

// Get token expiry info for display
$token_expiry_info = '';
if ($user) {
    $token_expiry_info = [
        'utc' => $user['reset_expires'],
        'jakarta' => convertUTCToLocal($user['reset_expires'], 'Asia/Jakarta'),
        'bali' => convertUTCToLocal($user['reset_expires'], 'Asia/Makassar'),
        'papua' => convertUTCToLocal($user['reset_expires'], 'Asia/Jayapura')
    ];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Ultimate Website</title>
    <link rel="icon" type="image/png" href="assets/images/company/logo.png" sizes="32x32">
    <link rel="apple-touch-icon" href="assets/images/company/logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <link href="assets/css/login-backgrounds.css" rel="stylesheet">
    <script src="https://unpkg.com/iconify-icon@1.0.8/dist/iconify-icon.min.js"></script>
    <style>
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
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

        .reset-container {
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

        .reset-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            padding: 48px 32px 32px 32px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.2);
            max-width: 400px;
            width: 90%;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 10;
        }

        .reset-header {
            text-align: center;
            margin-bottom: 28px;
        }

        .reset-logo {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 48px;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
        }

        .reset-title {
            font-size: 28px;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 20px;
        }

        .reset-subtitle {
            font-size: 16px;
            color: #666;
            margin-bottom: 28px;
        }

        .form-group {
            margin-bottom: 24px;
            position: relative;
        }

        .form-group:last-of-type {
            margin-bottom: 24px;
        }

        .form-label {
            font-size: 14px;
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 8px;
            display: block;
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

        .form-input[type="password"] {
            padding-right: 50px;
        }

        /* Hide default password reveal/clear icons (Edge/IE) to avoid double eyes */
        input[type="password"]::-ms-reveal,
        input[type="password"]::-ms-clear { display: none; width: 0; height: 0; }
        input[type="password"]::-webkit-clear-button { display: none; }
        input[type="password"]::-webkit-credentials-auto-fill-button { visibility: hidden; display: none; }

        .form-input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
            background: white;
        }

        .input-icon {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
            font-size: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 20px;
            width: 20px;
            pointer-events: none;
        }

        .password-toggle {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
            font-size: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 20px;
            width: 20px;
            cursor: pointer;
            background: none;
            border: none;
            padding: 0;
            transition: color 0.3s ease;
        }

        .password-toggle:hover {
            color: #667eea;
        }

        .password-toggle:focus {
            outline: none;
            color: #667eea;
        }

        .reset-btn {
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
            margin-bottom: 24px;
        }

        .reset-btn:hover {
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
            margin-top: 24px;
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
            margin-bottom: 16px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(255, 107, 107, 0.3);
        }

        .success-message {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 12px 16px;
            border-radius: 12px;
            font-size: 14px;
            margin-bottom: 16px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
        }
    </style>
</head>
<body>
    <div class="reset-container">
        <!-- Dynamic Background -->
        <div class="background-animation morning"></div>
        
        <!-- Reset Card -->
        <div class="reset-card">
            <div class="reset-header">
                <div class="reset-logo" style="background: transparent !important; border: none !important; box-shadow: none !important;">
                    <img src="assets/images/company/logo.png" alt="PPSolution Logo" style="height: 120px; width: auto; background: transparent !important; border: none !important; box-shadow: none !important; padding: 0 !important; margin: 0 !important; cursor: pointer;" onmouseover="this.style.animation='spin 2s linear infinite'" onmouseout="this.style.animation='none'; this.style.transform='rotate(0deg)'">
                </div>
                <h1 class="reset-title">Reset Password 🔐</h1>
                <p class="reset-subtitle">Set new password for your account</p>
            </div>

        <?php if ($error): ?>
            <div class="error-message">
                <iconify-icon icon="solar:danger-triangle-outline" style="margin-right: 8px;"></iconify-icon>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="success-message">
                <iconify-icon icon="solar:check-circle-outline" style="margin-right: 8px;"></iconify-icon>
                <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <?php if ($user && !$error && !$success): ?>
            <div style="background: rgba(102, 126, 234, 0.1); border: 1px solid #667eea; border-radius: 8px; padding: 12px; margin-bottom: 20px; text-align: center;">
                <strong>Reset Password for:</strong><br>
                <iconify-icon icon="solar:letter-outline" style="margin-right: 4px;"></iconify-icon> <?php echo htmlspecialchars($user['email']); ?>
            </div>

            <form method="POST">
                <div class="form-group">
                    <input type="password" name="password" id="password" class="form-input" placeholder="New Password" required>
                    <button type="button" class="password-toggle" onclick="togglePassword('password')" aria-label="Toggle password visibility">
                        <iconify-icon icon="solar:eye-outline" id="password-icon"></iconify-icon>
                    </button>
                </div>
                
                <div class="form-group">
                    <input type="password" name="confirm_password" id="confirm_password" class="form-input" placeholder="Confirmation Password" required>
                    <button type="button" class="password-toggle" onclick="togglePassword('confirm_password')" aria-label="Toggle confirm password visibility">
                        <iconify-icon icon="solar:eye-outline" id="confirm-password-icon"></iconify-icon>
                    </button>
                </div>
                
                <button type="submit" name="reset_password" class="reset-btn">
                    Reset Password
                </button>
            </form>
        <?php endif; ?>

            <button onclick="window.location.href='login.php'" class="back-btn">
                <iconify-icon icon="solar:arrow-left-outline" style="margin-right: 8px;"></iconify-icon>
                Back to Login
            </button>
        </div>
    </div>

    <script>
        // Toggle password visibility
        function togglePassword(inputId) {
            const passwordInput = document.getElementById(inputId);
            const passwordIcon = document.getElementById(inputId === 'password' ? 'password-icon' : 'confirm-password-icon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                passwordIcon.setAttribute('icon', 'solar:eye-closed-outline');
            } else {
                passwordInput.type = 'password';
                passwordIcon.setAttribute('icon', 'solar:eye-outline');
            }
        }
    </script>
</body>
</html> 
