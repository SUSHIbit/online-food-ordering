/**
 * Main JavaScript File
 * Online Food Ordering System - Phase 1
 * 
 * Contains basic functionality for the application
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize all components
    initMobileNavigation();
    initFormValidation();
    initModalHandlers();
    initAlertHandlers();
    initPasswordToggle();
    initConfirmDialogs();
});

/**
 * Mobile Navigation Toggle
 */
function initMobileNavigation() {
    const mobileToggle = document.querySelector('.mobile-nav-toggle');
    const nav = document.querySelector('.nav');
    
    if (mobileToggle && nav) {
        mobileToggle.addEventListener('click', function() {
            nav.classList.toggle('active');
            
            // Change icon based on state
            const icon = mobileToggle.querySelector('i') || mobileToggle;
            if (nav.classList.contains('active')) {
                icon.innerHTML = '✕';
            } else {
                icon.innerHTML = '☰';
            }
        });
        
        // Close menu when clicking on nav links
        const navLinks = document.querySelectorAll('.nav-link');
        navLinks.forEach(link => {
            link.addEventListener('click', function() {
                nav.classList.remove('active');
                const icon = mobileToggle.querySelector('i') || mobileToggle;
                icon.innerHTML = '☰';
            });
        });
    }
}

/**
 * Form Validation
 */
function initFormValidation() {
    const forms = document.querySelectorAll('form[data-validate]');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateForm(form)) {
                e.preventDefault();
            }
        });
        
        // Real-time validation
        const inputs = form.querySelectorAll('input, textarea, select');
        inputs.forEach(input => {
            input.addEventListener('blur', function() {
                validateField(input);
            });
            
            input.addEventListener('input', function() {
                // Remove error styling on input
                clearFieldError(input);
            });
        });
    });
}

/**
 * Validate entire form
 * @param {HTMLFormElement} form Form element
 * @returns {boolean} True if valid, false otherwise
 */
function validateForm(form) {
    let isValid = true;
    const inputs = form.querySelectorAll('input[required], textarea[required], select[required]');
    
    inputs.forEach(input => {
        if (!validateField(input)) {
            isValid = false;
        }
    });
    
    return isValid;
}

/**
 * Validate individual field
 * @param {HTMLElement} field Form field element
 * @returns {boolean} True if valid, false otherwise
 */
function validateField(field) {
    const value = field.value.trim();
    const type = field.type;
    const required = field.hasAttribute('required');
    
    // Clear previous errors
    clearFieldError(field);
    
    // Required field validation
    if (required && !value) {
        showFieldError(field, 'This field is required');
        return false;
    }
    
    // Skip further validation if field is empty and not required
    if (!value && !required) {
        return true;
    }
    
    // Email validation
    if (type === 'email' || field.name === 'email') {
        if (!isValidEmail(value)) {
            showFieldError(field, 'Please enter a valid email address');
            return false;
        }
    }
    
    // Password validation
    if (type === 'password' || field.name === 'password') {
        if (value.length < 6) {
            showFieldError(field, 'Password must be at least 6 characters');
            return false;
        }
    }
    
    // Phone validation
    if (field.name === 'phone' || field.type === 'tel') {
        if (!isValidPhone(value)) {
            showFieldError(field, 'Please enter a valid phone number');
            return false;
        }
    }
    
    // Confirm password validation
    if (field.name === 'confirm_password') {
        const passwordField = document.querySelector('input[name="password"]');
        if (passwordField && value !== passwordField.value) {
            showFieldError(field, 'Passwords do not match');
            return false;
        }
    }
    
    return true;
}

/**
 * Show field error
 * @param {HTMLElement} field Form field
 * @param {string} message Error message
 */
function showFieldError(field, message) {
    field.classList.add('error');
    
    // Remove existing error message
    const existingError = field.parentNode.querySelector('.field-error');
    if (existingError) {
        existingError.remove();
    }
    
    // Add error message
    const errorDiv = document.createElement('div');
    errorDiv.className = 'field-error';
    errorDiv.style.color = '#ef4444';
    errorDiv.style.fontSize = '0.875rem';
    errorDiv.style.marginTop = '0.25rem';
    errorDiv.textContent = message;
    
    field.parentNode.appendChild(errorDiv);
}

/**
 * Clear field error
 * @param {HTMLElement} field Form field
 */
function clearFieldError(field) {
    field.classList.remove('error');
    
    const errorMessage = field.parentNode.querySelector('.field-error');
    if (errorMessage) {
        errorMessage.remove();
    }
}

/**
 * Validate email format
 * @param {string} email Email address
 * @returns {boolean} True if valid, false otherwise
 */
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

/**
 * Validate phone number
 * @param {string} phone Phone number
 * @returns {boolean} True if valid, false otherwise
 */
function isValidPhone(phone) {
    const phoneRegex = /^[0-9+\-\s()]{10,20}$/;
    return phoneRegex.test(phone);
}

/**
 * Modal Handlers
 */
function initModalHandlers() {
    // Modal triggers
    const modalTriggers = document.querySelectorAll('[data-modal]');
    modalTriggers.forEach(trigger => {
        trigger.addEventListener('click', function(e) {
            e.preventDefault();
            const modalId = trigger.getAttribute('data-modal');
            openModal(modalId);
        });
    });
    
    // Modal close buttons
    const closeButtons = document.querySelectorAll('.modal-close, [data-modal-close]');
    closeButtons.forEach(button => {
        button.addEventListener('click', function() {
            const modal = button.closest('.modal');
            if (modal) {
                closeModal(modal.id);
            }
        });
    });
    
    // Close modal on backdrop click
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeModal(modal.id);
            }
        });
    });
    
    // Close modal on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const activeModal = document.querySelector('.modal.active');
            if (activeModal) {
                closeModal(activeModal.id);
            }
        }
    });
}

