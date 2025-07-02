<?php
require_once '../config/config.php';

// Require admin access
requireAdmin();

$error_message = '';
$success_message = '';

// Fetch categories for dropdown
$categories = $pdo->query("SELECT * FROM download_categories ORDER BY name")->fetchAll();

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_file'])) {
    $title = cleanInput($_POST['title']);
    $description = cleanInput($_POST['description']);
    $category_id = intval($_POST['category_id']);
    
    // Validation
    if (empty($title) || empty($description) || empty($category_id)) {
        $error_message = 'Please fill in all required fields.';
    } elseif (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        $error_message = 'Please select a file to upload.';
    } else {
        $file = $_FILES['file'];
        $file_size = $file['size'];
        $file_tmp = $file['tmp_name'];
        $file_name = $file['name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        // Allowed file extensions
        $allowed_extensions = [
            'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx',
            'txt', 'rtf', 'zip', 'rar', '7z',
            'jpg', 'jpeg', 'png', 'gif',
            'mp4', 'avi', 'mov', 'wmv'
        ];
        
        // File size limit (50MB)
        $max_file_size = 50 * 1024 * 1024;
        
        if (!in_array($file_ext, $allowed_extensions)) {
            $error_message = 'File type not allowed. Allowed types: ' . implode(', ', $allowed_extensions);
        } elseif ($file_size > $max_file_size) {
            $error_message = 'File size too large. Maximum size is 50MB.';
        } else {
            // Create upload directory if it doesn't exist
            $upload_dir = '../' . DOWNLOADS_PATH;
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            // Generate unique filename
            $unique_filename = time() . '_' . uniqid() . '.' . $file_ext;
            $upload_path = $upload_dir . $unique_filename;
            
            // Handle thumbnail upload
            $thumbnail_filename = '';
            if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
                $thumb_file = $_FILES['thumbnail'];
                $thumb_ext = strtolower(pathinfo($thumb_file['name'], PATHINFO_EXTENSION));
                $allowed_thumb_ext = ['jpg', 'jpeg', 'png', 'gif'];
                
                if (in_array($thumb_ext, $allowed_thumb_ext)) {
                    $thumbnail_dir = $upload_dir . 'thumbnails/';
                    if (!is_dir($thumbnail_dir)) {
                        mkdir($thumbnail_dir, 0755, true);
                    }
                    
                    $thumbnail_filename = 'thumb_' . time() . '_' . uniqid() . '.' . $thumb_ext;
                    $thumbnail_path = $thumbnail_dir . $thumbnail_filename;
                    
                    if (!move_uploaded_file($thumb_file['tmp_name'], $thumbnail_path)) {
                        $thumbnail_filename = '';
                    }
                }
            }
            
            // Upload main file
            if (move_uploaded_file($file_tmp, $upload_path)) {
                // Format file size
                $formatted_size = formatBytes($file_size);
                
                // Insert into database
                try {
                    $stmt = $pdo->prepare("INSERT INTO downloads (category_id, title, description, file_name, file_size, thumbnail) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([
                        $category_id,
                        $title,
                        $description,
                        $unique_filename,
                        $formatted_size,
                        $thumbnail_filename
                    ]);
                    
                    $success_message = 'File uploaded successfully!';
                    
                    // Clear form data
                    $_POST = [];
                    
                } catch (PDOException $e) {
                    $error_message = 'Database error: Failed to save file information.';
                    // Delete uploaded file if database insert fails
                    unlink($upload_path);
                    if ($thumbnail_filename && file_exists($thumbnail_path)) {
                        unlink($thumbnail_path);
                    }
                }
            } else {
                $error_message = 'Failed to upload file. Please try again.';
            }
        }
    }
}

// Handle category creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_category'])) {
    $cat_name = cleanInput($_POST['category_name']);
    $cat_description = cleanInput($_POST['category_description']);
    $cat_icon = cleanInput($_POST['category_icon']);
    
    if (empty($cat_name)) {
        $error_message = 'Category name is required.';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO download_categories (name, description, icon) VALUES (?, ?, ?)");
            $stmt->execute([$cat_name, $cat_description, $cat_icon]);
            
            $success_message = 'Category created successfully!';
            
            // Refresh categories list
            $categories = $pdo->query("SELECT * FROM download_categories ORDER BY name")->fetchAll();
            
        } catch (PDOException $e) {
            $error_message = 'Failed to create category. Category name might already exist.';
        }
    }
}

