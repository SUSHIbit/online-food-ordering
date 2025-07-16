<?php
/**
 * Order History Page
 * Online Food Ordering System - Phase 3
 * 
 * Display user's order history
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
$orders = getUserOrders($currentUser['user_id']);

// Page configuration
$pageTitle = 'Order History';
$bodyClass = 'orders-page';

// Include header
include '../includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h1 class="page-title">My Orders</h1>
        <p class="page-subtitle">Track your order history and status</p>
    </div>
    
    <?php if (empty($orders)): ?>
        <!-- No Orders -->
        <div class="no-orders">
            <div class="no-orders-icon">📋</div>
            <h2>No orders yet</h2>
            <p>You haven't placed any orders yet. Start exploring our menu!</p>
            <a href="<?php echo SITE_URL; ?>menu/menu.php" class="btn btn-primary btn-large">
                Browse Menu
            </a>
        </div>
    <?php else: ?>
        <!-- Orders List -->
        <div class="orders-list">
            <?php foreach ($orders as $order): ?>
                <div class="order-card">
                    <div class="order-header">
                        <div class="order-info">
                            <h3 class="order-id">Order #<?php echo $order['order_id']; ?></h3>
                            <p class="order-date"><?php echo formatDateTime($order['created_at']); ?></p>
                        </div>
                        <div class="order-status">
                            <span class="status-badge status-<?php echo $order['order_status']; ?>">
                                <?php echo ucfirst($order['order_status']); ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="order-details">
                        <div class="order-amount">
                            <strong>Total: <?php echo formatCurrency($order['total_amount']); ?></strong>
                        </div>
                        <div class="order-payment">
                            <span class="payment-status payment-<?php echo $order['payment_status']; ?>">
                                Payment: <?php echo ucfirst($order['payment_status']); ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="order-actions">
                        <a href="order-details.php?id=<?php echo $order['order_id']; ?>" 
                           class="btn btn-primary btn-small">
                            View Details
                        </a>
                        
                        <?php if ($order['order_status'] === 'delivered'): ?>
                            <a href="<?php echo SITE_URL; ?>menu/menu.php" 
                               class="btn btn-outline btn-small">
                                Order Again
                            </a>
                        <?php endif; ?>
                        
                        <?php if (in_array($order['order_status'], ['pending', 'confirmed'])): ?>
                            <form method="POST" action="cancel-order.php" style="display: inline;">
                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                                <button type="submit" 
                                        class="btn btn-danger btn-small"
                                        data-confirm="Are you sure you want to cancel this order?">
                                    Cancel Order
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<style>
.no-orders {
    text-align: center;
    padding: 4rem 2rem;
    color: #64748b;
}

.no-orders-icon {
    font-size: 4rem;
    margin-bottom: 1rem;
}

.no-orders h2 {
    color: #1e293b;
    margin-bottom: 0.5rem;
}

.orders-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.order-card {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 0.5rem;
    padding: 1.5rem;
    transition: box-shadow 0.2s ease;
}

.order-card:hover {
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.order-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1rem;
}

.order-id {
    font-size: 1.1rem;
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 0.25rem;
}

.order-date {
    color: #64748b;
    font-size: 0.875rem;
    margin: 0;
}

.status-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 1rem;
    font-size: 0.8rem;
    font-weight: 500;
    text-transform: uppercase;
}

.status-pending {
    background: #fef3c7;
    color: #92400e;
}

.status-confirmed {
    background: #dbeafe;
    color: #1e40af;
}

.status-preparing {
    background: #fde68a;
    color: #d97706;
}

.status-ready {
    background: #bbf7d0;
    color: #047857;
}

.status-delivered {
    background: #dcfce7;
    color: #166534;
}

.status-cancelled {
    background: #fee2e2;
    color: #991b1b;
}

.order-details {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
    padding: 1rem;
    background: #f8fafc;
    border-radius: 0.375rem;
}

.order-amount {
    font-size: 1.1rem;
    color: #1e293b;
}

.payment-status {
    font-size: 0.875rem;
    font-weight: 500;
}

.payment-pending {
    color: #d97706;
}

.payment-paid {
    color: #059669;
}

.payment-failed {
    color: #dc2626;
}

.order-actions {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

@media (max-width: 768px) {
    .order-header {
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .order-details {
        flex-direction: column;
        gap: 0.5rem;
        text-align: center;
    }
    
    .order-actions {
        justify-content: center;
    }
}
</style>

<?php
// Include footer
include '../includes/footer.php';
?>