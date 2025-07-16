<?php
/**
 * Homepage - Online Food Ordering System
 * Phase 1: Foundation & Authentication
 * 
 * Main landing page with hero section and feature highlights
 */

require_once 'config.php';
require_once 'functions.php';

// Page configuration
$pageTitle = 'Welcome';
$bodyClass = 'homepage';

// Include header
include 'includes/header.php';
?>

<!-- Hero Section -->
<section class="hero">
    <div class="container">
        <h1 class="hero-title">Delicious Food, Delivered Fresh</h1>
        <p class="hero-subtitle">
            Order your favorite meals online and enjoy fast, reliable delivery right to your doorstep
        </p>
        <div class="hero-cta">
            <?php if (isLoggedIn()): ?>
                <a href="<?php echo SITE_URL; ?>menu/menu.php" class="btn btn-primary btn-large">
                    Browse Menu
                </a>
                <?php if (isCustomer()): ?>
                    <a href="<?php echo SITE_URL; ?>orders/orders.php" class="btn btn-outline btn-large">
                        My Orders
                    </a>
                <?php endif; ?>
            <?php else: ?>
                <a href="<?php echo SITE_URL; ?>auth/register.php" class="btn btn-primary btn-large">
                    Get Started
                </a>
                <a href="<?php echo SITE_URL; ?>menu/menu.php" class="btn btn-outline btn-large">
                    Browse Menu
                </a>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="features">
    <div class="container">
        <div class="page-header text-center">
            <h2 class="page-title">Why Choose Us?</h2>
            <p class="page-subtitle">
                Experience the best food ordering service with our premium features
            </p>
        </div>
        
        <div class="grid grid-3">
            <!-- Feature 1: Fresh Ingredients -->
            <div class="feature-card">
                <div class="feature-icon">🥗</div>
                <h3 class="feature-title">Fresh Ingredients</h3>
                <p class="feature-description">
                    We source only the freshest ingredients from local suppliers to ensure 
                    the highest quality in every dish we prepare.
                </p>
            </div>
            
            <!-- Feature 2: Fast Delivery -->
            <div class="feature-card">
                <div class="feature-icon">🚚</div>
                <h3 class="feature-title">Fast Delivery</h3>
                <p class="feature-description">
                    Quick and reliable delivery service that brings your favorite meals 
                    to your doorstep within 30-45 minutes.
                </p>
            </div>
            
            <!-- Feature 3: Easy Ordering -->
            <div class="feature-card">
                <div class="feature-icon">📱</div>
                <h3 class="feature-title">Easy Ordering</h3>
                <p class="feature-description">
                    Simple and intuitive ordering process that lets you place orders 
                    quickly with just a few clicks.
                </p>
            </div>
            
            <!-- Feature 4: Secure Payment -->
            <div class="feature-card">
                <div class="feature-icon">🔒</div>
                <h3 class="feature-title">Secure Payment</h3>
                <p class="feature-description">
                    Safe and secure payment processing that protects your financial 
                    information with advanced encryption.
                </p>
            </div>
            
            <!-- Feature 5: Order Tracking -->
            <div class="feature-card">
                <div class="feature-icon">📍</div>
                <h3 class="feature-title">Order Tracking</h3>
                <p class="feature-description">
                    Real-time order tracking that keeps you updated on your order 
                    status from preparation to delivery.
                </p>
            </div>
            
            <!-- Feature 6: 24/7 Support -->
            <div class="feature-card">
                <div class="feature-icon">💬</div>
                <h3 class="feature-title">24/7 Support</h3>
                <p class="feature-description">
                    Round-the-clock customer support to assist you with any questions 
                    or concerns about your orders.
                </p>
            </div>
        </div>
    </div>
</section>

<?php if (isLoggedIn()): ?>
    <!-- Welcome Back Section for Logged-in Users -->
    <section class="welcome-back">
        <div class="container">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        Welcome back, <?php echo htmlspecialchars(getCurrentUser()['full_name']); ?>!
                    </h3>
                </div>
                <div class="card-body">
                    <div class="grid grid-2">
                        <div>
                            <p class="text-slate-600 mb-4">
                                Ready to order some delicious food? Browse our menu or check your recent orders.
                            </p>
                            <div class="flex gap-3">
                                <a href="<?php echo SITE_URL; ?>menu/menu.php" class="btn btn-primary">
                                    Browse Menu
                                </a>
                                <?php if (isCustomer()): ?>
                                    <a href="<?php echo SITE_URL; ?>orders/orders.php" class="btn btn-secondary">
                                        My Orders
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="text-center">
                            <div class="feature-icon" style="font-size: 4rem; margin-bottom: 1rem;">🍽️</div>
                            <p class="text-slate-500">
                                <?php if (isAdmin()): ?>
                                    <strong>Admin Dashboard:</strong><br>
                                    Manage orders, menu items, and system settings.
                                <?php else: ?>
                                    <strong>Customer Account:</strong><br>
                                    Enjoy personalized recommendations and order history.
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
<?php else: ?>
    <!-- Call to Action Section for Guests -->
    <section class="cta-section">
        <div class="container">
            <div class="card">
                <div class="card-body text-center">
                    <h3 class="card-title mb-4">Ready to Get Started?</h3>
                    <p class="text-slate-600 mb-4">
                        Join thousands of satisfied customers who trust us for their daily meals. 
                        Create your account today and enjoy exclusive benefits!
                    </p>
                    <div class="flex-center gap-3">
                        <a href="<?php echo SITE_URL; ?>auth/register.php" class="btn btn-primary btn-large">
                            Create Account
                        </a>
                        <a href="<?php echo SITE_URL; ?>auth/login.php" class="btn btn-outline btn-large">
                            Sign In
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
<?php endif; ?>

<!-- Statistics Section -->
<section class="stats-section">
    <div class="container">
        <div class="grid grid-4">
            <div class="stat-card text-center">
                <div class="stat-number">500+</div>
                <div class="stat-label">Happy Customers</div>
            </div>
            <div class="stat-card text-center">
                <div class="stat-number">50+</div>
                <div class="stat-label">Menu Items</div>
            </div>
            <div class="stat-card text-center">
                <div class="stat-number">24/7</div>
                <div class="stat-label">Service Hours</div>
            </div>
            <div class="stat-card text-center">
                <div class="stat-number">30min</div>
                <div class="stat-label">Avg Delivery</div>
            </div>
        </div>
    </div>
</section>

<style>
/* Homepage Specific Styles */
.welcome-back {
    padding: 3rem 0;
    background-color: #f8fafc;
}

.cta-section {
    padding: 3rem 0;
    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
}

.stats-section {
    padding: 3rem 0;
    background-color: #334155;
    color: white;
}

.stat-card {
    padding: 2rem;
}

.stat-number {
    font-size: 2.5rem;
    font-weight: bold;
    color: white;
    margin-bottom: 0.5rem;
}

.stat-label {
    font-size: 1rem;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

@media (max-width: 768px) {
    .stat-number {
        font-size: 2rem;
    }
    
    .stat-label {
        font-size: 0.875rem;
    }
}
</style>

<?php
// Include footer
include 'includes/footer.php';
?>