// Helper function to format file sizes
function formatBytes($size, $precision = 2) {
    $base = log($size, 1024);
    $suffixes = array('B', 'KB', 'MB', 'GB', 'TB');
    return round(pow(1024, $base - floor($base)), $precision) . ' ' . $suffixes[floor($base)];
}

$page_title = "Upload Download - Admin";
include '../includes/header.php';
?>

<div class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="../index.php">Home</a></li>
            <li class="breadcrumb-item"><a href="admin_index.php">Admin Dashboard</a></li>
            <li class="breadcrumb-item active">Upload Download</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2>
                        <i class="fas fa-upload me-2 text-primary"></i>
                        Upload New Download
                    </h2>
                    <p class="text-muted">Add files, documents, and resources to the downloads center</p>
                </div>
                <div>
                    <a href="manage_downloads.php" class="btn btn-outline-primary">
                        <i class="fas fa-list me-1"></i>Manage Downloads
                    </a>
                    <a href="../downloads.php" class="btn btn-outline-secondary">
                        <i class="fas fa-eye me-1"></i>View Downloads
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Messages -->
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

    <div class="row">
        <!-- Upload Form -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-file-upload me-2"></i>
                        Upload File
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="" enctype="multipart/form-data" id="uploadForm">
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label for="title" class="form-label fw-semibold">
                                    <i class="fas fa-heading me-1"></i>Title *
                                </label>
                                <input type="text" class="form-control" id="title" name="title" 
                                       value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>" 
                                       placeholder="Enter file title" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="category_id" class="form-label fw-semibold">
                                    <i class="fas fa-folder me-1"></i>Category *
                                </label>
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
                            <label for="description" class="form-label fw-semibold">
                                <i class="fas fa-align-left me-1"></i>Description *
                            </label>
                            <textarea class="form-control" id="description" name="description" rows="4" 
                                      placeholder="Enter file description" required><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label for="file" class="form-label fw-semibold">
                                    <i class="fas fa-file me-1"></i>Main File *
                                </label>
                                <input type="file" class="form-control" id="file" name="file" required>
                                <div class="form-text">
                                    Allowed formats: PDF, DOC, XLS, PPT, Images, Videos, Archives | Max size: 50MB
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="thumbnail" class="form-label fw-semibold">
                                    <i class="fas fa-image me-1"></i>Thumbnail (Optional)
                                </label>
                                <input type="file" class="form-control" id="thumbnail" name="thumbnail" accept="image/*">
                                <div class="form-text">
                                    JPG, PNG, GIF only
                                </div>
                            </div>
                        </div>

                        <!-- File Preview Area -->
                        <div id="filePreview" class="mb-3" style="display: none;">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">File Preview</h6>
                                    <div id="fileInfo"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Upload Progress -->
                        <div id="uploadProgress" class="mb-3" style="display: none;">
                            <div class="progress">
                                <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                     role="progressbar" style="width: 0%"></div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            <button type="button" class="btn btn-outline-secondary" onclick="resetForm()">
                                <i class="fas fa-undo me-1"></i>Reset Form
                            </button>
                            <button type="submit" name="upload_file" class="btn btn-primary btn-lg">
                                <i class="fas fa-cloud-upload-alt me-2"></i>Upload File
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Recent Uploads -->
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="fas fa-clock me-2"></i>
                        Recent Uploads
                    </h6>
                </div>
                <div class="card-body">
                    <?php
                    $recent_sql = "SELECT d.*, dc.name as category_name 
                                  FROM downloads d 
                                  JOIN download_categories dc ON d.category_id = dc.id 
                                  ORDER BY d.created_at DESC 
                                  LIMIT 5";
                    $recent_uploads = $pdo->query($recent_sql)->fetchAll();
                    ?>
                    
                    <?php if (!empty($recent_uploads)): ?>
                        <?php foreach ($recent_uploads as $upload): ?>
                        <div class="d-flex align-items-center p-2 border-bottom">
                            <div class="me-3">
                                <i class="fas fa-file fa-2x text-primary"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1"><?php echo htmlspecialchars($upload['title']); ?></h6>
                                <small class="text-muted">
                                    <?php echo htmlspecialchars($upload['category_name']); ?> | 
                                    <?php echo $upload['file_size']; ?> | 
                                    <?php echo date('M j, Y', strtotime($upload['created_at'])); ?>
                                </small>
                            </div>
                            <div>
                                <a href="../downloads.php?download=<?php echo $upload['id']; ?>" 
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-download"></i>
                                </a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted text-center mb-0">No uploads yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Quick Stats -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-chart-bar me-2"></i>
                        Download Statistics
                    </h6>
                </div>
                <div class="card-body">
                    <?php
                    $stats = [
                        'total_downloads' => $pdo->query("SELECT COUNT(*) FROM downloads")->fetchColumn(),
                        'total_categories' => $pdo->query("SELECT COUNT(*) FROM download_categories")->fetchColumn(),
                        'total_size' => $pdo->query("SELECT SUM(CAST(SUBSTRING_INDEX(file_size, ' ', 1) AS DECIMAL(10,2))) FROM downloads WHERE file_size LIKE '%MB'")->fetchColumn(),
                        'popular_category' => $pdo->query("SELECT dc.name FROM download_categories dc JOIN downloads d ON dc.id = d.category_id GROUP BY dc.id ORDER BY COUNT(d.id) DESC LIMIT 1")->fetchColumn()
                    ];
                    ?>
                    
                    <div class="row text-center">
                        <div class="col-6 mb-3">
                            <h4 class="text-info mb-0"><?php echo number_format($stats['total_downloads']); ?></h4>
                            <small class="text-muted">Total Files</small>
                        </div>
                        <div class="col-6 mb-3">
                            <h4 class="text-success mb-0"><?php echo number_format($stats['total_categories']); ?></h4>
                            <small class="text-muted">Categories</small>
                        </div>
                        <div class="col-6">
                            <h4 class="text-warning mb-0"><?php echo number_format($stats['total_size'] ?: 0, 1); ?>MB</h4>
                            <small class="text-muted">Total Size</small>
                        </div>
                        <div class="col-6">
                            <h4 class="text-danger mb-0">
                                <?php echo $stats['popular_category'] ? substr($stats['popular_category'], 0, 8) : 'N/A'; ?>
                            </h4>
                            <small class="text-muted">Popular</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Create Category -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-plus me-2"></i>
                        Create New Category
                    </h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="category_name" class="form-label">Category Name</label>
                            <input type="text" class="form-control" id="category_name" name="category_name" 
                                   placeholder="Enter category name" required>
                        </div>
                        <div class="mb-3">
                            <label for="category_description" class="form-label">Description</label>
                            <textarea class="form-control" id="category_description" name="category_description" 
                                      rows="2" placeholder="Enter description"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="category_icon" class="form-label">Icon Class</label>
                            <input type="text" class="form-control" id="category_icon" name="category_icon" 
                                   placeholder="fas fa-file" value="fas fa-file">
                            <div class="form-text">
                                <a href="https://fontawesome.com/icons" target="_blank">FontAwesome Icons</a>
                            </div>
                        </div>
                        <button type="submit" name="create_category" class="btn btn-success w-100">
                            <i class="fas fa-plus me-1"></i>Create Category
                        </button>
                    </form>
                </div>
            </div>

            <!-- Upload Guidelines -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <h6 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        Upload Guidelines
                    </h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            Maximum file size: 50MB
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            Use descriptive titles
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            Add detailed descriptions
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            Include thumbnails for better visibility
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            Choose appropriate categories
                        </li>
                        <li>
                            <i class="fas fa-times text-danger me-2"></i>
                            No copyrighted materials
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.border-bottom:last-child {
    border-bottom: none !important;
}

