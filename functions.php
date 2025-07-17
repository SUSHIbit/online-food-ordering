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
 * FINAL FIX: Create new menu item (Admin only)
 * Replace the createMenuItem function in your functions.php with this version
 */
function createMenuItem($itemData) {
    global $conn;
    
    if (empty($itemData['item_name']) || empty($itemData['price']) || empty($itemData['category_id'])) {
        return false;
    }
    
    // Prepare all variables individually to avoid reference issues
    $category_id = (int)$itemData['category_id'];
    $item_name = $itemData['item_name'];
    $description = isset($itemData['description']) ? $itemData['description'] : '';
    $price = (float)$itemData['price'];
    $image_url = isset($itemData['image_url']) ? $itemData['image_url'] : null;
    $preparation_time = isset($itemData['preparation_time']) ? (int)$itemData['preparation_time'] : 15;
    $ingredients = isset($itemData['ingredients']) ? $itemData['ingredients'] : '';
    $allergens = isset($itemData['allergens']) ? $itemData['allergens'] : '';
    $calories = isset($itemData['calories']) ? (int)$itemData['calories'] : null;
    $is_featured = isset($itemData['is_featured']) ? 1 : 0;
    $sort_order = isset($itemData['sort_order']) ? (int)$itemData['sort_order'] : 0;
    
    $stmt = $conn->prepare("INSERT INTO menu_items (category_id, item_name, description, price, image_url, preparation_time, ingredients, allergens, calories, is_featured, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    // Use the individual variables instead of array access
    $stmt->bind_param("issdsissiii", 
        $category_id,
        $item_name,
        $description,
        $price,
        $image_url,
        $preparation_time,
        $ingredients,
        $allergens,
        $calories,
        $is_featured,
        $sort_order
    );
    
    if ($stmt->execute()) {
        return $stmt->insert_id;
    }
    
    return false;
}

/**
 * FINAL FIX: Update menu item (Admin only)
 * Replace the updateMenuItem function in your functions.php with this version
 */
function updateMenuItem($itemId, $itemData) {
    global $conn;
    
    // Prepare all variables individually to avoid reference issues
    $category_id = (int)$itemData['category_id'];
    $item_name = $itemData['item_name'];
    $description = isset($itemData['description']) ? $itemData['description'] : '';
    $price = (float)$itemData['price'];
    $image_url = isset($itemData['image_url']) ? $itemData['image_url'] : null;
    $preparation_time = isset($itemData['preparation_time']) ? (int)$itemData['preparation_time'] : 15;
    $ingredients = isset($itemData['ingredients']) ? $itemData['ingredients'] : '';
    $allergens = isset($itemData['allergens']) ? $itemData['allergens'] : '';
    $calories = isset($itemData['calories']) ? (int)$itemData['calories'] : null;
    $is_featured = isset($itemData['is_featured']) ? 1 : 0;
    $sort_order = isset($itemData['sort_order']) ? (int)$itemData['sort_order'] : 0;
    $item_id = (int)$itemId;
    
    $stmt = $conn->prepare("UPDATE menu_items SET category_id = ?, item_name = ?, description = ?, price = ?, image_url = ?, preparation_time = ?, ingredients = ?, allergens = ?, calories = ?, is_featured = ?, sort_order = ?, updated_at = CURRENT_TIMESTAMP WHERE item_id = ?");
    
    // Use the individual variables instead of array access
    $stmt->bind_param("issdsissiiii", 
        $category_id,
        $item_name,
        $description,
        $price,
        $image_url,
        $preparation_time,
        $ingredients,
        $allergens,
        $calories,
        $is_featured,
        $sort_order,
        $item_id
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
 * FIXED: Upload menu item image with proper error handling
 * Replace the existing uploadMenuImage function in functions.php
 */
function uploadMenuImage($file) {
    // Check if file was uploaded
    if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
        error_log("Upload error: No file uploaded");
        return false;
    }
    
    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        error_log("Upload error: " . $file['error']);
        return false;
    }
    
    // Validate file type
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
    $fileType = $file['type'];
    
    // Also check by extension as backup
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($fileType, $allowedTypes) && !in_array($extension, $allowedExtensions)) {
        error_log("Upload error: Invalid file type - " . $fileType);
        return false;
    }
    
    // Validate file size (5MB max)
    $maxSize = 5 * 1024 * 1024; // 5MB
    if ($file['size'] > $maxSize) {
        error_log("Upload error: File too large - " . $file['size']);
        return false;
    }
    
    // Create upload directory if it doesn't exist
    $uploadDir = 'assets/images/menu/';
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
            error_log("Upload error: Cannot create directory - " . $uploadDir);
            return false;
        }
    }
    
    // Generate unique filename
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $filename = 'menu_' . time() . '_' . uniqid() . '.' . $extension;
    $filepath = $uploadDir . $filename;
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        error_log("Upload success: " . $filepath);
        return $filename;
    } else {
        error_log("Upload error: Cannot move file to - " . $filepath);
        return false;
    }
}

