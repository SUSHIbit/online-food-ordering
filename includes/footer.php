</main>
    
    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <!-- Footer Links -->
                <div class="footer-links">
                    <a href="<?php echo SITE_URL; ?>" class="footer-link">Home</a>
                    <a href="<?php echo SITE_URL; ?>menu/menu.php" class="footer-link">Menu</a>
                    <?php if (isLoggedIn()): ?>
                        <?php if (isCustomer()): ?>
                            <a href="<?php echo SITE_URL; ?>orders/orders.php" class="footer-link">My Orders</a>
                        <?php endif; ?>
                        <a href="<?php echo SITE_URL; ?>auth/profile.php" class="footer-link">Profile</a>
                    <?php else: ?>
                        <a href="<?php echo SITE_URL; ?>auth/login.php" class="footer-link">Login</a>
                        <a href="<?php echo SITE_URL; ?>auth/register.php" class="footer-link">Register</a>
                    <?php endif; ?>
                </div>
                
                <!-- Copyright -->
                <div class="footer-copyright">
                    <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.</p>
                    <p>Built with ❤️ for final year project demonstration</p>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- JavaScript -->
    <script src="<?php echo SITE_URL; ?>assets/js/script.js"></script>
    
    <!-- Additional JavaScript for specific pages -->
    <?php if (isset($additionalJS)): ?>
        <?php foreach ($additionalJS as $js): ?>
            <script src="<?php echo $js; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <!-- Inline JavaScript for specific pages -->
    <?php if (isset($inlineJS)): ?>
        <script>
            <?php echo $inlineJS; ?>
        </script>
    <?php endif; ?>
    
    <!-- Flash Messages Handler -->
    <?php if (isset($_SESSION['flash_message'])): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                <?php foreach ($_SESSION['flash_message'] as $type => $message): ?>
                    FoodOrderingApp.showAlert('<?php echo addslashes($message); ?>', '<?php echo $type; ?>');
                <?php endforeach; ?>
            });
        </script>
        <?php unset($_SESSION['flash_message']); ?>
    <?php endif; ?>
</body>
</html>