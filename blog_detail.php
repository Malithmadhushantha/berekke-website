<?php
require_once 'config/config.php';

// Get blog ID
$blog_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$blog_id) {
    header('Location: blogs.php');
    exit();
}

// Fetch blog with author information
$blog_sql = "SELECT b.*, u.first_name, u.last_name, u.profile_picture,
             (SELECT COUNT(*) FROM blog_likes WHERE blog_id = b.id) as likes_count,
             (SELECT COUNT(*) FROM blog_comments WHERE blog_id = b.id AND status = 'approved') as comments_count
             FROM blogs b 
             JOIN users u ON b.user_id = u.id 
             WHERE b.id = ? AND b.status = 'published'";

$stmt = $pdo->prepare($blog_sql);
$stmt->execute([$blog_id]);
$blog = $stmt->fetch();

if (!$blog) {
    header('Location: blogs.php');
    exit();
}

// Update view count
$stmt = $pdo->prepare("UPDATE blogs SET views = views + 1 WHERE id = ?");
$stmt->execute([$blog_id]);

// Check if user has liked this blog
$user_liked = false;
if (isLoggedIn()) {
    $stmt = $pdo->prepare("SELECT id FROM blog_likes WHERE blog_id = ? AND user_id = ?");
    $stmt->execute([$blog_id, $_SESSION['user_id']]);
    $user_liked = (bool)$stmt->fetch();
}

// Handle like toggle
if (isset($_POST['toggle_like']) && isLoggedIn()) {
    $user_id = $_SESSION['user_id'];
    
    if ($user_liked) {
        // Remove like
        $stmt = $pdo->prepare("DELETE FROM blog_likes WHERE blog_id = ? AND user_id = ?");
        $stmt->execute([$blog_id, $user_id]);
        $user_liked = false;
        $blog['likes_count']--;
    } else {
        // Add like
        $stmt = $pdo->prepare("INSERT INTO blog_likes (blog_id, user_id) VALUES (?, ?)");
        $stmt->execute([$blog_id, $user_id]);
        $user_liked = true;
        $blog['likes_count']++;
    }
    
    echo json_encode([
        'status' => 'success',
        'liked' => $user_liked,
        'likes_count' => $blog['likes_count']
    ]);
    exit();
}

// Handle comment submission
if (isset($_POST['submit_comment']) && isLoggedIn()) {
    $comment = cleanInput($_POST['comment']);
    $user_id = $_SESSION['user_id'];
    
    if (!empty($comment)) {
        $stmt = $pdo->prepare("INSERT INTO blog_comments (blog_id, user_id, comment, status) VALUES (?, ?, ?, 'approved')");
        $stmt->execute([$blog_id, $user_id, $comment]);
        
        $_SESSION['comment_success'] = 'Comment posted successfully!';
        header('Location: blog_detail.php?id=' . $blog_id . '#comments');
        exit();
    }
}

// Fetch approved comments
$comments_sql = "SELECT bc.*, u.first_name, u.last_name, u.profile_picture 
                FROM blog_comments bc 
                JOIN users u ON bc.user_id = u.id 
                WHERE bc.blog_id = ? AND bc.status = 'approved' 
                ORDER BY bc.created_at DESC";
$stmt = $pdo->prepare($comments_sql);
$stmt->execute([$blog_id]);
$comments = $stmt->fetchAll();

// Fetch related blogs
$related_sql = "SELECT b.*, u.first_name, u.last_name 
               FROM blogs b 
               JOIN users u ON b.user_id = u.id 
               WHERE b.id != ? AND b.status = 'published' 
               ORDER BY b.created_at DESC 
               LIMIT 3";
$stmt = $pdo->prepare($related_sql);
$stmt->execute([$blog_id]);
$related_blogs = $stmt->fetchAll();

$page_title = $blog['title'];
include 'includes/header.php';
?>

