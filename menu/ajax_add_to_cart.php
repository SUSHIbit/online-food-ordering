<?php
/**
 * AJAX Add to Cart Handler
 * Online Food Ordering System - Phase 3
 * 
 * Handle adding items to cart via AJAX
 * Save this file as: menu/ajax_add_to_cart.php
 */

require_once '../config.php';
require_once '../functions.php';

// Check if request is AJAX
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
    http_response_code(403);
    exit();
}

// Set JSON response header
header('Content-Type: application/json');

// Check if user is logged in and is customer
if (!isLoggedIn() || !isCustomer()) {
    echo json_encode([
        'success' => false,
        'message' => 'Please login as customer to add items to cart'
    ]);
    exit();
}

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid request token'
        ]);
        exit();
    }
    
    $itemId = (int)($_POST['item_id'] ?? 0);
    $quantity = (int)($_POST['quantity'] ?? 1);
    
    if ($itemId <= 0 || $quantity <= 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid item or quantity'
        ]);
        exit();
    }
    
    // Get item details to verify it exists and is available
    $item = getMenuItemById($itemId);
    if (!$item) {
        echo json_encode([
            'success' => false,
            'message' => 'Item not found'
        ]);
        exit();
    }
    
    if ($item['availability'] !== 'available') {
        echo json_encode([
            'success' => false,
            'message' => 'Item is currently unavailable'
        ]);
        exit();
    }
    
    // Add to cart
    if (addToCart($itemId, $quantity)) {
        $cartCount = getCartCount();
        
        echo json_encode([
            'success' => true,
            'message' => $item['item_name'] . ' added to cart!',
            'cart_count' => $cartCount
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to add item to cart'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
}
?>