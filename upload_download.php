<?php
require_once 'config/config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    $_SESSION['login_required'] = true;
    $_SESSION['redirect_after_login'] = 'upload_download.php';
    header('Location: login.php');
    exit();
}

$user = getUserInfo();
$error_message = '';
$success_message = '';

// Handle file upload (admin only)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_file']) && $user['role'] === 'admin') {
    $title = cleanInput($_POST['title']);
    $description = cleanInput($_POST['description']);
    $category_id = (int)$_POST['category_id'];
    
    // Validation
    if (empty($title)) {
        $error_message = 'Please enter a title for the file.';
    } elseif (empty($category_id)) {
        $error_message = 'Please select a category.';
    } elseif (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        $error_message = 'Please select a file to upload.';
    } else {
        // Check if DOWNLOADS_PATH is defined
        if (!defined('DOWNLOADS_PATH')) {
            $upload_dir = 'uploads/downloads/';
        } else {
            $upload_dir = DOWNLOADS_PATH;
        }
        
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $file_extension = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
        $allowed_extensions = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'zip', 'rar', 'jpg', 'jpeg', 'png', 'gif', 'mp4', 'avi', 'exe', 'msi', 'apk'];
        $max_file_size = 50 * 1024 * 1024; // 50MB
        
        if ($_FILES['file']['size'] > $max_file_size) {
            $error_message = 'File must be smaller than 50MB.';
        } elseif (!in_array(strtolower($file_extension), $allowed_extensions)) {
            $error_message = 'File type not allowed. Allowed types: ' . implode(', ', $allowed_extensions);
        } else {
            // Generate unique filename
            $original_name = pathinfo($_FILES['file']['name'], PATHINFO_FILENAME);
            $safe_filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $original_name);
            $unique_filename = $safe_filename . '_' . time() . '.' . $file_extension;
            $upload_path = $upload_dir . $unique_filename;
            
            if (move_uploaded_file($_FILES['file']['tmp_name'], $upload_path)) {
                // Format file size
                $file_size = formatFileSize($_FILES['file']['size']);
                
                // Handle thumbnail for images
                $thumbnail = null;
                $image_extensions = ['jpg', 'jpeg', 'png', 'gif'];
                if (in_array(strtolower($file_extension), $image_extensions)) {
                    $thumbnail = $unique_filename; // Use the same file as thumbnail for images
                }
                
                try {
                    // Insert into database
                    $stmt = $pdo->prepare("INSERT INTO downloads (category_id, title, description, file_name, file_size, thumbnail) VALUES (?, ?, ?, ?, ?, ?)");
                    $result = $stmt->execute([$category_id, $title, $description, $unique_filename, $file_size, $thumbnail]);
                    
                    if ($result) {
                        $success_message = 'File uploaded successfully!';
                        // Clear form data
                        $_POST = array();
                    } else {
                        $error_message = 'Failed to save file information to database.';
                        unlink($upload_path); // Delete uploaded file
                    }
                } catch (PDOException $e) {
                    $error_message = 'Database error: ' . $e->getMessage();
                    unlink($upload_path); // Delete uploaded file
                }
            } else {
                $error_message = 'Failed to upload file.';
            }
        }
    }
}

// Handle file download
if (isset($_GET['download']) && is_numeric($_GET['download'])) {
    $download_id = (int)$_GET['download'];
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM downloads WHERE id = ?");
        $stmt->execute([$download_id]);
        $download = $stmt->fetch();
        
        if ($download) {
            $file_path = (defined('DOWNLOADS_PATH') ? DOWNLOADS_PATH : 'uploads/downloads/') . $download['file_name'];
            
            if (file_exists($file_path)) {
                // Update download count
                $update_stmt = $pdo->prepare("UPDATE downloads SET download_count = download_count + 1 WHERE id = ?");
                $update_stmt->execute([$download_id]);
                
                // Set headers for download
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename="' . $download['title'] . '.' . pathinfo($download['file_name'], PATHINFO_EXTENSION) . '"');
                header('Content-Length: ' . filesize($file_path));
                header('Cache-Control: no-cache, must-revalidate');
                header('Pragma: no-cache');
                
                // Output file
                readfile($file_path);
                exit();
            } else {
                $error_message = 'File not found.';
            }
        } else {
            $error_message = 'Download not found.';
        }
    } catch (PDOException $e) {
        $error_message = 'Database error: ' . $e->getMessage();
    }
}

