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

/**
 * Enhanced JavaScript - Phase 2
 * Online Food Ordering System - Menu System
 * 
 * Additional functionality for menu display and management
 */

// Menu System Object
const MenuSystem = {
    // Search functionality
    searchDelay: 500,
    searchTimer: null,
    
    // Filter state
    activeFilters: {
        search: '',
        category: '',
        priceRange: '',
        sortBy: 'name'
    },
    
    // Loading states
    isLoading: false,
    
    // Initialize menu system
    init() {
        this.bindEvents();
        this.loadMenuItems();
        this.initializeFilters();
        this.setupInfiniteScroll();
    },
    
    // Bind all event listeners
    bindEvents() {
        // Search functionality
        const searchInput = document.getElementById('search');
        if (searchInput) {
            searchInput.addEventListener('input', (e) => {
                this.handleSearch(e.target.value);
            });
        }
        
        // Filter changes
        const categoryFilter = document.getElementById('category');
        const priceRangeFilter = document.getElementById('price_range');
        
        if (categoryFilter) {
            categoryFilter.addEventListener('change', (e) => {
                this.handleFilterChange('category', e.target.value);
            });
        }
        
        if (priceRangeFilter) {
            priceRangeFilter.addEventListener('change', (e) => {
                this.handleFilterChange('priceRange', e.target.value);
            });
        }
        
        // Category tabs
        const categoryTabs = document.querySelectorAll('.category-tab');
        categoryTabs.forEach(tab => {
            tab.addEventListener('click', (e) => {
                e.preventDefault();
                this.handleCategoryTab(tab);
            });
        });
        
        // Menu item actions
        this.bindMenuItemEvents();
        
        // View details modal
        this.bindViewDetailsEvents();
        
        // Admin actions
        this.bindAdminEvents();
    },
    
    // Handle search input
    handleSearch(searchTerm) {
        clearTimeout(this.searchTimer);
        this.activeFilters.search = searchTerm;
        
        this.searchTimer = setTimeout(() => {
            this.filterMenuItems();
        }, this.searchDelay);
    },
    
    // Handle filter changes
    handleFilterChange(filterType, value) {
        this.activeFilters[filterType] = value;
        this.filterMenuItems();
    },
    
    // Handle category tab clicks
    handleCategoryTab(tab) {
        // Remove active class from all tabs
        document.querySelectorAll('.category-tab').forEach(t => {
            t.classList.remove('active');
        });
        
        // Add active class to clicked tab
        tab.classList.add('active');
        
        // Get category ID
        const categoryId = tab.getAttribute('href').split('category=')[1] || '';
        this.handleFilterChange('category', categoryId);
    },
    
    // Bind menu item events
    bindMenuItemEvents() {
        // Add to cart buttons
        const addToCartButtons = document.querySelectorAll('.add-to-cart');
        addToCartButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                this.handleAddToCart(button);
            });
        });
        
        // View details buttons
        const viewDetailsButtons = document.querySelectorAll('.view-details');
        viewDetailsButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                this.handleViewDetails(button);
            });
        });
        
        // Menu item hover effects
        const menuItems = document.querySelectorAll('.menu-item');
        menuItems.forEach(item => {
            item.addEventListener('mouseenter', (e) => {
                this.handleMenuItemHover(item, true);
            });
            
            item.addEventListener('mouseleave', (e) => {
                this.handleMenuItemHover(item, false);
            });
        });
    },
    
    // Handle add to cart
    handleAddToCart(button) {
        const itemId = button.getAttribute('data-item-id');
        const itemName = button.getAttribute('data-item-name');
        const itemPrice = button.getAttribute('data-item-price');
        
        // Show loading state
        FoodOrderingApp.showLoading(button);
        
        // Simulate API call (will be implemented in Phase 3)
        setTimeout(() => {
            FoodOrderingApp.hideLoading(button);
            
            // Show success message
            FoodOrderingApp.showAlert(`${itemName} will be added to cart in Phase 3!`, 'info');
            
            // Add success animation
            button.classList.add('success-animation');
            setTimeout(() => {
                button.classList.remove('success-animation');
            }, 600);
        }, 1000);
    },
    
    // Handle view details
    handleViewDetails(button) {
        const itemId = button.getAttribute('data-item-id');
        this.loadItemDetails(itemId);
    },
    
    // Handle menu item hover
    handleMenuItemHover(item, isHovering) {
        const image = item.querySelector('.menu-item-image');
        const overlay = item.querySelector('.menu-item-overlay');
        
        if (isHovering) {
            item.classList.add('hovered');
            if (image) image.style.transform = 'scale(1.05)';
            if (overlay) overlay.style.opacity = '1';
        } else {
            item.classList.remove('hovered');
            if (image) image.style.transform = 'scale(1)';
            if (overlay) overlay.style.opacity = '0';
        }
    },
    
    // Load item details
    loadItemDetails(itemId) {
        const modal = document.getElementById('item-details-modal');
        const content = document.getElementById('item-details-content');
        
        if (!modal || !content) return;
        
        // Show loading state
        content.innerHTML = '<div class="spinner"></div>';
        FoodOrderingApp.openModal('item-details-modal');
        
        // Fetch item details
        FoodOrderingApp.ajaxRequest('ajax/get_item_details.php', 'POST', { item_id: itemId })
            .then(response => {
                if (response.success) {
                    content.innerHTML = response.html;
                    this.bindModalEvents();
                } else {
                    content.innerHTML = '<div class="alert alert-error">Failed to load item details.</div>';
                }
            })
            .catch(error => {
                content.innerHTML = '<div class="alert alert-error">Error loading item details.</div>';
                console.error('Error loading item details:', error);
            });
    },
    
    // Bind modal events
    bindModalEvents() {
        const addToCartBtn = document.querySelector('.add-to-cart-modal');
        if (addToCartBtn) {
            addToCartBtn.addEventListener('click', (e) => {
                this.handleAddToCart(addToCartBtn);
            });
        }
    },
    
    // Filter menu items
    filterMenuItems() {
        if (this.isLoading) return;
        
        this.isLoading = true;
        this.showLoadingState();
        
        // Build query parameters
        const params = new URLSearchParams();
        Object.keys(this.activeFilters).forEach(key => {
            if (this.activeFilters[key]) {
                params.append(key, this.activeFilters[key]);
            }
        });
        
        // Simulate API call for filtering
        setTimeout(() => {
            this.hideLoadingState();
            this.isLoading = false;
            
            // For now, just submit the form
            const form = document.querySelector('.filter-form');
            if (form) {
                form.submit();
            }
        }, 500);
    },
    
    // Show loading state
    showLoadingState() {
        const menuGrid = document.querySelector('.menu-grid');
        if (menuGrid) {
            menuGrid.innerHTML = this.generateSkeletonHTML();
        }
    },
    
    // Hide loading state
    hideLoadingState() {
        // This would be handled by the actual menu items rendering
    },
    
    // Generate skeleton HTML for loading
    generateSkeletonHTML() {
        let skeletonHTML = '<div class="menu-loading">';
        for (let i = 0; i < 6; i++) {
            skeletonHTML += `
                <div class="menu-item-skeleton">
                    <div class="skeleton-image"></div>
                    <div class="skeleton-text"></div>
                    <div class="skeleton-text short"></div>
                    <div class="skeleton-text"></div>
                </div>
            `;
        }
        skeletonHTML += '</div>';
        return skeletonHTML;
    },
    
    // Initialize filters from URL
    initializeFilters() {
        const urlParams = new URLSearchParams(window.location.search);
        
        this.activeFilters.search = urlParams.get('search') || '';
        this.activeFilters.category = urlParams.get('category') || '';
        this.activeFilters.priceRange = urlParams.get('price_range') || '';
        
        // Update form fields
        const searchInput = document.getElementById('search');
        const categorySelect = document.getElementById('category');
        const priceRangeSelect = document.getElementById('price_range');
        
        if (searchInput) searchInput.value = this.activeFilters.search;
        if (categorySelect) categorySelect.value = this.activeFilters.category;
        if (priceRangeSelect) priceRangeSelect.value = this.activeFilters.priceRange;
    },
    
    // Load menu items
    loadMenuItems() {
        const menuItems = document.querySelectorAll('.menu-item');
        
        // Add fade-in animation to menu items
        menuItems.forEach((item, index) => {
            item.style.opacity = '0';
            item.style.transform = 'translateY(20px)';
            
            setTimeout(() => {
                item.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                item.style.opacity = '1';
                item.style.transform = 'translateY(0)';
            }, index * 100);
        });
    },
    
    // Setup infinite scroll
    setupInfiniteScroll() {
        const options = {
            root: null,
            rootMargin: '200px',
            threshold: 0.1
        };
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    this.loadMoreItems();
                }
            });
        }, options);
        
        const loadMoreTrigger = document.querySelector('.load-more-trigger');
        if (loadMoreTrigger) {
            observer.observe(loadMoreTrigger);
        }
    },
    
    // Load more items (for infinite scroll)
    loadMoreItems() {
        // Placeholder for infinite scroll implementation
        console.log('Loading more items...');
    },
    
    // Bind admin events
    bindAdminEvents() {
        // Toggle availability
        const toggleButtons = document.querySelectorAll('.toggle-availability');
        toggleButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                this.handleToggleAvailability(button);
            });
        });
        
        // Delete confirmations
        const deleteButtons = document.querySelectorAll('.delete-item');
        deleteButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                if (!confirm('Are you sure you want to delete this item?')) {
                    e.preventDefault();
                }
            });
        });
        
        // Image preview
        const imageInputs = document.querySelectorAll('input[type="file"]');
        imageInputs.forEach(input => {
            input.addEventListener('change', (e) => {
                this.handleImagePreview(e);
            });
        });
        
        // Form validation
        const forms = document.querySelectorAll('form[data-validate]');
        forms.forEach(form => {
            form.addEventListener('submit', (e) => {
                if (!this.validateForm(form)) {
                    e.preventDefault();
                }
            });
        });
    },
    
    // Handle toggle availability
    handleToggleAvailability(button) {
        const itemId = button.getAttribute('data-item-id');
        const currentStatus = button.getAttribute('data-current-status');
        
        FoodOrderingApp.showLoading(button);
        
        // Simulate API call
        setTimeout(() => {
            FoodOrderingApp.hideLoading(button);
            
            // Update button text and status
            if (currentStatus === 'available') {
                button.textContent = '▶️ Enable';
                button.setAttribute('data-current-status', 'unavailable');
                button.classList.remove('btn-warning');
                button.classList.add('btn-success');
            } else {
                button.textContent = '⏸️ Disable';
                button.setAttribute('data-current-status', 'available');
                button.classList.remove('btn-success');
                button.classList.add('btn-warning');
            }
            
            FoodOrderingApp.showAlert('Item availability updated!', 'success');
        }, 1000);
    },
    
    // Handle image preview
    handleImagePreview(event) {
        const file = event.target.files[0];
        if (!file) return;
        
        const reader = new FileReader();
        reader.onload = (e) => {
            // Remove existing preview
            const existingPreview = event.target.parentNode.querySelector('.image-preview');
            if (existingPreview) {
                existingPreview.remove();
            }
            
            // Create new preview
            const preview = document.createElement('div');
            preview.className = 'image-preview';
            preview.innerHTML = `
                <img src="${e.target.result}" alt="Preview" class="preview-image">
                <p class="text-slate-500">Preview</p>
            `;
            
            event.target.parentNode.appendChild(preview);
        };
        
        reader.readAsDataURL(file);
    },
    
    // Validate form
    validateForm(form) {
        const requiredFields = form.querySelectorAll('[required]');
        let isValid = true;
        
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                this.showFieldError(field, 'This field is required');
                isValid = false;
            } else {
                this.clearFieldError(field);
            }
        });
        
        // Validate price fields
        const priceFields = form.querySelectorAll('input[type="number"][name="price"]');
        priceFields.forEach(field => {
            const value = parseFloat(field.value);
            if (value <= 0) {
                this.showFieldError(field, 'Price must be greater than 0');
                isValid = false;
            }
        });
        
        return isValid;
    },
    
    // Show field error
    showFieldError(field, message) {
        field.classList.add('error');
        
        const existingError = field.parentNode.querySelector('.field-error');
        if (existingError) {
            existingError.remove();
        }
        
        const errorDiv = document.createElement('div');
        errorDiv.className = 'field-error';
        errorDiv.textContent = message;
        field.parentNode.appendChild(errorDiv);
    },
    
    // Clear field error
    clearFieldError(field) {
        field.classList.remove('error');
        
        const errorMessage = field.parentNode.querySelector('.field-error');
        if (errorMessage) {
            errorMessage.remove();
        }
    }
};

