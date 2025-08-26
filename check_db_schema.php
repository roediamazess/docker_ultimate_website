<?php
session_start();
require_once 'db.php';

echo "<h1>🔍 Check Database Schema</h1>";

try {
    // Check customers table structure
    echo "<h2>📊 Customers Table Structure:</h2>";
    $sql = "SELECT column_name, data_type, is_nullable, column_default 
            FROM information_schema.columns 
            WHERE table_name = 'customers' 
            ORDER BY ordinal_position";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 20px 0;'>";
    echo "<tr style='background: #f0f0f0;'>";
    echo "<th>Column Name</th>";
    echo "<th>Data Type</th>";
    echo "<th>Nullable</th>";
    echo "<th>Default</th>";
    echo "</tr>";
    
    foreach ($columns as $column) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($column['column_name']) . "</td>";
        echo "<td>" . htmlspecialchars($column['data_type']) . "</td>";
        echo "<td>" . htmlspecialchars($column['is_nullable']) . "</td>";
        echo "<td>" . htmlspecialchars($column['column_default'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Check sample data
    echo "<h2>📋 Sample Data:</h2>";
    $sql = "SELECT * FROM customers LIMIT 3";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($customers) > 0) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 20px 0;'>";
        echo "<tr style='background: #f0f0f0;'>";
        foreach (array_keys($customers[0]) as $key) {
            echo "<th>" . htmlspecialchars($key) . "</th>";
        }
        echo "</tr>";
        
        foreach ($customers as $customer) {
            echo "<tr>";
            foreach ($customer as $value) {
                echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>❌ Tidak ada data customers.</p>";
    }
    
} catch (PDOException $e) {
    echo "<h1>❌ Error!</h1>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
table { margin: 20px 0; }
th, td { padding: 8px; text-align: left; border: 1px solid #ddd; }
th { background-color: #f2f2f2; }
</style>




