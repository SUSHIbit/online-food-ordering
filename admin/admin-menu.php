<?php
/**
 * Admin Menu Management
 * Online Food Ordering System - Phase 2
 * 
 * Admin interface for managing menu items and categories
 */

require_once '../config.php';
require_once '../functions.php';

// Require admin access
requireAdmin();

$currentUser = getCurrentUser();
$errors = [];
$success = false;

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        $action = $_POST['action'] ?? '';
        
        switch ($action) {
            case 'add_category':
                $categoryData = [
                    'category_name' => cleanInput($_POST['category_name'] ?? ''),
                    'description' => cleanInput($_POST['description'] ?? ''),
                    'sort_order' => (int)($_POST['sort_order'] ?? 0)
                ];
                
                if (empty($categoryData['category_name'])) {
                    $errors[] = 'Category name is required.';
                } elseif (categoryNameExists($categoryData['category_name'])) {
                    $errors[] = 'Category name already exists.';
                } else {
                    $categoryId = createCategory($categoryData);
                    if ($categoryId) {
                        $_SESSION['flash_message']['success'] = 'Category added successfully!';
                        header('Location: admin-menu.php');
                        exit();
                    } else {
                        $errors[] = 'Failed to add category.';
                    }
                }
                break;
                
            case 'edit_category':
                $categoryId = (int)($_POST['category_id'] ?? 0);
                $categoryData = [
                    'category_name' => cleanInput($_POST['category_name'] ?? ''),
                    'description' => cleanInput($_POST['description'] ?? ''),
                    'sort_order' => (int)($_POST['sort_order'] ?? 0)
                ];
                
                if (empty($categoryData['category_name'])) {
                    $errors[] = 'Category name is required.';
                } elseif (categoryNameExists($categoryData['category_name'], $categoryId)) {
                    $errors[] = 'Category name already exists.';
                } else {
                    if (updateCategory($categoryId, $categoryData)) {
                        $_SESSION['flash_message']['success'] = 'Category updated successfully!';
                        header('Location: admin-menu.php');
                        exit();
                    } else {
                        $errors[] = 'Failed to update category.';
                    }
                }
                break;
                
            case 'delete_category':
                $categoryId = (int)($_POST['category_id'] ?? 0);
                if (deleteCategory($categoryId)) {
                    $_SESSION['flash_message']['success'] = 'Category deleted successfully!';
                } else {
                    $_SESSION['flash_message']['error'] = 'Cannot delete category. It may contain menu items.';
                }
                header('Location: admin-menu.php');
                exit();
                break;
                
            case 'add_item':
                $itemData = [
                    'category_id' => (int)($_POST['category_id'] ?? 0),
                    'item_name' => cleanInput($_POST['item_name'] ?? ''),
                    'description' => cleanInput($_POST['description'] ?? ''),
                    'price' => (float)($_POST['price'] ?? 0),
                    'preparation_time' => (int)($_POST['preparation_time'] ?? 15),
                    'ingredients' => cleanInput($_POST['ingredients'] ?? ''),
                    'allergens' => cleanInput($_POST['allergens'] ?? ''),
                    'calories' => $_POST['calories'] ? (int)$_POST['calories'] : null,
                    'is_featured' => isset($_POST['is_featured']),
                    'sort_order' => (int)($_POST['sort_order'] ?? 0)
                ];
                
                // Handle image upload
                if (isset($_FILES['image']) && $_FILES['image']['size'] > 0) {
                    $imageName = uploadMenuImage($_FILES['image']);
                    if ($imageName) {
                        $itemData['image_url'] = $imageName;
                    } else {
                        $errors[] = 'Failed to upload image.';
                    }
                }
                
                if (empty($itemData['item_name'])) {
                    $errors[] = 'Item name is required.';
                } elseif ($itemData['price'] <= 0) {
                    $errors[] = 'Price must be greater than 0.';
                } elseif (!$itemData['category_id']) {
                    $errors[] = 'Category is required.';
                } elseif (empty($errors)) {
                    $itemId = createMenuItem($itemData);
                    if ($itemId) {
                        $_SESSION['flash_message']['success'] = 'Menu item added successfully!';
                        header('Location: admin-menu.php');
                        exit();
                    } else {
                        $errors[] = 'Failed to add menu item.';
                    }
                }
                break;
                
            case 'edit_item':
                $itemId = (int)($_POST['item_id'] ?? 0);
                $itemData = [
                    'category_id' => (int)($_POST['category_id'] ?? 0),
                    'item_name' => cleanInput($_POST['item_name'] ?? ''),
                    'description' => cleanInput($_POST['description'] ?? ''),
                    'price' => (float)($_POST['price'] ?? 0),
                    'preparation_time' => (int)($_POST['preparation_time'] ?? 15),
                    'ingredients' => cleanInput($_POST['ingredients'] ?? ''),
                    'allergens' => cleanInput($_POST['allergens'] ?? ''),
                    'calories' => $_POST['calories'] ? (int)$_POST['calories'] : null,
                    'is_featured' => isset($_POST['is_featured']),
                    'sort_order' => (int)($_POST['sort_order'] ?? 0)
                ];
                
                // Get current item for image handling
                $currentItem = getMenuItemById($itemId);
                $itemData['image_url'] = $currentItem['image_url'];
                
                // Handle image upload
                if (isset($_FILES['image']) && $_FILES['image']['size'] > 0) {
                    $imageName = uploadMenuImage($_FILES['image']);
                    if ($imageName) {
                        $itemData['image_url'] = $imageName;
                        // Delete old image if exists
                        if ($currentItem['image_url']) {
                            @unlink(UPLOAD_PATH . 'menu/' . $currentItem['image_url']);
                        }
                    } else {
                        $errors[] = 'Failed to upload image.';
                    }
                }
                
                if (empty($itemData['item_name'])) {
                    $errors[] = 'Item name is required.';
                } elseif ($itemData['price'] <= 0) {
                    $errors[] = 'Price must be greater than 0.';
                } elseif (!$itemData['category_id']) {
                    $errors[] = 'Category is required.';
                } elseif (empty($errors)) {
                    if (updateMenuItem($itemId, $itemData)) {
                        $_SESSION['flash_message']['success'] = 'Menu item updated successfully!';
                        header('Location: admin-menu.php');
                        exit();
                    } else {
                        $errors[] = 'Failed to update menu item.';
                    }
                }
                break;
                
            case 'toggle_availability':
                $itemId = (int)($_POST['item_id'] ?? 0);
                if (toggleMenuItemAvailability($itemId)) {
                    $_SESSION['flash_message']['success'] = 'Item availability updated!';
                } else {
                    $_SESSION['flash_message']['error'] = 'Failed to update availability.';
                }
                header('Location: admin-menu.php');
                exit();
                break;
                
            case 'delete_item':
                $itemId = (int)($_POST['item_id'] ?? 0);
                $item = getMenuItemById($itemId);
                
                if (deleteMenuItem($itemId)) {
                    // Delete image file if exists
                    if ($item['image_url']) {
                        @unlink(UPLOAD_PATH . 'menu/' . $item['image_url']);
                    }
                    $_SESSION['flash_message']['success'] = 'Menu item deleted successfully!';
                } else {
                    $_SESSION['flash_message']['error'] = 'Failed to delete menu item.';
                }
                header('Location: admin-menu.php');
                exit();
                break;
        }
    }
}

