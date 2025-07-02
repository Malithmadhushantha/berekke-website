<?php
require_once 'config/config.php';

$page_title = "Blogs";
$current_page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$items_per_page = 9;
$offset = ($current_page - 1) * $items_per_page;

// Fetch total published blogs count
$total_blogs = $pdo->query("SELECT COUNT(*) FROM blogs WHERE status = 'published'")->fetchColumn();

// Fetch blogs with author information
$blogs_sql = "SELECT b.*, u.first_name, u.last_name, u.profile_picture,
              (SELECT COUNT(*) FROM blog_likes WHERE blog_id = b.id) as likes_count,
              (SELECT COUNT(*) FROM blog_comments WHERE blog_id = b.id AND status = 'approved') as comments_count
              FROM blogs b 
              JOIN users u ON b.user_id = u.id 
              WHERE b.status = 'published' 
              ORDER BY b.created_at DESC 
              LIMIT :offset, :limit";

$stmt = $pdo->prepare($blogs_sql);
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->bindParam(':limit', $items_per_page, PDO::PARAM_INT);
$stmt->execute();
$blogs = $stmt->fetchAll();

// Calculate pagination
$total_pages = ceil($total_blogs / $items_per_page);

include 'includes/header.php';
?>

<div class="container py-4">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="bg-primary text-white rounded-4 p-4">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <h1 class="h2 mb-2">
                            <i class="fas fa-blog me-3"></i>
                            Berekke Blog
                        </h1>
                        <p class="mb-0 opacity-75">
                            Insights, updates, and knowledge sharing from the law enforcement community
                        </p>
                    </div>
                    <div class="col-lg-4 text-end">
                        <i class="fas fa-pen-alt fa-4x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Bar -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-0">Latest Posts</h4>
                    <small class="text-muted"><?php echo number_format($total_blogs); ?> articles published</small>
                </div>
                <div>
                    <?php if (isLoggedIn()): ?>
                        <a href="write_blog.php" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Write New Blog
                        </a>
                        <a href="my_blogs.php" class="btn btn-outline-primary ms-2">
                            <i class="fas fa-user-edit me-2"></i>My Blogs
                        </a>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-outline-primary">
                            <i class="fas fa-sign-in-alt me-2"></i>Login to Write
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Featured Blog (if exists) -->
    <?php if (!empty($blogs)): ?>
    <div class="row mb-5">
        <div class="col-12">
            <?php $featured = $blogs[0]; ?>
            <div class="card border-0 shadow-lg featured-blog">
                <div class="row g-0">
                    <div class="col-md-6">
                        <?php if (!empty($featured['featured_image'])): ?>
                            <img src="<?php echo BLOG_IMAGES_PATH . $featured['featured_image']; ?>" 
                                 class="img-fluid h-100 object-cover rounded-start" alt="Featured blog image">
                        <?php else: ?>
                            <div class="h-100 bg-light d-flex align-items-center justify-content-center rounded-start">
                                <i class="fas fa-image fa-4x text-muted"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <div class="card-body p-4 h-100 d-flex flex-column">
                            <div>
                                <span class="badge bg-primary mb-2">Featured</span>
                                <h3 class="card-title"><?php echo htmlspecialchars($featured['title']); ?></h3>
                                <p class="card-text text-muted">
                                    <?php echo htmlspecialchars(substr($featured['excerpt'] ?: $featured['content'], 0, 200)) . '...'; ?>
                                </p>
                            </div>
                            <div class="mt-auto">
                                <div class="d-flex align-items-center mb-3">
                                    <img src="<?php echo PROFILE_PICS_PATH . $featured['profile_picture']; ?>" 
                                         class="rounded-circle me-3" width="40" height="40" alt="Author">
                                    <div>
                                        <h6 class="mb-0"><?php echo htmlspecialchars($featured['first_name'] . ' ' . $featured['last_name']); ?></h6>
                                        <small class="text-muted"><?php echo date('M j, Y', strtotime($featured['created_at'])); ?></small>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <small class="text-muted me-3">
                                            <i class="fas fa-heart me-1"></i><?php echo number_format($featured['likes_count']); ?>
                                        </small>
                                        <small class="text-muted me-3">
                                            <i class="fas fa-comment me-1"></i><?php echo number_format($featured['comments_count']); ?>
                                        </small>
                                        <small class="text-muted">
                                            <i class="fas fa-eye me-1"></i><?php echo number_format($featured['views']); ?>
                                        </small>
                                    </div>
                                    <a href="blog_detail.php?id=<?php echo $featured['id']; ?>" class="btn btn-primary">
                                        Read All
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Blog Grid -->
    <?php if (!empty($blogs)): ?>
    <div class="row g-4">
        <?php 
        // Skip the first blog if it was featured
        $blog_list = count($blogs) > 1 ? array_slice($blogs, 1) : [];
        foreach ($blog_list as $blog): 
        ?>
        <div class="col-lg-4 col-md-6">
            <div class="card h-100 border-0 shadow-sm blog-card">
                <div class="blog-image-container">
                    <?php if (!empty($blog['featured_image'])): ?>
                        <img src="<?php echo BLOG_IMAGES_PATH . $blog['featured_image']; ?>" 
                             class="card-img-top blog-image" alt="Blog image">
                    <?php else: ?>
                        <div class="card-img-top blog-image bg-light d-flex align-items-center justify-content-center">
                            <i class="fas fa-image fa-3x text-muted"></i>
                        </div>
                    <?php endif; ?>
                    <div class="blog-overlay">
                        <a href="blog_detail.php?id=<?php echo $blog['id']; ?>" class="btn btn-light btn-sm">
                            <i class="fas fa-eye me-1"></i>Read More
                        </a>
                    </div>
                </div>
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title">
                        <a href="blog_detail.php?id=<?php echo $blog['id']; ?>" class="text-decoration-none">
                            <?php echo htmlspecialchars($blog['title']); ?>
                        </a>
                    </h5>
                    <p class="card-text text-muted flex-grow-1">
                        <?php 
                        $excerpt = $blog['excerpt'] ?: $blog['content'];
                        echo htmlspecialchars(substr($excerpt, 0, 120)) . '...'; 
                        ?>
                    </p>
                    <div class="card-meta">
                        <div class="d-flex align-items-center mb-3">
                            <img src="<?php echo PROFILE_PICS_PATH . $blog['profile_picture']; ?>" 
                                 class="rounded-circle me-2" width="30" height="30" alt="Author">
                            <div class="flex-grow-1">
                                <small class="fw-semibold"><?php echo htmlspecialchars($blog['first_name'] . ' ' . $blog['last_name']); ?></small>
                                <br>
                                <small class="text-muted"><?php echo date('M j, Y', strtotime($blog['created_at'])); ?></small>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted me-3">
                                    <i class="fas fa-heart me-1"></i><?php echo number_format($blog['likes_count']); ?>
                                </small>
                                <small class="text-muted">
                                    <i class="fas fa-comment me-1"></i><?php echo number_format($blog['comments_count']); ?>
                                </small>
                            </div>
                            <a href="blog_detail.php?id=<?php echo $blog['id']; ?>" class="btn btn-primary btn-sm">
                                Read All
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
    <div class="row mt-5">
        <div class="col-12">
            <nav aria-label="Blog pagination">
                <ul class="pagination justify-content-center">
                    <?php if ($current_page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo $current_page - 1; ?>">
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
                        <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                    </li>
                    <?php endfor; ?>
                    
                    <?php if ($current_page < $total_pages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo $current_page + 1; ?>">
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
                <h4>No Blog Posts Yet</h4>
                <p class="text-muted">Be the first to share your insights with the community!</p>
                <?php if (isLoggedIn()): ?>
                    <a href="write_blog.php" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Write First Blog
                    </a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-primary">
                        <i class="fas fa-sign-in-alt me-2"></i>Login to Write
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Popular Tags -->
    <div class="row mt-5">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="fas fa-tags me-2"></i>
                        Popular Topics
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-wrap gap-2">
                        <span class="badge bg-primary fs-6 p-2">Criminal Law</span>
                        <span class="badge bg-success fs-6 p-2">Investigation</span>
                        <span class="badge bg-warning fs-6 p-2">Evidence</span>
                        <span class="badge bg-info fs-6 p-2">Court Procedures</span>
                        <span class="badge bg-danger fs-6 p-2">Case Studies</span>
                        <span class="badge bg-secondary fs-6 p-2">Legal Updates</span>
                        <span class="badge bg-dark fs-6 p-2">Training</span>
                        <span class="badge bg-primary fs-6 p-2">Technology</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Newsletter Subscription -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card bg-primary text-white border-0">
                <div class="card-body text-center p-4">
                    <h5>Stay Updated</h5>
                    <p class="mb-3">Subscribe to our newsletter for the latest blog posts and updates</p>
                    <div class="row justify-content-center">
                        <div class="col-lg-6">
                            <div class="input-group">
                                <input type="email" class="form-control" placeholder="Enter your email">
                                <button class="btn btn-light text-primary" type="button">
                                    <i class="fas fa-paper-plane me-1"></i>Subscribe
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.featured-blog {
    transition: all 0.3s ease;
}

.featured-blog:hover {
    transform: translateY(-5px);
}

.blog-card {
    transition: all 0.3s ease;
    overflow: hidden;
}

.blog-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 0.5rem 1.5rem rgba(0,0,0,0.15) !important;
}

.blog-image-container {
    position: relative;
    overflow: hidden;
}

.blog-image {
    height: 200px;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.blog-card:hover .blog-image {
    transform: scale(1.05);
}

.blog-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.7);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.blog-card:hover .blog-overlay {
    opacity: 1;
}

.object-cover {
    object-fit: cover;
}

.card-meta {
    margin-top: auto;
}

@media (max-width: 768px) {
    .featured-blog .row {
        flex-direction: column-reverse;
    }
    
    .blog-image {
        height: 150px;
    }
}
</style>

<script>
// Animate cards on load
document.addEventListener('DOMContentLoaded', function() {
    const cards = document.querySelectorAll('.blog-card, .featured-blog');
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

// Newsletter subscription
document.querySelector('.input-group button')?.addEventListener('click', function() {
    const email = this.previousElementSibling.value;
    if (email && email.includes('@')) {
        alert('Thank you for subscribing! We\'ll keep you updated with the latest posts.');
        this.previousElementSibling.value = '';
    } else {
        alert('Please enter a valid email address.');
    }
});

// Tag click handling
document.querySelectorAll('.badge').forEach(badge => {
    badge.style.cursor = 'pointer';
    badge.addEventListener('click', function() {
        const tag = this.textContent.trim();
        alert(`Searching for posts tagged with "${tag}"`);
        // Implement tag filtering here
    });
});

// Smooth scroll for Read All buttons
document.querySelectorAll('a[href*="blog_detail.php"]').forEach(link => {
    link.addEventListener('click', function(e) {
        // Optional: Add reading analytics here
        console.log('Blog link clicked:', this.href);
    });
});

// Intersection Observer for blog cards animation
if (window.IntersectionObserver) {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, {
        threshold: 0.1
    });
    
    document.querySelectorAll('.blog-card').forEach(card => {
        observer.observe(card);
    });
}
</script>

<?php include 'includes/footer.php'; ?>