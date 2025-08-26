<?php
session_start();
require_once 'db.php';

echo "<h1>🔧 Fix Database Schema</h1>";

try {
    // Drop and recreate customers table according to schema
    echo "<h2>🔄 Recreating Customers Table:</h2>";
    
    // Drop existing table
    $pdo->exec("DROP TABLE IF EXISTS customers CASCADE");
    echo "<p>✅ Dropped existing customers table</p>";
    
    // Create table according to schema
    $sql = "CREATE TABLE customers (
        id SERIAL PRIMARY KEY,
        customer_id VARCHAR(50) NOT NULL,
        name VARCHAR(100),
        star SMALLINT,
        room VARCHAR(50),
        outlet VARCHAR(50),
        type customer_type,
        \"group\" TEXT,
        zone TEXT,
        address TEXT,
        billing billing_type,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    $pdo->exec($sql);
    echo "<p>✅ Created customers table with correct schema</p>";
    
    // Insert sample data
    echo "<h2>📝 Inserting Sample Data:</h2>";
    
    $sample_data = [
        [
            'customer_id' => 'CUST001',
            'name' => 'Test Corp',
            'star' => 5,
            'room' => '100',
            'outlet' => '2',
            'type' => 'Hotel',
            'group' => 'Premium',
            'zone' => 'Jakarta',
            'address' => 'Jl. Sudirman No. 123',
            'billing' => 'Monthly'
        ],
        [
            'customer_id' => 'CUST002',
            'name' => 'Sample Restaurant',
            'star' => 4,
            'room' => '50',
            'outlet' => '1',
            'type' => 'Restaurant',
            'group' => 'Standard',
            'zone' => 'Bandung',
            'address' => 'Jl. Asia Afrika No. 45',
            'billing' => 'Weekly'
        ]
    ];
    
    $insert_sql = "INSERT INTO customers (customer_id, name, star, room, outlet, type, \"group\", zone, address, billing) 
                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($insert_sql);
    
    foreach ($sample_data as $data) {
        $stmt->execute([
            $data['customer_id'],
            $data['name'],
            $data['star'],
            $data['room'],
            $data['outlet'],
            $data['type'],
            $data['group'],
            $data['zone'],
            $data['address'],
            $data['billing']
        ]);
    }
    
    echo "<p>✅ Inserted " . count($sample_data) . " sample records</p>";
    
    // Verify structure
    echo "<h2>✅ Final Table Structure:</h2>";
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
    
    // Test query
    echo "<h2>🧪 Test Query:</h2>";
    $sql = "SELECT c.* FROM customers c ORDER BY c.created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p>✅ Query successful! Found " . count($customers) . " customers</p>";
    
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
    
    echo "<h2>🎉 Success!</h2>";
    echo "<p>✅ Database schema sudah diperbaiki sesuai dengan database_schema_postgres.sql</p>";
    echo "<p>✅ Sample data sudah ditambahkan</p>";
    echo "<p>✅ Query test berhasil</p>";
    echo "<p>🔄 Sekarang coba akses halaman customer.php</p>";
    
} catch (PDOException $e) {
    echo "<h1>❌ Error!</h1>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
    echo "<p>File: " . $e->getFile() . "</p>";
    echo "<p>Line: " . $e->getLine() . "</p>";
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
table { margin: 20px 0; }
th, td { padding: 8px; text-align: left; border: 1px solid #ddd; }
th { background-color: #f2f2f2; }
</style>




