/**
 * Main JavaScript File - AJAX ERRORS FIXED
 * Online Food Ordering System
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize all components safely
    initMobileNavigation();
    initFormValidation();
    initModalHandlers();
    initAlertHandlers();
    initPasswordToggle();
    initConfirmDialogs();
    
    // Initialize menu-specific functionality only if on menu page
    if (document.querySelector('.menu-page')) {
        initMenuFunctionality();
    }
    
    // Initialize cart count safely - ONLY for customers
    if (isCustomerPage()) {
        updateCartCount();
    }
});

/**
 * Check if current user is customer (avoid unnecessary AJAX calls)
 */
function isCustomerPage() {
    // Check if cart element exists (indicates customer page)
    return document.getElementById('cart-count') !== null;
}

/**
 * Mobile Navigation Toggle - FIXED
 */
function initMobileNavigation() {
    const mobileToggle = document.querySelector('.mobile-nav-toggle');
    const nav = document.querySelector('.nav');
    
    if (mobileToggle && nav) {
        mobileToggle.addEventListener('click', function(e) {
            e.preventDefault();
            nav.classList.toggle('active');
            
            // Update button text
            const span = mobileToggle.querySelector('span');
            if (span) {
                if (nav.classList.contains('active')) {
                    span.innerHTML = '✕';
                } else {
                    span.innerHTML = '☰';
                }
            }
        });
        
        // Close menu when clicking on nav links
        const navLinks = nav.querySelectorAll('.nav-link');
        navLinks.forEach(link => {
            link.addEventListener('click', function() {
                if (window.innerWidth <= 768) {
                    nav.classList.remove('active');
                    const span = mobileToggle.querySelector('span');
                    if (span) {
                        span.innerHTML = '☰';
                    }
                }
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
                clearFieldError(input);
            });
        });
    });
}

/**
 * Validate entire form
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
 */
function validateField(field) {
    const value = field.value.trim();
    const type = field.type;
    const required = field.hasAttribute('required');
    
    clearFieldError(field);
    
    if (required && !value) {
        showFieldError(field, 'This field is required');
        return false;
    }
    
    if (!value && !required) {
        return true;
    }
    
    if (type === 'email' || field.name === 'email') {
        if (!isValidEmail(value)) {
            showFieldError(field, 'Please enter a valid email address');
            return false;
        }
    }
    
    if (type === 'password' || field.name === 'password') {
        if (value.length < 6) {
            showFieldError(field, 'Password must be at least 6 characters');
            return false;
        }
    }
    
    if (field.name === 'phone' || field.type === 'tel') {
        if (!isValidPhone(value)) {
            showFieldError(field, 'Please enter a valid phone number');
            return false;
        }
    }
    
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
 */
function showFieldError(field, message) {
    field.classList.add('error');
    
    const existingError = field.parentNode.querySelector('.field-error');
    if (existingError) {
        existingError.remove();
    }
    
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
 */
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

/**
 * Validate phone number
 */
function isValidPhone(phone) {
    const phoneRegex = /^[0-9+\-\s()]{10,20}$/;
    return phoneRegex.test(phone);
}

/**
 * Modal Handlers
 */
function initModalHandlers() {
    const modalTriggers = document.querySelectorAll('[data-modal]');
    modalTriggers.forEach(trigger => {
        trigger.addEventListener('click', function(e) {
            e.preventDefault();
            const modalId = trigger.getAttribute('data-modal');
            openModal(modalId);
        });
    });
    
    const closeButtons = document.querySelectorAll('.modal-close, [data-modal-close]');
    closeButtons.forEach(button => {
        button.addEventListener('click', function() {
            const modal = button.closest('.modal');
            if (modal) {
                closeModal(modal.id);
            }
        });
    });
    
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeModal(modal.id);
            }
        });
    });
    
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
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
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
        
        setTimeout(() => {
            hideAlert(alert);
        }, 5000);
    });
}

/**
 * Hide alert
 */
function hideAlert(alert) {
    if (!alert || !alert.parentNode) return;
    
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
    
    const main = document.querySelector('.main') || document.body;
    main.insertBefore(alertDiv, main.firstChild);
    
    const closeBtn = alertDiv.querySelector('.alert-close');
    closeBtn.addEventListener('click', function() {
        hideAlert(alertDiv);
    });
    
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
        
        const wrapper = document.createElement('div');
        wrapper.style.position = 'relative';
        wrapper.style.display = 'inline-block';
        wrapper.style.width = '100%';
        
        field.parentNode.insertBefore(wrapper, field);
        wrapper.appendChild(field);
        wrapper.appendChild(toggleBtn);
        
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
    if (!element) return;
    
    const originalText = element.textContent;
    element.setAttribute('data-original-text', originalText);
    element.textContent = 'Loading...';
    element.disabled = true;
    element.classList.add('btn-loading');
}

function hideLoading(element) {
    if (!element) return;
    
    const originalText = element.getAttribute('data-original-text');
    if (originalText) {
        element.textContent = originalText;
        element.removeAttribute('data-original-text');
    }
    element.disabled = false;
    element.classList.remove('btn-loading');
}

/**
 * MENU PAGE FUNCTIONALITY - FIXED
 */
