<?php
/**
 * Navigation Component
 * Online Food Ordering System - Phase 1
 * 
 * This file contains the navigation menu with role-based access control.
 */

// Get current page for active navigation highlighting
$currentPage = basename($_SERVER['PHP_SELF']);
$currentUser = getCurrentUser();
?>

<nav class="nav" id="main-nav">
    <!-- Public Navigation Items -->
    <ul class="nav-list">
        <li class="nav-item">
            <a href="<?php echo SITE_URL; ?>index.php" 
               class="nav-link <?php echo ($currentPage == 'index.php') ? 'active' : ''; ?>">
                Home
            </a>
        </li>
        
        <li class="nav-item">
            <a href="<?php echo SITE_URL; ?>menu/menu.php" 
               class="nav-link <?php echo ($currentPage == 'menu.php') ? 'active' : ''; ?>">
                Menu
            </a>
        </li>
        
        <?php if (isLoggedIn()): ?>
            <!-- Logged-in User Navigation -->
            
            <?php if (isCustomer()): ?>
                <!-- Customer-specific navigation -->
                <li class="nav-item">
                    <a href="<?php echo SITE_URL; ?>menu/cart.php" 
                       class="nav-link <?php echo ($currentPage == 'cart.php') ? 'active' : ''; ?>">
                        Cart
                        <span id="cart-count" class="cart-count"></span>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="<?php echo SITE_URL; ?>orders/orders.php" 
                       class="nav-link <?php echo ($currentPage == 'orders.php') ? 'active' : ''; ?>">
                        My Orders
                    </a>
                </li>
            <?php endif; ?>
            
            <?php if (isAdmin()): ?>
                <!-- Admin-specific navigation -->
                <li class="nav-item">
                    <a href="<?php echo SITE_URL; ?>admin/admin.php" 
                       class="nav-link <?php echo ($currentPage == 'admin.php') ? 'active' : ''; ?>">
                        Dashboard
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="<?php echo SITE_URL; ?>admin/admin-orders.php" 
                       class="nav-link <?php echo ($currentPage == 'admin-orders.php') ? 'active' : ''; ?>">
                        Orders
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="<?php echo SITE_URL; ?>admin/admin-menu.php" 
                       class="nav-link <?php echo ($currentPage == 'admin-menu.php') ? 'active' : ''; ?>">
                        Menu Management
                    </a>
                </li>
            <?php endif; ?>
            
            <!-- User Profile Dropdown -->
            <li class="nav-item nav-dropdown">
                <a href="#" class="nav-link nav-dropdown-toggle">
                    <?php echo htmlspecialchars($currentUser['full_name']); ?>
                    <span class="dropdown-arrow">▼</span>
                </a>
                <ul class="nav-dropdown-menu">
                    <li>
                        <a href="<?php echo SITE_URL; ?>auth/profile.php" 
                           class="nav-link <?php echo ($currentPage == 'profile.php') ? 'active' : ''; ?>">
                            Profile
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo SITE_URL; ?>logout.php" 
                           class="nav-link" 
                           data-confirm="Are you sure you want to logout?">
                            Logout
                        </a>
                    </li>
                </ul>
            </li>
            
        <?php else: ?>
            <!-- Guest Navigation -->
            <li class="nav-item">
                <a href="<?php echo SITE_URL; ?>auth/login.php" 
                   class="nav-link <?php echo ($currentPage == 'login.php') ? 'active' : ''; ?>">
                    Login
                </a>
            </li>
            
            <li class="nav-item">
                <a href="<?php echo SITE_URL; ?>auth/register.php" 
                   class="nav-link btn btn-primary <?php echo ($currentPage == 'register.php') ? 'active' : ''; ?>">
                    Register
                </a>
            </li>
        <?php endif; ?>
    </ul>
</nav>

<style>
/* Additional Navigation Styles */
.nav-list {
    display: flex;
    list-style: none;
    gap: 1rem;
    margin: 0;
    padding: 0;
    align-items: center;
}

