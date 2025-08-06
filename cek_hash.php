<?php
$hash = '$2y$10$JVWxlYz.2DLyF5Y41MeORO0denTz2aJ8slV3LiuUlQ4YuE9RUQ5QK';
$password = 'admin123'; // ganti dengan password yang ingin dicek

if (password_verify($password, $hash)) {
    echo "Cocok!";
} else {
    echo "Tidak cocok!";
}
?>