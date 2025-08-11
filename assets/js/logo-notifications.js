/**
 * Logo Notification Manager - Sistem Notifikasi Kapsul
 * Disesuaikan dengan desain Tailwind CSS dan struktur HTML baru
 */

class LogoNotificationManager {
    constructor() {
        this.container = null;
        this.maxNotifications = 5;
        this.notificationStack = [];
        this.init();
    }

    init() {
        // Tunggu DOM siap
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.setupContainer());
        } else {
            this.setupContainer();
        }
    }

    setupContainer() {
        // Cari container yang sudah ada atau buat baru
        this.container = document.getElementById('notification-container');
        
        if (!this.container) {
            this.container = document.createElement('div');
            this.container.id = 'notification-container';
            this.container.style.cssText = `
                position: fixed;
                top: 5rem;
                left: 1.5rem;
                z-index: 1001;
                display: flex;
                flex-direction: column;
                align-items: flex-start;
                gap: 0.75rem;
            `;
            document.body.appendChild(this.container);
        }

        // Tambahkan CSS untuk animasi jika belum ada
        this.addNotificationStyles();
    }

    addNotificationStyles() {
        if (document.getElementById('notification-styles')) return;

        const style = document.createElement('style');
        style.id = 'notification-styles';
        style.textContent = `
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
            /* Info blue color (configurable) */
            :root { --notify-info-color: #90C5D8; }
            .notify-info-progress { background-color: var(--notify-info-color); }
            .notify-info-circle { background-color: var(--notify-info-color); }
        `;
        document.head.appendChild(style);
    }

    showNotification(message, type = 'info', duration = 5000) {
        if (!this.container) {
            console.error('Notification container not found');
            return;
        }

        // Buat elemen notifikasi baru
        const notification = this.createNotificationElement(message, type);
        
        // Tambahkan ke container
        this.container.prepend(notification);
        
        // Tambahkan ke stack
        this.notificationStack.unshift(notification);
        
        // Batasi jumlah notifikasi
        if (this.notificationStack.length > this.maxNotifications) {
            const oldestNotification = this.notificationStack.pop();
            if (oldestNotification && oldestNotification.parentNode) {
                oldestNotification.remove();
            }
        }

        // Set timer untuk auto-hide
        setTimeout(() => {
            this.hideNotification(notification);
        }, duration);

        // Event listener untuk animasi selesai
        notification.addEventListener('animationend', (e) => {
            if (e.animationName === 'fade-out') {
                notification.remove();
                // Hapus dari stack
                const index = this.notificationStack.indexOf(notification);
                if (index > -1) {
                    this.notificationStack.splice(index, 1);
                }
            }
        });

        return notification;
    }

    createNotificationElement(message, type) {
        const notification = document.createElement('div');
        notification.className = 'notification-capsule bg-slate-800 text-white rounded-full shadow-2xl flex items-center p-2';

        // Tentukan ikon dan warna berdasarkan tipe
        let icon, progressColor;
        switch (type) {
            case 'danger':
                progressColor = 'bg-red-500';
                icon = `<div class="w-8 h-8 rounded-full bg-red-500 flex-shrink-0 flex items-center justify-center"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg></div>`;
                break;
            case 'info':
                progressColor = 'notify-info-progress';
                icon = `<div class="w-8 h-8 rounded-full notify-info-circle flex-shrink-0 flex items-center justify-center"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 13l3 3 7-7" /><path stroke-linecap="round" stroke-linejoin="round" d="M5 11l3 3 5-5" /></svg></div>`;
                break;
            case 'warning':
                progressColor = 'bg-orange-400';
                icon = `<div class="w-8 h-8 rounded-full bg-orange-500 flex-shrink-0 flex items-center justify-center"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" /></svg></div>`;
                break;
            default: // success
                progressColor = 'bg-green-400';
                icon = `<div class="w-8 h-8 rounded-full bg-green-500 flex-shrink-0 flex items-center justify-center"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg></div>`;
        }

        // Isi konten notifikasi
        notification.innerHTML = `
            ${icon}
            <div class="flex flex-col px-3 flex-grow">
                <p class="text-sm font-medium leading-tight">${message}</p>
                <div class="progress-line ${progressColor} mt-1 rounded-full"></div>
            </div>
        `;

        return notification;
    }

    hideNotification(notification) {
        if (notification && notification.classList) {
            notification.classList.add('hide');
        }
    }

    // Method khusus untuk aktivitas
    showActivityCreated(message = 'Activity berhasil dibuat!', duration = 5000) {
        return this.showNotification(message, 'success', duration);
    }

    showActivityUpdated(message = 'Activity berhasil diperbarui!', duration = 5000) {
        return this.showNotification(message, 'info', duration);
    }

    showActivityCanceled(message = 'Activity dibatalkan!', duration = 5000) {
        return this.showNotification(message, 'warning', duration);
    }


    showActivityError(message = 'Terjadi kesalahan!', duration = 5000) {
        return this.showNotification(message, 'danger', duration);
    }

    // Method untuk notifikasi umum
    showSuccess(message, duration = 5000) {
        return this.showNotification(message, 'success', duration);
    }

    showInfo(message, duration = 5000) {
        return this.showNotification(message, 'info', duration);
    }

    showWarning(message, duration = 5000) {
        return this.showNotification(message, 'warning', duration);
    }

    showError(message, duration = 5000) {
        return this.showNotification(message, 'danger', duration);
    }

    // Hapus semua notifikasi
    clearAll() {
        if (this.container) {
            this.container.innerHTML = '';
            this.notificationStack = [];
        }
    }

    // Hapus notifikasi tertentu
    removeNotification(notification) {
        if (notification && notification.parentNode) {
            notification.remove();
            const index = this.notificationStack.indexOf(notification);
            if (index > -1) {
                this.notificationStack.splice(index, 1);
            }
        }
    }

    // Aliases & utilities
    clear() {
        this.clearAll();
    }

    isAvailable() {
        return !!this.container;
    }
}

// Inisialisasi global instance
window.logoNotificationManager = new LogoNotificationManager();

// Export untuk penggunaan module (jika diperlukan)
if (typeof module !== 'undefined' && module.exports) {
    module.exports = LogoNotificationManager;
}