// Handle file deletion (admin only)
if (isset($_GET['delete']) && is_numeric($_GET['delete']) && $user['role'] === 'admin') {
    $delete_id = (int)$_GET['delete'];
    
    try {
        $stmt = $pdo->prepare("SELECT file_name FROM downloads WHERE id = ?");
        $stmt->execute([$delete_id]);
        $file = $stmt->fetch();
        
        if ($file) {
            $file_path = (defined('DOWNLOADS_PATH') ? DOWNLOADS_PATH : 'uploads/downloads/') . $file['file_name'];
            
            // Delete from database
            $delete_stmt = $pdo->prepare("DELETE FROM downloads WHERE id = ?");
            $delete_result = $delete_stmt->execute([$delete_id]);
            
            if ($delete_result) {
                // Delete physical file
                if (file_exists($file_path)) {
                    unlink($file_path);
                }
                $success_message = 'File deleted successfully!';
            } else {
                $error_message = 'Failed to delete file from database.';
            }
        } else {
            $error_message = 'File not found.';
        }
    } catch (PDOException $e) {
        $error_message = 'Database error: ' . $e->getMessage();
    }
}

// Get all categories
$categories_stmt = $pdo->query("SELECT * FROM download_categories ORDER BY name ASC");
$categories = $categories_stmt->fetchAll();

// Get downloads by category
$downloads_by_category = array();
foreach ($categories as $category) {
    $stmt = $pdo->prepare("SELECT * FROM downloads WHERE category_id = ? ORDER BY created_at DESC");
    $stmt->execute([$category['id']]);
    $downloads_by_category[$category['id']] = $stmt->fetchAll();
}

// File size formatter function
function formatFileSize($size) {
    $units = array('B', 'KB', 'MB', 'GB');
    $factor = floor((strlen($size) - 1) / 3);
    return sprintf("%.2f", $size / pow(1024, $factor)) . ' ' . $units[$factor];
}

$page_title = "Downloads";
include 'includes/header.php';
?>

