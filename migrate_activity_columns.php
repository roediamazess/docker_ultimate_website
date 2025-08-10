<?php
// Jalankan sekali untuk menambahkan kolom activity tambahan di PostgreSQL
// Gunakan: php migrate_activity_columns.php

declare(strict_types=1);

require_once __DIR__ . '/db.php';

header('Content-Type: text/plain');

function column_exists(PDO $pdo, string $table, string $column): bool {
    $stmt = $pdo->prepare("SELECT 1 FROM information_schema.columns WHERE table_name = :t AND column_name = :c");
    $stmt->execute(['t' => $table, 'c' => $column]);
    return (bool) $stmt->fetchColumn();
}

function type_exists(PDO $pdo, string $typeName): bool {
    $stmt = $pdo->prepare("SELECT 1 FROM pg_type WHERE typname = :n");
    $stmt->execute(['n' => $typeName]);
    return (bool) $stmt->fetchColumn();
}

try {
    $pdo->beginTransaction();

    // Buat enum activity_priority jika belum ada
    if (!type_exists($pdo, 'activity_priority')) {
        $pdo->exec("CREATE TYPE activity_priority AS ENUM ('Urgent','Normal','Low')");
        echo "Created type activity_priority\n";
    } else {
        echo "Type activity_priority already exists\n";
    }

    // Tambah kolom priority
    if (!column_exists($pdo, 'activities', 'priority')) {
        $pdo->exec("ALTER TABLE activities ADD COLUMN priority activity_priority NOT NULL DEFAULT 'Normal'");
        echo "Added column activities.priority\n";
    } else {
        echo "Column activities.priority already exists\n";
    }

    // Tambah kolom customer
    if (!column_exists($pdo, 'activities', 'customer')) {
        $pdo->exec("ALTER TABLE activities ADD COLUMN customer VARCHAR(255)");
        echo "Added column activities.customer\n";
    } else {
        echo "Column activities.customer already exists\n";
    }

    // Tambah kolom project (free text)
    if (!column_exists($pdo, 'activities', 'project')) {
        $pdo->exec("ALTER TABLE activities ADD COLUMN project VARCHAR(255)");
        echo "Added column activities.project\n";
    } else {
        echo "Column activities.project already exists\n";
    }

    // Tambah kolom created_by (referensi users.id)
    if (!column_exists($pdo, 'activities', 'created_by')) {
        $pdo->exec("ALTER TABLE activities ADD COLUMN created_by INTEGER");
        // Tambahkan FK bila memungkinkan, abaikan jika users belum ada
        try {
            $pdo->exec("ALTER TABLE activities ADD CONSTRAINT fk_activities_created_by_users FOREIGN KEY (created_by) REFERENCES users(id)");
            echo "Added FK fk_activities_created_by_users\n";
        } catch (Throwable $e) {
            echo "FK skipped: " . $e->getMessage() . "\n";
        }
        echo "Added column activities.created_by\n";
    } else {
        echo "Column activities.created_by already exists\n";
    }

    $pdo->commit();
    echo "Migration completed successfully.\n";
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo "Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}



