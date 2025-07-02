<?php
require_once 'config/config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    $_SESSION['login_required'] = true;
    $_SESSION['redirect_after_login'] = 'my_blogs.php';
    header('Location: login.php');
    exit();
}

$user = getUserInfo();
$error_message = '';
$success_message = '';

// Handle blog actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_blog'])) {
        $blog_id = intval($_POST['blog_id']);
        
        // Verify blog belongs to user
        $stmt = $pdo->prepare("SELECT * FROM blogs WHERE id = ? AND user_id = ?");
        $stmt->execute([$blog_id, $user['id']]);
        $blog = $stmt->fetch();
        
        if ($blog) {
            // Delete featured image if exists
            if ($blog['featured_image'] && file_exists(BLOG_IMAGES_PATH . $blog['featured_image'])) {
                unlink(BLOG_IMAGES_PATH . $blog['featured_image']);
            }
            
            // Delete blog and related data
            $pdo->beginTransaction();
            try {
                // Delete comments
                $stmt = $pdo->prepare("DELETE FROM blog_comments WHERE blog_id = ?");
                $stmt->execute([$blog_id]);
                
                // Delete likes
                $stmt = $pdo->prepare("DELETE FROM blog_likes WHERE blog_id = ?");
                $stmt->execute([$blog_id]);
                
                // Delete blog
                $stmt = $pdo->prepare("DELETE FROM blogs WHERE id = ?");
                $stmt->execute([$blog_id]);
                
                $pdo->commit();
                $success_message = 'Blog post deleted successfully.';
            } catch (PDOException $e) {
                $pdo->rollBack();
                $error_message = 'Failed to delete blog post.';
            }
        } else {
            $error_message = 'Blog post not found or you do not have permission to delete it.';
        }
    } elseif (isset($_POST['toggle_status'])) {
        $blog_id = intval($_POST['blog_id']);
        $new_status = cleanInput($_POST['new_status']);
        
        if (in_array($new_status, ['draft', 'published'])) {
            $stmt = $pdo->prepare("UPDATE blogs SET status = ? WHERE id = ? AND user_id = ?");
            if ($stmt->execute([$new_status, $blog_id, $user['id']])) {
                $success_message = 'Blog status updated successfully.';
            } else {
                $error_message = 'Failed to update blog status.';
            }
        }
    }
}

// Get filter and search parameters
$status_filter = isset($_GET['status']) ? cleanInput($_GET['status']) : 'all';
$search_query = isset($_GET['search']) ? cleanInput($_GET['search']) : '';
$current_page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$items_per_page = 10;
$offset = ($current_page - 1) * $items_per_page;

// Build query
$where_conditions = ['user_id = ?'];
$params = [$user['id']];

if ($status_filter !== 'all') {
    $where_conditions[] = 'status = ?';
    $params[] = $status_filter;
}

if (!empty($search_query)) {
    $where_conditions[] = '(title LIKE ? OR content LIKE ?)';
    $search_param = '%' . $search_query . '%';
    $params[] = $search_param;
    $params[] = $search_param;
}

$where_clause = implode(' AND ', $where_conditions);

// Get total count
$count_sql = "SELECT COUNT(*) FROM blogs WHERE " . $where_clause;
$stmt = $pdo->prepare($count_sql);
$stmt->execute($params);
$total_blogs = $stmt->fetchColumn();

// Get blogs with statistics
$blogs_sql = "SELECT b.*, 
              (SELECT COUNT(*) FROM blog_likes WHERE blog_id = b.id) as likes_count,
              (SELECT COUNT(*) FROM blog_comments WHERE blog_id = b.id) as comments_count
              FROM blogs b 
              WHERE " . $where_clause . "
              ORDER BY b.created_at DESC 
              LIMIT " . intval($items_per_page) . " OFFSET " . intval($offset);

$stmt = $pdo->prepare($blogs_sql);
$stmt->execute($params);
$blogs = $stmt->fetchAll();

// Calculate pagination
$total_pages = ceil($total_blogs / $items_per_page);

// Check for messages
if (isset($_SESSION['blog_saved'])) {
    $success_message = 'Blog post saved successfully!';
    unset($_SESSION['blog_saved']);
}

if (isset($_SESSION['blog_published'])) {
    $success_message = 'Blog post published successfully!';
    unset($_SESSION['blog_published']);
}

$page_title = "My Blogs";
include 'includes/header.php';
?>

