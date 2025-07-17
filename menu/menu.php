<?php
/**
 * Menu Display Page - COMPLETE FIXED VERSION
 * Online Food Ordering System - Phase 3
 * 
 * Public menu display with categories, search, and filtering
 * FIXED: Image display issues with comprehensive debugging
 */

require_once '../config.php';
require_once '../functions.php';

// Get all categories and menu items
$categories = getCategories();
$selectedCategory = isset($_GET['category']) ? (int)$_GET['category'] : null;
$searchTerm = isset($_GET['search']) ? cleanInput($_GET['search']) : '';
$priceRange = isset($_GET['price_range']) ? $_GET['price_range'] : '';

// Get menu items based on filters
if (!empty($searchTerm)) {
    $menuItems = searchMenuItems($searchTerm, $selectedCategory);
    $pageSubtitle = "Search results for \"" . htmlspecialchars($searchTerm) . "\"";
} elseif ($selectedCategory) {
    $menuItems = getMenuItemsByCategory($selectedCategory);
    $category = getCategoryById($selectedCategory);
    $pageSubtitle = $category ? $category['category_name'] : 'Menu Items';
} elseif (!empty($priceRange)) {
    $ranges = explode('-', $priceRange);
    if (count($ranges) == 2) {
        $menuItems = getMenuItemsByPriceRange((float)$ranges[0], (float)$ranges[1]);
        $pageSubtitle = "Items between RM " . $ranges[0] . " - RM " . $ranges[1];
    } else {
        $menuItems = getAllMenuItems();
        $pageSubtitle = 'All Menu Items';
    }
} else {
    $menuItems = getAllMenuItems();
    $pageSubtitle = 'All Menu Items';
}

// Get featured items for homepage section
$featuredItems = getFeaturedMenuItems(6);
$menuPriceRange = getMenuPriceRange();

// Page configuration
$pageTitle = 'Menu';
$bodyClass = 'menu-page';

// FIXED: Comprehensive image display function with debugging
function getImageDisplay($item) {
    if (empty($item['image_url'])) {
        return null;
    }
    
    // Try multiple possible paths with proper path resolution
    $possiblePaths = [
        // From menu/ directory going up one level
        '../assets/images/menu/' . $item['image_url'],
        // Direct path (if running from root)
        'assets/images/menu/' . $item['image_url'],
        // Alternative common locations
        '../images/menu/' . $item['image_url'],
        '../uploads/menu/' . $item['image_url'],
        '../uploads/' . $item['image_url']
    ];
    
    foreach ($possiblePaths as $path) {
        if (file_exists($path)) {
            // Convert file path to proper URL
            if (strpos($path, '../') === 0) {
                // Remove ../ from beginning and create URL
                $urlPath = substr($path, 3); // Remove '../'
            } else {
                $urlPath = $path;
            }
            return SITE_URL . $urlPath;
        }
    }
    
    return null;
}

// Debug function for troubleshooting
function getImageDebugInfo($item) {
    if (empty($item['image_url'])) {
        return "No image URL";
    }
    
    $debug = [];
    $debug['filename'] = $item['image_url'];
    
    $testPaths = [
        '../assets/images/menu/' . $item['image_url'],
        'assets/images/menu/' . $item['image_url'],
        '../images/menu/' . $item['image_url']
    ];
    
    foreach ($testPaths as $path) {
        $debug['paths'][$path] = file_exists($path) ? 'EXISTS' : 'MISSING';
    }
    
    return $debug;
}

// Include header
include '../includes/header.php';
?>

