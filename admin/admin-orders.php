<?php
/**
 * Enhanced Admin Orders Management with Payment Confirmation
 * Online Food Ordering System - Phase 4
 */

require_once '../config.php';
require_once '../functions.php';

requireAdmin();

$errors = [];
$currentUser = getCurrentUser();

// Handle status and payment updates
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request.';
    } else {
        $orderId = (int)($_POST['order_id'] ?? 0);
        $action = $_POST['action'];
        
        switch ($action) {
            case 'update_status':
                $newStatus = cleanInput($_POST['new_status'] ?? '');
                
                if ($orderId && $newStatus) {
                    if (updateOrderStatus($orderId, $newStatus)) {
                        // Auto-mark payment as paid when order is delivered (for cash orders)
                        if ($newStatus === 'delivered') {
                            $order = getOrderById($orderId);
                            if ($order && $order['payment_status'] === 'pending') {
                                updateOrderPaymentStatus($orderId, 'paid');
                                addOrderStatusHistory($orderId, $newStatus, 'Order delivered - Cash payment received', $currentUser['user_id']);
                                $_SESSION['flash_message']['success'] = 'Order status updated and payment confirmed!';
                            } else {
                                addOrderStatusHistory($orderId, $newStatus, 'Order delivered', $currentUser['user_id']);
                                $_SESSION['flash_message']['success'] = 'Order status updated successfully!';
                            }
                        } else {
                            addOrderStatusHistory($orderId, $newStatus, 'Status updated by admin', $currentUser['user_id']);
                            $_SESSION['flash_message']['success'] = 'Order status updated successfully!';
                        }
                    } else {
                        $errors[] = 'Failed to update order status.';
                    }
                }
                break;
                
            case 'confirm_payment':
                if ($orderId) {
                    if (updateOrderPaymentStatus($orderId, 'paid')) {
                        addOrderStatusHistory($orderId, null, 'Cash payment confirmed by admin', $currentUser['user_id']);
                        $_SESSION['flash_message']['success'] = 'Payment confirmed successfully!';
                    } else {
                        $errors[] = 'Failed to confirm payment.';
                    }
                }
                break;
                
            case 'mark_failed_payment':
                if ($orderId) {
                    if (updateOrderPaymentStatus($orderId, 'failed')) {
                        addOrderStatusHistory($orderId, null, 'Payment marked as failed by admin', $currentUser['user_id']);
                        $_SESSION['flash_message']['warning'] = 'Payment marked as failed.';
                    } else {
                        $errors[] = 'Failed to update payment status.';
                    }
                }
                break;
        }
        
        header('Location: admin-orders.php');
        exit();
    }
}

// Get filters
$statusFilter = $_GET['status'] ?? '';
$dateFilter = $_GET['date'] ?? '';
$paymentFilter = $_GET['payment'] ?? '';

// Get orders with filters
$orders = getAdminOrdersEnhanced($statusFilter, $dateFilter, $paymentFilter);
$orderStats = getOrderStatistics();

