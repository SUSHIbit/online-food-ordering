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

/**
 * Enhanced Functions File - Phase 2
 * Online Food Ordering System - Menu Functions
 * 
 * Additional functions for menu and category management
 */

/**
 * Get all active categories ordered by sort_order
 * @return array Categories array
 */
function getCategories() {
    global $conn;
    $result = $conn->query("SELECT * FROM categories WHERE status = 'active' ORDER BY sort_order ASC, category_name ASC");
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * Get category by ID
 * @param int $categoryId Category ID
 * @return array|null Category data or null if not found
 */
function getCategoryById($categoryId) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM categories WHERE category_id = ? AND status = 'active'");
    $stmt->bind_param("i", $categoryId);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

/**
 * Get menu items by category
 * @param int $categoryId Category ID
 * @param bool $availableOnly Show only available items
 * @return array Menu items array
 */
function getMenuItemsByCategory($categoryId, $availableOnly = true) {
    global $conn;
    $sql = "SELECT * FROM menu_items WHERE category_id = ?";
    
    if ($availableOnly) {
        $sql .= " AND availability = 'available'";
    }
    
    $sql .= " ORDER BY sort_order ASC, item_name ASC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $categoryId);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * Get all menu items with category information
 * @param bool $availableOnly Show only available items
 * @return array Menu items with category data
 */
function getAllMenuItems($availableOnly = true) {
    global $conn;
    $sql = "SELECT mi.*, c.category_name 
            FROM menu_items mi 
            JOIN categories c ON mi.category_id = c.category_id 
            WHERE c.status = 'active'";
    
    if ($availableOnly) {
        $sql .= " AND mi.availability = 'available'";
    }
    
    $sql .= " ORDER BY c.sort_order ASC, mi.sort_order ASC, mi.item_name ASC";
    
    $result = $conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * Get menu item by ID
 * @param int $itemId Item ID
 * @return array|null Menu item data or null if not found
 */
function getMenuItemById($itemId) {
    global $conn;
    $stmt = $conn->prepare("SELECT mi.*, c.category_name 
                           FROM menu_items mi 
                           JOIN categories c ON mi.category_id = c.category_id 
                           WHERE mi.item_id = ?");
    $stmt->bind_param("i", $itemId);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

/**
 * Get featured menu items
 * @param int $limit Number of items to return
 * @return array Featured menu items
 */
function getFeaturedMenuItems($limit = 6) {
    global $conn;
    $stmt = $conn->prepare("SELECT mi.*, c.category_name 
                           FROM menu_items mi 
                           JOIN categories c ON mi.category_id = c.category_id 
                           WHERE mi.is_featured = 1 AND mi.availability = 'available' 
                           AND c.status = 'active'
                           ORDER BY mi.sort_order ASC, mi.item_name ASC 
                           LIMIT ?");
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * Search menu items
 * @param string $searchTerm Search term
 * @param int $categoryId Optional category filter
 * @return array Search results
 */
function searchMenuItems($searchTerm, $categoryId = null) {
    global $conn;
    $searchTerm = '%' . $searchTerm . '%';
    
    $sql = "SELECT mi.*, c.category_name 
            FROM menu_items mi 
            JOIN categories c ON mi.category_id = c.category_id 
            WHERE (mi.item_name LIKE ? OR mi.description LIKE ? OR mi.ingredients LIKE ?) 
            AND mi.availability = 'available' AND c.status = 'active'";
    
    $params = [$searchTerm, $searchTerm, $searchTerm];
    
    if ($categoryId) {
        $sql .= " AND mi.category_id = ?";
        $params[] = $categoryId;
    }
    
    $sql .= " ORDER BY mi.item_name ASC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(str_repeat('s', count($params) - ($categoryId ? 1 : 0)) . ($categoryId ? 'i' : ''), ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * Create new category (Admin only)
 * @param array $categoryData Category data
 * @return bool|int Category ID if successful, false otherwise
 */
function createCategory($categoryData) {
    global $conn;
    
    if (empty($categoryData['category_name'])) {
        return false;
    }
    
    $stmt = $conn->prepare("INSERT INTO categories (category_name, description, sort_order) VALUES (?, ?, ?)");
    $stmt->bind_param("ssi", 
        $categoryData['category_name'],
        $categoryData['description'],
        $categoryData['sort_order'] ?? 0
    );
    
    if ($stmt->execute()) {
        return $stmt->insert_id;
    }
    
    return false;
}

/**
 * Update category (Admin only)
 * @param int $categoryId Category ID
 * @param array $categoryData Updated category data
 * @return bool True if successful, false otherwise
 */
function updateCategory($categoryId, $categoryData) {
    global $conn;
    
    $stmt = $conn->prepare("UPDATE categories SET category_name = ?, description = ?, sort_order = ?, updated_at = CURRENT_TIMESTAMP WHERE category_id = ?");
    $stmt->bind_param("ssii", 
        $categoryData['category_name'],
        $categoryData['description'],
        $categoryData['sort_order'] ?? 0,
        $categoryId
    );
    
    return $stmt->execute();
}

/**
 * Delete category (Admin only)
 * @param int $categoryId Category ID
 * @return bool True if successful, false otherwise
 */
function deleteCategory($categoryId) {
    global $conn;
    
    // Check if category has menu items
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM menu_items WHERE category_id = ?");
    $stmt->bind_param("i", $categoryId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    if ($row['count'] > 0) {
        return false; // Cannot delete category with items
    }
    
    $stmt = $conn->prepare("DELETE FROM categories WHERE category_id = ?");
    $stmt->bind_param("i", $categoryId);
    return $stmt->execute();
}

/**
 * Create new menu item (Admin only)
 * @param array $itemData Menu item data
 * @return bool|int Item ID if successful, false otherwise
 */
function createMenuItem($itemData) {
    global $conn;
    
    if (empty($itemData['item_name']) || empty($itemData['price']) || empty($itemData['category_id'])) {
        return false;
    }
    
    $stmt = $conn->prepare("INSERT INTO menu_items (category_id, item_name, description, price, image_url, preparation_time, ingredients, allergens, calories, is_featured, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssdissiib", 
        $itemData['category_id'],
        $itemData['item_name'],
        $itemData['description'],
        $itemData['price'],
        $itemData['image_url'] ?? null,
        $itemData['preparation_time'] ?? 15,
        $itemData['ingredients'] ?? '',
        $itemData['allergens'] ?? '',
        $itemData['calories'] ?? null,
        $itemData['is_featured'] ?? false,
        $itemData['sort_order'] ?? 0
    );
    
    if ($stmt->execute()) {
        return $stmt->insert_id;
    }
    
    return false;
}

/**
 * Update menu item (Admin only)
 * @param int $itemId Item ID
 * @param array $itemData Updated item data
 * @return bool True if successful, false otherwise
 */
function updateMenuItem($itemId, $itemData) {
    global $conn;
    
    $stmt = $conn->prepare("UPDATE menu_items SET category_id = ?, item_name = ?, description = ?, price = ?, image_url = ?, preparation_time = ?, ingredients = ?, allergens = ?, calories = ?, is_featured = ?, sort_order = ?, updated_at = CURRENT_TIMESTAMP WHERE item_id = ?");
    $stmt->bind_param("isssdissiiii", 
        $itemData['category_id'],
        $itemData['item_name'],
        $itemData['description'],
        $itemData['price'],
        $itemData['image_url'],
        $itemData['preparation_time'],
        $itemData['ingredients'],
        $itemData['allergens'],
        $itemData['calories'],
        $itemData['is_featured'] ?? false,
        $itemData['sort_order'] ?? 0,
        $itemId
    );
    
    return $stmt->execute();
}

/**
 * Toggle menu item availability (Admin only)
 * @param int $itemId Item ID
 * @return bool True if successful, false otherwise
 */
function toggleMenuItemAvailability($itemId) {
    global $conn;
    
    $stmt = $conn->prepare("UPDATE menu_items SET availability = CASE WHEN availability = 'available' THEN 'unavailable' ELSE 'available' END, updated_at = CURRENT_TIMESTAMP WHERE item_id = ?");
    $stmt->bind_param("i", $itemId);
    
    return $stmt->execute();
}

/**
 * Delete menu item (Admin only)
 * @param int $itemId Item ID
 * @return bool True if successful, false otherwise
 */
function deleteMenuItem($itemId) {
    global $conn;
    
    $stmt = $conn->prepare("DELETE FROM menu_items WHERE item_id = ?");
    $stmt->bind_param("i", $itemId);
    return $stmt->execute();
}

/**
 * Get menu statistics
 * @return array Statistics data
 */
function getMenuStatistics() {
    global $conn;
    
    $stats = [];
    
    // Total categories
    $result = $conn->query("SELECT COUNT(*) as count FROM categories WHERE status = 'active'");
    $stats['total_categories'] = $result->fetch_assoc()['count'];
    
    // Total menu items
    $result = $conn->query("SELECT COUNT(*) as count FROM menu_items WHERE availability = 'available'");
    $stats['total_items'] = $result->fetch_assoc()['count'];
    
    // Featured items
    $result = $conn->query("SELECT COUNT(*) as count FROM menu_items WHERE is_featured = 1 AND availability = 'available'");
    $stats['featured_items'] = $result->fetch_assoc()['count'];
    
    // Average price
    $result = $conn->query("SELECT AVG(price) as avg_price FROM menu_items WHERE availability = 'available'");
    $stats['average_price'] = round($result->fetch_assoc()['avg_price'], 2);
    
    return $stats;
}

/**
 * Get price range for menu items
 * @return array Min and max prices
 */
function getMenuPriceRange() {
    global $conn;
    
    $result = $conn->query("SELECT MIN(price) as min_price, MAX(price) as max_price FROM menu_items WHERE availability = 'available'");
    return $result->fetch_assoc();
}

/**
 * Get menu items by price range
 * @param float $minPrice Minimum price
 * @param float $maxPrice Maximum price
 * @return array Menu items in price range
 */
function getMenuItemsByPriceRange($minPrice, $maxPrice) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT mi.*, c.category_name 
                           FROM menu_items mi 
                           JOIN categories c ON mi.category_id = c.category_id 
                           WHERE mi.price BETWEEN ? AND ? 
                           AND mi.availability = 'available' 
                           AND c.status = 'active'
                           ORDER BY mi.price ASC");
    $stmt->bind_param("dd", $minPrice, $maxPrice);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * Check if category name exists
 * @param string $categoryName Category name
 * @param int $excludeCategoryId Category ID to exclude (for updates)
 * @return bool True if exists, false otherwise
 */
function categoryNameExists($categoryName, $excludeCategoryId = null) {
    global $conn;
    
    $sql = "SELECT category_id FROM categories WHERE category_name = ?";
    $params = [$categoryName];
    
    if ($excludeCategoryId) {
        $sql .= " AND category_id != ?";
        $params[] = $excludeCategoryId;
    }
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(str_repeat('s', count($params) - ($excludeCategoryId ? 1 : 0)) . ($excludeCategoryId ? 'i' : ''), ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0;
}

/**
 * Upload menu item image
 * @param array $file File data from $_FILES
 * @return string|bool Filename if successful, false otherwise
 */
function uploadMenuImage($file) {
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    $maxSize = 5 * 1024 * 1024; // 5MB
    
    if (!in_array($file['type'], $allowedTypes)) {
        return false;
    }
    
    if ($file['size'] > $maxSize) {
        return false;
    }
    
    return uploadFile($file, 'assets/images/menu/');
}
?>