<div class="container py-4">
    <!-- Back Button -->
    <div class="row mb-4">
        <div class="col-12">
            <a href="blogs.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Blogs
            </a>
        </div>
    </div>

    <!-- Blog Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="blog-header">
                <h1 class="display-5 fw-bold mb-3"><?php echo htmlspecialchars($blog['title']); ?></h1>
                
                <div class="d-flex align-items-center mb-4">
                    <img src="<?php echo PROFILE_PICS_PATH . $blog['profile_picture']; ?>" 
                         class="rounded-circle me-3" width="50" height="50" alt="Author">
                    <div class="flex-grow-1">
                        <h6 class="mb-0"><?php echo htmlspecialchars($blog['first_name'] . ' ' . $blog['last_name']); ?></h6>
                        <small class="text-muted">
                            Published on <?php echo date('F j, Y \a\t g:i A', strtotime($blog['created_at'])); ?>
                        </small>
                    </div>
                    <div class="blog-stats">
                        <span class="badge bg-primary me-2">
                            <i class="fas fa-eye me-1"></i><?php echo number_format($blog['views']); ?> views
                        </span>
                        <span class="badge bg-success me-2">
                            <i class="fas fa-heart me-1"></i><?php echo number_format($blog['likes_count']); ?> likes
                        </span>
                        <span class="badge bg-info">
                            <i class="fas fa-comment me-1"></i><?php echo number_format($blog['comments_count']); ?> comments
                        </span>
                    </div>
                </div>

                <!-- Social Share & Like -->
                <div class="d-flex align-items-center justify-content-between mb-4 py-3 border-top border-bottom">
                    <div class="social-share">
                        <span class="text-muted me-3">Share:</span>
                        <button class="btn btn-outline-primary btn-sm me-2" onclick="shareOnFacebook()">
                            <i class="fab fa-facebook-f me-1"></i>Facebook
                        </button>
                        <button class="btn btn-outline-info btn-sm me-2" onclick="shareOnTwitter()">
                            <i class="fab fa-twitter me-1"></i>Twitter
                        </button>
                        <button class="btn btn-outline-success btn-sm" onclick="copyLink()">
                            <i class="fas fa-link me-1"></i>Copy Link
                        </button>
                    </div>
                    
                    <?php if (isLoggedIn()): ?>
                    <button class="btn <?php echo $user_liked ? 'btn-danger' : 'btn-outline-danger'; ?>" 
                            onclick="toggleLike()" id="likeButton">
                        <i class="fas fa-heart me-1"></i>
                        <span id="likeText"><?php echo $user_liked ? 'Liked' : 'Like'; ?></span>
                        (<span id="likeCount"><?php echo number_format($blog['likes_count']); ?></span>)
                    </button>
                    <?php else: ?>
                    <a href="login.php" class="btn btn-outline-danger">
                        <i class="fas fa-heart me-1"></i>Login to Like
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Blog Content -->
    <div class="row">
        <div class="col-lg-8">
            <!-- Featured Image -->
            <?php if (!empty($blog['featured_image'])): ?>
            <div class="mb-4">
                <img src="<?php echo BLOG_IMAGES_PATH . $blog['featured_image']; ?>" 
                     class="img-fluid rounded shadow-sm" alt="Blog featured image">
            </div>
            <?php endif; ?>

            <!-- Blog Content -->
            <div class="blog-content mb-5">
                <div class="content-text">
                    <?php echo nl2br(htmlspecialchars($blog['content'])); ?>
                </div>
            </div>

            <!-- Comments Section -->
            <div class="comments-section" id="comments">
                <h4 class="mb-4">
                    <i class="fas fa-comments me-2"></i>
                    Comments (<?php echo count($comments); ?>)
                </h4>

                <!-- Comment Form -->
                <?php if (isLoggedIn()): ?>
                <div class="comment-form mb-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <?php if (isset($_SESSION['comment_success'])): ?>
                            <div class="alert alert-success alert-dismissible fade show">
                                <?php echo $_SESSION['comment_success']; unset($_SESSION['comment_success']); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                            <?php endif; ?>
                            
                            <form method="POST" action="">
                                <div class="d-flex">
                                    <img src="<?php echo PROFILE_PICS_PATH . $user['profile_picture']; ?>" 
                                         class="rounded-circle me-3" width="40" height="40" alt="Your avatar">
                                    <div class="flex-grow-1">
                                        <textarea class="form-control" name="comment" rows="3" 
                                                placeholder="Write your comment..." required></textarea>
                                        <div class="d-flex justify-content-end mt-2">
                                            <button type="submit" name="submit_comment" class="btn btn-primary">
                                                <i class="fas fa-paper-plane me-1"></i>Post Comment
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <?php else: ?>
                <div class="comment-login mb-4">
                    <div class="card border-0 bg-light">
                        <div class="card-body text-center">
                            <p class="mb-2">Join the conversation!</p>
                            <a href="login.php" class="btn btn-primary">
                                <i class="fas fa-sign-in-alt me-1"></i>Login to Comment
                            </a>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Comments List -->
                <div class="comments-list">
                    <?php if (!empty($comments)): ?>
                        <?php foreach ($comments as $comment): ?>
                        <div class="comment-item mb-3">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body">
                                    <div class="d-flex">
                                        <img src="<?php echo PROFILE_PICS_PATH . $comment['profile_picture']; ?>" 
                                             class="rounded-circle me-3" width="40" height="40" alt="Commenter avatar">
                                        <div class="flex-grow-1">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <h6 class="mb-0"><?php echo htmlspecialchars($comment['first_name'] . ' ' . $comment['last_name']); ?></h6>
                                                <small class="text-muted"><?php echo date('M j, Y \a\t g:i A', strtotime($comment['created_at'])); ?></small>
                                            </div>
                                            <p class="mb-0"><?php echo nl2br(htmlspecialchars($comment['comment'])); ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-comment-slash fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No comments yet. Be the first to share your thoughts!</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <div class="sidebar">
                <!-- Author Info -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0">
                            <i class="fas fa-user me-2"></i>About the Author
                        </h6>
                    </div>
                    <div class="card-body text-center">
                        <img src="<?php echo PROFILE_PICS_PATH . $blog['profile_picture']; ?>" 
                             class="rounded-circle mb-3" width="80" height="80" alt="Author">
                        <h6><?php echo htmlspecialchars($blog['first_name'] . ' ' . $blog['last_name']); ?></h6>
                        <p class="text-muted small">Law enforcement professional sharing insights and experiences.</p>
                        <button class="btn btn-primary btn-sm">
                            <i class="fas fa-user-plus me-1"></i>Follow
                        </button>
                    </div>
                </div>

                <!-- Related Posts -->
                <?php if (!empty($related_blogs)): ?>
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">
                            <i class="fas fa-newspaper me-2"></i>Related Posts
                        </h6>
                    </div>
                    <div class="card-body p-0">
                        <?php foreach ($related_blogs as $related): ?>
                        <div class="related-post p-3 border-bottom">
                            <h6 class="mb-1">
                                <a href="blog_detail.php?id=<?php echo $related['id']; ?>" class="text-decoration-none">
                                    <?php echo htmlspecialchars($related['title']); ?>
                                </a>
                            </h6>
                            <small class="text-muted">
                                By <?php echo htmlspecialchars($related['first_name'] . ' ' . $related['last_name']); ?>
                            </small>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Popular Tags -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">
                            <i class="fas fa-tags me-2"></i>Popular Tags
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex flex-wrap gap-2">
                            <span class="badge bg-primary">Criminal Law</span>
                            <span class="badge bg-success">Investigation</span>
                            <span class="badge bg-warning">Evidence</span>
                            <span class="badge bg-info">Legal Updates</span>
                            <span class="badge bg-danger">Case Studies</span>
                            <span class="badge bg-secondary">Training</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.blog-header {
    border-bottom: 1px solid #dee2e6;
    padding-bottom: 2rem;
    margin-bottom: 2rem;
}

