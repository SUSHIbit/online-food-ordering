<?php
/**
 * Helper Functions File
 * Online Food Ordering System - Phase 1
 * 
 * This file contains utility functions used throughout the application.
 */

/**
 * Hash password securely
 * @param string $password Plain text password
 * @return string Hashed password
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Verify password against hash
 * @param string $password Plain text password
 * @param string $hash Stored password hash
 * @return bool True if password matches, false otherwise
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Validate email format
 * @param string $email Email address to validate
 * @return bool True if valid, false otherwise
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate phone number (basic validation)
 * @param string $phone Phone number to validate
 * @return bool True if valid, false otherwise
 */
function validatePhone($phone) {
    return preg_match('/^[0-9+\-\s()]{10,20}$/', $phone);
}

/**
 * Get user by ID
 * @param int $userId User ID
 * @return array|null User data or null if not found
 */
function getUserById($userId) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ? AND status = 'active'");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

/**
 * Get user by email
 * @param string $email User email
 * @return array|null User data or null if not found
 */
function getUserByEmail($email) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND status = 'active'");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

/**
 * Get user by username
 * @param string $username Username
 * @return array|null User data or null if not found
 */
function getUserByUsername($username) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? AND status = 'active'");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

/**
 * Check if email exists
 * @param string $email Email to check
 * @param int $excludeUserId User ID to exclude (for updates)
 * @return bool True if exists, false otherwise
 */
function emailExists($email, $excludeUserId = null) {
    global $conn;
    $sql = "SELECT user_id FROM users WHERE email = ?";
    $params = [$email];
    
    if ($excludeUserId) {
        $sql .= " AND user_id != ?";
        $params[] = $excludeUserId;
    }
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(str_repeat('s', count($params)), ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0;
}

/**
 * Check if username exists
 * @param string $username Username to check
 * @param int $excludeUserId User ID to exclude (for updates)
 * @return bool True if exists, false otherwise
 */
function usernameExists($username, $excludeUserId = null) {
    global $conn;
    $sql = "SELECT user_id FROM users WHERE username = ?";
    $params = [$username];
    
    if ($excludeUserId) {
        $sql .= " AND user_id != ?";
        $params[] = $excludeUserId;
    }
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(str_repeat('s', count($params)), ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0;
}

/**
 * Create new user account
 * @param array $userData User data array
 * @return bool|int User ID if successful, false otherwise
 */
function createUser($userData) {
    global $conn;
    
    // Validate required fields
    if (empty($userData['username']) || empty($userData['email']) || empty($userData['password'])) {
        return false;
    }
    
    // Check if email or username already exists
    if (emailExists($userData['email']) || usernameExists($userData['username'])) {
        return false;
    }
    
    // Hash password
    $hashedPassword = hashPassword($userData['password']);
    
    // Set default role if not provided
    $role = isset($userData['role']) ? $userData['role'] : 'customer';
    
    // Prepare insert statement
    $stmt = $conn->prepare("INSERT INTO users (username, email, password, full_name, phone, address, role) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", 
        $userData['username'], 
        $userData['email'], 
        $hashedPassword, 
        $userData['full_name'], 
        $userData['phone'], 
        $userData['address'], 
        $role
    );
    
    if ($stmt->execute()) {
        return $stmt->insert_id;
    }
    
    return false;
}

/**
 * Authenticate user login
 * @param string $login Email or username
 * @param string $password Password
 * @return array|bool User data if successful, false otherwise
 */
function authenticateUser($login, $password) {
    global $conn;
    
    // Check if login is email or username
    if (validateEmail($login)) {
        $user = getUserByEmail($login);
    } else {
        $user = getUserByUsername($login);
    }
    
    if ($user && verifyPassword($password, $user['password'])) {
        return $user;
    }
    
    return false;
}

/**
 * Log user into session
 * @param array $user User data
 */
function loginUser($user) {
    // Remove password from session data
    unset($user['password']);
    
    // Store user data in session
    $_SESSION['user'] = $user;
    $_SESSION['login_time'] = time();
    
    // Regenerate session ID for security
    session_regenerate_id(true);
}

/**
 * Log user out
 */
function logoutUser() {
    // Clear session data
    $_SESSION = array();
    
    // Destroy session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    // Destroy session
    session_destroy();
}

/**
 * Format currency for display
 * @param float $amount Amount to format
 * @return string Formatted currency string
 */
function formatCurrency($amount) {
    return 'RM ' . number_format($amount, 2);
}

/**
 * Format date for display
 * @param string $date Date string
 * @return string Formatted date
 */
function formatDate($date) {
    return date('d M Y', strtotime($date));
}

/**
 * Format datetime for display
 * @param string $datetime Datetime string
 * @return string Formatted datetime
 */
function formatDateTime($datetime) {
    return date('d M Y, h:i A', strtotime($datetime));
}

/**
 * Generate random string
 * @param int $length Length of string
 * @return string Random string
 */
function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

/**
 * Get file extension from filename
 * @param string $filename Filename
 * @return string File extension
 */
function getFileExtension($filename) {
    return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
}

/**
 * Check if file is valid image
 * @param string $filename Filename
 * @return bool True if valid image, false otherwise
 */
function isValidImage($filename) {
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
    $extension = getFileExtension($filename);
    return in_array($extension, $allowedExtensions);
}

/**
 * Upload file to server
 * @param array $file File data from $_FILES
 * @param string $directory Upload directory
 * @return string|bool Filename if successful, false otherwise
 */
function uploadFile($file, $directory = 'assets/images/') {
    if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
        return false;
    }
    
    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }
    
    // Generate unique filename
    $extension = getFileExtension($file['name']);
    $filename = generateRandomString(20) . '.' . $extension;
    $filepath = $directory . $filename;
    
    // Create directory if it doesn't exist
    if (!is_dir($directory)) {
        mkdir($directory, 0777, true);
    }
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return $filename;
    }
    
    return false;
}

/**
 * Get total users count
 * @return int Total users
 */
function getTotalUsers() {
    global $conn;
    $result = $conn->query("SELECT COUNT(*) as total FROM users WHERE status = 'active'");
    $row = $result->fetch_assoc();
    return $row['total'];
}

/**
 * Get users by role
 * @param string $role User role
 * @return array Users array
 */
function getUsersByRole($role) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM users WHERE role = ? AND status = 'active' ORDER BY created_at DESC");
    $stmt->bind_param("s", $role);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * Update user profile
 * @param int $userId User ID
 * @param array $userData Updated user data
 * @return bool True if successful, false otherwise
 */
function updateUserProfile($userId, $userData) {
    global $conn;
    
    // Validate email and username uniqueness
    if (emailExists($userData['email'], $userId) || usernameExists($userData['username'], $userId)) {
        return false;
    }
    
    $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, full_name = ?, phone = ?, address = ?, updated_at = CURRENT_TIMESTAMP WHERE user_id = ?");
    $stmt->bind_param("sssssi", 
        $userData['username'], 
        $userData['email'], 
        $userData['full_name'], 
        $userData['phone'], 
        $userData['address'], 
        $userId
    );
    
    return $stmt->execute();
}

/**
 * Change user password
 * @param int $userId User ID
 * @param string $newPassword New password
 * @return bool True if successful, false otherwise
 */
function changeUserPassword($userId, $newPassword) {
    global $conn;
    
    $hashedPassword = hashPassword($newPassword);
    $stmt = $conn->prepare("UPDATE users SET password = ?, updated_at = CURRENT_TIMESTAMP WHERE user_id = ?");
    $stmt->bind_param("si", $hashedPassword, $userId);
    
    return $stmt->execute();
}
?>