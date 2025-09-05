<?php
session_start();
require_once 'db.php';

// Cek login
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    header('Location: login.php');
    exit;
}

// Get user info
$user_id = $_SESSION['user_id'] ?? null;
$user_name = $_SESSION['user_display_name'] ?? 'User';
$user_email = $_SESSION['user_email'] ?? 'user@example.com';
$user_role = $_SESSION['user_role'] ?? 'User';

// Get activities count
try {
    $stmt = $pdo->query('SELECT COUNT(*) as total FROM activities');
    $total_activities = $stmt->fetchColumn();
} catch (Exception $e) {
    $total_activities = 0;
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PowerPro Dashboard - Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/iconify-icon@1.0.7/dist/iconify-icon.min.js"></script>
    <style>
        body { 
            background: #f8fafc; 
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        }
        
        .dashboard-header { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
            color: white; 
            padding: 2rem; 
            border-radius: 12px; 
            margin-bottom: 2rem; 
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .activity-card { 
            background: white; 
            border-radius: 12px; 
            padding: 1.5rem; 
            box-shadow: 0 4px 6px rgba(0,0,0,0.1); 
            margin-bottom: 1rem; 
            border: 1px solid #e2e8f0;
        }
        
        .user-info { 
            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%); 
            padding: 1.5rem; 
            border-radius: 12px; 
            margin-bottom: 1.5rem; 
            border-left: 4px solid #2196f3;
        }
        
        .quick-actions { 
            display: flex; 
            gap: 1rem; 
            flex-wrap: wrap; 
        }
        
        .btn-action { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
            color: white; 
            border: none; 
            padding: 0.75rem 1.5rem; 
            border-radius: 8px; 
            text-decoration: none; 
            display: inline-flex; 
            align-items: center; 
            gap: 0.5rem; 
            transition: all 0.3s ease;
            font-weight: 500;
        }
        
        .btn-action:hover { 
            background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%); 
            color: white; 
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }
        
        .nav-sidebar {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            border: 1px solid #e2e8f0;
        }
        
        .nav-item {
            margin-bottom: 0.5rem;
        }
        
        .nav-item a {
            color: #64748b;
            text-decoration: none;
            padding: 0.5rem 0.75rem;
            border-radius: 8px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .nav-item a:hover {
            background: #f1f5f9;
            color: #334155;
        }
        
        .nav-item a.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .stats-card {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            border: 1px solid #cbd5e1;
            border-radius: 12px;
            padding: 1.5rem;
            text-align: center;
        }
        
        .stats-number {
            font-size: 2rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 0.5rem;
        }
        
        .stats-label {
            color: #64748b;
            font-size: 0.875rem;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <!-- Header -->
        <div class="dashboard-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 d-flex align-items-center gap-2">
                        <iconify-icon icon="solar:home-smile-angle-outline" style="font-size: 1.5rem;"></iconify-icon>
                        PowerPro Dashboard
                    </h1>
                    <p class="mb-0 opacity-75">Welcome back, <?= htmlspecialchars($user_name) ?>!</p>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <div class="stats-card">
                        <div class="stats-number"><?= $total_activities ?></div>
                        <div class="stats-label">Total Activities</div>
                    </div>
                    <a href="logout.php" class="btn btn-outline-light d-flex align-items-center gap-2">
                        <iconify-icon icon="solar:logout-2-outline"></iconify-icon>
                        Logout
                    </a>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3">
                <div class="nav-sidebar">
                    <h6 class="fw-semibold mb-3 d-flex align-items-center gap-2">
                        <iconify-icon icon="solar:menu-outline"></iconify-icon>
                        Navigation
                    </h6>
                    <ul class="list-unstyled">
                        <li class="nav-item">
                            <a href="index.php" class="d-flex align-items-center gap-2 active">
                                <iconify-icon icon="solar:home-smile-angle-outline"></iconify-icon>
                                Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="customer.php" class="d-flex align-items-center gap-2">
                                <iconify-icon icon="solar:users-group-two-rounded-outline"></iconify-icon>
                                Customers
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="activity.php" class="d-flex align-items-center gap-2">
                                <iconify-icon icon="solar:calendar-outline"></iconify-icon>
                                Activity
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="users.php" class="d-flex align-items-center gap-2">
                                <iconify-icon icon="solar:users-group-rounded-outline"></iconify-icon>
                                Users
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9">
                <!-- User Information -->
                <div class="user-info">
                    <h6 class="fw-semibold mb-3 d-flex align-items-center gap-2">
                        <iconify-icon icon="solar:user-outline"></iconify-icon>
                        User Information
                    </h6>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-2">
                                <strong class="text-primary">Name:</strong><br>
                                <span class="text-dark"><?= htmlspecialchars($user_name) ?></span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-2">
                                <strong class="text-primary">Email:</strong><br>
                                <span class="text-dark"><?= htmlspecialchars($user_email) ?></span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-2">
                                <strong class="text-primary">Role:</strong><br>
                                <span class="badge bg-primary"><?= htmlspecialchars($user_role) ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="activity-card">
                    <h6 class="fw-semibold mb-3 d-flex align-items-center gap-2">
                        <iconify-icon icon="solar:lightning-outline"></iconify-icon>
                        Quick Actions
                    </h6>
                    <div class="quick-actions">
                        <a href="activity.php" class="btn-action">
                            <iconify-icon icon="solar:calendar-outline"></iconify-icon>
                            View Activities
                        </a>
                        <a href="customer.php" class="btn-action">
                            <iconify-icon icon="solar:users-group-two-rounded-outline"></iconify-icon>
                            View Customers
                        </a>
                        <a href="users.php" class="btn-action">
                            <iconify-icon icon="solar:user-plus-outline"></iconify-icon>
                            Manage Users
                        </a>
                    </div>
                </div>

                <!-- Recent Activities -->
                <div class="activity-card">
                    <h6 class="fw-semibold mb-3 d-flex align-items-center gap-2">
                        <iconify-icon icon="solar:clock-circle-outline"></iconify-icon>
                        Recent Activities
                    </h6>
                    <div class="text-center py-4">
                        <iconify-icon icon="solar:calendar-outline" style="font-size: 3rem; color: #cbd5e1;"></iconify-icon>
                        <p class="text-muted mt-3 mb-0">No recent activities found.</p>
                        <small class="text-muted">Activities will appear here once they are created.</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
