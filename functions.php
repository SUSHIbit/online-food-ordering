<?php
/**
 * Complete Functions File - Online Food Ordering System
 * FIXED VERSION - With Working Image Upload
 */

/**
 * Hash password securely
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Verify password against hash
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Validate email format
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate phone number
 */
function validatePhone($phone) {
    return preg_match('/^[0-9+\-\s()]{10,20}$/', $phone);
}

/**
 * Format currency for display
 */
function formatCurrency($amount) {
    return 'RM ' . number_format($amount, 2);
}

/**
 * Format date for display
 */
function formatDate($date) {
    return date('d M Y', strtotime($date));
}

/**
 * Format datetime for display
 */
function formatDateTime($datetime) {
    return date('d M Y, h:i A', strtotime($datetime));
}

/**
 * Format bytes to human readable format
 */
function formatBytes($size, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB');
    for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
        $size /= 1024;
    }
    return round($size, $precision) . ' ' . $units[$i];
}

/**
 * Generate random string
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

// ============================================================================
// USER MANAGEMENT FUNCTIONS
// ============================================================================

/**
 * Get user by ID
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
 */
function emailExists($email, $excludeUserId = null) {
    global $conn;
    
    if ($excludeUserId) {
        $sql = "SELECT user_id FROM users WHERE email = ? AND user_id != ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $email, (int)$excludeUserId);
    } else {
        $sql = "SELECT user_id FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0;
}

/**
 * Check if username exists
 */
function usernameExists($username, $excludeUserId = null) {
    global $conn;
    
    if ($excludeUserId) {
        $sql = "SELECT user_id FROM users WHERE username = ? AND user_id != ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $username, (int)$excludeUserId);
    } else {
        $sql = "SELECT user_id FROM users WHERE username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0;
}

/**
 * Create new user account
 */
function createUser($userData) {
    global $conn;
    
    if (empty($userData['username']) || empty($userData['email']) || empty($userData['password'])) {
        return false;
    }
    
    if (emailExists($userData['email']) || usernameExists($userData['username'])) {
        return false;
    }
    
    $hashedPassword = hashPassword($userData['password']);
    $role = isset($userData['role']) ? $userData['role'] : 'customer';
    
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
 */
function authenticateUser($login, $password) {
    global $conn;
    
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
 */
function loginUser($user) {
    unset($user['password']);
    $_SESSION['user'] = $user;
    $_SESSION['login_time'] = time();
    session_regenerate_id(true);
}

/**
 * Log user out
 */
function logoutUser() {
    $_SESSION = array();
    
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    session_destroy();
}

/**
 * Update user profile
 */
function updateUserProfile($userId, $userData) {
    global $conn;
    
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
 */
function changeUserPassword($userId, $newPassword) {
    global $conn;
    
    $hashedPassword = hashPassword($newPassword);
    $stmt = $conn->prepare("UPDATE users SET password = ?, updated_at = CURRENT_TIMESTAMP WHERE user_id = ?");
    $stmt->bind_param("si", $hashedPassword, $userId);
    
    return $stmt->execute();
}

// ============================================================================
// CATEGORY MANAGEMENT FUNCTIONS
// ============================================================================

/**
 * Get all active categories
 */
function getCategories() {
    global $conn;
    $result = $conn->query("SELECT * FROM categories WHERE status = 'active' ORDER BY sort_order ASC, category_name ASC");
    if (!$result) {
        return [];
    }
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * Get category by ID
 */
function getCategoryById($categoryId) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM categories WHERE category_id = ? AND status = 'active'");
    if (!$stmt) {
        return null;
    }
    $stmt->bind_param("i", $categoryId);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

/**
 * Create new category
 */
function createCategory($categoryData) {
    global $conn;
    
    if (empty($categoryData['category_name'])) {
        return false;
    }
    
    $categoryName = $categoryData['category_name'];
    $description = isset($categoryData['description']) ? $categoryData['description'] : '';
    $sortOrder = isset($categoryData['sort_order']) ? (int)$categoryData['sort_order'] : 0;
    
    $stmt = $conn->prepare("INSERT INTO categories (category_name, description, sort_order) VALUES (?, ?, ?)");
    
    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error);
        return false;
    }
    
    $stmt->bind_param("ssi", $categoryName, $description, $sortOrder);
    
    if ($stmt->execute()) {
        $insertId = $stmt->insert_id;
        $stmt->close();
        return $insertId;
    } else {
        error_log("Execute failed: " . $stmt->error);
        $stmt->close();
        return false;
    }
}

/**
 * Update category
 */
function updateCategory($categoryId, $categoryData) {
    global $conn;
    
    $categoryName = $categoryData['category_name'];
    $description = isset($categoryData['description']) ? $categoryData['description'] : '';
    $sortOrder = isset($categoryData['sort_order']) ? (int)$categoryData['sort_order'] : 0;
    $categoryId = (int)$categoryId;
    
    $stmt = $conn->prepare("UPDATE categories SET category_name = ?, description = ?, sort_order = ?, updated_at = CURRENT_TIMESTAMP WHERE category_id = ?");
    
    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error);
        return false;
    }
    
    $stmt->bind_param("ssii", $categoryName, $description, $sortOrder, $categoryId);
    
    if ($stmt->execute()) {
        $result = $stmt->affected_rows > 0;
        $stmt->close();
        return $result;
    } else {
        error_log("Execute failed: " . $stmt->error);
        $stmt->close();
        return false;
    }
}

/**
 * Delete category
 */
function deleteCategory($categoryId) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM menu_items WHERE category_id = ?");
    $stmt->bind_param("i", $categoryId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    if ($row['count'] > 0) {
        return false;
    }
    
    $stmt = $conn->prepare("DELETE FROM categories WHERE category_id = ?");
    $stmt->bind_param("i", $categoryId);
    return $stmt->execute();
}

/**
 * Check if category name exists
 */
function categoryNameExists($categoryName, $excludeCategoryId = null) {
    global $conn;
    
    $categoryName = mysqli_real_escape_string($conn, $categoryName);
    $sql = "SELECT category_id FROM categories WHERE category_name = '$categoryName'";
    
    if ($excludeCategoryId) {
        $excludeCategoryId = (int)$excludeCategoryId;
        $sql .= " AND category_id != $excludeCategoryId";
    }
    
    $result = $conn->query($sql);
    return $result && $result->num_rows > 0;
}

// ============================================================================
// IMAGE COMPRESSION FUNCTIONS
// ============================================================================

/**
 * Auto-compress image during upload
 */
function autoCompressImage($sourceImage, $destination, $quality = 80, $maxWidth = 800) {
    if (!$sourceImage) {
        return false;
    }
    
    try {
        $originalWidth = imagesx($sourceImage);
        $originalHeight = imagesy($sourceImage);
        
        if (!$originalWidth || !$originalHeight) {
            return false;
        }
        
        if ($originalWidth > $maxWidth) {
            $newWidth = $maxWidth;
            $newHeight = intval(($originalHeight * $maxWidth) / $originalWidth);
        } else {
            $newWidth = $originalWidth;
            $newHeight = $originalHeight;
        }
        
        $newImage = imagecreatetruecolor($newWidth, $newHeight);
        if (!$newImage) {
            return false;
        }
        
        imagealphablending($newImage, false);
        imagesavealpha($newImage, true);
        $transparent = imagecolorallocatealpha($newImage, 255, 255, 255, 127);
        imagefilledrectangle($newImage, 0, 0, $newWidth, $newHeight, $transparent);
        
        $resizeResult = imagecopyresampled(
            $newImage, $sourceImage,
            0, 0, 0, 0,
            $newWidth, $newHeight,
            $originalWidth, $originalHeight
        );
        
        if (!$resizeResult) {
            imagedestroy($newImage);
            return false;
        }
        
        $result = imagejpeg($newImage, $destination, $quality);
        imagedestroy($newImage);
        
        return $result;
        
    } catch (Exception $e) {
        error_log("Image compression error: " . $e->getMessage());
        return false;
    }
}

/**
 * FIXED: Upload menu item image
 */
function uploadMenuImage($file) {
    // Debug: Log function call
    error_log("uploadMenuImage called with file: " . print_r($file, true));
    
    // Validate file input
    if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
        error_log("Upload error: No file uploaded or tmp_name empty");
        return false;
    }
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        error_log("Upload error code: " . $file['error']);
        return false;
    }
    
    // Check if file actually exists
    if (!is_uploaded_file($file['tmp_name'])) {
        error_log("Upload error: File is not a valid uploaded file");
        return false;
    }
    
    // Validate file type
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
    $fileType = $file['type'];
    
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($fileType, $allowedTypes) && !in_array($extension, $allowedExtensions)) {
        error_log("Upload error: Invalid file type - " . $fileType . " / " . $extension);
        return false;
    }
    
    // Validate file size (10MB max)
    $maxSize = 10 * 1024 * 1024;
    if ($file['size'] > $maxSize) {
        error_log("Upload error: File too large - " . formatBytes($file['size']));
        return false;
    }
    
    // Create upload directory if it doesn't exist
    $uploadDir = 'assets/images/menu/';
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
            error_log("Upload error: Cannot create directory - " . $uploadDir);
            return false;
        }
        error_log("Created directory: " . $uploadDir);
    }
    
    // Check directory permissions
    if (!is_writable($uploadDir)) {
        error_log("Upload error: Directory not writable - " . $uploadDir);
        return false;
    }
    
    // Generate unique filename
    $filename = 'menu_' . time() . '_' . uniqid() . '.jpg';
    $filepath = $uploadDir . $filename;
    
    error_log("Attempting to upload to: " . $filepath);
    
    // Try direct upload first (no compression)
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        // Verify file was created
        if (file_exists($filepath)) {
            $fileSize = filesize($filepath);
            error_log("File uploaded successfully: " . $filename . " (Size: " . formatBytes($fileSize) . ")");
            
            // Try compression if GD is available
            if (extension_loaded('gd') && function_exists('imagecreatefromjpeg')) {
                $tempFilename = 'temp_' . $filename;
                $tempFilepath = $uploadDir . $tempFilename;
                
                $sourceImage = null;
                switch (strtolower($extension)) {
                    case 'jpg':
                    case 'jpeg':
                        $sourceImage = @imagecreatefromjpeg($filepath);
                        break;
                    case 'png':
                        $sourceImage = @imagecreatefrompng($filepath);
                        break;
                    case 'gif':
                        $sourceImage = @imagecreatefromgif($filepath);
                        break;
                }
                
                if ($sourceImage && autoCompressImage($sourceImage, $tempFilepath, 80, 800)) {
                    imagedestroy($sourceImage);
                    
                    // Replace original with compressed version
                    if (file_exists($tempFilepath)) {
                        unlink($filepath);
                        rename($tempFilepath, $filepath);
                        
                        $compressedSize = filesize($filepath);
                        $savings = round((($fileSize - $compressedSize) / $fileSize) * 100, 1);
                        error_log("Image compressed: " . $filename . " (saved {$savings}%)");
                    }
                } elseif ($sourceImage) {
                    imagedestroy($sourceImage);
                }
            }
            
            return $filename;
        } else {
            error_log("Upload error: File moved but not found at destination");
            return false;
        }
    } else {
        error_log("Upload error: move_uploaded_file failed from " . $file['tmp_name'] . " to " . $filepath);
        return false;
    }
}

