<?php
/**
 * AJAX Handler for Item Details
 * Online Food Ordering System - Phase 2
 * 
 * Returns detailed information about a menu item
 */

require_once '../config.php';
require_once '../functions.php';

// Check if request is AJAX
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
    http_response_code(403);
    exit();
}

// Set JSON response header
header('Content-Type: application/json');

// Check if item ID is provided
if (!isset($_POST['item_id']) || empty($_POST['item_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Item ID is required'
    ]);
    exit();
}

$itemId = (int)$_POST['item_id'];

// Get item details
$item = getMenuItemById($itemId);

if (!$item) {
    echo json_encode([
        'success' => false,
        'message' => 'Item not found'
    ]);
    exit();
}

// Generate HTML for item details
ob_start();
?>
<div class="item-details">
    <div class="item-details-header">
        <?php if ($item['image_url']): ?>
            <div class="item-details-image">
                <img src="<?php echo SITE_URL . UPLOAD_PATH . 'menu/' . $item['image_url']; ?>" 
                     alt="<?php echo htmlspecialchars($item['item_name']); ?>">
                <?php if ($item['is_featured']): ?>
                    <div class="featured-badge">Featured</div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <div class="item-details-info">
            <h2 class="item-details-name"><?php echo htmlspecialchars($item['item_name']); ?></h2>
            <div class="item-details-price"><?php echo formatCurrency($item['price']); ?></div>
            <div class="item-details-category">
                <span class="category-badge"><?php echo htmlspecialchars($item['category_name']); ?></span>
            </div>
        </div>
    </div>
    
    <?php if ($item['description']): ?>
        <div class="item-details-section">
            <h3>Description</h3>
            <p><?php echo htmlspecialchars($item['description']); ?></p>
        </div>
    <?php endif; ?>
    
    <div class="item-details-section">
        <h3>Details</h3>
        <div class="details-grid">
            <div class="detail-item">
                <span class="detail-label">Preparation Time:</span>
                <span class="detail-value">⏱️ <?php echo $item['preparation_time']; ?> minutes</span>
            </div>
            
            <?php if ($item['calories']): ?>
                <div class="detail-item">
                    <span class="detail-label">Calories:</span>
                    <span class="detail-value">🔥 <?php echo $item['calories']; ?> cal</span>
                </div>
            <?php endif; ?>
            
            <div class="detail-item">
                <span class="detail-label">Availability:</span>
                <span class="detail-value availability-<?php echo $item['availability']; ?>">
                    <?php echo $item['availability'] === 'available' ? '✅ Available' : '❌ Currently Unavailable'; ?>
                </span>
            </div>
        </div>
    </div>
    
    <?php if ($item['ingredients']): ?>
        <div class="item-details-section">
            <h3>Ingredients</h3>
            <p class="ingredients-text"><?php echo htmlspecialchars($item['ingredients']); ?></p>
        </div>
    <?php endif; ?>
    
    <?php if ($item['allergens']): ?>
        <div class="item-details-section">
            <h3>Allergen Information</h3>
            <div class="allergen-warning">
                <span class="allergen-icon">⚠️</span>
                <span class="allergen-text">Contains: <?php echo htmlspecialchars($item['allergens']); ?></span>
            </div>
        </div>
    <?php endif; ?>
    
    <div class="item-details-actions">
        <?php if ($item['availability'] === 'available'): ?>
            <?php if (isCustomer()): ?>
                <button class="btn btn-primary btn-large add-to-cart-modal" 
                        data-item-id="<?php echo $item['item_id']; ?>"
                        data-item-name="<?php echo htmlspecialchars($item['item_name']); ?>"
                        data-item-price="<?php echo $item['price']; ?>">
                    Add to Cart - <?php echo formatCurrency($item['price']); ?>
                </button>
            <?php else: ?>
                <a href="<?php echo SITE_URL; ?>auth/login.php" class="btn btn-primary btn-large">
                    Login to Order
                </a>
            <?php endif; ?>
        <?php else: ?>
            <button class="btn btn-secondary btn-large" disabled>
                Currently Unavailable
            </button>
        <?php endif; ?>
    </div>
</div>

<style>
.item-details {
    padding: 0;
}

.item-details-header {
    display: flex;
    gap: 1.5rem;
    margin-bottom: 1.5rem;
}

.item-details-image {
    position: relative;
    width: 200px;
    height: 200px;
    flex-shrink: 0;
}

.item-details-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 0.5rem;
}

.item-details-info {
    flex: 1;
}

.item-details-name {
    font-size: 1.5rem;
    font-weight: bold;
    color: #1e293b;
    margin-bottom: 0.5rem;
}

.item-details-price {
    font-size: 1.75rem;
    font-weight: bold;
    color: #334155;
    margin-bottom: 1rem;
}

.category-badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    background: #f1f5f9;
    color: #334155;
    border-radius: 1rem;
    font-size: 0.875rem;
    font-weight: 500;
}

.item-details-section {
    margin-bottom: 1.5rem;
}

.item-details-section h3 {
    font-size: 1.1rem;
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 0.75rem;
}

.details-grid {
    display: grid;
    gap: 0.75rem;
}

.detail-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem;
    background: #f8fafc;
    border-radius: 0.375rem;
}

.detail-label {
    font-weight: 500;
    color: #475569;
}

.detail-value {
    color: #1e293b;
}

.availability-available {
    color: #059669;
    font-weight: 500;
}

.availability-unavailable {
    color: #dc2626;
    font-weight: 500;
}

.ingredients-text {
    color: #475569;
    line-height: 1.6;
}

.allergen-warning {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem;
    background: #fef3c7;
    border: 1px solid #fbbf24;
    border-radius: 0.375rem;
    color: #92400e;
}

.allergen-icon {
    font-size: 1.2rem;
}

.allergen-text {
    font-weight: 500;
}

.item-details-actions {
    margin-top: 2rem;
    padding-top: 1rem;
    border-top: 1px solid #e2e8f0;
}

@media (max-width: 600px) {
    .item-details-header {
        flex-direction: column;
        align-items: center;
        text-align: center;
    }
    
    .item-details-image {
        width: 150px;
        height: 150px;
    }
    
    .detail-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.25rem;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add to cart functionality from modal
    const addToCartBtn = document.querySelector('.add-to-cart-modal');
    if (addToCartBtn) {
        addToCartBtn.addEventListener('click', function() {
            const itemId = addToCartBtn.getAttribute('data-item-id');
            const itemName = addToCartBtn.getAttribute('data-item-name');
            const itemPrice = addToCartBtn.getAttribute('data-item-price');
            
            // Close modal first
            FoodOrderingApp.closeModal('item-details-modal');
            
            // Show placeholder message (will be implemented in Phase 3)
            FoodOrderingApp.showAlert('Add to cart functionality will be available in Phase 3!', 'info');
        });
    }
});
</script>

<?php
$html = ob_get_clean();

echo json_encode([
    'success' => true,
    'html' => $html
]);
?>