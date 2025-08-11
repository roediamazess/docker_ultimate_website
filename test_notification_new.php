<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Toast Notifications</title>
    
    <!-- Remix Icon -->
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background: #f5f5f5;
            min-height: 100vh;
        }
        
        .test-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .test-header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .test-header h1 {
            color: #333;
            margin-bottom: 10px;
        }
        
        .test-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
            margin-bottom: 40px;
        }
        
        .test-btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .test-btn.success {
            background: #10b981;
            color: white;
        }
        
        .test-btn.info {
            background: #3b82f6;
            color: white;
        }
        
        .test-btn.warning {
            background: #f59e0b;
            color: white;
        }
        
        .test-btn.error {
            background: #ef4444;
            color: white;
        }
        
        .test-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        /* Toast Notification Styles */
        .toast-container {
            position: fixed;
            z-index: 9999;
            pointer-events: none;
        }

        .toast-container.top-left {
            top: 20px;
            left: 20px;
        }

        .toast-container.top-right {
            top: 20px;
            right: 20px;
        }

        .toast-container.bottom-left {
            bottom: 20px;
            left: 20px;
        }

        .toast-container.bottom-right {
            bottom: 20px;
            right: 20px;
        }

        .toast-container.top-center {
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
        }

        .toast-container.bottom-center {
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
        }

        .toast {
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            margin-bottom: 10px;
            padding: 16px 20px;
            min-width: 300px;
            max-width: 400px;
            pointer-events: auto;
            position: relative;
            overflow: hidden;
            border-left: 4px solid #3b82f6;
            animation: slideIn 0.3s ease forwards;
        }

        .toast.success {
            border-left-color: #10b981;
        }

        .toast.info {
            border-left-color: #3b82f6;
        }

        .toast.warning {
            border-left-color: #f59e0b;
        }

        .toast.error {
            border-left-color: #ef4444;
        }

        .toast-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 8px;
        }

        .toast-title {
            font-weight: 600;
            font-size: 14px;
            color: #333;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .toast-close {
            background: none;
            border: none;
            cursor: pointer;
            padding: 4px;
            border-radius: 4px;
            color: #666;
            transition: all 0.2s ease;
        }

        .toast-close:hover {
            background: #f3f4f6;
            color: #333;
        }

        .toast-message {
            color: #666;
            font-size: 14px;
            line-height: 1.4;
        }

        .toast-progress {
            position: absolute;
            bottom: 0;
            left: 0;
            height: 3px;
            background: #e5e7eb;
            width: 100%;
        }

        .toast-progress-bar {
            height: 100%;
            background: #3b82f6;
            width: 100%;
            transition: width linear;
        }

        .toast.success .toast-progress-bar {
            background: #10b981;
        }

        .toast.info .toast-progress-bar {
            background: #3b82f6;
        }

        .toast.warning .toast-progress-bar {
            background: #f59e0b;
        }

        .toast.error .toast-progress-bar {
            background: #ef4444;
        }

        /* Animation Classes */
        .toast.slide-out {
            animation: slideOut 0.3s ease forwards;
        }

        /* Animation Keyframes */
        @keyframes slideIn {
            from {
                transform: translateX(-100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes slideOut {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(-100%);
                opacity: 0;
            }
        }

        /* Position Controls */
        .position-controls {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .position-controls h3 {
            margin-top: 0;
            margin-bottom: 15px;
            color: #333;
        }

        .position-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            justify-content: center;
        }

        .position-btn {
            padding: 8px 16px;
            border: 2px solid #e5e7eb;
            background: white;
            border-radius: 6px;
            cursor: pointer;
            font-size: 12px;
            transition: all 0.2s ease;
        }

        .position-btn:hover {
            border-color: #3b82f6;
            background: #f8fafc;
        }

        .position-btn.active {
            background: #3b82f6;
            color: white;
            border-color: #3b82f6;
        }
    </style>
</head>
<body>
    <div class="test-container">
        <div class="test-header">
            <h1>🧪 Test Toast Notifications</h1>
            <p>Klik tombol di bawah untuk menguji notifikasi toast dengan berbagai posisi</p>
        </div>
        
        <!-- Position Controls -->
        <div class="position-controls">
            <h3>📍 Pilih Posisi Notifikasi:</h3>
            <div class="position-buttons">
                <button class="position-btn active" onclick="setPosition('top-left')">Kiri Atas</button>
                <button class="position-btn" onclick="setPosition('top-center')">Tengah Atas</button>
                <button class="position-btn" onclick="setPosition('top-right')">Kanan Atas</button>
                <button class="position-btn" onclick="setPosition('bottom-left')">Kiri Bawah</button>
                <button class="position-btn" onclick="setPosition('bottom-center')">Tengah Bawah</button>
                <button class="position-btn" onclick="setPosition('bottom-right')">Kanan Bawah</button>
            </div>
        </div>
        
        <div class="test-buttons">
            <button class="test-btn success" onclick="showSuccessNotification()">
                <i class="ri-check-line"></i>
                Success
            </button>
            <button class="test-btn info" onclick="showInfoNotification()">
                <i class="ri-information-line"></i>
                Info
            </button>
            <button class="test-btn warning" onclick="showWarningNotification()">
                <i class="ri-error-warning-line"></i>
                Warning
            </button>
            <button class="test-btn error" onclick="showErrorNotification()">
                <i class="ri-close-circle-line"></i>
                Error
            </button>
        </div>
        
        <div style="margin-top: 40px; padding: 20px; background: white; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
            <h3>📋 Instruksi Testing:</h3>
            <ol>
                <li>Pilih posisi notifikasi yang diinginkan (kiri atas, tengah, kanan, dll)</li>
                <li>Klik salah satu tombol notifikasi untuk testing manual</li>
                <li>Notifikasi akan muncul dengan animasi slide dari kiri</li>
                <li>Klik tombol "X" untuk menutup notifikasi</li>
                <li>Notifikasi akan otomatis hilang setelah 5 detik</li>
                <li>Progress bar menunjukkan waktu tersisa</li>
            </ol>
        </div>
    </div>
    
    <script>
        class ToastNotificationManager {
            constructor() {
                this.currentPosition = 'top-left';
                this.notifications = new Map();
                this.createContainer();
            }
            
            createContainer() {
                // Remove existing containers
                const existingContainers = document.querySelectorAll('.toast-container');
                existingContainers.forEach(container => container.remove());
                
                // Create new container
                this.container = document.createElement('div');
                this.container.className = `toast-container ${this.currentPosition}`;
                document.body.appendChild(this.container);
            }
            
            setPosition(position) {
                this.currentPosition = position;
                this.createContainer();
                console.log(`📍 Position changed to: ${position}`);
            }
            
            showNotification(message, type = 'info', duration = 5000, icon = null) {
                if (!this.container) {
                    this.createContainer();
                }
                
                // Default icon if none provided
                if (!icon) {
                    switch (type) {
                        case 'success':
                            icon = 'ri-check-line';
                            break;
                        case 'info':
                            icon = 'ri-information-line';
                            break;
                        case 'warning':
                            icon = 'ri-error-warning-line';
                            break;
                        case 'error':
                            icon = 'ri-close-circle-line';
                            break;
                        default:
                            icon = 'ri-information-line';
                    }
                }
                
                // Get title based on type
                let title = 'Notifikasi';
                switch (type) {
                    case 'success':
                        title = 'Berhasil!';
                        break;
                    case 'info':
                        title = 'Info';
                        break;
                    case 'warning':
                        title = 'Peringatan';
                        break;
                    case 'error':
                        title = 'Error';
                        break;
                }
                
                // Create toast element
                const toast = this.createToastElement(title, message, type, icon);
                
                // Add to container
                this.container.appendChild(toast);
                
                // Store notification reference
                const notificationId = Date.now() + Math.random();
                this.notifications.set(notificationId, toast);
                
                // Start progress bar
                this.startProgressBar(toast, duration, notificationId);
                
                // Auto-hide after duration
                if (duration > 0) {
                    setTimeout(() => {
                        this.hideNotification(notificationId);
                    }, duration);
                }
                
                console.log(`🎉 Toast notification shown: ${message} (${type}) at ${this.currentPosition}`);
                return notificationId;
            }
            
            createToastElement(title, message, type, icon) {
                const toast = document.createElement('div');
                toast.className = `toast ${type}`;
                
                toast.innerHTML = `
                    <div class="toast-header">
                        <div class="toast-title">
                            <i class="${icon}"></i>
                            ${title}
                        </div>
                        <button class="toast-close" onclick="toastManager.hideNotificationByElement(this.closest('.toast'))">
                            <i class="ri-close-line"></i>
                        </button>
                    </div>
                    <div class="toast-message">${message}</div>
                    <div class="toast-progress">
                        <div class="toast-progress-bar"></div>
                    </div>
                `;
                
                return toast;
            }
            
            startProgressBar(toast, duration, notificationId) {
                const progressBar = toast.querySelector('.toast-progress-bar');
                if (progressBar) {
                    progressBar.style.transition = `width ${duration}ms linear`;
                    setTimeout(() => {
                        progressBar.style.width = '0%';
                    }, 100);
                }
            }
            
            hideNotification(notificationId) {
                const toast = this.notifications.get(notificationId);
                if (toast) {
                    this.hideToastElement(toast);
                    this.notifications.delete(notificationId);
                }
            }
            
            hideNotificationByElement(toastElement) {
                if (toastElement) {
                    this.hideToastElement(toastElement);
                    // Remove from notifications map
                    for (const [id, toast] of this.notifications.entries()) {
                        if (toast === toastElement) {
                            this.notifications.delete(id);
                            break;
                        }
                    }
                }
            }
            
            hideToastElement(toast) {
                // Add exit animation
                toast.classList.add('slide-out');
                
                // Remove after animation
                setTimeout(() => {
                    if (toast && toast.parentNode) {
                        toast.parentNode.removeChild(toast);
                    }
                }, 300);
            }
            
            clearAll() {
                this.notifications.forEach((toast, id) => {
                    this.hideToastElement(toast);
                });
                this.notifications.clear();
            }
        }
        
        // Initialize toast manager
        let toastManager;
        
        document.addEventListener('DOMContentLoaded', function() {
            toastManager = new ToastNotificationManager();
        });
        
        // Position control function
        function setPosition(position) {
            if (toastManager) {
                toastManager.setPosition(position);
                
                // Update active button
                document.querySelectorAll('.position-btn').forEach(btn => {
                    btn.classList.remove('active');
                });
                event.target.classList.add('active');
            }
        }
        
        // Test functions
        function showSuccessNotification() {
            if (toastManager) {
                toastManager.showNotification('Activity berhasil ditambahkan!', 'success', 5000, 'ri-add-line');
            }
        }
        
        function showInfoNotification() {
            if (toastManager) {
                toastManager.showNotification('Activity berhasil diperbarui!', 'info', 5000, 'ri-refresh-line');
            }
        }
        
        function showWarningNotification() {
            if (toastManager) {
                toastManager.showNotification('Activity berhasil dihapus!', 'warning', 5000, 'ri-delete-bin-line');
            }
        }
        
        function showErrorNotification() {
            if (toastManager) {
                toastManager.showNotification('Terjadi kesalahan saat memproses activity!', 'error', 5000, 'ri-error-warning-line');
            }
        }
    </script>
</body>
</html>