<!-- Quick Debug Panel (only show when debugging) -->
<?php if (isset($_GET['debug']) && (isAdmin() || isset($_GET['force']))): ?>
<div style="background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; margin: 15px; border-radius: 5px;">
    <h4>🔧 Debug Information</h4>
    <p><strong>SITE_URL:</strong> <?php echo SITE_URL; ?></p>
    <p><strong>Current directory:</strong> <?php echo __DIR__; ?></p>
    <p><strong>Assets directory exists:</strong> <?php echo is_dir('../assets/images/menu/') ? '✅ YES' : '❌ NO'; ?></p>
    <p><strong>Directory is readable:</strong> <?php echo is_readable('../assets/images/menu/') ? '✅ YES' : '❌ NO'; ?></p>
    <?php if (is_dir('../assets/images/menu/')): ?>
        <p><strong>Files in menu directory:</strong></p>
        <ul style="margin: 5px 0;">
        <?php 
        $files = scandir('../assets/images/menu/');
        $imageFiles = array_filter($files, function($f) { return !in_array($f, ['.', '..']); });
        if (empty($imageFiles)): ?>
            <li>No files found</li>
        <?php else: ?>
            <?php foreach ($imageFiles as $file): ?>
                <li><?php echo htmlspecialchars($file); ?></li>
            <?php endforeach; ?>
        <?php endif; ?>
        </ul>
    <?php endif; ?>
    <p><em>Add ?debug=1 to URL to see this panel. Remove ?debug=1 to hide it.</em></p>
</div>
<?php endif; ?>

<!-- Add CSRF Token Meta Tag -->
<meta name="csrf-token" content="<?php echo generateCSRFToken(); ?>">

