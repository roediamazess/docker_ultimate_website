/**
 * Activity Table Enhancement Script
 * Provides enhanced sorting and filtering functionality
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize activity table enhancements
    initActivityTable();
    
    // Initialize filter form enhancements
    initFilterForm();

    // Auto-calc Due Date on Create modal
    const infoInput = document.getElementById('create_information_date');
    const typeSelect = document.getElementById('create_type');
    const dueInput = document.getElementById('create_due_date');

    function computeOffsetByType(type) {
        switch (type) {
            case 'Issue': return 0;
            case 'Setup': return 2;
            case 'Question': return 1;
            case 'Report Issue': return 2;
            case 'Report Request': return 7;
            case 'Feature Request': return 30;
            default: return 0;
        }
    }

    function formatISO(date) {
        const y = date.getFullYear();
        const m = String(date.getMonth() + 1).padStart(2, '0');
        const d = String(date.getDate()).padStart(2, '0');
        return `${y}-${m}-${d}`;
    }

    function recalcDueDate() {
        if (!infoInput || !dueInput || !typeSelect) return;
        const infoVal = infoInput.value;
        const typeVal = typeSelect.value;
        if (!infoVal) return;
        const base = new Date(infoVal);
        if (isNaN(base.getTime())) return;
        const offset = computeOffsetByType(typeVal);
        const due = new Date(base);
        due.setDate(due.getDate() + offset);
        const min = formatISO(base);
        dueInput.min = min;

        const userEdited = dueInput.dataset.userEdited === 'true';
        // Jika belum diedit manual, set otomatis nilai due date
        if (!userEdited) {
            dueInput.value = formatISO(due);
        }
        // Tetap paksa minimal: due >= information_date
        if (dueInput.value) {
            const chosen = new Date(dueInput.value);
            if (chosen < base) {
                dueInput.value = min;
            }
        }
    }

    if (infoInput && typeSelect && dueInput) {
        // Inisialisasi pertama kali
        recalcDueDate();
        infoInput.addEventListener('change', recalcDueDate);
        typeSelect.addEventListener('change', recalcDueDate);
        dueInput.addEventListener('change', function() {
            if (!infoInput.value || !dueInput.value) return;
            const base = new Date(infoInput.value);
            const chosen = new Date(dueInput.value);
            if (chosen < base) {
                dueInput.value = formatISO(base);
            }
        });
        // Menandai bahwa user telah mengubah due date secara manual
        dueInput.addEventListener('input', function() {
            this.dataset.userEdited = 'true';
        });
    }
});

/**
 * Initialize activity table enhancements
 */
function initActivityTable() {
    const table = document.querySelector('.sortable-table');
    if (!table) return;
    
    // Add hover effects to table rows
    const tableRows = table.querySelectorAll('tbody tr');
    tableRows.forEach(row => {
        row.addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.01)';
            this.style.transition = 'all 0.3s ease';
        });
        
        row.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1)';
        });
    });
    
    // Add click effects to sortable headers
    const sortableHeaders = table.querySelectorAll('.sortable-header');
    sortableHeaders.forEach(header => {
        header.addEventListener('click', function(e) {
            // Add click animation
            this.style.transform = 'scale(0.95)';
            setTimeout(() => {
                this.style.transform = 'scale(1)';
            }, 150);
            
            // Update visual feedback for sorting
            updateSortingVisuals(this);
        });
    });
    
    // Initialize sorting visuals
    initSortingVisuals();
}

/**
 * Initialize sorting visuals based on current URL parameters
 */
function initSortingVisuals() {
    const urlParams = new URLSearchParams(window.location.search);
    const currentSort = urlParams.get('sort');
    const currentOrder = urlParams.get('order');
    
    if (currentSort) {
        const header = document.querySelector(`[href*="sort=${currentSort}"]`);
        if (header) {
            updateSortingVisuals(header, currentOrder);
        }
    }
}

/**
 * Update sorting visuals for a specific header
 */
function updateSortingVisuals(header, order = null) {
    // Remove all sorting classes first
    const allHeaders = document.querySelectorAll('.sortable-header');
    allHeaders.forEach(h => {
        h.classList.remove('sort-asc', 'sort-desc');
        // Hide all icons by default
        const icon = h.querySelector('.sort-icon');
        if (icon) {
            icon.style.display = 'none';
        }
    });
    
    // Get current order from URL if not provided
    if (!order) {
        const urlParams = new URLSearchParams(window.location.search);
        order = urlParams.get('order') || 'asc';
    }
    
    // Add appropriate sorting class
    if (order === 'desc') {
        header.classList.add('sort-desc');
    } else {
        header.classList.add('sort-asc');
    }
    
    // Update icon display for active sorting header
    const icon = header.querySelector('.sort-icon');
    if (icon) {
        icon.style.display = 'inline-block';
    }
    
    // Add hover effect to show icon on all headers
    addHoverEffectsToHeaders();
}

/**
 * Add hover effects to show sorting icons on hover
 */
