<?php
session_start();
require_once 'db.php';

echo "<h1>Quick Access to Ultimate Website</h1>";

// Check if user is already logged in
if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
    echo "<p>You are already logged in!</p>";
    echo "<a href='index.php' class='btn btn-primary'>Go to Dashboard</a><br><br>";
} else {
    // Try to auto-login with test user
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->execute(['test@test.com']);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            // Auto-login
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_display_name'] = $user['display_name'] ?? $user['full_name'];
            $_SESSION['login_time'] = time();
            
            echo "<p>Auto-login successful!</p>";
            echo "<p>Logged in as: " . $user['full_name'] . " (" . $user['email'] . ")</p>";
            echo "<a href='index.php' class='btn btn-success'>Go to Dashboard</a><br><br>";
        } else {
            echo "<p>No test user found. Please create one first.</p>";
            echo "<a href='create_test_user.php' class='btn btn-warning'>Create Test User</a><br><br>";
        }
    } catch (Exception $e) {
        echo "<p>Error: " . $e->getMessage() . "</p>";
    }
}

// Show available users
echo "<h2>Available Users for Login:</h2>";
try {
    $stmt = $pdo->query("SELECT id, full_name, email, role FROM users ORDER BY id");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th></tr>";
    foreach ($users as $user) {
        echo "<tr>";
        echo "<td>" . $user['id'] . "</td>";
        echo "<td>" . $user['full_name'] . "</td>";
        echo "<td>" . $user['email'] . "</td>";
        echo "<td>" . $user['role'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
} catch (Exception $e) {
    echo "<p>Error loading users: " . $e->getMessage() . "</p>";
}

echo "<br><h2>Quick Links:</h2>";
echo "<a href='login.php' class='btn btn-primary'>Manual Login</a> ";
echo "<a href='index.php' class='btn btn-success'>Dashboard</a> ";
echo "<a href='test_session.php' class='btn btn-info'>Test Session</a> ";
echo "<a href='test_dashboard.php' class='btn btn-warning'>Test Dashboard</a>";
?>
