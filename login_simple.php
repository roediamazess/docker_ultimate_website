<?php
// Set timezone ke Asia/Jakarta
date_default_timezone_set('Asia/Jakarta');

session_start();
require_once 'db.php';

$error = '';
$success = '';

if (isset($_POST['login'])) {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
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
        
        $success = 'Login berhasil! Redirecting...';
        header('Location: index.php');
        exit;
    } else {
        $error = 'Email atau password salah!';
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
            max-width: 420px;
            width: 90%;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 10;
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

        .login-subtitle {
            font-size: 16px;
            color: #666;
            margin-bottom: 0;
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
            
            console.log(`Waktu lokal: ${hour}:${now.getMinutes()} - ${timeOfDay} (${bgClass})`);
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
                    <img src="assets/images/company/logo.png" alt="PPSolution Logo" style="height: 60px; width: auto;">
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
                    <input type="password" name="password" class="form-input" placeholder="Password" required autocomplete="current-password">
                    <iconify-icon icon="solar:lock-outline" class="input-icon"></iconify-icon>
                </div>

                <button type="submit" name="login" class="login-btn">
                    Masuk Sekarang
                </button>
            </form>

            <div style="margin-top: 32px; text-align: center;">
                <p style="color: #666; font-size: 14px;">
                    <a href="forgot-password.php" style="color: #667eea; text-decoration: none;">Forgot Password? Click here</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html> 