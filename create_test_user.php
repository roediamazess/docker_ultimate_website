<?php
require_once 'db.php';

echo "<h1>Create Test User</h1>";

try {
    // Check if test user already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute(['test@test.com']);
    $existing = $stmt->fetch();
    
    if ($existing) {
        echo "Test user already exists!<br>";
        echo "Email: test@test.com<br>";
        echo "Password: test123<br>";
        echo "<a href='login.php'>Go to Login</a><br>";
    } else {
        // Create test user
        $password_hash = password_hash('test123', PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password, role, tier, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->execute(['Test User', 'test@test.com', $password_hash, 'Administrator', 'Tier 1']);
        
        echo "Test user created successfully!<br>";
        echo "Email: test@test.com<br>";
        echo "Password: test123<br>";
        echo "<a href='login.php'>Go to Login</a><br>";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
