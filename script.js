/**
 * Task Manager - Frontend JavaScript
 * Handles UI interactions, form validation, and AJAX requests
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize all components
    initSidebar();
    initModals();
    initForms();
    initTaskFilters();
    initConfirmDialogs();
    initFlashMessages();
});

/**
 * Sidebar toggle functionality
 */
function initSidebar() {
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('collapsed');
        });
    }
    
    if (mobileMenuBtn && sidebar) {
        mobileMenuBtn.addEventListener('click', function() {
            sidebar.classList.add('open');
            if (sidebarOverlay) {
                sidebarOverlay.classList.add('active');
            }
        });
    }
    
    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', function() {
            if (sidebar) {
                sidebar.classList.remove('open');
            }
            sidebarOverlay.classList.remove('active');
        });
    }
    
    // Close sidebar on link click (mobile)
    if (sidebar) {
        const navLinks = sidebar.querySelectorAll('a');
        navLinks.forEach(link => {
            link.addEventListener('click', function() {
                if (window.innerWidth <= 768) {
                    sidebar.classList.remove('open');
                    if (sidebarOverlay) {
                        sidebarOverlay.classList.remove('active');
                    }
                }
            });
        });
    }
    
    // Handle window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth > 768) {
            if (sidebar) sidebar.classList.remove('open');
            if (sidebarOverlay) sidebarOverlay.classList.remove('active');
        }
    });
}

/**
 * Modal functionality
 */
function initModals() {
    const modals = document.querySelectorAll('.modal-overlay');
    
    modals.forEach(modal => {
        // Close on overlay click
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeModal(modal);
            }
        });
        
        // Close on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && modal.classList.contains('active')) {
                closeModal(modal);
            }
        });
        
        // Close button
        const closeBtn = modal.querySelector('.modal-close');
        if (closeBtn) {
            closeBtn.addEventListener('click', function() {
                closeModal(modal);
            });
        }
        
        // Cancel button
        const cancelBtn = modal.querySelector('[data-modal-cancel]');
        if (cancelBtn) {
            cancelBtn.addEventListener('click', function() {
                closeModal(modal);
            });
        }
    });
}

function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
        
        // Focus first input
        const firstInput = modal.querySelector('input, select, textarea');
        if (firstInput) {
            setTimeout(() => firstInput.focus(), 100);
        }
    }
}

function closeModal(modal) {
    modal.classList.remove('active');
    document.body.style.overflow = '';
}

/**
 * Form validation and submission
 */
function initForms() {
    const forms = document.querySelectorAll('form[data-validate]');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateForm(form)) {
                e.preventDefault();
            }
        });
        
        // Real-time validation
        const inputs = form.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            input.addEventListener('blur', function() {
                validateField(input);
            });
            
            input.addEventListener('input', function() {
                clearFieldError(input);
            });
        });
    });
}

function validateForm(form) {
    let isValid = true;
    const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
    
    inputs.forEach(input => {
        if (!validateField(input)) {
            isValid = false;
        }
    });
    
    // Special validation for password confirmation
    const password = form.querySelector('input[name="password"]');
    const confirmPassword = form.querySelector('input[name="confirm_password"]');
    
    if (password && confirmPassword && password.value !== confirmPassword.value) {
        showFieldError(confirmPassword, 'Passwords do not match');
        isValid = false;
    }
    
    // Email format validation
    const email = form.querySelector('input[type="email"]');
    if (email && email.value && !isValidEmail(email.value)) {
        showFieldError(email, 'Please enter a valid email address');
        isValid = false;
    }
    
    return isValid;
}

function validateField(field) {
    const value = field.value.trim();
    let isValid = true;
    
    // Required field
    if (field.hasAttribute('required') && !value) {
        showFieldError(field, 'This field is required');
        isValid = false;
    }
    
    // Email validation
    if (field.type === 'email' && value && !isValidEmail(value)) {
        showFieldError(field, 'Please enter a valid email address');
        isValid = false;
    }
    
    // Min length validation
    const minLength = field.getAttribute('minlength');
    if (minLength && value.length < parseInt(minLength)) {
        showFieldError(field, `Must be at least ${minLength} characters`);
        isValid = false;
    }
    
    // Max length validation
    const maxLength = field.getAttribute('maxlength');
    if (maxLength && value.length > parseInt(maxLength)) {
        showFieldError(field, `Must be no more than ${maxLength} characters`);
        isValid = false;
    }
    
    if (isValid) {
        clearFieldError(field);
    }
    
    return isValid;
}

