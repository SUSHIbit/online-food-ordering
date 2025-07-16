<?php
/**
 * Admin Orders Management
 * Online Food Ordering System - Phase 4
 */

require_once '../config.php';
require_once '../functions.php';

requireAdmin();

$errors = [];
$currentUser = getCurrentUser();

// Handle status updates
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request.';
    } else {
        $orderId = (int)($_POST['order_id'] ?? 0);
        $newStatus = cleanInput($_POST['new_status'] ?? '');
        
        if ($orderId && $newStatus) {
            if (updateOrderStatus($orderId, $newStatus)) {
                // Add to status history
                addOrderStatusHistory($orderId, $newStatus, 'Status updated by admin', $currentUser['user_id']);
                $_SESSION['flash_message']['success'] = 'Order status updated successfully!';
            } else {
                $errors[] = 'Failed to update order status.';
            }
        }
        
        header('Location: admin-orders.php');
        exit();
    }
}

// Get filters
$statusFilter = $_GET['status'] ?? '';
$dateFilter = $_GET['date'] ?? '';

// Get orders with filters
$orders = getAdminOrders($statusFilter, $dateFilter);
$orderStats = getOrderStatistics();

$pageTitle = 'Order Management';
$bodyClass = 'admin-page';
include '../includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h1 class="page-title">Order Management</h1>
        <p class="page-subtitle">Manage customer orders and track status</p>
    </div>
    
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
    
    <!-- Filters -->
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
                                <th>Status</th>
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
                                                    <select name="new_status" class="form-select" style="width: auto; display: inline;" onchange="this.form.submit()">
                                                        <option value="">Update Status</option>
                                                        <?php if ($order['order_status'] === 'pending'): ?>
                                                            <option value="confirmed">Confirm</option>
                                                            <option value="cancelled">Cancel</option>
                                                        <?php elseif ($order['order_status'] === 'confirmed'): ?>
                                                            <option value="preparing">Start Preparing</option>
                                                        <?php elseif ($order['order_status'] === 'preparing'): ?>
                                                            <option value="ready">Mark Ready</option>
                                                        <?php elseif ($order['order_status'] === 'ready'): ?>
                                                            <option value="delivered">Mark Delivered</option>
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
    grid-template-columns: 1fr 1fr auto auto;
    gap: 1rem;
    align-items: center;
}

.action-buttons-small {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

@media (max-width: 768px) {
    .filter-row {
        grid-template-columns: 1fr;
    }
    
    .action-buttons-small {
        flex-direction: column;
    }
}
</style>

<?php include '../includes/footer.php'; ?>