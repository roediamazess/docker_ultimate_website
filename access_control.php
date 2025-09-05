<?php
/**
 * Access Control - Standardisasi kontrol akses
 * File ini berisi fungsi-fungsi standar untuk kontrol akses
 */

// Fungsi untuk mengecek apakah user sudah login
function require_login() {
    if (!isset($_SESSION['user_id']) || !$_SESSION['user_id']) {
        header('Location: /login.php');
        exit;
    }
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
