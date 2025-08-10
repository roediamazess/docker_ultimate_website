<?php
require_once __DIR__ . '/db.php';

header('Content-Type: text/plain');

function list_columns(PDO $pdo, string $table): array {
    $stmt = $pdo->prepare("SELECT column_name, data_type FROM information_schema.columns WHERE table_name = :t ORDER BY ordinal_position");
    $stmt->execute(['t' => $table]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

foreach (['activities','projects','users'] as $t) {
    echo "Table: $t\n";
    try {
        foreach (list_columns($pdo, $t) as $col) {
            echo " - {$col['column_name']} ({$col['data_type']})\n";
        }
    } catch (Throwable $e) {
        echo " Error reading $t: " . $e->getMessage() . "\n";
    }
    echo "\n";
}

// Test simple query used by activity_crud.php
echo "Test SELECT join columns...\n";
try {
    $sql = "SELECT a.id, a.created_by, a.priority, a.customer, a.project, p.project_name, u.display_name AS created_by_name FROM activities a LEFT JOIN projects p ON p.project_id = a.project_id LEFT JOIN users u ON u.id = a.created_by LIMIT 1";
    $stmt = $pdo->query($sql);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo $row ? json_encode($row) : "No rows";
    echo "\n";
} catch (Throwable $e) {
    echo " Query error: " . $e->getMessage() . "\n";
}



