<!-- meta tags and other links -->
<!DOCTYPE html>
<html lang="en" data-theme="light">

<?php include './partials/head.php' ?>

<style>
/* Inline CSS to ensure theme toggle is visible */
#themeToggle {
    width: 40px !important;
    height: 40px !important;
    border: 2px solid #ddd !important;
    border-radius: 50% !important;
    background: white !important;
    cursor: pointer !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    margin-right: 10px !important;
    font-size: 18px !important;
    z-index: 9999 !important;
    position: relative !important;
}

#themeToggle:hover {
    background: #f0f0f0 !important;
    border-color: #999 !important;
}

#themeIcon {
    font-size: 18px !important;
    line-height: 1 !important;
}

/* Ensure nav-actions is visible */
.nav-actions {
    display: flex !important;
    align-items: center !important;
    gap: 16px !important;
    flex-shrink: 0 !important;
    visibility: visible !important;
    opacity: 1 !important;
}

/* Footer styles */
.footer {
    background: #f8f9fa;
    padding: 20px 0;
    margin-top: 40px;
    border-top: 1px solid #dee2e6;
    text-align: center;
}

.footer p {
    margin: 0;
    color: #6c757d;
}

[data-theme="dark"] .footer {
    background: #212529;
    border-top-color: #495057;
}

[data-theme="dark"] .footer p {
    color: #adb5bd;
}
</style>