/**
 * FIXED: Get image URL with proper path checking
 * Add this helper function to functions.php
 */
function getMenuImageUrl($imageName) {
    if (empty($imageName)) {
        return null;
    }
    
    $imagePath = 'assets/images/menu/' . $imageName;
    
    // Check if file exists
    if (file_exists($imagePath)) {
        return SITE_URL . $imagePath;
    }
    
    // If file doesn't exist, return null to show placeholder
    return null;
}

/**
 * FIXED: Debug function to check image status
 * Add this temporary function to help debug
 */
function debugImageStatus($imageName) {
    if (empty($imageName)) {
        return "No image name provided";
    }
    
    $imagePath = 'assets/images/menu/' . $imageName;
    $fullPath = realpath($imagePath);
    
    $debug = [
        'image_name' => $imageName,
        'image_path' => $imagePath,
        'full_path' => $fullPath,
        'file_exists' => file_exists($imagePath),
        'is_readable' => is_readable($imagePath),
        'directory_exists' => is_dir('assets/images/menu/'),
        'directory_writable' => is_writable('assets/images/menu/'),
        'site_url' => SITE_URL
    ];
    
    return $debug;
}

/**
 * Cart Functions - Add to functions.php
 * Online Food Ordering System - Phase 3
 * 
 * Shopping cart management functions
 */

/**
 * Add item to cart
 * @param int $itemId Item ID
 * @param int $quantity Quantity to add
 * @return bool True if successful
 */
function addToCart($itemId, $quantity = 1) {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    // Check if item exists and is available
    $item = getMenuItemById($itemId);
    if (!$item || $item['availability'] !== 'available') {
        return false;
    }
    
    // Add or update quantity
    if (isset($_SESSION['cart'][$itemId])) {
        $_SESSION['cart'][$itemId]['quantity'] += $quantity;
    } else {
        $_SESSION['cart'][$itemId] = [
            'item_id' => $itemId,
            'quantity' => $quantity,
            'added_at' => time()
        ];
    }
    
    // Limit quantity to 10 per item
    if ($_SESSION['cart'][$itemId]['quantity'] > 10) {
        $_SESSION['cart'][$itemId]['quantity'] = 10;
    }
    
    return true;
}

/**
 * Update cart item quantity
 * @param int $itemId Item ID
 * @param int $quantity New quantity
 * @return bool True if successful
 */
function updateCartQuantity($itemId, $quantity) {
    if (!isset($_SESSION['cart'][$itemId])) {
        return false;
    }
    
    if ($quantity <= 0) {
        unset($_SESSION['cart'][$itemId]);
    } else {
        $_SESSION['cart'][$itemId]['quantity'] = min($quantity, 10);
    }
    
    return true;
}

/**
 * Remove item from cart
 * @param int $itemId Item ID
 * @return bool True if successful
 */