$pageTitle = 'Order Management';
$bodyClass = 'admin-page';
include '../includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h1 class="page-title">Order Management</h1>
        <p class="page-subtitle">Manage customer orders, status, and payment confirmation</p>
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
    
    <!-- Order Statistics -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">📋</div>
            <div class="stat-content">
                <div class="stat-number"><?php echo $orderStats['total_orders']; ?></div>
                <div class="stat-label">Total Orders</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">⏳</div>
            <div class="stat-content">
                <div class="stat-number"><?php echo $orderStats['pending_orders']; ?></div>
                <div class="stat-label">Pending</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">👨‍🍳</div>
            <div class="stat-content">
                <div class="stat-number"><?php echo $orderStats['preparing_orders']; ?></div>
                <div class="stat-label">Preparing</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">💰</div>
            <div class="stat-content">
                <div class="stat-number"><?php echo formatCurrency($orderStats['total_revenue']); ?></div>
                <div class="stat-label">Total Revenue</div>
            </div>
        </div>
    </div>
    
    <!-- Enhanced Filters -->
    <div class="card">
        <div class="card-body">
            <form method="GET" class="filter-form">
                <div class="filter-row">
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="pending" <?php echo $statusFilter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="confirmed" <?php echo $statusFilter === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                        <option value="preparing" <?php echo $statusFilter === 'preparing' ? 'selected' : ''; ?>>Preparing</option>
                        <option value="ready" <?php echo $statusFilter === 'ready' ? 'selected' : ''; ?>>Ready</option>
                        <option value="delivered" <?php echo $statusFilter === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                    </select>
                    
                    <select name="payment" class="form-select">
                        <option value="">All Payments</option>
                        <option value="pending" <?php echo $paymentFilter === 'pending' ? 'selected' : ''; ?>>Payment Pending</option>
                        <option value="paid" <?php echo $paymentFilter === 'paid' ? 'selected' : ''; ?>>Payment Paid</option>
                        <option value="failed" <?php echo $paymentFilter === 'failed' ? 'selected' : ''; ?>>Payment Failed</option>
                    </select>
                    
                    <input type="date" name="date" class="form-input" value="<?php echo htmlspecialchars($dateFilter); ?>">
                    
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="admin-orders.php" class="btn btn-secondary">Clear</a>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Orders Table -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Orders</h3>
        </div>
        <div class="card-body">
            <?php if (empty($orders)): ?>
                <div class="no-data">No orders found.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Total</th>
                                <th>Order Status</th>
                                <th>Payment Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td><strong>#<?php echo $order['order_id']; ?></strong></td>
                                    <td><?php echo htmlspecialchars($order['full_name']); ?></td>
                                    <td><?php echo formatCurrency($order['total_amount']); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo $order['order_status']; ?>">
                                            <?php echo ucfirst($order['order_status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="payment-badge payment-<?php echo $order['payment_status']; ?>">
                                            <?php echo ucfirst($order['payment_status']); ?>
                                        </span>
                                        
                                        <!-- Payment Action Button -->
                                        <?php if ($order['payment_status'] === 'pending'): ?>
                                            <form method="POST" style="display: inline; margin-left: 0.5rem;">
                                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                                <input type="hidden" name="action" value="confirm_payment">
                                                <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                                                <button type="submit" 
                                                        class="btn btn-success btn-tiny"
                                                        title="Confirm Cash Payment Received"
                                                        data-confirm="Confirm that cash payment has been received?">
                                                    💰 Confirm Payment
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo formatDateTime($order['created_at']); ?></td>
                                    <td>
                                        <div class="action-buttons-small">
                                            <a href="../orders/order-details.php?id=<?php echo $order['order_id']; ?>" 
                                               class="btn btn-small btn-secondary">View</a>
                                            
                                            <?php if ($order['order_status'] !== 'delivered' && $order['order_status'] !== 'cancelled'): ?>
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                                    <input type="hidden" name="action" value="update_status">
                                                    <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                                                    <select name="new_status" class="form-select form-select-mini" onchange="this.form.submit()">
                                                        <option value="">Update Status</option>
                                                        <?php if ($order['order_status'] === 'pending'): ?>
                                                            <option value="confirmed">✅ Confirm</option>
                                                            <option value="cancelled">❌ Cancel</option>
                                                        <?php elseif ($order['order_status'] === 'confirmed'): ?>
                                                            <option value="preparing">👨‍🍳 Start Preparing</option>
                                                        <?php elseif ($order['order_status'] === 'preparing'): ?>
                                                            <option value="ready">🎯 Mark Ready</option>
                                                        <?php elseif ($order['order_status'] === 'ready'): ?>
                                                            <option value="delivered">🚚 Mark Delivered</option>
                                                        <?php endif; ?>
                                                    </select>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Payment Management Quick Actions -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Payment Management</h3>
        </div>
        <div class="card-body">
            <div class="payment-actions">
                <div class="payment-info">
                    <h4>💡 Payment Status Guide:</h4>
                    <ul>
                        <li><strong>Pending:</strong> Cash payment not yet received</li>
                        <li><strong>Paid:</strong> Payment confirmed and received</li>
                        <li><strong>Failed:</strong> Payment failed or refunded</li>
                    </ul>
                </div>
                
                <div class="quick-actions">
                    <p><strong>Quick Actions:</strong></p>
                    <p>• When you deliver a cash order, click <span class="highlight">"💰 Confirm Payment"</span></p>
                    <p>• When you mark an order as "Delivered", cash payment is auto-confirmed</p>
                    <p>• Online payments are automatically marked as "Paid"</p>
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

.filter-row {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr auto auto;
    gap: 1rem;
    align-items: center;
}

.action-buttons-small {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
    align-items: center;
}

.form-select-mini {
    width: auto;
    min-width: 120px;
    font-size: 0.8rem;
    padding: 0.25rem 0.5rem;
}

.btn-tiny {
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
    border-radius: 0.25rem;
}

.payment-badge {
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    font-size: 0.75rem;
    font-weight: 500;
    text-transform: uppercase;
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

.payment-actions {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 2rem;
}

.payment-info ul {
    margin: 0.5rem 0;
    padding-left: 1.5rem;
}

.payment-info li {
    margin: 0.25rem 0;
    color: #64748b;
}

.quick-actions p {
    margin: 0.5rem 0;
    color: #64748b;
}

.highlight {
    background: #fef3c7;
    padding: 0.125rem 0.25rem;
    border-radius: 0.25rem;
    color: #92400e;
    font-weight: 500;
}

@media (max-width: 768px) {
    .filter-row {
        grid-template-columns: 1fr;
    }
    
    .action-buttons-small {
        flex-direction: column;
    }
    
    .payment-actions {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-refresh every 30 seconds
    setInterval(() => {
        if (!document.hidden) {
            // Check for new orders without full page reload
            fetch('get_order_count.php')
                .then(response => response.json())
                .then(data => {
                    if (data.new_orders) {
                        // Show notification for new orders
                        showNotification('New orders received! Refreshing...', 'info');
                        setTimeout(() => window.location.reload(), 2000);
                    }
                })
                .catch(() => {
                    // Silent fail - no internet or server issue
                });
        }
    }, 30000);
});

function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type}`;
    notification.style.position = 'fixed';
    notification.style.top = '20px';
    notification.style.right = '20px';
    notification.style.zIndex = '9999';
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 5000);
}
</script>

<?php include '../includes/footer.php'; ?>