// Get data for display
$categories = getCategories();
$menuItems = getAllMenuItems(false); // Include unavailable items for admin
$statistics = getMenuStatistics();

// Get current action for modal display
$currentAction = $_GET['action'] ?? '';
$editCategoryId = $_GET['edit_category'] ?? null;
$editItemId = $_GET['edit_item'] ?? null;

// Page configuration
$pageTitle = 'Menu Management';
$bodyClass = 'admin-page';

// Include header
include '../includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h1 class="page-title">Menu Management</h1>
        <p class="page-subtitle">Manage categories and menu items</p>
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
    
    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">📂</div>
            <div class="stat-content">
                <div class="stat-number"><?php echo $statistics['total_categories']; ?></div>
                <div class="stat-label">Categories</div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">🍽️</div>
            <div class="stat-content">
                <div class="stat-number"><?php echo $statistics['total_items']; ?></div>
                <div class="stat-label">Menu Items</div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">⭐</div>
            <div class="stat-content">
                <div class="stat-number"><?php echo $statistics['featured_items']; ?></div>
                <div class="stat-label">Featured Items</div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">💰</div>
            <div class="stat-content">
                <div class="stat-number"><?php echo formatCurrency($statistics['average_price']); ?></div>
                <div class="stat-label">Avg Price</div>
            </div>
        </div>
    </div>
    
    <!-- Action Buttons -->
    <div class="action-buttons">
        <button class="btn btn-primary" data-modal="add-category-modal">
            ➕ Add Category
        </button>
        <button class="btn btn-primary" data-modal="add-item-modal">
            ➕ Add Menu Item
        </button>
    </div>
    
    <!-- Categories Management -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Categories</h3>
        </div>
        <div class="card-body">
            <?php if (empty($categories)): ?>
                <div class="no-data">
                    <p>No categories found. Add your first category to get started.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Category Name</th>
                                <th>Description</th>
                                <th>Items Count</th>
                                <th>Sort Order</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categories as $category): ?>
                                <?php
                                $categoryItems = getMenuItemsByCategory($category['category_id'], false);
                                ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($category['category_name']); ?></strong>
                                    </td>
                                    <td><?php echo htmlspecialchars($category['description']); ?></td>
                                    <td>
                                        <span class="badge badge-info"><?php echo count($categoryItems); ?> items</span>
                                    </td>
                                    <td><?php echo $category['sort_order']; ?></td>
                                    <td>
                                        <div class="action-buttons-small">
                                            <a href="?edit_category=<?php echo $category['category_id']; ?>" 
                                               class="btn btn-small btn-secondary">
                                                ✏️ Edit
                                            </a>
                                            <?php if (count($categoryItems) == 0): ?>
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                                    <input type="hidden" name="action" value="delete_category">
                                                    <input type="hidden" name="category_id" value="<?php echo $category['category_id']; ?>">
                                                    <button type="submit" 
                                                            class="btn btn-small btn-danger"
                                                            data-confirm="Are you sure you want to delete this category?">
                                                        🗑️ Delete
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Menu Items Management -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Menu Items</h3>
        </div>
        <div class="card-body">
            <?php if (empty($menuItems)): ?>
                <div class="no-data">
                    <p>No menu items found. Add your first menu item to get started.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>Item Name</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Status</th>
                                <th>Featured</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($menuItems as $item): ?>
                                <tr>
                                    <td>
                                        <?php if ($item['image_url']): ?>
                                            <img src="<?php echo SITE_URL . UPLOAD_PATH . 'menu/' . $item['image_url']; ?>" 
                                                 alt="<?php echo htmlspecialchars($item['item_name']); ?>"
                                                 class="item-thumbnail">
                                        <?php else: ?>
                                            <div class="item-thumbnail-placeholder">🍽️</div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($item['item_name']); ?></strong>
                                        <br>
                                        <small class="text-slate-500">
                                            <?php echo htmlspecialchars(substr($item['description'], 0, 50)) . '...'; ?>
                                        </small>
                                    </td>
                                    <td><?php echo htmlspecialchars($item['category_name']); ?></td>
                                    <td>
                                        <strong><?php echo formatCurrency($item['price']); ?></strong>
                                    </td>
                                    <td>
                                        <span class="status-badge status-<?php echo $item['availability']; ?>">
                                            <?php echo ucfirst($item['availability']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($item['is_featured']): ?>
                                            <span class="badge badge-warning">Featured</span>
                                        <?php else: ?>
                                            <span class="text-slate-400">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="action-buttons-small">
                                            <a href="?edit_item=<?php echo $item['item_id']; ?>" 
                                               class="btn btn-small btn-secondary">
                                                ✏️ Edit
                                            </a>
                                            
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                                <input type="hidden" name="action" value="toggle_availability">
                                                <input type="hidden" name="item_id" value="<?php echo $item['item_id']; ?>">
                                                <button type="submit" 
                                                        class="btn btn-small <?php echo $item['availability'] == 'available' ? 'btn-warning' : 'btn-success'; ?>">
                                                    <?php echo $item['availability'] == 'available' ? '⏸️ Disable' : '▶️ Enable'; ?>
                                                </button>
                                            </form>
                                            
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                                <input type="hidden" name="action" value="delete_item">
                                                <input type="hidden" name="item_id" value="<?php echo $item['item_id']; ?>">
                                                <button type="submit" 
                                                        class="btn btn-small btn-danger"
                                                        data-confirm="Are you sure you want to delete this menu item?">
                                                    🗑️ Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Add Category Modal -->
<div id="add-category-modal" class="modal <?php echo $currentAction == 'add_category' ? 'active' : ''; ?>">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Add New Category</h3>
            <button type="button" class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <input type="hidden" name="action" value="add_category">
                
                <div class="form-group">
                    <label for="category_name" class="form-label">Category Name *</label>
                    <input type="text" 
                           id="category_name" 
                           name="category_name" 
                           class="form-input" 
                           required>
                </div>
                
                <div class="form-group">
                    <label for="description" class="form-label">Description</label>
                    <textarea id="description" 
                              name="description" 
                              class="form-input form-textarea" 
                              rows="3"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="sort_order" class="form-label">Sort Order</label>
                    <input type="number" 
                           id="sort_order" 
                           name="sort_order" 
                           class="form-input" 
                           value="0" 
                           min="0">
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Add Category</button>
                    <button type="button" class="btn btn-secondary" data-modal-close>Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Category Modal -->
<?php if ($editCategoryId): ?>
    <?php $editCategory = getCategoryById($editCategoryId); ?>
    <?php if ($editCategory): ?>
        <div id="edit-category-modal" class="modal active">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title">Edit Category</h3>
                    <button type="button" class="modal-close" onclick="window.location.href='admin-menu.php'">&times;</button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        <input type="hidden" name="action" value="edit_category">
                        <input type="hidden" name="category_id" value="<?php echo $editCategory['category_id']; ?>">
                        
                        <div class="form-group">
                            <label for="edit_category_name" class="form-label">Category Name *</label>
                            <input type="text" 
                                   id="edit_category_name" 
                                   name="category_name" 
                                   class="form-input" 
                                   value="<?php echo htmlspecialchars($editCategory['category_name']); ?>"
                                   required>
                        </div>
                        
                        <div class="form-group">
                            <label for="edit_description" class="form-label">Description</label>
                            <textarea id="edit_description" 
                                      name="description" 
                                      class="form-input form-textarea" 
                                      rows="3"><?php echo htmlspecialchars($editCategory['description']); ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="edit_sort_order" class="form-label">Sort Order</label>
                            <input type="number" 
                                   id="edit_sort_order" 
                                   name="sort_order" 
                                   class="form-input" 
                                   value="<?php echo $editCategory['sort_order']; ?>" 
                                   min="0">
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">Update Category</button>
                            <a href="admin-menu.php" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endif; ?>
<?php endif; ?>

<!-- Add Menu Item Modal -->
<div id="add-item-modal" class="modal <?php echo $currentAction == 'add_item' ? 'active' : ''; ?>">
    <div class="modal-content large-modal">
        <div class="modal-header">
            <h3 class="modal-title">Add New Menu Item</h3>
            <button type="button" class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <form method="POST" action="" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <input type="hidden" name="action" value="add_item">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="item_name" class="form-label">Item Name *</label>
                        <input type="text" 
                               id="item_name" 
                               name="item_name" 
                               class="form-input" 
                               required>
                    </div>
                    
                    <div class="form-group">
                        <label for="category_id" class="form-label">Category *</label>
                        <select id="category_id" name="category_id" class="form-select" required>
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['category_id']; ?>">
                                    <?php echo htmlspecialchars($category['category_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="description" class="form-label">Description</label>
                    <textarea id="description" 
                              name="description" 
                              class="form-input form-textarea" 
                              rows="3"></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="price" class="form-label">Price (RM) *</label>
                        <input type="number" 
                               id="price" 
                               name="price" 
                               class="form-input" 
                               step="0.01" 
                               min="0.01" 
                               required>
                    </div>
                    
                    <div class="form-group">
                        <label for="preparation_time" class="form-label">Preparation Time (mins)</label>
                        <input type="number" 
                               id="preparation_time" 
                               name="preparation_time" 
                               class="form-input" 
                               value="15" 
                               min="1">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="image" class="form-label">Image</label>
                    <input type="file" 
                           id="image" 
                           name="image" 
                           class="form-input" 
                           accept="image/*">
                    <small class="text-slate-500">Max size: 5MB. Supported formats: JPG, PNG, GIF</small>
                </div>
                
                <div class="form-group">
                    <label for="ingredients" class="form-label">Ingredients</label>
                    <textarea id="ingredients" 
                              name="ingredients" 
                              class="form-input form-textarea" 
                              rows="2"
                              placeholder="List main ingredients separated by commas"></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="allergens" class="form-label">Allergens</label>
                        <input type="text" 
                               id="allergens" 
                               name="allergens" 
                               class="form-input"
                               placeholder="e.g., Gluten, Dairy, Nuts">
                    </div>
                    
                    <div class="form-group">
                        <label for="calories" class="form-label">Calories</label>
                        <input type="number" 
                               id="calories" 
                               name="calories" 
                               class="form-input" 
                               min="0">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="sort_order" class="form-label">Sort Order</label>
                        <input type="number" 
                               id="sort_order" 
                               name="sort_order" 
                               class="form-input" 
                               value="0" 
                               min="0">
                    </div>
                    
                    <div class="form-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="is_featured" value="1">
                            <span class="checkmark"></span>
                            Featured Item
                        </label>
                    </div>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Add Menu Item</button>
                    <button type="button" class="btn btn-secondary" data-modal-close>Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Menu Item Modal -->
<?php if ($editItemId): ?>
    <?php $editItem = getMenuItemById($editItemId); ?>
    <?php if ($editItem): ?>
        <div id="edit-item-modal" class="modal active">
            <div class="modal-content large-modal">
                <div class="modal-header">
                    <h3 class="modal-title">Edit Menu Item</h3>
                    <button type="button" class="modal-close" onclick="window.location.href='admin-menu.php'">&times;</button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        <input type="hidden" name="action" value="edit_item">
                        <input type="hidden" name="item_id" value="<?php echo $editItem['item_id']; ?>">
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="edit_item_name" class="form-label">Item Name *</label>
                                <input type="text" 
                                       id="edit_item_name" 
                                       name="item_name" 
                                       class="form-input" 
                                       value="<?php echo htmlspecialchars($editItem['item_name']); ?>"
                                       required>
                            </div>
                            
                            <div class="form-group">
                                <label for="edit_category_id" class="form-label">Category *</label>
                                <select id="edit_category_id" name="category_id" class="form-select" required>
                                    <option value="">Select Category</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo $category['category_id']; ?>"
                                                <?php echo $editItem['category_id'] == $category['category_id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($category['category_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="edit_description" class="form-label">Description</label>
                            <textarea id="edit_description" 
                                      name="description" 
                                      class="form-input form-textarea" 
                                      rows="3"><?php echo htmlspecialchars($editItem['description']); ?></textarea>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="edit_price" class="form-label">Price (RM) *</label>
                                <input type="number" 
                                       id="edit_price" 
                                       name="price" 
                                       class="form-input" 
                                       step="0.01" 
                                       min="0.01" 
                                       value="<?php echo $editItem['price']; ?>"
                                       required>
                            </div>
                            
                            <div class="form-group">
                                <label for="edit_preparation_time" class="form-label">Preparation Time (mins)</label>
                                <input type="number" 
                                       id="edit_preparation_time" 
                                       name="preparation_time" 
                                       class="form-input" 
                                       value="<?php echo $editItem['preparation_time']; ?>" 
                                       min="1">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="edit_image" class="form-label">Image</label>
                            <?php if ($editItem['image_url']): ?>
                                <div class="current-image">
                                    <img src="<?php echo SITE_URL . UPLOAD_PATH . 'menu/' . $editItem['image_url']; ?>" 
                                         alt="Current image" 
                                         class="preview-image">
                                    <p class="text-slate-500">Current image</p>
                                </div>
                            <?php endif; ?>
                            <input type="file" 
                                   id="edit_image" 
                                   name="image" 
                                   class="form-input" 
                                   accept="image/*">
                            <small class="text-slate-500">Leave empty to keep current image. Max size: 5MB.</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="edit_ingredients" class="form-label">Ingredients</label>
                            <textarea id="edit_ingredients" 
                                      name="ingredients" 
                                      class="form-input form-textarea" 
                                      rows="2"
                                      placeholder="List main ingredients separated by commas"><?php echo htmlspecialchars($editItem['ingredients']); ?></textarea>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="edit_allergens" class="form-label">Allergens</label>
                                <input type="text" 
                                       id="edit_allergens" 
                                       name="allergens" 
                                       class="form-input"
                                       value="<?php echo htmlspecialchars($editItem['allergens']); ?>"
                                       placeholder="e.g., Gluten, Dairy, Nuts">
                            </div>
                            
                            <div class="form-group">
                                <label for="edit_calories" class="form-label">Calories</label>
                                <input type="number" 
                                       id="edit_calories" 
                                       name="calories" 
                                       class="form-input" 
                                       value="<?php echo $editItem['calories']; ?>"
                                       min="0">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="edit_sort_order" class="form-label">Sort Order</label>
                                <input type="number" 
                                       id="edit_sort_order" 
                                       name="sort_order" 
                                       class="form-input" 
                                       value="<?php echo $editItem['sort_order']; ?>" 
                                       min="0">
                            </div>
                            
                            <div class="form-group">
                                <label class="checkbox-label">
                                    <input type="checkbox" 
                                           name="is_featured" 
                                           value="1"
                                           <?php echo $editItem['is_featured'] ? 'checked' : ''; ?>>
                                    <span class="checkmark"></span>
                                    Featured Item
                                </label>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">Update Menu Item</button>
                            <a href="admin-menu.php" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endif; ?>
<?php endif; ?>

<style>
/* Admin Menu Management Styles */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: white;
    padding: 1.5rem;
    border-radius: 0.5rem;
    border: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    gap: 1rem;
}

.stat-icon {
    font-size: 2rem;
    opacity: 0.8;
}

.stat-content {
    flex: 1;
}

.stat-number {
    font-size: 1.5rem;
    font-weight: bold;
    color: #1e293b;
}

.stat-label {
    color: #64748b;
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.action-buttons {
    display: flex;
    gap: 1rem;
    margin-bottom: 2rem;
}

.action-buttons-small {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.item-thumbnail {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: 0.375rem;
}

.item-thumbnail-placeholder {
    width: 60px;
    height: 60px;
    background: #f1f5f9;
    border-radius: 0.375rem;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: #94a3b8;
}

.status-badge {
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    font-size: 0.75rem;
    font-weight: 500;
    text-transform: uppercase;
}

.status-available {
    background: #dcfce7;
    color: #166534;
}

.status-unavailable {
    background: #fee2e2;
    color: #991b1b;
}

.badge {
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    font-size: 0.75rem;
    font-weight: 500;
    text-transform: uppercase;
}

.badge-info {
    background: #dbeafe;
    color: #1e40af;
}

.badge-warning {
    background: #fef3c7;
    color: #92400e;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.large-modal .modal-content {
    max-width: 800px;
}

.current-image {
    margin-bottom: 1rem;
}

.preview-image {
    width: 100px;
    height: 100px;
    object-fit: cover;
    border-radius: 0.375rem;
    border: 1px solid #e2e8f0;
}

.table-responsive {
    overflow-x: auto;
}

.no-data {
    text-align: center;
    padding: 3rem;
    color: #64748b;
}

/* Responsive Design */
@media (max-width: 768px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .action-buttons {
        flex-direction: column;
    }
    
    .action-buttons-small {
        flex-direction: column;
    }
    
    .large-modal .modal-content {
        max-width: 95%;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Image preview functionality
    const imageInputs = document.querySelectorAll('input[type="file"]');
    imageInputs.forEach(input => {
        input.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    // Create preview if it doesn't exist
                    let preview = input.parentNode.querySelector('.image-preview');
                    if (!preview) {
                        preview = document.createElement('div');
                        preview.className = 'image-preview';
                        preview.style.marginTop = '1rem';
                        input.parentNode.appendChild(preview);
                    }
                    
                    preview.innerHTML = `
                        <img src="${e.target.result}" 
                             alt="Preview" 
                             class="preview-image">
                        <p class="text-slate-500">Preview</p>
                    `;
                };
                reader.readAsDataURL(file);
            }
        });
    });
    
    // Form validation
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                FoodOrderingApp.showLoading(submitBtn);
            }
        });
    });
    
    // Auto-hide flash messages
    setTimeout(() => {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            FoodOrderingApp.hideAlert(alert);
        });
    }, 5000);
});
</script>

<?php
// Include footer
include '../includes/footer.php';
?>