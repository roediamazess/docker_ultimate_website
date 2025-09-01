        </div><!-- content-wrapper end -->
    </main><!-- main-content end -->

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row align-items-center justify-content-between">
                <div class="col-md-4">
                    <!-- Left side - empty for balance -->
                </div>
                <div class="col-md-4 d-flex justify-content-center">
                    <!-- Advanced Theme Toggle in Center -->
                    <div class="theme-toggle-container">
                        <input type="checkbox" id="theme-toggle" class="hidden">
                        <label for="theme-toggle" class="toggle-label">
                            <div class="cloud c1"></div>
                            <div class="cloud c2"></div>
                            <div class="cloud c3"></div>
                            <div class="star s1"></div>
                            <div class="star s2"></div>
                            <div class="star s3"></div>
                            <div class="star s4"></div>
                            <div class="star s5"></div>
                            <div class="toggle-circle">
                                <div class="crater cr1"></div>
                                <div class="crater cr2"></div>
                                <div class="crater cr3"></div>
                            </div>
                        </label>
                    </div>
                </div>
                <div class="col-md-4 text-end">
                    <!-- Version / Copyright on Right -->
                    <p class="mb-0">&copy; 2025 All rights reserved. | v.3.2508.1.0</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Theme Transition Overlay -->
    <div id="theme-transition-overlay"></div>

    <!-- Scripts -->
    <!-- jQuery library js -->
    <script src="assets/js/lib/jquery-3.7.1.min.js"></script>
    <!-- Bootstrap js -->
    <script src="assets/js/lib/bootstrap.bundle.min.js"></script>
    <!-- Apex Chart js -->
    <script src="assets/js/lib/apexcharts.min.js"></script>
    <!-- Data Table js -->
    <script src="assets/js/lib/dataTables.min.js"></script>
    <!-- Iconify Font js -->
    <script src="assets/js/lib/iconify-icon.min.js"></script>
    <!-- jQuery UI js -->
    <script src="assets/js/lib/jquery-ui.min.js"></script>
    <!-- Vector Map js -->
    <script src="assets/js/lib/jquery-jvectormap-2.0.5.min.js"></script>
    <script src="assets/js/lib/jquery-jvectormap-world-mill-en.js"></script>
    <!-- Popup js -->
    <script src="assets/js/lib/magnifc-popup.min.js"></script>
    <!-- Slick Slider js -->
    <script src="assets/js/lib/slick.min.js"></script>
    <!-- prism js -->
    <script src="assets/js/lib/prism.js"></script>
    <!-- file upload js -->
    <script src="assets/js/lib/file-upload.js"></script>
    <!-- audioplayer -->
    <script src="assets/js/lib/audioplayer.js"></script>

    <!-- main js -->
    <script src="assets/js/app.js"></script>
    <script src="assets/js/horizontal-layout.js"></script>
    
    <!-- Additional scripts can be added here -->
    <?php
    if (isset($_SESSION['notification'])) {
        $notification = $_SESSION['notification'];
        $type = htmlspecialchars($notification['type'], ENT_QUOTES, 'UTF-8');
        $message = htmlspecialchars($notification['message'], ENT_QUOTES, 'UTF-8');
        
        // Use a more direct and robust script injection
        echo "<script>\n            document.addEventListener('DOMContentLoaded', function() {\n                if (typeof logoNotificationManager !== 'undefined' && logoNotificationManager.isAvailable()) {\n                    switch ('$type') {\n                        case 'success':\n                            logoNotificationManager.showSuccess('$message');\n                            break;\n                        case 'error':\n                            logoNotificationManager.showError('$message');\n                            break;\n                        case 'info':\n                            logoNotificationManager.showInfo('$message');\n                            break;\n                        case 'warning':\n                            logoNotificationManager.showWarning('$message');\n                            break;\n                        // Keep old types for compatibility
                        case 'created':
                            logoNotificationManager.showSuccess('$message'); // Map 'created' to 'success'
                            break;
                        case 'updated':
                            logoNotificationManager.showInfo('$message'); // Map 'updated' to 'info'
                            break;
                        default:
                            logoNotificationManager.showInfo('$message');\n                    }\n                } else {\n                    // Fallback for critical notifications
                    if ('$type' === 'success' || '$type' === 'error' || '$type' === 'warning') {
                        alert('[Notification] $message');
                    }
                }
            });\n        </script>";
        unset($_SESSION['notification']);
    }
    ?>
    <?php if (isset($script)) echo $script; ?>
    
</body>
</html>
