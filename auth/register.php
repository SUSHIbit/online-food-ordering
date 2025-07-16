<?php
/**
 * User Registration Page
 * Online Food Ordering System - Phase 1
 * 
 * Handles user registration with validation and security
 */

require_once '../config.php';
require_once '../functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirectByRole();
}

// Handle form submission
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Verify CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        // Get and sanitize form data
        $userData = [
            'username' => cleanInput($_POST['username'] ?? ''),
            'email' => cleanInput($_POST['email'] ?? ''),
            'password' => $_POST['password'] ?? '',
            'confirm_password' => $_POST['confirm_password'] ?? '',
            'full_name' => cleanInput($_POST['full_name'] ?? ''),
            'phone' => cleanInput($_POST['phone'] ?? ''),
            'address' => cleanInput($_POST['address'] ?? '')
        ];
        
        // Validation
        if (empty($userData['username'])) {
            $errors[] = 'Username is required.';
        } elseif (strlen($userData['username']) < 3) {
            $errors[] = 'Username must be at least 3 characters long.';
        } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $userData['username'])) {
            $errors[] = 'Username can only contain letters, numbers, and underscores.';
        }
        
        if (empty($userData['email'])) {
            $errors[] = 'Email is required.';
        } elseif (!validateEmail($userData['email'])) {
            $errors[] = 'Please enter a valid email address.';
        }
        
        if (empty($userData['password'])) {
            $errors[] = 'Password is required.';
        } elseif (strlen($userData['password']) < 6) {
            $errors[] = 'Password must be at least 6 characters long.';
        }
        
        if ($userData['password'] !== $userData['confirm_password']) {
            $errors[] = 'Passwords do not match.';
        }
        
        if (empty($userData['full_name'])) {
            $errors[] = 'Full name is required.';
        }
        
        if (!empty($userData['phone']) && !validatePhone($userData['phone'])) {
            $errors[] = 'Please enter a valid phone number.';
        }
        
        // Check if username or email already exists
        if (empty($errors)) {
            if (usernameExists($userData['username'])) {
                $errors[] = 'Username already exists. Please choose a different one.';
            }
            
            if (emailExists($userData['email'])) {
                $errors[] = 'Email already exists. Please use a different email address.';
            }
        }
        
        // Create user if no errors
        if (empty($errors)) {
            $userId = createUser($userData);
            
            if ($userId) {
                $success = true;
                $_SESSION['flash_message']['success'] = 'Registration successful! You can now log in with your credentials.';
                header('Location: login.php');
                exit();
            } else {
                $errors[] = 'Registration failed. Please try again.';
            }
        }
    }
}

// Page configuration
$pageTitle = 'Register';
$bodyClass = 'auth-page';

// Include header
include '../includes/header.php';
?>

<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <h1 class="auth-title">Create Account</h1>
            <p class="auth-subtitle">Join us and start ordering delicious food</p>
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
        
        <!-- Registration Form -->
        <form method="POST" action="" data-validate novalidate>
            <!-- CSRF Token -->
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            
            <!-- Username -->
            <div class="form-group">
                <label for="username" class="form-label">Username *</label>
                <input type="text" 
                       id="username" 
                       name="username" 
                       class="form-input" 
                       value="<?php echo htmlspecialchars($userData['username'] ?? ''); ?>"
                       required 
                       minlength="3"
                       pattern="[a-zA-Z0-9_]+"
                       title="Username can only contain letters, numbers, and underscores">
                <small class="text-slate-500">
                    Username must be at least 3 characters and contain only letters, numbers, and underscores.
                </small>
            </div>
            
            <!-- Email -->
            <div class="form-group">
                <label for="email" class="form-label">Email Address *</label>
                <input type="email" 
                       id="email" 
                       name="email" 
                       class="form-input" 
                       value="<?php echo htmlspecialchars($userData['email'] ?? ''); ?>"
                       required>
            </div>
            
            <!-- Full Name -->
            <div class="form-group">
                <label for="full_name" class="form-label">Full Name *</label>
                <input type="text" 
                       id="full_name" 
                       name="full_name" 
                       class="form-input" 
                       value="<?php echo htmlspecialchars($userData['full_name'] ?? ''); ?>"
                       required>
            </div>
            
            <!-- Phone -->
            <div class="form-group">
                <label for="phone" class="form-label">Phone Number</label>
                <input type="tel" 
                       id="phone" 
                       name="phone" 
                       class="form-input" 
                       value="<?php echo htmlspecialchars($userData['phone'] ?? ''); ?>"
                       placeholder="+60123456789">
                <small class="text-slate-500">
                    Optional. Format: +60123456789 or 0123456789
                </small>
            </div>
            
            <!-- Address -->
            <div class="form-group">
                <label for="address" class="form-label">Address</label>
                <textarea id="address" 
                          name="address" 
                          class="form-input form-textarea" 
                          rows="3"
                          placeholder="Enter your delivery address"><?php echo htmlspecialchars($userData['address'] ?? ''); ?></textarea>
                <small class="text-slate-500">
                    Optional. This will be used as your default delivery address.
                </small>
            </div>
            
            <!-- Password -->
            <div class="form-group">
                <label for="password" class="form-label">Password *</label>
                <input type="password" 
                       id="password" 
                       name="password" 
                       class="form-input" 
                       required 
                       minlength="6">
                <small class="text-slate-500">
                    Password must be at least 6 characters long.
                </small>
            </div>
            
            <!-- Confirm Password -->
            <div class="form-group">
                <label for="confirm_password" class="form-label">Confirm Password *</label>
                <input type="password" 
                       id="confirm_password" 
                       name="confirm_password" 
                       class="form-input" 
                       required 
                       minlength="6">
            </div>
            
            <!-- Terms and Conditions -->
            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" 
                           name="terms" 
                           required>
                    <span class="checkmark"></span>
                    I agree to the <a href="#" class="auth-link">Terms and Conditions</a> and <a href="#" class="auth-link">Privacy Policy</a>
                </label>
            </div>
            
            <!-- Submit Button -->
            <div class="form-group">
                <button type="submit" class="btn btn-primary btn-full btn-large">
                    Create Account
                </button>
            </div>
        </form>
        
        <!-- Login Link -->
        <div class="auth-footer">
            <p>Already have an account? 
                <a href="login.php" class="auth-link">Sign in here</a>
            </p>
        </div>
    </div>
