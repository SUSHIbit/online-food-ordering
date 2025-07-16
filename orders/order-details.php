<?php
/**
 * Order Details Page
 * Online Food Ordering System - Phase 3
 * 
 * Display detailed order information
 */

require_once '../config.php';
require_once '../functions.php';

// Require login
requireLogin();

$orderId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$orderId) {
    header('Location: orders.php');
    exit();
}

// Get order details
$order = getOrderById($orderId);
if (!$order) {
    header('Location: orders.php');
    exit();
}

// Check access permissions
if (isCustomer() && $order['user_id'] != getCurrentUser()['user_id']) {
    header('Location: orders.php');
    exit();
}

// Get order items
$orderItems = getOrderItems($orderId);

// Calculate totals
$subtotal = 0;
foreach ($orderItems as $item) {
    $subtotal += $item['item_price'] * $item['quantity'];
}
$deliveryFee = 5.00;
$tax = $subtotal * 0.06;

// Page configuration
$pageTitle = 'Order #' . $orderId;
$bodyClass = 'order-details-page';

// Include header
include '../includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h1 class="page-title">Order #<?php echo $orderId; ?></h1>
        <p class="page-subtitle">Order placed on <?php echo formatDateTime($order['created_at']); ?></p>
    </div>
    
    <div class="order-container">
        <!-- Order Status -->
        <div class="order-status-section">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Order Status</h3>
                </div>
                <div class="card-body">
                    <div class="status-timeline">
                        <div class="status-step <?php echo in_array($order['order_status'], ['pending', 'confirmed', 'preparing', 'ready', 'delivered']) ? 'active' : ''; ?>">
                            <div class="step-icon">📝</div>
                            <div class="step-info">
                                <strong>Order Placed</strong>
                                <small><?php echo formatDateTime($order['created_at']); ?></small>
                            </div>
                        </div>
                        
                        <div class="status-step <?php echo in_array($order['order_status'], ['confirmed', 'preparing', 'ready', 'delivered']) ? 'active' : ''; ?>">
                            <div class="step-icon">✅</div>
                            <div class="step-info">
                                <strong>Confirmed</strong>
                                <small><?php echo $order['order_status'] === 'confirmed' ? 'Current Status' : 'Completed'; ?></small>
                            </div>
                        </div>
                        
                        <div class="status-step <?php echo in_array($order['order_status'], ['preparing', 'ready', 'delivered']) ? 'active' : ''; ?>">
                            <div class="step-icon">👨‍🍳</div>
                            <div class="step-info">
                                <strong>Preparing</strong>
                                <small><?php echo $order['order_status'] === 'preparing' ? 'Current Status' : ($order['order_status'] === 'pending' || $order['order_status'] === 'confirmed' ? 'Pending' : 'Completed'); ?></small>
                            </div>
                        </div>
                        
                        <div class="status-step <?php echo in_array($order['order_status'], ['ready', 'delivered']) ? 'active' : ''; ?>">
                            <div class="step-icon">🎯</div>
                            <div class="step-info">
                                <strong>Ready</strong>
                                <small><?php echo $order['order_status'] === 'ready' ? 'Current Status' : ($order['order_status'] === 'delivered' ? 'Completed' : 'Pending'); ?></small>
                            </div>
                        </div>
                        
                        <div class="status-step <?php echo $order['order_status'] === 'delivered' ? 'active' : ''; ?>">
                            <div class="step-icon">🚚</div>
                            <div class="step-info">
                                <strong>Delivered</strong>
                                <small><?php echo $order['order_status'] === 'delivered' ? 'Completed' : 'Pending'; ?></small>
                            </div>
                        </div>
                    </div>
                    
                    <?php if ($order['order_status'] === 'cancelled'): ?>
                        <div class="alert alert-error">
                            <strong>Order Cancelled</strong><br>
                            This order has been cancelled.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Order Items -->
        <div class="order-items-section">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Order Items</h3>
                </div>
                <div class="card-body">
                    <div class="order-items-list">
                        <?php foreach ($orderItems as $item): ?>
                            <div class="order-item">
                                <div class="item-image">
                                    <?php if ($item['image_url']): ?>
                                        <img src="<?php echo SITE_URL . UPLOAD_PATH . 'menu/' . $item['image_url']; ?>" 
                                             alt="<?php echo htmlspecialchars($item['item_name']); ?>">
                                    <?php else: ?>
                                        <div class="placeholder-image">🍽️</div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="item-details">
                                    <h4 class="item-name"><?php echo htmlspecialchars($item['item_name']); ?></h4>
                                    <p class="item-category"><?php echo htmlspecialchars($item['category_name']); ?></p>
                                    <p class="item-price"><?php echo formatCurrency($item['item_price']); ?> each</p>
                                </div>
                                
                                <div class="item-quantity">
                                    <span>Qty: <?php echo $item['quantity']; ?></span>
                                </div>
                                
                                <div class="item-total">
                                    <strong><?php echo formatCurrency($item['item_price'] * $item['quantity']); ?></strong>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Order Summary -->
        <div class="order-summary-section">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Order Summary</h3>
                </div>
                <div class="card-body">
                    <div class="summary-lines">
                        <div class="summary-line">
                            <span>Subtotal</span>
                            <span><?php echo formatCurrency($subtotal); ?></span>
                        </div>
                        <div class="summary-line">
                            <span>Delivery Fee</span>
                            <span><?php echo formatCurrency($deliveryFee); ?></span>
                        </div>
                        <div class="summary-line">
                            <span>Service Tax (6%)</span>
                            <span><?php echo formatCurrency($tax); ?></span>
                        </div>
                        <div class="summary-line total-line">
                            <span>Total</span>
                            <span><?php echo formatCurrency($order['total_amount']); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Delivery Information -->
        <div class="delivery-info-section">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Delivery Information</h3>
                </div>
                <div class="card-body">
                    <div class="info-grid">
                        <div class="info-item">
                            <strong>Address:</strong>
                            <p><?php echo htmlspecialchars($order['delivery_address']); ?></p>
                        </div>
                        <div class="info-item">
                            <strong>Phone:</strong>
                            <p><?php echo htmlspecialchars($order['phone']); ?></p>
                        </div>
                        <?php if ($order['notes']): ?>
                            <div class="info-item">
                                <strong>Special Instructions:</strong>
                                <p><?php echo htmlspecialchars($order['notes']); ?></p>
                            </div>
                        <?php endif; ?>
                        <div class="info-item">
                            <strong>Payment Status:</strong>
                            <span class="payment-status payment-<?php echo $order['payment_status']; ?>">
                                <?php echo ucfirst($order['payment_status']); ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Action Buttons -->
    <div class="order-actions">
        <?php if (isCustomer()): ?>
            <a href="orders.php" class="btn btn-secondary">Back to Orders</a>
            
            <?php if ($order['order_status'] === 'delivered'): ?>
                <a href="<?php echo SITE_URL; ?>menu/menu.php" class="btn btn-primary">Order Again</a>
            <?php endif; ?>
            
            <?php if (in_array($order['order_status'], ['pending', 'confirmed'])): ?>
                <form method="POST" action="cancel-order.php" style="display: inline;">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <input type="hidden" name="order_id" value="<?php echo $orderId; ?>">
                    <button type="submit" class="btn btn-danger" data-confirm="Are you sure you want to cancel this order?">
                        Cancel Order
                    </button>
                </form>
            <?php endif; ?>
        <?php endif; ?>
        
        <?php if (isAdmin()): ?>
            <a href="<?php echo SITE_URL; ?>admin/admin-orders.php" class="btn btn-secondary">Back to Orders</a>
        <?php endif; ?>
    </div>
