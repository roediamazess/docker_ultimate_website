// Horizontal Layout JavaScript - Simple & Direct Approach
console.log('🚀 Loading horizontal layout JavaScript...');

// Wait for DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
    console.log('📋 DOM loaded, initializing...');
    
    // Initialize everything after a short delay
    setTimeout(initializeAll, 200);
});

function initializeAll() {
    console.log('🔧 Starting initialization...');
    
    // Initialize main navigation dropdowns
    initializeMainDropdowns();
    
    // Initialize mobile menu
    initializeMobileMenu();
    
    // Initialize right side buttons
    initializeRightSideButtons();
    
    // Initialize simple theme toggle
    initializeSimpleThemeToggle();
    
    console.log('✅ All initialization complete');
}

// Simple Theme Toggle
function initializeSimpleThemeToggle() {
    console.log('🌙 Initializing simple theme toggle...');
    
    const themeToggle = document.getElementById('themeToggle');
    const themeIcon = document.getElementById('themeIcon');
    
    if (themeToggle && themeIcon) {
        // Set initial theme
        const savedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);
        updateThemeIcon(savedTheme);
        
        // Add click event
        themeToggle.addEventListener('click', function() {
            console.log('🌙 Theme toggle clicked');
            
            const currentTheme = document.documentElement.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            
            document.documentElement.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            updateThemeIcon(newTheme);
        });
        
        console.log('✅ Simple theme toggle initialized');
    } else {
        console.log('❌ Theme toggle elements not found');
    }
}

function updateThemeIcon(theme) {
    const themeIcon = document.getElementById('themeIcon');
    if (themeIcon) {
        if (theme === 'dark') {
            themeIcon.textContent = '🌙';
        } else {
            themeIcon.textContent = '☀️';
        }
    }
}

// Main navigation dropdowns
function initializeMainDropdowns() {
    console.log('🔽 Initializing main dropdowns...');
    
    const dropdownItems = document.querySelectorAll('.nav-item.dropdown');
    console.log(`Found ${dropdownItems.length} dropdown items`);
    
    dropdownItems.forEach((item, index) => {
        const link = item.querySelector('.nav-link');
        const dropdown = item.querySelector('.dropdown-menu');
        
        if (link && dropdown) {
            console.log(`Setting up dropdown ${index + 1}: ${link.textContent.trim()}`);
            
            // Remove any existing event listeners
            const newLink = link.cloneNode(true);
            link.parentNode.replaceChild(newLink, link);
            
            // Variables for hover delay
            let hoverTimeout;
            let isDropdownOpen = false;
            
            // Add click event for mobile
            newLink.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                console.log(`📱 Clicked dropdown: ${this.textContent.trim()}`);
                
                // Close all other dropdowns
                dropdownItems.forEach(otherItem => {
                    if (otherItem !== item) {
                        otherItem.classList.remove('dropdown-open', 'mobile-open');
                    }
                });
                
                // Toggle current dropdown
                item.classList.toggle('dropdown-open');
                item.classList.toggle('mobile-open');
                isDropdownOpen = item.classList.contains('dropdown-open');
            });
            
            // Add hover events for desktop
            item.addEventListener('mouseenter', function() {
                if (window.innerWidth > 992) {
                    console.log(`🖥️ Hover on: ${newLink.textContent.trim()}`);
                    
                    // Clear any existing timeout
                    clearTimeout(hoverTimeout);
                    
                    // Open dropdown immediately
                    item.classList.add('dropdown-open');
                    isDropdownOpen = true;
                }
            });
            
            item.addEventListener('mouseleave', function(e) {
                if (window.innerWidth > 992) {
                    console.log(`🖥️ Leave: ${newLink.textContent.trim()}`);
                    
                    // Add delay before closing dropdown
                    hoverTimeout = setTimeout(() => {
                        // Check if mouse is still over the dropdown area
                        const rect = item.getBoundingClientRect();
                        const mouseX = e.clientX;
                        const mouseY = e.clientY;
                        
                        // If mouse is within the dropdown area, don't close
                        if (mouseX >= rect.left && mouseX <= rect.right && 
                            mouseY >= rect.top && mouseY <= rect.bottom + 200) {
                            console.log('🖱️ Mouse still in dropdown area, keeping open');
                            return;
                        }
                        
                        item.classList.remove('dropdown-open');
                        isDropdownOpen = false;
                    }, 150); // 150ms delay
                }
            });
            
            // Add hover events for dropdown menu itself
            dropdown.addEventListener('mouseenter', function() {
                if (window.innerWidth > 992) {
                    console.log(`🖥️ Hover on dropdown menu: ${newLink.textContent.trim()}`);
                    clearTimeout(hoverTimeout);
                    item.classList.add('dropdown-open');
                    isDropdownOpen = true;
                }
            });
            
            dropdown.addEventListener('mouseleave', function(e) {
                if (window.innerWidth > 992) {
                    console.log(`🖥️ Leave dropdown menu: ${newLink.textContent.trim()}`);
                    
                    // Add delay before closing dropdown
                    hoverTimeout = setTimeout(() => {
                        // Check if mouse is still over the nav item area
                        const rect = item.getBoundingClientRect();
                        const mouseX = e.clientX;
                        const mouseY = e.clientY;
                        
                        // If mouse is within the nav item area, don't close
                        if (mouseX >= rect.left && mouseX <= rect.right && 
                            mouseY >= rect.top && mouseY <= rect.bottom) {
                            console.log('🖱️ Mouse still in nav item area, keeping open');
                            return;
                        }
                        
                        item.classList.remove('dropdown-open');
                        isDropdownOpen = false;
                    }, 150); // 150ms delay
                }
            });
        }
    });
}

