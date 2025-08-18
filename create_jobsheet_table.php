<?php
require_once __DIR__ . '/db.php';

try {
    $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

    if ($driver === 'mysql') {
        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS jobsheet (
  user_id VARCHAR(64) NOT NULL,
  day VARCHAR(8) NOT NULL,
  value VARCHAR(20) NOT NULL,
  ontime TINYINT(1) NOT NULL DEFAULT 0,
  late TINYINT(1) NOT NULL DEFAULT 0,
  note TEXT NULL,
  PRIMARY KEY (user_id, day),
  INDEX idx_jobsheet_user (user_id),
  CONSTRAINT fk_jobsheet_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
SQL;
    } else { // postgresql default
        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS jobsheet (
  user_id VARCHAR(64) NOT NULL REFERENCES users(user_id) ON DELETE CASCADE,
  day VARCHAR(8) NOT NULL,
  value VARCHAR(20) NOT NULL,
  ontime BOOLEAN NOT NULL DEFAULT FALSE,
  late BOOLEAN NOT NULL DEFAULT FALSE,
  note TEXT NULL,
  PRIMARY KEY (user_id, day)
);
SQL;
    }

    $pdo->exec($sql);
    echo "Jobsheet table migration succeeded\n";
} catch (Throwable $e) {
    http_response_code(500);
    echo "Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}




