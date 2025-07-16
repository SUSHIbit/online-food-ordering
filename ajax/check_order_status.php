<?php
/**
 * Check Order Status AJAX Handler
 * Online Food Ordering System - Phase 4
 */

require_once '../config.php';
require_once '../functions.php';

// Check if request is AJAX
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
    http_response_code(403);
    exit();
}

header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

$orderId = (int)($_GET['order_id'] ?? 0);
$currentUser = getCurrentUser();

if (!$orderId) {
    echo json_encode(['success' => false, 'message' => 'Invalid order ID']);
    exit();
}

// Get order
$order = getOrderById($orderId);
if (!$order) {
    echo json_encode(['success' => false, 'message' => 'Order not found']);
    exit();
}

// Check permissions
if (isCustomer() && $order['user_id'] != $currentUser['user_id']) {
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit();
}

// Check if status changed (compare with session if available)
$statusChanged = false;
$sessionKey = 'order_status_' . $orderId;

if (isset($_SESSION[$sessionKey])) {
    $statusChanged = $_SESSION[$sessionKey] !== $order['order_status'];
}

$_SESSION[$sessionKey] = $order['order_status'];

echo json_encode([
    'success' => true,
    'current_status' => $order['order_status'],
    'status_changed' => $statusChanged,
    'new_status' => $statusChanged ? ucfirst($order['order_status']) : null,
    'updated_at' => $order['updated_at']
]);
?>