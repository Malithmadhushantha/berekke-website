<?php
require_once 'config/config.php';

$page_title = "Downloads";
$selected_category = isset($_GET['category']) ? intval($_GET['category']) : null;

// Fetch all categories with their download counts
$categories_sql = "SELECT dc.*, COUNT(d.id) as download_count 
                  FROM download_categories dc 
                  LEFT JOIN downloads d ON dc.id = d.category_id 
                  GROUP BY dc.id 
                  ORDER BY dc.name";
$categories = $pdo->query($categories_sql)->fetchAll();

// If a specific category is selected, fetch all downloads for that category
$category_downloads = [];
$category_info = null;
if ($selected_category) {
    $stmt = $pdo->prepare("SELECT * FROM download_categories WHERE id = ?");
    $stmt->execute([$selected_category]);
    $category_info = $stmt->fetch();
    
    if ($category_info) {
        $stmt = $pdo->prepare("SELECT * FROM downloads WHERE category_id = ? ORDER BY created_at DESC");
        $stmt->execute([$selected_category]);
        $category_downloads = $stmt->fetchAll();
    }
}

// Handle file download
if (isset($_GET['download']) && is_numeric($_GET['download'])) {
    $download_id = intval($_GET['download']);
    
    // Get file info
    $stmt = $pdo->prepare("SELECT * FROM downloads WHERE id = ?");
    $stmt->execute([$download_id]);
    $file = $stmt->fetch();
    
    if ($file) {
        // Update download count
        $stmt = $pdo->prepare("UPDATE downloads SET download_count = download_count + 1 WHERE id = ?");
        $stmt->execute([$download_id]);
        
        // Force download
        $file_path = DOWNLOADS_PATH . $file['file_name'];
        if (file_exists($file_path)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($file['file_name']) . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file_path));
            readfile($file_path);
            exit();
        }
    }
}

include 'includes/header.php';
?>

