<?php
/**
 * User Profile Management
 * Online Food Ordering System - Phase 1
 * 
 * Allows users to view and edit their profile information
 */

require_once '../config.php';
require_once '../functions.php';

// Require login
requireLogin();

$currentUser = getCurrentUser();
$errors = [];
$success = false;

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        if ($_POST['action'] === 'update_profile') {
            // Update profile information
            $userData = [
                'username' => cleanInput($_POST['username'] ?? ''),
                'email' => cleanInput($_POST['email'] ?? ''),
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
            
            if (empty($userData['full_name'])) {
                $errors[] = 'Full name is required.';
            }
            
            if (!empty($userData['phone']) && !validatePhone($userData['phone'])) {
                $errors[] = 'Please enter a valid phone number.';
            }
            
            // Update profile if no errors
            if (empty($errors)) {
                if (updateUserProfile($currentUser['user_id'], $userData)) {
                    // Update session data
                    $_SESSION['user'] = array_merge($_SESSION['user'], $userData);
                    $currentUser = getCurrentUser();
                    
                    $_SESSION['flash_message']['success'] = 'Profile updated successfully!';
                    header('Location: profile.php');
                    exit();
                } else {
                    $errors[] = 'Failed to update profile. Email or username may already be in use.';
                }
            }
        } elseif ($_POST['action'] === 'change_password') {
            // Change password
            $currentPassword = $_POST['current_password'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            
            // Validation
            if (empty($currentPassword)) {
                $errors[] = 'Current password is required.';
            } elseif (!verifyPassword($currentPassword, getUserById($currentUser['user_id'])['password'])) {
                $errors[] = 'Current password is incorrect.';
            }
            
            if (empty($newPassword)) {
                $errors[] = 'New password is required.';
            } elseif (strlen($newPassword) < 6) {
                $errors[] = 'New password must be at least 6 characters long.';
            }
            
            if ($newPassword !== $confirmPassword) {
                $errors[] = 'New passwords do not match.';
            }
            
            if ($currentPassword === $newPassword) {
                $errors[] = 'New password must be different from current password.';
            }
            
            // Change password if no errors
            if (empty($errors)) {
                if (changeUserPassword($currentUser['user_id'], $newPassword)) {
                    $_SESSION['flash_message']['success'] = 'Password changed successfully!';
                    header('Location: profile.php');
                    exit();
                } else {
                    $errors[] = 'Failed to change password. Please try again.';
                }
            }
        }
    }
}

// Page configuration
$pageTitle = 'My Profile';
$bodyClass = 'profile-page';

