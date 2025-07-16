<?php
// 1. Create: ajax/get_cart_count.php
/**
 * AJAX Handler for Cart Count
 */

require_once '../config.php';
require_once '../functions.php';

if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
    http_response_code(403);
    exit();
}

header('Content-Type: application/json');

if (!isLoggedIn() || !isCustomer()) {
    echo json_encode(['success' => true, 'count' => 0]);
    exit();
}

$cartCount = getCartCount();
echo json_encode(['success' => true, 'count' => $cartCount]);
?>