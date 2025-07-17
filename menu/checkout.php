<?php
/**
 * FIXED Checkout Page - Auto Status Update
 * Online Food Ordering System - Phase 3
 * 
 * Order placement and payment processing with automatic status updates
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
$cartItems = getCartItems();
$cartTotal = getCartTotal();
$cartCount = getCartCount();

// Redirect if cart is empty
if (empty($cartItems)) {
    header('Location: cart.php');
    exit();
}

$errors = [];
$success = false;

// Handle order submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        // Get and validate form data
        $orderData = [
            'user_id' => $currentUser['user_id'],
            'delivery_address' => cleanInput($_POST['delivery_address'] ?? ''),
            'phone' => cleanInput($_POST['phone'] ?? ''),
            'notes' => cleanInput($_POST['notes'] ?? ''),
            'payment_method' => cleanInput($_POST['payment_method'] ?? 'cash')
        ];
        
        // Validation
        if (empty($orderData['delivery_address'])) {
            $errors[] = 'Delivery address is required.';
        }
        
        if (empty($orderData['phone'])) {
            $errors[] = 'Phone number is required.';
        } elseif (!validatePhone($orderData['phone'])) {
            $errors[] = 'Please enter a valid phone number.';
        }
        
        if (!in_array($orderData['payment_method'], ['cash', 'online'])) {
            $errors[] = 'Invalid payment method.';
        }
        
        // Create order if no errors
        if (empty($errors)) {
            $orderId = createOrderFromCart($orderData);
            
            if ($orderId) {
                // Automatically update order status based on payment method
                if ($orderData['payment_method'] === 'online') {
                    // For online payment, mark as confirmed and paid
                    updateOrderStatus($orderId, 'confirmed');
                    updateOrderPaymentStatus($orderId, 'paid');
                    addOrderStatusHistory($orderId, 'confirmed', 'Order confirmed - Online payment received', $currentUser['user_id']);
                    
                    $_SESSION['flash_message']['success'] = 'Order placed and payment confirmed! Your order #' . $orderId . ' is being prepared.';
                } else {
                    // For cash on delivery, confirm the order but payment is pending
                    updateOrderStatus($orderId, 'confirmed');
                    addOrderStatusHistory($orderId, 'confirmed', 'Order confirmed - Cash on delivery', $currentUser['user_id']);
                    
                    $_SESSION['flash_message']['success'] = 'Order placed successfully! Your order #' . $orderId . ' has been confirmed.';
                }
                
                header('Location: ../orders/order-details.php?id=' . $orderId);
                exit();
            } else {
                $errors[] = 'Failed to place order. Please try again.';
            }
        }
    }
}

// Calculate totals
$subtotal = $cartTotal;
$deliveryFee = 5.00;
$tax = $subtotal * 0.06;
$grandTotal = $subtotal + $deliveryFee + $tax;

// Page configuration
$pageTitle = 'Checkout';
$bodyClass = 'checkout-page';

// Include header
include '../includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h1 class="page-title">Checkout</h1>
        <p class="page-subtitle">Complete your order</p>
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
    
    <div class="checkout-container">
        <!-- Order Form -->
        <div class="checkout-form">
            <form method="POST" action="" data-validate>
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                
                <!-- Delivery Information -->
                <div class="form-section">
                    <h3 class="section-title">Delivery Information</h3>
                    
                    <div class="form-group">
                        <label for="delivery_address" class="form-label">Delivery Address *</label>
                        <textarea id="delivery_address" 
                                  name="delivery_address" 
                                  class="form-input form-textarea" 
                                  rows="3" 
                                  required 
                                  placeholder="Enter your complete delivery address"><?php echo htmlspecialchars($currentUser['address'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone" class="form-label">Phone Number *</label>
                        <input type="tel" 
                               id="phone" 
                               name="phone" 
                               class="form-input" 
                               value="<?php echo htmlspecialchars($currentUser['phone'] ?? ''); ?>"
                               required 
                               placeholder="+60123456789">
                    </div>
                    
                    <div class="form-group">
                        <label for="notes" class="form-label">Special Instructions</label>
                        <textarea id="notes" 
                                  name="notes" 
                                  class="form-input form-textarea" 
                                  rows="2" 
                                  placeholder="Any special instructions for delivery or preparation"></textarea>
                    </div>
                </div>
                
                <!-- Payment Method -->
                <div class="form-section">
                    <h3 class="section-title">Payment Method</h3>
                    
                    <div class="payment-options">
                        <label class="payment-option">
                            <input type="radio" name="payment_method" value="cash" checked>
                            <span class="payment-info">
                                <strong>Cash on Delivery</strong>
                                <small>Pay when your order arrives • Order will be confirmed immediately</small>
                            </span>
                        </label>
                        
                        <label class="payment-option">
                            <input type="radio" name="payment_method" value="online">
                            <span class="payment-info">
                                <strong>Online Payment (Instant Confirmation)</strong>
                                <small>Pay now with card or e-wallet • Order confirmed immediately</small>
                            </span>
                        </label>
                    </div>
                    
                    <!-- Payment Notice -->
                    <div class="payment-notice">
                        <div class="notice-icon">ℹ️</div>
                        <div class="notice-content">
                            <strong>Order Processing:</strong>
                            <p>• <strong>Cash on Delivery:</strong> Order confirmed immediately, pay upon delivery</p>
                            <p>• <strong>Online Payment:</strong> Order confirmed and payment processed instantly</p>
                        </div>
                    </div>
                </div>
                
                <!-- Place Order Button -->
                <div class="form-section">
                    <button type="submit" class="btn btn-primary btn-full btn-large">
                        Place Order - <?php echo formatCurrency($grandTotal); ?>
                    </button>
                    <p class="order-confirmation-text">
                        By placing this order, you agree to our terms and conditions. 
                        Your order will be confirmed immediately and preparation will begin.
                    </p>
                </div>
            </form>
        </div>
        
        <!-- Order Summary -->
        <div class="order-summary">
            <div class="summary-card">
                <h3>Order Summary</h3>
                
                <!-- Order Items -->
                <div class="order-items">
                    <?php foreach ($cartItems as $item): ?>
                        <div class="summary-item">
                            <div class="item-info">
                                <span class="item-name"><?php echo htmlspecialchars($item['item_name']); ?></span>
                                <span class="item-quantity">x<?php echo $item['quantity']; ?></span>
                            </div>
                            <span class="item-total"><?php echo formatCurrency($item['price'] * $item['quantity']); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Totals -->
                <div class="summary-totals">
                    <div class="total-line">
                        <span>Subtotal (<?php echo $cartCount; ?> items)</span>
                        <span><?php echo formatCurrency($subtotal); ?></span>
                    </div>
                    <div class="total-line">
                        <span>Delivery Fee</span>
                        <span><?php echo formatCurrency($deliveryFee); ?></span>
                    </div>
                    <div class="total-line">
                        <span>Service Tax (6%)</span>
                        <span><?php echo formatCurrency($tax); ?></span>
                    </div>
                    <div class="total-line grand-total">
                        <span>Grand Total</span>
                        <span><?php echo formatCurrency($grandTotal); ?></span>
                    </div>
                </div>
                
                <!-- Delivery Info -->
                <div class="delivery-info">
                    <h4>Delivery Information</h4>
                    <p><strong>Estimated Time:</strong> 30-45 minutes</p>
                    <p><strong>Delivery Hours:</strong> 10:00 AM - 10:00 PM</p>
                    <p><strong>Order Status:</strong> Confirmed immediately upon submission</p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.checkout-container {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 2rem;
    max-width: 1000px;
    margin: 0 auto;
}

.checkout-form {
    background: white;
    padding: 2rem;
    border-radius: 0.5rem;
    border: 1px solid #e2e8f0;
    height: fit-content;
}

.form-section {
    margin-bottom: 2rem;
    padding-bottom: 2rem;
    border-bottom: 1px solid #f1f5f9;
}

.form-section:last-child {
    border-bottom: none;
    margin-bottom: 0;
}

.section-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 1rem;
}

.payment-options {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.payment-option {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    border: 2px solid #e2e8f0;
    border-radius: 0.5rem;
    cursor: pointer;
    transition: all 0.2s ease;
}

.payment-option:hover {
    border-color: #334155;
}

.payment-option input[type="radio"]:checked + .payment-info {
    color: #334155;
}

.payment-option input[type="radio"]:checked ~ * {
    border-color: #334155;
}

.payment-info {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.payment-info strong {
    font-weight: 600;
}

.payment-info small {
    color: #64748b;
    font-size: 0.875rem;
}

.payment-notice {
    display: flex;
    gap: 1rem;
    padding: 1rem;
    background: #f0f9ff;
    border: 1px solid #bae6fd;
    border-radius: 0.5rem;
    color: #0c4a6e;
}

.notice-icon {
    font-size: 1.25rem;
    flex-shrink: 0;
}

.notice-content p {
    margin: 0.25rem 0;
    font-size: 0.875rem;
}

.order-confirmation-text {
    font-size: 0.875rem;
    color: #64748b;
    text-align: center;
    margin-top: 1rem;
    margin-bottom: 0;
}

.summary-card {
    background: white;
    padding: 1.5rem;
    border-radius: 0.5rem;
    border: 1px solid #e2e8f0;
    position: sticky;
    top: 2rem;
}

.summary-card h3 {
    margin-bottom: 1rem;
    color: #1e293b;
}

.order-items {
    margin-bottom: 1.5rem;
}

.summary-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.75rem;
}

.item-info {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.item-name {
    font-weight: 500;
    color: #1e293b;
}

.item-quantity {
    font-size: 0.875rem;
    color: #64748b;
}

.item-total {
    font-weight: 500;
    color: #334155;
}

.summary-totals {
    border-top: 1px solid #e2e8f0;
    padding-top: 1rem;
}

.total-line {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.5rem;
    color: #64748b;
}

.grand-total {
    font-weight: 600;
    font-size: 1.1rem;
    color: #1e293b;
    border-top: 1px solid #e2e8f0;
    padding-top: 0.5rem;
    margin-top: 0.5rem;
}

.delivery-info {
    margin-top: 1.5rem;
    padding-top: 1.5rem;
    border-top: 1px solid #e2e8f0;
}

.delivery-info h4 {
    margin-bottom: 0.5rem;
    color: #1e293b;
}

.delivery-info p {
    margin-bottom: 0.25rem;
    font-size: 0.875rem;
    color: #64748b;
}

@media (max-width: 768px) {
    .checkout-container {
        grid-template-columns: 1fr;
    }
    
    .checkout-form {
        padding: 1.5rem;
    }
    
    .payment-option {
        flex-direction: column;
        align-items: flex-start;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Form validation
    const form = document.querySelector('form[data-validate]');
    if (form) {
        form.addEventListener('submit', function(e) {
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                FoodOrderingApp.showLoading(submitBtn);
                
                // Add processing message
                const processingMsg = document.createElement('div');
                processingMsg.className = 'alert alert-info';
                processingMsg.style.marginTop = '1rem';
                processingMsg.innerHTML = '⏳ Processing your order... Please wait.';
                submitBtn.parentNode.appendChild(processingMsg);
            }
        });
    }
    
    // Payment method selection
    const paymentOptions = document.querySelectorAll('input[name="payment_method"]');
    paymentOptions.forEach(option => {
        option.addEventListener('change', function() {
            // Remove active class from all options
            document.querySelectorAll('.payment-option').forEach(opt => {
                opt.classList.remove('active');
            });
            
            // Add active class to selected option
            this.closest('.payment-option').classList.add('active');
            
            // Update button text based on payment method
            const submitBtn = document.querySelector('button[type="submit"]');
            if (this.value === 'online') {
                submitBtn.innerHTML = 'Pay Now - <?php echo formatCurrency($grandTotal); ?>';
            } else {
                submitBtn.innerHTML = 'Place Order - <?php echo formatCurrency($grandTotal); ?>';
            }
        });
    });
});
</script>

<?php
// Include footer
include '../includes/footer.php';
?>