.blog-content {
    font-size: 1.1rem;
    line-height: 1.8;
    color: #333;
}

.content-text {
    text-align: justify;
}

.comment-item {
    transition: all 0.3s ease;
}

.comment-item:hover {
    transform: translateX(5px);
}

.related-post {
    transition: background-color 0.3s ease;
}

.related-post:hover {
    background-color: #f8f9fa;
}

.related-post:last-child {
    border-bottom: none !important;
}

.social-share button {
    transition: transform 0.2s ease;
}

.social-share button:hover {
    transform: translateY(-2px);
}

@media (max-width: 768px) {
    .blog-stats {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .d-flex.justify-content-between {
        flex-direction: column;
        gap: 1rem;
    }
    
    .social-share {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }
}
</style>

<script>
function toggleLike() {
    <?php if (!isLoggedIn()): ?>
    window.location.href = 'login.php';
    return;
    <?php endif; ?>
    
    const formData = new FormData();
    formData.append('toggle_like', '1');
    
    fetch('blog_detail.php?id=<?php echo $blog_id; ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            const likeButton = document.getElementById('likeButton');
            const likeText = document.getElementById('likeText');
            const likeCount = document.getElementById('likeCount');
            
            if (data.liked) {
                likeButton.className = 'btn btn-danger';
                likeText.textContent = 'Liked';
            } else {
                likeButton.className = 'btn btn-outline-danger';
                likeText.textContent = 'Like';
            }
            
            likeCount.textContent = data.likes_count.toLocaleString();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while updating the like status.');
    });
}

function shareOnFacebook() {
    const url = encodeURIComponent(window.location.href);
    const title = encodeURIComponent('<?php echo addslashes($blog['title']); ?>');
    window.open(`https://www.facebook.com/sharer/sharer.php?u=${url}`, '_blank', 'width=600,height=400');
}

function shareOnTwitter() {
    const url = encodeURIComponent(window.location.href);
    const title = encodeURIComponent('<?php echo addslashes($blog['title']); ?>');
    window.open(`https://twitter.com/intent/tweet?url=${url}&text=${title}`, '_blank', 'width=600,height=400');
}

function copyLink() {
    navigator.clipboard.writeText(window.location.href).then(() => {
        alert('Link copied to clipboard!');
    });
}

// Smooth scroll to comments when coming from hash
document.addEventListener('DOMContentLoaded', function() {
    if (window.location.hash === '#comments') {
        document.getElementById('comments').scrollIntoView({ behavior: 'smooth' });
    }
    
    // Auto-resize comment textarea
    const textarea = document.querySelector('textarea[name="comment"]');
    if (textarea) {
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });
    }
});

// Reading progress indicator
window.addEventListener('scroll', function() {
    const article = document.querySelector('.blog-content');
    if (!article) return;
    
    const articleTop = article.offsetTop;
    const articleHeight = article.offsetHeight;
    const viewportHeight = window.innerHeight;
    const scrollTop = window.pageYOffset;
    
    const progress = Math.min(
        Math.max((scrollTop - articleTop + viewportHeight / 2) / articleHeight, 0),
        1
    );
    
    // You can use this progress value to show a reading progress bar
    console.log('Reading progress:', Math.round(progress * 100) + '%');
});
</script>

<?php include 'includes/footer.php'; ?>