#filePreview {
    animation: fadeIn 0.3s ease-in;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

.progress {
    height: 8px;
}

.file-info-item {
    display: flex;
    justify-content: space-between;
    padding: 0.25rem 0;
    border-bottom: 1px solid #eee;
}

.file-info-item:last-child {
    border-bottom: none;
}
</style>

<script>
// File preview functionality
document.getElementById('file').addEventListener('change', function(e) {
    const file = e.target.files[0];
    const preview = document.getElementById('filePreview');
    const fileInfo = document.getElementById('fileInfo');
    
    if (file) {
        const fileSize = formatFileSize(file.size);
        const fileType = file.type || 'Unknown';
        const fileName = file.name;
        
        fileInfo.innerHTML = `
            <div class="file-info-item">
                <span><strong>Name:</strong></span>
                <span>${fileName}</span>
            </div>
            <div class="file-info-item">
                <span><strong>Size:</strong></span>
                <span>${fileSize}</span>
            </div>
            <div class="file-info-item">
                <span><strong>Type:</strong></span>
                <span>${fileType}</span>
            </div>
        `;
        
        preview.style.display = 'block';
        
        // Auto-fill title if empty
        const titleInput = document.getElementById('title');
        if (!titleInput.value) {
            const nameWithoutExt = fileName.substring(0, fileName.lastIndexOf('.')) || fileName;
            titleInput.value = nameWithoutExt.replace(/[-_]/g, ' ');
        }
    } else {
        preview.style.display = 'none';
    }
});

