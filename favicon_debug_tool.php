<?php
/**
 * Favicon Debug Tool
 * This tool will help you identify and fix favicon issues
 * Run this by visiting http://localhost/berekke_website/debug_favicon.php
 */

// Detect if we're in admin directory
$is_admin = (basename(dirname($_SERVER['PHP_SELF'])) === 'admin');
$base_path = $is_admin ? '../' : '';

// Define favicon paths
$favicon_files = [
    'favicon.ico' => $base_path . 'assets/images/favicon.ico',
    'favicon.svg' => $base_path . 'assets/images/favicon.svg',
    'favicon-96x96.png' => $base_path . 'assets/images/favicon-96x96.png',
    'favicon-32x32.png' => $base_path . 'assets/images/favicon-32x32.png',
    'favicon-16x16.png' => $base_path . 'assets/images/favicon-16x16.png',
    'apple-touch-icon.png' => $base_path . 'assets/images/apple-touch-icon.png',
    'site.webmanifest' => $base_path . 'assets/images/site.webmanifest'
];

echo "<!DOCTYPE html>";
echo "<html><head><title>Favicon Debug Tool</title></head><body>";
echo "<h1>🔍 Favicon Debug Tool</h1>";
echo "<p><strong>Current directory:</strong> " . __DIR__ . "</p>";
echo "<p><strong>Base path:</strong> " . ($base_path ?: 'root') . "</p>";
echo "<hr>";

echo "<h2>📁 File Existence Check</h2>";
echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr><th>File</th><th>Path</th><th>Exists</th><th>Size</th><th>Action</th></tr>";

foreach ($favicon_files as $name => $path) {
    $exists = file_exists($path);
    $size = $exists ? filesize($path) : 'N/A';
    $status = $exists ? '✅ Yes' : '❌ No';
    $action = $exists ? "<a href='$path' target='_blank'>View</a>" : "<span style='color:red;'>Missing</span>";
    
    echo "<tr>";
    echo "<td>$name</td>";
    echo "<td>$path</td>";
    echo "<td>$status</td>";
    echo "<td>" . ($exists ? number_format($size) . ' bytes' : 'N/A') . "</td>";
    echo "<td>$action</td>";
    echo "</tr>";
}
echo "</table>";

echo "<h2>🌐 URL Access Test</h2>";
echo "<p>Testing if favicon URLs are accessible:</p>";
echo "<ul>";

// Test URL access
$site_url = 'http://localhost/berekke_website';
foreach ($favicon_files as $name => $path) {
    $url = $site_url . '/' . $path;
    echo "<li><a href='$url' target='_blank'>$url</a></li>";
}
echo "</ul>";

echo "<h2>🔧 Browser Cache Info</h2>";
echo "<div style='background: #f0f0f0; padding: 10px; border-radius: 5px;'>";
echo "<p><strong>Current timestamp:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "<p><strong>Cache buster:</strong> ?v=" . date('YmdHis') . "</p>";
echo "<p><strong>Clear browser cache:</strong> Ctrl+Shift+R (Windows) or Cmd+Shift+R (Mac)</p>";
echo "</div>";

echo "<h2>📝 Recommended Fix</h2>";
echo "<div style='background: #e8f5e8; padding: 15px; border-radius: 5px;'>";

$missing_files = [];
foreach ($favicon_files as $name => $path) {
    if (!file_exists($path)) {
        $missing_files[] = $name;
    }
}

if (!empty($missing_files)) {
    echo "<h3 style='color: red;'>❌ Missing Files Found!</h3>";
    echo "<p>The following favicon files are missing:</p>";
    echo "<ul>";
    foreach ($missing_files as $file) {
        echo "<li style='color: red;'>$file</li>";
    }
    echo "</ul>";
    echo "<p><strong>Solution:</strong> Run the favicon generator below to create these files.</p>";
} else {
    echo "<h3 style='color: green;'>✅ All Files Present!</h3>";
    echo "<p>All favicon files exist. If you still can't see the favicon:</p>";
    echo "<ol>";
    echo "<li>Clear your browser cache completely</li>";
    echo "<li>Try in an incognito/private window</li>";
    echo "<li>Check the simplified favicon code below</li>";
    echo "</ol>";
}
echo "</div>";

echo "<h2>⚡ Quick Fix Generator</h2>";
echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px;'>";
echo "<p>Click the button below to generate missing favicon files from your logo:</p>";
echo "<form method='post' action=''>";
echo "<button type='submit' name='generate_favicons' style='background: #28a745; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;'>Generate Missing Favicons</button>";
echo "</form>";
echo "</div>";