</div>

<style>
.order-container {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 2rem;
    margin-bottom: 2rem;
}

.order-status-section {
    grid-column: 1 / -1;
}

.status-timeline {
    display: flex;
    justify-content: space-between;
    position: relative;
}

.status-timeline::before {
    content: '';
    position: absolute;
    top: 30px;
    left: 30px;
    right: 30px;
    height: 2px;
    background: #e2e8f0;
    z-index: 1;
}

.status-step {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    position: relative;
    z-index: 2;
}

.status-step.active .step-icon {
    background: #334155;
    color: white;
}

.status-step.active::before {
    background: #334155;
}

.step-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: #e2e8f0;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    margin-bottom: 0.5rem;
    border: 3px solid white;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.step-info {
    max-width: 100px;
}

.step-info strong {
    display: block;
    margin-bottom: 0.25rem;
    color: #1e293b;
}

.step-info small {
    color: #64748b;
    font-size: 0.8rem;
}

.order-items-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.order-item {
    display: grid;
    grid-template-columns: 60px 1fr auto auto;
    gap: 1rem;
    align-items: center;
    padding: 1rem;
    border: 1px solid #f1f5f9;
    border-radius: 0.375rem;
}

.item-image {
    width: 60px;
    height: 60px;
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
    color: #94a3b8;
    font-size: 1.5rem;
}

.item-name {
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 0.25rem;
}

.item-category {
    color: #64748b;
    font-size: 0.875rem;
    margin-bottom: 0.25rem;
}

.item-price {
    color: #334155;
    font-size: 0.875rem;
}

.item-quantity {
    font-weight: 500;
    color: #64748b;
}

.item-total {
    font-weight: 600;
    color: #334155;
    font-size: 1.1rem;
}

.summary-lines {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.summary-line {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.total-line {
    font-weight: 600;
    font-size: 1.1rem;
    border-top: 1px solid #e2e8f0;
    padding-top: 0.75rem;
    color: #1e293b;
}

.info-grid {
    display: grid;
    gap: 1rem;
}

.info-item {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.info-item strong {
    color: #1e293b;
    font-weight: 600;
}

.info-item p {
    color: #64748b;
    margin: 0;
}

.payment-status {
    font-weight: 500;
    padding: 0.25rem 0.75rem;
    border-radius: 0.25rem;
    font-size: 0.875rem;
}

.payment-pending {
    background: #fef3c7;
    color: #92400e;
}

.payment-paid {
    background: #dcfce7;
    color: #166534;
}

.payment-failed {
    background: #fee2e2;
    color: #991b1b;
}

.order-actions {
    display: flex;
    gap: 1rem;
    justify-content: center;
    flex-wrap: wrap;
}

@media (max-width: 768px) {
    .order-container {
        grid-template-columns: 1fr;
    }
    
    .status-timeline {
        flex-direction: column;
        gap: 1rem;
    }
    
    .status-timeline::before {
        display: none;
    }
    
    .status-step {
        flex-direction: row;
        text-align: left;
        gap: 1rem;
    }
    
    .step-info {
        max-width: none;
    }
    
    .order-item {
        grid-template-columns: 50px 1fr;
        gap: 0.75rem;
    }
    
    .item-quantity,
    .item-total {
        grid-column: 1 / -1;
        display: flex;
        justify-content: space-between;
        margin-top: 0.5rem;
    }
}
</style>

<?php
// Include footer
include '../includes/footer.php';
?>