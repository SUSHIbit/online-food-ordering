<?php
/**
 * Common Header Component
 * Online Food Ordering System - Phase 1
 * 
 * This file contains the common HTML header used across all pages.
 */

// Include configuration if not already included
if (!defined('DB_HOST')) {
    require_once __DIR__ . '/../config.php';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Online Food Ordering System - Order delicious food online with ease">
    <meta name="keywords" content="food, ordering, restaurant, delivery, online">
    <meta name="author" content="Online Food Ordering System">
    
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' . SITE_NAME : SITE_NAME; ?></title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?php echo SITE_URL; ?>assets/images/favicon.ico">
    
    <!-- CSS -->
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>assets/css/styles.css">
    
    <!-- Additional CSS for specific pages -->
    <?php if (isset($additionalCSS)): ?>
        <?php foreach ($additionalCSS as $css): ?>
            <link rel="stylesheet" href="<?php echo $css; ?>">
        <?php endforeach; ?>
    <?php endif; ?>
    
    <!-- Meta tags for specific pages -->
    <?php if (isset($metaTags)): ?>
        <?php echo $metaTags; ?>
    <?php endif; ?>
</head>
<body class="<?php echo isset($bodyClass) ? $bodyClass : ''; ?>">
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="header-content">
                <!-- Logo -->
                <a href="<?php echo SITE_URL; ?>" class="logo">
                    <?php echo SITE_NAME; ?>
                </a>
                
                <!-- Mobile Navigation Toggle -->
                <button class="mobile-nav-toggle" type="button" aria-label="Toggle navigation">
                    <span>☰</span>
                </button>
                
                <!-- Navigation -->
                <?php include __DIR__ . '/nav.php'; ?>
            </div>
        </div>
    </header>
    
    <!-- Main Content -->
    <main class="main">
        <?php if (isset($_SESSION['flash_message'])): ?>
            <?php foreach ($_SESSION['flash_message'] as $type => $message): ?>
                <div class="alert alert-<?php echo $type; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endforeach; ?>
            <?php unset($_SESSION['flash_message']); ?>
        <?php endif; ?>