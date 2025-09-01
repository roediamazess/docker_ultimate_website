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

@include('partials.head')

<style>
/* CSS Variables untuk navbar floating */
:root {
    --nav-height: 70px;
    --nav-offset: 12px;
    --logo-vpad: 10px;
}

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

/* Offset content under fixed navbar */
.main-content { padding-top: calc(var(--nav-height) + var(--nav-offset) + 12px); }
</style>

<body>

@include('partials.layouts.header')

    <!-- Main Content Area -->
    <main class="main-content">
        <div class="content-wrapper">
            @yield('content')
        </div>
    </main>

@include('partials.layouts.footer')

@yield('scripts')

<!-- theme js -->
<script src="{{ asset('assets/js/theme.js') }}"></script>

<script>
            document.addEventListener('DOMContentLoaded', function() {
                // Advanced Theme Toggle functionality in footer
                const themeToggle = document.getElementById('theme-toggle');
                const transitionOverlay = document.getElementById('theme-transition-overlay');
                
                if (themeToggle) {
                    // Initialize theme toggle state
                    function updateToggleState() {
                        const currentTheme = document.documentElement.getAttribute('data-theme');
                        themeToggle.checked = (currentTheme === 'dark');
                    }
                    
                    // Initialize state
                    updateToggleState();
                    
                    // Toggle theme with transition effect
                    themeToggle.addEventListener('change', function() {
                        const newTheme = this.checked ? 'dark' : 'light';
                        
                        // Transition effect
                        if (transitionOverlay) {
                            transitionOverlay.style.width = '200vw';
                            transitionOverlay.style.height = '200vh';
                            transitionOverlay.style.backgroundColor = newTheme === 'dark' ? '#0c1445' : '#f0f9ff';
                            
                            setTimeout(() => {
                                document.documentElement.setAttribute('data-theme', newTheme);
                                localStorage.setItem('theme', newTheme);
                                
                                setTimeout(() => {
                                    transitionOverlay.style.width = '0';
                                    transitionOverlay.style.height = '0';
                                }, 100);
                            }, 400);
                        } else {
                            document.documentElement.setAttribute('data-theme', newTheme);
                            localStorage.setItem('theme', newTheme);
                        }
                    });
                }
    
    // User dropdown functionality
    const userButton = document.querySelector('.user-button');
    const userDropdown = document.querySelector('.user-menu .dropdown-menu');
    
    if (userButton && userDropdown) {
        userButton.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const isVisible = userDropdown.style.display === 'block';
            userDropdown.style.display = isVisible ? 'none' : 'block';
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!userButton.contains(e.target) && !userDropdown.contains(e.target)) {
                userDropdown.style.display = 'none';
            }
        });
    }
    
    // Navigation dropdown functionality
    const dropdownItems = document.querySelectorAll('.nav-item.dropdown');
    dropdownItems.forEach(item => {
        const menu = item.querySelector('.dropdown-menu');
        if (menu) {
            item.addEventListener('mouseenter', function() {
                menu.style.display = 'block';
            });
            
            item.addEventListener('mouseleave', function() {
                menu.style.display = 'none';
            });
        }
    });
});
</script>

</body>
</html>