// Custom JavaScript for Fitness Club Management System
// Enhanced with modern UI interactions and animations

// DOM Ready function
function domReady(callback) {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', callback);
    } else {
        callback();
    }
}

// Initialize enhanced modern UI on load
domReady(function () {
    initializeModernUI();
    initializeFormEnhancements();
    initializePerformanceMetrics();
});

// Modal functionality
class Modal {
    constructor(element) {
        this.modal = element;
        this.backdrop = null;
        this.isShown = false;

        // Bind close button event
        const closeBtn = this.modal.querySelector('.btn-close');
        if (closeBtn) {
            closeBtn.addEventListener('click', () => this.hide());
        }

        // Close on backdrop click
        this.modal.addEventListener('click', (e) => {
            if (e.target === this.modal) {
                this.hide();
            }
        });
    }

    show() {
        if (this.isShown) return;

        this.isShown = true;
        this.modal.style.display = 'flex';

        // Add backdrop
        this.backdrop = document.createElement('div');
        this.backdrop.className = 'modal-backdrop';
        document.body.appendChild(this.backdrop);

        // Prevent body scroll
        document.body.style.overflow = 'hidden';

        // Add show class with slight delay for animation
        setTimeout(() => {
            this.modal.classList.add('show');
        }, 10);

        // Focus first focusable element
        const focusableElements = this.modal.querySelectorAll('input, button, select, textarea, [tabindex]:not([tabindex="-1"])');
        if (focusableElements.length > 0) {
            focusableElements[0].focus();
        }
    }

    hide() {
        if (!this.isShown) return;

        this.isShown = false;
        this.modal.classList.remove('show');

        // Hide modal and remove backdrop
        setTimeout(() => {
            this.modal.style.display = 'none';
            if (this.backdrop) {
                document.body.removeChild(this.backdrop);
                this.backdrop = null;
            }
            // Restore body scroll
            document.body.style.overflow = '';
        }, 150);
    }

    toggle() {
        if (this.isShown) {
            this.hide();
        } else {
            this.show();
        }
    }
}

// Dropdown functionality
class Dropdown {
    constructor(element) {
        this.dropdown = element;
        this.toggle = element.querySelector('.dropdown-toggle');
        this.menu = element.querySelector('.dropdown-menu');
        this.isOpen = false;

        if (this.toggle) {
            this.toggle.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.toggleDropdown();
            });
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            if (!this.dropdown.contains(e.target) && this.isOpen) {
                this.close();
            }
        });

        // Close dropdown on escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.isOpen) {
                this.close();
            }
        });
    }

    toggleDropdown() {
        if (this.isOpen) {
            this.close();
        } else {
            this.open();
        }
    }

    open() {
        this.isOpen = true;
        this.dropdown.classList.add('active');
        this.menu.style.display = 'block';
    }

    close() {
        this.isOpen = false;
        this.dropdown.classList.remove('active');
        this.menu.style.display = 'none';
    }
}

// Alert functionality
class Alert {
    constructor(element) {
        this.alert = element;
        this.closeBtn = element.querySelector('.btn-close');

        if (this.closeBtn) {
            this.closeBtn.addEventListener('click', () => this.close());
        }
    }

    close() {
        this.alert.classList.remove('show');
        this.alert.classList.add('fade');

        setTimeout(() => {
            if (this.alert.parentNode) {
                this.alert.parentNode.removeChild(this.alert);
            }
        }, 150);
    }
}

