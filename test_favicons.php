<?php
// Simple favicon test page
$base_path = '';
$favicon_files = [
    'favicon.ico' => 'Standard Favicon',
    'favicon-16x16.png' => '16x16 PNG Favicon',
    'favicon-32x32.png' => '32x32 PNG Favicon',
    'favicon-96x96.png' => '96x96 PNG Favicon',
    'apple-touch-icon.png' => 'Apple Touch Icon (180x180)',
    'android-chrome-192x192.png' => 'Android Chrome (192x192)',
    'android-chrome-512x512.png' => 'Android Chrome (512x512)',
    'safari-pinned-tab.svg' => 'Safari Pinned Tab',
    'site.webmanifest' => 'Web App Manifest',
    'browserconfig.xml' => 'Browser Config'
];

$page_title = "Favicon Test";
include 'includes/header.php';
?>

<div class="container py-5">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">Favicon Test Page</h1>
            
            <div class="alert alert-info">
                <h5><i class="fas fa-info-circle me-2"></i>Testing Instructions</h5>
                <ol>
                    <li><strong>Check the browser tab</strong> - You should see the Berekke favicon</li>
                    <li><strong>Bookmark this page</strong> - The favicon should appear in bookmarks</li>
                    <li><strong>Add to home screen</strong> (mobile) - The app icon should appear</li>
                    <li><strong>Test different browsers</strong> - Chrome, Firefox, Safari, Edge</li>
                </ol>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h5>Favicon File Status Check</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php foreach ($favicon_files as $file => $description): ?>
                        <div class="col-md-6 mb-3">
                            <div class="d-flex align-items-center">
                                <?php 
                                $file_path = "assets/images/" . $file;
                                $exists = file_exists($file_path);
                                $status_class = $exists ? 'text-success' : 'text-danger';
                                $status_icon = $exists ? 'fa-check-circle' : 'fa-times-circle';
                                ?>
                                <i class="fas <?php echo $status_icon; ?> <?php echo $status_class; ?> me-2"></i>
                                <div>
                                    <strong><?php echo $description; ?></strong><br>
                                    <small class="text-muted"><?php echo $file; ?></small>
                                    <?php if ($exists): ?>
                                    <br><small class="text-success">✓ File exists</small>
                                    <?php else: ?>
                                    <br><small class="text-danger">✗ File missing</small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
            <div class="card mt-4">
                <div class="card-header">
                    <h5>Quick Tests</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Favicon URLs to Test Directly:</h6>
                            <ul class="list-unstyled">
                                <li>
                                    <a href="assets/images/favicon.ico" target="_blank" class="text-decoration-none">
                                        <i class="fas fa-external-link-alt me-1"></i>favicon.ico
                                    </a>
                                </li>
                                <li>
                                    <a href="assets/images/apple-touch-icon.png" target="_blank" class="text-decoration-none">
                                        <i class="fas fa-external-link-alt me-1"></i>apple-touch-icon.png
                                    </a>
                                </li>
                                <li>
                                    <a href="assets/images/site.webmanifest" target="_blank" class="text-decoration-none">
                                        <i class="fas fa-external-link-alt me-1"></i>site.webmanifest
                                    </a>
                                </li>
                                <li>
                                    <a href="assets/images/browserconfig.xml" target="_blank" class="text-decoration-none">
                                        <i class="fas fa-external-link-alt me-1"></i>browserconfig.xml
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6>Cache Clearing Instructions:</h6>
                            <ul class="small">
                                <li><strong>Chrome:</strong> Ctrl+Shift+R (hard refresh)</li>
                                <li><strong>Firefox:</strong> Ctrl+F5</li>
                                <li><strong>Safari:</strong> Cmd+Option+R</li>
                                <li><strong>Edge:</strong> Ctrl+Shift+R</li>
                            </ul>
                            <div class="mt-3">
                                <button class="btn btn-outline-primary btn-sm" onclick="location.reload(true)">
                                    <i class="fas fa-sync me-1"></i>Hard Refresh Page
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="alert alert-warning mt-4">
                <h6><i class="fas fa-exclamation-triangle me-2"></i>Troubleshooting</h6>
                <p class="mb-2">If favicons are not showing:</p>
                <ol class="mb-0">
                    <li>Ensure all files exist in <code>assets/images/</code> folder</li>
                    <li>Clear browser cache completely (not just refresh)</li>
                    <li>Test in incognito/private browsing mode</li>
                    <li>Check browser developer tools for 404 errors</li>
                    <li>Verify file permissions (files should be readable)</li>
                </ol>
            </div>
            
            <div class="text-center mt-4">
                <a href="index.php" class="btn btn-primary">
                    <i class="fas fa-home me-2"></i>Back to Home
                </a>
                <button class="btn btn-danger ms-2" onclick="deleteFaviconTest()">
                    <i class="fas fa-trash me-2"></i>Delete This Test File
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function deleteFaviconTest() {
    if (confirm('Are you sure you want to delete this test file? This action cannot be undone.')) {
        // Note: This would require server-side script to actually delete
        alert('Please manually delete test_favicons.php from your server for security.');
    }
}

// Check if page is bookmarked (for testing)
document.addEventListener('DOMContentLoaded', function() {
    console.log('Favicon Test Page Loaded');
    console.log('Check browser tab for favicon');
    console.log('Check browser bookmarks after bookmarking this page');
});
</script>

<?php include 'includes/footer.php'; ?>