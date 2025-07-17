<?php
/**
 * One-Time Image Compression Script
 * Save as: compress_existing.php in your project root
 * Run once to compress your current 8 images
 */

require_once 'config.php';
require_once 'functions.php';

// Check if admin
if (!isAdmin()) {
    die('Admin access required');
}

echo "<h2>🗜️ One-Time Image Compression</h2>";
echo "<p>This will compress your existing 8 images and update the database.</p>";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = compressExistingImages();
    
    if ($result['success']) {
        echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
        echo "<h3>✅ Compression Complete!</h3>";
        echo "<p><strong>Images processed:</strong> {$result['processed']}</p>";
        echo "<p><strong>Total space saved:</strong> " . formatBytes($result['savings']) . "</p>";
        echo "<p><strong>Backup location:</strong> assets/images/menu/backup/</p>";
        echo "</div>";
        
        // Show new stats
        $stats = getImageCompressionStats();
        echo "<h3>📊 New Image Statistics:</h3>";
        echo "<ul>";
        echo "<li><strong>Total files:</strong> {$stats['total_files']}</li>";
        echo "<li><strong>Total size:</strong> {$stats['formatted_total']}</li>";
        echo "<li><strong>Average size per image:</strong> {$stats['formatted_average']}</li>";
        echo "</ul>";
        
        echo "<h3>🎉 What happened:</h3>";
        echo "<ul>";
        echo "<li>✅ All images resized to maximum 800px width</li>";
        echo "<li>✅ Compressed to 80% quality (no visible loss)</li>";
        echo "<li>✅ Converted to JPEG format for best compression</li>";
        echo "<li>✅ Database updated with new filenames</li>";
        echo "<li>✅ Original images backed up</li>";
        echo "</ul>";
        
        echo "<p><a href='menu/menu.php'>🔗 Check your menu page</a> to see the results!</p>";
        
    } else {
        echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
        echo "<h3>❌ Error</h3>";
        echo "<p>{$result['message']}</p>";
        echo "</div>";
    }
} else {
    // Show current stats before compression
    $stats = getImageCompressionStats();
    
    echo "<h3>📊 Current Image Statistics:</h3>";
    echo "<ul>";
    echo "<li><strong>Total files:</strong> {$stats['total_files']}</li>";
    echo "<li><strong>Total size:</strong> {$stats['formatted_total']}</li>";
    echo "<li><strong>Average size per image:</strong> {$stats['formatted_average']}</li>";
    echo "</ul>";
    
    if ($stats['total_files'] > 0) {
        $avgSizeKB = $stats['average_size'] / 1024;
        if ($avgSizeKB > 500) {
            echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
            echo "<h4>⚠️ Large Images Detected</h4>";
            echo "<p>Your images are quite large. Compression will significantly reduce loading times!</p>";
            echo "<p><strong>Expected results after compression:</strong></p>";
            echo "<ul>";
            echo "<li>Image size: ~200-400KB each (down from " . formatBytes($stats['average_size']) . ")</li>";
            echo "<li>Total size reduction: ~70-80%</li>";
            echo "<li>Faster page loading</li>";
            echo "<li>Better user experience</li>";
            echo "</ul>";
            echo "</div>";
        }
    }
    
    echo "<form method='POST'>";
    echo "<div style='background: #e7f3ff; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h4>🔧 What this will do:</h4>";
    echo "<ol>";
    echo "<li><strong>Create backups</strong> of all original images</li>";
    echo "<li><strong>Resize</strong> images to max 800px width (perfect for web)</li>";
    echo "<li><strong>Compress</strong> to 80% quality (no visible quality loss)</li>";
    echo "<li><strong>Convert</strong> to JPEG format for best compression</li>";
    echo "<li><strong>Update database</strong> with new filenames</li>";
    echo "<li><strong>Replace</strong> original files with compressed versions</li>";
    echo "</ol>";
    echo "<p><strong>⚠️ Important:</strong> This is safe! Original images will be backed up.</p>";
    echo "</div>";
    
    echo "<button type='submit' style='background: #007cba; color: white; padding: 15px 30px; border: none; border-radius: 5px; font-size: 16px; cursor: pointer;'>";
    echo "🚀 Compress All Images Now";
    echo "</button>";
    echo "</form>";
    
    echo "<p style='color: #666; font-size: 14px; margin-top: 20px;'>";
    echo "<strong>Note:</strong> This process is safe and reversible. If you're not happy with the results, ";
    echo "you can restore the original images from the backup folder.";
    echo "</p>";
}

echo "<hr style='margin: 30px 0;'>";
echo "<h3>🔄 Future Uploads</h3>";
echo "<p>After running this compression, all <strong>new images you upload</strong> through the admin panel will be automatically compressed!</p>";
echo "<p>The compression happens automatically when you add new menu items with images.</p>";
?>