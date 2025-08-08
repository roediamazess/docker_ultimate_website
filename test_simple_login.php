<?php
session_start();
require_once 'db.php';

echo "<h2>Simple Login Test</h2>";

if (isset($_POST['test_login'])) {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    echo "<p><strong>Testing Login:</strong></p>";
    echo "<p>Email: $email</p>";
    echo "<p>Password: $password</p>";
    
    // Check user
    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        echo "<p>✅ User found: " . $user['email'] . "</p>";
        echo "<p>User role: " . $user['role'] . "</p>";
        
        if (password_verify($password, $user['password'])) {
            echo "<p>✅ Password correct!</p>";
            
            // Set session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_display_name'] = $user['display_name'];
            
            echo "<p>✅ Session created!</p>";
            echo "<p>Session data: " . print_r($_SESSION, true) . "</p>";
            
            echo "<p><a href='index.php'>Go to Dashboard</a></p>";
        } else {
            echo "<p>❌ Password incorrect!</p>";
        }
    } else {
        echo "<p>❌ User not found!</p>";
    }
}

// Show all users
echo "<h3>Available Users:</h3>";
$users = $pdo->query("SELECT id, email, display_name, role FROM users")->fetchAll();
echo "<table border='1'>";
echo "<tr><th>ID</th><th>Email</th><th>Name</th><th>Role</th></tr>";
foreach ($users as $u) {
    echo "<tr>";
    echo "<td>" . $u['id'] . "</td>";
    echo "<td>" . $u['email'] . "</td>";
    echo "<td>" . $u['display_name'] . "</td>";
    echo "<td>" . $u['role'] . "</td>";
    echo "</tr>";
}
echo "</table>";
?>

<form method="post" style="margin: 20px 0; padding: 20px; border: 1px solid #ccc;">
    <h3>Test Login</h3>
    <p>Email: <input type="email" name="email" value="admin@example.com" required></p>
    <p>Password: <input type="password" name="password" value="admin123" required></p>
    <p><input type="submit" name="test_login" value="Test Login"></p>
</form>

<p><a href="login.php">Back to Main Login</a></p> 
