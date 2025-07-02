<?php
$page_title = "Home";
include 'includes/header.php';
?>

<!-- Hero Section -->
<section class="hero-section bg-primary text-white py-5">
    <div class="container">
        <div class="row align-items-center min-vh-50">
            <div class="col-lg-6">
                <h1 class="display-4 fw-bold mb-4">
                    Welcome to <span class="text-warning">Berekke</span>
                </h1>
                <p class="lead mb-4">
                    Sri Lankan Police officers සඳහා නිර්මාණය කළ විශේෂ ඩිජිටල් වේදිකාවකි. 
                    නීතිමය ප්‍රලේখන, AI මෙවලම්, සහ අවශ්‍ය සියලුම සම්පත් එක තැනින්.
                </p>
                <div class="d-flex flex-wrap gap-3">
                    <?php if (!isLoggedIn()): ?>
                    <a href="register.php" class="btn btn-warning btn-lg px-4">
                        <i class="fas fa-user-plus me-2"></i>Get Started
                    </a>
                    <a href="login.php" class="btn btn-outline-light btn-lg px-4">
                        <i class="fas fa-sign-in-alt me-2"></i>Login
                    </a>
                    <?php else: ?>
                    <a href="ai_tools.php" class="btn btn-warning btn-lg px-4">
                        <i class="fas fa-robot me-2"></i>Explore Tools
                    </a>
                    <a href="penal_code.php" class="btn btn-outline-light btn-lg px-4">
                        <i class="fas fa-gavel me-2"></i>Legal Resources
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-lg-6 text-center">
                <img src="assets/images/hero-illustration.jpg" alt="Police Digital Platform" 
                     class="img-fluid" style="max-height: 400px;">
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">ප්‍රධාන විශේෂාංග</h2>
            <p class="text-muted">Our platform offers comprehensive tools for modern policing</p>
        </div>
        
        <div class="row g-4">
            <div class="col-lg-4 col-md-6">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center p-4">
                        <div class="feature-icon bg-primary text-white rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" 
                             style="width: 80px; height: 80px;">
                            <i class="fas fa-robot fa-2x"></i>
                        </div>
                        <h5 class="card-title">AI Tools</h5>
                        <p class="card-text text-muted">
                            Advanced AI-powered tools for document analysis, case management, and intelligent assistance.
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center p-4">
                        <div class="feature-icon bg-success text-white rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" 
                             style="width: 80px; height: 80px;">
                            <i class="fas fa-balance-scale fa-2x"></i>
                        </div>
                        <h5 class="card-title">Legal Database</h5>
                        <p class="card-text text-muted">
                            Complete access to Penal Code, Criminal Procedure Code, and Evidence Ordinance with search functionality.
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center p-4">
                        <div class="feature-icon bg-warning text-white rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" 
                             style="width: 80px; height: 80px;">
                            <i class="fas fa-download fa-2x"></i>
                        </div>
                        <h5 class="card-title">Resource Center</h5>
                        <p class="card-text text-muted">
                            Download forms, applications, presentations, and other essential resources for police work.
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center p-4">
                        <div class="feature-icon bg-info text-white rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" 
                             style="width: 80px; height: 80px;">
                            <i class="fas fa-file-alt fa-2x"></i>
                        </div>
                        <h5 class="card-title">Document Tools</h5>
                        <p class="card-text text-muted">
                            Create, edit, and manage official documents with professional templates and formatting.
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center p-4">
                        <div class="feature-icon bg-danger text-white rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" 
                             style="width: 80px; height: 80px;">
                            <i class="fas fa-chart-line fa-2x"></i>
                        </div>
                        <h5 class="card-title">Analytics</h5>
                        <p class="card-text text-muted">
                            Generate reports, charts, and analytics for case management and operational insights.
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center p-4">
                        <div class="feature-icon bg-secondary text-white rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" 
                             style="width: 80px; height: 80px;">
                            <i class="fas fa-users fa-2x"></i>
                        </div>
                        <h5 class="card-title">Community</h5>
                        <p class="card-text text-muted">
                            Connect with fellow officers, share knowledge, and stay updated with the latest developments.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Quick Access Section -->
<section class="bg-light py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Quick Access</h2>
            <p class="text-muted">Most used tools and resources</p>
        </div>
        
        <div class="row g-4">
            <div class="col-lg-3 col-md-6">
                <a href="penal_code.php" class="text-decoration-none">
                    <div class="card border-0 shadow-sm h-100 text-center quick-access-card">
                        <div class="card-body p-4">
                            <i class="fas fa-gavel fa-3x text-primary mb-3"></i>
                            <h6 class="card-title">Penal Code</h6>
                            <p class="card-text text-muted small">දණ්ඩ නීති සංග්‍රහය</p>
                        </div>
                    </div>
                </a>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <a href="criminal_procedure_code_act.php" class="text-decoration-none">
                    <div class="card border-0 shadow-sm h-100 text-center quick-access-card">
                        <div class="card-body p-4">
                            <i class="fas fa-clipboard-list fa-3x text-success mb-3"></i>
                            <h6 class="card-title">Criminal Procedure</h6>
                            <p class="card-text text-muted small">අපරාධ නඩු විධාන</p>
                        </div>
                    </div>
                </a>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <a href="evidence_ordinance.php" class="text-decoration-none">
                    <div class="card border-0 shadow-sm h-100 text-center quick-access-card">
                        <div class="card-body p-4">
                            <i class="fas fa-search fa-3x text-warning mb-3"></i>
                            <h6 class="card-title">Evidence Ordinance</h6>
                            <p class="card-text text-muted small">සාක්ෂි ආඥාපනත</p>
                        </div>
                    </div>
                </a>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <a href="downloads.php" class="text-decoration-none">
                    <div class="card border-0 shadow-sm h-100 text-center quick-access-card">
                        <div class="card-body p-4">
                            <i class="fas fa-download fa-3x text-info mb-3"></i>
                            <h6 class="card-title">Downloads</h6>
                            <p class="card-text text-muted small">ගොනු බාගත කිරීම්</p>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Recent Blogs Section -->
