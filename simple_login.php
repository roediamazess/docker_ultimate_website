<?php
session_start();
require_once 'db.php';

$error = '';
$success = '';

if (isset($_POST['login'])) {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    echo "<p>Debug: Email = $email</p>";
    
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
        echo "<script>setTimeout(function(){ window.location.href = 'index.php'; }, 2000);</script>";
    } else {
        $error = 'Email atau password salah!';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simple Login Test</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .form-group { margin: 10px 0; }
        input { padding: 10px; width: 300px; }
        button { padding: 10px 20px; background: #007bff; color: white; border: none; cursor: pointer; }
        .error { color: red; }
        .success { color: green; }
    </style>
</head>
<body>
    <h1>Simple Login Test</h1>
    
    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    
    <form method="post">
        <div class="form-group">
            <label>Email:</label><br>
            <input type="email" name="email" value="admin@test.com" required>
        </div>
        
        <div class="form-group">
            <label>Password:</label><br>
            <input type="password" name="password" value="admin123" required>
        </div>
        
        <div class="form-group">
            <button type="submit" name="login">Login</button>
        </div>
    </form>
    
    <p><a href="login.php">Kembali ke Login Page Utama</a></p>
</body>
</html> 