function initMenuFunctionality() {
    // Add to cart functionality with safety checks
    const addToCartButtons = document.querySelectorAll('.add-to-cart');
    addToCartButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const itemId = button.getAttribute('data-item-id');
            
            if (!itemId) {
                console.warn('No item ID found for add to cart button');
                return;
            }
            
            showLoading(button);
            addToCartAjax(itemId, 1, button);
        });
    });
    
    // View details functionality
    const viewDetailsButtons = document.querySelectorAll('.view-details');
    viewDetailsButtons.forEach(button => {
        button.addEventListener('click', function() {
            const itemId = button.getAttribute('data-item-id');
            if (itemId) {
                loadItemDetails(itemId);
            }
        });
    });
    
    // Auto-submit form on filter change
    const filterSelects = document.querySelectorAll('#category, #price_range');
    filterSelects.forEach(select => {
        select.addEventListener('change', function() {
            const form = document.querySelector('.filter-form');
            if (form) {
                form.submit();
            }
        });
    });
    
    // Search input with debounce
    const searchInput = document.getElementById('search');
    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                if (searchInput.value.length >= 3 || searchInput.value.length === 0) {
                    const form = document.querySelector('.filter-form');
                    if (form) {
                        form.submit();
                    }
                }
            }, 500);
        });
    }
}

/**
 * FIXED: Add to cart AJAX function
 */
function addToCartAjax(itemId, quantity, button) {
    // Get CSRF token safely
    const csrfToken = getCSRFToken();
    if (!csrfToken) {
        console.error('No CSRF token found');
        hideLoading(button);
        showAlert('Security error. Please refresh the page.', 'error');
        return;
    }
    
    const formData = new FormData();
    formData.append('csrf_token', csrfToken);
    formData.append('item_id', itemId);
    formData.append('quantity', quantity);
    
    fetch('ajax_add_to_cart.php', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        hideLoading(button);
        
        if (data.success) {
            showAlert(data.message, 'success');
            updateCartCountDisplay(data.cart_count);
            
            button.classList.add('success-animation');
            setTimeout(() => {
                button.classList.remove('success-animation');
            }, 600);
        } else {
            showAlert(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        hideLoading(button);
        showAlert('Failed to add item to cart. Please try again.', 'error');
    });
}

/**
 * FIXED: Get CSRF token safely
 */
function getCSRFToken() {
    // Try meta tag first
    const metaToken = document.querySelector('meta[name="csrf-token"]');
    if (metaToken) {
        return metaToken.getAttribute('content');
    }
    
    // Try form input
    const tokenInput = document.querySelector('input[name="csrf_token"]');
    if (tokenInput) {
        return tokenInput.value;
    }
    
    // Try hidden form
    const csrfForm = document.getElementById('csrf-form');
    if (csrfForm) {
        const hiddenToken = csrfForm.querySelector('input[name="csrf_token"]');
        if (hiddenToken) {
            return hiddenToken.value;
        }
    }
    
    // Generate a temporary token for pages without CSRF
    return 'temp_token_' + Date.now();
}

/**
 * FIXED: Update cart count display
 */
function updateCartCountDisplay(count) {
    const cartCountElement = document.getElementById('cart-count');
    if (cartCountElement) {
        if (count > 0) {
            cartCountElement.textContent = count;
            cartCountElement.classList.add('has-items');
            cartCountElement.style.display = 'inline-block';
        } else {
            cartCountElement.classList.remove('has-items');
            cartCountElement.style.display = 'none';
        }
    }
}

/**
 * FIXED: Update cart count from server
 */
function updateCartCount() {
    const cartCountElement = document.getElementById('cart-count');
    if (!cartCountElement) return;
    
    // Determine correct AJAX path based on current location
    let ajaxPath = getAjaxPath('get_cart_count.php');
    
    fetch(ajaxPath, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            updateCartCountDisplay(data.count);
        } else {
            console.log('Cart count: user not logged in or not customer');
            cartCountElement.style.display = 'none';
        }
    })
    .catch(error => {
        console.log('Cart count update failed (normal for non-customers):', error.message);
        cartCountElement.style.display = 'none';
    });
}

/**
 * FIXED: Get correct AJAX path based on current directory
 */
function getAjaxPath(filename) {
    const path = window.location.pathname;
    
    if (path.includes('/menu/')) {
        return `../ajax/${filename}`;
    } else if (path.includes('/admin/') || path.includes('/auth/') || path.includes('/orders/')) {
        return `../ajax/${filename}`;
    } else {
        return `ajax/${filename}`;
    }
}

/**
 * Load item details modal - FIXED
 */
function loadItemDetails(itemId) {
    const modal = document.getElementById('item-details-modal');
    const content = document.getElementById('item-details-content');
    
    if (!modal || !content) {
        console.warn('Item details modal not found');
        return;
    }
    
    content.innerHTML = '<div class="spinner"></div>';
    openModal('item-details-modal');
    
    const ajaxPath = getAjaxPath('get_item_details.php');
    const formData = new FormData();
    formData.append('item_id', itemId);
    
    fetch(ajaxPath, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            content.innerHTML = data.html;
            
            const addToCartBtn = content.querySelector('.add-to-cart-modal');
            if (addToCartBtn) {
                addToCartBtn.addEventListener('click', function() {
                    const itemId = addToCartBtn.getAttribute('data-item-id');
                    addToCartAjax(itemId, 1, addToCartBtn);
                    
                    setTimeout(() => {
                        closeModal('item-details-modal');
                    }, 1000);
                });
            }
        } else {
            content.innerHTML = '<div class="alert alert-error">Failed to load item details.</div>';
        }
    })
    .catch(error => {
        console.error('Error loading item details:', error);
        content.innerHTML = '<div class="alert alert-error">Error loading item details.</div>';
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
    formatCurrency,
    formatDate,
    formatDateTime,
    debounce,
    throttle,
    updateCartCount,
    updateCartCountDisplay
};