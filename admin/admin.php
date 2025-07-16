<?php
/**
 * Admin Dashboard
 * Online Food Ordering System - Phase 4
 */

require_once '../config.php';
require_once '../functions.php';

requireAdmin();

$currentUser = getCurrentUser();

// Get dashboard data
$dashboardData = getDashboardStats();
$recentOrders = getRecentOrders(5);
$lowStockItems = getLowStockItems(); // Placeholder for future feature
$systemStatus = getSystemStatus();

$pageTitle = 'Admin Dashboard';
$bodyClass = 'admin-page dashboard-page';
include '../includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h1 class="page-title">Admin Dashboard</h1>
        <p class="page-subtitle">System overview and quick actions</p>
    </div>
    
    <!-- Key Metrics -->
    <div class="dashboard-grid">
        <div class="metric-card">
            <div class="metric-icon">📊</div>
            <div class="metric-content">
                <div class="metric-number"><?php echo $dashboardData['total_orders_today']; ?></div>
                <div class="metric-label">Orders Today</div>
                <div class="metric-change positive">+<?php echo $dashboardData['orders_change']; ?>%</div>
            </div>
        </div>
        
        <div class="metric-card">
            <div class="metric-icon">💰</div>
            <div class="metric-content">
                <div class="metric-number"><?php echo formatCurrency($dashboardData['revenue_today']); ?></div>
                <div class="metric-label">Revenue Today</div>
                <div class="metric-change positive">+<?php echo $dashboardData['revenue_change']; ?>%</div>
            </div>
        </div>
        
        <div class="metric-card">
            <div class="metric-icon">👥</div>
            <div class="metric-content">
                <div class="metric-number"><?php echo $dashboardData['active_customers']; ?></div>
                <div class="metric-label">Active Customers</div>
                <div class="metric-change neutral">This month</div>
            </div>
        </div>
        
        <div class="metric-card">
            <div class="metric-icon">⭐</div>
            <div class="metric-content">
                <div class="metric-number"><?php echo $dashboardData['avg_order_value']; ?></div>
                <div class="metric-label">Avg Order Value</div>
                <div class="metric-change positive">+RM 2.50</div>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="quick-actions">
        <h3>Quick Actions</h3>
        <div class="action-grid">
            <a href="admin-orders.php" class="action-card">
                <div class="action-icon">📋</div>
                <div class="action-text">
                    <strong>Manage Orders</strong>
                    <span><?php echo $dashboardData['pending_orders']; ?> pending orders</span>
                </div>
            </a>
            
            <a href="admin-menu.php" class="action-card">
                <div class="action-icon">🍽️</div>
                <div class="action-text">
                    <strong>Menu Management</strong>
                    <span><?php echo $dashboardData['total_menu_items']; ?> menu items</span>
                </div>
            </a>
            
            <a href="#" class="action-card">
                <div class="action-icon">👥</div>
                <div class="action-text">
                    <strong>Customer Management</strong>
                    <span><?php echo $dashboardData['total_customers']; ?> customers</span>
                </div>
            </a>
            
            <a href="#" class="action-card">
                <div class="action-icon">📈</div>
                <div class="action-text">
                    <strong>Reports</strong>
                    <span>View detailed analytics</span>
                </div>
            </a>
        </div>
    </div>
    
    <!-- Recent Orders & System Status -->
    <div class="dashboard-panels">
        <div class="panel">
            <div class="panel-header">
                <h3>Recent Orders</h3>
                <a href="admin-orders.php" class="btn btn-small btn-outline">View All</a>
            </div>
            <div class="panel-body">
                <?php if (empty($recentOrders)): ?>
                    <p class="no-data">No recent orders</p>
                <?php else: ?>
                    <div class="orders-list">
                        <?php foreach ($recentOrders as $order): ?>
                            <div class="order-item">
                                <div class="order-info">
                                    <strong>#<?php echo $order['order_id']; ?></strong>
                                    <span><?php echo htmlspecialchars($order['full_name']); ?></span>
                                </div>
                                <div class="order-meta">
                                    <span class="status-badge status-<?php echo $order['order_status']; ?>">
                                        <?php echo ucfirst($order['order_status']); ?>
                                    </span>
                                    <span><?php echo formatCurrency($order['total_amount']); ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="panel">
            <div class="panel-header">
                <h3>System Status</h3>
            </div>
            <div class="panel-body">
                <div class="status-list">
                    <div class="status-item">
                        <span class="status-indicator active"></span>
                        <span>Database Connection</span>
                        <span class="status-text">Healthy</span>
                    </div>
                    <div class="status-item">
                        <span class="status-indicator active"></span>
                        <span>Order Processing</span>
                        <span class="status-text">Running</span>
                    </div>
                    <div class="status-item">
                        <span class="status-indicator active"></span>
                        <span>Menu Updates</span>
                        <span class="status-text">Synced</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.dashboard-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.metric-card {
    background: white;
    padding: 1.5rem;
    border-radius: 0.5rem;
    border: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    gap: 1rem;
}

.metric-icon {
    font-size: 2.5rem;
}

.metric-number {
    font-size: 1.75rem;
    font-weight: bold;
    color: #1e293b;
}

.metric-label {
    color: #64748b;
    font-size: 0.875rem;
    margin-bottom: 0.25rem;
}

.metric-change {
    font-size: 0.8rem;
    font-weight: 500;
}

.metric-change.positive { color: #059669; }
.metric-change.negative { color: #dc2626; }
.metric-change.neutral { color: #64748b; }

.quick-actions {
    margin-bottom: 2rem;
}

.action-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-top: 1rem;
}

.action-card {
    background: white;
    padding: 1rem;
    border-radius: 0.5rem;
    border: 1px solid #e2e8f0;
    text-decoration: none;
    color: inherit;
    display: flex;
    align-items: center;
    gap: 1rem;
    transition: all 0.2s ease;
}

.action-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.action-icon {
    font-size: 2rem;
}

.dashboard-panels {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 2rem;
}

.panel {
    background: white;
    border-radius: 0.5rem;
    border: 1px solid #e2e8f0;
}

.panel-header {
    padding: 1rem;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.panel-body {
    padding: 1rem;
}

.order-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 0;
    border-bottom: 1px solid #f1f5f9;
}

.order-item:last-child {
    border-bottom: none;
}

.order-meta {
    display: flex;
    gap: 1rem;
    align-items: center;
}

.status-indicator {
    display: inline-block;
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #dc2626;
}

.status-indicator.active {
    background: #059669;
}

.status-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.5rem 0;
}

.status-text {
    font-size: 0.875rem;
    color: #64748b;
}

@media (max-width: 768px) {
    .dashboard-panels {
        grid-template-columns: 1fr;
    }
    
    .action-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<?php include '../includes/footer.php'; ?>