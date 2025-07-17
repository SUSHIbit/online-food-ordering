<?php
/**
 * Fixed Navigation Component
 * Online Food Ordering System - Working Mobile Menu
 */

// Get current page for active navigation highlighting
$currentPage = basename($_SERVER['PHP_SELF']);
$currentUser = getCurrentUser();
?>

<nav class="nav" id="main-nav">
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
            <?php if (isCustomer()): ?>
                <!-- Customer Cart Link -->
                <li class="nav-item">
                    <a href="<?php echo SITE_URL; ?>menu/cart.php" 
                       class="nav-link cart-link <?php echo ($currentPage == 'cart.php') ? 'active' : ''; ?>">
                        Cart
                        <span id="cart-count" class="cart-count">0</span>
                    </a>
                </li>
                
                <!-- Customer Orders -->
                <li class="nav-item">
                    <a href="<?php echo SITE_URL; ?>orders/orders.php" 
                       class="nav-link <?php echo ($currentPage == 'orders.php') ? 'active' : ''; ?>">
                        My Orders
                    </a>
                </li>
            <?php endif; ?>
            
            <?php if (isAdmin()): ?>
                <!-- Admin Links -->
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
                        Menu
                    </a>
                </li>
            <?php endif; ?>
            
            <!-- User Profile Dropdown - FIXED -->
            <li class="nav-item nav-dropdown">
                <a href="#" class="nav-link nav-dropdown-toggle" onclick="toggleDropdown(event)">
                    <?php echo htmlspecialchars($currentUser['username']); ?>
                    <span class="dropdown-arrow">▼</span>
                </a>
                <ul class="nav-dropdown-menu" id="user-dropdown">
                    <li>
                        <a href="<?php echo SITE_URL; ?>auth/profile.php" class="nav-dropdown-link">
                            Profile
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo SITE_URL; ?>logout.php?confirm=1" 
                           class="nav-dropdown-link"
                           onclick="return confirm('Are you sure you want to logout?');">
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
/* FIXED Navigation Styles */
.nav-list {
    display: flex;
    list-style: none;
    gap: 1rem;
    margin: 0;
    padding: 0;
    align-items: center;
}

.nav-item {
    position: relative;
}

.nav-dropdown {
    position: relative;
}

.nav-dropdown-toggle {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    cursor: pointer;
}

.dropdown-arrow {
    font-size: 0.75rem;
    transition: transform 0.2s ease;
}

/* Dropdown Menu */
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
    list-style: none;
}

.nav-dropdown-menu li {
    width: 100%;
    margin: 0;
    padding: 0;
}

.nav-dropdown-link {
    display: block;
    padding: 0.75rem 1rem;
    color: #1e293b !important;
    text-decoration: none;
    font-size: 0.9rem;
    transition: background-color 0.2s ease;
    border-radius: 0;
    background: none !important;
}

.nav-dropdown-link:hover {
    background-color: #f1f5f9 !important;
    color: #334155 !important;
}

/* Show dropdown on CLICK only - NO HOVER */
.nav-dropdown-menu {
    display: none;
}

.nav-dropdown-menu.show {
    display: block;
}

/* Cart count styling */
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
    line-height: 1;
}

.cart-count.has-items {
    display: inline-block;
}

/* FIXED Mobile Navigation */
@media (max-width: 768px) {
    .nav {
        display: none;
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background-color: #334155;
        border-top: 1px solid #475569;
        z-index: 999;
    }
    
    .nav.active {
        display: block;
    }
    
    .nav-list {
        flex-direction: column;
        gap: 0;
        width: 100%;
        padding: 0;
    }
    
    .nav-item {
        width: 100%;
        border-bottom: 1px solid #475569;
    }
    
    .nav-item:last-child {
        border-bottom: none;
    }
    
    .nav-link {
        display: block;
        padding: 1rem;
        width: 100%;
        color: white !important;
        text-decoration: none;
        transition: background-color 0.2s ease;
    }
    
    .nav-link:hover {
        background-color: #475569;
    }
    
    .nav-link.active {
        background-color: #64748b;
    }
    
    /* Mobile dropdown */
    .nav-dropdown-menu {
        position: static;
        display: none;
        background-color: #475569;
        border: none;
        box-shadow: none;
        border-radius: 0;
        margin: 0;
        padding: 0;
    }
    
    .nav-dropdown-menu.show {
        display: block;
    }
    
    .nav-dropdown-link {
        padding: 0.75rem 2rem !important;
        background-color: #475569 !important;
        color: #e2e8f0 !important;
        border-bottom: 1px solid #64748b;
    }
    
    .nav-dropdown-link:hover {
        background-color: #64748b !important;
        color: white !important;
    }
    
    .dropdown-arrow {
        margin-left: auto;
    }
    
    .cart-count {
        position: relative;
        top: auto;
        right: auto;
        margin-left: 0.5rem;
    }
}
</style>

<script>
// FIXED: Click-only dropdown function
function toggleDropdown(event) {
    event.preventDefault();
    const dropdown = document.getElementById('user-dropdown');
    const arrow = event.target.querySelector('.dropdown-arrow') || event.target.parentNode.querySelector('.dropdown-arrow');
    
    if (dropdown.classList.contains('show')) {
        dropdown.classList.remove('show');
        if (arrow) arrow.style.transform = 'rotate(0deg)';
    } else {
        dropdown.classList.add('show');
        if (arrow) arrow.style.transform = 'rotate(180deg)';
    }
}

// Close dropdown when clicking outside
document.addEventListener('click', function(event) {
    const dropdown = document.getElementById('user-dropdown');
    const toggle = document.querySelector('.nav-dropdown-toggle');
    
    if (dropdown && toggle && !toggle.contains(event.target) && !dropdown.contains(event.target)) {
        dropdown.classList.remove('show');
        const arrow = toggle.querySelector('.dropdown-arrow');
        if (arrow) arrow.style.transform = 'rotate(0deg)';
    }
});

document.addEventListener('DOMContentLoaded', function() {
    // Update cart count on page load
    if (typeof updateCartCount === 'function') {
        updateCartCount();
    }
});

// Make functions globally available for other scripts
window.updateCartCount = window.updateCartCount || function() {
    console.log('updateCartCount function not yet loaded');
};

window.updateCartCountDisplay = window.updateCartCountDisplay || function(count) {
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
};
</script>