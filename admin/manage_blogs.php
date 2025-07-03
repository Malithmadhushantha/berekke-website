<?php
require_once '../config/config.php';

// Check if user is admin
requireAdmin();

$error_message = '';
$success_message = '';

// Handle blog status update
if (isset($_POST['update_status'])) {
    $blog_id = intval($_POST['blog_id']);
    $new_status = cleanInput($_POST['status']);
    
    if (in_array($new_status, ['draft', 'published'])) {
        try {
            $stmt = $pdo->prepare("UPDATE blogs SET status = ? WHERE id = ?");
            $stmt->execute([$new_status, $blog_id]);
            $success_message = 'Blog status updated successfully!';
        } catch (PDOException $e) {
            $error_message = 'Error updating blog status: ' . $e->getMessage();
        }
    }
}

// Handle blog deletion
if (isset($_POST['delete_blog'])) {
    $blog_id = intval($_POST['blog_id']);
    
    try {
        // Get blog info for file cleanup
        $stmt = $pdo->prepare("SELECT featured_image FROM blogs WHERE id = ?");
        $stmt->execute([$blog_id]);
        $blog = $stmt->fetch();
        
        // Delete associated comments and likes
        $stmt = $pdo->prepare("DELETE FROM blog_comments WHERE blog_id = ?");
        $stmt->execute([$blog_id]);
        
        $stmt = $pdo->prepare("DELETE FROM blog_likes WHERE blog_id = ?");
        $stmt->execute([$blog_id]);
        
        // Delete blog
        $stmt = $pdo->prepare("DELETE FROM blogs WHERE id = ?");
        $stmt->execute([$blog_id]);
        
        // Delete featured image if exists
        if ($blog && $blog['featured_image'] && file_exists('../' . BLOG_IMAGES_PATH . $blog['featured_image'])) {
            unlink('../' . BLOG_IMAGES_PATH . $blog['featured_image']);
        }
        
        $success_message = 'Blog deleted successfully!';
    } catch (PDOException $e) {
        $error_message = 'Error deleting blog: ' . $e->getMessage();
    }
}

// Handle comment moderation
if (isset($_POST['moderate_comment'])) {
    $comment_id = intval($_POST['comment_id']);
    $action = cleanInput($_POST['action']);
    
    try {
        if ($action === 'approve') {
            $stmt = $pdo->prepare("UPDATE blog_comments SET status = 'approved' WHERE id = ?");
            $stmt->execute([$comment_id]);
            $success_message = 'Comment approved!';
        } elseif ($action === 'reject') {
            $stmt = $pdo->prepare("UPDATE blog_comments SET status = 'pending' WHERE id = ?");
            $stmt->execute([$comment_id]);
            $success_message = 'Comment rejected!';
        } elseif ($action === 'delete') {
            $stmt = $pdo->prepare("DELETE FROM blog_comments WHERE id = ?");
            $stmt->execute([$comment_id]);
            $success_message = 'Comment deleted!';
        }
    } catch (PDOException $e) {
        $error_message = 'Error moderating comment: ' . $e->getMessage();
    }
}

// Get statistics
$stats = [];

$stmt = $pdo->query("SELECT COUNT(*) as total_blogs FROM blogs");
$stats['total_blogs'] = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) as published_blogs FROM blogs WHERE status = 'published'");
$stats['published_blogs'] = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) as draft_blogs FROM blogs WHERE status = 'draft'");
$stats['draft_blogs'] = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT SUM(views) as total_views FROM blogs");
$stats['total_views'] = $stmt->fetchColumn() ?: 0;

$stmt = $pdo->query("SELECT COUNT(*) as total_comments FROM blog_comments");
$stats['total_comments'] = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) as pending_comments FROM blog_comments WHERE status = 'pending'");
$stats['pending_comments'] = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) as total_likes FROM blog_likes");
$stats['total_likes'] = $stmt->fetchColumn();

