<?php
/**
 * FIXED: AJAX Cart Count Handler
 * Online Food Ordering System - Bug Fix
 */

require_once '../config.php';
require_once '../functions.php';

// Set JSON response header FIRST
header('Content-Type: application/json');

// Check if request is AJAX
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
    exit();
}

// Check if user is logged in and is customer
if (!isLoggedIn()) {
    echo json_encode(['success' => true, 'count' => 0]);
    exit();
}

if (!isCustomer()) {
    echo json_encode(['success' => true, 'count' => 0]);
    exit();
}

// Get cart count from session
$cartCount = 0;
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $cartCount += (int)$item['quantity'];
    }
}

// Return proper JSON response
echo json_encode([
    'success' => true, 
    'count' => $cartCount
]);
exit();
?>