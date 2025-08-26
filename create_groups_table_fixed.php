<?php
require_once 'db.php';

try {
    $sql = "
    CREATE TABLE IF NOT EXISTS hotel_groups (
        id SERIAL PRIMARY KEY,
        name VARCHAR(255) NOT NULL UNIQUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );";
    $pdo->exec($sql);
    echo "Table 'hotel_groups' created successfully (if it didn't exist).";
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>