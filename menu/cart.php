<?php
/**
 * Shopping Cart Page
 * Online Food Ordering System - Phase 3
 * 
 * Display and manage shopping cart items
 */

require_once '../config.php';
require_once '../functions.php';

// Require customer login
requireLogin();
if (!isCustomer()) {
    header('Location: ' . SITE_URL . 'auth/login.php');
    exit();
}

$currentUser = getCurrentUser();
$errors = [];
$success = false;

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle cart actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        $action = $_POST['action'] ?? '';
        
        switch ($action) {
            case 'add_item':
                $itemId = (int)($_POST['item_id'] ?? 0);
                $quantity = (int)($_POST['quantity'] ?? 1);
                
                if ($itemId > 0 && $quantity > 0) {
                    $item = getMenuItemById($itemId);
                    if ($item && $item['availability'] === 'available') {
                        addToCart($itemId, $quantity);
                        $_SESSION['flash_message']['success'] = 'Item added to cart!';
                    } else {
                        $errors[] = 'Item not available.';
                    }
                }
                break;
                
            case 'update_quantity':
                $itemId = (int)($_POST['item_id'] ?? 0);
                $quantity = (int)($_POST['quantity'] ?? 1);
                
                if ($itemId > 0) {
                    if ($quantity > 0) {
                        updateCartQuantity($itemId, $quantity);
                        $_SESSION['flash_message']['success'] = 'Cart updated!';
                    } else {
                        removeFromCart($itemId);
                        $_SESSION['flash_message']['success'] = 'Item removed from cart!';
                    }
                }
                break;
                
            case 'remove_item':
                $itemId = (int)($_POST['item_id'] ?? 0);
                if ($itemId > 0) {
                    removeFromCart($itemId);
                    $_SESSION['flash_message']['success'] = 'Item removed from cart!';
                }
                break;
                
            case 'clear_cart':
                $_SESSION['cart'] = [];
                $_SESSION['flash_message']['success'] = 'Cart cleared!';
                break;
        }
        
        header('Location: cart.php');
        exit();
    }
}

// Get cart items with details
$cartItems = getCartItems();
$cartTotal = getCartTotal();
$cartCount = getCartCount();

// Page configuration
$pageTitle = 'Shopping Cart';
$bodyClass = 'cart-page';

// Include header
include '../includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h1 class="page-title">Shopping Cart</h1>
        <p class="page-subtitle">Review your items before checkout</p>
    </div>
    
    <!-- Display Errors -->
    <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <ul style="margin: 0; padding-left: 1.5rem;">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <?php if (empty($cartItems)): ?>
        <!-- Empty Cart -->
        <div class="empty-cart">
            <div class="empty-cart-icon">🛒</div>
            <h2>Your cart is empty</h2>
            <p>Add some delicious items to your cart to get started!</p>
            <a href="<?php echo SITE_URL; ?>menu/menu.php" class="btn btn-primary btn-large">
                Browse Menu
            </a>
        </div>
    <?php else: ?>
        <!-- Cart Items -->
        <div class="cart-container">
            <div class="cart-items">
                <div class="cart-header">
                    <h3>Cart Items (<?php echo $cartCount; ?>)</h3>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        <input type="hidden" name="action" value="clear_cart">
                        <button type="submit" class="btn btn-small btn-danger" data-confirm="Clear all items from cart?">
                            Clear Cart
                        </button>
                    </form>
                </div>
                
                <?php foreach ($cartItems as $item): ?>
                    <div class="cart-item">
                        <div class="item-image">
                            <?php if ($item['image_url']): ?>
                                <img src="<?php echo SITE_URL . UPLOAD_PATH . 'menu/' . $item['image_url']; ?>" 
                                     alt="<?php echo htmlspecialchars($item['item_name']); ?>">
                            <?php else: ?>
                                <div class="placeholder-image">🍽️</div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="item-info">
                            <h4 class="item-name"><?php echo htmlspecialchars($item['item_name']); ?></h4>
                            <p class="item-category"><?php echo htmlspecialchars($item['category_name']); ?></p>
                            <p class="item-price"><?php echo formatCurrency($item['price']); ?> each</p>
                        </div>
                        
                        <div class="item-quantity">
                            <form method="POST" class="quantity-form">
                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                <input type="hidden" name="action" value="update_quantity">
                                <input type="hidden" name="item_id" value="<?php echo $item['item_id']; ?>">
                                
                                <div class="quantity-controls">
                                    <button type="button" class="quantity-btn minus" data-item-id="<?php echo $item['item_id']; ?>">-</button>
                                    <input type="number" 
                                           name="quantity" 
                                           value="<?php echo $item['quantity']; ?>" 
                                           min="1" 
                                           max="10" 
                                           class="quantity-input">
                                    <button type="button" class="quantity-btn plus" data-item-id="<?php echo $item['item_id']; ?>">+</button>
                                </div>
                            </form>
                        </div>
                        
                        <div class="item-total">
                            <span class="total-price"><?php echo formatCurrency($item['price'] * $item['quantity']); ?></span>
                        </div>
                        
                        <div class="item-actions">
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                <input type="hidden" name="action" value="remove_item">
                                <input type="hidden" name="item_id" value="<?php echo $item['item_id']; ?>">
                                <button type="submit" class="btn btn-small btn-danger" data-confirm="Remove this item?">
                                    Remove
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Cart Summary -->
            <div class="cart-summary">
                <div class="summary-card">
                    <h3>Order Summary</h3>
                    
                    <div class="summary-line">
                        <span>Subtotal (<?php echo $cartCount; ?> items)</span>
                        <span><?php echo formatCurrency($cartTotal); ?></span>
                    </div>
                    
                    <div class="summary-line">
                        <span>Delivery Fee</span>
                        <span><?php echo formatCurrency(5.00); ?></span>
                    </div>
                    
                    <div class="summary-line">
                        <span>Service Tax (6%)</span>
                        <span><?php echo formatCurrency($cartTotal * 0.06); ?></span>
                    </div>
                    
                    <div class="summary-line total-line">
                        <span>Total</span>
                        <span><?php echo formatCurrency($cartTotal + 5.00 + ($cartTotal * 0.06)); ?></span>
                    </div>
                    
                    <a href="checkout.php" class="btn btn-primary btn-full btn-large">
                        Proceed to Checkout
                    </a>
                    
                    <a href="<?php echo SITE_URL; ?>menu/menu.php" class="btn btn-outline btn-full">
                        Continue Shopping
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
.empty-cart {
    text-align: center;
    padding: 4rem 2rem;
    color: #64748b;
}