<div class="container py-5">
    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Upload Section (Admin Only) -->
            <?php if ($user['role'] === 'admin'): ?>
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-upload me-2"></i>
                        Upload New File
                    </h5>
                </div>
                <div class="card-body">
                    <?php if ($error_message): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <?php echo $error_message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($success_message): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            <?php echo $success_message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="" enctype="multipart/form-data" id="uploadForm">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="title" class="form-label fw-semibold">File Title *</label>
                                <input type="text" class="form-control" id="title" name="title" 
                                       value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>" 
                                       placeholder="Enter file title..." required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="category_id" class="form-label fw-semibold">Category *</label>
                                <select class="form-select" id="category_id" name="category_id" required>
                                    <option value="">Select Category</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo $category['id']; ?>" 
                                                <?php echo (isset($_POST['category_id']) && $_POST['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($category['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label fw-semibold">Description (Optional)</label>
                            <textarea class="form-control" id="description" name="description" rows="3" 
                                      placeholder="Enter file description..."><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="file" class="form-label fw-semibold">Select File *</label>
                            <input type="file" class="form-control" id="file" name="file" required>
                            <div class="form-text">
                                Max file size: 50MB. Allowed types: PDF, DOC, XLS, PPT, Images, Videos, Applications, Archives
                            </div>
                            <div id="fileInfo" class="mt-2" style="display: none;">
                                <small class="text-muted">
                                    <i class="fas fa-file me-1"></i>
                                    <span id="fileName"></span> - <span id="fileSize"></span>
                                </small>
                            </div>
                        </div>
                        
                        <button type="submit" name="upload_file" class="btn btn-primary">
                            <i class="fas fa-upload me-2"></i>Upload File
                        </button>
                    </form>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Downloads Section -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="fas fa-download me-2"></i>
                        Available Downloads
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (empty($categories)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No download categories available.</p>
                        </div>
                    <?php else: ?>
                        <div class="accordion" id="downloadsAccordion">
                            <?php foreach ($categories as $index => $category): ?>
                                <div class="accordion-item border-0 shadow-sm mb-3">
                                    <h2 class="accordion-header" id="heading<?php echo $category['id']; ?>">
                                        <button class="accordion-button <?php echo $index !== 0 ? 'collapsed' : ''; ?>" type="button" 
                                                data-bs-toggle="collapse" data-bs-target="#collapse<?php echo $category['id']; ?>" 
                                                aria-expanded="<?php echo $index === 0 ? 'true' : 'false'; ?>" 
                                                aria-controls="collapse<?php echo $category['id']; ?>">
                                            <i class="<?php echo $category['icon']; ?> me-2"></i>
                                            <?php echo htmlspecialchars($category['name']); ?>
                                            <span class="badge bg-primary ms-2">
                                                <?php echo count($downloads_by_category[$category['id']]); ?>
                                            </span>
                                        </button>
                                    </h2>
                                    <div id="collapse<?php echo $category['id']; ?>" 
                                         class="accordion-collapse collapse <?php echo $index === 0 ? 'show' : ''; ?>" 
                                         aria-labelledby="heading<?php echo $category['id']; ?>" 
                                         data-bs-parent="#downloadsAccordion">
                                        <div class="accordion-body">
                                            <?php if (empty($downloads_by_category[$category['id']])): ?>
                                                <p class="text-muted mb-0">No files available in this category.</p>
                                            <?php else: ?>
                                                <div class="row">
                                                    <?php foreach ($downloads_by_category[$category['id']] as $download): ?>
                                                        <div class="col-md-6 mb-3">
                                                            <div class="card h-100 border-light">
                                                                <div class="card-body p-3">
                                                                    <div class="d-flex align-items-start">
                                                                        <div class="flex-shrink-0 me-3">
                                                                            <?php if ($download['thumbnail']): ?>
                                                                                <img src="<?php echo (defined('DOWNLOADS_PATH') ? DOWNLOADS_PATH : 'uploads/downloads/') . $download['thumbnail']; ?>" 
                                                                                     class="rounded" width="50" height="50" alt="Thumbnail" 
                                                                                     style="object-fit: cover;">
                                                                            <?php else: ?>
                                                                                <div class="bg-light rounded d-flex align-items-center justify-content-center" 
                                                                                     style="width: 50px; height: 50px;">
                                                                                    <i class="fas fa-file text-muted"></i>
                                                                                </div>
                                                                            <?php endif; ?>
                                                                        </div>
                                                                        <div class="flex-grow-1">
                                                                            <h6 class="card-title mb-1">
                                                                                <?php echo htmlspecialchars($download['title']); ?>
                                                                            </h6>
                                                                            <?php if ($download['description']): ?>
                                                                                <p class="card-text small text-muted mb-2">
                                                                                    <?php echo htmlspecialchars(substr($download['description'], 0, 100)); ?>
                                                                                    <?php echo strlen($download['description']) > 100 ? '...' : ''; ?>
                                                                                </p>
                                                                            <?php endif; ?>
                                                                            <div class="d-flex justify-content-between align-items-center">
                                                                                <small class="text-muted">
                                                                                    <i class="fas fa-download me-1"></i>
                                                                                    <?php echo number_format($download['download_count']); ?> downloads
                                                                                </small>
                                                                                <small class="text-muted">
                                                                                    <?php echo $download['file_size']; ?>
                                                                                </small>
                                                                            </div>
                                                                            <div class="mt-2">
                                                                                <a href="?download=<?php echo $download['id']; ?>" 
                                                                                   class="btn btn-sm btn-primary me-1">
                                                                                    <i class="fas fa-download me-1"></i>Download
                                                                                </a>
                                                                                <?php if ($user['role'] === 'admin'): ?>
                                                                                    <a href="?delete=<?php echo $download['id']; ?>" 
                                                                                       class="btn btn-sm btn-outline-danger"
                                                                                       onclick="return confirm('Are you sure you want to delete this file?')">
                                                                                        <i class="fas fa-trash"></i>
                                                                                    </a>
                                                                                <?php endif; ?>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="card-footer bg-light py-2">
                                                                    <small class="text-muted">
                                                                        <i class="fas fa-calendar me-1"></i>
                                                                        Uploaded <?php echo date('M j, Y', strtotime($download['created_at'])); ?>
                                                                    </small>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Statistics -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="fas fa-chart-bar me-2"></i>Download Statistics
                    </h6>
                </div>
                <div class="card-body">
                    <?php
                    // Get statistics
                    $total_files = $pdo->query("SELECT COUNT(*) as count FROM downloads")->fetch()['count'];
                    $total_downloads = $pdo->query("SELECT SUM(download_count) as total FROM downloads")->fetch()['total'] ?? 0;
                    $total_categories = count($categories);
                    
                    // Get most downloaded file
                    $most_downloaded = $pdo->query("SELECT title, download_count FROM downloads ORDER BY download_count DESC LIMIT 1")->fetch();
                    ?>
                    
                    <div class="row text-center">
                        <div class="col-6 mb-3">
                            <div class="p-2">
                                <h4 class="text-primary mb-1"><?php echo number_format($total_files); ?></h4>
                                <small class="text-muted">Total Files</small>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="p-2">
                                <h4 class="text-success mb-1"><?php echo number_format($total_downloads); ?></h4>
                                <small class="text-muted">Total Downloads</small>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="p-2">
                                <h4 class="text-info mb-1"><?php echo $total_categories; ?></h4>
                                <small class="text-muted">Categories</small>
                            </div>
                        </div>
                    </div>
                    
                    <?php if ($most_downloaded): ?>
                        <hr>
                        <div class="text-center">
                            <h6 class="fw-semibold">Most Downloaded</h6>
                            <p class="mb-1"><?php echo htmlspecialchars($most_downloaded['title']); ?></p>
                            <small class="text-muted"><?php echo number_format($most_downloaded['download_count']); ?> downloads</small>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Quick Info -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>Download Guidelines
                    </h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            <small>All files are scanned for security</small>
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            <small>Downloads are tracked for statistics</small>
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            <small>Files are organized by categories</small>
                        </li>
                        <li class="mb-0">
                            <i class="fas fa-check text-success me-2"></i>
                            <small>Regular updates and new content</small>
                        </li>
                    </ul>
                </div>
            </div>
            
            <!-- Categories Overview -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="fas fa-tags me-2"></i>Categories
                    </h6>
                </div>
                <div class="card-body">
                    <?php foreach ($categories as $category): ?>
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <div>
                                <i class="<?php echo $category['icon']; ?> me-2 text-primary"></i>
                                <span class="fw-semibold"><?php echo htmlspecialchars($category['name']); ?></span>
                            </div>
                            <span class="badge bg-light text-dark">
                                <?php echo count($downloads_by_category[$category['id']]); ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.accordion-button:not(.collapsed) {
    background-color: #f8f9fa;
    box-shadow: none;
}

.card-hover:hover {
    transform: translateY(-2px);
    transition: transform 0.2s ease-in-out;
}

.file-icon {
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    background-color: #f8f9fa;
}

.download-card {
    transition: all 0.2s ease-in-out;
}

.download-card:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // File input change handler
    const fileInput = document.getElementById('file');
    const fileInfo = document.getElementById('fileInfo');
    const fileName = document.getElementById('fileName');
    const fileSize = document.getElementById('fileSize');
    
    fileInput?.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            // Show file info
            fileName.textContent = file.name;
            fileSize.textContent = formatFileSize(file.size);
            fileInfo.style.display = 'block';
            
            // Validate file size
            const maxSize = 50 * 1024 * 1024; // 50MB
            if (file.size > maxSize) {
                alert('File size exceeds 50MB limit. Please choose a smaller file.');
                this.value = '';
                fileInfo.style.display = 'none';
                return;
            }
            
            // Auto-fill title if empty
            const titleInput = document.getElementById('title');
            if (!titleInput.value.trim()) {
                const baseName = file.name.replace(/\.[^/.]+$/, ""); // Remove extension
                titleInput.value = baseName;
            }
        } else {
            fileInfo.style.display = 'none';
        }
    });
    
    // Format file size function
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
    
    // Form submission handler
    const uploadForm = document.getElementById('uploadForm');
    uploadForm?.addEventListener('submit', function(e) {
        const submitButton = this.querySelector('button[type="submit"]');
        
        // Disable submit button to prevent double submission
        submitButton.disabled = true;
        submitButton.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Uploading...';
        
        // Re-enable after 30 seconds to prevent permanent lock
        setTimeout(() => {
            submitButton.disabled = false;
            submitButton.innerHTML = '<i class="fas fa-upload me-2"></i>Upload File';
        }, 30000);
    });
    
    // Download button analytics (optional)
    document.querySelectorAll('a[href*="download="]').forEach(link => {
        link.addEventListener('click', function() {
            console.log('Download initiated:', this.href);
            // You can add analytics tracking here
        });
    });
    
    // Auto-hide alerts after 5 seconds
    setTimeout(() => {
        document.querySelectorAll('.alert').forEach(alert => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);
});

// Search functionality (optional enhancement)
function searchDownloads() {
    const searchTerm = document.getElementById('searchInput')?.value.toLowerCase() || '';
    const downloadCards = document.querySelectorAll('.download-card');
    
    downloadCards.forEach(card => {
        const title = card.querySelector('.card-title').textContent.toLowerCase();
        const description = card.querySelector('.card-text')?.textContent.toLowerCase() || '';
        
        if (title.includes(searchTerm) || description.includes(searchTerm)) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
}
</script>

<?php include 'includes/footer.php'; ?>