// Handle favicon generation
if (isset($_POST['generate_favicons'])) {
    echo "<h2>🔄 Generating Favicons...</h2>";
    
    $source_logo = $base_path . 'assets/images/logo.png';
    $output_dir = $base_path . 'assets/images/';
    
    if (!file_exists($source_logo)) {
        echo "<p style='color: red;'>❌ Source logo not found: $source_logo</p>";
    } else {
        // Generate missing favicon sizes
        $sizes_to_generate = [
            16 => 'favicon-16x16.png',
            32 => 'favicon-32x32.png',
            96 => 'favicon-96x96.png',
            180 => 'apple-touch-icon.png'
        ];
        
        foreach ($sizes_to_generate as $size => $filename) {
            $output_path = $output_dir . $filename;
            if (!file_exists($output_path)) {
                if (generateFaviconSize($source_logo, $output_path, $size)) {
                    echo "<p style='color: green;'>✅ Generated: $filename</p>";
                } else {
                    echo "<p style='color: red;'>❌ Failed to generate: $filename</p>";
                }
            } else {
                echo "<p style='color: blue;'>ℹ️ Already exists: $filename</p>";
            }
        }
        
        // Generate ICO file
        $ico_path = $output_dir . 'favicon.ico';
        if (!file_exists($ico_path)) {
            if (copy($output_dir . 'favicon-32x32.png', $ico_path)) {
                echo "<p style='color: green;'>✅ Generated: favicon.ico</p>";
            } else {
                echo "<p style='color: red;'>❌ Failed to generate: favicon.ico</p>";
            }
        }
        
        echo "<p><strong>🎉 Generation complete! Refresh this page to see results.</strong></p>";
    }
}

echo "<h2>💻 Simplified Favicon Code</h2>";
echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px;'>";
echo "<p>Replace your current favicon section with this simplified version:</p>";
echo "<pre style='background: #e9ecef; padding: 10px; border-radius: 3px; overflow-x: auto;'>";
echo htmlspecialchars('<!-- SIMPLIFIED FAVICON (Copy this to your header) -->
<link rel="icon" type="image/x-icon" href="' . $base_path . 'assets/images/favicon.ico">
<link rel="icon" type="image/png" sizes="32x32" href="' . $base_path . 'assets/images/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="' . $base_path . 'assets/images/favicon-16x16.png">
<link rel="apple-touch-icon" sizes="180x180" href="' . $base_path . 'assets/images/apple-touch-icon.png">
<link rel="manifest" href="' . $base_path . 'assets/images/site.webmanifest">');
echo "</pre>";
echo "</div>";

echo "<h2>🧪 Live Test</h2>";
echo "<div style='background: #d1ecf1; padding: 15px; border-radius: 5px;'>";
echo "<p>Test your favicon right now:</p>";
echo "<iframe src='data:text/html,";
$test_html = '<!DOCTYPE html><html><head><title>Favicon Test</title><link rel="icon" type="image/x-icon" href="' . $site_url . '/' . $base_path . 'assets/images/favicon.ico"></head><body><h1>Check browser tab for favicon!</h1><p>If you see a favicon in this tab, it\'s working!</p></body></html>';
echo htmlspecialchars($test_html);
echo "' width='100%' height='100' style='border: 1px solid #ccc;'></iframe>";
echo "</div>";

echo "<hr>";
echo "<p><small>Debug tool completed. Delete this file after fixing the favicon issue.</small></p>";
echo "</body></html>";

/**
 * Generate a favicon of specific size from source image
 */
function generateFaviconSize($source, $destination, $size) {
    if (!extension_loaded('gd')) {
        return false;
    }
    
    $image_info = getimagesize($source);
    if (!$image_info) {
        return false;
    }
    
    $original_width = $image_info[0];
    $original_height = $image_info[1];
    $mime_type = $image_info['mime'];
    
    // Create source image
    switch ($mime_type) {
        case 'image/jpeg':
            $source_image = imagecreatefromjpeg($source);
            break;
        case 'image/png':
            $source_image = imagecreatefrompng($source);
            break;
        default:
            return false;
    }
    
    if (!$source_image) {
        return false;
    }
    
    // Create destination image
    $destination_image = imagecreatetruecolor($size, $size);
    
    // Preserve transparency
    imagealphablending($destination_image, false);
    imagesavealpha($destination_image, true);
    $transparent = imagecolorallocatealpha($destination_image, 255, 255, 255, 127);
    imagefill($destination_image, 0, 0, $transparent);
    
    // Resize image
    imagecopyresampled(
        $destination_image, $source_image,
        0, 0, 0, 0,
        $size, $size,
        $original_width, $original_height
    );
    
    // Save image
    $success = imagepng($destination_image, $destination);
    
    // Clean up
    imagedestroy($source_image);
    imagedestroy($destination_image);
    
    return $success;
}
?>