// Form validation
class FormValidator {
    constructor(form) {
        this.form = form;
        this.errors = {};

        // Add event listeners
        this.form.addEventListener('submit', (e) => {
            if (!this.validate()) {
                e.preventDefault();
            }
        });

        // Real-time validation
        const inputs = this.form.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            input.addEventListener('blur', () => {
                this.validateField(input);
            });
        });
    }

    validate() {
        this.errors = {};
        const inputs = this.form.querySelectorAll('input[required], select[required], textarea[required]');

        inputs.forEach(input => {
            this.validateField(input);
        });

        return Object.keys(this.errors).length === 0;
    }

    validateField(field) {
        const value = field.value.trim();
        const fieldName = field.name || field.id;

        // Remove existing error
        this.removeFieldError(field);

        // Check required fields
        if (field.hasAttribute('required') && !value) {
            this.addFieldError(field, 'This field is required');
            return false;
        }

        // Email validation
        if (field.type === 'email' && value) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(value)) {
                this.addFieldError(field, 'Please enter a valid email address');
                return false;
            }
        }

        // Phone validation (basic)
        if (field.type === 'tel' && value) {
            const phoneRegex = /^[\+]?[\d\s\-\(\)]+$/;
            if (!phoneRegex.test(value)) {
                this.addFieldError(field, 'Please enter a valid phone number');
                return false;
            }
        }

        // Number validation
        if (field.type === 'number' && value) {
            const min = parseFloat(field.getAttribute('min'));
            const max = parseFloat(field.getAttribute('max'));
            const numValue = parseFloat(value);

            if (isNaN(numValue)) {
                this.addFieldError(field, 'Please enter a valid number');
                return false;
            }

            if (!isNaN(min) && numValue < min) {
                this.addFieldError(field, `Value must be at least ${min}`);
                return false;
            }

            if (!isNaN(max) && numValue > max) {
                this.addFieldError(field, `Value must not exceed ${max}`);
                return false;
            }
        }

        return true;
    }

    addFieldError(field, message) {
        const fieldName = field.name || field.id;
        this.errors[fieldName] = message;

        // Add error styling
        field.style.borderColor = '#dc3545';

        // Create error message element
        let errorElement = field.parentNode.querySelector('.field-error');
        if (!errorElement) {
            errorElement = document.createElement('div');
            errorElement.className = 'field-error text-danger';
            errorElement.style.fontSize = '0.875rem';
            errorElement.style.marginTop = '0.25rem';
            field.parentNode.appendChild(errorElement);
        }
        errorElement.textContent = message;
    }

    removeFieldError(field) {
        const fieldName = field.name || field.id;
        delete this.errors[fieldName];

        // Remove error styling
        field.style.borderColor = '';

        // Remove error message
        const errorElement = field.parentNode.querySelector('.field-error');
        if (errorElement) {
            errorElement.parentNode.removeChild(errorElement);
        }
    }
}

// AJAX helper
class AjaxHelper {
    static async request(url, options = {}) {
        const config = {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            ...options
        };

        try {
            const response = await fetch(url, config);
            const data = await response.json();
            return { success: response.ok, data, status: response.status };
        } catch (error) {
            return { success: false, error: error.message };
        }
    }

    static async get(url) {
        return this.request(url);
    }

    static async post(url, data) {
        return this.request(url, {
            method: 'POST',
            body: JSON.stringify(data)
        });
    }

    static async put(url, data) {
        return this.request(url, {
            method: 'PUT',
            body: JSON.stringify(data)
        });
    }

    static async delete(url) {
        return this.request(url, {
            method: 'DELETE'
        });
    }
}

// Notification system
class Notifications {
    static show(message, type = 'info', duration = 5000) {
        const notification = document.createElement('div');
        notification.className = `alert alert-${type} alert-dismissible fade show`;
        notification.style.position = 'fixed';
        notification.style.top = '20px';
        notification.style.right = '20px';
        notification.style.zIndex = '9999';
        notification.style.minWidth = '300px';
        notification.style.maxWidth = '500px';

        notification.innerHTML = `
            <span>${message}</span>
            <button type="button" class="btn-close"></button>
        `;

        document.body.appendChild(notification);

        // Initialize alert
        const alertInstance = new Alert(notification);

        // Auto-hide after duration
        if (duration > 0) {
            setTimeout(() => {
                alertInstance.close();
            }, duration);
        }

        return alertInstance;
    }

    static success(message, duration) {
        return this.show(message, 'success', duration);
    }

    static error(message, duration) {
        return this.show(message, 'danger', duration);
    }

    static warning(message, duration) {
        return this.show(message, 'warning', duration);
    }

