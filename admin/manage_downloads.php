<?php
require_once '../config/config.php';

// Check if user is admin
requireAdmin();

$error_message = '';
$success_message = '';

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_download'])) {
    $title = cleanInput($_POST['title']);
    $description = cleanInput($_POST['description']);
    $category_id = intval($_POST['category_id']);
    
    if (empty($title) || empty($category_id)) {
        $error_message = 'Title and category are required.';
    } elseif (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../' . DOWNLOADS_PATH;
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $file_name = $_FILES['file']['name'];
        $file_size = $_FILES['file']['size'];
        $file_tmp = $_FILES['file']['tmp_name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        // Allowed file types
        $allowed_types = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'zip', 'rar'];
        
        if (!in_array($file_ext, $allowed_types)) {
            $error_message = 'File type not allowed. Allowed types: ' . implode(', ', $allowed_types);
        } elseif ($file_size > 50 * 1024 * 1024) { // 50MB limit
            $error_message = 'File size too large. Maximum 50MB allowed.';
        } else {
            $new_file_name = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $file_name);
            $upload_path = $upload_dir . $new_file_name;
            
            if (move_uploaded_file($file_tmp, $upload_path)) {
                // Handle thumbnail upload
                $thumbnail = 'default_thumbnail.jpg';
                if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
                    $thumb_ext = strtolower(pathinfo($_FILES['thumbnail']['name'], PATHINFO_EXTENSION));
                    if (in_array($thumb_ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                        $thumbnail = 'thumb_' . time() . '.' . $thumb_ext;
                        move_uploaded_file($_FILES['thumbnail']['tmp_name'], $upload_dir . $thumbnail);
                    }
                }
                
                $file_size_formatted = formatBytes($file_size);
                
                try {
                    $stmt = $pdo->prepare("INSERT INTO downloads (category_id, title, description, file_name, file_size, thumbnail) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$category_id, $title, $description, $new_file_name, $file_size_formatted, $thumbnail]);
                    $success_message = 'Download added successfully!';
                } catch (PDOException $e) {
                    $error_message = 'Database error: ' . $e->getMessage();
                    unlink($upload_path); // Remove uploaded file on error
                }
            } else {
                $error_message = 'Failed to upload file.';
            }
        }
    } else {
        $error_message = 'Please select a file to upload.';
    }
}

// Handle delete download
if (isset($_POST['delete_download'])) {
    $download_id = intval($_POST['download_id']);
    
    // Get file info before deleting
    $stmt = $pdo->prepare("SELECT file_name, thumbnail FROM downloads WHERE id = ?");
    $stmt->execute([$download_id]);
    $download = $stmt->fetch();
    
    if ($download) {
        // Delete files
        $file_path = '../' . DOWNLOADS_PATH . $download['file_name'];
        if (file_exists($file_path)) {
            unlink($file_path);
        }
        
        if ($download['thumbnail'] !== 'default_thumbnail.jpg') {
            $thumb_path = '../uploads/downloads/' . DOWNLOADS_PATH . $download['thumbnail'];
            if (file_exists($thumb_path)) {
                unlink($thumb_path);
            }
        }
        
        // Delete from database
        $stmt = $pdo->prepare("DELETE FROM downloads WHERE id = ?");
        $stmt->execute([$download_id]);
        $success_message = 'Download deleted successfully!';
    }
}

// Handle category management
if (isset($_POST['add_category'])) {
    $cat_name = cleanInput($_POST['category_name']);
    $cat_description = cleanInput($_POST['category_description']);
    $cat_icon = cleanInput($_POST['category_icon']);
    
    if (!empty($cat_name)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO download_categories (name, description, icon) VALUES (?, ?, ?)");
            $stmt->execute([$cat_name, $cat_description, $cat_icon]);
            $success_message = 'Category added successfully!';
        } catch (PDOException $e) {
            $error_message = 'Error adding category: ' . $e->getMessage();
        }
    }
}

if (isset($_POST['delete_category'])) {
    $cat_id = intval($_POST['category_id']);
    
    // Check if category has downloads
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM downloads WHERE category_id = ?");
    $stmt->execute([$cat_id]);
    $count = $stmt->fetchColumn();
    
    if ($count > 0) {
        $error_message = 'Cannot delete category with existing downloads. Please move or delete downloads first.';
    } else {
        $stmt = $pdo->prepare("DELETE FROM download_categories WHERE id = ?");
        $stmt->execute([$cat_id]);
        $success_message = 'Category deleted successfully!';
    }
}

// Get statistics
$stats = [];
$stmt = $pdo->query("SELECT COUNT(*) as total_downloads FROM downloads");
$stats['total_downloads'] = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) as total_categories FROM download_categories");
$stats['total_categories'] = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT SUM(download_count) as total_downloaded FROM downloads");
$stats['total_downloaded'] = $stmt->fetchColumn() ?: 0;

