<?php
/**
 * CSRF Token Generator for AJAX
 * Online Food Ordering System - Phase 3
 * 
 * Generate CSRF token for AJAX requests
 * Save as: ajax/get_csrf_token.php
 */

require_once '../config.php';

// Check if request is AJAX
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
    http_response_code(403);
    exit();
}

// Set JSON response header
header('Content-Type: application/json');

echo json_encode([
    'success' => true,
    'csrf_token' => generateCSRFToken()
]);
?>