<?php
session_start();
require_once 'db.php';
require_once 'access_control.php';
require_login();

header('Content-Type: application/json; charset=utf-8');

try {
    $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    if ($driver === 'mysql') {
        $pdo->exec("CREATE TABLE IF NOT EXISTS jobsheet (
            user_id VARCHAR(64) NULL,
            pic_name VARCHAR(64) NOT NULL,
            day VARCHAR(8) NOT NULL,
            value VARCHAR(20) NOT NULL,
            ontime TINYINT(1) NOT NULL DEFAULT 0,
            late TINYINT(1) NOT NULL DEFAULT 0,
            note TEXT NULL,
            PRIMARY KEY (pic_name, day)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        try { $pdo->exec("ALTER TABLE jobsheet MODIFY user_id VARCHAR(64) NULL"); } catch (Throwable $e) {}
        try { $pdo->exec("ALTER TABLE jobsheet ADD COLUMN IF NOT EXISTS pic_name VARCHAR(64) NOT NULL DEFAULT ''"); } catch (Throwable $e) {}
        try { $pdo->exec("ALTER TABLE jobsheet DROP PRIMARY KEY"); } catch (Throwable $e) {}
        try { $pdo->exec("ALTER TABLE jobsheet ADD PRIMARY KEY (pic_name, day)"); } catch (Throwable $e) {}
    } else {
        $pdo->exec("CREATE TABLE IF NOT EXISTS jobsheet (
            user_id VARCHAR(64) NULL,
            pic_name VARCHAR(64) NOT NULL DEFAULT '',
            day VARCHAR(8) NOT NULL,
            value VARCHAR(20) NOT NULL,
            ontime BOOLEAN NOT NULL DEFAULT FALSE,
            late BOOLEAN NOT NULL DEFAULT FALSE,
            note TEXT NULL
        )");
        try { $pdo->exec("ALTER TABLE jobsheet ADD COLUMN IF NOT EXISTS pic_name VARCHAR(64) NOT NULL DEFAULT ''"); } catch (Throwable $e) {}
        try { $pdo->exec("ALTER TABLE jobsheet ALTER COLUMN user_id DROP NOT NULL"); } catch (Throwable $e) {}
        try { $pdo->exec("ALTER TABLE jobsheet DROP CONSTRAINT IF EXISTS jobsheet_pkey"); } catch (Throwable $e) {}
        try { $pdo->exec("ALTER TABLE jobsheet ADD PRIMARY KEY (pic_name, day)"); } catch (Throwable $e) {}
    }

    $input = json_decode(file_get_contents('php://input'), true);
    if (!is_array($input)) $input = [];

    $pdo->beginTransaction();
    $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    if ($driver === 'mysql') {
        $stmt = $pdo->prepare('INSERT INTO jobsheet (user_id, pic_name, day, value, ontime, late, note) VALUES (:user_id, :pic_name, :day, :value, :ontime, :late, :note)
            ON DUPLICATE KEY UPDATE value = VALUES(value), ontime = VALUES(ontime), late = VALUES(late), note = VALUES(note), user_id = IFNULL(VALUES(user_id), user_id)');
    } else {
        $stmt = $pdo->prepare('INSERT INTO jobsheet (user_id, pic_name, day, value, ontime, late, note) VALUES (:user_id, :pic_name, :day, :value, :ontime, :late, :note)
            ON CONFLICT (pic_name, day) DO UPDATE SET value = EXCLUDED.value, ontime = EXCLUDED.ontime, late = EXCLUDED.late, note = EXCLUDED.note, user_id = COALESCE(EXCLUDED.user_id, jobsheet.user_id)');
    }
    foreach ($input as $row) {
        $userId = isset($row['user_id']) ? trim((string)$row['user_id']) : '';
        $picName= isset($row['pic_name']) ? trim((string)$row['pic_name']) : (isset($row['pic']) ? trim((string)$row['pic']) : '');
        $day    = isset($row['day']) ? trim((string)$row['day']) : '';
        $value  = isset($row['value']) ? trim((string)$row['value']) : '';
        $ontime = !empty($row['ontime']);
        $late   = !empty($row['late']);
        $note   = isset($row['note']) ? trim((string)$row['note']) : '';
        if ($picName === '' || $day === '' || $value === '') continue;

        // Bind values with proper types (esp. booleans on Postgres)
        if ($userId === '') {
            $stmt->bindValue(':user_id', null, PDO::PARAM_NULL);
        } else {
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_STR);
        }
        $stmt->bindValue(':pic_name', $picName, PDO::PARAM_STR);
        $stmt->bindValue(':day', $day, PDO::PARAM_STR);
        $stmt->bindValue(':value', $value, PDO::PARAM_STR);
        if ($driver === 'mysql') {
            $stmt->bindValue(':ontime', $ontime ? 1 : 0, PDO::PARAM_INT);
            $stmt->bindValue(':late', $late ? 1 : 0, PDO::PARAM_INT);
        } else { // pgsql
            $stmt->bindValue(':ontime', $ontime, PDO::PARAM_BOOL);
            $stmt->bindValue(':late', $late, PDO::PARAM_BOOL);
        }
        $stmt->bindValue(':note', $note, PDO::PARAM_STR);
        $stmt->execute();
    }
    $pdo->commit();
    echo json_encode(['ok' => true]);
} catch (Throwable $e) {
    if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
        try { $pdo->rollBack(); } catch (Throwable $__) {}
    }
    error_log('[jobsheet_save] ' . $e->getMessage());
    http_response_code(200);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
?>


