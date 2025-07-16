<?php
/**
 * Cancel Order Handler
 * Online Food Ordering System - Phase 4
 */

require_once '../config.php';
require_once '../functions.php';

// Require login
requireLogin();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['flash_message']['error'] = 'Invalid request.';
        header('Location: orders.php');
        exit();
    }
    
    $orderId = (int)($_POST['order_id'] ?? 0);
    $currentUser = getCurrentUser();
    
    if (!$orderId) {
        $_SESSION['flash_message']['error'] = 'Invalid order ID.';
        header('Location: orders.php');
        exit();
    }
    
    // Cancel order
    if (cancelOrder($orderId, isAdmin() ? null : $currentUser['user_id'])) {
        $_SESSION['flash_message']['success'] = 'Order cancelled successfully.';
    } else {
        $_SESSION['flash_message']['error'] = 'Failed to cancel order. Order may not be cancellable.';
    }
    
    // Redirect based on user role
    if (isAdmin()) {
        header('Location: ../admin/admin-orders.php');
    } else {
        header('Location: orders.php');
    }
} else {
    header('Location: orders.php');
}
exit();
?>