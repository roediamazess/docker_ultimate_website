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
    $sql = "SELECT id, email, display_name, reset_token, reset_expires FROM users WHERE reset_token = ? AND reset_expires > ?";
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
            header("refresh:3;url=login_simple.php");
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .reset-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 500px;
            text-align: center;
        }

        .logo {
            font-size: 2.5rem;
            color: #667eea;
            margin-bottom: 20px;
        }

        .title {
            font-size: 1.8rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 10px;
        }

        .subtitle {
            color: #666;
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }

        .form-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
            display: block;
        }

        .form-control {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e1e5e9;
            border-radius: 10px;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .btn-reset {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-reset:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }

        .alert {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .alert-danger {
            background: #ffe6e6;
            color: #d63031;
            border: 1px solid #fab1a0;
        }

        .alert-success {
            background: #e6ffe6;
            color: #00b894;
            border: 1px solid #a0fab1;
        }

        .user-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: left;
        }

        .timezone-info {
            background: #e3f2fd;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
            text-align: left;
        }

        .back-link {
            margin-top: 20px;
        }

        .back-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
        }

        .back-link a:hover {
            text-decoration: underline;
        }

        .device-timezone {
            background: #fff3cd;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 15px;
            font-size: 14px;
            border: 1px solid #ffeaa7;
        }
    </style>
</head>
<body>
    <div class="reset-container">
        <div class="logo" style="text-align: center; margin-bottom: 30px; background: transparent !important; border: none !important; box-shadow: none !important;">
            <img src="assets/images/company/logo.png" alt="PPSolution Logo" style="height: 120px; width: auto; background: transparent !important; border: none !important; box-shadow: none !important; padding: 0 !important; margin: 0 !important; cursor: pointer;" onmouseover="this.style.animation='spin 2s linear infinite'" onmouseout="this.style.animation='none'; this.style.transform='rotate(0deg)'">
        </div>
        
        <h1 class="title">Reset Password</h1>
        <p class="subtitle">Set new password for your account</p>

        <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="ri-error-warning-line"></i>
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="ri-check-line"></i>
                <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <?php if ($user && !$error && !$success): ?>
            <div class="user-info">
                <strong>Reset Password for:</strong><br>
                <i class="ri-user-line"></i> <?php echo htmlspecialchars($user['display_name']); ?><br>
                <i class="ri-mail-line"></i> <?php echo htmlspecialchars($user['email']); ?>
            </div>

            <div class="device-timezone" id="deviceTimezone">
                <i class="ri-time-line"></i> Detecting your timezone...
            </div>

            <div class="timezone-info">
                <strong>Token valid until:</strong><br>
                <span id="localTime">Loading...</span><br>
                <small>Based on your device timezone</small>
            </div>

            <form method="POST">
                <div class="form-group">
                    <label class="form-label">Password Baru</label>
                    <input type="password" name="password" class="form-control" placeholder="Masukkan password baru" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Konfirmasi Password</label>
                    <input type="password" name="confirm_password" class="form-control" placeholder="Konfirmasi password baru" required>
                </div>
                
                <button type="submit" name="reset_password" class="btn-reset">
                    <i class="ri-lock-unlock-line"></i> Reset Password
                </button>
            </form>
        <?php endif; ?>

        <div class="back-link">
            <a href="login_simple.php">
                <i class="ri-arrow-left-line"></i> Kembali ke Login
            </a>
        </div>
    </div>

    <script>
        // Detect device timezone and show local time
        function detectAndShowTimezone() {
            try {
                // Get device timezone
                const deviceTimezone = Intl.DateTimeFormat().resolvedOptions().timeZone;
                const deviceOffset = new Date().getTimezoneOffset();
                
                // Convert UTC time to device local time
                const utcTime = '<?php echo $user ? $user['reset_expires'] : ''; ?>';
                if (utcTime) {
                    const localDate = new Date(utcTime + 'Z'); // Add Z to treat as UTC
                    const localTimeString = localDate.toLocaleString('id-ID', {
                        timeZone: deviceTimezone,
                        year: 'numeric',
                        month: '2-digit',
                        day: '2-digit',
                        hour: '2-digit',
                        minute: '2-digit',
                        second: '2-digit'
                    });
                    
                    // Update display
                    document.getElementById('deviceTimezone').innerHTML = 
                        `<i class="ri-time-line"></i> <strong>Device Timezone:</strong> ${deviceTimezone}`;
                    
                    document.getElementById('localTime').textContent = localTimeString;
                    
                    // Check if token is expired in device timezone
                    const now = new Date();
                    const tokenExpiry = new Date(utcTime + 'Z');
                    
                    if (tokenExpiry <= now) {
                        document.getElementById('localTime').innerHTML = 
                            `<span style="color: #d63031; font-weight: bold;">${localTimeString} (EXPIRED)</span>`;
                    } else {
                        document.getElementById('localTime').innerHTML = 
                            `<span style="color: #00b894; font-weight: bold;">${localTimeString} (VALID)</span>`;
                    }
                }
            } catch (error) {
                console.error('Error detecting timezone:', error);
                document.getElementById('deviceTimezone').innerHTML = 
                    '<i class="ri-error-warning-line"></i> Cannot detect device timezone';
                document.getElementById('localTime').textContent = 'Error detecting timezone';
            }
        }

        // Run on page load
        document.addEventListener('DOMContentLoaded', detectAndShowTimezone);
    </script>
</body>
</html> 