.nav-dropdown {
    position: relative;
}

.nav-dropdown-toggle {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.dropdown-arrow {
    font-size: 0.75rem;
    transition: transform 0.2s ease;
}

.nav-dropdown:hover .dropdown-arrow {
    transform: rotate(180deg);
}

.nav-dropdown-menu {
    display: none;
    position: absolute;
    top: 100%;
    right: 0;
    background-color: white;
    border: 1px solid #e2e8f0;
    border-radius: 0.375rem;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    min-width: 150px;
    z-index: 1000;
    padding: 0.5rem 0;
    margin-top: 0.5rem;
}

.nav-dropdown:hover .nav-dropdown-menu {
    display: block;
}

.nav-dropdown-menu li {
    width: 100%;
}

.nav-dropdown-menu .nav-link {
    display: block;
    padding: 0.5rem 1rem;
    color: #1e293b;
    border-radius: 0;
    white-space: nowrap;
}

.nav-dropdown-menu .nav-link:hover {
    background-color: #f1f5f9;
    color: #334155;
}

.cart-count {
    background-color: #ef4444;
    color: white;
    font-size: 0.75rem;
    font-weight: bold;
    padding: 0.125rem 0.375rem;
    border-radius: 50%;
    margin-left: 0.25rem;
    min-width: 1.25rem;
    text-align: center;
    display: none;
}

.cart-count.has-items {
    display: inline-block;
}

/* Mobile Navigation Adjustments */
@media (max-width: 768px) {
    .nav-list {
        flex-direction: column;
        gap: 0;
        width: 100%;
    }
    
    .nav-item {
        width: 100%;
    }
    
    .nav-link {
        display: block;
        padding: 0.75rem 1rem;
        border-bottom: 1px solid #475569;
        width: 100%;
    }
    
    .nav-dropdown-menu {
        position: static;
        display: block;
        background-color: #475569;
        border: none;
        box-shadow: none;
        border-radius: 0;
        margin: 0;
        padding: 0;
    }
    
    .nav-dropdown-menu .nav-link {
        padding-left: 2rem;
        background-color: #475569;
        color: #e2e8f0;
    }
    
    .nav-dropdown-menu .nav-link:hover {
        background-color: #64748b;
        color: white;
    }
    
    .dropdown-arrow {
        margin-left: auto;
    }
}
</style>

<script>
// Update cart count on page load
document.addEventListener('DOMContentLoaded', function() {
    updateCartCount();
});

// Function to update cart count
function updateCartCount() {
    const cartCountElement = document.getElementById('cart-count');
    if (!cartCountElement) return;
    
    // Check if user is logged in and is a customer
    <?php if (isCustomer()): ?>
        // Get cart count from session or make AJAX request
        // This will be implemented in later phases
        const cartCount = getCartItemCount(); // Function to be implemented
        
        if (cartCount > 0) {
            cartCountElement.textContent = cartCount;
            cartCountElement.classList.add('has-items');
        } else {
            cartCountElement.classList.remove('has-items');
        }
    <?php endif; ?>
}

// Placeholder function for cart count (to be implemented in Phase 3)
function getCartItemCount() {
    // This will be implemented when we add cart functionality
    return 0;
}

// Dropdown menu functionality for mobile
document.addEventListener('DOMContentLoaded', function() {
    const dropdownToggles = document.querySelectorAll('.nav-dropdown-toggle');
    
    dropdownToggles.forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            if (window.innerWidth <= 768) {
                e.preventDefault();
                const dropdown = toggle.parentNode;
                const menu = dropdown.querySelector('.nav-dropdown-menu');
                
                // Toggle menu visibility
                if (menu.style.display === 'block') {
                    menu.style.display = 'none';
                    toggle.querySelector('.dropdown-arrow').style.transform = 'rotate(0deg)';
                } else {
                    menu.style.display = 'block';
                    toggle.querySelector('.dropdown-arrow').style.transform = 'rotate(180deg)';
                }
            }
        });
    });
});
</script>