<div class="container">
    <!-- Hidden CSRF Form -->
    <form id="csrf-form" style="display: none;">
        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
    </form>

    <!-- Page Header -->
    <div class="page-header">
        <h1 class="page-title">Our Menu</h1>
        <p class="page-subtitle"><?php echo $pageSubtitle; ?></p>
    </div>
    
    <!-- Search and Filter Section -->
    <div class="menu-controls">
        <div class="card">
            <div class="card-body">
                <form method="GET" action="" class="filter-form">
                    <div class="filter-row">
                        <!-- Search -->
                        <div class="filter-group">
                            <label for="search" class="form-label">Search Menu</label>
                            <input type="text" 
                                   id="search" 
                                   name="search" 
                                   class="form-input" 
                                   placeholder="Search for dishes..."
                                   value="<?php echo htmlspecialchars($searchTerm); ?>">
                        </div>
                        
                        <!-- Category Filter -->
                        <div class="filter-group">
                            <label for="category" class="form-label">Category</label>
                            <select id="category" name="category" class="form-select">
                                <option value="">All Categories</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['category_id']; ?>" 
                                            <?php echo $selectedCategory == $category['category_id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($category['category_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <!-- Price Range Filter -->
                        <div class="filter-group">
                            <label for="price_range" class="form-label">Price Range</label>
                            <select id="price_range" name="price_range" class="form-select">
                                <option value="">All Prices</option>
                                <option value="0-10" <?php echo $priceRange === '0-10' ? 'selected' : ''; ?>>RM 0 - RM 10</option>
                                <option value="10-20" <?php echo $priceRange === '10-20' ? 'selected' : ''; ?>>RM 10 - RM 20</option>
                                <option value="20-30" <?php echo $priceRange === '20-30' ? 'selected' : ''; ?>>RM 20 - RM 30</option>
                                <option value="30-50" <?php echo $priceRange === '30-50' ? 'selected' : ''; ?>>RM 30 - RM 50</option>
                                <option value="50-100" <?php echo $priceRange === '50-100' ? 'selected' : ''; ?>>RM 50+</option>
                            </select>
                        </div>
                        
                        <!-- Filter Buttons -->
                        <div class="filter-group filter-buttons">
                            <button type="submit" class="btn btn-primary">
                                🔍 Filter
                            </button>
                            <a href="menu.php" class="btn btn-secondary">
                                Clear
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Quick Category Navigation -->
    <div class="category-nav">
        <div class="category-tabs">
            <a href="menu.php" class="category-tab <?php echo !$selectedCategory ? 'active' : ''; ?>">
                All Items
            </a>
            <?php foreach ($categories as $category): ?>
                <a href="?category=<?php echo $category['category_id']; ?>" 
                   class="category-tab <?php echo $selectedCategory == $category['category_id'] ? 'active' : ''; ?>">
                    <?php echo htmlspecialchars($category['category_name']); ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
    
    <!-- Featured Items Section (show only on main menu page) -->
    <?php if (!$selectedCategory && empty($searchTerm) && empty($priceRange) && !empty($featuredItems)): ?>
        <div class="featured-section">
            <div class="section-header">
                <h2 class="section-title">Featured Items</h2>
                <p class="section-subtitle">Try our chef's special recommendations</p>
            </div>
            
            <div class="menu-grid featured-grid">
                <?php foreach ($featuredItems as $item): ?>
                    <div class="menu-item featured-item">
                        <!-- FIXED: Image Display with Comprehensive Error Handling -->
                        <div class="item-image">
                            <?php 
                            $imageUrl = getImageDisplay($item);
                            $debugInfo = isset($_GET['debug']) ? getImageDebugInfo($item) : null;
                            
                            if ($imageUrl): ?>
                                <img src="<?php echo $imageUrl; ?>" 
                                     alt="<?php echo htmlspecialchars($item['item_name']); ?>"
                                     loading="lazy"
                                     style="width: 100%; height: 100%; object-fit: cover;"
                                     onerror="console.log('Image failed to load: <?php echo addslashes($imageUrl); ?>'); this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <div class="placeholder-image" style="display: none;">
                                    <span class="placeholder-icon">🍽️</span>
                                </div>
                            <?php else: ?>
                                <div class="placeholder-image">
                                    <span class="placeholder-icon">🍽️</span>
                                </div>
                            <?php endif; ?>
                            
                            <div class="featured-badge">Featured</div>
                            
                            <!-- Debug overlay (only when debugging) -->
                            <?php if (isset($_GET['debug']) && $debugInfo): ?>
                                <div style="position: absolute; bottom: 0; left: 0; right: 0; background: rgba(0,0,0,0.9); color: white; font-size: 9px; padding: 3px; line-height: 1.2;">
                                    <strong><?php echo htmlspecialchars($item['item_name']); ?></strong><br>
                                    DB: <?php echo htmlspecialchars($debugInfo['filename']); ?><br>
                                    <?php foreach ($debugInfo['paths'] as $path => $status): ?>
                                        <?php echo basename($path) . ':' . $status; ?><br>
                                    <?php endforeach; ?>
                                    URL: <?php echo $imageUrl ? '✅' : '❌'; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="item-content">
                            <div class="item-header">
                                <h3 class="item-name"><?php echo htmlspecialchars($item['item_name']); ?></h3>
                                <span class="item-price"><?php echo formatCurrency($item['price']); ?></span>
                            </div>
                            
                            <p class="item-description"><?php echo htmlspecialchars($item['description']); ?></p>
                            
                            <div class="item-meta">
                                <span class="item-category"><?php echo htmlspecialchars($item['category_name']); ?></span>
                                <span class="item-time">⏱️ <?php echo $item['preparation_time']; ?> mins</span>
                                <?php if ($item['calories']): ?>
                                    <span class="item-calories">🔥 <?php echo $item['calories']; ?> cal</span>
                                <?php endif; ?>
                            </div>
                            
                            <?php if ($item['allergens']): ?>
                                <div class="item-allergens">
                                    <small>⚠️ Contains: <?php echo htmlspecialchars($item['allergens']); ?></small>
                                </div>
                            <?php endif; ?>
                            
                            <div class="item-actions">
                                <?php if (isCustomer()): ?>
                                    <button class="btn btn-primary add-to-cart" 
                                            data-item-id="<?php echo $item['item_id']; ?>"
                                            data-item-name="<?php echo htmlspecialchars($item['item_name']); ?>"
                                            data-item-price="<?php echo $item['price']; ?>">
                                        Add to Cart
                                    </button>
                                <?php else: ?>
                                    <a href="<?php echo SITE_URL; ?>auth/login.php" class="btn btn-outline">
                                        Login to Order
                                    </a>
                                <?php endif; ?>
                                <button class="btn btn-secondary view-details" 
                                        data-item-id="<?php echo $item['item_id']; ?>">
                                    View Details
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
    
    <!-- Menu Items Section -->
    <div class="menu-section">
        <div class="section-header">
            <h2 class="section-title">
                <?php if ($selectedCategory && isset($category)): ?>
                    <?php echo htmlspecialchars($category['category_name']); ?>
                <?php elseif (!empty($searchTerm)): ?>
                    Search Results
                <?php else: ?>
                    All Menu Items
                <?php endif; ?>
            </h2>
            <p class="section-subtitle">
                <?php echo count($menuItems); ?> item<?php echo count($menuItems) !== 1 ? 's' : ''; ?> found
            </p>
        </div>
        
        <?php if (empty($menuItems)): ?>
            <div class="no-results">
                <div class="no-results-icon">🔍</div>
                <h3>No items found</h3>
                <p>Try adjusting your search terms or filters.</p>
                <a href="menu.php" class="btn btn-primary">View All Items</a>
            </div>
        <?php else: ?>
            <div class="menu-grid">
                <?php foreach ($menuItems as $item): ?>
                    <div class="menu-item">
                        <!-- FIXED: Image Display with Comprehensive Error Handling -->
                        <div class="item-image">
                            <?php 
                            $imageUrl = getImageDisplay($item);
                            $debugInfo = isset($_GET['debug']) ? getImageDebugInfo($item) : null;
                            
                            if ($imageUrl): ?>
                                <img src="<?php echo $imageUrl; ?>" 
                                     alt="<?php echo htmlspecialchars($item['item_name']); ?>"
                                     loading="lazy"
                                     style="width: 100%; height: 100%; object-fit: cover;"
                                     onerror="console.log('Image failed to load: <?php echo addslashes($imageUrl); ?>'); this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <div class="placeholder-image" style="display: none;">
                                    <span class="placeholder-icon">🍽️</span>
                                </div>
                            <?php else: ?>
                                <div class="placeholder-image">
                                    <span class="placeholder-icon">🍽️</span>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($item['is_featured']): ?>
                                <div class="featured-badge">Featured</div>
                            <?php endif; ?>
                            
                            <!-- Debug overlay (only when debugging) -->
                            <?php if (isset($_GET['debug']) && $debugInfo): ?>
                                <div style="position: absolute; bottom: 0; left: 0; right: 0; background: rgba(0,0,0,0.9); color: white; font-size: 9px; padding: 3px; line-height: 1.2;">
                                    <strong><?php echo htmlspecialchars($item['item_name']); ?></strong><br>
                                    DB: <?php echo htmlspecialchars($debugInfo['filename']); ?><br>
                                    <?php foreach ($debugInfo['paths'] as $path => $status): ?>
                                        <?php echo basename($path) . ':' . $status; ?><br>
                                    <?php endforeach; ?>
                                    URL: <?php echo $imageUrl ? '✅' : '❌'; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="item-content">
                            <div class="item-header">
                                <h3 class="item-name"><?php echo htmlspecialchars($item['item_name']); ?></h3>
                                <span class="item-price"><?php echo formatCurrency($item['price']); ?></span>
                            </div>
                            
                            <p class="item-description"><?php echo htmlspecialchars($item['description']); ?></p>
                            
                            <div class="item-meta">
                                <span class="item-category"><?php echo htmlspecialchars($item['category_name']); ?></span>
                                <span class="item-time">⏱️ <?php echo $item['preparation_time']; ?> mins</span>
                                <?php if ($item['calories']): ?>
                                    <span class="item-calories">🔥 <?php echo $item['calories']; ?> cal</span>
                                <?php endif; ?>
                            </div>
                            
                            <?php if ($item['allergens']): ?>
                                <div class="item-allergens">
                                    <small>⚠️ Contains: <?php echo htmlspecialchars($item['allergens']); ?></small>
                                </div>
                            <?php endif; ?>
                            
                            <div class="item-actions">
                                <?php if (isCustomer()): ?>
                                    <button class="btn btn-primary add-to-cart" 
                                            data-item-id="<?php echo $item['item_id']; ?>"
                                            data-item-name="<?php echo htmlspecialchars($item['item_name']); ?>"
                                            data-item-price="<?php echo $item['price']; ?>">
                                        Add to Cart
                                    </button>
                                <?php else: ?>
                                    <a href="<?php echo SITE_URL; ?>auth/login.php" class="btn btn-outline">
                                        Login to Order
                                    </a>
                                <?php endif; ?>
                                <button class="btn btn-secondary view-details" 
                                        data-item-id="<?php echo $item['item_id']; ?>">
                                    View Details
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Item Details Modal -->
<div id="item-details-modal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Item Details</h3>
            <button type="button" class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <div id="item-details-content">
                <!-- Content will be loaded dynamically -->
            </div>
        </div>
    </div>
</div>

<style>
/* Menu Page Specific Styles */
.menu-controls {
    margin-bottom: 2rem;
}

.filter-form {
    margin: 0;
}

.filter-row {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr auto;
    gap: 1rem;
    align-items: end;
}

.filter-group {
    display: flex;
    flex-direction: column;
}

.filter-buttons {
    display: flex;
    gap: 0.5rem;
}

.category-nav {
    margin-bottom: 2rem;
    overflow-x: auto;
}

.category-tabs {
    display: flex;
    gap: 0.5rem;
    padding: 1rem 0;
    min-width: max-content;
}

.category-tab {
    padding: 0.75rem 1.5rem;
    border: 1px solid #e2e8f0;
    border-radius: 2rem;
    text-decoration: none;
    color: #64748b;
    font-weight: 500;
    transition: all 0.2s ease;
    white-space: nowrap;
}

.category-tab:hover {
    background-color: #f1f5f9;
    border-color: #334155;
}

.category-tab.active {
    background-color: #334155;
    color: white;
    border-color: #334155;
}

.section-header {
    text-align: center;
    margin-bottom: 2rem;
}

.section-title {
    font-size: 1.75rem;
    font-weight: bold;
    color: #1e293b;
    margin-bottom: 0.5rem;
}

.section-subtitle {
    color: #64748b;
    font-size: 1.1rem;
}

.featured-section {
    margin-bottom: 3rem;
    padding: 2rem;
    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
    border-radius: 0.5rem;
}

.menu-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
}

