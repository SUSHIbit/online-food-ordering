<?php
/**
 * Payment Confirmation Tool
 * Save as: admin/payments.php
 * 
 * Simple tool for admins to confirm cash payments
 */

require_once '../config.php';
require_once '../functions.php';

requireAdmin();

$message = '';
$messageType = '';

// Handle payment confirmation
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $message = 'Invalid request token.';
        $messageType = 'error';
    } else {
        $orderId = (int)($_POST['order_id'] ?? 0);
        $action = $_POST['action'] ?? '';
        
        if ($orderId && $action === 'confirm_payment') {
            if (updateOrderPaymentStatus($orderId, 'paid')) {
                addOrderStatusHistory($orderId, null, 'Cash payment confirmed by admin', getCurrentUser()['user_id']);
                $message = "Payment confirmed for Order #$orderId";
                $messageType = 'success';
            } else {
                $message = 'Failed to confirm payment.';
                $messageType = 'error';
            }
        }
    }
}

// Get orders with pending payments
$pendingPayments = getOrdersWithPendingPayments(20);
$paymentStats = getPaymentStatistics();

$pageTitle = 'Payment Management';
include '../includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h1 class="page-title">💰 Payment Management</h1>
        <p class="page-subtitle">Confirm cash payments and manage payment status</p>
    </div>
    
    <?php if ($message): ?>
        <div class="alert alert-<?php echo $messageType; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>
    
    <!-- Payment Statistics -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">⏳</div>
            <div class="stat-content">
                <div class="stat-number"><?php echo $paymentStats['pending_payments']; ?></div>
                <div class="stat-label">Pending Payments</div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">💵</div>
            <div class="stat-content">
                <div class="stat-number"><?php echo formatCurrency($paymentStats['pending_amount']); ?></div>
                <div class="stat-label">Pending Amount</div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">🚚</div>
            <div class="stat-content">
                <div class="stat-number"><?php echo $paymentStats['delivered_unpaid']; ?></div>
                <div class="stat-label">Delivered & Unpaid</div>
            </div>
        </div>
        
        <div class="stat-card urgent">
            <div class="stat-icon">⚠️</div>
            <div class="stat-content">
                <div class="stat-number"><?php echo $paymentStats['delivered_unpaid']; ?></div>
                <div class="stat-label">Need Payment Confirmation</div>
            </div>
        </div>
    </div>
    
    <!-- Pending Payments -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Orders Awaiting Payment Confirmation</h3>
            <div class="header-actions">
                <a href="admin-orders.php" class="btn btn-outline">All Orders</a>
                <span class="count-badge"><?php echo count($pendingPayments); ?> pending</span>
            </div>
        </div>
        <div class="card-body">
            <?php if (empty($pendingPayments)): ?>
                <div class="no-data">
                    <div class="success-icon">🎉</div>
                    <h3>All Payments Confirmed!</h3>
                    <p>No orders with pending payments. Great job!</p>
                    <a href="../menu/menu.php" class="btn btn-primary">View Menu</a>
                </div>
            <?php else: ?>
                <div class="payments-grid">
                    <?php foreach ($pendingPayments as $order): ?>
                        <div class="payment-card <?php echo $order['order_status'] === 'delivered' ? 'urgent' : ''; ?>">
                            <div class="payment-header">
                                <h4>Order #<?php echo $order['order_id']; ?></h4>
                                <div class="order-badges">
                                    <span class="status-badge status-<?php echo $order['order_status']; ?>">
                                        <?php echo ucfirst($order['order_status']); ?>
                                    </span>
                                    <span class="payment-badge payment-pending">Payment Pending</span>
                                </div>
                            </div>
                            
                            <div class="payment-info">
                                <p><strong>Customer:</strong> <?php echo htmlspecialchars($order['full_name']); ?></p>
                                <p><strong>Amount:</strong> <?php echo formatCurrency($order['total_amount']); ?></p>
                                <p><strong>Order Time:</strong> <?php echo formatDateTime($order['created_at']); ?></p>
                                
                                <?php if ($order['order_status'] === 'delivered'): ?>
                                    <div class="urgent-notice">
                                        <strong>⚠️ DELIVERED - Confirm Cash Payment</strong>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="payment-actions">
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                    <input type="hidden" name="action" value="confirm_payment">
                                    <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                                    <button type="submit" 
                                            class="btn btn-success btn-small"
                                            data-confirm="Confirm that cash payment of <?php echo formatCurrency($order['total_amount']); ?> has been received?">
                                        💰 Confirm Payment
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
                
                <!-- Bulk Actions -->
                <div class="bulk-actions">
                    <h4>Bulk Actions</h4>
                    <button type="button" class="btn btn-primary" onclick="confirmAllDelivered()">
                        ✅ Confirm All Delivered Orders
                    </button>
                    <span class="help-text">
                        This will confirm payment for all delivered orders with pending payments
                    </span>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Payment Guide -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">💡 Payment Management Guide</h3>
        </div>
        <div class="card-body">
            <div class="guide-grid">
                <div class="guide-item">
                    <div class="guide-icon">💳</div>
                    <div class="guide-content">
                        <h4>Online Payments</h4>
                        <p>Automatically marked as "Paid" when customer pays online during checkout.</p>
                    </div>
                </div>
                
                <div class="guide-item">
                    <div class="guide-icon">💵</div>
                    <div class="guide-content">
                        <h4>Cash on Delivery</h4>
                        <p>Remains "Pending" until you manually confirm cash payment has been received.</p>
                    </div>
                </div>
                
                <div class="guide-item">
                    <div class="guide-icon">✅</div>
                    <div class="guide-content">
                        <h4>Auto-Confirmation</h4>
                        <p>When you mark an order as "Delivered", cash payment is automatically confirmed.</p>
                    </div>
                </div>
                
                <div class="guide-item">
                    <div class="guide-icon">⚠️</div>
                    <div class="guide-content">
                        <h4>Urgent Items</h4>
                        <p>Delivered orders with pending payments need immediate attention (highlighted in red).</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: white;
    padding: 1.5rem;
    border-radius: 0.5rem;
    border: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    gap: 1rem;
}

