<?php
// Migration script: Move users.user_role data into users.role
// Safe for both PostgreSQL and MySQL/MariaDB

require_once __DIR__ . '/db.php';

function columnMap(PDO $pdo): array {
    $cols = [];
    try {
        $stmt = $pdo->query("SELECT column_name, is_nullable FROM information_schema.columns WHERE table_name = 'users'");
        while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $cols[$r['column_name']] = strtoupper((string)$r['is_nullable']) !== 'NO' ? 'NULL' : 'NOT NULL';
        }
    } catch (Throwable $e) {}
    return $cols;
}

function ensureRoleColumn(PDO $pdo, array $cols): void {
    if (!array_key_exists('role', $cols)) {
        try { $pdo->exec("ALTER TABLE users ADD COLUMN role VARCHAR(40) NULL"); } catch (Throwable $e) {}
    }
}

function copyUserRoleToRole(PDO $pdo): int {
    try {
        $sql = "UPDATE users SET role = user_role WHERE (role IS NULL OR role = '') AND user_role IS NOT NULL AND user_role <> ''";
        return (int)$pdo->exec($sql);
    } catch (Throwable $e) {
        return -1; // signal error
    }
}

$result = [
    'added_role_column' => false,
    'updated_rows' => 0,
    'dropped_user_role' => false,
    'errors' => []
];

try {
    $pdo->beginTransaction();
    $cols = columnMap($pdo);
    if (!array_key_exists('role', $cols)) {
        ensureRoleColumn($pdo, $cols);
        $result['added_role_column'] = true;
    }
    $affected = copyUserRoleToRole($pdo);
    if ($affected >= 0) { $result['updated_rows'] = $affected; }
    // Try dropping user_role column if copy was successful or column exists
    try {
        $cols = columnMap($pdo);
        if (array_key_exists('user_role', $cols)) {
            $pdo->exec("ALTER TABLE users DROP COLUMN user_role");
            $result['dropped_user_role'] = true;
        }
    } catch (Throwable $e) { $result['errors'][] = 'drop user_role: '.$e->getMessage(); }

    $pdo->commit();
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    $result['errors'][] = $e->getMessage();
}

$summary = "users.role migration\n";
$summary .= 'added_role_column: ' . ($result['added_role_column'] ? 'yes' : 'no') . "\n";
$summary .= 'updated_rows: ' . $result['updated_rows'] . "\n";
if (!empty($result['errors'])) { $summary .= 'errors: ' . implode(' | ', $result['errors']) . "\n"; }

@header('Content-Type: text/plain');
echo $summary;
@file_put_contents(__DIR__.'/migrate_users_role.log', $summary);