<div class="container py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1">My Blog Posts</h2>
                    <p class="text-muted mb-0">Manage your published and draft blog posts</p>
                </div>
                <div>
                    <a href="write_blog.php" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Write New Blog
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <?php
        // Get user statistics
        $stats_sql = "SELECT 
                        COUNT(*) as total_blogs,
                        SUM(CASE WHEN status = 'published' THEN 1 ELSE 0 END) as published_blogs,
                        SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) as draft_blogs,
                        COALESCE(SUM(views), 0) as total_views
                      FROM blogs WHERE user_id = ?";
        $stmt = $pdo->prepare($stats_sql);
        $stmt->execute([$user['id']]);
        $stats = $stmt->fetch();
        
        // Get total likes
        $likes_sql = "SELECT COUNT(*) as total_likes 
                     FROM blog_likes bl 
                     JOIN blogs b ON bl.blog_id = b.id 
                     WHERE b.user_id = ?";
        $stmt = $pdo->prepare($likes_sql);
        $stmt->execute([$user['id']]);
        $likes_stats = $stmt->fetch();
        ?>
        
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body">
                    <i class="fas fa-blog fa-2x text-primary mb-2"></i>
                    <h4 class="text-primary"><?php echo number_format($stats['total_blogs']); ?></h4>
                    <p class="text-muted mb-0">Total Blogs</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body">
                    <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                    <h4 class="text-success"><?php echo number_format($stats['published_blogs']); ?></h4>
                    <p class="text-muted mb-0">Published</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body">
                    <i class="fas fa-eye fa-2x text-info mb-2"></i>
                    <h4 class="text-info"><?php echo number_format($stats['total_views']); ?></h4>
                    <p class="text-muted mb-0">Total Views</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body">
                    <i class="fas fa-heart fa-2x text-danger mb-2"></i>
                    <h4 class="text-danger"><?php echo number_format($likes_stats['total_likes']); ?></h4>
                    <p class="text-muted mb-0">Total Likes</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters and Search -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form method="GET" action="" class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label for="status" class="form-label">Filter by Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>All Posts</option>
                                <option value="published" <?php echo $status_filter === 'published' ? 'selected' : ''; ?>>Published</option>
                                <option value="draft" <?php echo $status_filter === 'draft' ? 'selected' : ''; ?>>Drafts</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="search" class="form-label">Search</label>
                            <input type="text" class="form-control" id="search" name="search" 
                                   value="<?php echo htmlspecialchars($search_query); ?>"
                                   placeholder="Search in title or content...">
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search me-2"></i>Filter
                            </button>
                        </div>
                    </form>
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

    <!-- Blog Posts -->
    <?php if (!empty($blogs)): ?>
        <div class="row">
            <?php foreach ($blogs as $blog): ?>
            <div class="col-12 mb-4">
                <div class="card border-0 shadow-sm blog-card">
                    <div class="row g-0">
                        <div class="col-md-3">
                            <?php if (!empty($blog['featured_image'])): ?>
                                <img src="<?php echo BLOG_IMAGES_PATH . $blog['featured_image']; ?>" 
                                     class="img-fluid h-100 object-cover rounded-start" alt="Blog image">
                            <?php else: ?>
                                <div class="h-100 bg-light d-flex align-items-center justify-content-center rounded-start">
                                    <i class="fas fa-image fa-3x text-muted"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-9">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h5 class="card-title mb-1">
                                            <a href="blog_detail.php?id=<?php echo $blog['id']; ?>" class="text-decoration-none">
                                                <?php echo htmlspecialchars($blog['title']); ?>
                                            </a>
                                        </h5>
                                        <div class="d-flex align-items-center gap-3 mb-2">
                                            <span class="badge <?php echo $blog['status'] === 'published' ? 'bg-success' : 'bg-warning text-dark'; ?>">
                                                <?php echo ucfirst($blog['status']); ?>
                                            </span>
                                            <small class="text-muted">
                                                <i class="fas fa-calendar me-1"></i>
                                                <?php echo date('M j, Y', strtotime($blog['created_at'])); ?>
                                            </small>
                                            <?php if ($blog['updated_at'] !== $blog['created_at']): ?>
                                            <small class="text-muted">
                                                <i class="fas fa-edit me-1"></i>
                                                Updated <?php echo date('M j, Y', strtotime($blog['updated_at'])); ?>
                                            </small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="dropdown">
                                        <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" 
                                                data-bs-toggle="dropdown">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li>
                                                <a class="dropdown-item" href="blog_detail.php?id=<?php echo $blog['id']; ?>">
                                                    <i class="fas fa-eye me-2"></i>View
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="edit_blog.php?id=<?php echo $blog['id']; ?>">
                                                    <i class="fas fa-edit me-2"></i>Edit
                                                </a>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <form method="POST" action="" style="display: inline;">
                                                    <input type="hidden" name="blog_id" value="<?php echo $blog['id']; ?>">
                                                    <input type="hidden" name="new_status" value="<?php echo $blog['status'] === 'published' ? 'draft' : 'published'; ?>">
                                                    <button type="submit" name="toggle_status" class="dropdown-item">
                                                        <i class="fas fa-<?php echo $blog['status'] === 'published' ? 'eye-slash' : 'paper-plane'; ?> me-2"></i>
                                                        <?php echo $blog['status'] === 'published' ? 'Unpublish' : 'Publish'; ?>
                                                    </button>
                                                </form>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <button type="button" class="dropdown-item text-danger" 
                                                        onclick="confirmDelete(<?php echo $blog['id']; ?>, '<?php echo addslashes($blog['title']); ?>')">
                                                    <i class="fas fa-trash me-2"></i>Delete
                                                </button>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                
                                <p class="card-text text-muted">
                                    <?php 
                                    $excerpt = $blog['excerpt'] ?: $blog['content'];
                                    echo htmlspecialchars(substr(strip_tags($excerpt), 0, 150)) . '...'; 
                                    ?>
                                </p>
                                
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="blog-stats">
                                        <small class="text-muted me-3">
                                            <i class="fas fa-eye me-1"></i><?php echo number_format($blog['views']); ?> views
                                        </small>
                                        <small class="text-muted me-3">
                                            <i class="fas fa-heart me-1"></i><?php echo number_format($blog['likes_count']); ?> likes
                                        </small>
                                        <small class="text-muted">
                                            <i class="fas fa-comment me-1"></i><?php echo number_format($blog['comments_count']); ?> comments
                                        </small>
                                    </div>
                                    <div>
                                        <a href="blog_detail.php?id=<?php echo $blog['id']; ?>" class="btn btn-primary btn-sm">
                                            View Post
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <div class="row">
            <div class="col-12">
                <nav aria-label="Blog pagination">
                    <ul class="pagination justify-content-center">
                        <?php if ($current_page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $current_page - 1; ?>&status=<?php echo $status_filter; ?>&search=<?php echo urlencode($search_query); ?>">
                                <i class="fas fa-chevron-left"></i> Previous
                            </a>
                        </li>
                        <?php endif; ?>
                        
                        <?php
                        $start_page = max(1, $current_page - 2);
                        $end_page = min($total_pages, $start_page + 4);
                        
                        for ($i = $start_page; $i <= $end_page; $i++):
                        ?>
                        <li class="page-item <?php echo $i == $current_page ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>&status=<?php echo $status_filter; ?>&search=<?php echo urlencode($search_query); ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                        <?php endfor; ?>
                        
                        <?php if ($current_page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $current_page + 1; ?>&status=<?php echo $status_filter; ?>&search=<?php echo urlencode($search_query); ?>">
                                Next <i class="fas fa-chevron-right"></i>
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        </div>
        <?php endif; ?>

    <?php else: ?>
        <!-- No Blogs State -->
        <div class="row">
            <div class="col-12">
                <div class="text-center py-5">
                    <i class="fas fa-blog fa-4x text-muted mb-3"></i>
                    <?php if (!empty($search_query) || $status_filter !== 'all'): ?>
                        <h4>No blogs found</h4>
                        <p class="text-muted">Try adjusting your search criteria or filters.</p>
                        <a href="my_blogs.php" class="btn btn-primary">
                            <i class="fas fa-times me-2"></i>Clear Filters
                        </a>
                    <?php else: ?>
                        <h4>No blog posts yet</h4>
                        <p class="text-muted">Start sharing your knowledge and experiences with the community!</p>
                        <a href="write_blog.php" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Write Your First Blog
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
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
                <p>Are you sure you want to delete the blog post "<span id="blogTitle"></span>"?</p>
                <p class="text-danger small">
                    <i class="fas fa-exclamation-triangle me-1"></i>
                    This action cannot be undone. All comments and likes will also be deleted.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form method="POST" action="" style="display: inline;">
                    <input type="hidden" name="blog_id" id="deleteBlogId">
                    <button type="submit" name="delete_blog" class="btn btn-danger">
                        <i class="fas fa-trash me-2"></i>Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.blog-card {
    transition: all 0.3s ease;
}

.blog-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15) !important;
}

.object-cover {
    object-fit: cover;
}

.blog-stats {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
}

@media (max-width: 768px) {
    .blog-stats {
        flex-direction: column;
        gap: 0.25rem;
    }
    
    .card-body {
        padding: 1rem;
    }
}
</style>

<script>
function confirmDelete(blogId, blogTitle) {
    document.getElementById('blogTitle').textContent = blogTitle;
    document.getElementById('deleteBlogId').value = blogId;
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}

// Auto-dismiss alerts
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });
    
    // Animate cards on load
    const cards = document.querySelectorAll('.blog-card');
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

// Prevent accidental form submission
document.querySelectorAll('form[method="POST"]').forEach(form => {
    form.addEventListener('submit', function(e) {
        const submitButton = form.querySelector('button[type="submit"]');
        if (submitButton && !submitButton.classList.contains('btn-danger')) {
            submitButton.disabled = true;
            setTimeout(() => {
                submitButton.disabled = false;
            }, 3000);
        }
    });
});
</script>

<?php include 'includes/footer.php'; ?>