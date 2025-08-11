<?php
// Set timezone ke Asia/Jakarta
date_default_timezone_set('Asia/Jakarta');

session_start();
require_once 'db.php';

$error = '';
$success = '';

if (isset($_POST['login'])) {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Basic validation
    if (empty($email) || empty($password)) {
        $error = 'Email dan password harus diisi!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid!';
    } else {
        // Rate limiting check (basic)
        $attempt_key = 'login_attempts_' . md5($email);
        $attempts = $_SESSION[$attempt_key] ?? 0;
        
        if ($attempts >= 5) {
            $error = 'Terlalu banyak percobaan login. Silakan coba lagi dalam 15 menit.';
        } else {
            $sql = "SELECT * FROM users WHERE email = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify($password, $user['password'])) {
                // Login sukses
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['user_display_name'] = $user['display_name'];
                $_SESSION['login_time'] = time();
                
                // Clear login attempts
                unset($_SESSION[$attempt_key]);
                
                $success = 'Login berhasil! Redirecting...';
                header('Location: index.php');
                exit;
            } else {
                // Increment failed attempts
                $_SESSION[$attempt_key] = $attempts + 1;
                $error = 'Email atau password salah!';
            }
        }
    }
}

            // Default values - akan diupdate oleh JavaScript
            $timeOfDay = 'Gaes!';
            $bgClass = 'morning';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Ultimate Website</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <link href="assets/css/login-backgrounds.css" rel="stylesheet">
    <script src="https://unpkg.com/iconify-icon@1.0.8/dist/iconify-icon.min.js"></script>
    <style>
        :root{
            --login-primary-start:#667eea; /* purple-blue */
            --login-primary-end:#764ba2;   /* purple */
            --login-accent:#90C5D8;        /* brand accent */
        }
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

        /* Modern gradient glow overlay */
        .background-animation::after{
            content:"";
            position:absolute; inset:0;
            pointer-events:none;
            background:
              radial-gradient(800px 400px at 10% 10%, rgba(144,197,216,.20), transparent 60%),
              radial-gradient(700px 350px at 90% 20%, rgba(118,75,162,.15), transparent 60%),
              radial-gradient(700px 350px at 20% 80%, rgba(102,126,234,.18), transparent 60%);
            mix-blend-mode: screen;
            animation: bgFloat 14s ease-in-out infinite alternate;
        }

        @keyframes bgFloat{
            0%{transform: translate3d(0,0,0) scale(1);} 
            100%{transform: translate3d(0,-15px,0) scale(1.02);} 
        }

        .login-card {
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
            animation: popIn .6s cubic-bezier(.2,.8,.2,1) both;
        }

        @keyframes popIn{from{opacity:0; transform: translate(-50%, -46%) scale(.96);} to{opacity:1; transform: translate(-50%, -50%) scale(1);} }

        .login-header {
            text-align: center;
            margin-bottom: 28px;
        }

        .login-logo {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--login-primary-start) 0%, var(--login-primary-end) 100%);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 48px;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
        }

        .login-title {
            font-size: 28px;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 20px;
        }

        .login-subtitle {
            font-size: 16px;
            color: #666;
            margin-bottom: 0;
        }

        .time-greeting {
            background: linear-gradient(135deg, var(--login-primary-start) 0%, var(--login-primary-end) 100%);
            color: white;
            padding: 12px 24px;
            border-radius: 50px;
            font-size: 14px;
            font-weight: 600;
            display: inline-block;
            margin-bottom: 28px;
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }

        .form-group {
            margin-bottom: 24px;
            position: relative;
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

        .login-btn {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, var(--login-primary-start) 0%, var(--login-primary-end) 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.25s ease;
            margin-bottom: 24px;
            position: relative; overflow: hidden;
        }

        .login-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 12px 28px rgba(102, 126, 234, 0.45);
        }

        .login-btn:active { transform: translateY(0); }

        /* subtle shine animation */
        .login-btn::after{
            content:""; position:absolute; inset:0; background: linear-gradient(120deg, transparent 0%, rgba(255,255,255,.35) 30%, transparent 60%);
            transform: translateX(-120%);
        }
        .login-btn:hover::after{ transform: translateX(120%); transition: transform .9s ease; }

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
            
            console.log(`Waktu lokal: ${hour}:${now.getMinutes()} - ${timeOfDay} (${bgClass})`);
        }
        
        // Update saat halaman dimuat
        document.addEventListener('DOMContentLoaded', function() {
            updateTimeBasedContent();
            
            // Update setiap menit untuk memastikan akurasi
            setInterval(updateTimeBasedContent, 60000);
        });

        // Toggle password visibility
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const passwordIcon = document.getElementById('password-icon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                passwordIcon.setAttribute('icon', 'solar:eye-closed-outline');
            } else {
                passwordInput.type = 'password';
                passwordIcon.setAttribute('icon', 'solar:eye-outline');
            }
        }
    </script>
</head>
<body>
    <div class="login-container">
        <!-- Dynamic Background -->
        <div class="background-animation <?= $bgClass ?>"></div>
        
        <!-- Login Card -->
        <div class="login-card">
            <div class="login-header">
                <div class="login-logo" style="background: transparent !important; border: none !important; box-shadow: none !important; padding: 0 !important; margin: 0 auto 48px !important;">
                    <img src="assets/images/company/logo.png" alt="PPSolution Logo" style="height: 120px; width: auto; max-width: 200px; background: transparent !important; border: none !important; box-shadow: none !important; padding: 0 !important; margin: 0 !important; cursor: pointer; display: block;" onmouseover="this.style.animation='spin 2s linear infinite'" onmouseout="this.style.animation='none'; this.style.transform='rotate(0deg)'">
                </div>
                <h1 class="login-title">Welcome Back! 👋</h1>
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
                    <input type="email" name="email" class="form-input" placeholder="Email" required autocomplete="username">
                    <iconify-icon icon="solar:letter-outline" class="input-icon"></iconify-icon>
                </div>

                <div class="form-group">
                    <input type="password" name="password" id="password" class="form-input" placeholder="Password" required autocomplete="current-password">
                    <button type="button" class="password-toggle" onclick="togglePassword()" aria-label="Toggle password visibility">
                        <iconify-icon icon="solar:eye-outline" id="password-icon"></iconify-icon>
                    </button>
                </div>

                <button type="submit" name="login" class="login-btn">
                    Login
                </button>
            </form>

            <div style="margin-top: 24px; text-align: center;">
                <p style="color: #666; font-size: 14px; margin-bottom: 0;">
                    <a href="forgot-password.php" style="color: #667eea; text-decoration: none;">Forgot Password? Click here</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html> 
