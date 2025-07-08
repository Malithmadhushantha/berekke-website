<?php
/**
 * Quick Favicon Generator
 * Place this file in your root directory and run it once
 * Visit: http://localhost/berekke_website/create_favicons.php
 */

if (!extension_loaded('gd')) {
    die('Error: GD extension is required. Please enable GD in your PHP configuration.');
}

$source_image = 'assets/images/logo.png';
$output_dir = 'assets/images/';

// Check if source exists
if (!file_exists($source_image)) {
    die("Error: Source logo not found at: $source_image");
}

// Favicon sizes to generate
$favicon_sizes = [
    16 => 'favicon-16x16.png',
    32 => 'favicon-32x32.png',
    96 => 'favicon-96x96.png',
    180 => 'apple-touch-icon.png'
];

echo "<h1>🎨 Quick Favicon Generator</h1>";
echo "<p>Generating favicons from: <strong>$source_image</strong></p>";
echo "<hr>";

$success_count = 0;
$errors = [];

foreach ($favicon_sizes as $size => $filename) {
    $output_file = $output_dir . $filename;
    
    if (file_exists($output_file)) {
        echo "<p style='color: orange;'>⚠️ Skipped: $filename (already exists)</p>";
        continue;
    }
    
    try {
        if (createFavicon($source_image, $output_file, $size)) {
            echo "<p style='color: green;'>✅ Created: $filename ({$size}x{$size})</p>";
            $success_count++;
        } else {
            echo "<p style='color: red;'>❌ Failed: $filename</p>";
            $errors[] = $filename;
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Error creating $filename: " . $e->getMessage() . "</p>";
        $errors[] = $filename;
    }
}

// Create ICO file (copy from 32x32 PNG)
$ico_file = $output_dir . 'favicon.ico';
if (!file_exists($ico_file) && file_exists($output_dir . 'favicon-32x32.png')) {
    if (copy($output_dir . 'favicon-32x32.png', $ico_file)) {
        echo "<p style='color: green;'>✅ Created: favicon.ico</p>";
        $success_count++;
    } else {
        echo "<p style='color: red;'>❌ Failed: favicon.ico</p>";
    }
} elseif (file_exists($ico_file)) {
    echo "<p style='color: orange;'>⚠️ Skipped: favicon.ico (already exists)</p>";
}

// Create basic SVG favicon
$svg_file = $output_dir . 'favicon.svg';
if (!file_exists($svg_file)) {
    $svg_content = '<?xml version="1.0" encoding="UTF-8"?>
<svg width="32" height="32" viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg">
    <rect width="32" height="32" fill="#0056b3" rx="4"/>
    <text x="16" y="22" text-anchor="middle" fill="white" font-family="Arial, sans-serif" font-size="18" font-weight="bold">B</text>
</svg>';
    
    if (file_put_contents($svg_file, $svg_content)) {
        echo "<p style='color: green;'>✅ Created: favicon.svg</p>";
        $success_count++;
    } else {
        echo "<p style='color: red;'>❌ Failed: favicon.svg</p>";
    }
} else {
    echo "<p style='color: orange;'>⚠️ Skipped: favicon.svg (already exists)</p>";
}

echo "<hr>";
echo "<h2>📊 Summary</h2>";
echo "<p><strong>✅ Successfully created:</strong> $success_count files</p>";

if (!empty($errors)) {
    echo "<p><strong>❌ Failed:</strong> " . count($errors) . " files</p>";
    echo "<ul>";
    foreach ($errors as $error) {
        echo "<li style='color: red;'>$error</li>";
    }
    echo "</ul>";
}

echo "<h2>🚀 Next Steps</h2>";
echo "<ol>";
echo "<li><strong>Update your header.php</strong> - Use the simplified favicon code provided</li>";
echo "<li><strong>Clear browser cache:</strong> Ctrl+Shift+R (Windows) or Cmd+Shift+R (Mac)</li>";
echo "<li><strong>Test in incognito window</strong> to bypass cache completely</li>";
echo "<li><strong>Delete this script</strong> for security once done</li>";
echo "</ol>";

echo "<h2>🔧 Favicon HTML Code</h2>";
echo "<pre style='background: #f5f5f5; padding: 10px; border-radius: 5px;'>";
echo htmlspecialchars('<!-- Add this to your header.php -->
<link rel="icon" type="image/x-icon" href="assets/images/favicon.ico">
<link rel="icon" type="image/png" sizes="32x32" href="assets/images/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="assets/images/favicon-16x16.png">
<link rel="apple-touch-icon" sizes="180x180" href="assets/images/apple-touch-icon.png">
<meta name="theme-color" content="#0056b3">');
echo "</pre>";

echo "<p><small>✨ Favicon generation complete! Your favicons should now work across all browsers.</small></p>";

/**
 * Create a favicon of specific size
 */
function createFavicon($source, $destination, $size) {
    // Get source image info
    $image_info = getimagesize($source);
    if (!$image_info) {
        throw new Exception("Cannot read source image: $source");
    }
    
    $original_width = $image_info[0];
    $original_height = $image_info[1];
    $mime_type = $image_info['mime'];
    
    // Create source image resource
    switch ($mime_type) {
        case 'image/jpeg':
            $source_gd = imagecreatefromjpeg($source);
            break;
        case 'image/png':
            $source_gd = imagecreatefrompng($source);
            break;
        case 'image/gif':
            $source_gd = imagecreatefromgif($source);
            break;
        default:
            throw new Exception("Unsupported image type: $mime_type");
    }
    
    if (!$source_gd) {
        throw new Exception("Failed to create image resource from: $source");
    }
    
    // Create destination image
    $dest_gd = imagecreatetruecolor($size, $size);
    
    // Preserve transparency
    imagealphablending($dest_gd, false);
    imagesavealpha($dest_gd, true);
    $transparent = imagecolorallocatealpha($dest_gd, 255, 255, 255, 127);
    imagefill($dest_gd, 0, 0, $transparent);
    
    // Resize image
    $success = imagecopyresampled(
        $dest_gd, $source_gd,
        0, 0, 0, 0,
        $size, $size,
        $original_width, $original_height
    );
    
    if (!$success) {
        imagedestroy($source_gd);
        imagedestroy($dest_gd);
        throw new Exception("Failed to resize image");
    }
    
    // Save image
    $save_success = imagepng($dest_gd, $destination);
    
    // Clean up
    imagedestroy($source_gd);
    imagedestroy($dest_gd);
    
    if (!$save_success) {
        throw new Exception("Failed to save image to: $destination");
    }
    
    return true;
}
?>