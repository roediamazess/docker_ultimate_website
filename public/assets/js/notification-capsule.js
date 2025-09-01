/**
 * Notification Capsule System
 * Desain kapsul yang muncul dari logo dengan animasi yang smooth
 */

class NotificationCapsule {
    constructor() {
        this.container = null;
        this.init();
    }

    init() {
        // Buat container jika belum ada
        if (!document.getElementById('notification-container')) {
            this.createContainer();
        }
        this.container = document.getElementById('notification-container');
    }

    createContainer() {
        const container = document.createElement('div');
        container.id = 'notification-container';
        document.body.appendChild(container);
    }

    /**
     * Tampilkan notifikasi kapsul
     * @param {string} message - Pesan notifikasi
     * @param {string} type - Tipe notifikasi: 'success', 'info', 'danger', 'warning'
     * @param {number} duration - Durasi tampil dalam milidetik (default: 5000ms)
     */
    show(message, type = 'info', duration = 5000) {
        const notif = this.createNotificationElement(message, type);
        
        // Tambahkan ke container
        this.container.prepend(notif);
        
        // Set timer untuk menghilangkan notifikasi
        setTimeout(() => {
            this.hide(notif);
        }, duration);

        return notif;
    }

    /**
     * Buat elemen notifikasi
     */
    createNotificationElement(message, type) {
        const notif = document.createElement('div');
        notif.className = 'notification-capsule bg-slate-800 text-white rounded-full shadow-2xl flex items-center p-2';
        
        // Tentukan ikon dan warna berdasarkan tipe
        const { icon, progressColor } = this.getTypeConfig(type);
        
        // Set innerHTML
        notif.innerHTML = `
            ${icon}
            <div class="flex flex-col px-3 flex-grow">
                <p class="text-sm font-medium leading-tight">${message}</p>
                <div class="progress-line ${progressColor} mt-1 rounded-full"></div>
            </div>
        `;
        
        // Event listener untuk animasi
        notif.addEventListener('animationend', (e) => {
            if (e.animationName === 'fade-out') {
                notif.remove();
            }
        });
        
        return notif;
    }

    /**
     * Konfigurasi tipe notifikasi
     */
    getTypeConfig(type) {
        const configs = {
            'success': {
                progressColor: 'bg-green-400',
                icon: `<div class="w-8 h-8 rounded-full bg-green-500 flex-shrink-0 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                </div>`
            },
            'info': {
                progressColor: 'bg-blue-400',
                icon: `<div class="w-8 h-8 rounded-full bg-blue-500 flex-shrink-0 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>`
            },
            'warning': {
                progressColor: 'bg-yellow-400',
                icon: `<div class="w-8 h-8 rounded-full bg-yellow-500 flex-shrink-0 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z" />
                    </svg>
                </div>`
            },
            'danger': {
                progressColor: 'bg-red-400',
                icon: `<div class="w-8 h-8 rounded-full bg-red-500 flex-shrink-0 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </div>`
            }
        };
        
        return configs[type] || configs['info'];
    }

    /**
     * Hilangkan notifikasi dengan animasi
     */
    hide(notification) {
        if (notification && notification.classList) {
            notification.classList.add('hide');
        }
    }

    /**
     * Hilangkan semua notifikasi
     */
    clearAll() {
        if (this.container) {
            const notifications = this.container.querySelectorAll('.notification-capsule');
            notifications.forEach(notif => this.hide(notif));
        }
    }

    /**
     * Notifikasi untuk aktivitas
     */
    showActivityNotification(action, data = {}) {
        const messages = {
            'created': 'Aktivitas berhasil dibuat! 🎉',
            'updated': 'Aktivitas telah diperbarui! ✏️',
            'cancelled': 'Aktivitas telah dibatalkan! 🚫',
            'deleted': 'Aktivitas telah dihapus! 🗑️'
        };

        const types = {
            'created': 'success',
            'updated': 'info',
            'cancelled': 'warning',
            'deleted': 'danger'
        };

        const message = data.message || messages[action] || 'Aktivitas berhasil diproses!';
        const type = types[action] || 'info';

        return this.show(message, type);
    }
}

// Inisialisasi global
window.NotificationCapsule = new NotificationCapsule();

// Fungsi helper global untuk backward compatibility
window.showNotification = (message, type = 'info', duration = 5000) => {
    return window.NotificationCapsule.show(message, type, duration);
};

window.showActivityNotification = (action, data = {}) => {
    return window.NotificationCapsule.showActivityNotification(action, data);
};
