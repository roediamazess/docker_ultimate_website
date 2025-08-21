<!-- meta tags and other links -->
<!DOCTYPE html>
<html lang="en" data-theme="light">

<script>
// Set tema sedini mungkin sebelum CSS agar tidak flash putih di dark mode
(function() {
  try {
    var saved = localStorage.getItem('theme') || 'light';
    document.documentElement.setAttribute('data-theme', saved);
    if (saved === 'dark') {
      // Hindari kilat terang sebelum CSS termuat (samakan dengan Activity List)
      var darkBg = '#0b1220';
      document.documentElement.style.backgroundColor = darkBg;
      if (document.body) document.body.style.backgroundColor = darkBg;
    }
  } catch (e) {}
})();
</script>

<?php include './partials/head.php' ?>

<style>
/* Sinkronisasi background global agar tidak ada pinggiran terang */
html[data-theme="dark"], body[data-theme="dark"] { background-color: #0b1220 !important; }
html[data-theme="light"], body[data-theme="light"] { background-color: #f8fafc !important; }

/* Paksa semua wrapper halaman mengikuti warna tema (mengatasi style lain) */
html[data-theme="dark"] body,
html[data-theme="dark"] .main-content,
html[data-theme="dark"] .content-wrapper,
html[data-theme="dark"] .dashboard-main-body,
html[data-theme="dark"] .content,
html[data-theme="dark"] #root {
    background-color: #0b1220 !important;
    background-image: none !important;
}
html[data-theme="light"] body,
html[data-theme="light"] .main-content,
html[data-theme="light"] .content-wrapper,
html[data-theme="light"] .dashboard-main-body,
html[data-theme="light"] .content,
html[data-theme="light"] #root {
    background-color: #f8fafc !important;
    background-image: none !important;
}
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

/* Navbar floating variables */
:root { --nav-height: 72px; --nav-offset: 24px; --logo-vpad: 12px; }

/* Notification Flip Circle System Styles */
.notification-stack {
    position: fixed;
    top: calc(var(--nav-height) + var(--nav-offset) + 16px);
    left: 1.5rem; /* 24px */
    z-index: 1000;
    display: flex;
    flex-direction: column;
    gap: 1rem;
    perspective: 1000px; /* Penting untuk efek 3D semua kartu */
}

/* Color definitions for notification types */
.bg-success {
    background-color: #10b981 !important; /* Green */
}

.bg-info {
    background-color: #3b82f6 !important; /* Blue */
}

.bg-warning {
    background-color: #f59e0b !important; /* Yellow/Orange */
}

.bg-danger {
    background-color: #ef4444 !important; /* Red */
}

.bg-secondary {
    background-color: #6b7280 !important; /* Gray */
}

.bg-light {
    background-color: #f3f4f6 !important; /* Light gray */
}

.text-success {
    color: #10b981 !important;
}

.text-info {
    color: #3b82f6 !important;
}

.text-warning {
    color: #f59e0b !important;
}

.text-danger {
    color: #ef4444 !important;
}

.text-secondary {
    color: #6b7280 !important;
}

.text-dark {
    color: #1f2937 !important;
}

.text-muted {
    color: #6b7280 !important;
}

.flip-card-container {
    transform-origin: top left; /* Animasi berpusat dari pojok kiri atas */
    animation: emerge-from-logo 0.5s cubic-bezier(0.21, 1.02, 0.73, 1) forwards;
}

.flip-card {
    width: 120px;
    height: 120px;
    position: relative;
}

.flip-card-inner {
    position: absolute;
    width: 100%;
    height: 100%;
    transition: transform 0.6s;
    transform-style: preserve-3d;
}

/* Saat di-hover, putar kartu */
.flip-card:hover .flip-card-inner {
    transform: rotateY(180deg);
}