function addHoverEffectsToHeaders() {
    const allHeaders = document.querySelectorAll('.sortable-header');
    
    allHeaders.forEach(header => {
        const icon = header.querySelector('.sort-icon');
        if (!icon) return;
        
        // Show icon on hover
        header.addEventListener('mouseenter', function() {
            if (!this.classList.contains('sort-asc') && !this.classList.contains('sort-desc')) {
                icon.style.display = 'inline-block';
                icon.style.opacity = '0.6';
            }
        });
        
        // Hide icon on mouse leave (unless it's the active sorting column)
        header.addEventListener('mouseleave', function() {
            if (!this.classList.contains('sort-asc') && !this.classList.contains('sort-desc')) {
                icon.style.display = 'none';
                icon.style.opacity = '1';
            }
        });
    });
}

/**
 * Initialize filter form enhancements
 */
function initFilterForm() {
    const filterForm = document.querySelector('.filter-form');
    if (!filterForm) return;
    
    // Add real-time search functionality
    const searchInput = filterForm.querySelector('input[name="search"]');
    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                // Auto-submit form after 500ms of no typing
                filterForm.submit();
            }, 500);
        });
    }
    
    // Add filter change auto-submit for select elements
    const filterSelects = filterForm.querySelectorAll('select[name^="filter_"]');
    filterSelects.forEach(select => {
        select.addEventListener('change', function() {
            // Add visual feedback
            this.style.borderColor = '#10b981';
            this.style.boxShadow = '0 0 0 3px rgba(16, 185, 129, 0.1)';
            
            setTimeout(() => {
                this.style.borderColor = '';
                this.style.boxShadow = '';
            }, 1000);
            
            // Auto-submit form
            filterForm.submit();
        });
    });
    
    // Add button animations
    const applyBtn = filterForm.querySelector('.btn-apply');
    if (applyBtn) {
        applyBtn.addEventListener('click', function(e) {
            // Add click animation
            this.style.transform = 'scale(0.95)';
            setTimeout(() => {
                this.style.transform = 'scale(1)';
            }, 150);
        });
    }
    
    const resetBtn = filterForm.querySelector('.btn-reset');
    if (resetBtn) {
        resetBtn.addEventListener('click', function(e) {
            // Add click animation
            this.style.transform = 'scale(0.95)';
            setTimeout(() => {
                this.style.transform = 'scale(1)';
            }, 150);
        });
    }
}

/**
 * Show create activity form with animation
 */
function showCreateForm() {
    const form = document.getElementById('createActivityForm');
    if (form) {
        form.style.display = 'block';
        form.style.opacity = '0';
        form.style.transform = 'translateY(-20px)';
        
        // Animate in
        setTimeout(() => {
            form.style.transition = 'all 0.3s ease';
            form.style.opacity = '1';
            form.style.transform = 'translateY(0)';
        }, 10);
    }
}

/**
 * Hide create activity form with animation
 */
function hideCreateForm() {
    const form = document.getElementById('createActivityForm');
    if (form) {
        form.style.transition = 'all 0.3s ease';
        form.style.opacity = '0';
        form.style.transform = 'translateY(-20px)';
        
        // Hide after animation
        setTimeout(() => {
            form.style.display = 'none';
        }, 300);
    }
}

/**
 * Show edit activity form with animation
 */
function showEditForm(activityId) {
    const form = document.getElementById('editActivityForm' + activityId);
    if (form) {
        form.style.display = 'block';
        form.style.opacity = '0';
        form.style.transform = 'translateY(-20px)';
        
        // Animate in
        setTimeout(() => {
            form.style.transition = 'all 0.3s ease';
            form.style.opacity = '1';
            form.style.transform = 'translateY(0)';
        }, 10);
    }
}

/**
 * Hide edit activity form with animation
 */
function hideEditForm(activityId) {
    const form = document.getElementById('editActivityForm' + activityId);
    if (form) {
        form.style.transition = 'all 0.3s ease';
        form.style.opacity = '0';
        form.style.transform = 'translateY(-20px)';
        
        // Hide after animation
        setTimeout(() => {
            form.style.display = 'none';
        }, 300);
    }
}


/**
 * Add loading state to buttons
 */
function addLoadingState(button) {
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="ri-loader-4-line ri-spin"></i> Loading...';
    button.disabled = true;
    
    return function() {
        button.innerHTML = originalText;
        button.disabled = false;
    };
}

/**
 * Show notification message
 */
function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} notification-toast`;
    notification.innerHTML = message;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        min-width: 300px;
        animation: slideInRight 0.3s ease;
    `;
    
    // Add to page
    document.body.appendChild(notification);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        notification.style.animation = 'slideOutRight 0.3s ease';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }, 5000);
}

// Add CSS animations
const style = document.createElement('style');
style.textContent = `
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
    
    @keyframes slideOutRight {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
    
    .notification-toast {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        border: none;
        border-radius: 8px;
    }
`;
document.head.appendChild(style);