<body>

    <!-- Horizontal Navigation Bar -->
    <nav class="horizontal-navbar">
        <div class="nav-container">
            <!-- Logo Section -->
            <div class="nav-logo">
                                 <a href="index.php">
                    <img src="assets/images/company/logo.png" alt="PPSolution Logo" style="height: 50px; width: auto; cursor: pointer;" onmouseover="this.style.animation='spin 2s linear infinite'" onmouseout="this.style.animation='none'; this.style.transform='rotate(0deg)'">
                </a>
            </div>

            <!-- Main Navigation Menu -->
            <div class="nav-menu">
                <ul class="nav-list">
                    <li class="nav-item dropdown" data-debug="dropdown-item">
                        <a href="javascript:void(0)" class="nav-link" data-debug="dropdown-link">
                            <iconify-icon icon="solar:home-smile-angle-outline" class="nav-icon"></iconify-icon>
                            <span>Dashboard</span>
                            <iconify-icon icon="solar:alt-arrow-down-outline" class="dropdown-arrow"></iconify-icon>
                        </a>
                                                 <ul class="dropdown-menu" data-debug="dropdown-menu">
                             <li><a href="index.php">Dashboard</a></li>
                            <li><a href="index-2.php">CRM</a></li>
                            <li><a href="index-3.php">eCommerce</a></li>
                            <li><a href="index-4.php">Cryptocurrency</a></li>
                            <li><a href="index-5.php">Investment</a></li>
                            <li><a href="index-6.php">LMS</a></li>
                            <li><a href="index-7.php">NFT & Gaming</a></li>
                            <li><a href="index-8.php">Medical</a></li>
                            <li><a href="index-9.php">Analytics</a></li>
                            <li><a href="index-10.php">POS & Inventory</a></li>
                        </ul>
                    </li>

                    <li class="nav-item dropdown" data-debug="dropdown-item">
                        <a href="javascript:void(0)" class="nav-link" data-debug="dropdown-link">
                            <iconify-icon icon="solar:document-text-outline" class="nav-icon"></iconify-icon>
                            <span>Components</span>
                            <iconify-icon icon="solar:alt-arrow-down-outline" class="dropdown-arrow"></iconify-icon>
                        </a>
                        <ul class="dropdown-menu" data-debug="dropdown-menu">
                            <li><a href="typography.php">Typography</a></li>
                            <li><a href="colors.php">Colors</a></li>
                            <li><a href="button.php">Button</a></li>
                            <li><a href="dropdown.php">Dropdown</a></li>
                            <li><a href="alert.php">Alerts</a></li>
                            <li><a href="card.php">Card</a></li>
                            <li><a href="carousel.php">Carousel</a></li>
                            <li><a href="avatar.php">Avatars</a></li>
                            <li><a href="progress.php">Progress bar</a></li>
                            <li><a href="tabs.php">Tab & Accordion</a></li>
                            <li><a href="pagination.php">Pagination</a></li>
                            <li><a href="badges.php">Badges</a></li>
                        </ul>
                    </li>

                    <li class="nav-item dropdown" data-debug="dropdown-item">
                        <a href="javascript:void(0)" class="nav-link" data-debug="dropdown-link">
                            <iconify-icon icon="solar:users-group-rounded-outline" class="nav-icon"></iconify-icon>
                            <span>Users</span>
                            <iconify-icon icon="solar:alt-arrow-down-outline" class="dropdown-arrow"></iconify-icon>
                        </a>
                        <ul class="dropdown-menu" data-debug="dropdown-menu">
                            <li><a href="users-grid.php">Users Grid</a></li>
                            <li><a href="users-list.php">Users List</a></li>
                            <li><a href="add-user-form.php">Add User</a></li>
                            <li><a href="view-profile.php">View Profile</a></li>
                        </ul>
                    </li>

                    <li class="nav-item dropdown" data-debug="dropdown-item">
                        <a href="javascript:void(0)" class="nav-link" data-debug="dropdown-link">
                            <iconify-icon icon="solar:users-group-two-rounded-outline" class="nav-icon"></iconify-icon>
                            <span>Customers</span>
                            <iconify-icon icon="solar:alt-arrow-down-outline" class="dropdown-arrow"></iconify-icon>
                        </a>
                        <ul class="dropdown-menu" data-debug="dropdown-menu">
                            <li><a href="customer_crud.php">Customer Management</a></li>
                            <li><a href="customer_crud_new.php">New Customer</a></li>
                        </ul>
                    </li>

                    <li class="nav-item dropdown" data-debug="dropdown-item">
                        <a href="javascript:void(0)" class="nav-link" data-debug="dropdown-link">
                            <iconify-icon icon="solar:folder-with-files-outline" class="nav-icon"></iconify-icon>
                            <span>Projects</span>
                            <iconify-icon icon="solar:alt-arrow-down-outline" class="dropdown-arrow"></iconify-icon>
                        </a>
                        <ul class="dropdown-menu" data-debug="dropdown-menu">
                            <li><a href="project_crud.php">Project Management</a></li>
                            <li><a href="project_crud_new.php">New Project</a></li>
                        </ul>
                    </li>

                    <li class="nav-item dropdown" data-debug="dropdown-item">
                        <a href="javascript:void(0)" class="nav-link" data-debug="dropdown-link">
                            <iconify-icon icon="solar:calendar-outline" class="nav-icon"></iconify-icon>
                            <span>Activities</span>
                            <iconify-icon icon="solar:alt-arrow-down-outline" class="dropdown-arrow"></iconify-icon>
                        </a>
                        <ul class="dropdown-menu" data-debug="dropdown-menu">
                            <li><a href="activity_crud.php">Activity Management</a></li>
                            <li><a href="activity_crud_new.php">New Activity</a></li>
                        </ul>
                    </li>

                    <li class="nav-item">
                        <a href="log_view.php" class="nav-link">
                            <iconify-icon icon="solar:document-text-outline" class="nav-icon"></iconify-icon>
                            <span>Logs</span>
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Right Side Actions -->
            <div class="nav-actions">
                <!-- User Menu -->
                <div class="user-menu dropdown" data-debug="user-menu" data-bs-auto-close="false">
                    <button class="user-button" type="button" data-debug="user-button" data-bs-toggle="none">
                        <div class="user-avatar">
                            <?php
                            // Get user profile photo
                            $user_id = $_SESSION['user_id'] ?? null;
                            $profile_photo = null;
                            if ($user_id) {
                                $stmt = $pdo->prepare("SELECT profile_photo FROM users WHERE id = ?");
                                $stmt->execute([$user_id]);
                                $user_data = $stmt->fetch();
                                $profile_photo = $user_data['profile_photo'] ?? null;
                            }
                            ?>
                            <?php if ($profile_photo && file_exists($profile_photo)): ?>
                                                     <img src="<?= htmlspecialchars($profile_photo) ?>"
                          alt="Profile Photo"
                          class="avatar-image"
                          style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                            <?php else: ?>
                                <iconify-icon icon="solar:user-outline" class="avatar-icon"></iconify-icon>
                            <?php endif; ?>
                        </div>
                        <span class="user-name"><?= htmlspecialchars($_SESSION['user_display_name'] ?? 'User') ?></span>
                        <iconify-icon icon="solar:alt-arrow-down-outline" class="dropdown-arrow"></iconify-icon>
                    </button>
                    <ul class="dropdown-menu" data-debug="user-dropdown-menu">
                        <li><a href="view-profile.php">
                            <iconify-icon icon="solar:user-outline" class="me-2"></iconify-icon>
                            Profile
                        </a></li>
                        <li><a href="settings.php">
                            <iconify-icon icon="solar:settings-outline" class="me-2"></iconify-icon>
                            Settings
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a href="logout.php">
                            <iconify-icon icon="solar:logout-2-outline" class="me-2"></iconify-icon>
                            Logout
                        </a></li>
                    </ul>
                </div>
            </div>

            <!-- Mobile Menu Toggle -->
            <button class="mobile-menu-toggle" data-debug="mobile-menu-toggle">
                <iconify-icon icon="heroicons:bars-3-solid" class="menu-icon"></iconify-icon>
            </button>
        </div>
    </nav>

    <!-- Main Content Area -->
    <main class="main-content">
        <div class="content-wrapper">
