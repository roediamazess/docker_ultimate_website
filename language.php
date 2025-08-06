<?php
// Definisi string multi-bahasa
$lang = [
    'id' => [
        'login' => 'Masuk',
        'logout' => 'Keluar',
        'dashboard' => 'Dasbor',
        'user' => 'Pengguna',
        'customer' => 'Pelanggan',
        'project' => 'Proyek',
        'activity' => 'Aktivitas',
        'email' => 'Email',
        'password' => 'Kata Sandi',
        'create' => 'Tambah',
        'update' => 'Ubah',
        'delete' => 'Hapus',
        'search' => 'Cari',
        'reset' => 'Reset',
        'export_excel' => 'Ekspor Excel',
        'not_found' => 'Data tidak ditemukan',
        'access_denied' => 'Akses ditolak',
        'save' => 'Simpan',
        'cancel' => 'Batal',
        'select_language' => 'Pilih Bahasa',
        'indonesian' => 'Indonesia',
        'english' => 'Inggris',
    ],
    'en' => [
        'login' => 'Login',
        'logout' => 'Logout',
        'dashboard' => 'Dashboard',
        'user' => 'User',
        'customer' => 'Customer',
        'project' => 'Project',
        'activity' => 'Activity',
        'email' => 'Email',
        'password' => 'Password',
        'create' => 'Create',
        'update' => 'Update',
        'delete' => 'Delete',
        'search' => 'Search',
        'reset' => 'Reset',
        'export_excel' => 'Export Excel',
        'not_found' => 'Data not found',
        'access_denied' => 'Access denied',
        'save' => 'Save',
        'cancel' => 'Cancel',
        'select_language' => 'Select Language',
        'indonesian' => 'Indonesian',
        'english' => 'English',
    ]
];

if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['lang'])) $_SESSION['lang'] = 'id';
function __($key) {
    global $lang;
    $l = $_SESSION['lang'] ?? 'id';
    return $lang[$l][$key] ?? $key;
}
