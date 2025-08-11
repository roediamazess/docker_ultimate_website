<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simple Notification Test</title>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background: #f0f2f5;
        }
        
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
        }
        
        .button-group {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            justify-content: center;
            margin-bottom: 30px;
        }
        
        .btn {
            padding: 12px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-success { background: #10b981; color: white; }
        .btn-info { background: #3b82f6; color: white; }
        .btn-warning { background: #f59e0b; color: white; }
        .btn-error { background: #ef4444; color: white; }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }
        
        /* Notification Styles */
        .notification {
            position: fixed;
            padding: 15px 20px;
            border-radius: 8px;
            color: white;
            font-weight: 500;
            z-index: 10000;
            max-width: 300px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .notification.success { background: #10b981; }
        .notification.info { background: #3b82f6; }
        .notification.warning { background: #f59e0b; }
        .notification.error { background: #ef4444; }
        
        /* Position Classes */
        .notification.top-left {
            top: 20px;
            left: 20px;
            animation: slideInLeft 0.5s ease;
        }
        
        .notification.top-right {
            top: 20px;
            right: 20px;
            animation: slideInRight 0.5s ease;
        }
        
        .notification.bottom-left {
            bottom: 20px;
            left: 20px;
            animation: slideInLeft 0.5s ease;
        }
        
        .notification.bottom-right {
            bottom: 20px;
            right: 20px;
            animation: slideInRight 0.5s ease;
        }
        
        .notification.top-center {
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            animation: slideInTop 0.5s ease;
        }
        
        .notification.bottom-center {
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            animation: slideInBottom 0.5s ease;
        }
        
        /* Animations */
        @keyframes slideInLeft {
            from {
                transform: translateX(-100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        @keyframes slideInTop {
            from {
                transform: translateX(-50%) translateY(-100%);
                opacity: 0;
            }
            to {
                transform: translateX(-50%) translateY(0);
                opacity: 1;
            }
        }
        
        @keyframes slideInBottom {
            from {
                transform: translateX(-50%) translateY(100%);
                opacity: 0;
            }
            to {
                transform: translateX(-50%) translateY(0);
                opacity: 1;
            }
        }
        
        .notification.hide {
            animation: fadeOut 0.5s ease forwards;
        }
        
        @keyframes fadeOut {
            to {
                opacity: 0;
                transform: scale(0.8);
            }
        }
        
        /* Position Controls */
        .position-controls {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        
        .position-controls h3 {
            margin-top: 0;
            margin-bottom: 15px;
            color: #333;
            text-align: center;
        }
        
        .position-buttons {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            justify-content: center;
        }
        
        .pos-btn {
            padding: 8px 16px;
            border: 2px solid #dee2e6;
            background: white;
            border-radius: 6px;
            cursor: pointer;
            font-size: 12px;
            transition: all 0.2s ease;
        }
        
        .pos-btn:hover {
            border-color: #3b82f6;
            background: #f8fafc;
        }
        
        .pos-btn.active {
            background: #3b82f6;
            color: white;
            border-color: #3b82f6;
        }
        
        .instructions {
            background: #e3f2fd;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #2196f3;
        }
        
        .instructions h3 {
            margin-top: 0;
            color: #1976d2;
        }
        
        .instructions ol {
            margin: 0;
            padding-left: 20px;
        }
        
        .instructions li {
            margin-bottom: 8px;
            color: #1565c0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔔 Test Notifikasi Sederhana</h1>
        
        <!-- Position Controls -->
        <div class="position-controls">
            <h3>📍 Pilih Posisi Notifikasi:</h3>
            <div class="position-buttons">
                <button class="pos-btn active" onclick="setPosition('top-left')">Kiri Atas</button>
                <button class="pos-btn" onclick="setPosition('top-center')">Tengah Atas</button>
                <button class="pos-btn" onclick="setPosition('top-right')">Kanan Atas</button>
                <button class="pos-btn" onclick="setPosition('bottom-left')">Kiri Bawah</button>
                <button class="pos-btn" onclick="setPosition('bottom-center')">Tengah Bawah</button>
                <button class="pos-btn" onclick="setPosition('bottom-right')">Kanan Bawah</button>
            </div>
        </div>
        
        <!-- Test Buttons -->
        <div class="button-group">
            <button class="btn btn-success" onclick="showNotification('success')">
                <i class="ri-check-line"></i>
                Success
            </button>
            <button class="btn btn-info" onclick="showNotification('info')">
                <i class="ri-information-line"></i>
                Info
            </button>
            <button class="btn btn-warning" onclick="showNotification('warning')">
                <i class="ri-error-warning-line"></i>
                Warning
            </button>
            <button class="btn btn-error" onclick="showNotification('error')">
                <i class="ri-close-circle-line"></i>
                Error
            </button>
        </div>
        
        <!-- Instructions -->
        <div class="instructions">
            <h3>📋 Cara Penggunaan:</h3>
            <ol>
                <li>Pilih posisi notifikasi yang diinginkan</li>
                <li>Klik salah satu tombol notifikasi</li>
                <li>Notifikasi akan muncul dengan animasi slide</li>
                <li>Notifikasi otomatis hilang setelah 3 detik</li>
                <li>Setiap posisi memiliki animasi yang berbeda</li>
            </ol>
        </div>
    </div>
    
    <script>
        let currentPosition = 'top-left';
        let currentNotification = null;
        
        function setPosition(position) {
            currentPosition = position;
            
            // Update active button
            document.querySelectorAll('.pos-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            event.target.classList.add('active');
            
            console.log(`📍 Position changed to: ${position}`);
        }
        
        function showNotification(type) {
            // Remove existing notification
            if (currentNotification) {
                currentNotification.remove();
                currentNotification = null;
            }
            
            // Create notification element
            const notification = document.createElement('div');
            notification.className = `notification ${type} ${currentPosition}`;
            
            // Set content based on type
            let icon, message;
            switch(type) {
                case 'success':
                    icon = 'ri-check-line';
                    message = 'Operasi berhasil diselesaikan!';
                    break;
                case 'info':
                    icon = 'ri-information-line';
                    message = 'Ini adalah informasi penting.';
                    break;
                case 'warning':
                    icon = 'ri-error-warning-line';
                    message = 'Perhatian! Ada yang perlu diperhatikan.';
                    break;
                case 'error':
                    icon = 'ri-close-circle-line';
                    message = 'Terjadi kesalahan! Silakan coba lagi.';
                    break;
            }
            
            notification.innerHTML = `
                <i class="${icon}" style="font-size: 18px;"></i>
                <span>${message}</span>
            `;
            
            // Add to body
            document.body.appendChild(notification);
            currentNotification = notification;
            
            // Auto-hide after 3 seconds
            setTimeout(() => {
                hideNotification();
            }, 3000);
            
            console.log(`🎉 ${type} notification shown at ${currentPosition}`);
        }
        
        function hideNotification() {
            if (currentNotification) {
                currentNotification.classList.add('hide');
                setTimeout(() => {
                    if (currentNotification && currentNotification.parentNode) {
                        currentNotification.parentNode.removeChild(currentNotification);
                    }
                    currentNotification = null;
                }, 500);
            }
        }
        
        // Hide notification on click (optional)
        document.addEventListener('click', function(e) {
            if (e.target.closest('.notification')) {
                hideNotification();
            }
        });
    </script>
</body>
</html>