    static info(message, duration) {
        return this.show(message, 'info', duration);
    }
}

// Confirm dialog
function confirmAction(message, callback) {
    const confirmed = confirm(message || 'Are you sure you want to perform this action?');
    if (confirmed && typeof callback === 'function') {
        callback();
    }
    return confirmed;
}

// Data attribute API for modals
function initDataAPI() {
    // Modal triggers
    document.addEventListener('click', (e) => {
        const trigger = e.target.closest('[data-bs-toggle="modal"]');
        if (trigger) {
            e.preventDefault();
            const targetSelector = trigger.getAttribute('data-bs-target');
            if (targetSelector) {
                const targetModal = document.querySelector(targetSelector);
                if (targetModal && !targetModal._modalInstance) {
                    targetModal._modalInstance = new Modal(targetModal);
                }
                if (targetModal && targetModal._modalInstance) {
                    targetModal._modalInstance.show();
                }
            }
        }
    });

    // Dropdown triggers
    const dropdowns = document.querySelectorAll('.dropdown');
    dropdowns.forEach(dropdown => {
        if (!dropdown._dropdownInstance) {
            dropdown._dropdownInstance = new Dropdown(dropdown);
        }
    });

    // Alert close buttons
    const alerts = document.querySelectorAll('.alert-dismissible');
    alerts.forEach(alert => {
        if (!alert._alertInstance) {
            alert._alertInstance = new Alert(alert);
        }
    });

    // Form validation
    const forms = document.querySelectorAll('form[data-validate="true"]');
    forms.forEach(form => {
        if (!form._validatorInstance) {
            form._validatorInstance = new FormValidator(form);
        }
    });
}

// Initialize when DOM is ready
domReady(() => {
    initDataAPI();

    // Auto-hide alerts after 5 seconds
    setTimeout(() => {
        const alerts = document.querySelectorAll('.alert-dismissible');
        alerts.forEach(alert => {
            if (alert._alertInstance) {
                alert._alertInstance.close();
            }
        });
    }, 5000);
});

// Global utilities
window.Modal = Modal;
window.Dropdown = Dropdown;
window.Alert = Alert;
window.FormValidator = FormValidator;
window.AjaxHelper = AjaxHelper;
window.Notifications = Notifications;
window.confirmAction = confirmAction;

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        Modal,
        Dropdown,
        Alert,
        FormValidator,
        AjaxHelper,
        Notifications,
        confirmAction,
        initializeModernUI,
        initializeFormEnhancements,
        initializePerformanceMetrics
    };
}

// Modern UI Enhancement Functions
function initializeModernUI() {
    // Add intersection observer for animations
    const observerOptions = {
        root: null,
        rootMargin: '0px',
        threshold: 0.1
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);

    // Observe cards for fade-in animation
    document.querySelectorAll('.card').forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        card.style.transition = `opacity 0.6s ease ${index * 0.1}s, transform 0.6s ease ${index * 0.1}s`;
        observer.observe(card);
    });

    // Add ripple effect to buttons
    document.querySelectorAll('.btn').forEach(btn => {
        btn.addEventListener('click', function (e) {
            if (!this.classList.contains('btn-outline-secondary')) {
                createRipple(e, this);
            }
        });
    });

    // Enhance sidebar navigation
    const navLinks = document.querySelectorAll('.nav-link');
    const currentPath = window.location.pathname;

    navLinks.forEach(link => {
        // Add active state based on current page
        if (link.href && typeof link.href === 'string') {
            const linkSegments = new URL(link.href).pathname.split('/').filter(Boolean);
            const pathSegments = currentPath.split('/').filter(Boolean);
            const linkPage = linkSegments[linkSegments.length - 1];

            if (linkPage && pathSegments.includes(linkPage)) {
                link.classList.add('active');
            }
        }

        // Enhance hover animations
        link.addEventListener('mouseenter', function () {
            this.style.transition = 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
        });
    });

    // Add smooth scrolling
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });
}