.empty-cart-icon {
    font-size: 4rem;
    margin-bottom: 1rem;
}

.cart-container {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 2rem;
}

.cart-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #e2e8f0;
}

.cart-item {
    display: grid;
    grid-template-columns: 80px 1fr auto auto auto;
    gap: 1rem;
    align-items: center;
    padding: 1rem;
    border: 1px solid #e2e8f0;
    border-radius: 0.5rem;
    margin-bottom: 1rem;
}

.item-image {
    width: 80px;
    height: 80px;
    overflow: hidden;
    border-radius: 0.375rem;
}

.item-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.placeholder-image {
    width: 100%;
    height: 100%;
    background: #f1f5f9;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    color: #94a3b8;
}

.item-name {
    font-size: 1.1rem;
    font-weight: 600;
    margin-bottom: 0.25rem;
}

.item-category {
    color: #64748b;
    font-size: 0.9rem;
    margin-bottom: 0.25rem;
}

.item-price {
    font-weight: 500;
    color: #334155;
}

.quantity-controls {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.quantity-btn {
    width: 30px;
    height: 30px;
    border: 1px solid #e2e8f0;
    background: white;
    border-radius: 0.25rem;
    cursor: pointer;
    font-weight: bold;
}

.quantity-input {
    width: 60px;
    text-align: center;
    padding: 0.25rem;
    border: 1px solid #e2e8f0;
    border-radius: 0.25rem;
}

.total-price {
    font-weight: 600;
    font-size: 1.1rem;
    color: #334155;
}

.summary-card {
    background: white;
    padding: 1.5rem;
    border-radius: 0.5rem;
    border: 1px solid #e2e8f0;
    position: sticky;
    top: 2rem;
}

.summary-line {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.75rem;
}

.total-line {
    font-weight: 600;
    font-size: 1.1rem;
    border-top: 1px solid #e2e8f0;
    padding-top: 0.75rem;
    margin-top: 0.75rem;
}

@media (max-width: 768px) {
    .cart-container {
        grid-template-columns: 1fr;
    }
    
    .cart-item {
        grid-template-columns: 60px 1fr;
        gap: 0.75rem;
    }
    
    .item-quantity,
    .item-total,
    .item-actions {
        grid-column: 1 / -1;
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 0.5rem;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Quantity controls
    const quantityBtns = document.querySelectorAll('.quantity-btn');
    
    quantityBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const isPlus = btn.classList.contains('plus');
            const isMinus = btn.classList.contains('minus');
            const input = btn.parentNode.querySelector('.quantity-input');
            const form = btn.closest('.quantity-form');
            
            let currentValue = parseInt(input.value);
            
            if (isPlus && currentValue < 10) {
                input.value = currentValue + 1;
            } else if (isMinus && currentValue > 1) {
                input.value = currentValue - 1;
            }
            
            // Auto-submit form
            setTimeout(() => {
                form.submit();
            }, 500);
        });
    });
    
    // Auto-submit on quantity input change
    const quantityInputs = document.querySelectorAll('.quantity-input');
    quantityInputs.forEach(input => {
        input.addEventListener('change', function() {
            const form = input.closest('.quantity-form');
            form.submit();
        });
    });
});
</script>

<?php
// Include footer
include '../includes/footer.php';
?>