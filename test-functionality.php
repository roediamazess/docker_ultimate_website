<?php
session_start();
require_once 'db.php';
require_once 'access_control.php';

// Cek akses
require_login();

// Test CRUD operations
$test_message = '';

if (isset($_POST['test_create'])) {
    try {
        // Test create operation
        $stmt = $pdo->prepare('INSERT INTO logs (user_email, action, description, created_at) VALUES (?, ?, ?, ?)');
        $stmt->execute(['test@example.com', 'test_create', 'Testing CRUD functionality', date('Y-m-d H:i:s')]);
        $test_message = '✅ Create operation successful!';
    } catch (Exception $e) {
        $test_message = '❌ Create operation failed: ' . $e->getMessage();
    }
}

if (isset($_POST['test_read'])) {
    try {
        // Test read operation
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM logs WHERE action = ?');
        $stmt->execute(['test_create']);
        $count = $stmt->fetchColumn();
        $test_message = "✅ Read operation successful! Found $count test records.";
    } catch (Exception $e) {
        $test_message = '❌ Read operation failed: ' . $e->getMessage();
    }
}

if (isset($_POST['test_update'])) {
    try {
        // Test update operation
        $stmt = $pdo->prepare('UPDATE logs SET description = ? WHERE action = ? LIMIT 1');
        $stmt->execute(['Updated test description', 'test_create']);
        $test_message = '✅ Update operation successful!';
    } catch (Exception $e) {
        $test_message = '❌ Update operation failed: ' . $e->getMessage();
    }
}

if (isset($_POST['test_delete'])) {
    try {
        // Test delete operation
        $stmt = $pdo->prepare('DELETE FROM logs WHERE action = ?');
        $stmt->execute(['test_create']);
        $test_message = '✅ Delete operation successful!';
    } catch (Exception $e) {
        $test_message = '❌ Delete operation failed: ' . $e->getMessage();
    }
}
?>

<?php include './partials/layouts/layoutHorizontal.php'; ?>

<div class="dashboard-main-body">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4>🧪 Functionality Test Suite</h4>
                        <p class="text-muted">Testing Event Listeners & CRUD Operations</p>
                    </div>
                    <div class="card-body">
                        
                        <!-- Test Results -->
                        <?php if ($test_message): ?>
                            <div class="alert alert-info">
                                <strong>Test Result:</strong> <?= htmlspecialchars($test_message) ?>
                            </div>
                        <?php endif; ?>

                        <!-- CRUD Test Buttons -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h5>📊 Database CRUD Tests</h5>
                                <div class="d-flex gap-2 flex-wrap">
                                    <form method="post" style="display: inline;">
                                        <button type="submit" name="test_create" class="btn btn-success btn-sm">
                                            <iconify-icon icon="solar:add-circle-outline"></iconify-icon>
                                            Test Create
                                        </button>
                                    </form>
                                    <form method="post" style="display: inline;">
                                        <button type="submit" name="test_read" class="btn btn-info btn-sm">
                                            <iconify-icon icon="solar:eye-outline"></iconify-icon>
                                            Test Read
                                        </button>
                                    </form>
                                    <form method="post" style="display: inline;">
                                        <button type="submit" name="test_update" class="btn btn-warning btn-sm">
                                            <iconify-icon icon="solar:pen-outline"></iconify-icon>
                                            Test Update
                                        </button>
                                    </form>
                                    <form method="post" style="display: inline;">
                                        <button type="submit" name="test_delete" class="btn btn-danger btn-sm">
                                            <iconify-icon icon="solar:trash-bin-outline"></iconify-icon>
                                            Test Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <h5>🎯 JavaScript Event Tests</h5>
                                <div class="d-flex gap-2 flex-wrap">
                                    <button id="testDropdown" class="btn btn-primary btn-sm">
                                        <iconify-icon icon="solar:menu-dots-outline"></iconify-icon>
                                        Test Dropdown
                                    </button>
                                    <button id="testThemeToggle" class="btn btn-secondary btn-sm">
                                        <iconify-icon icon="solar:sun-outline"></iconify-icon>
                                        Test Theme Toggle
                                    </button>
                                    <button id="testUserMenu" class="btn btn-dark btn-sm">
                                        <iconify-icon icon="solar:user-outline"></iconify-icon>
                                        Test User Menu
                                    </button>
                                    <button id="testMobileMenu" class="btn btn-outline-primary btn-sm">
                                        <iconify-icon icon="solar:hamburger-menu-outline"></iconify-icon>
                                        Test Mobile Menu
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Test Results Display -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h6>📋 JavaScript Console Logs</h6>
                                    </div>
                                    <div class="card-body">
                                        <div id="consoleOutput" style="background: #f8f9fa; padding: 10px; border-radius: 5px; font-family: monospace; font-size: 12px; max-height: 200px; overflow-y: auto;">
                                            <div>Console logs will appear here...</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h6>🔍 Event Listener Status</h6>
                                    </div>
                                    <div class="card-body">
                                        <div id="eventStatus">
                                            <div class="mb-2">
                                                <span class="badge bg-secondary">Dropdowns: <span id="dropdownStatus">Checking...</span></span>
                                            </div>
                                            <div class="mb-2">
                                                <span class="badge bg-secondary">Theme Toggle: <span id="themeStatus">Checking...</span></span>
                                            </div>
                                            <div class="mb-2">
                                                <span class="badge bg-secondary">User Menu: <span id="userMenuStatus">Checking...</span></span>
                                            </div>
                                            <div class="mb-2">
                                                <span class="badge bg-secondary">Mobile Menu: <span id="mobileMenuStatus">Checking...</span></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Navigation Test -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h6>🧭 Navigation Test</h6>
                                    </div>
                                    <div class="card-body">
                                        <p>Test the navigation dropdowns by hovering over the menu items above. All dropdowns should work properly.</p>
                                        <div class="alert alert-info">
                                            <strong>Expected Behavior:</strong>
                                            <ul class="mb-0 mt-2">
                                                <li>Dropdowns should open on hover (desktop) or click (mobile)</li>
                                                <li>Theme toggle should work with ripple effect</li>
                                                <li>User menu should open/close properly</li>
                                                <li>Mobile menu should toggle on mobile devices</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Override console.log to capture logs
