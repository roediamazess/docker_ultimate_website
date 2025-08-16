<?php
// Test langsung untuk API project ID uniqueness
require_once 'db.php';

echo "<h1>🧪 Direct API Test - Project ID Uniqueness</h1>";

// Test 1: Check database connection
echo "<h2>1. Database Connection Test</h2>";
try {
    $stmt = $pdo->query("SELECT 1");
    echo "✅ Database connection successful<br>";
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "<br>";
    exit;
}

// Test 2: Check if projects table exists
echo "<h2>2. Projects Table Test</h2>";
try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM projects");
    $totalProjects = $stmt->fetchColumn();
    echo "✅ Projects table exists. Total projects: <strong>$totalProjects</strong><br>";
} catch (Exception $e) {
    echo "❌ Projects table error: " . $e->getMessage() . "<br>";
    exit;
}

// Test 3: Check specific project ID PRJ999
echo "<h2>3. Specific Project ID Test (PRJ999)</h2>";
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM projects WHERE project_id = ?");
    $stmt->execute(['PRJ999']);
    $count = $stmt->fetchColumn();
    
    echo "Project ID 'PRJ999' count: <strong>$count</strong><br>";
    if ($count > 0) {
        echo "✅ PRJ999 sudah ada di database<br>";
        
        // Show details
        $stmt = $pdo->prepare("SELECT * FROM projects WHERE project_id = ?");
        $stmt->execute(['PRJ999']);
        $project = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo "<h3>Project Details:</h3>";
        echo "<pre>" . print_r($project, true) . "</pre>";
    } else {
        echo "❌ PRJ999 tidak ada di database<br>";
    }
} catch (Exception $e) {
    echo "❌ Error checking PRJ999: " . $e->getMessage() . "<br>";
}

// Test 4: Show all project IDs
echo "<h2>4. All Project IDs in Database</h2>";
try {
    $stmt = $pdo->query("SELECT project_id, hotel_name FROM projects ORDER BY project_id");
    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($projects)) {
        echo "Tidak ada project di database<br>";
    } else {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>Project ID</th><th>Hotel Name</th></tr>";
        foreach ($projects as $project) {
            echo "<tr>";
            echo "<td><strong>" . htmlspecialchars($project['project_id']) . "</strong></td>";
            echo "<td>" . htmlspecialchars($project['hotel_name']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} catch (Exception $e) {
    echo "❌ Error fetching all projects: " . $e->getMessage() . "<br>";
}

// Test 5: Simulate API call directly
echo "<h2>5. Direct API Simulation</h2>";
try {
    $project_id = 'PRJ999';
    
    // Simulate the exact query from check_project_id_uniqueness.php
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM projects WHERE project_id = ?");
    $stmt->execute([$project_id]);
    $count = $stmt->fetchColumn();
    
    $exists = ($count > 0);
    
    echo "Testing project_id: <strong>$project_id</strong><br>";
    echo "SQL Query: <code>SELECT COUNT(*) FROM projects WHERE project_id = '$project_id'</code><br>";
    echo "Result count: <strong>$count</strong><br>";
    echo "Exists: <strong>" . ($exists ? 'YES' : 'NO') . "</strong><br>";
    
    if ($exists) {
        echo "✅ Project ID '$project_id' sudah digunakan<br>";
    } else {
        echo "❌ Project ID '$project_id' tersedia<br>";
    }
    
    // Simulate JSON response
    $response = [
        'success' => true,
        'exists' => $exists,
        'message' => $exists ? "Project ID '$project_id' sudah digunakan" : "Project ID '$project_id' tersedia",
        'count' => $count
    ];
    
    echo "<h3>JSON Response:</h3>";
    echo "<pre>" . json_encode($response, JSON_PRETTY_PRINT) . "</pre>";
    
} catch (Exception $e) {
    echo "❌ Error in API simulation: " . $e->getMessage() . "<br>";
}

// Test 6: Test with different project ID
echo "<h2>6. Test with Different Project ID</h2>";
try {
    $test_project_id = 'TEST123';
    
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM projects WHERE project_id = ?");
    $stmt->execute([$test_project_id]);
    $count = $stmt->fetchColumn();
    
    $exists = ($count > 0);
    
    echo "Testing project_id: <strong>$test_project_id</strong><br>";
    echo "Result count: <strong>$count</strong><br>";
    echo "Exists: <strong>" . ($exists ? 'YES' : 'NO') . "</strong><br>";
    
    if ($exists) {
        echo "✅ Project ID '$test_project_id' sudah digunakan<br>";
    } else {
        echo "❌ Project ID '$test_project_id' tersedia<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Error testing different project ID: " . $e->getMessage() . "<br>";
}

echo "<hr>";
echo "<h2>🔍 Troubleshooting Steps</h2>";
echo "<ol>";
echo "<li>Check console logs in browser Developer Tools</li>";
echo "<li>Verify API endpoint 'check_project_id_uniqueness.php' is accessible</li>";
echo "<li>Check if JavaScript validation is running</li>";
echo "<li>Verify event listeners are bound correctly</li>";
echo "</ol>";

echo "<h2>📱 Next Steps</h2>";
echo "<p>1. Refresh halaman projects di browser</p>";
echo "<p>2. Buka Developer Tools (F12)</p>";
echo "<p>3. Buka form Add Project</p>";
echo "<p>4. Input PRJ999 dan lihat console logs</p>";
echo "<p>5. Klik tombol '🧪 Test Validation' jika ada</p>";
?>