// Cart functionality (placeholder for Phase 3)
const Cart = {
    items: [],
    total: 0,
    
    // Add item to cart
    addItem(itemId, itemName, itemPrice, quantity = 1) {
        const existingItem = this.items.find(item => item.id === itemId);
        
        if (existingItem) {
            existingItem.quantity += quantity;
        } else {
            this.items.push({
                id: itemId,
                name: itemName,
                price: parseFloat(itemPrice),
                quantity: quantity
            });
        }
        
        this.updateTotal();
        this.updateCartDisplay();
        this.saveToStorage();
    },
    
    // Remove item from cart
    removeItem(itemId) {
        this.items = this.items.filter(item => item.id !== itemId);
        this.updateTotal();
        this.updateCartDisplay();
        this.saveToStorage();
    },
    
    // Update item quantity
    updateQuantity(itemId, quantity) {
        const item = this.items.find(item => item.id === itemId);
        if (item) {
            item.quantity = quantity;
            this.updateTotal();
            this.updateCartDisplay();
            this.saveToStorage();
        }
    },
    
    // Update total
    updateTotal() {
        this.total = this.items.reduce((sum, item) => {
            return sum + (item.price * item.quantity);
        }, 0);
    },
    
    // Update cart display
    updateCartDisplay() {
        const cartCount = document.getElementById('cart-count');
        const totalItems = this.items.reduce((sum, item) => sum + item.quantity, 0);
        
        if (cartCount) {
            if (totalItems > 0) {
                cartCount.textContent = totalItems;
                cartCount.classList.add('has-items');
            } else {
                cartCount.classList.remove('has-items');
            }
        }
    },
    
    // Save to storage
    saveToStorage() {
        // Note: localStorage is not available in artifacts, so this is a placeholder
        // In a real implementation, this would save to localStorage
        console.log('Cart saved:', this.items);
    },
    
    // Load from storage
    loadFromStorage() {
        // Note: localStorage is not available in artifacts, so this is a placeholder
        // In a real implementation, this would load from localStorage
        console.log('Cart loaded');
    },
    
    // Clear cart
    clear() {
        this.items = [];
        this.total = 0;
        this.updateCartDisplay();
        this.saveToStorage();
    }
};

