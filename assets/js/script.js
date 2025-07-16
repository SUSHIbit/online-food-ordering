/**
 * Main JavaScript File
 * Online Food Ordering System - Fixed Version
 * 
 * Contains all functionality for the application
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize all components
    initMobileNavigation();
    initFormValidation();
    initModalHandlers();
    initAlertHandlers();
    initPasswordToggle();
    initConfirmDialogs();
    
    // Initialize menu-specific functionality
    if (document.querySelector('.menu-page')) {
        initMenuFunctionality();
    }
    
    // Initialize cart count
    updateCartCount();
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

/**
 * MENU PAGE FUNCTIONALITY - Phase 3
 */
function initMenuFunctionality() {
    // Add to cart functionality (SINGLE EVENT LISTENER)
    const addToCartButtons = document.querySelectorAll('.add-to-cart');
    addToCartButtons.forEach(button => {
        // Remove any existing listeners to prevent duplicates
        button.replaceWith(button.cloneNode(true));
    });
    
    // Re-select buttons after cloning
    const freshAddToCartButtons = document.querySelectorAll('.add-to-cart');
    freshAddToCartButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const itemId = button.getAttribute('data-item-id');
            const itemName = button.getAttribute('data-item-name');
            const itemPrice = button.getAttribute('data-item-price');
            
            // Show loading state
            showLoading(button);
            
            // Add to cart via AJAX
            addToCartAjax(itemId, 1, button);
        });
    });
    
    // View details functionality
    const viewDetailsButtons = document.querySelectorAll('.view-details');
    viewDetailsButtons.forEach(button => {
        button.addEventListener('click', function() {
            const itemId = button.getAttribute('data-item-id');
            loadItemDetails(itemId);
        });
    });
    
    // Auto-submit form on filter change
    const filterSelects = document.querySelectorAll('#category, #price_range');
    filterSelects.forEach(select => {
        select.addEventListener('change', function() {
            document.querySelector('.filter-form').submit();
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
                    document.querySelector('.filter-form').submit();
                }
            }, 500);
        });
    }
}

/**
 * Add to cart AJAX function
 */
function addToCartAjax(itemId, quantity, button) {
    const formData = new FormData();
    formData.append('csrf_token', getCSRFToken());
    formData.append('item_id', itemId);
    formData.append('quantity', quantity);
    
    fetch('ajax_add_to_cart.php', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        hideLoading(button);
        
        if (data.success) {
            // Show success message
            showAlert(data.message, 'success');
            
            // Update cart count
            updateCartCountDisplay(data.cart_count);
            
            // Add success animation
            button.classList.add('success-animation');
            setTimeout(() => {
                button.classList.remove('success-animation');
            }, 600);
        } else {
            showAlert(data.message, 'error');
        }
    })
    .catch(error => {
        hideLoading(button);
        showAlert('Failed to add item to cart. Please try again.', 'error');
        console.error('Error:', error);
    });
}

/**
 * Get CSRF token
 */
function getCSRFToken() {
    // Try to get from meta tag first
    const metaToken = document.querySelector('meta[name="csrf-token"]');
    if (metaToken) {
        return metaToken.getAttribute('content');
    }
    
    // Try to get from form
    const tokenInput = document.querySelector('input[name="csrf_token"]');
    if (tokenInput) {
        return tokenInput.value;
    }
    
    return 'temp_csrf_token';
}

/**
 * Update cart count display
 */
function updateCartCountDisplay(count) {
    const cartCountElement = document.getElementById('cart-count');
    if (cartCountElement) {
        if (count > 0) {
            cartCountElement.textContent = count;
            cartCountElement.classList.add('has-items');
        } else {
            cartCountElement.classList.remove('has-items');
        }
    }
}

/**
 * Update cart count from server
 */
function updateCartCount() {
    fetch('../ajax/get_cart_count.php', {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateCartCountDisplay(data.count);
        }
    })
    .catch(error => {
        console.error('Error updating cart count:', error);
    });
}

/**
 * Load item details modal
 */
function loadItemDetails(itemId) {
    const modal = document.getElementById('item-details-modal');
    const content = document.getElementById('item-details-content');
    
    if (!modal || !content) return;
    
    // Show loading state
    content.innerHTML = '<div class="spinner"></div>';
    openModal('item-details-modal');
    
    // Fetch item details
    ajaxRequest('../ajax/get_item_details.php', 'POST', { item_id: itemId })
        .then(response => {
            if (response.success) {
                content.innerHTML = response.html;
                
                // Bind add to cart button in modal
                const addToCartBtn = content.querySelector('.add-to-cart-modal');
                if (addToCartBtn) {
                    addToCartBtn.addEventListener('click', function() {
                        const itemId = addToCartBtn.getAttribute('data-item-id');
                        
                        addToCartAjax(itemId, 1, addToCartBtn);
                        
                        // Close modal after adding
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
            content.innerHTML = '<div class="alert alert-error">Error loading item details.</div>';
            console.error('Error loading item details:', error);
        });
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