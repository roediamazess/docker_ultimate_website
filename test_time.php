<?php
// Set timezone ke Asia/Jakarta
date_default_timezone_set('Asia/Jakarta');

// Test file untuk mengecek logika waktu
echo "<h2>Test Logika Waktu (Asia/Jakarta)</h2>";

$current_hour = date('H');
$current_time = date('H:i');
$timezone = date_default_timezone_get();

echo "<p><strong>Informasi Waktu:</strong></p>";
echo "<ul>";
echo "<li>Waktu sekarang: $current_time</li>";
echo "<li>Jam: $current_hour</li>";
echo "<li>Timezone: $timezone</li>";
echo "</ul>";

echo "<p><strong>Logika Waktu Saat Ini:</strong></p>";

if ($current_hour >= 3 && $current_hour < 10) {
    $timeOfDay = 'Pagi Gaes!';
    $bgClass = 'morning';
    echo "<p style='color: orange;'>✅ <strong>$timeOfDay</strong> (03:00-09:59)</p>";
} elseif ($current_hour >= 10 && $current_hour < 15) {
    $timeOfDay = 'Siang Gaes!';
    $bgClass = 'afternoon';
    echo "<p style='color: blue;'>✅ <strong>$timeOfDay</strong> (10:00-14:59)</p>";
} elseif ($current_hour >= 15 && $current_hour < 18) {
    $timeOfDay = 'Sore Gaes!';
    $bgClass = 'evening';
    echo "<p style='color: red;'>✅ <strong>$timeOfDay</strong> (15:00-17:59)</p>";
} else {
    $timeOfDay = 'Malam Gaes!';
    $bgClass = 'night';
    echo "<p style='color: purple;'>✅ <strong>$timeOfDay</strong> (18:00-02:59)</p>";
}

echo "<p><strong>Background Class:</strong> $bgClass</p>";

echo "<h3>Test Semua Range Waktu:</h3>";
for ($hour = 0; $hour < 24; $hour++) {
    if ($hour >= 3 && $hour < 10) {
        $timeOfDay = 'Pagi Gaes!';
        $bgClass = 'morning';
        $color = 'orange';
    } elseif ($hour >= 10 && $hour < 15) {
        $timeOfDay = 'Siang Gaes!';
        $bgClass = 'afternoon';
        $color = 'blue';
    } elseif ($hour >= 15 && $hour < 18) {
        $timeOfDay = 'Sore Gaes!';
        $bgClass = 'evening';
        $color = 'red';
    } else {
        $timeOfDay = 'Malam Gaes!';
        $bgClass = 'night';
        $color = 'purple';
    }
    
    $current = ($hour == $current_hour) ? " <strong>← SEKARANG</strong>" : "";
    echo "<p style='color: $color;'>$hour:00 - $timeOfDay ($bgClass)$current</p>";
}

echo "<p><a href='login.php'>Kembali ke Login</a></p>";
?> 