// Admin Dashboard functionality
const AdminDashboard = {
    init() {
        this.bindEvents();
        this.loadStatistics();
        this.setupCharts();
    },
    
    bindEvents() {
        // Quick actions
        const quickActions = document.querySelectorAll('.quick-action');
        quickActions.forEach(action => {
            action.addEventListener('click', (e) => {
                this.handleQuickAction(action);
            });
        });
        
        // Bulk actions
        const bulkActionButtons = document.querySelectorAll('.bulk-action');
        bulkActionButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                this.handleBulkAction(button);
            });
        });
        
        // Export functionality
        const exportButtons = document.querySelectorAll('.export-btn');
        exportButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                this.handleExport(button);
            });
        });
    },
    
    handleQuickAction(action) {
        const actionType = action.getAttribute('data-action');
        
        switch (actionType) {
            case 'add-item':
                FoodOrderingApp.openModal('add-item-modal');
                break;
            case 'add-category':
                FoodOrderingApp.openModal('add-category-modal');
                break;
            case 'view-orders':
                window.location.href = 'admin-orders.php';
                break;
            default:
                console.log('Unknown action:', actionType);
        }
    },
    
    handleBulkAction(button) {
        const action = button.getAttribute('data-bulk-action');
        const selectedItems = document.querySelectorAll('.item-checkbox:checked');
        
        if (selectedItems.length === 0) {
            FoodOrderingApp.showAlert('Please select items first', 'warning');
            return;
        }
        
        if (confirm(`Are you sure you want to ${action} ${selectedItems.length} item(s)?`)) {
            // Simulate bulk action
            FoodOrderingApp.showLoading(button);
            
            setTimeout(() => {
                FoodOrderingApp.hideLoading(button);
                FoodOrderingApp.showAlert(`${action} completed successfully!`, 'success');
                
                // Refresh page or update items
                location.reload();
            }, 2000);
        }
    },
    
    handleExport(button) {
        const exportType = button.getAttribute('data-export-type');
        
        FoodOrderingApp.showLoading(button);
        
        // Simulate export
        setTimeout(() => {
            FoodOrderingApp.hideLoading(button);
            FoodOrderingApp.showAlert(`${exportType} export completed!`, 'success');
            
            // In a real implementation, this would trigger a file download
            this.downloadFile(`${exportType}-export.csv`, 'text/csv', 'Sample,CSV,Data\n1,2,3');
        }, 2000);
    },
    
    downloadFile(filename, contentType, content) {
        const blob = new Blob([content], { type: contentType });
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = filename;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        URL.revokeObjectURL(url);
    },
    
    loadStatistics() {
        // Animate statistics counters
        const counters = document.querySelectorAll('.stat-number');
        counters.forEach(counter => {
            const target = parseInt(counter.textContent);
            const increment = target / 100;
            let current = 0;
            
            const timer = setInterval(() => {
                current += increment;
                if (current >= target) {
                    counter.textContent = target;
                    clearInterval(timer);
                } else {
                    counter.textContent = Math.ceil(current);
                }
            }, 20);
        });
    },
    
    setupCharts() {
        // Placeholder for chart initialization
        // In a real implementation, this would use Chart.js or similar
        console.log('Charts initialized');
    }
};

