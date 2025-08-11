/**
 * Modern Notification System for Ultimate Website
 * Based on test_notification_new.html
 */

class NotificationManager {
    constructor() {
        this.container = null;
        this.init();
    }

    init() {
        this.createContainer();
    }

    createContainer() {
        if (!document.getElementById('notification-container')) {
            const container = document.createElement('div');
            container.id = 'notification-container';
            container.className = 'fixed top-20 left-4 z-50 flex flex-col gap-3';
            document.body.appendChild(container);
            this.container = container;
        }
    }

    show(options) {
        const { type = 'info', title = '', message = '', duration = 4500 } = options;
        
        const notification = document.createElement('div');
        notification.className = 'bg-white rounded-lg shadow-lg border-l-4 p-4 min-w-[300px] max-w-[400px]';
        
        const colors = {
            success: 'border-green-500',
            error: 'border-red-500',
            warning: 'border-yellow-500',
            info: 'border-blue-500'
        };
        
        notification.className += ` ${colors[type] || colors.info}`;
        
        notification.innerHTML = `
            <div class="flex items-start">
                <div class="ml-3 flex-1">
                    ${title ? `<h3 class="text-sm font-medium">${title}</h3>` : ''}
                    <p class="text-sm text-gray-600">${message}</p>
                </div>
            </div>
        `;
        
        this.container.insertBefore(notification, this.container.firstChild);
        
        setTimeout(() => {
            notification.remove();
        }, duration);
        
        return notification;
    }

    // Quick methods for common notifications
    showSuccess(message, title = 'Success') {
        this.show({ type: 'success', title, message });
    }

    showError(message, title = 'Error') {
        this.show({ type: 'error', title, message });
    }

    showInfo(message, title = 'Info') {
        this.show({ type: 'info', title, message });
    }

    showWarning(message, title = 'Warning') {
        this.show({ type: 'warning', title, message });
    }
}

// Initialize globally
window.notificationManager = new NotificationManager();
