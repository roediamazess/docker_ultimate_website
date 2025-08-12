/**
 * Activity Notification Handler
 * Menangani notifikasi untuk operasi activity (add, update)
 * Terintegrasi dengan sistem logo notification
 * 
 * @author PPSolution
 * @version 1.0.0
 */

class ActivityNotificationHandler {
    constructor() {
        this.init();
    }
    
    /**
     * Initialize activity notification system
     */
    init() {
        console.log('Activity Notification Handler initialized');
        
        // Listen for form submissions
        this.setupFormListeners();
        
        // Listen for AJAX responses
        this.setupAjaxListeners();
        
        // Listen for custom events
        this.setupCustomEventListeners();
    }
    
    /**
     * Setup form listeners for activity forms
     */
    setupFormListeners() {
        // Activity Add Form
        const addActivityForm = document.getElementById('addActivityForm');
        if (addActivityForm) {
            addActivityForm.addEventListener('submit', (e) => {
                this.handleFormSubmission(e, 'add');
            });
        }
        
        // Activity Edit Form
        const editActivityForm = document.getElementById('editActivityForm');
        if (editActivityForm) {
            editActivityForm.addEventListener('submit', (e) => {
                this.handleFormSubmission(e, 'update');
            });
        }
        
        
        // Generic activity forms
        document.addEventListener('submit', (e) => {
            const form = e.target;
            if (form.classList.contains('activity-form')) {
                const action = form.dataset.action || 'add';
                this.handleFormSubmission(e, action);
            }
        });
    }
    
    /**
     * Setup AJAX listeners for activity operations
     */
    setupAjaxListeners() {
        // Listen for fetch responses
        const originalFetch = window.fetch;
        window.fetch = async (...args) => {
            try {
                const response = await originalFetch(...args);
                this.handleAjaxResponse(response, args[0]);
                return response;
            } catch (error) {
                console.error('Fetch error:', error);
                return Promise.reject(error);
            }
        };
        
        // Listen for XMLHttpRequest responses
        const originalXHROpen = XMLHttpRequest.prototype.open;
        XMLHttpRequest.prototype.open = function(...args) {
            this.addEventListener('load', function() {
                if (this.responseURL && this.responseURL.includes('activity')) {
                    window.activityNotificationHandler.handleXhrResponse(this);
                }
            });
            return originalXHROpen.apply(this, args);
        };
    }
    
    /**
     * Setup custom event listeners
     */
    setupCustomEventListeners() {
        // Listen for custom activity events
        document.addEventListener('activityCreated', (e) => {
            this.showActivityNotification('created', e.detail);
        });
        
        document.addEventListener('activityUpdated', (e) => {
            this.showActivityNotification('updated', e.detail);
        });
        
        
        document.addEventListener('activityError', (e) => {
            this.showActivityNotification('error', e.detail);
        });
    }
    
    /**
     * Handle form submission
     */
    handleFormSubmission(event, action) {
        const form = event.target;
        const formData = new FormData(form);
        
        // Show loading notification
        this.showLoadingNotification(action);
        
        // Form will be handled by existing logic
        // We just need to show the notification
    }
    
    /**
     * Handle AJAX response
     */
    async handleAjaxResponse(response, request) {
        if (!response.ok) return;
        
        try {
            const data = await response.json();
            this.processResponseData(data, request);
        } catch (error) {
            // Response might not be JSON
            this.processResponseText(await response.text(), request);
        }
    }
    
    /**
     * Handle XHR response
     */
    handleXhrResponse(xhr) {
        try {
            const data = JSON.parse(xhr.responseText);
            this.processResponseData(data, xhr.responseURL);
        } catch (error) {
            this.processResponseText(xhr.responseText, xhr.responseURL);
        }
    }
    
    /**
     * Process response data
     */
    processResponseData(data, request) {
        if (!data) return;
        
        // Check if it's an activity-related response
        if (this.isActivityResponse(data, request)) {
            if (data.success) {
                if (data.action === 'create' || data.action === 'add') {
                    this.showActivityNotification('created', data);
                } else if (data.action === 'update' || data.action === 'edit') {
                    this.showActivityNotification('updated', data);
            } else {
                this.showActivityNotification('error', data);
            }
        }
    }
}
    
