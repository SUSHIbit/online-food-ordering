<?php
/**
 * Fixed Navigation Component
 * Online Food Ordering System - Bug Fixes
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
                <a href="#" class="nav-link nav-dropdown-toggle" onclick="return false;">
                    <?php echo htmlspecialchars($currentUser['full_name']); ?>
                    <span class="dropdown-arrow">▼</span>
                </a>
                <ul class="nav-dropdown-menu">
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
/* Fixed Navigation Styles */
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

/* FIXED DROPDOWN MENU */
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

/* Show dropdown on hover with better timing */
.nav-dropdown:hover .nav-dropdown-menu {
    display: block;
}

.nav-dropdown:hover .dropdown-arrow {
    transform: rotate(180deg);
}

/* Keep dropdown visible when hovering over menu */
.nav-dropdown-menu:hover {
    display: block;
}

/* Cart count styling - FIXED */
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

/* Mobile Navigation */
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
        padding-left: 2rem !important;
        background-color: #475569 !important;
        color: #e2e8f0 !important;
    }
    
    .nav-dropdown-link:hover {
        background-color: #64748b !important;
        color: white !important;
    }
    
    .dropdown-arrow {
        margin-left: auto;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Update cart count on page load
    updateCartCount();
    
    // Mobile dropdown functionality
    const dropdownToggles = document.querySelectorAll('.nav-dropdown-toggle');
    
    dropdownToggles.forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            
            if (window.innerWidth <= 768) {
                const dropdown = toggle.parentNode;
                const menu = dropdown.querySelector('.nav-dropdown-menu');
                
                // Toggle menu visibility
                if (menu.classList.contains('show')) {
                    menu.classList.remove('show');
                    toggle.querySelector('.dropdown-arrow').style.transform = 'rotate(0deg)';
                } else {
                    menu.classList.add('show');
                    toggle.querySelector('.dropdown-arrow').style.transform = 'rotate(180deg)';
                }
            }
        });
    });
});

// FIXED: Update cart count function
function updateCartCount() {
    const cartCountElement = document.getElementById('cart-count');
    if (!cartCountElement) return;
    
    <?php if (isCustomer()): ?>
        // Make AJAX request to get cart count
        fetch('<?php echo SITE_URL; ?>ajax/get_cart_count.php', {
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
            console.log('Cart count update failed:', error);
            cartCountElement.style.display = 'none';
        });
    <?php else: ?>
        // Not a customer, hide cart count
        cartCountElement.style.display = 'none';
    <?php endif; ?>
}

// FIXED: Update cart count display
function updateCartCountDisplay(count) {
    const cartCountElement = document.getElementById('cart-count');
    if (cartCountElement) {
        if (count > 0) {
            cartCountElement.textContent = count;
            cartCountElement.classList.add('has-items');
            cartCountElement.style.display = 'flex';
        } else {
            cartCountElement.classList.remove('has-items');
            cartCountElement.style.display = 'none';
        }
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // Update cart count on page load
    updateCartCount();
    
    // Enhanced dropdown handling for stability
    const dropdowns = document.querySelectorAll('.nav-dropdown');
    
    dropdowns.forEach(dropdown => {
        const toggle = dropdown.querySelector('.nav-dropdown-toggle');
        const menu = dropdown.querySelector('.nav-dropdown-menu');
        const arrow = dropdown.querySelector('.dropdown-arrow');
        
        if (!toggle || !menu) return;
        
        let hoverTimeout;
        let isMenuOpen = false;
        
        // Mouse enter on dropdown container
        dropdown.addEventListener('mouseenter', function() {
            clearTimeout(hoverTimeout);
            if (!isMenuOpen) {
                showDropdown();
            }
        });
        
        // Mouse leave on dropdown container
        dropdown.addEventListener('mouseleave', function() {
            hoverTimeout = setTimeout(() => {
                hideDropdown();
            }, 150); // Small delay to prevent accidental closes
        });
        
        // Keep menu open when hovering over it
        menu.addEventListener('mouseenter', function() {
            clearTimeout(hoverTimeout);
        });
        
        // Close when leaving menu
        menu.addEventListener('mouseleave', function() {
            hoverTimeout = setTimeout(() => {
                hideDropdown();
            }, 100);
        });
        
        // Click handling for mobile
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            
            if (window.innerWidth <= 768) {
                // Mobile behavior
                if (menu.classList.contains('show')) {
                    hideDropdown();
                } else {
                    showDropdown();
                }
            }
        });
        
        // Prevent menu clicks from closing dropdown
        menu.addEventListener('click', function(e) {
            e.stopPropagation();
        });
        
        function showDropdown() {
            menu.style.display = 'block';
            menu.classList.add('show');
            if (arrow) arrow.style.transform = 'rotate(180deg)';
            isMenuOpen = true;
        }
        
        function hideDropdown() {
            menu.style.display = 'none';
            menu.classList.remove('show');
            if (arrow) arrow.style.transform = 'rotate(0deg)';
            isMenuOpen = false;
        }
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!dropdown.contains(e.target)) {
                hideDropdown();
            }
        });
    });
    
    // Mobile navigation toggle
    const mobileToggle = document.querySelector('.mobile-nav-toggle');
    const nav = document.querySelector('.nav');
    
    if (mobileToggle && nav) {
        mobileToggle.addEventListener('click', function() {
            nav.classList.toggle('active');
            
            const icon = mobileToggle.querySelector('i') || mobileToggle;
            if (nav.classList.contains('active')) {
                icon.innerHTML = '✕';
            } else {
                icon.innerHTML = '☰';
            }
        });
        
        // Close mobile menu when clicking on nav links
        const navLinks = document.querySelectorAll('.nav-link');
        navLinks.forEach(link => {
            link.addEventListener('click', function() {
                if (window.innerWidth <= 768) {
                    nav.classList.remove('active');
                    const icon = mobileToggle.querySelector('i') || mobileToggle;
                    icon.innerHTML = '☰';
                }
            });
        });
    }
});

// Make functions globally available
window.updateCartCount = updateCartCount;
window.updateCartCountDisplay = updateCartCountDisplay;
</script>