function showFieldError(field, message) {
    clearFieldError(field);
    
    field.classList.add('error');
    
    const errorDiv = document.createElement('div');
    errorDiv.className = 'form-error';
    errorDiv.textContent = message;
    errorDiv.id = field.id + '-error';
    
    field.parentNode.appendChild(errorDiv);
}

function clearFieldError(field) {
    field.classList.remove('error');
    
    const errorDiv = document.getElementById(field.id + '-error');
    if (errorDiv) {
        errorDiv.remove();
    }
}

function isValidEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

/**
 * Task filtering and search
 */
function initTaskFilters() {
    const filterForm = document.getElementById('taskFilterForm');
    const searchInput = document.getElementById('taskSearch');
    const statusFilter = document.getElementById('statusFilter');
    const priorityFilter = document.getElementById('priorityFilter');
    const sortFilter = document.getElementById('sortFilter');
    
    if (!filterForm && !searchInput) return;
    
    // Debounced search
    let searchTimeout;
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                applyFilters();
            }, 300);
        });
    }
    
    // Instant filter changes
    [statusFilter, priorityFilter, sortFilter].forEach(filter => {
        if (filter) {
            filter.addEventListener('change', applyFilters);
        }
    });
    
    // Form submit
    if (filterForm) {
        filterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            applyFilters();
        });
    }
    
    function applyFilters() {
        const params = new URLSearchParams();
        
        if (searchInput && searchInput.value.trim()) {
            params.set('search', searchInput.value.trim());
        }
        
        if (statusFilter && statusFilter.value) {
            params.set('status', statusFilter.value);
        }
        
        if (priorityFilter && priorityFilter.value) {
            params.set('priority', priorityFilter.value);
        }
        
        if (sortFilter && sortFilter.value) {
            params.set('sort', sortFilter.value);
        }
        
        // Update URL without reload
        const newUrl = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
        window.history.replaceState({}, '', newUrl);
        
        // If we have a task list container, we could fetch via AJAX
        // For now, we'll just reload the page to keep it simple
        // window.location.href = newUrl;
    }
}

/**
 * Confirm dialogs for delete actions
 */
function initConfirmDialogs() {
    document.addEventListener('click', function(e) {
        const deleteBtn = e.target.closest('[data-confirm-delete]');
        if (deleteBtn) {
            e.preventDefault();
            
            const message = deleteBtn.getAttribute('data-confirm-message') || 'Are you sure you want to delete this item?';
            const url = deleteBtn.getAttribute('href') || deleteBtn.getAttribute('data-url');
            
            if (confirm(message)) {
                if (deleteBtn.tagName === 'A' && url) {
                    window.location.href = url;
                } else if (deleteBtn.tagName === 'BUTTON' && url) {
                    // Submit form or make AJAX request
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = url;
                    form.style.display = 'none';
                    document.body.appendChild(form);
                    form.submit();
                }
            }
        }
    });
}

/**
 * Flash message auto-dismiss
 */
function initFlashMessages() {
    const flashMessages = document.querySelectorAll('.flash-message');
    
    flashMessages.forEach(message => {
        // Auto dismiss after 5 seconds
        setTimeout(() => {
            message.style.opacity = '0';
            message.style.transform = 'translateY(-10px)';
            message.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
            
            setTimeout(() => {
                message.remove();
            }, 300);
        }, 5000);
        
        // Allow manual dismiss
        message.addEventListener('click', function() {
            this.style.opacity = '0';
            this.style.transform = 'translateY(-10px)';
            this.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
            
            setTimeout(() => {
                this.remove();
            }, 300);
        });
    });
}

/**
 * Utility: Show loading state on buttons
 */
function setButtonLoading(button, loading) {
    if (loading) {
        button.dataset.originalText = button.innerHTML;
        button.innerHTML = '<svg class="spinner" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10" stroke-opacity="0.25"></circle><path d="M12 2a10 10 0 0 1 10 10" stroke-linecap="round"></path></svg> Loading...';
        button.disabled = true;
    } else {
        button.innerHTML = button.dataset.originalText || button.innerHTML;
        button.disabled = false;
        delete button.dataset.originalText;
    }
}