.featured-grid {
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
}

.menu-item {
    background: white;
    border-radius: 0.5rem;
    overflow: hidden;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.menu-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}

.featured-item {
    border: 2px solid #fbbf24;
}

.item-image {
    position: relative;
    height: 200px;
    overflow: hidden;
}

.item-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: opacity 0.3s ease;
}

.placeholder-image {
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
    display: flex;
    align-items: center;
    justify-content: center;
}

.placeholder-icon {
    font-size: 3rem;
    color: #94a3b8;
}

.featured-badge {
    position: absolute;
    top: 0.5rem;
    right: 0.5rem;
    background: #fbbf24;
    color: #92400e;
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
}

.item-content {
    padding: 1.5rem;
}

.item-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 0.5rem;
}

.item-name {
    font-size: 1.1rem;
    font-weight: 600;
    color: #1e293b;
    margin: 0;
    flex: 1;
}

.item-price {
    font-size: 1.25rem;
    font-weight: bold;
    color: #334155;
    margin-left: 1rem;
}

.item-description {
    color: #64748b;
    font-size: 0.9rem;
    line-height: 1.5;
    margin-bottom: 1rem;
}

.item-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
    margin-bottom: 0.75rem;
    font-size: 0.875rem;
}

.item-category {
    color: #334155;
    font-weight: 500;
}