    /**
     * Process response text
     */
    processResponseText(text, request) {
        if (!text) return;
        
        // Check for common success/error patterns in text
        if (text.includes('successfully') || text.includes('berhasil')) {
            if (text.includes('created') || text.includes('ditambahkan')) {
                this.showActivityNotification('created', { message: 'Activity created successfully' });
            } else if (text.includes('updated') || text.includes('diperbarui')) {
                this.showActivityNotification('updated', { message: 'Activity updated successfully' });
            }
        } else if (text.includes('error') || text.includes('gagal')) {
            this.showActivityNotification('error', { message: 'Operation failed' });
        }
    }
    
    /**
     * Check if response is activity-related
     */
    isActivityResponse(data, request) {
        if (!data) return false;
        
        // Check request URL
        if (request && typeof request === 'string') {
            if (request.includes('activity') || request.includes('aktivitas')) {
                return true;
            }
        }
        
        // Check response data
        if (data.message && typeof data.message === 'string') {
            const message = data.message.toLowerCase();
            if (message.includes('activity') || message.includes('aktivitas')) {
                return true;
            }
        }
        
        // Check for common activity fields
        if (data.title || data.description || data.date || data.status) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Show activity notification
     */
    showActivityNotification(type, data) {
        if (!window.logoNotificationManager || !window.logoNotificationManager.isAvailable()) {
            console.warn('Logo notification system not available');
            return;
        }
        
        const message = this.getNotificationMessage(type, data);
        const duration = 5000;
        
        switch (type) {
            case 'created':
            case 'add':
                window.logoNotificationManager.showActivityCreated(message, duration);
                break;
            case 'updated':
            case 'update':
                window.logoNotificationManager.showActivityUpdated(message, duration);
                break;
            case 'error':
                window.logoNotificationManager.showActivityError(message, duration);
                break;
            default:
                window.logoNotificationManager.showActivityUpdate(message, duration);
        }
    }
    
    /**
     * Show loading notification
     */
    showLoadingNotification(action) {
        if (!window.logoNotificationManager || !window.logoNotificationManager.isAvailable()) {
            return;
        }
        
        const messages = {
            'add': 'Creating activity...',
            'update': 'Updating activity...'
        };
        
        const message = messages[action] || 'Processing...';
        window.logoNotificationManager.showActivityUpdate(message, 2000);
    }
    
    /**
     * Get notification message
     */
    getNotificationMessage(type, data) {
        if (data && data.message) {
            return data.message;
        }
        
        const defaultMessages = {
            'created': 'Activity created successfully! 🎉',
            'updated': 'Activity updated successfully! ✨',
            'error': 'Operation failed. Please try again! ❌'
        };
        
        return defaultMessages[type] || 'Operation completed!';
    }
    
    /**
     * Manual trigger for activity notifications
     */
    triggerActivityNotification(type, message, data = {}) {
        this.showActivityNotification(type, { message, ...data });
    }
    
    /**
     * Clear current notification
     */
    clearNotification() {
        if (window.logoNotificationManager && window.logoNotificationManager.isAvailable()) {
            window.logoNotificationManager.clear();
        }
    }
}

// Initialize when DOM is ready
let activityNotificationHandler;

document.addEventListener('DOMContentLoaded', function() {
    activityNotificationHandler = new ActivityNotificationHandler();
    
    // Make it globally available
    window.activityNotificationHandler = activityNotificationHandler;
    
    // Global functions for manual triggering
    window.showActivityNotification = (type, message, data) => {
        if (activityNotificationHandler) {
            activityNotificationHandler.triggerActivityNotification(type, message, data);
        }
    };
    
    window.clearActivityNotification = () => {
        if (activityNotificationHandler) {
            activityNotificationHandler.clearNotification();
        }
    };
});

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = ActivityNotificationHandler;
}
