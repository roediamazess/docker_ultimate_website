<?php
require_once 'db.php';

echo "<pre>";
echo "Memulai sinkronisasi skema untuk tabel 'users'...
";

// Helper function to execute a query and report status
function execute_query($pdo, $sql, $success_msg) {
    try {
        $pdo->exec($sql);
        echo "BERHASIL: $success_msg
";
    } catch (PDOException $e) {
        // Error code '42P07' for type exists, '42701' for column exists in PostgreSQL
        if ($e->getCode() == '42P07' || $e->getCode() == '42701') {
            echo "INFO:      " . substr($e->getMessage(), strpos($e->getMessage(), ':') + 2) . " (dianggap sudah sinkron)
";
        } else {
            echo "GAGAL:     $success_msg
";
            echo "           Error: " . $e->getMessage() . "
";
        }
    }
}

// 1. Sync ENUM types (PostgreSQL requires custom types)
echo "\n--- Sinkronisasi Tipe Data ENUM ---
";
execute_query($pdo, "CREATE TYPE user_tier AS ENUM('New Born', 'Tier 1', 'Tier 2', 'Tier 3')", "Membuat tipe 'user_tier'");
execute_query($pdo, "CREATE TYPE user_role AS ENUM('Administrator', 'Management', 'Admin Office', 'User', 'Client')", "Membuat tipe 'user_role'");


// 2. Sync table columns
echo "\n--- Sinkronisasi Kolom Tabel 'users' ---
";

// display_name
execute_query($pdo, "ALTER TABLE users ADD COLUMN display_name VARCHAR(100)", "Menambah kolom 'display_name'");

// full_name
execute_query($pdo, "ALTER TABLE users ADD COLUMN full_name VARCHAR(100)", "Menambah kolom 'full_name'");
execute_query($pdo, "UPDATE users SET full_name = display_name WHERE full_name IS NULL AND display_name IS NOT NULL", "Mengisi 'full_name' dari 'display_name' yang ada");
execute_query($pdo, "UPDATE users SET full_name = 'Nama Lengkap' WHERE full_name IS NULL", "Mengisi 'full_name' dengan nilai default jika masih kosong");
execute_query($pdo, "ALTER TABLE users ALTER COLUMN full_name SET NOT NULL", "Mengubah 'full_name' menjadi NOT NULL");

// tier
execute_query($pdo, "ALTER TABLE users ADD COLUMN tier user_tier", "Menambah kolom 'tier' dengan tipe 'user_tier'");

// role - This one is tricky, it might exist as a different type.
// We will try to alter it directly. This works if the base type is compatible (e.g. varchar)
try {
    $pdo->exec("ALTER TABLE users ALTER COLUMN role TYPE user_role USING role::text::user_role");
    echo "BERHASIL: Mengubah tipe kolom 'role' menjadi 'user_role'\n";
} catch (PDOException $e) {
    echo "INFO:      Tidak bisa mengubah tipe kolom 'role' secara langsung. Kemungkinan sudah sesuai. Pesan: " . $e->getMessage() . "\n";
}

// start_work
execute_query($pdo, "ALTER TABLE users ADD COLUMN start_work DATE", "Menambah kolom 'start_work'");

// created_at
execute_query($pdo, "ALTER TABLE users ADD COLUMN created_at TIMESTAMP", "Menambah kolom 'created_at'");
execute_query($pdo, "ALTER TABLE users ALTER COLUMN created_at SET DEFAULT CURRENT_TIMESTAMP", "Set default value untuk 'created_at'");

// 3. Sync Constraints
echo "\n--- Sinkronisasi Constraints ---
";
execute_query($pdo, "ALTER TABLE users ADD CONSTRAINT users_email_unique UNIQUE (email)", "Menambah constraint UNIQUE pada 'email'");


echo "\nSinkronisasi skema selesai.
";
echo "Silakan periksa output di atas untuk melihat apakah ada error yang perlu ditangani secara manual.
";
echo "</pre>";

?>