.stat-card.urgent {
    border-color: #ef4444;
    background: #fef2f2;
}

.stat-icon {
    font-size: 2rem;
}

.stat-number {
    font-size: 1.5rem;
    font-weight: bold;
    color: #1e293b;
}

.stat-label {
    color: #64748b;
    font-size: 0.875rem;
    text-transform: uppercase;
}

.header-actions {
    display: flex;
    gap: 1rem;
    align-items: center;
}

.count-badge {
    background: #fbbf24;
    color: #92400e;
    padding: 0.25rem 0.75rem;
    border-radius: 1rem;
    font-size: 0.8rem;
    font-weight: 500;
}

.payments-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 1rem;
    margin-bottom: 2rem;
}

.payment-card {
    border: 1px solid #e2e8f0;
    border-radius: 0.5rem;
    padding: 1.5rem;
    background: white;
    transition: all 0.2s ease;
}

.payment-card.urgent {
    border-color: #ef4444;
    background: #fef2f2;
    box-shadow: 0 4px 8px rgba(239, 68, 68, 0.2);
}

.payment-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.payment-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid #f1f5f9;
}

.payment-header h4 {
    margin: 0;
    color: #1e293b;
}

.order-badges {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.status-badge, .payment-badge {
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    font-size: 0.75rem;
    font-weight: 500;
    text-transform: uppercase;
    text-align: center;
}

.payment-pending {
    background: #fef3c7;
    color: #92400e;
}

.payment-info {
    margin-bottom: 1rem;
    font-size: 0.9rem;
}

.payment-info p {
    margin: 0.5rem 0;
    color: #64748b;
}

.urgent-notice {
    background: #fee2e2;
    color: #991b1b;
    padding: 0.75rem;
    border-radius: 0.375rem;
    margin-top: 0.5rem;
    border: 1px solid #fecaca;
}

.payment-actions {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.bulk-actions {
    text-align: center;
    padding: 2rem;
    background: #f8fafc;
    border-radius: 0.5rem;
    border: 1px solid #e2e8f0;
}

.help-text {
    display: block;
    font-size: 0.875rem;
    color: #64748b;
    margin-top: 0.5rem;
}

.no-data {
    text-align: center;
    padding: 3rem;
    color: #64748b;
}

.success-icon {
    font-size: 3rem;
    margin-bottom: 1rem;
}

.guide-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1rem;
}

.guide-item {
    display: flex;
    gap: 1rem;
    padding: 1rem;
    background: #f8fafc;
    border-radius: 0.5rem;
}

.guide-icon {
    font-size: 2rem;
    flex-shrink: 0;
}

.guide-content h4 {
    margin: 0 0 0.5rem 0;
    color: #1e293b;
}

.guide-content p {
    margin: 0;
    color: #64748b;
    font-size: 0.9rem;
}

@media (max-width: 768px) {
    .payments-grid {
        grid-template-columns: 1fr;
    }
    
    .payment-actions {
        flex-direction: column;
    }
    
    .guide-grid {
        grid-template-columns: 1fr;
    }
    
    .header-actions {
        flex-direction: column;
        align-items: flex-start;
    }
}
</style>

<script>
function confirmAllDelivered() {
    const deliveredOrders = <?php echo json_encode(array_filter($pendingPayments, function($order) { return $order['order_status'] === 'delivered'; })); ?>;
    
    if (deliveredOrders.length === 0) {
        alert('No delivered orders with pending payments found.');
        return;
    }
    
    if (confirm(`Confirm payment for ${deliveredOrders.length} delivered orders? This will mark all cash payments as received.`)) {
        const btn = event.target;
        btn.disabled = true;
        btn.textContent = 'Processing...';
        
        // Process each delivered order
        deliveredOrders.forEach((order, index) => {
            setTimeout(() => {
                const form = document.createElement('form');
                form.method = 'POST';
                form.style.display = 'none';
                
                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = 'csrf_token';
                csrfInput.value = '<?php echo generateCSRFToken(); ?>';
                
                const actionInput = document.createElement('input');
                actionInput.type = 'hidden';
                actionInput.name = 'action';
                actionInput.value = 'confirm_payment';
                
                const orderInput = document.createElement('input');
                orderInput.type = 'hidden';
                orderInput.name = 'order_id';
                orderInput.value = order.order_id;
                
                form.appendChild(csrfInput);
                form.appendChild(actionInput);
                form.appendChild(orderInput);
                document.body.appendChild(form);
                
                form.submit();
            }, index * 500); // Stagger submissions
        });
        
        // Refresh page after processing
        setTimeout(() => {
            window.location.reload();
        }, deliveredOrders.length * 500 + 1000);
    }
}

// Auto-refresh every 60 seconds
setInterval(() => {
    if (!document.hidden) {
        window.location.reload();
    }
}, 60000);
</script>

<?php include '../includes/footer.php'; ?>