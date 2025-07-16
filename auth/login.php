<?php
/**
 * User Login Page
 * Online Food Ordering System - Phase 1
 * 
 * Handles user authentication with role-based redirection
 */

require_once '../config.php';
require_once '../functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirectByRole();
}

// Handle form submission
$errors = [];
$loginAttempts = 0;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check rate limiting (simple session-based)
    if (!isset($_SESSION['login_attempts'])) {
        $_SESSION['login_attempts'] = 0;
        $_SESSION['last_attempt'] = time();
    }
    
    // Reset attempts after 15 minutes
    if (time() - $_SESSION['last_attempt'] > 900) {
        $_SESSION['login_attempts'] = 0;
    }
    
    // Check if too many attempts
    if ($_SESSION['login_attempts'] >= 5) {
        $errors[] = 'Too many login attempts. Please try again in 15 minutes.';
    } else {
        // Verify CSRF token
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            $errors[] = 'Invalid request. Please try again.';
        } else {
            // Get and sanitize form data
            $login = cleanInput($_POST['login'] ?? '');
            $password = $_POST['password'] ?? '';
            $remember = isset($_POST['remember']);
            
            // Validation
            if (empty($login)) {
                $errors[] = 'Email or username is required.';
            }
            
            if (empty($password)) {
                $errors[] = 'Password is required.';
            }
            
            // Authenticate user if no validation errors
            if (empty($errors)) {
                $user = authenticateUser($login, $password);
                
                if ($user) {
                    // Reset login attempts on successful login
                    $_SESSION['login_attempts'] = 0;
                    
                    // Log user in
                    loginUser($user);
                    
                    // Set remember me cookie if requested
                    if ($remember) {
                        $token = generateRandomString(32);
                        setcookie('remember_token', $token, time() + (30 * 24 * 60 * 60), '/', '', false, true);
                        // In production, store this token in database associated with user
                    }
                    
                    // Redirect based on role and return URL
                    $returnUrl = $_GET['return'] ?? '';
                    if (!empty($returnUrl) && filter_var($returnUrl, FILTER_VALIDATE_URL) === false) {
                        // Simple validation for return URL - should be relative path
                        if (strpos($returnUrl, '/') === 0 && strpos($returnUrl, '//') !== 0) {
                            header('Location: ' . SITE_URL . ltrim($returnUrl, '/'));
                            exit();
                        }
                    }
                    
                    // Default redirection based on role
                    redirectByRole();
                } else {
                    // Increment login attempts
                    $_SESSION['login_attempts']++;
                    $_SESSION['last_attempt'] = time();
                    
                    $errors[] = 'Invalid email/username or password.';
                    
                    // Add attempt counter warning
                    $remainingAttempts = 5 - $_SESSION['login_attempts'];
                    if ($remainingAttempts > 0 && $_SESSION['login_attempts'] >= 2) {
                        $errors[] = "Warning: {$remainingAttempts} login attempts remaining.";
                    }
                }
            }
        }
    }
}

// Page configuration
$pageTitle = 'Login';
$bodyClass = 'auth-page';

// Include header
include '../includes/header.php';
?>

<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <h1 class="auth-title">Welcome Back</h1>
            <p class="auth-subtitle">Sign in to your account to continue</p>
        </div>
        
        <!-- Display Errors -->
        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <ul style="margin: 0; padding-left: 1.5rem;">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <!-- Demo Credentials Info -->
        <div class="alert alert-info">
            <strong>Demo Credentials:</strong><br>
            <strong>Admin:</strong> admin / admin123<br>
            <strong>Customer:</strong> Register a new account or use any registered email
        </div>
        
        <!-- Login Form -->
        <form method="POST" action="" data-validate novalidate>
            <!-- CSRF Token -->
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            
            <!-- Email/Username -->
            <div class="form-group">
                <label for="login" class="form-label">Email or Username</label>
                <input type="text" 
                       id="login" 
                       name="login" 
                       class="form-input" 
                       value="<?php echo htmlspecialchars($_POST['login'] ?? ''); ?>"
                       required 
                       autofocus
                       placeholder="Enter your email or username">
            </div>
            
            <!-- Password -->
            <div class="form-group">
                <label for="password" class="form-label">Password</label>
                <input type="password" 
                       id="password" 
                       name="password" 
                       class="form-input" 
                       required
                       placeholder="Enter your password">
            </div>
            
            <!-- Remember Me and Forgot Password -->
            <div class="form-group">
                <div class="form-row">
                    <label class="checkbox-label">
                        <input type="checkbox" 
                               name="remember" 
                               value="1"
                               <?php echo (isset($_POST['remember'])) ? 'checked' : ''; ?>>
                        <span class="checkmark"></span>
                        Remember me for 30 days
                    </label>
                    
                    <a href="#" class="auth-link forgot-password" data-modal="forgot-password-modal">
                        Forgot Password?
                    </a>
                </div>
            </div>
            
            <!-- Submit Button -->
            <div class="form-group">
                <button type="submit" class="btn btn-primary btn-full btn-large">
                    Sign In
                </button>
            </div>
        </form>
        
        <!-- Register Link -->
        <div class="auth-footer">
            <p>Don't have an account? 
                <a href="register.php" class="auth-link">Create one here</a>
            </p>
        </div>
    </div>
