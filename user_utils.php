<?php
/**
 * User Utilities - Standardisasi fungsi user ID
 * File ini berisi fungsi-fungsi standar untuk menangani user ID
 */

// Fungsi untuk mendapatkan user ID dari session
function get_current_user_id() {
    return $_SESSION['user_id'] ?? null;
}

// Fungsi untuk mendapatkan user email dari session
function get_current_user_email() {
    return $_SESSION['user_email'] ?? null;
}

// Fungsi untuk mendapatkan user display name dari session
function get_current_user_display_name() {
    return $_SESSION['user_display_name'] ?? null;
}

// Fungsi untuk mengecek apakah user sudah login
function is_user_logged_in() {
    return isset($_SESSION['user_id']);
}

// Fungsi untuk mengecek role user
function has_user_role($role) {
    return $_SESSION['user_role'] === $role;
}

// Fungsi untuk mengecek apakah user memiliki salah satu dari role yang diberikan
function has_user_roles($roles) {
    if (!is_array($roles)) {
        $roles = [$roles];
    }
    return in_array($_SESSION['user_role'] ?? '', $roles);
}

// Fungsi standardisasi untuk logging activity
function log_user_activity($action, $description = '') {
    try {
        global $pdo;
        if (!$pdo) {
            require_once 'db.php';
        }
        
        $user_id = get_current_user_id();
        $user_email = get_current_user_email();
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        if ($pdo) {
            $stmt = $pdo->prepare('INSERT INTO logs (user_id, user_email, action, description, ip, user_agent, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())');
            $stmt->execute([$user_id, $user_email, $action, $description, $ip, $ua]);
        }
    } catch (Exception $e) {
        // Ignore database errors during logging
        error_log("Logging error: " . $e->getMessage());
    }
}

// Fungsi untuk redirect jika user tidak memiliki role tertentu
function require_role($role) {
    require_login();
    if (!has_user_role($role)) {
        header('Location: index.php');
        exit;
    }
}

// Fungsi untuk redirect jika user tidak memiliki salah satu role
function require_roles($roles) {
    require_login();
    if (!has_user_roles($roles)) {
        header('Location: index.php');
        exit;
    }
}
?> 