/**
 * Get image compression statistics
 */
function getImageCompressionStats() {
    $menuDir = 'assets/images/menu/';
    $files = glob($menuDir . '*.{jpg,jpeg,png,gif}', GLOB_BRACE);
    
    $totalSize = 0;
    $count = 0;
    
    foreach ($files as $file) {
        $totalSize += filesize($file);
        $count++;
    }
    
    return [
        'total_files' => $count,
        'total_size' => $totalSize,
        'average_size' => $count > 0 ? $totalSize / $count : 0,
        'formatted_total' => formatBytes($totalSize),
        'formatted_average' => formatBytes($count > 0 ? $totalSize / $count : 0)
    ];
}

/**
 * Compress existing images
 */
function compressExistingImages() {
    global $conn;
    $menuDir = 'assets/images/menu/';
    $backupDir = 'assets/images/menu/backup/';
    
    if (!is_dir($menuDir)) {
        return ['success' => false, 'message' => 'Menu directory not found'];
    }
    
    if (!is_dir($backupDir)) {
        mkdir($backupDir, 0755, true);
    }
    
    $files = glob($menuDir . '*.{jpg,jpeg,png,gif}', GLOB_BRACE);
    $processed = 0;
    $totalSavings = 0;
    
    foreach ($files as $file) {
        $filename = basename($file);
        
        if (strpos($filename, 'menu_') === 0 && strpos($filename, '.jpg') !== false) {
            continue;
        }
        
        $backupPath = $backupDir . $filename;
        $originalSize = filesize($file);
        
        copy($file, $backupPath);
        
        $sourceImage = null;
        $imageInfo = getimagesize($file);
        
        switch ($imageInfo['mime']) {
            case 'image/jpeg':
                $sourceImage = imagecreatefromjpeg($file);
                break;
            case 'image/png':
                $sourceImage = imagecreatefrompng($file);
                break;
            case 'image/gif':
                $sourceImage = imagecreatefromgif($file);
                break;
        }
        
        if ($sourceImage) {
            $newFilename = 'menu_' . time() . '_' . uniqid() . '.jpg';
            $newPath = $menuDir . $newFilename;
            
            if (autoCompressImage($sourceImage, $newPath, 80, 800)) {
                $compressedSize = filesize($newPath);
                $savings = $originalSize - $compressedSize;
                $totalSavings += $savings;
                
                unlink($file);
                
                // Update database
                $stmt = $conn->prepare("UPDATE menu_items SET image_url = ? WHERE image_url = ?");
                if ($stmt) {
                    $stmt->bind_param("ss", $newFilename, $filename);
                    $stmt->execute();
                    $stmt->close();
                }
                
                $processed++;
            }
            
            imagedestroy($sourceImage);
        }
    }
    
    return [
        'success' => true, 
        'processed' => $processed, 
        'savings' => $totalSavings
    ];
}

