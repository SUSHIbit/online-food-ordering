<?php
/**
 * Image Debug Script - Save as debug_specific_image.php
 * This will help us find exactly what's wrong with the SKIBIDI image
 */

require_once 'config.php';
require_once 'functions.php';

// Check if admin
if (!isAdmin()) {
    die('Admin access required');
}

echo "<h1>Image Debug Report for SKIBIDI</h1>";

// 1. Check the database record for SKIBIDI
echo "<h2>1. Database Record for SKIBIDI</h2>";
$stmt = $conn->prepare("SELECT * FROM menu_items WHERE item_name LIKE '%skibidi%'");
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $item = $result->fetch_assoc();
    echo "<p><strong>Item ID:</strong> " . $item['item_id'] . "</p>";
    echo "<p><strong>Item Name:</strong> " . htmlspecialchars($item['item_name']) . "</p>";
    echo "<p><strong>Image URL in Database:</strong> " . ($item['image_url'] ? htmlspecialchars($item['image_url']) : 'NULL/Empty') . "</p>";
    
    if ($item['image_url']) {
        // 2. Check if the file exists
        echo "<h2>2. File System Check</h2>";
        $imagePath = 'assets/images/menu/' . $item['image_url'];
        echo "<p><strong>Expected Path:</strong> " . $imagePath . "</p>";
        echo "<p><strong>File Exists:</strong> " . (file_exists($imagePath) ? '✅ YES' : '❌ NO') . "</p>";
        
        if (file_exists($imagePath)) {
            echo "<p><strong>File Size:</strong> " . number_format(filesize($imagePath)) . " bytes</p>";
            echo "<p><strong>File Type:</strong> " . mime_content_type($imagePath) . "</p>";
            echo "<p><strong>Real Path:</strong> " . realpath($imagePath) . "</p>";
            
            // 3. Test the URL
            echo "<h2>3. URL Test</h2>";
            $fullUrl = SITE_URL . $imagePath;
            echo "<p><strong>Full URL:</strong> <a href='$fullUrl' target='_blank'>$fullUrl</a></p>";
            
            // 4. Display the image
            echo "<h2>4. Image Display Test</h2>";
            echo "<img src='$fullUrl' style='max-width: 200px; max-height: 200px; border: 1px solid #ccc;' alt='SKIBIDI image'>";
            
        } else {
            echo "<p style='color: red;'>❌ File does not exist at expected location!</p>";
            
            // Check if it exists elsewhere
            echo "<h2>Alternative Locations Check</h2>";
            $alternativePaths = [
                'menu/assets/images/menu/' . $item['image_url'],
                '../assets/images/menu/' . $item['image_url'],
                'images/menu/' . $item['image_url'],
                'uploads/' . $item['image_url']
            ];
            
            foreach ($alternativePaths as $altPath) {
                echo "<p><strong>$altPath:</strong> " . (file_exists($altPath) ? '✅ FOUND HERE!' : '❌ No') . "</p>";
            }
        }
    } else {
        echo "<p style='color: orange;'>⚠️ No image URL stored in database</p>";
    }
} else {
    echo "<p style='color: red;'>❌ No SKIBIDI item found in database</p>";
}

// 5. Check what files are actually in the menu directory
echo "<h2>5. Files in Menu Directory</h2>";
$menuDir = 'assets/images/menu/';
if (is_dir($menuDir)) {
    $files = scandir($menuDir);
    $imageFiles = array_filter($files, function($file) {
        return !in_array($file, ['.', '..']) && preg_match('/\.(jpg|jpeg|png|gif)$/i', $file);
    });
    
    if (empty($imageFiles)) {
        echo "<p>No image files found in menu directory</p>";
    } else {
        echo "<ul>";
        foreach ($imageFiles as $file) {
            $filePath = $menuDir . $file;
            echo "<li><strong>$file</strong> - " . number_format(filesize($filePath)) . " bytes</li>";
        }
        echo "</ul>";
    }
} else {
    echo "<p style='color: red;'>Menu directory does not exist!</p>";
}

// 6. Test upload form
echo "<h2>6. Quick Re-upload Test</h2>";
echo "<form method='post' enctype='multipart/form-data'>";
echo "<p>Re-upload image for SKIBIDI: <input type='file' name='skibidi_image' accept='image/*'></p>";
echo "<p><input type='submit' name='reupload' value='Re-upload for SKIBIDI'></p>";
echo "</form>";

if (isset($_POST['reupload']) && isset($_FILES['skibidi_image'])) {
    echo "<h3>Re-upload Result:</h3>";
    
    $file = $_FILES['skibidi_image'];
    if ($file['error'] === UPLOAD_ERR_OK) {
        $newFilename = uploadMenuImage($file);
        if ($newFilename) {
            // Update database
            $stmt = $conn->prepare("UPDATE menu_items SET image_url = ? WHERE item_name LIKE '%skibidi%'");
            $stmt->bind_param("s", $newFilename);
            
            if ($stmt->execute()) {
                echo "<p style='color: green;'>✅ Successfully uploaded and updated database!</p>";
                echo "<p>New filename: $newFilename</p>";
                echo "<p>View at: <a href='" . SITE_URL . "assets/images/menu/$newFilename' target='_blank'>" . SITE_URL . "assets/images/menu/$newFilename</a></p>";
                echo "<img src='" . SITE_URL . "assets/images/menu/$newFilename' style='max-width: 200px; max-height: 200px; border: 1px solid #ccc;'>";
            } else {
                echo "<p style='color: red;'>❌ Upload successful but database update failed</p>";
            }
        } else {
            echo "<p style='color: red;'>❌ Upload failed</p>";
        }
    }
}

// 7. Fix button
echo "<h2>7. Quick Fix Options</h2>";
echo "<a href='admin/admin-menu.php?edit_item=" . ($item['item_id'] ?? '') . "' style='background: #007cba; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px;'>Edit SKIBIDI Item</a> ";
echo "<a href='menu/menu.php' style='background: #28a745; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px;'>View Menu</a>";
?>