</div>

<!-- Forgot Password Modal -->
<div id="forgot-password-modal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Reset Password</h3>
            <button type="button" class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <p class="mb-4">
                Password reset functionality will be implemented in a future update. 
                For now, please contact the administrator for password assistance.
            </p>
            <form id="forgot-password-form">
                <div class="form-group">
                    <label for="reset-email" class="form-label">Email Address</label>
                    <input type="email" 
                           id="reset-email" 
                           name="reset_email" 
                           class="form-input" 
                           placeholder="Enter your email address"
                           required>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary btn-full" disabled>
                        Send Reset Link (Coming Soon)
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
/* Login Form Specific Styles */
.form-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
}

.checkbox-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    cursor: pointer;
    font-size: 0.875rem;
}

.checkbox-label input[type="checkbox"] {
    margin: 0;
    width: auto;
}

.forgot-password {
    font-size: 0.875rem;
    white-space: nowrap;
}

.demo-credentials {
    background-color: #f0f9ff;
    border: 1px solid #bae6fd;
    color: #0c4a6e;
    padding: 1rem;
    border-radius: 0.375rem;
    margin-bottom: 1.5rem;
    font-size: 0.875rem;
}

.demo-credentials strong {
    color: #075985;
}

/* Rate limiting warning */
.rate-limit-warning {
    background-color: #fef3c7;
    border: 1px solid #fbbf24;
    color: #92400e;
    padding: 1rem;
    border-radius: 0.375rem;
    margin-bottom: 1rem;
    font-size: 0.875rem;
}

/* Loading overlay for form */
.form-loading {
    position: relative;
    pointer-events: none;
    opacity: 0.6;
}

.form-loading::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.8);
    display: flex;
    align-items: center;
    justify-content: center;
}

@media (max-width: 480px) {
    .form-row {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }
    
    .forgot-password {
        align-self: flex-end;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-focus on login field if empty
    const loginField = document.getElementById('login');
    if (loginField && !loginField.value) {
        loginField.focus();
    }
    
    // Form submission with loading state
    const form = document.querySelector('form[data-validate]');
    if (form) {
        form.addEventListener('submit', function() {
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn && !form.querySelector('.field-error')) {
                showLoading(submitBtn);
                form.classList.add('form-loading');
            }
        });
    }
    
    // Auto-login demo functionality
    const demoButtons = document.querySelectorAll('.demo-login');
    demoButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const role = button.getAttribute('data-role');
            
            if (role === 'admin') {
                document.getElementById('login').value = 'admin';
                document.getElementById('password').value = 'admin123';
            }
            
            // Auto-submit form
            form.submit();
        });
    });
    
    // Forgot password form handler
    const forgotForm = document.getElementById('forgot-password-form');
    if (forgotForm) {
        forgotForm.addEventListener('submit', function(e) {
            e.preventDefault();
            FoodOrderingApp.showAlert('Password reset feature will be available soon. Please contact support for assistance.', 'info');
            FoodOrderingApp.closeModal('forgot-password-modal');
        });
    }
    
    // Show appropriate message based on URL parameters
    const urlParams = new URLSearchParams(window.location.search);
    const message = urlParams.get('message');
    
    if (message === 'logout') {
        FoodOrderingApp.showAlert('You have been logged out successfully.', 'success');
    } else if (message === 'session_expired') {
        FoodOrderingApp.showAlert('Your session has expired. Please log in again.', 'warning');
    } else if (message === 'access_denied') {
        FoodOrderingApp.showAlert('Access denied. Please log in to continue.', 'error');
    }
});

// Auto-clear URL parameters after showing message
if (window.location.search) {
    setTimeout(() => {
        const url = new URL(window.location);
        url.search = '';
        window.history.replaceState({}, document.title, url);
    }, 100);
}
</script>

<?php
// Include footer
include '../includes/footer.php';
?>