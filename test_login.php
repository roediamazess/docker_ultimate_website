<?php
require_once 'db.php';

echo "<h2>Test Login System</h2>";

// Test database connection
try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM users");
    $user_count = $stmt->fetchColumn();
    echo "<p>✅ Database connection: OK</p>";
    echo "<p>✅ Users in database: $user_count</p>";
} catch (Exception $e) {
    echo "<p>❌ Database error: " . $e->getMessage() . "</p>";
    exit;
}

// Test user credentials
$test_email = 'admin@test.com';
$test_password = 'admin123';

$sql = "SELECT * FROM users WHERE email = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$test_email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {
    echo "<p>✅ User found: " . $user['email'] . "</p>";
    echo "<p>✅ User role: " . $user['role'] . "</p>";
    
    if (password_verify($test_password, $user['password'])) {
        echo "<p>✅ Password verification: OK</p>";
    } else {
        echo "<p>❌ Password verification: FAILED</p>";
    }
} else {
    echo "<p>❌ User not found: $test_email</p>";
}

// Test session
session_start();
echo "<p>✅ Session started: OK</p>";

// Test form submission simulation
echo "<h3>Test Form Submission</h3>";
echo "<form method='post' action='login.php'>";
echo "<input type='email' name='email' value='admin@test.com' readonly><br>";
echo "<input type='password' name='password' value='admin123' readonly><br>";
echo "<input type='submit' name='login' value='Test Login'>";
echo "</form>";

echo "<h3>Login Instructions</h3>";
echo "<p>1. Buka <a href='login.php' target='_blank'>login.php</a></p>";
echo "<p>2. Gunakan email: admin@test.com</p>";
echo "<p>3. Gunakan password: admin123</p>";
echo "<p>4. Klik tombol 'Masuk Sekarang'</p>";
?> 