// Mobile menu toggle
function initializeMobileMenu() {
    console.log('📱 Initializing mobile menu...');
    
    const mobileToggle = document.querySelector('.mobile-menu-toggle');
    const navMenu = document.querySelector('.nav-menu');
    
    if (mobileToggle && navMenu) {
        // Remove existing listeners
        const newToggle = mobileToggle.cloneNode(true);
        mobileToggle.parentNode.replaceChild(newToggle, mobileToggle);
        
        newToggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            console.log('📱 Mobile menu clicked');
            
            navMenu.classList.toggle('mobile-active');
            newToggle.classList.toggle('active');
        });
    }
}

// Right side buttons (user menu)
function initializeRightSideButtons() {
    console.log('🔧 Initializing right side buttons...');
    
    // User menu
    const userButton = document.querySelector('.user-button');
    const userDropdown = document.querySelector('.user-menu .dropdown-menu');
    
    if (userButton && userDropdown) {
        // Remove existing listeners
        const newUserButton = userButton.cloneNode(true);
        userButton.parentNode.replaceChild(newUserButton, userButton);
        
        newUserButton.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            console.log('👤 User menu clicked');
            
            // Close other dropdowns
            document.querySelectorAll('.nav-item.dropdown').forEach(item => {
                item.classList.remove('dropdown-open', 'mobile-open');
            });
            
            userDropdown.classList.toggle('show');
        });
    }
}

// Global click handler to close dropdowns when clicking outside
document.addEventListener('click', function(e) {
    // Don't close if clicking on dropdown elements
    if (e.target.closest('.nav-item.dropdown') || 
        e.target.closest('.user-menu') || 
        e.target.closest('.mobile-menu-toggle') ||
        e.target.closest('#themeToggle')) {
        return;
    }
    
    // Close all dropdowns
    document.querySelectorAll('.nav-item.dropdown').forEach(item => {
        item.classList.remove('dropdown-open', 'mobile-open');
    });
    
    // Close user dropdown
    const userDropdown = document.querySelector('.user-menu .dropdown-menu');
    if (userDropdown) {
        userDropdown.classList.remove('show');
    }
    
    // Close mobile menu
    const navMenu = document.querySelector('.nav-menu');
    const mobileToggle = document.querySelector('.mobile-menu-toggle');
    if (navMenu && mobileToggle) {
        navMenu.classList.remove('mobile-active');
        mobileToggle.classList.remove('active');
    }
});

// Handle window resize
window.addEventListener('resize', function() {
    if (window.innerWidth > 992) {
        // Close mobile menu on desktop
        const navMenu = document.querySelector('.nav-menu');
        const mobileToggle = document.querySelector('.mobile-menu-toggle');
        if (navMenu && mobileToggle) {
            navMenu.classList.remove('mobile-active');
            mobileToggle.classList.remove('active');
        }
        
        // Close mobile dropdowns
        document.querySelectorAll('.nav-item.dropdown').forEach(item => {
            item.classList.remove('mobile-open');
        });
    }
});

// Keyboard navigation
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        // Close all dropdowns
        document.querySelectorAll('.nav-item.dropdown').forEach(item => {
            item.classList.remove('dropdown-open', 'mobile-open');
        });
        
        // Close user dropdown
        const userDropdown = document.querySelector('.user-menu .dropdown-menu');
        if (userDropdown) {
            userDropdown.classList.remove('show');
        }
        
        // Close mobile menu
        const navMenu = document.querySelector('.nav-menu');
        const mobileToggle = document.querySelector('.mobile-menu-toggle');
        if (navMenu && mobileToggle) {
            navMenu.classList.remove('mobile-active');
            mobileToggle.classList.remove('active');
        }
    }
});

console.log('📜 Horizontal layout JavaScript loaded successfully');