function removeFromCart($itemId) {
    if (isset($_SESSION['cart'][$itemId])) {
        unset($_SESSION['cart'][$itemId]);
        return true;
    }
    return false;
}

/**
 * Clear entire cart
 */
function clearCart() {
    $_SESSION['cart'] = [];
}

/**
 * Get cart items with full details
 * @return array Cart items with menu details
 */
function getCartItems() {
    if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
        return [];
    }
    
    $cartItems = [];
    
    foreach ($_SESSION['cart'] as $itemId => $cartItem) {
        $menuItem = getMenuItemById($itemId);
        if ($menuItem) {
            $cartItems[] = array_merge($menuItem, [
                'quantity' => $cartItem['quantity'],
                'added_at' => $cartItem['added_at']
            ]);
        }
    }
    
    return $cartItems;
}

/**
 * Get cart total amount
 * @return float Total amount
 */
function getCartTotal() {
    $total = 0;
    $cartItems = getCartItems();
    
    foreach ($cartItems as $item) {
        $total += $item['price'] * $item['quantity'];
    }
    
    return $total;
}

/**
 * Get cart item count
 * @return int Total number of items
 */
function getCartCount() {
    if (!isset($_SESSION['cart'])) {
        return 0;
    }
    
    $count = 0;
    foreach ($_SESSION['cart'] as $item) {
        $count += $item['quantity'];
    }
    
    return $count;
}

/**
 * Check if item is in cart
 * @param int $itemId Item ID
 * @return bool True if in cart
 */
function isInCart($itemId) {
    return isset($_SESSION['cart'][$itemId]);
}

/**
 * Get item quantity in cart
 * @param int $itemId Item ID
 * @return int Quantity in cart
 */
function getCartItemQuantity($itemId) {
    return isset($_SESSION['cart'][$itemId]) ? $_SESSION['cart'][$itemId]['quantity'] : 0;
}

/**
 * Create order from cart
 * @param array $orderData Order information
 * @return bool|int Order ID if successful, false otherwise
 */
function createOrderFromCart($orderData) {
    global $conn;
    
    $cartItems = getCartItems();
    if (empty($cartItems)) {
        return false;
    }
    
    $subtotal = getCartTotal();
    $deliveryFee = 5.00;
    $tax = $subtotal * 0.06;
    $totalAmount = $subtotal + $deliveryFee + $tax;
    
    // Start transaction
    $conn->autocommit(false);
    
    try {
        // Insert order
        $stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount, delivery_address, phone, notes, order_status, payment_status) VALUES (?, ?, ?, ?, ?, 'pending', 'pending')");
        $stmt->bind_param("idsss", 
            $orderData['user_id'],
            $totalAmount,
            $orderData['delivery_address'],
            $orderData['phone'],
            $orderData['notes']
        );
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to create order");
        }
        
        $orderId = $stmt->insert_id;
        
        // Insert order items
        $stmt = $conn->prepare("INSERT INTO order_items (order_id, item_id, quantity, item_price) VALUES (?, ?, ?, ?)");
        
        foreach ($cartItems as $item) {
            $stmt->bind_param("iiid", 
                $orderId,
                $item['item_id'],
                $item['quantity'],
                $item['price']
            );
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to add order item");
            }
        }
        
        // Commit transaction
        $conn->commit();
        
        // Clear cart
        clearCart();
        
        return $orderId;
        
    } catch (Exception $e) {
        $conn->rollback();
        return false;
    } finally {
        $conn->autocommit(true);
    }
}

/**
 * Get order by ID
 * @param int $orderId Order ID
 * @return array|null Order data or null if not found
 */
function getOrderById($orderId) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM orders WHERE order_id = ?");
    $stmt->bind_param("i", $orderId);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

/**
 * Get order items
 * @param int $orderId Order ID
 * @return array Order items with menu details
 */
