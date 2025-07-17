<?php
/**
 * Image Diagnostic Script
 * Save this as: debug_images.php in your root directory
 * Run it to check image upload issues
 */

require_once 'config.php';
require_once 'functions.php';

// Check if admin
if (!isAdmin()) {
    die('Admin access required');
}

echo "<h1>Image Diagnostic Report</h1>";

// 1. Check directory structure
echo "<h2>1. Directory Structure</h2>";
$directories = [
    'assets/',
    'assets/images/',
    'assets/images/menu/'
];

foreach ($directories as $dir) {
    $exists = is_dir($dir);
    $writable = $exists ? is_writable($dir) : false;
    $permissions = $exists ? substr(sprintf('%o', fileperms($dir)), -4) : 'N/A';
    
    echo "<p><strong>$dir</strong><br>";
    echo "Exists: " . ($exists ? '✅ Yes' : '❌ No') . "<br>";
    echo "Writable: " . ($writable ? '✅ Yes' : '❌ No') . "<br>";
    echo "Permissions: $permissions</p>";
    
    // Try to create if doesn't exist
    if (!$exists) {
        if (mkdir($dir, 0755, true)) {
            echo "<p style='color: green;'>✅ Created directory: $dir</p>";
        } else {
            echo "<p style='color: red;'>❌ Failed to create directory: $dir</p>";
        }
    }
}

// 2. Check database images
echo "<h2>2. Database Image Records</h2>";
$menuItems = getAllMenuItems(false); // Include unavailable items

if (empty($menuItems)) {
    echo "<p>No menu items found in database.</p>";
} else {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Item Name</th><th>Image URL</th><th>File Exists</th><th>Full Path</th><th>Status</th></tr>";
    
    foreach ($menuItems as $item) {
        $imageName = $item['image_url'];
        $imagePath = 'assets/images/menu/' . $imageName;
        $fileExists = !empty($imageName) && file_exists($imagePath);
        $fullPath = !empty($imageName) ? realpath($imagePath) : 'N/A';
        
        echo "<tr>";
        echo "<td>" . htmlspecialchars($item['item_name']) . "</td>";
        echo "<td>" . htmlspecialchars($imageName ?: 'No image') . "</td>";
        echo "<td>" . ($fileExists ? '✅ Yes' : '❌ No') . "</td>";
        echo "<td>" . htmlspecialchars($fullPath ?: 'N/A') . "</td>";
        
        if (empty($imageName)) {
            echo "<td style='color: orange;'>No image set</td>";
        } elseif ($fileExists) {
            echo "<td style='color: green;'>✅ OK</td>";
        } else {
            echo "<td style='color: red;'>❌ Missing file</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
}

// 3. Check upload configuration
echo "<h2>3. PHP Upload Configuration</h2>";
echo "<p><strong>file_uploads:</strong> " . (ini_get('file_uploads') ? '✅ Enabled' : '❌ Disabled') . "</p>";
echo "<p><strong>upload_max_filesize:</strong> " . ini_get('upload_max_filesize') . "</p>";
echo "<p><strong>post_max_size:</strong> " . ini_get('post_max_size') . "</p>";
echo "<p><strong>max_file_uploads:</strong> " . ini_get('max_file_uploads') . "</p>";
echo "<p><strong>upload_tmp_dir:</strong> " . (ini_get('upload_tmp_dir') ?: 'Default') . "</p>";

// 4. List actual files in menu directory
echo "<h2>4. Actual Files in Menu Directory</h2>";
$menuDir = 'assets/images/menu/';
if (is_dir($menuDir)) {
    $files = scandir($menuDir);
    $files = array_filter($files, function($file) {
        return !in_array($file, ['.', '..']);
    });
    
    if (empty($files)) {
        echo "<p>No files found in menu directory.</p>";
    } else {
        echo "<ul>";
        foreach ($files as $file) {
            $filePath = $menuDir . $file;
            $fileSize = filesize($filePath);
            $fileType = mime_content_type($filePath);
            echo "<li><strong>$file</strong> - Size: " . number_format($fileSize) . " bytes - Type: $fileType</li>";
        }
        echo "</ul>";
    }
} else {
    echo "<p style='color: red;'>Menu directory does not exist!</p>";
}

// 5. Test image URL generation
echo "<h2>5. Test Image URL Generation</h2>";
echo "<p><strong>SITE_URL:</strong> " . SITE_URL . "</p>";
echo "<p><strong>Sample image URL:</strong> " . SITE_URL . "assets/images/menu/sample.jpg</p>";

// 6. Recommendations
echo "<h2>6. Recommendations</h2>";
echo "<ol>";
echo "<li>Make sure the 'assets/images/menu/' directory exists and is writable (permissions 755 or 775)</li>";
echo "<li>Check that your SITE_URL in config.php is correct</li>";
echo "<li>Verify that uploaded images are actually being saved to the correct directory</li>";
echo "<li>If images exist but don't display, check the web server configuration for serving static files</li>";
echo "</ol>";

echo "<h2>7. Quick Test Upload</h2>";
echo "<form method='post' enctype='multipart/form-data'>";
echo "<p>Test upload an image: <input type='file' name='test_image' accept='image/*'></p>";
echo "<p><input type='submit' name='test_upload' value='Test Upload'></p>";
echo "</form>";

if (isset($_POST['test_upload']) && isset($_FILES['test_image'])) {
    echo "<h3>Upload Test Result:</h3>";
    
    $file = $_FILES['test_image'];
    echo "<p><strong>Original filename:</strong> " . htmlspecialchars($file['name']) . "</p>";
    echo "<p><strong>File size:</strong> " . number_format($file['size']) . " bytes</p>";
    echo "<p><strong>File type:</strong> " . htmlspecialchars($file['type']) . "</p>";
    echo "<p><strong>Upload error:</strong> " . $file['error'] . "</p>";
    
    if ($file['error'] === UPLOAD_ERR_OK) {
        $result = uploadMenuImage($file);
        if ($result) {
            echo "<p style='color: green;'>✅ Upload successful! Filename: $result</p>";
            echo "<p>Image should be accessible at: " . SITE_URL . "assets/images/menu/$result</p>";
            echo "<img src='" . SITE_URL . "assets/images/menu/$result' style='max-width: 200px; max-height: 200px;' alt='Test upload'>";
        } else {
            echo "<p style='color: red;'>❌ Upload failed!</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ Upload error: " . $file['error'] . "</p>";
    }
}
?>