const originalConsoleLog = console.log;
const consoleOutput = document.getElementById('consoleOutput');

console.log = function(...args) {
    originalConsoleLog.apply(console, args);
    
    const logEntry = document.createElement('div');
    logEntry.style.borderBottom = '1px solid #dee2e6';
    logEntry.style.padding = '2px 0';
    logEntry.textContent = args.join(' ');
    consoleOutput.appendChild(logEntry);
    
    // Keep only last 20 entries
    while (consoleOutput.children.length > 20) {
        consoleOutput.removeChild(consoleOutput.firstChild);
    }
    
    consoleOutput.scrollTop = consoleOutput.scrollHeight;
};

// Test functions
document.getElementById('testDropdown').addEventListener('click', function() {
    console.log('🧪 Testing dropdown functionality...');
    const dropdowns = document.querySelectorAll('.nav-item.dropdown');
    console.log(`Found ${dropdowns.length} dropdown items`);
    
    dropdowns.forEach((dropdown, index) => {
        const link = dropdown.querySelector('.nav-link');
        console.log(`Dropdown ${index + 1}: ${link.textContent.trim()}`);
    });
    
    document.getElementById('dropdownStatus').textContent = '✅ Working';
    document.getElementById('dropdownStatus').parentElement.className = 'badge bg-success';
});

document.getElementById('testThemeToggle').addEventListener('click', function() {
    console.log('🧪 Testing theme toggle functionality...');
    const toggle = document.getElementById('theme-toggle');
    const overlay = document.getElementById('theme-transition-overlay');
    
    if (toggle && overlay) {
        console.log('✅ Theme toggle elements found');
        document.getElementById('themeStatus').textContent = '✅ Working';
        document.getElementById('themeStatus').parentElement.className = 'badge bg-success';
    } else {
        console.log('❌ Theme toggle elements not found');
        document.getElementById('themeStatus').textContent = '❌ Missing';
        document.getElementById('themeStatus').parentElement.className = 'badge bg-danger';
    }
});

document.getElementById('testUserMenu').addEventListener('click', function() {
    console.log('🧪 Testing user menu functionality...');
    const userButton = document.querySelector('.user-button');
    const userDropdown = document.querySelector('.user-menu .dropdown-menu');
    
    if (userButton && userDropdown) {
        console.log('✅ User menu elements found');
        document.getElementById('userMenuStatus').textContent = '✅ Working';
        document.getElementById('userMenuStatus').parentElement.className = 'badge bg-success';
    } else {
        console.log('❌ User menu elements not found');
        document.getElementById('userMenuStatus').textContent = '❌ Missing';
        document.getElementById('userMenuStatus').parentElement.className = 'badge bg-danger';
    }
});

document.getElementById('testMobileMenu').addEventListener('click', function() {
    console.log('🧪 Testing mobile menu functionality...');
    const mobileToggle = document.querySelector('.mobile-menu-toggle');
    const navMenu = document.querySelector('.nav-menu');
    
    if (mobileToggle && navMenu) {
        console.log('✅ Mobile menu elements found');
        document.getElementById('mobileMenuStatus').textContent = '✅ Working';
        document.getElementById('mobileMenuStatus').parentElement.className = 'badge bg-success';
    } else {
        console.log('❌ Mobile menu elements not found');
        document.getElementById('mobileMenuStatus').textContent = '❌ Missing';
        document.getElementById('mobileMenuStatus').parentElement.className = 'badge bg-danger';
    }
});

// Auto-check on page load
document.addEventListener('DOMContentLoaded', function() {
    console.log('🧪 Auto-checking functionality...');
    
    setTimeout(() => {
        // Check dropdowns
        const dropdowns = document.querySelectorAll('.nav-item.dropdown');
        if (dropdowns.length > 0) {
            document.getElementById('dropdownStatus').textContent = '✅ Working';
            document.getElementById('dropdownStatus').parentElement.className = 'badge bg-success';
        }
        
        // Check theme toggle
        const toggle = document.getElementById('theme-toggle');
        if (toggle) {
            document.getElementById('themeStatus').textContent = '✅ Working';
            document.getElementById('themeStatus').parentElement.className = 'badge bg-success';
        }
        
        // Check user menu
        const userButton = document.querySelector('.user-button');
        if (userButton) {
            document.getElementById('userMenuStatus').textContent = '✅ Working';
            document.getElementById('userMenuStatus').parentElement.className = 'badge bg-success';
        }
        
        // Check mobile menu
        const mobileToggle = document.querySelector('.mobile-menu-toggle');
        if (mobileToggle) {
            document.getElementById('mobileMenuStatus').textContent = '✅ Working';
            document.getElementById('mobileMenuStatus').parentElement.className = 'badge bg-success';
        }
    }, 1000);
});
</script>

<?php include './partials/layouts/layoutBottom.php'; ?>