</div>

<style>
/* Registration Form Specific Styles */
.checkbox-label {
    display: flex;
    align-items: flex-start;
    gap: 0.5rem;
    cursor: pointer;
    font-size: 0.875rem;
    line-height: 1.4;
}

.checkbox-label input[type="checkbox"] {
    margin: 0;
    width: auto;
}

.form-input.error {
    border-color: #ef4444;
    box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
}

.field-error {
    color: #ef4444;
    font-size: 0.875rem;
    margin-top: 0.25rem;
}

.form-textarea {
    resize: vertical;
    min-height: 80px;
}

/* Password strength indicator */
.password-strength {
    margin-top: 0.5rem;
    font-size: 0.875rem;
}

.strength-weak { color: #ef4444; }
.strength-medium { color: #f59e0b; }
.strength-strong { color: #10b981; }

/* Loading state for submit button */
.btn.loading {
    opacity: 0.7;
    pointer-events: none;
}

.btn.loading::after {
    content: '';
    width: 16px;
    height: 16px;
    margin-left: 0.5rem;
    border: 2px solid transparent;
    border-top: 2px solid currentColor;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    display: inline-block;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Password strength checker
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirm_password');
    
    if (passwordInput) {
        // Add password strength indicator
        const strengthDiv = document.createElement('div');
        strengthDiv.className = 'password-strength';
        passwordInput.parentNode.appendChild(strengthDiv);
        
        passwordInput.addEventListener('input', function() {
            const password = passwordInput.value;
            const strength = getPasswordStrength(password);
            
            strengthDiv.className = 'password-strength strength-' + strength.level;
            strengthDiv.textContent = strength.message;
        });
    }
    
    // Real-time password confirmation
    if (confirmPasswordInput && passwordInput) {
        confirmPasswordInput.addEventListener('input', function() {
            if (confirmPasswordInput.value && passwordInput.value !== confirmPasswordInput.value) {
                showFieldError(confirmPasswordInput, 'Passwords do not match');
            } else {
                clearFieldError(confirmPasswordInput);
            }
        });
    }
    
    // Form submission with loading state
    const form = document.querySelector('form[data-validate]');
    if (form) {
        form.addEventListener('submit', function() {
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                showLoading(submitBtn);
            }
        });
    }
});

function getPasswordStrength(password) {
    if (password.length < 6) {
        return { level: 'weak', message: 'Password too short' };
    }
    
    let score = 0;
    
    // Length bonus
    if (password.length >= 8) score++;
    if (password.length >= 12) score++;
    
    // Character variety
    if (/[a-z]/.test(password)) score++;
    if (/[A-Z]/.test(password)) score++;
    if (/[0-9]/.test(password)) score++;
    if (/[^A-Za-z0-9]/.test(password)) score++;
    
    if (score < 3) {
        return { level: 'weak', message: 'Weak password' };
    } else if (score < 5) {
        return { level: 'medium', message: 'Medium strength' };
    } else {
        return { level: 'strong', message: 'Strong password' };
    }
}
</script>

<?php
// Include footer
include '../includes/footer.php';
?>