function createRipple(event, element) {
    const ripple = document.createElement('span');
    const rect = element.getBoundingClientRect();
    const size = Math.max(rect.width, rect.height);
    const x = event.clientX - rect.left - size / 2;
    const y = event.clientY - rect.top - size / 2;

    ripple.style.cssText = `
        position: absolute;
        width: ${size}px;
        height: ${size}px;
        left: ${x}px;
        top: ${y}px;
        background: rgba(255, 255, 255, 0.3);
        border-radius: 50%;
        transform: scale(0);
        animation: rippleEffect 0.6s ease-out;
        pointer-events: none;
        z-index: 1000;
    `;

    element.style.position = 'relative';
    element.style.overflow = 'hidden';
    element.appendChild(ripple);

    setTimeout(() => {
        if (ripple.parentNode) {
            ripple.remove();
        }
    }, 600);
}

function initializeFormEnhancements() {
    // Enhanced form field interactions
    document.querySelectorAll('.form-control, .form-select').forEach(input => {
        // Add focus enhancement
        input.addEventListener('focus', function () {
            this.style.transform = 'translateY(-1px)';
            this.parentElement.classList.add('focused');
        });

        input.addEventListener('blur', function () {
            this.style.transform = 'translateY(0)';
            this.parentElement.classList.remove('focused');

            // Real-time validation
            if (this.hasAttribute('required') || this.value) {
                validateField(this);
            }
        });

        // Input animation
        input.addEventListener('input', function () {
            if (this.classList.contains('is-invalid')) {
                validateField(this);
            }
        });
    });

    // Enhanced button loading states
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function (e) {
            const submitBtn = this.querySelector('button[type="submit"], input[type="submit"]');
            if (submitBtn && this.checkValidity()) {
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';
                submitBtn.style.opacity = '0.8';
                submitBtn.style.pointerEvents = 'none';

                // Reset button after timeout (fallback)
                setTimeout(() => {
                    submitBtn.innerHTML = originalText;
                    submitBtn.style.opacity = '1';
                    submitBtn.style.pointerEvents = 'auto';
                }, 10000);
            }
        });
    });
}

function validateField(field) {
    const isValid = field.checkValidity();

    field.classList.remove('is-valid', 'is-invalid');

    if (field.value.trim() !== '') {
        field.classList.add(isValid ? 'is-valid' : 'is-invalid');

        // Custom styling
        if (isValid) {
            field.style.borderColor = '#10b981';
            field.style.boxShadow = '0 0 0 4px rgba(16, 185, 129, 0.15)';
        } else {
            field.style.borderColor = '#ef4444';
            field.style.boxShadow = '0 0 0 4px rgba(239, 68, 68, 0.15)';
        }
    } else {
        // Reset styles for empty fields
        field.style.borderColor = '';
        field.style.boxShadow = '';
    }
}

function initializePerformanceMetrics() {
    // Performance monitoring
    window.addEventListener('load', function () {
        if ('performance' in window) {
            const loadTime = performance.now();
            console.log(`%c🏋️ GenzFitness loaded in ${loadTime.toFixed(2)}ms`,
                'color: #1b4332; font-weight: bold; font-size: 14px; text-shadow: 0 1px 2px rgba(0,0,0,0.1);');
        }
    });

    // Add smooth page transition effects
    if ('startViewTransition' in document) {
        // Use View Transitions API if available (modern browsers)
        document.addEventListener('click', function (e) {
            const link = e.target.closest('a[href]');
            if (link && !link.target && link.hostname === window.location.hostname) {
                e.preventDefault();
                document.startViewTransition(() => {
                    window.location.href = link.href;
                });
            }
        });
    }
}