/* Style untuk sisi depan dan belakang kartu */
.flip-card-front, .flip-card-back {
    position: absolute;
    width: 100%;
    height: 100%;
    -webkit-backface-visibility: hidden; /* Safari */
    backface-visibility: hidden;
    border-radius: 9999px; /* Membuat jadi lingkaran penuh */
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

.flip-card-back {
    transform: rotateY(180deg);
}

/* Animasi "muncul dari logo" */
@keyframes emerge-from-logo {
    from {
        opacity: 0;
        transform: translateY(-40px) scale(0.5);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

/* Animasi saat notifikasi hilang */
.fade-out {
    animation: fade-out-anim 0.4s ease-in forwards;
}

@keyframes fade-out-anim {
    from { opacity: 1; transform: scale(1); }
    to { opacity: 0; transform: scale(0.8); }
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
/* Logo Notification System Styles */
#notification-container {
    position: fixed;
    top: calc(var(--nav-height) + var(--nav-offset) + 16px);
    left: 1.5rem;
    z-index: 1001;
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 0.75rem;
}

/* Floating horizontal navbar */
.horizontal-navbar {
    position: fixed;
    top: var(--nav-offset);
    left: 0;
    right: 0;
    height: var(--nav-height);
    z-index: 1050;
    display: block;
    padding: 0 12px; /* sedikit gutter agar kapsul tidak menempel tepi layar */
    background: transparent !important;
    border: none !important;
    box-shadow: none !important;
    transition: none;
    -webkit-backdrop-filter: none !important;
    backdrop-filter: none !important;
}
.horizontal-navbar::before,
.horizontal-navbar::after { display:none !important; content:none !important; }

.horizontal-navbar .nav-container {
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    padding: 0 16px;
    max-width: 1440px;
    margin: 0 auto;
}

/* Kapsul permukaan navbar */
.horizontal-navbar .nav-surface {
    height: 100%;
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    padding: 0 16px;
    border-radius: 9999px;
    -webkit-backdrop-filter: none; /* matikan glass di dalam kapsul, gunakan warna solid semi transparan */
    backdrop-filter: none;
    border: 1px solid rgba(15,23,42,0.06);
    background: rgba(255,255,255,0.98);
    box-shadow: 0 6px 16px rgba(15,23,42,0.12);
}
html[data-theme="dark"] .horizontal-navbar .nav-surface {
    border-color: rgba(148,163,184,0.12);
    background: rgba(11,18,32,0.96);
    box-shadow: 0 6px 16px rgba(2,6,23,0.45);
}

/* Samakan jarak vertikal logo (atas-bawah sama) */
.horizontal-navbar .nav-logo { height: 100%; display: flex; align-items: center; }
.horizontal-navbar .nav-logo a { height: 100%; display: flex; align-items: center; }
.horizontal-navbar .nav-logo img {
    display: block;
    height: calc(var(--nav-height) - (var(--logo-vpad) * 2)) !important;
}

/* Samakan latar belakang di dalam kapsul: menu transparan (tidak menimpa nav-surface) */
.horizontal-navbar .nav-surface .nav-menu {
    background: transparent !important;
    border: none !important;
    box-shadow: none !important;
}
html[data-theme="dark"] .horizontal-navbar .nav-surface .nav-menu {
    background: transparent !important;
    border: none !important;
    box-shadow: none !important;
}

/* Shadow saat scroll hanya untuk kapsul, bukan fullbar */
.horizontal-navbar .nav-surface.is-scrolled { box-shadow: 0 10px 28px rgba(15, 23, 42, 0.20); }
html[data-theme="dark"] .horizontal-navbar .nav-surface.is-scrolled { box-shadow: 0 12px 30px rgba(2,6,23,0.55); }

/* Offset content under fixed navbar */
.main-content { padding-top: calc(var(--nav-height) + var(--nav-offset) + 12px); }

.notification-capsule {
    transform-origin: top left;
    animation: emerge-from-logo 0.5s cubic-bezier(0.21, 1.02, 0.73, 1) forwards;
}

@keyframes emerge-from-logo {
    from {
        opacity: 0;
        transform: translateY(-30px) scale(0.6);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

.notification-capsule.hide {
    animation: fade-out 0.4s ease-in forwards;
}

@keyframes fade-out {
    from {
        opacity: 1;
        transform: scale(1);
    }
    to {
        opacity: 0;
        transform: scale(0.6);
    }
}

.progress-line {
    height: 2px;
    animation: shrink 4.5s linear forwards;
}

@keyframes shrink {
    from { width: 100%; }
    to { width: 0%; }
}
</style>

<body>

    <!-- Horizontal Navigation Bar -->
    <nav class="horizontal-navbar">
        <div class="nav-container">
            <div class="nav-surface">
            <!-- Logo Section -->
            <div class="nav-logo" id="companyLogo">
                <a href="index.php">
                    <img src="assets/images/company/logo.png" alt="PPSolution Logo" style="height: 50px; width: auto; cursor: pointer;" onmouseover="this.style.animation='spin 2s linear infinite'" onmouseout="this.style.animation='none'; this.style.transform='rotate(0deg)'">
                </a>
            </div>
            
            <!-- Notification Container for Capsule Notifications -->
            <div id="notification-container"></div>

            <!-- Main Navigation Menu -->
            <div class="nav-menu" style="background:transparent;border:none;box-shadow:none;">
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

                    <?php 
                    // Kondisi untuk menampilkan menu Users berdasarkan role
                    if (isset($_SESSION['user_role']) && in_array($_SESSION['user_role'], ['Administrator', 'Management', 'Admin Office'])): 
                    ?>
                    <li class="nav-item" data-debug="nav-item">
                        <a href="users.php" class="nav-link">
                            <iconify-icon icon="solar:users-group-rounded-outline" class="nav-icon"></iconify-icon>
                            <span>Users</span>
                        </a>
                    </li>
                    <?php endif; ?>

                    <li class="nav-item" data-debug="nav-item">
                        <a href="customer.php" class="nav-link">
                            <iconify-icon icon="solar:users-group-two-rounded-outline" class="nav-icon"></iconify-icon>
                            <span>Customers</span>
                        </a>
                    </li>

                    <li class="nav-item" data-debug="nav-item">
                        <a href="projects.php" class="nav-link">
                            <iconify-icon icon="solar:folder-with-files-outline" class="nav-icon"></iconify-icon>
                            <span>Projects</span>
                        </a>
                    </li>

                    <li class="nav-item" data-debug="nav-item">
                        <a href="activity.php" class="nav-link">
                            <iconify-icon icon="solar:calendar-outline" class="nav-icon"></iconify-icon>
                            <span>Activities</span>
                        </a>
                    </li>

                    <li class="nav-item" data-debug="nav-item">
                        <a href="jobsheet.php" class="nav-link">
                            <iconify-icon icon="solar:clipboard-list-outline" class="nav-icon"></iconify-icon>
                            <span>Jobsheet</span>
                        </a>
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
                                $stmt = $pdo->prepare("SELECT profile_photo FROM users WHERE user_id = ?");
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
        </div>
    </nav>
    <script>
    // Tambahkan bayangan pada kapsul saat discroll untuk depth
    (function(){
        var surface = document.querySelector('.horizontal-navbar .nav-surface');
        if (!surface) return;
        function onScroll(){
            if (window.scrollY > 1) surface.classList.add('is-scrolled');
            else surface.classList.remove('is-scrolled');
        }
        onScroll();
        window.addEventListener('scroll', onScroll, { passive: true });
    })();
    </script>
    <script>
    // Posisikan notifikasi tepat di bawah logo kapsul
    (function(){
        var logoEl = document.getElementById('companyLogo');
        var notifContainer = document.getElementById('notification-container');
        var notifStack = document.querySelector('.notification-stack');
        function positionNotifications(){
            if (!logoEl) return;
            var surface = document.querySelector('.horizontal-navbar .nav-surface');
            var rect = logoEl.getBoundingClientRect();
            var surfRect = surface ? surface.getBoundingClientRect() : null;
            var topPx = (surfRect ? surfRect.bottom : rect.bottom) + 12; // 12px jarak di bawah kapsul
            var leftPx = rect.left; // sejajar kiri logo
            if (notifContainer) {
                notifContainer.style.top = topPx + 'px';
                notifContainer.style.left = leftPx + 'px';
            }
            if (notifStack) {
                notifStack.style.top = topPx + 'px';
                notifStack.style.left = leftPx + 'px';
            }
        }
        window.addEventListener('resize', positionNotifications, { passive: true });
        window.addEventListener('scroll', positionNotifications, { passive: true });
        document.addEventListener('DOMContentLoaded', positionNotifications);
        // panggil segera jika elemen sudah siap
        setTimeout(positionNotifications, 0);
    })();
    </script>

    <!-- Main Content Area -->
    <main class="main-content">
        <div class="content-wrapper">

<!-- Flip Circle Notification System Scripts -->
<script>
// Flip Circle Notification System
const notificationStack = document.getElementById('notification-stack');

// Function to show flip circle notification
function showFlipNotification(title, message, type = 'info') {
    // Create container for flip card
    const container = document.createElement('div');
    container.className = 'flip-card-container';

    // Determine icon and color based on type
    let iconFront, iconBack, frontBgColor;
    switch (type) {
        case 'success':
            frontBgColor = 'bg-green-600';
            iconFront = `<svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>`;
            iconBack = `<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>`;
            break;
        case 'info':
            frontBgColor = 'bg-info';
            iconFront = `<svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>`;
            iconBack = `<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-info" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>`;
            break;
        case 'warning':
            frontBgColor = 'bg-warning';
            iconFront = `<svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>`;
            iconBack = `<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-warning" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>`;
            break;
        case 'danger':
            frontBgColor = 'bg-danger';
            iconFront = `<svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>`;
            iconBack = `<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-danger" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>`;
            break;
        default: // fallback
            frontBgColor = 'bg-secondary';
            iconFront = `<svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>`;
            iconBack = `<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-secondary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>`;
    }

    // Fill notification content
    container.innerHTML = `
        <div class="flip-card">
            <div class="flip-card-inner">
                <!-- FRONT SIDE OF CARD -->
                <div class="flip-card-front ${frontBgColor} text-white p-4 flex flex-col items-center justify-center text-center">
                    ${iconFront}
                    <p class="text-xs opacity-80 mt-1">Hover</p>
                </div>
                <!-- BACK SIDE OF CARD -->
                <div class="flip-card-back bg-white p-3 flex flex-col justify-center items-center text-center">
                    <div class="mb-1">${iconBack}</div>
                    <h4 class="font-bold text-dark text-xs">${title}</h4>
                    <p class="text-xs text-muted mb-2 leading-tight">${message}</p>
                    <button class="w-full bg-light text-dark text-xs py-1 rounded-md hover:bg-secondary transition-colors">Tutup</button>
                </div>
            </div>
        </div>
    `;
    
    const closeButton = container.querySelector('button');
    closeButton.onclick = () => container.classList.add('fade-out');

    // Remove element from DOM after fade-out animation completes
    container.addEventListener('animationend', (e) => {
        if (e.animationName === 'fade-out-anim') {
            container.remove();
        }
    });

    notificationStack.prepend(container); // Use prepend so new notifications are always on top

    // Set timer to automatically hide notification
    setTimeout(() => {
        container.classList.add('fade-out');
    }, 6000);
}

// Global function to show activity notifications
function showActivityFlipNotification(type, message) {
    let title, notificationType;
    
    switch (type) {
        case 'created':
            title = 'Activity Created!';
            notificationType = 'success';
            break;
        case 'updated':
            title = 'Activity Updated!';
            notificationType = 'info';
            break;
        case 'error':
            title = 'Error!';
            notificationType = 'danger';
            break;
        default:
            title = 'Notification';
            notificationType = 'info';
    }
    
    showFlipNotification(title, message, notificationType);
}
</script>

<!-- Logo Notification System Scripts -->
<script src="assets/js/logo-notifications.js"></script>
<script src="assets/js/activity-notifications.js"></script>

<!-- Activity Notification Integration -->
<script>
// Initialize activity notifications when page loads
document.addEventListener('DOMContentLoaded', function() {
    // Check if both systems are available
    if (typeof logoNotificationManager !== 'undefined' && logoNotificationManager.isAvailable()) {
        console.log('✅ Logo Notification System: Ready');
    } else {
        console.warn('⚠️ Logo Notification System: Not available');
    }
    
    if (typeof activityNotificationHandler !== 'undefined') {
        console.log('✅ Activity Notification Handler: Ready');
    } else {
        console.warn('⚠️ Activity Notification Handler: Not available');
    }
});

// Global functions for manual testing (development only)
function testActivityNotifications() {
    if (typeof logoNotificationManager !== 'undefined' && logoNotificationManager.isAvailable()) {
        console.log('Testing activity notifications with logoNotificationManager...');
        
        // Test activity created
        setTimeout(() => {
            logoNotificationManager.showActivityCreated('Test: Activity created successfully! 🎉');
        }, 1000);
        
        // Test activity updated
        setTimeout(() => {
            logoNotificationManager.showActivityUpdated('Test: Activity updated successfully! ✨');
        }, 3000);
        
        // Test activity canceled
        setTimeout(() => {
            logoNotificationManager.showActivityCanceled('Test: Activity canceled successfully! ❌');
        }, 5000);
        
        // Test activity error - COMMENTED OUT TO PREVENT AUTOMATIC ERROR NOTIFICATIONS
        // setTimeout(() => {
        //     logoNotificationManager.showActivityError('Test: Operation failed! ❌');
        // }, 7000);
    } else {
        console.warn('LogoNotificationManager not available');
    }
}

// Function to manually trigger notifications (for integration with existing code)
function triggerActivityNotification(type, message) {
    if (typeof logoNotificationManager !== 'undefined' && logoNotificationManager.isAvailable()) {
        switch (type) {
            case 'created':
                logoNotificationManager.showActivityCreated(message);
                break;
            case 'updated':
                logoNotificationManager.showActivityUpdated(message);
                break;
            case 'cancel':
            case 'canceled':
                logoNotificationManager.showActivityCanceled(message);
                break;
            case 'error':
                logoNotificationManager.showActivityError(message);
                break;
            default:
                logoNotificationManager.showInfo(message);
                break;
        }
    } else {
        console.warn('LogoNotificationManager not available');
    }
}
</script>