// Get downloads with category info
$search = isset($_GET['search']) ? cleanInput($_GET['search']) : '';
$category_filter = isset($_GET['category']) ? intval($_GET['category']) : 0;

$sql = "SELECT d.*, c.name as category_name, c.icon as category_icon 
        FROM downloads d 
        JOIN download_categories c ON d.category_id = c.id 
        WHERE 1=1";

$params = [];
if (!empty($search)) {
    $sql .= " AND (d.title LIKE ? OR d.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($category_filter > 0) {
    $sql .= " AND d.category_id = ?";
    $params[] = $category_filter;
}

$sql .= " ORDER BY d.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$downloads = $stmt->fetchAll();

// Get categories
$stmt = $pdo->query("SELECT * FROM download_categories ORDER BY name");
$categories = $stmt->fetchAll();

function formatBytes($size, $precision = 2) {
    $base = log($size, 1024);
    $suffixes = array('B', 'KB', 'MB', 'GB', 'TB');
    return round(pow(1024, $base - floor($base)), $precision) . ' ' . $suffixes[floor($base)];
}

$page_title = "Manage Downloads";
include '../includes/header.php';
?>

<div class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="admin_index.php">Admin Dashboard</a></li>
            <li class="breadcrumb-item active">Manage Downloads</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h2 mb-1">
                        <i class="fas fa-download me-2"></i>
                        Manage Downloads
                    </h1>
                    <p class="text-muted mb-0">Upload and manage downloadable files and resources</p>
                </div>
                <div>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDownloadModal">
                        <i class="fas fa-plus me-1"></i>Add Download
                    </button>
                    <button class="btn btn-outline-primary ms-2" data-bs-toggle="modal" data-bs-target="#manageCategoriesModal">
                        <i class="fas fa-tags me-1"></i>Categories
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Alerts -->
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

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="feature-icon bg-primary text-white rounded-circle me-3" 
                             style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-file"></i>
                        </div>
                        <div>
                            <h3 class="mb-0"><?php echo number_format($stats['total_downloads']); ?></h3>
                            <small class="text-muted">Total Downloads</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="feature-icon bg-success text-white rounded-circle me-3" 
                             style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-tags"></i>
                        </div>
                        <div>
                            <h3 class="mb-0"><?php echo number_format($stats['total_categories']); ?></h3>
                            <small class="text-muted">Categories</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="feature-icon bg-info text-white rounded-circle me-3" 
                             style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-download"></i>
                        </div>
                        <div>
                            <h3 class="mb-0"><?php echo number_format($stats['total_downloaded']); ?></h3>
                            <small class="text-muted">Times Downloaded</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="feature-icon bg-warning text-white rounded-circle me-3" 
                             style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div>
                            <h3 class="mb-0"><?php echo $stats['total_downloads'] > 0 ? round($stats['total_downloaded'] / $stats['total_downloads'], 1) : 0; ?></h3>
                            <small class="text-muted">Avg. Downloads</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Search and Filter -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form method="GET" class="row align-items-end">
                        <div class="col-lg-6 col-md-6 mb-3">
                            <label for="search" class="form-label">Search Downloads</label>
                            <input type="text" class="form-control" id="search" name="search" 
                                   value="<?php echo htmlspecialchars($search); ?>" 
                                   placeholder="Search by title or description...">
                        </div>
                        <div class="col-lg-4 col-md-4 mb-3">
                            <label for="category" class="form-label">Filter by Category</label>
                            <select class="form-select" id="category" name="category">
                                <option value="0">All Categories</option>
                                <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>" <?php echo $category_filter == $cat['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-lg-2 col-md-2 mb-3">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search me-1"></i>Search
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Downloads List -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Downloads List</h5>
                </div>
                <div class="card-body p-0">
                    <?php if (!empty($downloads)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>File</th>
                                        <th>Category</th>
                                        <th>Size</th>
                                        <th>Downloads</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($downloads as $download): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="../<?php echo DOWNLOADS_PATH . $download['thumbnail']; ?>" 
                                                     alt="Thumbnail" class="me-3" 
                                                     style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px;"
                                                     onerror="this.src='../assets/images/file-icon.png'">
                                                <div>
                                                    <h6 class="mb-1"><?php echo htmlspecialchars($download['title']); ?></h6>
                                                    <small class="text-muted"><?php echo htmlspecialchars($download['description']); ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary">
                                                <i class="<?php echo htmlspecialchars($download['category_icon']); ?> me-1"></i>
                                                <?php echo htmlspecialchars($download['category_name']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($download['file_size']); ?></td>
                                        <td>
                                            <span class="badge bg-info"><?php echo number_format($download['download_count']); ?></span>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                <?php echo date('M j, Y', strtotime($download['created_at'])); ?>
                                            </small>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="../<?php echo DOWNLOADS_PATH . $download['file_name']; ?>" 
                                                   class="btn btn-sm btn-outline-primary" target="_blank">
                                                    <i class="fas fa-download"></i>
                                                </a>
                                                <button class="btn btn-sm btn-outline-warning" 
                                                        onclick="editDownload(<?php echo $download['id']; ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger" 
                                                        onclick="deleteDownload(<?php echo $download['id']; ?>)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-folder-open fa-4x text-muted mb-3"></i>
                            <h5>No downloads found</h5>
                            <p class="text-muted">Add your first download to get started.</p>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDownloadModal">
                                <i class="fas fa-plus me-1"></i>Add Download
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Download Modal -->
<div class="modal fade" id="addDownloadModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Download</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="category_id" class="form-label">Category <span class="text-danger">*</span></label>
                            <select class="form-select" id="category_id" name="category_id" required>
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>">
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3" 
                                  placeholder="Brief description of the download"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="file" class="form-label">File <span class="text-danger">*</span></label>
                        <input type="file" class="form-control" id="file" name="file" required>
                        <div class="form-text">
                            Allowed types: PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, TXT, ZIP, RAR (Max 50MB)
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="thumbnail" class="form-label">Thumbnail (Optional)</label>
                        <input type="file" class="form-control" id="thumbnail" name="thumbnail" accept="image/*">
                        <div class="form-text">
                            Upload a custom thumbnail image (JPG, PNG, GIF)
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_download" class="btn btn-primary">
                        <i class="fas fa-upload me-1"></i>Upload Download
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Manage Categories Modal -->
<div class="modal fade" id="manageCategoriesModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Manage Categories</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Add Category Form -->
                <div class="border rounded p-3 mb-4">
                    <h6 class="mb-3">Add New Category</h6>
                    <form method="POST" class="row">
                        <div class="col-md-4 mb-3">
                            <label for="category_name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="category_name" name="category_name" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="category_icon" class="form-label">Icon Class</label>
                            <input type="text" class="form-control" id="category_icon" name="category_icon" 
                                   placeholder="fas fa-file-alt" value="fas fa-file-alt">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">&nbsp;</label>
                            <button type="submit" name="add_category" class="btn btn-success w-100">
                                <i class="fas fa-plus me-1"></i>Add
                            </button>
                        </div>
                        <div class="col-12">
                            <label for="category_description" class="form-label">Description</label>
                            <textarea class="form-control" id="category_description" name="category_description" rows="2"></textarea>
                        </div>
                    </form>
                </div>
                
                <!-- Categories List -->
                <h6 class="mb-3">Existing Categories</h6>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Icon</th>
                                <th>Downloads Count</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categories as $cat): ?>
                            <?php
                            $stmt = $pdo->prepare("SELECT COUNT(*) FROM downloads WHERE category_id = ?");
                            $stmt->execute([$cat['id']]);
                            $download_count = $stmt->fetchColumn();
                            ?>
                            <tr>
                                <td>
                                    <i class="<?php echo htmlspecialchars($cat['icon']); ?> me-2"></i>
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </td>
                                <td><code><?php echo htmlspecialchars($cat['icon']); ?></code></td>
                                <td><span class="badge bg-info"><?php echo $download_count; ?></span></td>
                                <td>
                                    <?php if ($download_count == 0): ?>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="category_id" value="<?php echo $cat['id']; ?>">
                                        <button type="submit" name="delete_category" class="btn btn-sm btn-outline-danger" 
                                                onclick="return confirm('Are you sure?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    <?php else: ?>
                                    <button class="btn btn-sm btn-outline-secondary" disabled title="Cannot delete category with downloads">
                                        <i class="fas fa-lock"></i>
                                    </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this download? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form method="POST" class="d-inline" id="deleteForm">
                    <input type="hidden" name="download_id" id="deleteDownloadId">
                    <button type="submit" name="delete_download" class="btn btn-danger">
                        <i class="fas fa-trash me-1"></i>Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.feature-icon {
    transition: all 0.3s ease;
}

.table th {
    border-top: none;
    font-weight: 600;
    color: #495057;
}

.badge {
    font-size: 0.75rem;
}

@media (max-width: 768px) {
    .d-flex.justify-content-between {
        flex-direction: column;
        gap: 1rem;
    }
    
    .btn-group {
        flex-direction: column;
    }
}
</style>

<script>
function deleteDownload(downloadId) {
    document.getElementById('deleteDownloadId').value = downloadId;
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}

function editDownload(downloadId) {
    // This would open an edit modal or redirect to edit page
    // For now, showing an alert
    alert('Edit functionality can be implemented similar to the add modal');
}

// File size validation
document.getElementById('file').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const maxSize = 50 * 1024 * 1024; // 50MB
        if (file.size > maxSize) {
            alert('File size exceeds 50MB limit');
            this.value = '';
        }
    }
});

// Auto-dismiss alerts
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });
});

// Search form auto-submit
let searchTimeout;
document.getElementById('search').addEventListener('input', function() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        this.form.submit();
    }, 500);
});
</script>

<?php include '../includes/footer.php'; ?>