/**
 * Open modal
 * @param {string} modalId Modal ID
 */
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
}

/**
 * Close modal
 * @param {string} modalId Modal ID
 */
function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('active');
        document.body.style.overflow = '';
    }
}

/**
 * Alert Handlers
 */
function initAlertHandlers() {
    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        // Add close button if not present
        if (!alert.querySelector('.alert-close')) {
            const closeBtn = document.createElement('button');
            closeBtn.className = 'alert-close';
            closeBtn.innerHTML = '×';
            closeBtn.style.cssText = `
                background: none;
                border: none;
                float: right;
                font-size: 1.2rem;
                cursor: pointer;
                margin-left: 1rem;
                opacity: 0.7;
            `;
            
            closeBtn.addEventListener('click', function() {
                hideAlert(alert);
            });
            
            alert.appendChild(closeBtn);
        }
        
        // Auto-hide after 5 seconds
        setTimeout(() => {
            hideAlert(alert);
        }, 5000);
    });
}

/**
 * Hide alert
 * @param {HTMLElement} alert Alert element
 */
function hideAlert(alert) {
    alert.style.opacity = '0';
    alert.style.transform = 'translateY(-10px)';
    alert.style.transition = 'all 0.3s ease';
    
    setTimeout(() => {
        if (alert.parentNode) {
            alert.parentNode.removeChild(alert);
        }
    }, 300);
}

/**
 * Show alert programmatically
 * @param {string} message Alert message
 * @param {string} type Alert type (success, error, info, warning)
 */
function showAlert(message, type = 'info') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type}`;
    alertDiv.innerHTML = `
        ${message}
        <button class="alert-close" style="
            background: none;
            border: none;
            float: right;
            font-size: 1.2rem;
            cursor: pointer;
            margin-left: 1rem;
            opacity: 0.7;
        ">×</button>
    `;
    
    // Insert at top of main content or body
    const main = document.querySelector('.main') || document.body;
    main.insertBefore(alertDiv, main.firstChild);
    
    // Add close handler
    const closeBtn = alertDiv.querySelector('.alert-close');
    closeBtn.addEventListener('click', function() {
        hideAlert(alertDiv);
    });
    
    // Auto-hide after 5 seconds
    setTimeout(() => {
        hideAlert(alertDiv);
    }, 5000);
}

/**
 * Password Toggle
 */
function initPasswordToggle() {
    const passwordFields = document.querySelectorAll('input[type="password"]');
    
    passwordFields.forEach(field => {
        // Create toggle button
        const toggleBtn = document.createElement('button');
        toggleBtn.type = 'button';
        toggleBtn.className = 'password-toggle';
        toggleBtn.innerHTML = '👁️';
        toggleBtn.style.cssText = `
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            font-size: 1rem;
        `;
        
        // Wrap field in relative container
        const wrapper = document.createElement('div');
        wrapper.style.position = 'relative';
        wrapper.style.display = 'inline-block';
        wrapper.style.width = '100%';
        
        field.parentNode.insertBefore(wrapper, field);
        wrapper.appendChild(field);
        wrapper.appendChild(toggleBtn);
        
        // Toggle password visibility
        toggleBtn.addEventListener('click', function() {
            if (field.type === 'password') {
                field.type = 'text';
                toggleBtn.innerHTML = '🙈';
            } else {
                field.type = 'password';
                toggleBtn.innerHTML = '👁️';
            }
        });
    });
}

/**
 * Confirm Dialogs
 */
function initConfirmDialogs() {
    const confirmLinks = document.querySelectorAll('[data-confirm]');
    
    confirmLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            const message = link.getAttribute('data-confirm');
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });
}

/**
 * Loading States
 */
function showLoading(element) {
    const originalText = element.textContent;
    element.setAttribute('data-original-text', originalText);
    element.textContent = 'Loading...';
    element.disabled = true;
    element.classList.add('loading');
}

function hideLoading(element) {
    const originalText = element.getAttribute('data-original-text');
    if (originalText) {
        element.textContent = originalText;
        element.removeAttribute('data-original-text');
    }
    element.disabled = false;
    element.classList.remove('loading');
}

/**
 * AJAX Helper Functions
 */
function ajaxRequest(url, method = 'GET', data = null) {
    return new Promise((resolve, reject) => {
        const xhr = new XMLHttpRequest();
        
        xhr.open(method, url, true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        
        xhr.onload = function() {
            if (xhr.status >= 200 && xhr.status < 300) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    resolve(response);
                } catch (e) {
                    resolve(xhr.responseText);
                }
            } else {
                reject(new Error('Request failed'));
            }
        };
        
        xhr.onerror = function() {
            reject(new Error('Network error'));
        };
        
        if (data && method !== 'GET') {
            if (typeof data === 'object') {
                const formData = new URLSearchParams();
                for (const key in data) {
                    formData.append(key, data[key]);
                }
                xhr.send(formData);
            } else {
                xhr.send(data);
            }
        } else {
            xhr.send();
        }
    });
}

/**
 * Utility Functions
 */
function formatCurrency(amount) {
    return 'RM ' + parseFloat(amount).toFixed(2);
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-MY', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

function formatDateTime(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-MY', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

function throttle(func, limit) {
    let inThrottle;
    return function() {
        const args = arguments;
        const context = this;
        if (!inThrottle) {
            func.apply(context, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
}

// Export functions for global use
window.FoodOrderingApp = {
    showAlert,
    hideAlert,
    openModal,
    closeModal,
    showLoading,
    hideLoading,
    ajaxRequest,
    formatCurrency,
    formatDate,
    formatDateTime,
    debounce,
    throttle
};