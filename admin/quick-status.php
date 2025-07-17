<?php
/**
 * Quick Order Status Update Tool
 * Save as: admin/quick-status.php
 * 
 * A simple tool for admins to quickly update order statuses
 */

require_once '../config.php';
require_once '../functions.php';

requireAdmin();

$message = '';
$messageType = '';

// Handle status update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $message = 'Invalid request token.';
        $messageType = 'error';
    } else {
        $orderId = (int)($_POST['order_id'] ?? 0);
        $newStatus = cleanInput($_POST['new_status'] ?? '');
        
        if ($orderId && $newStatus) {
            if (updateOrderStatus($orderId, $newStatus)) {
                addOrderStatusHistory($orderId, $newStatus, 'Status updated by admin via quick tool', getCurrentUser()['user_id']);
                
                // Auto-update payment status for online orders
                if ($newStatus === 'confirmed') {
                    $order = getOrderById($orderId);
                    if ($order && strpos($order['notes'], 'online') !== false) {
                        updateOrderPaymentStatus($orderId, 'paid');
                    }
                }
                
                $message = "Order #$orderId status updated to: " . ucfirst($newStatus);
                $messageType = 'success';
            } else {
                $message = 'Failed to update order status.';
                $messageType = 'error';
            }
        } else {
            $message = 'Invalid order ID or status.';
            $messageType = 'error';
        }
    }
}

// Get pending orders
$pendingOrders = getAdminOrders('pending', null, 20);

$pageTitle = 'Quick Status Update';
include '../includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h1 class="page-title">Quick Order Status Update</h1>
        <p class="page-subtitle">Quickly update order statuses from pending to confirmed</p>
    </div>
    
    <?php if ($message): ?>
        <div class="alert alert-<?php echo $messageType; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Pending Orders</h3>
            <a href="admin-orders.php" class="btn btn-outline">View All Orders</a>
        </div>
        <div class="card-body">
            <?php if (empty($pendingOrders)): ?>
                <div class="no-data">
                    <p>🎉 No pending orders! All orders are processed.</p>
                    <a href="../menu/menu.php" class="btn btn-primary">View Menu</a>
                </div>
            <?php else: ?>
                <div class="orders-grid">
                    <?php foreach ($pendingOrders as $order): ?>
                        <div class="order-card">
                            <div class="order-header">
                                <h4>Order #<?php echo $order['order_id']; ?></h4>
                                <span class="order-amount"><?php echo formatCurrency($order['total_amount']); ?></span>
                            </div>
                            
                            <div class="order-info">
                                <p><strong>Customer:</strong> <?php echo htmlspecialchars($order['full_name']); ?></p>
                                <p><strong>Email:</strong> <?php echo htmlspecialchars($order['email']); ?></p>
                                <p><strong>Time:</strong> <?php echo formatDateTime($order['created_at']); ?></p>
                            </div>
                            
                            <div class="status-actions">
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                    <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                                    <input type="hidden" name="new_status" value="confirmed">
                                    <button type="submit" class="btn btn-success btn-small">
                                        ✅ Confirm Order
                                    </button>
                                </form>
                                
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                    <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                                    <input type="hidden" name="new_status" value="preparing">
                                    <button type="submit" class="btn btn-warning btn-small">
                                        👨‍🍳 Start Preparing
                                    </button>
                                </form>
                                
                                <a href="../orders/order-details.php?id=<?php echo $order['order_id']; ?>" 
                                   class="btn btn-outline btn-small">
                                    View Details
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Quick Bulk Actions -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Bulk Actions</h3>
        </div>
        <div class="card-body">
            <div class="bulk-actions">
                <form method="POST" id="bulk-confirm-form">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <button type="button" class="btn btn-primary" onclick="confirmAllPending()">
                        ✅ Confirm All Pending Orders
                    </button>
                    <span class="help-text">This will confirm all <?php echo count($pendingOrders); ?> pending orders</span>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.orders-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1rem;
}

.order-card {
    border: 1px solid #e2e8f0;
    border-radius: 0.5rem;
    padding: 1rem;
    background: white;
}

.order-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid #f1f5f9;
}

.order-header h4 {
    margin: 0;
    color: #1e293b;
}

.order-amount {
    font-weight: bold;
    color: #059669;
}

.order-info {
    margin-bottom: 1rem;
    font-size: 0.9rem;
}

.order-info p {
    margin: 0.25rem 0;
    color: #64748b;
}

.status-actions {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.bulk-actions {
    text-align: center;
    padding: 1rem;
}

.help-text {
    display: block;
    font-size: 0.875rem;
    color: #64748b;
    margin-top: 0.5rem;
}

.no-data {
    text-align: center;
    padding: 2rem;
    color: #64748b;
}

@media (max-width: 768px) {
    .orders-grid {
        grid-template-columns: 1fr;
    }
    
    .status-actions {
        flex-direction: column;
    }
}
</style>

<script>
function confirmAllPending() {
    if (confirm('Are you sure you want to confirm all pending orders? This action cannot be undone.')) {
        const orders = <?php echo json_encode(array_column($pendingOrders, 'order_id')); ?>;
        
        // Show loading
        const btn = event.target;
        btn.disabled = true;
        btn.textContent = 'Processing...';
        
        // Process each order
        let processed = 0;
        orders.forEach(orderId => {
            const form = document.createElement('form');
            form.method = 'POST';
            form.style.display = 'none';
            
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = 'csrf_token';
            csrfInput.value = '<?php echo generateCSRFToken(); ?>';
            
            const orderInput = document.createElement('input');
            orderInput.type = 'hidden';
            orderInput.name = 'order_id';
            orderInput.value = orderId;
            
            const statusInput = document.createElement('input');
            statusInput.type = 'hidden';
            statusInput.name = 'new_status';
            statusInput.value = 'confirmed';
            
            form.appendChild(csrfInput);
            form.appendChild(orderInput);
            form.appendChild(statusInput);
            document.body.appendChild(form);
            
            // Submit form and remove it
            form.submit();
        });
        
        // Refresh page after a moment
        setTimeout(() => {
            window.location.reload();
        }, 1000);
    }
}

// Auto-refresh every 30 seconds to show new orders
setInterval(() => {
    if (document.hidden === false) {
        window.location.reload();
    }
}, 30000);
</script>

<?php include '../includes/footer.php'; ?>