// ============================================================================
// MENU ITEM FUNCTIONS
// ============================================================================

/**
 * Get all menu items with category information
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
    if (!$result) {
        return [];
    }
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * Get menu items by category
 */
function getMenuItemsByCategory($categoryId, $availableOnly = true) {
    global $conn;
    $sql = "SELECT mi.*, c.category_name 
            FROM menu_items mi 
            JOIN categories c ON mi.category_id = c.category_id 
            WHERE mi.category_id = ? AND c.status = 'active'";
    
    if ($availableOnly) {
        $sql .= " AND mi.availability = 'available'";
    }
    
    $sql .= " ORDER BY mi.sort_order ASC, mi.item_name ASC";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return [];
    }
    $stmt->bind_param("i", $categoryId);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * Get menu item by ID
 */
function getMenuItemById($itemId) {
    global $conn;
    $stmt = $conn->prepare("SELECT mi.*, c.category_name 
                           FROM menu_items mi 
                           JOIN categories c ON mi.category_id = c.category_id 
                           WHERE mi.item_id = ?");
    if (!$stmt) {
        return null;
    }
    $stmt->bind_param("i", $itemId);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

/**
 * Get featured menu items
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
    if (!$stmt) {
        return [];
    }
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * Search menu items
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
    $types = "sss";
    
    if ($categoryId) {
        $sql .= " AND mi.category_id = ?";
        $params[] = $categoryId;
        $types .= "i";
    }
    
    $sql .= " ORDER BY mi.item_name ASC";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return [];
    }
    
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * Get menu items by price range
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
    
    if (!$stmt) {
        return [];
    }
    
    $stmt->bind_param("dd", $minPrice, $maxPrice);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * Create new menu item
 */
function createMenuItem($itemData) {
    global $conn;
    
    if (empty($itemData['item_name']) || empty($itemData['price']) || empty($itemData['category_id'])) {
        error_log("Create menu item error: Missing required fields");
        return false;
    }
    
    $category_id = (int)$itemData['category_id'];
    $item_name = $itemData['item_name'];
    $description = isset($itemData['description']) ? $itemData['description'] : '';
    $price = (float)$itemData['price'];
    $image_url = isset($itemData['image_url']) ? $itemData['image_url'] : null;
    $preparation_time = isset($itemData['preparation_time']) ? (int)$itemData['preparation_time'] : 15;
    $ingredients = isset($itemData['ingredients']) ? $itemData['ingredients'] : '';
    $allergens = isset($itemData['allergens']) ? $itemData['allergens'] : '';
    $calories = isset($itemData['calories']) && $itemData['calories'] ? (int)$itemData['calories'] : null;
    $is_featured = isset($itemData['is_featured']) ? 1 : 0;
    $sort_order = isset($itemData['sort_order']) ? (int)$itemData['sort_order'] : 0;
    
    $stmt = $conn->prepare("INSERT INTO menu_items (category_id, item_name, description, price, image_url, preparation_time, ingredients, allergens, calories, is_featured, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    if (!$stmt) {
        error_log("Create menu item error: Prepare failed - " . $conn->error);
        return false;
    }
    
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
        $insertId = $stmt->insert_id;
        $stmt->close();
        error_log("Menu item created successfully: ID $insertId, Image: " . ($image_url ?? 'none'));
        return $insertId;
    } else {
        error_log("Create menu item error: Execute failed - " . $stmt->error);
        $stmt->close();
        return false;
    }
}

/**
 * Update menu item
 */
function updateMenuItem($itemId, $itemData) {
    global $conn;
    
    $category_id = (int)$itemData['category_id'];
    $item_name = $itemData['item_name'];
    $description = isset($itemData['description']) ? $itemData['description'] : '';
    $price = (float)$itemData['price'];
    $image_url = isset($itemData['image_url']) ? $itemData['image_url'] : null;
    $preparation_time = isset($itemData['preparation_time']) ? (int)$itemData['preparation_time'] : 15;
    $ingredients = isset($itemData['ingredients']) ? $itemData['ingredients'] : '';
    $allergens = isset($itemData['allergens']) ? $itemData['allergens'] : '';
    $calories = isset($itemData['calories']) && $itemData['calories'] ? (int)$itemData['calories'] : null;
    $is_featured = isset($itemData['is_featured']) ? 1 : 0;
    $sort_order = isset($itemData['sort_order']) ? (int)$itemData['sort_order'] : 0;
    $item_id = (int)$itemId;
    
    $stmt = $conn->prepare("UPDATE menu_items SET category_id = ?, item_name = ?, description = ?, price = ?, image_url = ?, preparation_time = ?, ingredients = ?, allergens = ?, calories = ?, is_featured = ?, sort_order = ?, updated_at = CURRENT_TIMESTAMP WHERE item_id = ?");
    
    if (!$stmt) {
        error_log("Update menu item error: Prepare failed - " . $conn->error);
        return false;
    }
    
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
    
    if ($stmt->execute()) {
        $stmt->close();
        error_log("Menu item updated successfully: ID $item_id, Image: " . ($image_url ?? 'none'));
        return true;
    } else {
        error_log("Update menu item error: Execute failed - " . $stmt->error);
        $stmt->close();
        return false;
    }
}

/**
 * Toggle menu item availability
 */
function toggleMenuItemAvailability($itemId) {
    global $conn;
    
    $stmt = $conn->prepare("UPDATE menu_items SET availability = CASE WHEN availability = 'available' THEN 'unavailable' ELSE 'available' END, updated_at = CURRENT_TIMESTAMP WHERE item_id = ?");
    $stmt->bind_param("i", $itemId);
    
    return $stmt->execute();
}

/**
 * Delete menu item
 */
function deleteMenuItem($itemId) {
    global $conn;
    
    $stmt = $conn->prepare("DELETE FROM menu_items WHERE item_id = ?");
    $stmt->bind_param("i", $itemId);
    return $stmt->execute();
}

/**
 * Get menu statistics
 */
function getMenuStatistics() {
    global $conn;
    
    $stats = [];
    
    $result = $conn->query("SELECT COUNT(*) as count FROM categories WHERE status = 'active'");
    $stats['total_categories'] = $result->fetch_assoc()['count'];
    
    $result = $conn->query("SELECT COUNT(*) as count FROM menu_items WHERE availability = 'available'");
    $stats['total_items'] = $result->fetch_assoc()['count'];
    
    $result = $conn->query("SELECT COUNT(*) as count FROM menu_items WHERE is_featured = 1 AND availability = 'available'");
    $stats['featured_items'] = $result->fetch_assoc()['count'];
    
    $result = $conn->query("SELECT AVG(price) as avg_price FROM menu_items WHERE availability = 'available'");
    $stats['average_price'] = round($result->fetch_assoc()['avg_price'], 2);
    
    return $stats;
}

// ============================================================================
// CART FUNCTIONS
// ============================================================================

/**
 * Add item to cart
 */
function addToCart($itemId, $quantity = 1) {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    $item = getMenuItemById($itemId);
    if (!$item || $item['availability'] !== 'available') {
        return false;
    }
    
    if (isset($_SESSION['cart'][$itemId])) {
        $_SESSION['cart'][$itemId]['quantity'] += $quantity;
    } else {
        $_SESSION['cart'][$itemId] = [
            'item_id' => $itemId,
            'quantity' => $quantity,
            'added_at' => time()
        ];
    }
    
    if ($_SESSION['cart'][$itemId]['quantity'] > 10) {
        $_SESSION['cart'][$itemId]['quantity'] = 10;
    }
    
    return true;
}

/**
 * Update cart item quantity
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
 */
function isInCart($itemId) {
    return isset($_SESSION['cart'][$itemId]);
}

/**
 * Get item quantity in cart
 */
function getCartItemQuantity($itemId) {
    return isset($_SESSION['cart'][$itemId]) ? $_SESSION['cart'][$itemId]['quantity'] : 0;
}

// ============================================================================
// ORDER FUNCTIONS
// ============================================================================

/**
 * Create order from cart
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
    
    $conn->autocommit(false);
    
    try {
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
        
        $conn->commit();
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
 * Update order payment status
 */
function updateOrderPaymentStatus($orderId, $paymentStatus) {
    global $conn;
    $validStatuses = ['pending', 'paid', 'failed', 'refunded'];
    
    if (!in_array($paymentStatus, $validStatuses)) {
        return false;
    }
    
    $stmt = $conn->prepare("UPDATE orders SET payment_status = ?, updated_at = CURRENT_TIMESTAMP WHERE order_id = ?");
    $stmt->bind_param("si", $paymentStatus, $orderId);
    
    return $stmt->execute();
}

/**
 * Cancel order
 */
function cancelOrder($orderId, $userId = null) {
    global $conn;
    
    $order = getOrderById($orderId);
    if (!$order || !in_array($order['order_status'], ['pending', 'confirmed'])) {
        return false;
    }
    
    if ($userId && $order['user_id'] != $userId) {
        return false;
    }
    
    $stmt = $conn->prepare("UPDATE orders SET order_status = 'cancelled', updated_at = CURRENT_TIMESTAMP WHERE order_id = ?");
    $stmt->bind_param("i", $orderId);
    
    if ($stmt->execute()) {
        addOrderStatusHistory($orderId, 'cancelled', 'Order cancelled', $userId);
        return true;
    }
    
    return false;
}

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
 * Get admin orders with enhanced filtering including payment status
 */
function getAdminOrdersEnhanced($statusFilter = null, $dateFilter = null, $paymentFilter = null, $limit = 50) {
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
    
    if ($paymentFilter) {
        $sql .= " AND o.payment_status = ?";
        $params[] = $paymentFilter;
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
    
    $result = $conn->query("SELECT COUNT(*) as count FROM orders");
    $stats['total_orders'] = $result->fetch_assoc()['count'];
    
    $result = $conn->query("SELECT COUNT(*) as count FROM orders WHERE order_status = 'pending'");
    $stats['pending_orders'] = $result->fetch_assoc()['count'];
    
    $result = $conn->query("SELECT COUNT(*) as count FROM orders WHERE order_status = 'preparing'");
    $stats['preparing_orders'] = $result->fetch_assoc()['count'];
    
    $result = $conn->query("SELECT SUM(total_amount) as total FROM orders WHERE order_status = 'delivered'");
    $stats['total_revenue'] = $result->fetch_assoc()['total'] ?? 0;
    
    return $stats;
}

/**
 * Get payment statistics
 */
function getPaymentStatistics() {
    global $conn;
    
    $stats = [];
    
    $result = $conn->query("SELECT COUNT(*) as count FROM orders WHERE payment_status = 'pending'");
    $stats['pending_payments'] = $result->fetch_assoc()['count'];
    
    $result = $conn->query("SELECT SUM(total_amount) as total FROM orders WHERE payment_status = 'pending'");
    $stats['pending_amount'] = $result->fetch_assoc()['total'] ?? 0;
    
    $result = $conn->query("SELECT COUNT(*) as count FROM orders WHERE payment_status = 'pending' AND order_status = 'delivered'");
    $stats['delivered_unpaid'] = $result->fetch_assoc()['count'];
    
    return $stats;
}

/**
 * Get orders with pending payments
 */
function getOrdersWithPendingPayments($limit = 10) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT o.*, u.full_name 
                           FROM orders o 
                           JOIN users u ON o.user_id = u.user_id 
                           WHERE o.payment_status = 'pending' 
                           AND o.order_status IN ('delivered', 'ready', 'confirmed', 'preparing')
                           ORDER BY o.created_at DESC 
                           LIMIT ?");
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

/**
 * Get dashboard statistics
 */
function getDashboardStats() {
    global $conn;
    
    $stats = [];
    
    $result = $conn->query("SELECT COUNT(*) as count FROM orders WHERE DATE(created_at) = CURDATE()");
    $stats['total_orders_today'] = $result->fetch_assoc()['count'];
    
    $result = $conn->query("SELECT SUM(total_amount) as total FROM orders WHERE DATE(created_at) = CURDATE() AND order_status = 'delivered'");
    $stats['revenue_today'] = $result->fetch_assoc()['total'] ?? 0;
    
    $result = $conn->query("SELECT COUNT(DISTINCT user_id) as count FROM orders WHERE MONTH(created_at) = MONTH(CURDATE())");
    $stats['active_customers'] = $result->fetch_assoc()['count'];
    
    $result = $conn->query("SELECT AVG(total_amount) as avg_val FROM orders WHERE order_status = 'delivered'");
    $stats['avg_order_value'] = formatCurrency($result->fetch_assoc()['avg_val'] ?? 0);
    
    $result = $conn->query("SELECT COUNT(*) as count FROM orders WHERE order_status = 'pending'");
    $stats['pending_orders'] = $result->fetch_assoc()['count'];
    
    $result = $conn->query("SELECT COUNT(*) as count FROM menu_items WHERE availability = 'available'");
    $stats['total_menu_items'] = $result->fetch_assoc()['count'];
    
    $result = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'customer' AND status = 'active'");
    $stats['total_customers'] = $result->fetch_assoc()['count'];
    
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
    
    if ($status === null) {
        $stmt = $conn->prepare("INSERT INTO order_status_history (order_id, status, notes, changed_by) VALUES (?, 'payment_update', ?, ?)");
        $stmt->bind_param("isi", $orderId, $notes, $changedBy);
    } else {
        $stmt = $conn->prepare("INSERT INTO order_status_history (order_id, status, notes, changed_by) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("issi", $orderId, $status, $notes, $changedBy);
    }
    
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
 * Get menu price range
 */
function getMenuPriceRange() {
    global $conn;
    
    $result = $conn->query("SELECT MIN(price) as min_price, MAX(price) as max_price FROM menu_items WHERE availability = 'available'");
    return $result->fetch_assoc();
}

/**
 * Get total users count
 */
function getTotalUsers() {
    global $conn;
    $result = $conn->query("SELECT COUNT(*) as total FROM users WHERE status = 'active'");
    $row = $result->fetch_assoc();
    return $row['total'];
}

/**
 * Get users by role
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
 * Get file extension from filename
 */
function getFileExtension($filename) {
    return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
}

/**
 * Check if file is valid image
 */
function isValidImage($filename) {
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
    $extension = getFileExtension($filename);
    return in_array($extension, $allowedExtensions);
}

/**
 * Upload file to server (generic function)
 */
function uploadFile($file, $directory = 'assets/images/') {
    if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
        return false;
    }
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }
    
    $extension = getFileExtension($file['name']);
    $filename = generateRandomString(20) . '.' . $extension;
    $filepath = $directory . $filename;
    
    if (!is_dir($directory)) {
        mkdir($directory, 0777, true);
    }
    
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return $filename;
    }
    
    return false;
}

/**
 * Get low stock items (placeholder function for future inventory management)
 */
function getLowStockItems($threshold = 10) {
    return [];
}

/**
 * Auto-confirm payment when order is delivered (for cash orders)
 */
function autoConfirmCashPayment($orderId) {
    global $conn;
    
    $order = getOrderById($orderId);
    if ($order && $order['payment_status'] === 'pending' && $order['order_status'] === 'delivered') {
        updateOrderPaymentStatus($orderId, 'paid');
        addOrderStatusHistory($orderId, null, 'Cash payment auto-confirmed on delivery', null);
        return true;
    }
    
    return false;
}
?>