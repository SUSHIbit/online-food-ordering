<?php
/**
 * Logout Handler
 * Online Food Ordering System - Phase 1
 * 
 * Handles user logout and session cleanup
 */

require_once 'config.php';
require_once 'functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: ' . SITE_URL . 'auth/login.php');
    exit();
}

// Verify logout request (prevent CSRF)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && !isset($_GET['confirm'])) {
    // Show confirmation page for GET requests without confirmation
    $pageTitle = 'Logout Confirmation';
    include 'includes/header.php';
    ?>
    
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h1 class="auth-title">Confirm Logout</h1>
                <p class="auth-subtitle">Are you sure you want to log out?</p>
            </div>
            
            <div class="text-center">
                <p class="mb-4">
                    You are currently logged in as <strong><?php echo htmlspecialchars(getCurrentUser()['full_name']); ?></strong>.
                </p>
                
                <div class="flex-center gap-3">
                    <a href="?confirm=1" class="btn btn-primary">
                        Yes, Log Out
                    </a>
                    <a href="javascript:history.back()" class="btn btn-secondary">
                        Cancel
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <?php
    include 'includes/footer.php';
    exit();
}

// Perform logout
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['confirm'])) {
    // Clear remember me cookie if it exists
    if (isset($_COOKIE['remember_token'])) {
        setcookie('remember_token', '', time() - 3600, '/', '', false, true);
        // In production, also remove the token from database
    }
    
    // Store user info for goodbye message
    $userName = getCurrentUser()['full_name'];
    
    // Log out user
    logoutUser();
    
    // Set flash message for login page
    session_start();
    $_SESSION['flash_message']['success'] = "Goodbye, {$userName}! You have been logged out successfully.";
    
    // Redirect to login page with logout message
    header('Location: ' . SITE_URL . 'auth/login.php?message=logout');
    exit();
}

// Handle POST requests (for AJAX or form submissions)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token for POST requests
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        http_response_code(403);
        if (isset($_POST['ajax'])) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid request']);
        } else {
            header('Location: ' . SITE_URL);
        }
        exit();
    }
    
    // Clear remember me cookie
    if (isset($_COOKIE['remember_token'])) {
        setcookie('remember_token', '', time() - 3600, '/', '', false, true);
    }
    
    // Store user info for goodbye message
    $userName = getCurrentUser()['full_name'];
    
    // Log out user
    logoutUser();
    
    // Handle AJAX requests
    if (isset($_POST['ajax'])) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => "Goodbye, {$userName}! You have been logged out successfully.",
            'redirect' => SITE_URL . 'auth/login.php?message=logout'
        ]);
        exit();
    }
    
    // Set flash message for regular form submissions
    session_start();
    $_SESSION['flash_message']['success'] = "Goodbye, {$userName}! You have been logged out successfully.";
    
    // Redirect to login page
    header('Location: ' . SITE_URL . 'auth/login.php?message=logout');
    exit();
}

// Fallback redirect
header('Location: ' . SITE_URL);
exit();
?>