<?php
/**
 * AJAX Dashboard Stats Handler
 * Save as: ajax/get_dashboard_stats.php
 */

require_once '../config.php';
require_once '../functions.php';

if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || !isAdmin()) {
    http_response_code(403);
    exit();
}

header('Content-Type: application/json');

$stats = getDashboardStats();

echo json_encode([
    'success' => true,
    'stats' => $stats
]);
?>

---

<?php
/**
 * Update Admin Password (First Login)
 * Save as: admin/update_admin_password.php
 */

require_once '../config.php';
require_once '../functions.php';

requireAdmin();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['flash_message']['error'] = 'Invalid request.';
        header('Location: admin.php');
        exit();
    }
    
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    if ($newPassword !== $confirmPassword) {
        $_SESSION['flash_message']['error'] = 'Passwords do not match.';
    } elseif (strlen($newPassword) < 6) {
        $_SESSION['flash_message']['error'] = 'Password must be at least 6 characters.';
    } else {
        $currentUser = getCurrentUser();
        if (changeUserPassword($currentUser['user_id'], $newPassword)) {
            $_SESSION['flash_message']['success'] = 'Password updated successfully!';
        } else {
            $_SESSION['flash_message']['error'] = 'Failed to update password.';
        }
    }
}

header('Location: admin.php');
exit();
?>

---

/**
 * Create Upload Directory Structure
 * Run this PHP script once to create directories
 */

<?php
$uploadDirs = [
    'assets/images',
    'assets/images/menu'
];

foreach ($uploadDirs as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
        echo "Created directory: $dir\n";
    } else {
        echo "Directory exists: $dir\n";
    }
}

echo "Upload directories setup complete!\n";
?>