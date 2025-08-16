<?php
// Test script to verify project ID uniqueness validation
require_once 'db.php';
require_once 'access_control.php';

// Ensure user is logged in
require_login();

// Function to check if project ID exists
function checkProjectIdExists($project_id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM projects WHERE project_id = ?");
        $stmt->execute([$project_id]);
        $count = $stmt->fetchColumn();
        return $count > 0;
    } catch (Exception $e) {
        return false;
    }
}

// Function to create a test project
function createTestProject($project_id, $project_name) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("INSERT INTO projects (project_id, project_name, hotel_id, start_date, end_date, type, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $project_id,
            $project_name,
            1, // hotel_id
            date('Y-m-d'),
            date('Y-m-d', strtotime('+7 days')),
            'Implementation',
            'Scheduled',
            date('Y-m-d H:i:s')
        ]);
        return true;
    } catch (Exception $e) {
        return false;
    }
}

// Test cases
echo "<h2>Project ID Uniqueness Test</h2>";

// Test 1: Check if a project ID exists
$test_project_id = "TEST-001";
$exists = checkProjectIdExists($test_project_id);
echo "<p>Test 1 - Check if project ID '$test_project_id' exists: " . ($exists ? "YES" : "NO") . "</p>";

// Test 2: Try to create a project with a unique ID
$unique_id = "TEST-" . time(); // Generate a unique ID
$created = createTestProject($unique_id, "Test Project " . time());
echo "<p>Test 2 - Create project with unique ID '$unique_id': " . ($created ? "SUCCESS" : "FAILED") . "</p>";

// Test 3: Try to create a project with an existing ID
if ($created) {
    $duplicate_created = createTestProject($unique_id, "Duplicate Test Project");
    echo "<p>Test 3 - Create project with duplicate ID '$unique_id': " . ($duplicate_created ? "SUCCESS (UNEXPECTED)" : "FAILED (EXPECTED)") . "</p>";
}

// Test 4: Frontend validation simulation
if (isset($_GET['project_id'])) {
    $project_id = trim($_GET['project_id']);
    $exists = checkProjectIdExists($project_id);
    
    header('Content-Type: application/json');
    echo json_encode([
        'exists' => $exists,
        'message' => $exists ? "Project ID '$project_id' sudah digunakan" : "Project ID '$project_id' tersedia"
    ]);
    exit;
}

echo "<hr>";
echo "<h3>Manual Test Form</h3>";
echo "<form method='get'>";
echo "<label for='project_id'>Project ID:</label>";
echo "<input type='text' name='project_id' id='project_id' required>";
echo "<button type='submit'>Check Uniqueness</button>";
echo "</form>";

if (isset($_GET['project_id'])) {
    $project_id = trim($_GET['project_id']);
    $exists = checkProjectIdExists($project_id);
    echo "<p>Project ID '$project_id' " . ($exists ? "already exists" : "is available") . "</p>";
}
?>