<section class="py-5">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-5">
            <div>
                <h2 class="fw-bold">Latest Updates</h2>
                <p class="text-muted">Stay informed with our latest posts</p>
            </div>
            <a href="blogs.php" class="btn btn-outline-primary">View All Blogs</a>
        </div>
        
        <?php
        // Fetch latest 3 blogs
        $stmt = $pdo->prepare("SELECT b.*, u.first_name, u.last_name FROM blogs b 
                              JOIN users u ON b.user_id = u.id 
                              WHERE b.status = 'published' 
                              ORDER BY b.created_at DESC LIMIT 3");
        $stmt->execute();
        $blogs = $stmt->fetchAll();
        ?>
        
        <div class="row g-4">
            <?php if (!empty($blogs)): ?>
                <?php foreach ($blogs as $blog): ?>
                <div class="col-lg-4 col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <?php if ($blog['featured_image']): ?>
                        <img src="<?php echo BLOG_IMAGES_PATH . $blog['featured_image']; ?>" 
                             class="card-img-top" style="height: 200px; object-fit: cover;" 
                             alt="<?php echo htmlspecialchars($blog['title']); ?>">
                        <?php endif; ?>
                        <div class="card-body d-flex flex-column">
                            <h6 class="card-title"><?php echo htmlspecialchars($blog['title']); ?></h6>
                            <p class="card-text text-muted flex-grow-1">
                                <?php echo htmlspecialchars(substr($blog['excerpt'] ?: $blog['content'], 0, 120)) . '...'; ?>
                            </p>
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    By <?php echo htmlspecialchars($blog['first_name'] . ' ' . $blog['last_name']); ?>
                                </small>
                                <a href="blog_detail.php?id=<?php echo $blog['id']; ?>" 
                                   class="btn btn-sm btn-outline-primary">Read More</a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center">
                    <p class="text-muted">No blogs available yet. Check back soon!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Statistics Section -->
<section class="bg-primary text-white py-5">
    <div class="container">
        <div class="row text-center">
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stat-item">
                    <h3 class="display-6 fw-bold text-warning" id="usersCount">0</h3>
                    <p>Registered Users</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stat-item">
                    <h3 class="display-6 fw-bold text-warning" id="sectionsCount">0</h3>
                    <p>Legal Sections</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stat-item">
                    <h3 class="display-6 fw-bold text-warning" id="downloadsCount">0</h3>
                    <p>Downloads Available</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stat-item">
                    <h3 class="display-6 fw-bold text-warning" id="blogsCount">0</h3>
                    <p>Blog Posts</p>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.min-vh-50 {
    min-height: 50vh;
}

.quick-access-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.quick-access-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 0.5rem 1.5rem rgba(0,0,0,0.15) !important;
}

.stat-item h3 {
    margin-bottom: 0.5rem;
}

@keyframes countUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.stat-item {
    animation: countUp 0.8s ease-out;
}
</style>

<script>
// Animated counters
function animateCounter(element, target, duration = 2000) {
    let start = 0;
    const increment = target / (duration / 16);
    
    const timer = setInterval(() => {
        start += increment;
        if (start >= target) {
            start = target;
            clearInterval(timer);
        }
        element.textContent = Math.floor(start).toLocaleString();
    }, 16);
}

// Initialize counters when in viewport
const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            // Fetch and animate actual counts
            <?php
            // Get actual counts
            $userCount = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
            $sectionsCount = $pdo->query("SELECT 
                (SELECT COUNT(*) FROM penal_code) + 
                (SELECT COUNT(*) FROM criminal_procedure_code) + 
                (SELECT COUNT(*) FROM evidence_ordinance) as total")->fetchColumn();
            $downloadsCount = $pdo->query("SELECT COUNT(*) FROM downloads")->fetchColumn();
            $blogsCount = $pdo->query("SELECT COUNT(*) FROM blogs WHERE status = 'published'")->fetchColumn();
            ?>
            
            animateCounter(document.getElementById('usersCount'), <?php echo $userCount; ?>);
            animateCounter(document.getElementById('sectionsCount'), <?php echo $sectionsCount; ?>);
            animateCounter(document.getElementById('downloadsCount'), <?php echo $downloadsCount; ?>);
            animateCounter(document.getElementById('blogsCount'), <?php echo $blogsCount; ?>);
            
            observer.unobserve(entry.target);
        }
    });
});

document.addEventListener('DOMContentLoaded', function() {
    const statsSection = document.querySelector('.bg-primary');
    if (statsSection) {
        observer.observe(statsSection);
    }
});
</script>

<?php include 'includes/footer.php'; ?>