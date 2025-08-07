<?php
session_start();
require_once 'user_utils.php';

// Log logout activity
if (is_user_logged_in()) {
    log_user_activity('logout', 'User logged out successfully');
}

// Destroy session
session_destroy();

// Clear all session variables
$_SESSION = array();

// Delete session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Redirect to login page
header('Location: login_simple.php');
exit;
?>
