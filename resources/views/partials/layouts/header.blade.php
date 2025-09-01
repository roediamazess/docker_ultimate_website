    <!-- Horizontal Navigation Bar -->
    <nav class="horizontal-navbar">
        <div class="nav-container">
            <div class="nav-surface">
            <!-- Logo Section -->
            <div class="nav-logo" id="companyLogo">
                <a href="{{ url('/') }}">
                    <img src="{{ asset('assets/images/company/logo.png') }}" alt="PPSolution Logo" style="height: 50px; width: auto; cursor: pointer;" onmouseover="this.style.animation='spin 2s linear infinite'" onmouseout="this.style.animation='none'; this.style.transform='rotate(0deg)'">
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
                            <li><a href="{{ url('/') }}">Dashboard</a></li>
                            <li><a href="{{ url('/index-2.php') }}">CRM</a></li>
                            <li><a href="{{ url('/index-3.php') }}">eCommerce</a></li>
                            <li><a href="{{ url('/index-4.php') }}">Cryptocurrency</a></li>
                            <li><a href="{{ url('/index-5.php') }}">Investment</a></li>
                            <li><a href="{{ url('/index-6.php') }}">LMS</a></li>
                            <li><a href="{{ url('/index-7.php') }}">NFT & Gaming</a></li>
                            <li><a href="{{ url('/index-8.php') }}">Medical</a></li>
                            <li><a href="{{ url('/index-9.php') }}">Analytics</a></li>
                            <li><a href="{{ url('/index-10.php') }}">POS & Inventory</a></li>
                        </ul>
                    </li>

                    <li class="nav-item dropdown" data-debug="dropdown-item">
                        <a href="javascript:void(0)" class="nav-link" data-debug="dropdown-link">
                            <iconify-icon icon="solar:document-text-outline" class="nav-icon"></iconify-icon>
                            <span>Tables</span>
                            <iconify-icon icon="solar:alt-arrow-down-outline" class="dropdown-arrow"></iconify-icon>
                        </a>
                        <ul class="dropdown-menu" data-debug="dropdown-menu">
                            <li><a href="{{ url('/group.php') }}">Hotel Groups</a></li>
                            <li><a href="{{ url('/typography.php') }}">Typography</a></li>
                            <li><a href="{{ url('/colors.php') }}">Colors</a></li>
                            <li><a href="{{ url('/button.php') }}">Button</a></li>
                            <li><a href="{{ url('/dropdown.php') }}">Dropdown</a></li>
                            <li><a href="{{ url('/alert.php') }}">Alerts</a></li>
                            <li><a href="{{ url('/card.php') }}">Card</a></li>
                            <li><a href="{{ url('/carousel.php') }}">Carousel</a></li>
                            <li><a href="{{ url('/avatar.php') }}">Avatars</a></li>
                            <li><a href="{{ url('/progress.php') }}">Progress bar</a></li>
                            <li><a href="{{ url('/tabs.php') }}">Tab & Accordion</a></li>
                            <li><a href="{{ url('/pagination.php') }}">Pagination</a></li>
                            <li><a href="{{ url('/badges.php') }}">Badges</a></li>
                        </ul>
                    </li>

                    @if(in_array(session('user_role'), ['Administrator', 'Management', 'Admin Office']))
                    <li class="nav-item" data-debug="nav-item">
                        <a href="{{ url('/users.php') }}" class="nav-link">
                            <iconify-icon icon="solar:users-group-rounded-outline" class="nav-icon"></iconify-icon>
                            <span>Users</span>
                        </a>
                    </li>
                    @endif

                    <li class="nav-item" data-debug="nav-item">
                        <a href="{{ url('/customer.php') }}" class="nav-link">
                            <iconify-icon icon="solar:users-group-two-rounded-outline" class="nav-icon"></iconify-icon>
                            <span>Customers</span>
                        </a>
                    </li>

                    <li class="nav-item" data-debug="nav-item">
                        <a href="{{ url('/projects.php') }}" class="nav-link">
                            <iconify-icon icon="solar:case-round-outline" class="nav-icon"></iconify-icon>
                            <span>Projects</span>
                        </a>
                    </li>

                    <li class="nav-item" data-debug="nav-item">
                        <a href="{{ url('/activity.php') }}" class="nav-link">
                            <iconify-icon icon="solar:document-add-outline" class="nav-icon"></iconify-icon>
                            <span>Activities</span>
                        </a>
                    </li>

                    <li class="nav-item" data-debug="nav-item">
                        <a href="{{ url('/jobsheet.php') }}" class="nav-link">
                            <iconify-icon icon="solar:clipboard-list-outline" class="nav-icon"></iconify-icon>
                            <span>Jobsheet</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="{{ url('/log_view.php') }}" class="nav-link">
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
                            <iconify-icon icon="solar:user-outline" class="avatar-icon"></iconify-icon>
                        </div>
                        <span class="user-name">{{ session('user_name', 'User') }}</span>
                        <iconify-icon icon="solar:alt-arrow-down-outline" class="dropdown-arrow"></iconify-icon>
                    </button>
                    <ul class="dropdown-menu" data-debug="user-dropdown-menu">
                        <li><a href="{{ url('/view-profile.php') }}">
                            <iconify-icon icon="solar:user-outline" class="me-2"></iconify-icon>
                            Profile
                        </a></li>
                        
                        <li><hr class="dropdown-divider"></li>
                        
                        <li><a href="{{ url('/logout') }}">
                            <iconify-icon icon="solar:logout-2-outline" class="me-2"></iconify-icon>
                            Logout
                        </a></li>
                    </ul>
                </div>

                <!-- Theme toggle removed, now in footer -->
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
        positionNotifications();
        window.addEventListener('resize', positionNotifications, { passive: true });
        window.addEventListener('scroll', positionNotifications, { passive: true });
    })();
    </script>