<?php
require_once 'db.php';

echo "<!DOCTYPE html>
<html>
<head>
    <title>Test Web Interface</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .error { color: red; }
        .success { color: green; }
        .warning { color: orange; }
        .info { color: blue; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 5px; }
        .button { background: #007cba; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; margin: 10px 0; }
        .button:hover { background: #005a87; }
        table { border-collapse: collapse; width: 100%; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h1>Test Web Interface</h1>";

try {
    // Check if project ID '100' already exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM projects WHERE project_id = ?");
    $stmt->execute(['100']);
    $count = $stmt->fetchColumn();
    
    if ($count > 0) {
        echo "<p class='info'>Project ID '100' already exists in database</p>";
    } else {
        echo "<p class='info'>Project ID '100' does not exist in database</p>";
        
        // Insert a project with ID '100' for testing
        $stmt = $pdo->prepare("INSERT INTO projects (project_id, project_name, hotel_name, start_date, type, status) VALUES (?, 'Test Project', 1, '2023-01-01', 'Maintenance', 'Scheduled')");
        $stmt->execute(['100']);
        echo "<p class='success'>✅ Inserted test project with ID '100'</p>";
    }
    
    // Show some sample projects
    echo "<h2>Sample projects in database:</h2>";
    $stmt = $pdo->query('SELECT id, project_id, project_name FROM projects ORDER BY created_at DESC LIMIT 10');
    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($projects)) {
        echo "<p class='info'>No projects found in database</p>";
    } else {
        echo "<table><tr><th>ID</th><th>Project ID</th><th>Project Name</th></tr>";
        foreach ($projects as $project) {
            echo "<tr><td>{$project['id']}</td><td>{$project['project_id']}</td><td>{$project['project_name']}</td></tr>";
        }
        echo "</table>";
    }
    
    echo "<h2>Test Form</h2>";
    echo "<p class='info'>Try to create a project with ID '100' through the web interface:</p>";
    
    // Show a form that mimics the web interface
    echo "<form method='post' action='projects.php'>
            <input type='hidden' name='action' value='save_project'>
            <div>
                <label for='project_id'>Project ID:</label>
                <input type='text' id='project_id' name='project_id' value='100' required>
            </div>
            <div>
                <label for='project_name'>Project Name:</label>
                <input type='text' id='project_name' name='project_name' value='Test Duplicate Project' required>
            </div>
            <div>
                <label for='hotel_id'>Hotel ID:</label>
                <input type='text' id='hotel_id' name='hotel_id' value='1'>
            </div>
            <div>
                <label for='hotel_name'>Hotel Name:</label>
                <input type='text' id='hotel_name' name='hotel_name' value='Test Hotel'>
            </div>
            <div>
                <label for='start_date'>Start Date:</label>
                <input type='date' id='start_date' name='start_date' value='2023-01-01'>
            </div>
            <div>
                <label for='end_date'>End Date:</label>
                <input type='date' id='end_date' name='end_date' value='2023-01-31'>
            </div>
            <div>
                <label for='type'>Type:</label>
                <select id='type' name='type'>
                    <option value='Maintenance'>Maintenance</option>
                </select>
            </div>
            <div>
                <label for='status'>Status:</label>
                <select id='status' name='status'>
                    <option value='Scheduled'>Scheduled</option>
                </select>
            </div>
            <div>
                <label for='project_remark'>Project Remark:</label>
                <input type='text' id='project_remark' name='project_remark' value='Test remark'>
            </div>
            <button type='submit' class='button'>Create Project</button>
          </form>";
    
    echo "<p class='info'>When you submit this form, it should show an error message because project ID '100' already exists.</p>";
    
} catch (Exception $e) {
    echo "<p class='error'>Error: " . $e->getMessage() . "</p>";
}

echo "</body>
</html>";