<div class="container py-4">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="bg-info text-white rounded-4 p-4">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <h1 class="h2 mb-2">
                            <i class="fas fa-download me-3"></i>
                            Downloads Center
                        </h1>
                        <p class="mb-0 opacity-75">
                            Essential documents, forms, applications, and resources for law enforcement
                        </p>
                    </div>
                    <div class="col-lg-4 text-end">
                        <i class="fas fa-cloud-download-alt fa-4x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if ($selected_category && $category_info): ?>
        <!-- Category View -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex align-items-center mb-3">
                    <a href="downloads.php" class="btn btn-outline-secondary me-3">
                        <i class="fas fa-arrow-left me-1"></i>Back to Categories
                    </a>
                    <h3 class="mb-0">
                        <i class="<?php echo htmlspecialchars($category_info['icon']); ?> me-2"></i>
                        <?php echo htmlspecialchars($category_info['name']); ?>
                    </h3>
                </div>
                <p class="text-muted"><?php echo htmlspecialchars($category_info['description']); ?></p>
            </div>
        </div>

        <!-- Downloads Grid -->
        <div class="row g-4">
            <?php if (!empty($category_downloads)): ?>
                <?php foreach ($category_downloads as $download): ?>
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <div class="card h-100 border-0 shadow-sm download-card">
                        <div class="card-body text-center p-4">
                            <?php if (!empty($download['thumbnail'])): ?>
                                <img src="<?php echo DOWNLOADS_PATH . 'thumbnails/' . $download['thumbnail']; ?>" 
                                     class="img-fluid mb-3" style="max-height: 100px;" alt="Thumbnail">
                            <?php else: ?>
                                <i class="fas fa-file-download fa-4x text-info mb-3"></i>
                            <?php endif; ?>
                            
                            <h6 class="card-title"><?php echo htmlspecialchars($download['title']); ?></h6>
                            <p class="card-text text-muted small">
                                <?php echo htmlspecialchars(substr($download['description'], 0, 80)) . '...'; ?>
                            </p>
                            
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <small class="text-muted">
                                    <i class="fas fa-download me-1"></i>
                                    <?php echo number_format($download['download_count']); ?>
                                </small>
                                <?php if (!empty($download['file_size'])): ?>
                                <small class="text-muted"><?php echo htmlspecialchars($download['file_size']); ?></small>
                                <?php endif; ?>
                            </div>
                            
                            <a href="?download=<?php echo $download['id']; ?>" class="btn btn-info btn-sm w-100">
                                <i class="fas fa-download me-1"></i>Download
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5">
                    <i class="fas fa-folder-open fa-4x text-muted mb-3"></i>
                    <h4>No Downloads Available</h4>
                    <p class="text-muted">This category doesn't have any downloads yet.</p>
                </div>
            <?php endif; ?>
        </div>

    <?php else: ?>
        <!-- Categories Overview -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <h3>Download Categories</h3>
                    <div class="input-group" style="max-width: 300px;">
                        <input type="text" class="form-control" placeholder="Search downloads..." id="searchInput">
                        <button class="btn btn-outline-secondary" type="button">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Categories Grid -->
        <?php 
        $category_chunks = array_chunk($categories, 4); // 4 categories per row as requested
        foreach ($category_chunks as $row_categories): 
        ?>
        <div class="row g-4 mb-5">
            <?php foreach ($row_categories as $category): ?>
            <div class="col-lg-3 col-md-6">
                <div class="card h-100 border-0 shadow-sm category-card">
                    <div class="card-header bg-light text-center">
                        <i class="<?php echo htmlspecialchars($category['icon']); ?> fa-3x text-info mb-2"></i>
                        <h5 class="mb-0"><?php echo htmlspecialchars($category['name']); ?></h5>
                    </div>
                    <div class="card-body">
                        <p class="card-text text-muted small">
                            <?php echo htmlspecialchars($category['description']); ?>
                        </p>
                        
                        <!-- Show 3 sample downloads -->
                        <?php
                        $stmt = $pdo->prepare("SELECT * FROM downloads WHERE category_id = ? ORDER BY download_count DESC LIMIT 3");
                        $stmt->execute([$category['id']]);
                        $sample_downloads = $stmt->fetchAll();
                        ?>
                        
                        <?php if (!empty($sample_downloads)): ?>
                        <div class="sample-downloads mb-3">
                            <?php foreach ($sample_downloads as $download): ?>
                            <div class="d-flex align-items-center mb-2 p-2 bg-light rounded">
                                <i class="fas fa-file fa-sm text-muted me-2"></i>
                                <div class="flex-grow-1">
                                    <small class="fw-semibold"><?php echo htmlspecialchars($download['title']); ?></small>
                                    <div class="text-muted" style="font-size: 0.75rem;">
                                        <i class="fas fa-download me-1"></i>
                                        <?php echo number_format($download['download_count']); ?> downloads
                                    </div>
                                </div>
                                <a href="?download=<?php echo $download['id']; ?>" class="btn btn-sm btn-outline-info">
                                    <i class="fas fa-download"></i>
                                </a>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                        
                        <div class="text-center">
                            <span class="badge bg-info mb-2">
                                <?php echo number_format($category['download_count']); ?> items
                            </span>
                            <br>
                            <a href="?category=<?php echo $category['id']; ?>" class="btn btn-primary btn-sm">
                                <i class="fas fa-folder-open me-1"></i>Browse All Items
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endforeach; ?>

        <!-- Popular Downloads -->
        <div class="row mt-5">
            <div class="col-12">
                <h4 class="mb-4">
                    <i class="fas fa-fire me-2 text-danger"></i>
                    Most Popular Downloads
                </h4>
            </div>
        </div>

        <div class="row g-4">
            <?php
            $popular_sql = "SELECT d.*, dc.name as category_name, dc.icon as category_icon 
                           FROM downloads d 
                           JOIN download_categories dc ON d.category_id = dc.id 
                           ORDER BY d.download_count DESC 
                           LIMIT 8";
            $popular_downloads = $pdo->query($popular_sql)->fetchAll();
            
            foreach ($popular_downloads as $download):
            ?>
            <div class="col-lg-3 col-md-4 col-sm-6">
                <div class="card h-100 border-0 shadow-sm download-card">
                    <div class="card-body text-center p-4">
                        <?php if (!empty($download['thumbnail'])): ?>
                            <img src="<?php echo DOWNLOADS_PATH . 'thumbnails/' . $download['thumbnail']; ?>" 
                                 class="img-fluid mb-3" style="max-height: 80px;" alt="Thumbnail">
                        <?php else: ?>
                            <i class="<?php echo htmlspecialchars($download['category_icon']); ?> fa-3x text-info mb-3"></i>
                        <?php endif; ?>
                        
                        <h6 class="card-title"><?php echo htmlspecialchars($download['title']); ?></h6>
                        <p class="card-text text-muted small">
                            <?php echo htmlspecialchars(substr($download['description'], 0, 60)) . '...'; ?>
                        </p>
                        
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <small class="text-muted">
                                <i class="fas fa-folder me-1"></i>
                                <?php echo htmlspecialchars($download['category_name']); ?>
                            </small>
                            <small class="badge bg-danger">
                                <?php echo number_format($download['download_count']); ?> downloads
                            </small>
                        </div>
                        
                        <a href="?download=<?php echo $download['id']; ?>" class="btn btn-info btn-sm w-100">
                            <i class="fas fa-download me-1"></i>Download
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Upload Section for Admins -->
        <?php if (isLoggedIn() && $_SESSION['user_role'] === 'admin'): ?>
        <div class="row mt-5">
            <div class="col-12">
                <div class="card border-warning">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0">
                            <i class="fas fa-upload me-2"></i>
                            Admin: Upload New Files
                        </h5>
                    </div>
                    <div class="card-body">
                        <a href="admin/upload_download.php" class="btn btn-warning">
                            <i class="fas fa-plus me-2"></i>
                            Upload New Download
                        </a>
                        <a href="admin/manage_downloads.php" class="btn btn-outline-warning ms-2">
                            <i class="fas fa-cog me-2"></i>
                            Manage Downloads
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<style>
.category-card {
    transition: all 0.3s ease;
}

