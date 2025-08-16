<?php
// Test file untuk mengecek project ID uniqueness
require_once 'db.php';

echo "<h2>🧪 Test Project ID Uniqueness Check</h2>";

try {
    // Test 1: Check if PRJ999 exists
    echo "<h3>Test 1: Check if PRJ999 exists</h3>";
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM projects WHERE project_id = ?");
    $stmt->execute(['PRJ999']);
    $count = $stmt->fetchColumn();
    
    echo "Project ID 'PRJ999' count: <strong>$count</strong><br>";
    if ($count > 0) {
        echo "✅ PRJ999 sudah ada di database<br>";
    } else {
        echo "❌ PRJ999 tidak ada di database<br>";
    }
    
    // Test 2: Show all project IDs
    echo "<h3>Test 2: Show all existing project IDs</h3>";
    $stmt = $pdo->query("SELECT project_id FROM projects ORDER BY project_id");
    $projectIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($projectIds)) {
        echo "Tidak ada project di database<br>";
    } else {
        echo "Project IDs yang ada:<br>";
        echo "<ul>";
        foreach ($projectIds as $id) {
            echo "<li><strong>$id</strong></li>";
        }
        echo "</ul>";
    }
    
    // Test 3: Test the API endpoint
    echo "<h3>Test 3: Test API endpoint</h3>";
    echo "<button onclick='testAPI()'>Test API Check</button>";
    echo "<div id='api-result'></div>";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}
?>

<script>
async function testAPI() {
    const resultDiv = document.getElementById('api-result');
    resultDiv.innerHTML = '🔄 Testing API...';
    
    try {
        const response = await fetch('check_project_id_uniqueness.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'project_id=PRJ999'
        });
        
        const result = await response.json();
        resultDiv.innerHTML = `
            <h4>API Response:</h4>
            <pre>${JSON.stringify(result, null, 2)}</pre>
            <p><strong>Exists:</strong> ${result.exists ? 'Yes' : 'No'}</p>
            <p><strong>Message:</strong> ${result.message}</p>
        `;
    } catch (error) {
        resultDiv.innerHTML = `❌ Error: ${error.message}`;
    }
}
</script>
