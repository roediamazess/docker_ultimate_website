<?php
session_start();
try {
    require_once "db.php";
    require_once "access_control.php";
    
    // Basic HTML structure
    echo "<!DOCTYPE html>";
    echo "<html><head>";
    echo "<title>Jobsheet - Rebuilt Version</title>";
    echo "<meta charset='utf-8'>";
    echo "<meta name='viewport' content='width=device-width, initial-scale=1'>";
    echo "<link href='assets/css/bootstrap.min.css' rel='stylesheet'>";
    echo "<link href='assets/css/main.css' rel='stylesheet'>";
    echo "</head><body>";
    
    echo "<div class='container-fluid mt-3'>";
    echo "<h1>Jobsheet - Rebuilt Version</h1>";
    
    // Test database connection
    $stmt = $pdo->query("SELECT COUNT(*) FROM jobsheet");
    $count = $stmt->fetchColumn();
    echo "<div class='alert alert-info'>Database OK - Jobsheet records: $count</div>";
    
    // Basic table structure
    echo "<div class='table-responsive'>";
    echo "<table class='table table-bordered table-striped'>";
    echo "<thead class='table-dark'>";
    echo "<tr><th>User</th><th>Date</th><th>Value</th><th>On Time</th><th>Late</th><th>Note</th></tr>";
    echo "</thead><tbody>";
    
    // Fetch and display data
    $stmt = $pdo->query("SELECT * FROM jobsheet ORDER BY day DESC LIMIT 20");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['pic_name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['day']) . "</td>";
        echo "<td>" . htmlspecialchars($row['value']) . "</td>";
        echo "<td>" . ($row['ontime'] ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-secondary">No</span>') . "</td>";
        echo "<td>" . ($row['late'] ? '<span class="badge bg-danger">Yes</span>' : '<span class="badge bg-secondary">No</span>') . "</td>";
        echo "<td>" . htmlspecialchars($row['note']) . "</td>";
        echo "</tr>";
    }
    
    echo "</tbody></table>";
    echo "</div>";
    
    // Navigation links
    echo "<div class='mt-3'>";
    echo "<a href='jobsheet_simple.php' class='btn btn-secondary'>← Back to simple version</a> ";
    echo "<a href='jobsheet.php' class='btn btn-primary'>Test original jobsheet.php</a>";
    echo "</div>";
    
    echo "</div>";
    
    echo "<script src='assets/js/bootstrap.bundle.min.js'></script>";
    echo "</body></html>";
    
} catch (Exception $e) {
    echo "<h1>❌ Error!</h1>";
    echo "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
    echo "<a href='jobsheet_simple.php'>← Back to simple version</a>";
}
?>