// Format file size
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

// Form validation
document.getElementById('uploadForm').addEventListener('submit', function(e) {
    const fileInput = document.getElementById('file');
    const titleInput = document.getElementById('title');
    const categoryInput = document.getElementById('category_id');
    const descriptionInput = document.getElementById('description');
    
    if (!fileInput.files[0]) {
        e.preventDefault();
        alert('Please select a file to upload.');
        return;
    }
    
    if (!titleInput.value.trim()) {
        e.preventDefault();
        alert('Please enter a title for the file.');
        titleInput.focus();
        return;
    }
    
    if (!categoryInput.value) {
        e.preventDefault();
        alert('Please select a category.');
        categoryInput.focus();
        return;
    }
    
    if (!descriptionInput.value.trim()) {
        e.preventDefault();
        alert('Please enter a description for the file.');
        descriptionInput.focus();
        return;
    }
    
    // Show upload progress
    const progressDiv = document.getElementById('uploadProgress');
    const progressBar = progressDiv.querySelector('.progress-bar');
    progressDiv.style.display = 'block';
    
    // Simulate progress (you can implement real progress tracking with XMLHttpRequest)
    let progress = 0;
    const interval = setInterval(() => {
        progress += 10;
        progressBar.style.width = progress + '%';
        if (progress >= 90) {
            clearInterval(interval);
        }
    }, 200);
});

// Reset form function
function resetForm() {
    if (confirm('Are you sure you want to reset the form? All entered data will be lost.')) {
        document.getElementById('uploadForm').reset();
        document.getElementById('filePreview').style.display = 'none';
        document.getElementById('uploadProgress').style.display = 'none';
    }
}

// Drag and drop functionality
const fileInput = document.getElementById('file');
const form = document.getElementById('uploadForm');

['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
    form.addEventListener(eventName, preventDefaults, false);
});

function preventDefaults(e) {
    e.preventDefault();
    e.stopPropagation();
}

['dragenter', 'dragover'].forEach(eventName => {
    form.addEventListener(eventName, highlight, false);
});

['dragleave', 'drop'].forEach(eventName => {
    form.addEventListener(eventName, unhighlight, false);
});

function highlight(e) {
    form.classList.add('border-primary');
}

function unhighlight(e) {
    form.classList.remove('border-primary');
}

form.addEventListener('drop', handleDrop, false);

function handleDrop(e) {
    const dt = e.dataTransfer;
    const files = dt.files;
    
    if (files.length > 0) {
        fileInput.files = files;
        fileInput.dispatchEvent(new Event('change'));
    }
}

// Auto-dismiss alerts
setTimeout(() => {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        const bsAlert = new bootstrap.Alert(alert);
        bsAlert.close();
    });
}, 5000);
</script>

<?php include '../includes/footer.php'; ?>