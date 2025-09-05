<?php
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
        $_SESSION['logged_in'] = true;
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_display_name'] = $user['user_id'];
        
        $success = 'Login berhasil! Redirecting...';
        echo "<script>setTimeout(function(){ window.location.href = 'index.php'; }, 2000);</script>";
    } else {
        $error = 'Email atau password salah!';
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PowerPro Dashboard - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/iconify-icon@1.0.7/dist/iconify-icon.min.js"></script>
    <style>
        body { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .login-container {
            background: white;
            border-radius: 20px;
            padding: 3rem;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
            margin: 2rem;
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .login-header h1 {
            color: #1e293b;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .login-header p {
            color: #64748b;
            margin-bottom: 0;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-label {
            color: #374151;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .form-control {
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 0.75rem 1rem;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 12px;
            padding: 0.75rem 2rem;
            font-weight: 600;
            font-size: 1rem;
            width: 100%;
            transition: all 0.3s ease;
        }
        
        .btn-login:hover {
            background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
        }
        
        .alert-modern {
            border: none;
            border-radius: 12px;
            padding: 1rem;
            margin-bottom: 1.5rem;
            font-weight: 500;
        }
        
        .alert-error-modern {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            color: #991b1b;
            border-left: 4px solid #ef4444;
        }
        
        .alert-success-modern {
            background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
            color: #065f46;
            border-left: 4px solid #10b981;
        }
        
        .input-group {
            position: relative;
        }
        
        .input-group .form-control {
            padding-left: 3rem;
        }
        
        .input-group-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            z-index: 10;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1 class="h3 mb-2 d-flex align-items-center justify-content-center gap-2">
                <iconify-icon icon="solar:shield-user-outline" style="font-size: 2rem; color: #667eea;"></iconify-icon>
                PowerPro
            </h1>
            <p>Sign in to your account</p>
        </div>
        
        <?php if ($error): ?>
            <div class="alert-modern alert-error-modern">
                <div class="d-flex align-items-center gap-2">
                    <iconify-icon icon="solar:danger-circle-outline"></iconify-icon>
                    <?= htmlspecialchars($error) ?>
                </div>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert-modern alert-success-modern">
                <div class="d-flex align-items-center gap-2">
                    <iconify-icon icon="solar:check-circle-outline"></iconify-icon>
                    <?= htmlspecialchars($success) ?>
                </div>
            </div>
        <?php endif; ?>
        
        <form method="post">
            <div class="form-group">
                <label class="form-label">Email Address</label>
                <div class="input-group">
                    <iconify-icon icon="solar:letter-unread-outline" class="input-group-icon"></iconify-icon>
                    <input type="email" class="form-control" name="email" placeholder="Enter your email" required>
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Password</label>
                <div class="input-group">
                    <iconify-icon icon="solar:lock-password-outline" class="input-group-icon"></iconify-icon>
                    <input type="password" class="form-control" name="password" placeholder="Enter your password" required>
                </div>
            </div>
            
            <div class="form-group">
                <button type="submit" name="login" class="btn btn-login">
                    <iconify-icon icon="solar:login-2-outline"></iconify-icon>
                    Sign In
                </button>
            </div>
        </form>
        
        <div class="text-center mt-4">
            <small class="text-muted">
                <iconify-icon icon="solar:shield-check-outline"></iconify-icon>
                Secure login powered by PowerPro
            </small>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
