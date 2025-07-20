<?php
/**
 * FIXED Image Compression Script
 * Online Food Ordering System - Handles missing GD extension
 */

require_once 'config.php';
require_once 'functions.php';

// Require admin access
requireAdmin();

$message = '';
$messageType = '';
$stats = [];

// Check if GD extension is available
function checkGDExtension() {
    return extension_loaded('gd') && function_exists('imagecreatefromjpeg');
}

// Get system information
function getSystemInfo() {
    return [
        'php_version' => PHP_VERSION,
        'gd_enabled' => extension_loaded('gd'),
        'gd_functions' => [
            'imagecreatefromjpeg' => function_exists('imagecreatefromjpeg'),
            'imagecreatefrompng' => function_exists('imagecreatefrompng'),
            'imagecreatefromgif' => function_exists('imagecreatefromgif'),
            'imagejpeg' => function_exists('imagejpeg'),
        ]
    ];
}

// Safe image compression function
function safeCompressImage($sourceFile, $destination, $quality = 80, $maxWidth = 800) {
    if (!checkGDExtension()) {
        return false;
    }
    
    try {
        $imageInfo = getimagesize($sourceFile);
        if (!$imageInfo) {
            return false;
        }
        
        $sourceImage = null;
        switch ($imageInfo['mime']) {
            case 'image/jpeg':
                $sourceImage = imagecreatefromjpeg($sourceFile);
                break;
            case 'image/png':
                $sourceImage = imagecreatefrompng($sourceFile);
                break;
            case 'image/gif':
                $sourceImage = imagecreatefromgif($sourceFile);
                break;
            default:
                return false;
        }
        
        if (!$sourceImage) {
            return false;
        }
        
        $originalWidth = imagesx($sourceImage);
        $originalHeight = imagesy($sourceImage);
        
        if ($originalWidth > $maxWidth) {
            $newWidth = $maxWidth;
            $newHeight = intval(($originalHeight * $maxWidth) / $originalWidth);
        } else {
            $newWidth = $originalWidth;
            $newHeight = $originalHeight;
        }
        
        $newImage = imagecreatetruecolor($newWidth, $newHeight);
        
        // Handle transparency for PNG/GIF
        imagealphablending($newImage, false);
        imagesavealpha($newImage, true);
        $transparent = imagecolorallocatealpha($newImage, 255, 255, 255, 127);
        imagefilledrectangle($newImage, 0, 0, $newWidth, $newHeight, $transparent);
        
        imagecopyresampled(
            $newImage, $sourceImage,
            0, 0, 0, 0,
            $newWidth, $newHeight,
            $originalWidth, $originalHeight
        );
        
        $result = imagejpeg($newImage, $destination, $quality);
        
        imagedestroy($sourceImage);
        imagedestroy($newImage);
        
        return $result;
        
    } catch (Exception $e) {
        return false;
    }
}

// Handle compression request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['compress'])) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $message = 'Invalid request token.';
        $messageType = 'error';
    } elseif (!checkGDExtension()) {
        $message = 'GD extension is not enabled. Please enable it in your PHP configuration.';
        $messageType = 'error';
    } else {
        $menuDir = 'assets/images/menu/';
        $backupDir = 'assets/images/menu/backup/';
        
        if (!is_dir($menuDir)) {
            $message = 'Menu images directory not found.';
            $messageType = 'error';
        } else {
            // Create backup directory
            if (!is_dir($backupDir)) {
                mkdir($backupDir, 0755, true);
            }
            
            $files = glob($menuDir . '*.{jpg,jpeg,png,gif}', GLOB_BRACE);
            $processed = 0;
            $totalSavings = 0;
            $errors = 0;
            
            foreach ($files as $file) {
                $filename = basename($file);
                $originalSize = filesize($file);
                
                // Skip already compressed files
                if (strpos($filename, 'menu_') === 0 && strpos($filename, '.jpg') !== false) {
                    continue;
                }
                
                // Backup original
                $backupPath = $backupDir . $filename;
                if (!copy($file, $backupPath)) {
                    $errors++;
                    continue;
                }
                
                // Compress image
                $newFilename = 'menu_' . time() . '_' . uniqid() . '.jpg';
                $newPath = $menuDir . $newFilename;
                
                if (safeCompressImage($file, $newPath, 80, 800)) {
                    $compressedSize = filesize($newPath);
                    $savings = $originalSize - $compressedSize;
                    $totalSavings += $savings;
                    
                    // Update database
                    $stmt = $conn->prepare("UPDATE menu_items SET image_url = ? WHERE image_url = ?");
                    if ($stmt) {
                        $stmt->bind_param("ss", $newFilename, $filename);
                        $stmt->execute();
                    }
                    
                    // Remove original file
                    unlink($file);
                    $processed++;
                } else {
                    $errors++;
                    // Remove backup if compression failed
                    unlink($backupPath);
                }
            }
            
            if ($processed > 0) {
                $message = "Successfully compressed {$processed} images. Total space saved: " . formatBytes($totalSavings);
                if ($errors > 0) {
                    $message .= " ({$errors} files failed to compress)";
                }
                $messageType = 'success';
            } elseif ($errors > 0) {
                $message = "Failed to compress {$errors} images. Check if files are valid image formats.";
                $messageType = 'error';
            } else {
                $message = 'No images found to compress or all images are already optimized.';
                $messageType = 'info';
            }
        }
    }
}

