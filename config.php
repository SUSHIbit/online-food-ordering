<?php
/**
 * Database Configuration File
 * Online Food Ordering System - Phase 1
 * 
 * This file contains database connection settings and establishes
 * the connection to MySQL database.
 */

// Start session for user authentication
session_start();

// Database configuration constants
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'online_food_ordering');

// Application configuration
define('SITE_NAME', 'Online Food Ordering');
define('SITE_URL', 'http://localhost/online-food-ordering/');
define('UPLOAD_PATH', 'assets/images/');

// Database connection
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Set charset to UTF-8
mysqli_set_charset($conn, "utf8");

/**
 * Get current user information from session
 * @return array|null User data or null if not logged in
 */
function getCurrentUser() {
    return isset($_SESSION['user']) ? $_SESSION['user'] : null;
}

/**
 * Check if user is logged in
 * @return bool True if logged in, false otherwise
 */
function isLoggedIn() {
    return isset($_SESSION['user']) && isset($_SESSION['user']['user_id']);
}

/**
 * Check if current user has admin role
 * @return bool True if admin, false otherwise
 */
function isAdmin() {
    return isLoggedIn() && $_SESSION['user']['role'] === 'admin';
}

/**
 * Check if current user has customer role
 * @return bool True if customer, false otherwise
 */
function isCustomer() {
    return isLoggedIn() && $_SESSION['user']['role'] === 'customer';
}

/**
 * Redirect to login page if not logged in
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: " . SITE_URL . "auth/login.php");
        exit();
    }
}

/**
 * Redirect to login page if not admin
 */
function requireAdmin() {
    if (!isAdmin()) {
        header("Location: " . SITE_URL . "auth/login.php");
        exit();
    }
}

/**
 * Redirect to appropriate page based on user role
 */
function redirectByRole() {
    if (isAdmin()) {
        header("Location: " . SITE_URL . "admin/admin.php");
    } else if (isCustomer()) {
        header("Location: " . SITE_URL . "index.php");
    } else {
        header("Location: " . SITE_URL . "auth/login.php");
    }
    exit();
}

/**
 * Clean and sanitize input data
 * @param string $data Input data to clean
 * @return string Cleaned data
 */
function cleanInput($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return mysqli_real_escape_string($conn, $data);
}

/**
 * Display success message
 * @param string $message Success message
 */
function showSuccess($message) {
    echo "<div class='alert alert-success'>" . htmlspecialchars($message) . "</div>";
}

/**
 * Display error message
 * @param string $message Error message
 */
function showError($message) {
    echo "<div class='alert alert-error'>" . htmlspecialchars($message) . "</div>";
}

/**
 * Display info message
 * @param string $message Info message
 */
function showInfo($message) {
    echo "<div class='alert alert-info'>" . htmlspecialchars($message) . "</div>";
}

/**
 * Generate CSRF token
 * @return string CSRF token
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 * @param string $token Token to verify
 * @return bool True if valid, false otherwise
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Set timezone
date_default_timezone_set('Asia/Kuala_Lumpur');
?>