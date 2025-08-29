<?php
// Skrip sementara untuk menangkap dan menampilkan error dalam format yang mudah disalin.

echo "<p>Silakan salin semua teks yang muncul di bawah ini dan kirimkan ke saya.</p>";
echo "<hr>";
echo "<pre>";

// Atur penangan error kustom
set_error_handler(function($severity, $message, $file, $line) {
    // Format error sebagai teks biasa agar mudah disalin
    echo "==================== ERROR TERTANGKAP ====================\n";
    echo "Level:    " . $severity . "\n";
    echo "Pesan:    " . $message . "\n";
    echo "File:     " . $file . "\n";
    echo "Baris:    " . $line . "\n";
    echo "=========================================================\n\n";
    // Jangan jalankan penangan error internal PHP
    return true;
});

// Matikan pelaporan error standar agar tidak tumpang tindih
error_reporting(0);
ini_set('display_errors', 0);

// Sekarang, sertakan file yang menyebabkan error.
// Kita tahu errornya ada di dashboard.
include 'index.php';

echo "</pre>";

echo "<hr><p>Proses selesai.</p>";

?>