// Get current statistics
$stats = getImageCompressionStats();
$systemInfo = getSystemInfo();

$pageTitle = 'Image Compression';
include 'includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h1 class="page-title">📦 One-Time Image Compression</h1>
        <p class="page-subtitle">Optimize your existing menu images for better performance</p>
    </div>
    
    <?php if ($message): ?>
        <div class="alert alert-<?php echo $messageType; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>
    
    <!-- System Status -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">System Status</h3>
        </div>
        <div class="card-body">
            <div class="status-grid">
                <div class="status-item">
                    <span class="status-label">PHP Version:</span>
                    <span class="status-value"><?php echo $systemInfo['php_version']; ?></span>
                </div>
                <div class="status-item">
                    <span class="status-label">GD Extension:</span>
                    <span class="status-value <?php echo $systemInfo['gd_enabled'] ? 'status-ok' : 'status-error'; ?>">
                        <?php echo $systemInfo['gd_enabled'] ? '✅ Enabled' : '❌ Disabled'; ?>
                    </span>
                </div>
                <div class="status-item">
                    <span class="status-label">Image Functions:</span>
                    <span class="status-value <?php echo checkGDExtension() ? 'status-ok' : 'status-error'; ?>">
                        <?php echo checkGDExtension() ? '✅ Available' : '❌ Missing'; ?>
                    </span>
                </div>
            </div>
            
            <?php if (!checkGDExtension()): ?>
                <div class="alert alert-warning">
                    <h4>⚠️ GD Extension Required</h4>
                    <p>To use image compression, you need to enable the GD extension in PHP:</p>
                    <ol>
                        <li>Open your <code>php.ini</code> file</li>
                        <li>Find the line <code>;extension=gd</code></li>
                        <li>Remove the semicolon: <code>extension=gd</code></li>
                        <li>Restart your web server (Apache/Nginx)</li>
                    </ol>
                    <p><strong>XAMPP users:</strong> The php.ini file is usually at <code>C:\xampp\php\php.ini</code></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Current Statistics -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Current Image Statistics</h3>
        </div>
        <div class="card-body">
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-number"><?php echo $stats['total_files']; ?></div>
                    <div class="stat-label">Total Images</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number"><?php echo $stats['formatted_total']; ?></div>
                    <div class="stat-label">Total Size</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number"><?php echo $stats['formatted_average']; ?></div>
                    <div class="stat-label">Average Size</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Compression Action -->
    <?php if (checkGDExtension()): ?>
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Compress Images</h3>
            </div>
            <div class="card-body">
                <p>This will compress your existing menu images and update the database. Original images will be backed up.</p>
                
                <div class="compression-settings">
                    <h4>Compression Settings:</h4>
                    <ul>
                        <li>Quality: 80% (good balance of size and quality)</li>
                        <li>Max Width: 800px (mobile-friendly)</li>
                        <li>Format: JPEG (best compression)</li>
                        <li>Backup: Original images saved to backup folder</li>
                    </ul>
                </div>
                
                <form method="POST" style="margin-top: 2rem;">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <button type="submit" 
                            name="compress" 
                            class="btn btn-primary btn-large"
                            data-confirm="This will compress all menu images and cannot be easily undone. Continue?">
                        🗜️ Start Compression
                    </button>
                </form>
            </div>
        </div>
    <?php endif; ?>
    
    <!-- Help Section -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">💡 Help & Information</h3>
        </div>
        <div class="card-body">
            <div class="help-grid">
                <div class="help-item">
                    <h4>What does this do?</h4>
                    <p>Compresses existing menu images to reduce file sizes and improve page loading speed.</p>
                </div>
                <div class="help-item">
                    <h4>Is it safe?</h4>
                    <p>Yes! Original images are backed up before compression. You can restore them if needed.</p>
                </div>
                <div class="help-item">
                    <h4>How much space will I save?</h4>
                    <p>Typically 60-80% reduction in file size with minimal quality loss.</p>
                </div>
                <div class="help-item">
                    <h4>What if something goes wrong?</h4>
                    <p>Original images are saved in the backup folder and can be restored manually.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.status-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
}

.status-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem;
    background: #f8fafc;
    border-radius: 0.375rem;
}

.status-label {
    font-weight: 500;
    color: #475569;
}

.status-ok {
    color: #059669;
    font-weight: 500;
}

.status-error {
    color: #dc2626;
    font-weight: 500;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 1rem;
}

.stat-item {
    text-align: center;
    padding: 1.5rem;
    background: #f8fafc;
    border-radius: 0.375rem;
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
    margin-top: 0.25rem;
}

.compression-settings {
    background: #f0f9ff;
    padding: 1rem;
    border-radius: 0.375rem;
    border: 1px solid #bae6fd;
}

.compression-settings h4 {
    margin-bottom: 0.5rem;
    color: #0c4a6e;
}

.compression-settings ul {
    margin: 0;
    padding-left: 1.5rem;
    color: #0c4a6e;
}

.help-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1rem;
}

.help-item {
    padding: 1rem;
    background: #f8fafc;
    border-radius: 0.375rem;
}

.help-item h4 {
    margin-bottom: 0.5rem;
    color: #1e293b;
}

.help-item p {
    margin: 0;
    color: #64748b;
    font-size: 0.9rem;
}

@media (max-width: 768px) {
    .status-grid,
    .stats-grid,
    .help-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<?php include 'includes/footer.php'; ?>