// Search and filter parameters
$search = isset($_GET['search']) ? cleanInput($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? cleanInput($_GET['status']) : '';
$author_filter = isset($_GET['author']) ? intval($_GET['author']) : 0;
$current_page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$items_per_page = 10;
$offset = ($current_page - 1) * $items_per_page;

// Build query for blogs
$sql = "SELECT b.*, u.first_name, u.last_name, u.email,
        (SELECT COUNT(*) FROM blog_comments bc WHERE bc.blog_id = b.id) as comment_count,
        (SELECT COUNT(*) FROM blog_likes bl WHERE bl.blog_id = b.id) as like_count
        FROM blogs b 
        JOIN users u ON b.user_id = u.id 
        WHERE 1=1";

$count_sql = "SELECT COUNT(*) FROM blogs b JOIN users u ON b.user_id = u.id WHERE 1=1";

$params = [];
$count_params = [];

if (!empty($search)) {
    $search_condition = " AND (b.title LIKE ? OR b.content LIKE ? OR u.first_name LIKE ? OR u.last_name LIKE ?)";
    $sql .= $search_condition;
    $count_sql .= $search_condition;
    $search_param = "%$search%";
    $params = array_merge($params, [$search_param, $search_param, $search_param, $search_param]);
    $count_params = array_merge($count_params, [$search_param, $search_param, $search_param, $search_param]);
}

if (!empty($status_filter)) {
    $sql .= " AND b.status = ?";
    $count_sql .= " AND b.status = ?";
    $params[] = $status_filter;
    $count_params[] = $status_filter;
}

if ($author_filter > 0) {
    $sql .= " AND b.user_id = ?";
    $count_sql .= " AND b.user_id = ?";
    $params[] = $author_filter;
    $count_params[] = $author_filter;
}

$sql .= " ORDER BY b.created_at DESC LIMIT ? OFFSET ?";
$params[] = $items_per_page;
$params[] = $offset;

// Execute queries
$stmt = $pdo->prepare($sql);
foreach ($params as $i => $param) {
    $stmt->bindValue($i + 1, $param, is_int($param) ? PDO::PARAM_INT : PDO::PARAM_STR);
}
$stmt->execute();
$blogs = $stmt->fetchAll();

$stmt = $pdo->prepare($count_sql);
foreach ($count_params as $i => $param) {
    $stmt->bindValue($i + 1, $param, is_int($param) ? PDO::PARAM_INT : PDO::PARAM_STR);
}
$stmt->execute();
$total_blogs = $stmt->fetchColumn();
$total_pages = ceil($total_blogs / $items_per_page);

// Get authors for filter
$stmt = $pdo->query("SELECT DISTINCT u.id, u.first_name, u.last_name FROM users u 
                     JOIN blogs b ON u.id = b.user_id ORDER BY u.first_name");
$authors = $stmt->fetchAll();

// Get recent comments for moderation
$stmt = $pdo->prepare("SELECT bc.*, b.title as blog_title, u.first_name, u.last_name 
                       FROM blog_comments bc 
                       JOIN blogs b ON bc.blog_id = b.id 
                       JOIN users u ON bc.user_id = u.id 
                       WHERE bc.status = 'pending' 
                       ORDER BY bc.created_at DESC 
                       LIMIT 5");
$stmt->execute();
$pending_comments = $stmt->fetchAll();

$page_title = "Manage Blogs";
include '../includes/header.php';
?>

<div class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="admin_index.php">Admin Dashboard</a></li>
            <li class="breadcrumb-item active">Manage Blogs</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h2 mb-1">
                        <i class="fas fa-blog me-2"></i>
                        Manage Blogs
                    </h1>
                    <p class="text-muted mb-0">Monitor and moderate blog posts and comments</p>
                </div>
                <div>
                    <a href="../blogs.php" class="btn btn-outline-primary me-2">
                        <i class="fas fa-eye me-1"></i>View Public Blogs
                    </a>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#commentsModal">
                        <i class="fas fa-comments me-1"></i>
                        Moderate Comments
                        <?php if ($stats['pending_comments'] > 0): ?>
                        <span class="badge bg-warning text-dark ms-1"><?php echo $stats['pending_comments']; ?></span>
                        <?php endif; ?>
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
                            <i class="fas fa-blog"></i>
                        </div>
                        <div>
                            <h3 class="mb-0"><?php echo number_format($stats['total_blogs']); ?></h3>
                            <small class="text-muted">Total Blogs</small>
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
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div>
                            <h3 class="mb-0"><?php echo number_format($stats['published_blogs']); ?></h3>
                            <small class="text-muted">Published</small>
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
                            <i class="fas fa-edit"></i>
                        </div>
                        <div>
                            <h3 class="mb-0"><?php echo number_format($stats['draft_blogs']); ?></h3>
                            <small class="text-muted">Drafts</small>
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
                            <i class="fas fa-eye"></i>
                        </div>
                        <div>
                            <h3 class="mb-0"><?php echo number_format($stats['total_views']); ?></h3>
                            <small class="text-muted">Total Views</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Secondary Statistics -->
    <div class="row mb-4">
        <div class="col-lg-4 col-md-4 mb-3">
            <div class="card border-0 shadow-sm bg-light">
                <div class="card-body text-center">
                    <h4 class="text-primary mb-1"><?php echo number_format($stats['total_comments']); ?></h4>
                    <small class="text-muted">Total Comments</small>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-4 mb-3">
            <div class="card border-0 shadow-sm bg-light">
                <div class="card-body text-center">
                    <h4 class="text-warning mb-1"><?php echo number_format($stats['pending_comments']); ?></h4>
                    <small class="text-muted">Pending Comments</small>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-4 mb-3">
            <div class="card border-0 shadow-sm bg-light">
                <div class="card-body text-center">
                    <h4 class="text-danger mb-1"><?php echo number_format($stats['total_likes']); ?></h4>
                    <small class="text-muted">Total Likes</small>
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
                        <div class="col-lg-4 col-md-6 mb-3">
                            <label for="search" class="form-label">Search Blogs</label>
                            <input type="text" class="form-control" id="search" name="search" 
                                   value="<?php echo htmlspecialchars($search); ?>" 
                                   placeholder="Search by title, content, or author...">
                        </div>
                        <div class="col-lg-2 col-md-3 mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="">All Status</option>
                                <option value="published" <?php echo $status_filter === 'published' ? 'selected' : ''; ?>>Published</option>
                                <option value="draft" <?php echo $status_filter === 'draft' ? 'selected' : ''; ?>>Draft</option>
                            </select>
                        </div>
                        <div class="col-lg-3 col-md-3 mb-3">
                            <label for="author" class="form-label">Author</label>
                            <select class="form-select" id="author" name="author">
                                <option value="0">All Authors</option>
                                <?php foreach ($authors as $author): ?>
                                <option value="<?php echo $author['id']; ?>" <?php echo $author_filter == $author['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($author['first_name'] . ' ' . $author['last_name']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-lg-2 col-md-12 mb-3">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search me-1"></i>Search
                            </button>
                        </div>
                        <div class="col-lg-1 col-md-12 mb-3">
                            <a href="manage_blogs.php" class="btn btn-outline-secondary w-100">
                                <i class="fas fa-times"></i>
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Blogs List -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        Blogs List 
                        <?php if ($total_blogs > 0): ?>
                        <span class="badge bg-primary"><?php echo number_format($total_blogs); ?> total</span>
                        <?php endif; ?>
                    </h5>
                    <div>
                        <button class="btn btn-sm btn-outline-success" onclick="exportBlogs()">
                            <i class="fas fa-download me-1"></i>Export
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <?php if (!empty($blogs)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Blog</th>
                                        <th>Author</th>
                                        <th>Status</th>
                                        <th>Stats</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($blogs as $blog): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-start">
                                                <?php if ($blog['featured_image']): ?>
                                                <img src="../<?php echo BLOG_IMAGES_PATH . $blog['featured_image']; ?>" 
                                                     alt="Featured" class="me-3" 
                                                     style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px;"
                                                     onerror="this.style.display='none'">
                                                <?php endif; ?>
                                                <div>
                                                    <h6 class="mb-1">
                                                        <a href="../blog_detail.php?id=<?php echo $blog['id']; ?>" 
                                                           target="_blank" class="text-decoration-none">
                                                            <?php echo htmlspecialchars($blog['title']); ?>
                                                        </a>
                                                    </h6>
                                                    <small class="text-muted">
                                                        <?php echo htmlspecialchars(substr($blog['excerpt'] ?: $blog['content'], 0, 100)); ?>...
                                                    </small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div>
                                                <small class="fw-semibold">
                                                    <?php echo htmlspecialchars($blog['first_name'] . ' ' . $blog['last_name']); ?>
                                                </small>
                                                <br>
                                                <small class="text-muted"><?php echo htmlspecialchars($blog['email']); ?></small>
                                            </div>
                                        </td>
                                        <td>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="blog_id" value="<?php echo $blog['id']; ?>">
                                                <select name="status" class="form-select form-select-sm" 
                                                        onchange="this.form.submit()" style="width: auto;">
                                                    <option value="draft" <?php echo $blog['status'] === 'draft' ? 'selected' : ''; ?>>Draft</option>
                                                    <option value="published" <?php echo $blog['status'] === 'published' ? 'selected' : ''; ?>>Published</option>
                                                </select>
                                                <input type="hidden" name="update_status" value="1">
                                            </form>
                                        </td>
                                        <td>
                                            <div class="small">
                                                <div><i class="fas fa-eye text-info me-1"></i><?php echo number_format($blog['views']); ?> views</div>
                                                <div><i class="fas fa-comment text-primary me-1"></i><?php echo number_format($blog['comment_count']); ?> comments</div>
                                                <div><i class="fas fa-heart text-danger me-1"></i><?php echo number_format($blog['like_count']); ?> likes</div>
                                            </div>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                <?php echo date('M j, Y', strtotime($blog['created_at'])); ?>
                                                <br>
                                                <?php echo date('g:i A', strtotime($blog['created_at'])); ?>
                                            </small>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="../blog_detail.php?id=<?php echo $blog['id']; ?>" 
                                                   class="btn btn-sm btn-outline-primary" target="_blank" title="View">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <button class="btn btn-sm btn-outline-info" 
                                                        onclick="viewBlogStats(<?php echo $blog['id']; ?>)" title="Statistics">
                                                    <i class="fas fa-chart-bar"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger" 
                                                        onclick="deleteBlog(<?php echo $blog['id']; ?>)" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                        <div class="card-footer">
                            <nav aria-label="Blogs pagination">
                                <ul class="pagination justify-content-center mb-0">
                                    <?php if ($current_page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $current_page - 1])); ?>">
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
                                        <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    </li>
                                    <?php endfor; ?>
                                    
                                    <?php if ($current_page < $total_pages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $current_page + 1])); ?>">
                                            Next <i class="fas fa-chevron-right"></i>
                                        </a>
                                    </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        </div>
                        <?php endif; ?>
                        
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-blog fa-4x text-muted mb-3"></i>
                            <h5>No blogs found</h5>
                            <p class="text-muted">No blogs match your current filters.</p>
                            <a href="manage_blogs.php" class="btn btn-primary">View All Blogs</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Comments Moderation Modal -->
<div class="modal fade" id="commentsModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    Moderate Comments
                    <?php if ($stats['pending_comments'] > 0): ?>
                    <span class="badge bg-warning text-dark"><?php echo $stats['pending_comments']; ?> pending</span>
                    <?php endif; ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <?php if (!empty($pending_comments)): ?>
                    <?php foreach ($pending_comments as $comment): ?>
                    <div class="border rounded p-3 mb-3">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <h6 class="mb-1">
                                    <?php echo htmlspecialchars($comment['first_name'] . ' ' . $comment['last_name']); ?>
                                    <small class="text-muted">commented on</small>
                                    <a href="../blog_detail.php?id=<?php echo $comment['blog_id']; ?>" target="_blank" class="text-decoration-none">
                                        <?php echo htmlspecialchars($comment['blog_title']); ?>
                                    </a>
                                </h6>
                                <small class="text-muted">
                                    <?php echo date('M j, Y g:i A', strtotime($comment['created_at'])); ?>
                                </small>
                            </div>
                            <div class="btn-group" role="group">
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="comment_id" value="<?php echo $comment['id']; ?>">
                                    <input type="hidden" name="action" value="approve">
                                    <button type="submit" name="moderate_comment" class="btn btn-sm btn-success" title="Approve">
                                        <i class="fas fa-check"></i>
                                    </button>
                                </form>
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="comment_id" value="<?php echo $comment['id']; ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <button type="submit" name="moderate_comment" class="btn btn-sm btn-danger" title="Delete"
                                            onclick="return confirm('Delete this comment?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                        <div class="comment-content">
                            <?php echo nl2br(htmlspecialchars($comment['comment'])); ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-comments fa-3x text-muted mb-3"></i>
                        <h5>No pending comments</h5>
                        <p class="text-muted">All comments have been moderated.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Delete Blog Modal -->
<div class="modal fade" id="deleteBlogModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Blog</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this blog? This will also delete all associated comments and likes. This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form method="POST" class="d-inline" id="deleteBlogForm">
                    <input type="hidden" name="blog_id" id="deleteBlogId">
                    <button type="submit" name="delete_blog" class="btn btn-danger">
                        <i class="fas fa-trash me-1"></i>Delete Blog
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Blog Statistics Modal -->
<div class="modal fade" id="blogStatsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Blog Statistics</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="blogStatsContent">
                <div class="text-center p-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
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

.comment-content {
    background-color: #f8f9fa;
    padding: 0.75rem;
    border-radius: 0.375rem;
    border-left: 3px solid #007bff;
}

@media (max-width: 768px) {
    .d-flex.justify-content-between {
        flex-direction: column;
        gap: 1rem;
    }
    
    .btn-group {
        flex-direction: column;
    }
    
    .table-responsive {
        font-size: 0.875rem;
    }
}
</style>

<script>
function deleteBlog(blogId) {
    document.getElementById('deleteBlogId').value = blogId;
    const modal = new bootstrap.Modal(document.getElementById('deleteBlogModal'));
    modal.show();
}

function viewBlogStats(blogId) {
    const modal = new bootstrap.Modal(document.getElementById('blogStatsModal'));
    const content = document.getElementById('blogStatsContent');
    
    // Show loading
    content.innerHTML = `
        <div class="text-center p-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    `;
    
    modal.show();
    
    // Simulate loading stats (replace with actual AJAX call)
    setTimeout(() => {
        content.innerHTML = `
            <div class="row">
                <div class="col-md-6 mb-3">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h4 class="text-primary">245</h4>
                            <small>Total Views</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h4 class="text-success">18</h4>
                            <small>Comments</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h4 class="text-danger">32</h4>
                            <small>Likes</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h4 class="text-info">15</h4>
                            <small>Shares</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mt-3">
                <h6>Recent Activity</h6>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">New comment from John Doe - 2 hours ago</li>
                    <li class="list-group-item">Blog liked by Jane Smith - 5 hours ago</li>
                    <li class="list-group-item">Blog viewed 15 times - Today</li>
                </ul>
            </div>
        `;
    }, 1000);
}

function exportBlogs() {
    // This would trigger a CSV/Excel export
    alert('Export functionality will be implemented to generate CSV/Excel files.');
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