// Include header
include '../includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h1 class="page-title">My Profile</h1>
        <p class="page-subtitle">Manage your account information and settings</p>
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
    
    <div class="grid grid-2">
        <!-- Profile Information -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Profile Information</h3>
            </div>
            <div class="card-body">
                <form method="POST" action="" data-validate>
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <input type="hidden" name="action" value="update_profile">
                    
                    <!-- Username -->
                    <div class="form-group">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" 
                               id="username" 
                               name="username" 
                               class="form-input" 
                               value="<?php echo htmlspecialchars($currentUser['username']); ?>"
                               required 
                               minlength="3"
                               pattern="[a-zA-Z0-9_]+">
                    </div>
                    
                    <!-- Email -->
                    <div class="form-group">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" 
                               id="email" 
                               name="email" 
                               class="form-input" 
                               value="<?php echo htmlspecialchars($currentUser['email']); ?>"
                               required>
                    </div>
                    
                    <!-- Full Name -->
                    <div class="form-group">
                        <label for="full_name" class="form-label">Full Name</label>
                        <input type="text" 
                               id="full_name" 
                               name="full_name" 
                               class="form-input" 
                               value="<?php echo htmlspecialchars($currentUser['full_name']); ?>"
                               required>
                    </div>
                    
                    <!-- Phone -->
                    <div class="form-group">
                        <label for="phone" class="form-label">Phone Number</label>
                        <input type="tel" 
                               id="phone" 
                               name="phone" 
                               class="form-input" 
                               value="<?php echo htmlspecialchars($currentUser['phone'] ?? ''); ?>"
                               placeholder="+60123456789">
                    </div>
                    
                    <!-- Address -->
                    <div class="form-group">
                        <label for="address" class="form-label">Address</label>
                        <textarea id="address" 
                                  name="address" 
                                  class="form-input form-textarea" 
                                  rows="3"
                                  placeholder="Enter your delivery address"><?php echo htmlspecialchars($currentUser['address'] ?? ''); ?></textarea>
                    </div>
                    
                    <!-- Submit Button -->
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                            Update Profile
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Account Security -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Account Security</h3>
            </div>
            <div class="card-body">
                <!-- Account Information -->
                <div class="account-info mb-4">
                    <div class="info-row">
                        <span class="info-label">Account Type:</span>
                        <span class="info-value">
                            <?php echo ucfirst($currentUser['role']); ?>
                            <?php if ($currentUser['role'] === 'admin'): ?>
                                <span class="badge badge-admin">Administrator</span>
                            <?php else: ?>
                                <span class="badge badge-customer">Customer</span>
                            <?php endif; ?>
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Member Since:</span>
                        <span class="info-value"><?php echo formatDate($currentUser['created_at']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Last Updated:</span>
                        <span class="info-value"><?php echo formatDateTime($currentUser['updated_at']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Account Status:</span>
                        <span class="info-value">
                            <?php if ($currentUser['status'] === 'active'): ?>
                                <span class="status-active">Active</span>
                            <?php else: ?>
                                <span class="status-inactive">Inactive</span>
                            <?php endif; ?>
                        </span>
                    </div>
                </div>
                
                <!-- Change Password Form -->
                <form method="POST" action="" data-validate>
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <input type="hidden" name="action" value="change_password">
                    
                    <h4 class="mb-3">Change Password</h4>
                    
                    <!-- Current Password -->
                    <div class="form-group">
                        <label for="current_password" class="form-label">Current Password</label>
                        <input type="password" 
                               id="current_password" 
                               name="current_password" 
                               class="form-input" 
                               required>
                    </div>
                    
                    <!-- New Password -->
                    <div class="form-group">
                        <label for="new_password" class="form-label">New Password</label>
                        <input type="password" 
                               id="new_password" 
                               name="new_password" 
                               class="form-input" 
                               required 
                               minlength="6">
                    </div>
                    
                    <!-- Confirm New Password -->
                    <div class="form-group">
                        <label for="confirm_password" class="form-label">Confirm New Password</label>
                        <input type="password" 
                               id="confirm_password" 
                               name="confirm_password" 
                               class="form-input" 
                               required 
                               minlength="6">
                    </div>
                    
                    <!-- Submit Button -->
                    <div class="form-group">
                        <button type="submit" class="btn btn-secondary">
                            Change Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="card mt-4">
        <div class="card-header">
            <h3 class="card-title">Quick Actions</h3>
        </div>
        <div class="card-body">
            <div class="quick-actions">
                <?php if (isCustomer()): ?>
                    <a href="<?php echo SITE_URL; ?>menu/menu.php" class="action-btn">
                        <div class="action-icon">🍽️</div>
                        <div class="action-text">
                            <strong>Browse Menu</strong>
                            <span>Explore our delicious food options</span>
                        </div>
                    </a>
                    
                    <a href="<?php echo SITE_URL; ?>menu/cart.php" class="action-btn">
                        <div class="action-icon">🛒</div>
                        <div class="action-text">
                            <strong>View Cart</strong>
                            <span>Check your current order items</span>
                        </div>
                    </a>
                    
                    <a href="<?php echo SITE_URL; ?>orders/orders.php" class="action-btn">
                        <div class="action-icon">📋</div>
                        <div class="action-text">
                            <strong>Order History</strong>
                            <span>View your past orders</span>
                        </div>
                    </a>
                <?php endif; ?>
                
                <?php if (isAdmin()): ?>
                    <a href="<?php echo SITE_URL; ?>admin/admin.php" class="action-btn">
                        <div class="action-icon">📊</div>
                        <div class="action-text">
                            <strong>Admin Dashboard</strong>
                            <span>View system overview</span>
                        </div>
                    </a>
                    
                    <a href="<?php echo SITE_URL; ?>admin/admin-orders.php" class="action-btn">
                        <div class="action-icon">📦</div>
                        <div class="action-text">
                            <strong>Manage Orders</strong>
                            <span>Process customer orders</span>
                        </div>
                    </a>
                    
                    <a href="<?php echo SITE_URL; ?>admin/admin-menu.php" class="action-btn">
                        <div class="action-icon">🍕</div>
                        <div class="action-text">
                            <strong>Menu Management</strong>
                            <span>Add and edit menu items</span>
                        </div>
                    </a>
                <?php endif; ?>
                
                <a href="<?php echo SITE_URL; ?>logout.php" class="action-btn logout-btn" data-confirm="Are you sure you want to logout?">
                    <div class="action-icon">🚪</div>
                    <div class="action-text">
                        <strong>Logout</strong>
                        <span>Sign out of your account</span>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>

<style>
/* Profile Page Specific Styles */
.account-info {
    background-color: #f8fafc;
    padding: 1.5rem;
    border-radius: 0.375rem;
    border: 1px solid #e2e8f0;
}

.info-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.5rem 0;
    border-bottom: 1px solid #e2e8f0;
}

.info-row:last-child {
    border-bottom: none;
}

.info-label {
    font-weight: 500;
    color: #475569;
}

.info-value {
    color: #1e293b;
    text-align: right;
}

.badge {
    display: inline-block;
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    font-size: 0.75rem;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.badge-admin {
    background-color: #fee2e2;
    color: #991b1b;
}

.badge-customer {
    background-color: #dbeafe;
    color: #1e40af;
}

.status-active {
    color: #059669;
    font-weight: 500;
}

.status-inactive {
    color: #dc2626;
    font-weight: 500;
}

.quick-actions {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1rem;
}

.action-btn {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    border: 1px solid #e2e8f0;
    border-radius: 0.375rem;
    background-color: white;
    text-decoration: none;
    color: #1e293b;
    transition: all 0.2s ease;
}

.action-btn:hover {
    background-color: #f8fafc;
    border-color: #334155;
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.action-btn.logout-btn:hover {
    background-color: #fef2f2;
    border-color: #ef4444;
    color: #dc2626;
}

.action-icon {
    font-size: 2rem;
    flex-shrink: 0;
}

.action-text {
    flex: 1;
}

.action-text strong {
    display: block;
    font-weight: 600;
    margin-bottom: 0.25rem;
}

.action-text span {
    font-size: 0.875rem;
    color: #64748b;
}

@media (max-width: 768px) {
    .grid-2 {
        grid-template-columns: 1fr;
    }
    
    .quick-actions {
        grid-template-columns: 1fr;
    }
    
    .info-row {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.25rem;
    }
    
    .info-value {
        text-align: left;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Password confirmation validation
    const newPasswordInput = document.getElementById('new_password');
    const confirmPasswordInput = document.getElementById('confirm_password');
    
    if (newPasswordInput && confirmPasswordInput) {
        confirmPasswordInput.addEventListener('input', function() {
            if (confirmPasswordInput.value && newPasswordInput.value !== confirmPasswordInput.value) {
                showFieldError(confirmPasswordInput, 'Passwords do not match');
            } else {
                clearFieldError(confirmPasswordInput);
            }
        });
    }
    
    // Form submission handlers
    const forms = document.querySelectorAll('form[data-validate]');
    forms.forEach(form => {
        form.addEventListener('submit', function() {
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                showLoading(submitBtn);
            }
        });
    });
    
    // Auto-save draft functionality (optional enhancement)
    const profileForm = document.querySelector('form[action=""][method="POST"]');
    if (profileForm) {
        const inputs = profileForm.querySelectorAll('input, textarea');
        inputs.forEach(input => {
            input.addEventListener('input', debounce(function() {
                // Save to sessionStorage for recovery
                const formData = new FormData(profileForm);
                const data = {};
                for (let [key, value] of formData.entries()) {
                    if (key !== 'csrf_token' && key !== 'action') {
                        data[key] = value;
                    }
                }
                sessionStorage.setItem('profile_draft', JSON.stringify(data));
            }, 1000));
        });
        
        // Restore draft on page load
        const draft = sessionStorage.getItem('profile_draft');
        if (draft) {
            try {
                const data = JSON.parse(draft);
                Object.keys(data).forEach(key => {
                    const input = profileForm.querySelector(`[name="${key}"]`);
                    if (input && input.value === '') {
                        input.value = data[key];
                    }
                });
            } catch (e) {
                sessionStorage.removeItem('profile_draft');
            }
        }
        
        // Clear draft on successful submission
        profileForm.addEventListener('submit', function() {
            sessionStorage.removeItem('profile_draft');
        });
    }
});

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}
</script>

<?php
// Include footer
include '../includes/footer.php';
?>