// Enhanced utility functions
window.GenzFitness = {
    showNotification: function (message, type = 'info', duration = 5000) {
        const notification = document.createElement('div');
        notification.className = `alert alert-${type} shadow-strong position-fixed`;
        notification.style.cssText = `
            top: 20px;
            right: 20px;
            z-index: 10000;
            min-width: 320px;
            max-width: 400px;
            opacity: 0;
            transform: translateX(100%);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            border: none;
            backdrop-filter: blur(20px);
        `;

        const iconMap = {
            success: 'check-circle',
            danger: 'exclamation-circle',
            warning: 'exclamation-triangle',
            info: 'info-circle'
        };

        notification.innerHTML = `
            <i class="fas fa-${iconMap[type] || 'info-circle'} me-2"></i>
            <span>${message}</span>
            <button type="button" class="btn-close ms-2" onclick="this.parentElement.remove()"></button>
        `;

        document.body.appendChild(notification);

        // Trigger animation
        requestAnimationFrame(() => {
            notification.style.opacity = '1';
            notification.style.transform = 'translateX(0)';
        });

        // Auto-remove
        setTimeout(() => {
            notification.style.opacity = '0';
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => notification.remove(), 400);
        }, duration);

        return notification;
    },

    confirmAction: function (message, callback, options = {}) {
        const modal = document.createElement('div');
        modal.className = 'modal d-flex align-items-center justify-content-center';
        modal.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(5px);
            z-index: 10001;
            opacity: 0;
            transition: opacity 0.3s ease;
        `;

        const content = document.createElement('div');
        content.className = 'modal-content shadow-strong';
        content.style.cssText = `
            max-width: 400px;
            margin: 1rem;
            border: none;
            border-radius: 20px;
            transform: scale(0.7);
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        `;

        content.innerHTML = `
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title">
                    <i class="fas fa-question-circle text-warning me-2"></i>
                    ${options.title || 'Confirm Action'}
                </h5>
            </div>
            <div class="modal-body">
                <p class="mb-0">${message}</p>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-secondary" data-action="cancel">
                    <i class="fas fa-times me-2"></i>Cancel
                </button>
                <button type="button" class="btn btn-danger" data-action="confirm">
                    <i class="fas fa-check me-2"></i>${options.confirmText || 'Confirm'}
                </button>
            </div>
        `;

        modal.appendChild(content);
        document.body.appendChild(modal);

        // Show modal with animation
        requestAnimationFrame(() => {
            modal.style.opacity = '1';
            content.style.transform = 'scale(1)';
        });

        // Handle button clicks
        content.addEventListener('click', function (e) {
            const action = e.target.closest('[data-action]')?.dataset.action;
            if (action === 'confirm') {
                callback();
            }
            if (action) {
                modal.remove();
            }
        });

        // Close on backdrop click
        modal.addEventListener('click', function (e) {
            if (e.target === modal) {
                modal.remove();
            }
        });
    },

    loadingOverlay: {
        show: function (message = 'Loading...') {
            const overlay = document.createElement('div');
            overlay.id = 'genz-loading-overlay';
            overlay.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(240, 249, 255, 0.95);
                backdrop-filter: blur(10px);
                display: flex;
                justify-content: center;
                align-items: center;
                z-index: 10002;
                opacity: 0;
                transition: opacity 0.3s ease;
            `;

            overlay.innerHTML = `
                <div class="text-center">
                    <div class="spinner-border text-primary mb-3" style="width: 3rem; height: 3rem;"></div>
                    <h5 class="text-primary">${message}</h5>
                </div>
            `;

            document.body.appendChild(overlay);
            requestAnimationFrame(() => overlay.style.opacity = '1');
            return overlay;
        },

        hide: function () {
            const overlay = document.getElementById('genz-loading-overlay');
            if (overlay) {
                overlay.style.opacity = '0';
                setTimeout(() => overlay.remove(), 300);
            }
        }
    }
};

// Add CSS for animations if not present
if (!document.getElementById('genz-animations')) {
    const style = document.createElement('style');
    style.id = 'genz-animations';
    style.textContent = `
        @keyframes rippleEffect {
            0% { transform: scale(0); opacity: 1; }
            100% { transform: scale(2); opacity: 0; }
        }
        
        .form-group.focused .form-label {
            color: #1b4332;
            transform: translateY(-2px);
        }
        
        .hover-glow:hover {
            box-shadow: 0 0 20px rgba(27, 67, 50, 0.3);
        }
    `;
    document.head.appendChild(style);
}