/**
 * Utility: Format date for display
 */
function formatDate(dateString) {
    if (!dateString) return 'Not set';
    
    const date = new Date(dateString);
    if (isNaN(date.getTime())) return 'Invalid date';
    
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

/**
 * Utility: Format datetime for display
 */
function formatDateTime(dateString) {
    if (!dateString) return 'Not set';
    
    const date = new Date(dateString);
    if (isNaN(date.getTime())) return 'Invalid date';
    
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

/**
 * Utility: Get priority badge class
 */
function getPriorityClass(priority) {
    switch (priority) {
        case 'High': return 'priority-high';
        case 'Medium': return 'priority-medium';
        case 'Low': return 'priority-low';
        default: return '';
    }
}

/**
 * Utility: Get status badge class
 */
function getStatusClass(status) {
    switch (status) {
        case 'Completed': return 'status-completed';
        case 'In Progress': return 'status-in-progress';
        case 'Pending': return 'status-pending';
        default: return 'status-pending';
    }
}

/**
 * Utility: Check if task is overdue
 */
function isTaskOverdue(dueDate, status) {
    if (!dueDate || status === 'Completed') return false;
    
    const due = new Date(dueDate);
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    
    return due < today;
}

/**
 * AJAX helper for form submissions
 */
async function submitFormAjax(form, url) {
    const formData = new FormData(form);
    const submitBtn = form.querySelector('button[type="submit"]');
    
    try {
        setButtonLoading(submitBtn, true);
        
        const response = await fetch(url, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        const data = await response.json();
        
        setButtonLoading(submitBtn, false);
        
        if (data.success) {
            showFlashMessage(data.message || 'Success', 'success');
            
            if (data.redirect) {
                setTimeout(() => {
                    window.location.href = data.redirect;
                }, 1000);
            }
            
            return data;
        } else {
            showFlashMessage(data.message || 'An error occurred', 'error');
            return data;
        }
    } catch (error) {
        setButtonLoading(submitBtn, false);
        showFlashMessage('An unexpected error occurred', 'error');
        console.error('Form submission error:', error);
    }
}

/**
 * Show flash message
 */
function showFlashMessage(message, type = 'info') {
    const container = document.querySelector('.content-wrapper') || document.querySelector('.auth-container') || document.body;
    
    const flashDiv = document.createElement('div');
    flashDiv.className = `flash-message ${type}`;
    flashDiv.textContent = message;
    
    container.insertBefore(flashDiv, container.firstChild);
    
    // Auto dismiss
    setTimeout(() => {
        flashDiv.style.opacity = '0';
        flashDiv.style.transform = 'translateY(-10px)';
        flashDiv.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
        
        setTimeout(() => {
            flashDiv.remove();
        }, 300);
    }, 5000);
}

/**
 * Initialize tooltips (simple version)
 */
function initTooltips() {
    const tooltips = document.querySelectorAll('[data-tooltip]');
    
    tooltips.forEach(el => {
        el.addEventListener('mouseenter', function() {
            const tooltip = document.createElement('div');
            tooltip.className = 'tooltip';
            tooltip.textContent = this.getAttribute('data-tooltip');
            tooltip.style.cssText = `
                position: absolute;
                background: var(--dark-color);
                color: var(--white);
                padding: 0.375rem 0.625rem;
                border-radius: var(--radius-sm);
                font-size: 0.75rem;
                z-index: 1000;
                pointer-events: none;
                white-space: nowrap;
            `;
            
            document.body.appendChild(tooltip);
            
            const rect = this.getBoundingClientRect();
            tooltip.style.left = rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2) + 'px';
            tooltip.style.top = rect.top - tooltip.offsetHeight - 8 + 'px';
            
            this._tooltip = tooltip;
        });
        
        el.addEventListener('mouseleave', function() {
            if (this._tooltip) {
                this._tooltip.remove();
                this._tooltip = null;
            }
        });
    });
}

// Export functions for global access
window.TaskManager = {
    openModal,
    closeModal,
    setButtonLoading,
    showFlashMessage,
    formatDate,
    formatDateTime,
    getPriorityClass,
    getStatusClass,
    isTaskOverdue,
    submitFormAjax
};