function getOrderItems($orderId) {
    global $conn;
    $stmt = $conn->prepare("SELECT oi.*, mi.item_name, mi.image_url, c.category_name 
                           FROM order_items oi 
                           JOIN menu_items mi ON oi.item_id = mi.item_id 
                           JOIN categories c ON mi.category_id = c.category_id 
                           WHERE oi.order_id = ?");
    $stmt->bind_param("i", $orderId);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * Get user orders
 * @param int $userId User ID
 * @param int $limit Number of orders to return
 * @return array User orders
 */
function getUserOrders($userId, $limit = 50) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC LIMIT ?");
    $stmt->bind_param("ii", $userId, $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * Update order status
 * @param int $orderId Order ID
 * @param string $status New status
 * @return bool True if successful
 */
function updateOrderStatus($orderId, $status) {
    global $conn;
    $validStatuses = ['pending', 'confirmed', 'preparing', 'ready', 'delivered', 'cancelled'];
    
    if (!in_array($status, $validStatuses)) {
        return false;
    }
    
    $stmt = $conn->prepare("UPDATE orders SET order_status = ?, updated_at = CURRENT_TIMESTAMP WHERE order_id = ?");
    $stmt->bind_param("si", $status, $orderId);
    
    return $stmt->execute();
}

/**
 * Get all orders (Admin only)
 * @param string $status Optional status filter
 * @param int $limit Number of orders to return
 * @return array All orders
 */
function getAllOrders($status = null, $limit = 100) {
    global $conn;
    
    $sql = "SELECT o.*, u.full_name, u.email 
            FROM orders o 
            JOIN users u ON o.user_id = u.user_id";
    
    if ($status) {
        $sql .= " WHERE o.order_status = ?";
    }
    
    $sql .= " ORDER BY o.created_at DESC LIMIT ?";
    
    $stmt = $conn->prepare($sql);
    
    if ($status) {
        $stmt->bind_param("si", $status, $limit);
    } else {
        $stmt->bind_param("i", $limit);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * Additional Functions for Phase 4
 * Add these functions to your existing functions.php file
 */

/**
 * Get admin orders with filters
 */
function getAdminOrders($statusFilter = null, $dateFilter = null, $limit = 50) {
    global $conn;
    
    $sql = "SELECT o.*, u.full_name, u.email 
            FROM orders o 
            JOIN users u ON o.user_id = u.user_id 
            WHERE 1=1";
    $params = [];
    $types = "";
    
    if ($statusFilter) {
        $sql .= " AND o.order_status = ?";
        $params[] = $statusFilter;
        $types .= "s";
    }
    
    if ($dateFilter) {
        $sql .= " AND DATE(o.created_at) = ?";
        $params[] = $dateFilter;
        $types .= "s";
    }
    
    $sql .= " ORDER BY o.created_at DESC LIMIT ?";
    $params[] = $limit;
    $types .= "i";
    
    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

/**
 * Get order statistics
 */
function getOrderStatistics() {
    global $conn;
    
    $stats = [];
    
    // Total orders
    $result = $conn->query("SELECT COUNT(*) as count FROM orders");
    $stats['total_orders'] = $result->fetch_assoc()['count'];
    
    // Pending orders
    $result = $conn->query("SELECT COUNT(*) as count FROM orders WHERE order_status = 'pending'");
    $stats['pending_orders'] = $result->fetch_assoc()['count'];
    
    // Preparing orders
    $result = $conn->query("SELECT COUNT(*) as count FROM orders WHERE order_status = 'preparing'");
    $stats['preparing_orders'] = $result->fetch_assoc()['count'];
    
    // Total revenue
    $result = $conn->query("SELECT SUM(total_amount) as total FROM orders WHERE order_status = 'delivered'");
    $stats['total_revenue'] = $result->fetch_assoc()['total'] ?? 0;
    
    return $stats;
}

/**
 * Get dashboard statistics
 */
function getDashboardStats() {
    global $conn;
    
    $stats = [];
    
    // Orders today
    $result = $conn->query("SELECT COUNT(*) as count FROM orders WHERE DATE(created_at) = CURDATE()");
    $stats['total_orders_today'] = $result->fetch_assoc()['count'];
    
    // Revenue today
    $result = $conn->query("SELECT SUM(total_amount) as total FROM orders WHERE DATE(created_at) = CURDATE() AND order_status = 'delivered'");
    $stats['revenue_today'] = $result->fetch_assoc()['total'] ?? 0;
    
    // Active customers (this month)
    $result = $conn->query("SELECT COUNT(DISTINCT user_id) as count FROM orders WHERE MONTH(created_at) = MONTH(CURDATE())");
    $stats['active_customers'] = $result->fetch_assoc()['count'];
    
    // Average order value
    $result = $conn->query("SELECT AVG(total_amount) as avg_val FROM orders WHERE order_status = 'delivered'");
    $stats['avg_order_value'] = formatCurrency($result->fetch_assoc()['avg_val'] ?? 0);
    
    // Pending orders
    $result = $conn->query("SELECT COUNT(*) as count FROM orders WHERE order_status = 'pending'");
    $stats['pending_orders'] = $result->fetch_assoc()['count'];
    
    // Total menu items
    $result = $conn->query("SELECT COUNT(*) as count FROM menu_items WHERE availability = 'available'");
    $stats['total_menu_items'] = $result->fetch_assoc()['count'];
    
    // Total customers
    $result = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'customer' AND status = 'active'");
    $stats['total_customers'] = $result->fetch_assoc()['count'];
    
    // Dummy percentage changes (in real app, calculate from historical data)
    $stats['orders_change'] = rand(5, 25);
    $stats['revenue_change'] = rand(8, 30);
    
    return $stats;
}

/**
 * Get recent orders
 */
function getRecentOrders($limit = 10) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT o.*, u.full_name 
                           FROM orders o 
                           JOIN users u ON o.user_id = u.user_id 
                           ORDER BY o.created_at DESC 
                           LIMIT ?");
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

/**
 * Add order status history
 */
function addOrderStatusHistory($orderId, $status, $notes = '', $changedBy = null) {
    global $conn;
    
    $stmt = $conn->prepare("INSERT INTO order_status_history (order_id, status, notes, changed_by) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("issi", $orderId, $status, $notes, $changedBy);
    return $stmt->execute();
}

/**
 * Get order status history
 */
function getOrderStatusHistory($orderId) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT h.*, u.full_name as changed_by_name 
                           FROM order_status_history h 
                           LEFT JOIN users u ON h.changed_by = u.user_id 
                           WHERE h.order_id = ? 
                           ORDER BY h.created_at DESC");
    $stmt->bind_param("i", $orderId);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

/**
 * Get low stock items (placeholder for future inventory feature)
 */
function getLowStockItems() {
    // Placeholder - in real app would check inventory levels
    return [];
}

/**
 * Get system status
 */
function getSystemStatus() {
    global $conn;
    
    $status = [
        'database' => $conn ? 'healthy' : 'error',
        'orders' => 'running',
        'menu' => 'synced'
    ];
    
    return $status;
}

/**
 * Cancel order (customer or admin)
 */
function cancelOrder($orderId, $userId = null) {
    global $conn;
    
    // Verify order exists and can be cancelled
    $order = getOrderById($orderId);
    if (!$order || !in_array($order['order_status'], ['pending', 'confirmed'])) {
        return false;
    }
    
    // Check permissions
    if ($userId && $order['user_id'] != $userId) {
        return false;
    }
    
    // Update order status
    $stmt = $conn->prepare("UPDATE orders SET order_status = 'cancelled', updated_at = CURRENT_TIMESTAMP WHERE order_id = ?");
    $stmt->bind_param("i", $orderId);
    
    if ($stmt->execute()) {
        // Add to history
        addOrderStatusHistory($orderId, 'cancelled', 'Order cancelled', $userId);
        return true;
    }
    
    return false;
}

?>