.item-time,
.item-calories {
    color: #64748b;
}

.item-allergens {
    margin-bottom: 1rem;
}

.item-allergens small {
    color: #dc2626;
    font-size: 0.8rem;
}

.item-actions {
    display: flex;
    gap: 0.5rem;
}

.item-actions .btn {
    flex: 1;
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
}

.no-results {
    text-align: center;
    padding: 3rem;
    color: #64748b;
}

.no-results-icon {
    font-size: 4rem;
    margin-bottom: 1rem;
}

.no-results h3 {
    color: #1e293b;
    margin-bottom: 0.5rem;
}

/* Success animation for add to cart */
.success-animation {
    animation: successPulse 0.6s ease-in-out;
}

@keyframes successPulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.05); }
}

/* Responsive Design */
@media (max-width: 768px) {
    .filter-row {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .filter-buttons {
        justify-content: center;
    }
    
    .category-tabs {
        padding: 0.5rem 0;
    }
    
    .menu-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .item-actions {
        flex-direction: column;
    }
    
    .item-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .item-price {
        margin-left: 0;
        margin-top: 0.25rem;
    }
}

@media (max-width: 480px) {
    .featured-section {
        padding: 1rem;
    }
    
    .item-content {
        padding: 1rem;
    }
    
    .section-title {
        font-size: 1.5rem;
    }
}
</style>

<?php
// Include footer
include '../includes/footer.php';
?>