.category-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 0.5rem 1.5rem rgba(0,0,0,0.15) !important;
}

.download-card {
    transition: all 0.3s ease;
}

.download-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15) !important;
}

.sample-downloads {
    max-height: 200px;
    overflow-y: auto;
}

.sample-downloads::-webkit-scrollbar {
    width: 4px;
}

.sample-downloads::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 2px;
}

.sample-downloads::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 2px;
}

.sample-downloads::-webkit-scrollbar-thumb:hover {
    background: #555;
}

@media (max-width: 768px) {
    .sample-downloads {
        max-height: 150px;
    }
    
    .card-title {
        font-size: 0.9rem;
    }
}
</style>

<script>
// Search functionality
document.getElementById('searchInput')?.addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const cards = document.querySelectorAll('.category-card, .download-card');
    
    cards.forEach(card => {
        const title = card.querySelector('.card-title, h5')?.textContent.toLowerCase() || '';
        const description = card.querySelector('.card-text')?.textContent.toLowerCase() || '';
        
        if (title.includes(searchTerm) || description.includes(searchTerm)) {
            card.closest('.col-lg-3, .col-md-4, .col-md-6, .col-sm-6').style.display = '';
        } else {
            card.closest('.col-lg-3, .col-md-4, .col-md-6, .col-sm-6').style.display = 'none';
        }
    });
});

// Download tracking
document.addEventListener('DOMContentLoaded', function() {
    const downloadLinks = document.querySelectorAll('a[href*="download="]');
    
    downloadLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            // Optional: Add download tracking analytics here
            console.log('Download initiated for:', this.href);
        });
    });
    
    // Animate cards on load
    const cards = document.querySelectorAll('.category-card, .download-card');
    cards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        
        setTimeout(() => {
            card.style.transition = 'all 0.6s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 100);
    });
});

// File type icons
function getFileTypeIcon(filename) {
    const extension = filename.split('.').pop().toLowerCase();
    const iconMap = {
        'pdf': 'fas fa-file-pdf text-danger',
        'doc': 'fas fa-file-word text-primary',
        'docx': 'fas fa-file-word text-primary',
        'xls': 'fas fa-file-excel text-success',
        'xlsx': 'fas fa-file-excel text-success',
        'ppt': 'fas fa-file-powerpoint text-warning',
        'pptx': 'fas fa-file-powerpoint text-warning',
        'zip': 'fas fa-file-archive text-secondary',
        'rar': 'fas fa-file-archive text-secondary',
        'jpg': 'fas fa-file-image text-info',
        'jpeg': 'fas fa-file-image text-info',
        'png': 'fas fa-file-image text-info',
        'gif': 'fas fa-file-image text-info'
    };
    
    return iconMap[extension] || 'fas fa-file text-muted';
}
</script>

<?php include 'includes/footer.php'; ?>