// Utility functions
const Utils = {
    // Format currency
    formatCurrency(amount) {
        return 'RM ' + parseFloat(amount).toFixed(2);
    },
    
    // Format date
    formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-MY', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    },
    
    // Format time
    formatTime(dateString) {
        const date = new Date(dateString);
        return date.toLocaleTimeString('en-MY', {
            hour: '2-digit',
            minute: '2-digit'
        });
    },
    
    // Debounce function
    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    },
    
    // Throttle function
    throttle(func, limit) {
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
    },
    
    // Generate random ID
    generateId() {
        return Date.now().toString(36) + Math.random().toString(36).substr(2);
    },
    
    // Validate email
    validateEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    },
    
    // Validate phone
    validatePhone(phone) {
        const phoneRegex = /^[0-9+\-\s()]{10,20}$/;
        return phoneRegex.test(phone);
    },
    
    // Sanitize HTML
    sanitizeHTML(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    },
    
    // Copy to clipboard
    copyToClipboard(text) {
        if (navigator.clipboard) {
            navigator.clipboard.writeText(text).then(() => {
                FoodOrderingApp.showAlert('Copied to clipboard!', 'success');
            });
        } else {
            // Fallback for older browsers
            const textarea = document.createElement('textarea');
            textarea.value = text;
            document.body.appendChild(textarea);
            textarea.select();
            document.execCommand('copy');
            document.body.removeChild(textarea);
            FoodOrderingApp.showAlert('Copied to clipboard!', 'success');
        }
    },
    
    // Get URL parameters
    getUrlParams() {
        const params = {};
        const urlParams = new URLSearchParams(window.location.search);
        for (const [key, value] of urlParams.entries()) {
            params[key] = value;
        }
        return params;
    },
    
    // Update URL without reload
    updateUrl(params) {
        const url = new URL(window.location);
        Object.keys(params).forEach(key => {
            if (params[key]) {
                url.searchParams.set(key, params[key]);
            } else {
                url.searchParams.delete(key);
            }
        });
        window.history.pushState({}, '', url);
    }
};

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Initialize menu system
    if (document.querySelector('.menu-page')) {
        MenuSystem.init();
    }
    
    // Initialize admin dashboard
    if (document.querySelector('.admin-page')) {
        AdminDashboard.init();
    }
    
    // Initialize cart
    Cart.loadFromStorage();
    
    // Global event listeners
    document.addEventListener('click', function(e) {
        // Close dropdowns when clicking outside
        if (!e.target.closest('.dropdown')) {
            const dropdowns = document.querySelectorAll('.dropdown-menu.show');
            dropdowns.forEach(dropdown => {
                dropdown.classList.remove('show');
            });
        }
        
        // Handle dropdown toggles
        if (e.target.closest('.dropdown-toggle')) {
            e.preventDefault();
            const dropdown = e.target.closest('.dropdown');
            const menu = dropdown.querySelector('.dropdown-menu');
            menu.classList.toggle('show');
        }
    });
    
    // Handle form submissions
    document.addEventListener('submit', function(e) {
        const form = e.target;
        if (form.hasAttribute('data-async')) {
            e.preventDefault();
            handleAsyncForm(form);
        }
    });
    
    // Handle keyboard navigation
    document.addEventListener('keydown', function(e) {
        // Close modals with Escape key
        if (e.key === 'Escape') {
            const activeModal = document.querySelector('.modal.active');
            if (activeModal) {
                FoodOrderingApp.closeModal(activeModal.id);
            }
        }
        
        // Search shortcut (Ctrl/Cmd + K)
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            const searchInput = document.getElementById('search');
            if (searchInput) {
                searchInput.focus();
            }
        }
    });
});

// Handle async forms
function handleAsyncForm(form) {
    const submitBtn = form.querySelector('button[type="submit"]');
    const formData = new FormData(form);
    
    FoodOrderingApp.showLoading(submitBtn);
    
    fetch(form.action, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        FoodOrderingApp.hideLoading(submitBtn);
        
        if (data.success) {
            FoodOrderingApp.showAlert(data.message, 'success');
            if (data.redirect) {
                setTimeout(() => {
                    window.location.href = data.redirect;
                }, 1000);
            }
        } else {
            FoodOrderingApp.showAlert(data.message, 'error');
        }
    })
    .catch(error => {
        FoodOrderingApp.hideLoading(submitBtn);
        FoodOrderingApp.showAlert('An error occurred. Please try again.', 'error');
        console.error('Form submission error:', error);
    });
}

// Export to global scope
window.MenuSystem = MenuSystem;
window.Cart = Cart;
window.AdminDashboard = AdminDashboard;
window.Utils = Utils;