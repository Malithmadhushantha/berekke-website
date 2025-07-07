<?php
require_once 'config/config.php';

// Helper function to get correct profile picture path
function getProfilePicturePath($profile_picture) {
    if (empty($profile_picture) || $profile_picture === 'default_avatar.jpg') {
        return 'assets/images/avatars/default_avatar.jpg';
    }
    
    if (file_exists(PROFILE_PICS_PATH . $profile_picture)) {
        return PROFILE_PICS_PATH . $profile_picture;
    }
    
    if (file_exists('assets/images/avatars/' . $profile_picture)) {
        return 'assets/images/avatars/' . $profile_picture;
    }
    
    return 'assets/images/avatars/default_avatar.jpg';
}

$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_testimonial'])) {
    $name = cleanInput($_POST['name']);
    $email = cleanInput($_POST['email']);
    $position = cleanInput($_POST['position']);
    $organization = cleanInput($_POST['organization']);
    $rating = intval($_POST['rating']);
    $comment = cleanInput($_POST['comment']);
    
    if (empty($name) || empty($comment) || $rating < 1 || $rating > 5) {
        $error_message = 'Please fill in all required fields and provide a valid rating.';
    } else {
        try {
            $user_id = isLoggedIn() ? $_SESSION['user_id'] : null;
            $profile_picture = 'default_avatar.jpg';
            
            if ($user_id) {
                $stmt = $pdo->prepare("SELECT profile_picture FROM users WHERE id = ?");
                $stmt->execute([$user_id]);
                $user_data = $stmt->fetch();
                if ($user_data && !empty($user_data['profile_picture'])) {
                    $profile_picture = $user_data['profile_picture'];
                }
            }
            
            $stmt = $pdo->prepare("INSERT INTO testimonials (user_id, name, email, position, organization, rating, comment, profile_picture) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$user_id, $name, $email, $position, $organization, $rating, $comment, $profile_picture]);
            
            $success_message = 'Thank you for your testimonial! It will be reviewed and published soon.';
            $_POST = array();
        } catch (PDOException $e) {
            $error_message = 'Error submitting testimonial. Please try again.';
        }
    }
}

$stmt = $pdo->prepare("SELECT * FROM testimonials WHERE status = 'approved' ORDER BY is_featured DESC, created_at DESC LIMIT 10");
$stmt->execute();
$testimonials = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT * FROM testimonials WHERE status = 'approved' AND is_featured = 1 ORDER BY created_at DESC");
$stmt->execute();
$featured_testimonials = $stmt->fetchAll();

$page_title = "About Us";
include 'includes/header.php';
?>

<!-- Hero Section -->
<section class="hero-section bg-gradient-primary text-white py-6 py-lg-8">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10 text-center">
                <h1 class="display-3 fw-bold mb-4">About Berekke</h1>
                <p class="lead mb-5">Digital Transformation for Sri Lankan Law Enforcement</p>
                <div class="max-w-800 mx-auto">
                    <p class="mb-4">Empowering Sri Lankan law enforcement with digital tools and comprehensive legal resources designed to enhance operational efficiency and effectiveness.</p>
                    <p class="mb-0">වර්තමාන ඩිජිටල් යුගයේ නීතිමය ක්ෂේත්‍රයේ කාර්යක්ෂමතාව වැඩි දියුණු කිරීම සඳහා නිර්මාණය කරන ලද විශේෂ වේදිකාවකි.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Mission Section -->
<section class="py-6 py-lg-8">
    <div class="container">
        <div class="row justify-content-center mb-6">
            <div class="col-lg-8 text-center">
                <div class="section-header">
                    <span class="badge bg-primary-soft mb-3">Our Purpose</span>
                    <h2 class="display-5 fw-bold mb-3">Revolutionizing Law Enforcement</h2>
                    <p class="lead text-muted">Innovative digital solutions designed specifically for Sri Lankan police professionals</p>
                </div>
            </div>
        </div>
        
        <div class="row g-4">
            <div class="col-md-4">
                <div class="feature-card p-4 h-100">
                    <div class="icon-circle bg-primary text-white mb-4">
                        <i class="fas fa-shield-alt fs-3"></i>
                    </div>
                    <h4 class="mb-3">Security First</h4>
                    <p class="text-muted mb-0">Enterprise-grade security protocols to protect sensitive law enforcement data and operations.</p>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="feature-card p-4 h-100">
                    <div class="icon-circle bg-success text-white mb-4">
                        <i class="fas fa-users fs-3"></i>
                    </div>
                    <h4 class="mb-3">User-Centric Design</h4>
                    <p class="text-muted mb-0">Intuitive interfaces tailored to the specific needs of police officers in the field.</p>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="feature-card p-4 h-100">
                    <div class="icon-circle bg-info text-white mb-4">
                        <i class="fas fa-rocket fs-3"></i>
                    </div>
                    <h4 class="mb-3">Cutting-Edge Technology</h4>
                    <p class="text-muted mb-0">Leveraging the latest advancements in web and mobile technologies.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Developer Profile -->
<section class="py-6 py-lg-8 bg-light">
    <div class="container">
        <div class="row justify-content-center mb-6">
            <div class="col-lg-8 text-center">
                <div class="section-header">
                    <span class="badge bg-primary-soft mb-3">The Developer</span>
                    <h2 class="display-5 fw-bold mb-3">Meet the Creator</h2>
                    <p class="lead text-muted">Police Constable & Full-Stack Developer</p>
                </div>
            </div>
        </div>
        
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card border-0 shadow-sm overflow-hidden">
                    <div class="row g-0">
                        <div class="col-md-5 bg-primary-soft">
                            <div class="p-5 d-flex flex-column h-100 align-items-center justify-content-center text-center">
                                <div class="avatar-xl mb-4">
                                    <img src="assets/images/developer/malith_madhushantha.jpg" 
                                         alt="Malith Madhushantha" 
                                         class="img-fluid rounded-circle border border-4 border-white"
                                         onerror="this.src='assets/images/developer/default_developer.jpg'">
                                </div>
                                <h3 class="mb-1">Malith Madhushantha</h3>
                                <p class="text-muted mb-4">Police Constable & Developer</p>
                                
                                <div class="d-flex gap-3 mb-4">
                                    <a href="tel:+94719803639" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                        <i class="fas fa-phone me-2"></i>Call
                                    </a>
                                    <a href="mailto:malith@berekke.lk" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                        <i class="fas fa-envelope me-2"></i>Email
                                    </a>
                                </div>
                                
                                <div class="w-100">
                                    <h6 class="text-uppercase text-muted mb-3">Connect</h6>
                                    <div class="d-flex justify-content-center gap-3">
                                        <a href="#" class="text-muted hover-primary">
                                            <i class="fab fa-linkedin-in fs-5"></i>
                                        </a>
                                        <a href="#" class="text-muted hover-primary">
                                            <i class="fab fa-github fs-5"></i>
                                        </a>
                                        <a href="#" class="text-muted hover-primary">
                                            <i class="fab fa-twitter fs-5"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-7">
                            <div class="p-5">
                                <h4 class="mb-4">Professional Background</h4>
                                <p class="mb-4">A dedicated law enforcement officer with a passion for technology and innovation. Combining field experience in policing with advanced technical skills to create practical solutions for the Sri Lankan Police Force.</p>
                                
                                <div class="mb-5">
                                    <h5 class="mb-3">
                                        <i class="fas fa-graduation-cap text-primary me-2"></i>
                                        Education & Qualifications
                                    </h5>
                                    <ul class="list-unstyled">
                                        <li class="mb-3">
                                            <div class="d-flex">
                                                <div class="me-3">
                                                    <i class="fas fa-certificate text-primary"></i>
                                                </div>
                                                <div>
                                                    <h6 class="mb-0">Diploma in Human Resource Management</h6>
                                                    <small class="text-muted">University of Colombo</small>
                                                </div>
                                            </div>
                                        </li>
                                        <li class="mb-3">
                                            <div class="d-flex">
                                                <div class="me-3">
                                                    <i class="fas fa-laptop-code text-primary"></i>
                                                </div>
                                                <div>
                                                    <h6 class="mb-0">Diploma in Information Technology</h6>
                                                    <small class="text-muted">National Youth Services Council</small>
                                                </div>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="d-flex">
                                                <div class="me-3">
                                                    <i class="fas fa-code text-primary"></i>
                                                </div>
                                                <div>
                                                    <h6 class="mb-0">MERN Full Stack Development</h6>
                                                    <small class="text-muted">Skyrek - Web Development</small>
                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                                
                                <div class="skills-section">
                                    <h5 class="mb-3">
                                        <i class="fas fa-tools text-primary me-2"></i>
                                        Technical Expertise
                                    </h5>
                                    <div class="d-flex flex-wrap gap-2">
                                        <span class="badge bg-primary-soft text-primary">PHP</span>
                                        <span class="badge bg-primary-soft text-primary">MySQL</span>
                                        <span class="badge bg-primary-soft text-primary">JavaScript</span>
                                        <span class="badge bg-primary-soft text-primary">React.js</span>
                                        <span class="badge bg-primary-soft text-primary">Node.js</span>
                                        <span class="badge bg-primary-soft text-primary">MongoDB</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="py-6 py-lg-8">
    <div class="container">
        <div class="row justify-content-center mb-6">
            <div class="col-lg-8 text-center">
                <div class="section-header">
                    <span class="badge bg-primary-soft mb-3">Capabilities</span>
                    <h2 class="display-5 fw-bold mb-3">Platform Features</h2>
                    <p class="lead text-muted">Comprehensive tools designed for modern law enforcement needs</p>
                </div>
            </div>
        </div>
        
        <div class="row g-4">
            <div class="col-md-6">
                <div class="feature-card-large p-5 h-100">
                    <div class="d-flex mb-4">
                        <div class="icon-circle-lg bg-primary text-white me-4 flex-shrink-0">
                            <i class="fas fa-database fs-4"></i>
                        </div>
                        <div>
                            <h3 class="h4 mb-2">Legal Database</h3>
                            <p class="text-muted mb-0">Complete searchable database of Sri Lankan Penal Code with advanced search functionality.</p>
                        </div>
                    </div>
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            Instant search across all legal texts
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            Personal bookmarking system
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            Note-taking capabilities
                        </li>
                        <li>
                            <i class="fas fa-check-circle text-success me-2"></i>
                            Mobile-responsive design
                        </li>
                    </ul>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="feature-card-large p-5 h-100">
                    <div class="d-flex mb-4">
                        <div class="icon-circle-lg bg-success text-white me-4 flex-shrink-0">
                            <i class="fas fa-robot fs-4"></i>
                        </div>
                        <div>
                            <h3 class="h4 mb-2">AI Tools</h3>
                            <p class="text-muted mb-0">Advanced artificial intelligence tools for document analysis and case summarization.</p>
                        </div>
                    </div>
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            Document analysis and extraction
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            Automated case summarization
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            Legal research assistant
                        </li>
                        <li>
                            <i class="fas fa-check-circle text-success me-2"></i>
                            Evidence analyzer (coming soon)
                        </li>
                    </ul>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="feature-card-large p-5 h-100">
                    <div class="d-flex mb-4">
                        <div class="icon-circle-lg bg-info text-white me-4 flex-shrink-0">
                            <i class="fas fa-file-alt fs-4"></i>
                        </div>
                        <div>
                            <h3 class="h4 mb-2">Document Management</h3>
                            <p class="text-muted mb-0">Professional document creation with templates and digital signatures.</p>
                        </div>
                    </div>
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            Pre-built report templates
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            Advanced document editor
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            PDF generation capabilities
                        </li>
                        <li>
                            <i class="fas fa-check-circle text-success me-2"></i>
                            Digital signature support
                        </li>
                    </ul>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="feature-card-large p-5 h-100">
                    <div class="d-flex mb-4">
                        <div class="icon-circle-lg bg-warning text-white me-4 flex-shrink-0">
                            <i class="fas fa-chart-line fs-4"></i>
                        </div>
                        <div>
                            <h3 class="h4 mb-2">Analytics</h3>
                            <p class="text-muted mb-0">Powerful analytics and reporting tools for generating insights.</p>
                        </div>
                    </div>
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            Interactive chart generation
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            Case timeline visualization
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            Statistical analysis tools
                        </li>
                        <li>
                            <i class="fas fa-check-circle text-success me-2"></i>
                            Customizable dashboards
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials Section -->
<section class="py-6 py-lg-8 bg-light">
    <div class="container">
        <div class="row justify-content-center mb-6">
            <div class="col-lg-8 text-center">
                <div class="section-header">
                    <span class="badge bg-primary-soft mb-3">Feedback</span>
                    <h2 class="display-5 fw-bold mb-3">User Testimonials</h2>
                    <p class="lead text-muted">What law enforcement professionals say about our platform</p>
                </div>
            </div>
        </div>
        
        <?php if (!empty($featured_testimonials)): ?>
        <div class="row mb-6">
            <div class="col-12">
                <div class="testimonial-carousel">
                    <div class="swiper-container">
                        <div class="swiper-wrapper">
                            <?php foreach ($featured_testimonials as $testimonial): ?>
                            <div class="swiper-slide">
                                <div class="testimonial-card text-center p-5">
                                    <div class="avatar-lg mx-auto mb-4">
                                        <img src="<?php echo getProfilePicturePath($testimonial['profile_picture']); ?>" 
                                             alt="<?php echo htmlspecialchars($testimonial['name']); ?>"
                                             class="img-fluid rounded-circle border border-3 border-primary"
                                             onerror="this.src='assets/images/avatars/default_avatar.jpg'">
                                    </div>
                                    <div class="star-rating mb-4">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="fas fa-star <?php echo $i <= $testimonial['rating'] ? 'text-warning' : 'text-muted'; ?>"></i>
                                        <?php endfor; ?>
                                    </div>
                                    <blockquote class="mb-4">
                                        <p class="lead">"<?php echo htmlspecialchars($testimonial['comment']); ?>"</p>
                                    </blockquote>
                                    <footer class="blockquote-footer">
                                        <strong><?php echo htmlspecialchars($testimonial['name']); ?></strong>
                                        <?php if (!empty($testimonial['position'])): ?>
                                        <br>
                                        <cite title="Position"><?php echo htmlspecialchars($testimonial['position']); ?></cite>
                                        <?php endif; ?>
                                        <?php if (!empty($testimonial['organization'])): ?>
                                        <br>
                                        <small class="text-muted"><?php echo htmlspecialchars($testimonial['organization']); ?></small>
                                        <?php endif; ?>
                                    </footer>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="swiper-pagination"></div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="row">
            <div class="col-12">
                <h4 class="text-center mb-5">Recent Feedback</h4>
                <div class="row g-4">
                    <?php if (!empty($testimonials)): ?>
                        <?php foreach (array_slice($testimonials, 0, 6) as $testimonial): ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="testimonial-card-small p-4 h-100">
                                <div class="d-flex align-items-center mb-3">
                                    <img src="<?php echo getProfilePicturePath($testimonial['profile_picture']); ?>" 
                                         alt="<?php echo htmlspecialchars($testimonial['name']); ?>"
                                         class="rounded-circle me-3"
                                         style="width: 50px; height: 50px; object-fit: cover;"
                                         onerror="this.src='assets/images/avatars/default_avatar.jpg'">
                                    <div>
                                        <h6 class="mb-0"><?php echo htmlspecialchars($testimonial['name']); ?></h6>
                                        <small class="text-muted"><?php echo htmlspecialchars($testimonial['position']); ?></small>
                                    </div>
                                </div>
                                <div class="star-rating mb-3">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star <?php echo $i <= $testimonial['rating'] ? 'text-warning' : 'text-muted'; ?> fa-sm"></i>
                                    <?php endfor; ?>
                                </div>
                                <p class="text-muted small mb-0">
                                    "<?php echo htmlspecialchars(substr($testimonial['comment'], 0, 150)) . (strlen($testimonial['comment']) > 150 ? '...' : ''); ?>"
                                </p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12 text-center">
                            <div class="alert alert-info">
                                No testimonials yet. Be the first to share your experience!
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Testimonial Form Section -->
<section class="py-6 py-lg-8">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-primary text-white p-4">
                        <h3 class="mb-0 text-center">
                            <i class="fas fa-comment-alt me-2"></i>
                            Share Your Experience
                        </h3>
                    </div>
                    <div class="card-body p-5">
                        <?php if ($success_message): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            <?php echo $success_message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($error_message): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <?php echo $error_message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="">
                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="name" name="name" required
                                           value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                                </div>
                                <div class="col-md-6 mb-4">
                                    <label for="email" class="form-label">Email Address</label>
                                    <input type="email" class="form-control" id="email" name="email"
                                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                                </div>
                                <div class="col-md-6 mb-4">
                                    <label for="position" class="form-label">Position/Rank</label>
                                    <input type="text" class="form-control" id="position" name="position" 
                                           placeholder="e.g., Police Inspector"
                                           value="<?php echo isset($_POST['position']) ? htmlspecialchars($_POST['position']) : ''; ?>">
                                </div>
                                <div class="col-md-6 mb-4">
                                    <label for="organization" class="form-label">Organization</label>
                                    <input type="text" class="form-control" id="organization" name="organization" 
                                           placeholder="e.g., Colombo Police Station"
                                           value="<?php echo isset($_POST['organization']) ? htmlspecialchars($_POST['organization']) : ''; ?>">
                                </div>
                                <div class="col-12 mb-4">
                                    <label class="form-label">Rating <span class="text-danger">*</span></label>
                                    <div class="rating-input">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <input type="radio" id="star<?php echo $i; ?>" name="rating" value="<?php echo $i; ?>" 
                                               <?php echo (isset($_POST['rating']) && $_POST['rating'] == $i) ? 'checked' : ''; ?> required>
                                        <label for="star<?php echo $i; ?>" class="star-label">
                                            <i class="fas fa-star"></i>
                                        </label>
                                        <?php endfor; ?>
                                    </div>
                                    <small class="text-muted">Click the stars to rate your experience</small>
                                </div>
                                <div class="col-12 mb-4">
                                    <label for="comment" class="form-label">Your Testimonial <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="comment" name="comment" rows="4" required
                                              placeholder="Share your experience with the Berekke platform..."><?php echo isset($_POST['comment']) ? htmlspecialchars($_POST['comment']) : ''; ?></textarea>
                                </div>
                            </div>
                            <div class="text-center mt-4">
                                <button type="submit" name="submit_testimonial" class="btn btn-primary btn-lg px-5">
                                    <i class="fas fa-paper-plane me-2"></i>
                                    Submit Testimonial
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Technology Stack -->
<section class="py-6 py-lg-8 bg-dark text-white">
    <div class="container">
        <div class="row justify-content-center mb-6">
            <div class="col-lg-8 text-center">
                <div class="section-header">
                    <span class="badge bg-primary mb-3">Technology</span>
                    <h2 class="display-5 fw-bold mb-3">Built With Modern Technologies</h2>
                    <p class="lead text-muted">Reliable, secure, and scalable technology stack</p>
                </div>
            </div>
        </div>
        
        <div class="row text-center g-4">
            <div class="col-md-3 col-6">
                <div class="tech-icon mx-auto mb-3">
                    <i class="fab fa-php fa-4x text-primary"></i>
                </div>
                <h5 class="mb-1">PHP 8.x</h5>
                <small class="text-muted">Backend Development</small>
            </div>
            
            <div class="col-md-3 col-6">
                <div class="tech-icon mx-auto mb-3">
                    <i class="fas fa-database fa-4x text-success"></i>
                </div>
                <h5 class="mb-1">MySQL 8.0</h5>
                <small class="text-muted">Database Management</small>
            </div>
            
            <div class="col-md-3 col-6">
                <div class="tech-icon mx-auto mb-3">
                    <i class="fab fa-bootstrap fa-4x text-info"></i>
                </div>
                <h5 class="mb-1">Bootstrap 5</h5>
                <small class="text-muted">Frontend Framework</small>
            </div>
            
            <div class="col-md-3 col-6">
                <div class="tech-icon mx-auto mb-3">
                    <i class="fab fa-js-square fa-4x text-warning"></i>
                </div>
                <h5 class="mb-1">JavaScript ES6+</h5>
                <small class="text-muted">Interactive Features</small>
            </div>
        </div>
        
        <div class="row mt-5 g-4">
            <div class="col-md-4">
                <div class="feature-card bg-dark-soft p-4 h-100 text-center">
                    <i class="fas fa-shield-alt fa-2x text-danger mb-3"></i>
                    <h5 class="mb-2">Advanced Security</h5>
                    <p class="text-muted small mb-0">Multi-layer protection for sensitive law enforcement data</p>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="feature-card bg-dark-soft p-4 h-100 text-center">
                    <i class="fas fa-mobile-alt fa-2x text-purple mb-3"></i>
                    <h5 class="mb-2">Mobile Responsive</h5>
                    <p class="text-muted small mb-0">Fully functional on all devices including smartphones</p>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="feature-card bg-dark-soft p-4 h-100 text-center">
                    <i class="fas fa-cloud fa-2x text-secondary mb-3"></i>
                    <h5 class="mb-2">Cloud Ready</h5>
                    <p class="text-muted small mb-0">Scalable deployment options for any organization size</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Development Approach -->
<section class="py-6 py-lg-8">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-5 mb-lg-0">
                <div class="pe-lg-5">
                    <span class="badge bg-primary-soft mb-3">Development</span>
                    <h2 class="display-5 fw-bold mb-4">Professional Development Standards</h2>
                    <p class="lead text-muted mb-5">Built with modern technologies and best practices for law enforcement solutions</p>
                    
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="d-flex">
                                <div class="me-4 text-primary">
                                    <i class="fas fa-laptop-code fa-2x"></i>
                                </div>
                                <div>
                                    <h5 class="mb-2">Full-Stack</h5>
                                    <p class="text-muted small mb-0">Complete web application development</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="d-flex">
                                <div class="me-4 text-success">
                                    <i class="fas fa-shield-alt fa-2x"></i>
                                </div>
                                <div>
                                    <h5 class="mb-2">Security</h5>
                                    <p class="text-muted small mb-0">Enterprise-grade security implementation</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="d-flex">
                                <div class="me-4 text-info">
                                    <i class="fas fa-brain fa-2x"></i>
                                </div>
                                <div>
                                    <h5 class="mb-2">AI Integration</h5>
                                    <p class="text-muted small mb-0">Artificial intelligence and machine learning</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="d-flex">
                                <div class="me-4 text-warning">
                                    <i class="fas fa-users fa-2x"></i>
                                </div>
                                <div>
                                    <h5 class="mb-2">User Experience</h5>
                                    <p class="text-muted small mb-0">User-centered design and usability</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6">
                <div class="development-image bg-primary-soft rounded-3 p-5 text-center">
                    <img src="assets/images/development.svg" alt="Development Process" class="img-fluid">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Contact Section -->
<section class="py-6 py-lg-8 bg-light">
    <div class="container">
        <div class="row justify-content-center mb-6">
            <div class="col-lg-8 text-center">
                <div class="section-header">
                    <span class="badge bg-primary-soft mb-3">Contact</span>
                    <h2 class="display-5 fw-bold mb-3">Get In Touch</h2>
                    <p class="lead text-muted">We'd love to hear from law enforcement professionals</p>
                </div>
            </div>
        </div>
        
        <div class="row g-4">
            <div class="col-md-4">
                <div class="contact-card p-4 h-100 text-center">
                    <div class="icon-circle-lg bg-primary text-white mx-auto mb-4">
                        <i class="fas fa-envelope fa-2x"></i>
                    </div>
                    <h5 class="mb-3">Email Us</h5>
                    <p class="text-muted mb-1">
                        <a href="mailto:info@berekke.lk" class="text-decoration-none">info@berekke.lk</a>
                    </p>
                    <p class="text-muted mb-0">
                        <a href="mailto:support@berekke.lk" class="text-decoration-none">support@berekke.lk</a>
                    </p>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="contact-card p-4 h-100 text-center">
                    <div class="icon-circle-lg bg-success text-white mx-auto mb-4">
                        <i class="fas fa-phone fa-2x"></i>
                    </div>
                    <h5 class="mb-3">Call Us</h5>
                    <p class="text-muted mb-1">+94 11 234 5678</p>
                    <p class="text-muted mb-0">+94 77 123 4567 (Mobile)</p>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="contact-card p-4 h-100 text-center">
                    <div class="icon-circle-lg bg-info text-white mx-auto mb-4">
                        <i class="fas fa-map-marker-alt fa-2x"></i>
                    </div>
                    <h5 class="mb-3">Visit Us</h5>
                    <p class="text-muted mb-1">Colombo, Sri Lanka</p>
                    <p class="text-muted mb-0">(Exact address available upon request)</p>
                </div>
            </div>
        </div>
        
        <div class="row mt-5">
            <div class="col-12">
                <div class="text-center">
                    <h5 class="mb-4">Follow Our Development</h5>
                    <div class="d-flex justify-content-center gap-4">
                        <a href="#" class="social-icon">
                            <i class="fab fa-github fa-2x"></i>
                        </a>
                        <a href="#" class="social-icon">
                            <i class="fab fa-linkedin fa-2x"></i>
                        </a>
                        <a href="#" class="social-icon">
                            <i class="fab fa-twitter fa-2x"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Legal Notice -->
<section class="py-6">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card border-warning">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0">
                            <i class="fas fa-gavel me-2"></i>
                            Legal Notice & Disclaimer
                        </h5>
                    </div>
                    <div class="card-body">
                        <p class="small text-muted mb-2">
                            <strong>Important:</strong> This website is designed as a tool to assist law enforcement professionals 
                            and should not be considered as official legal advice or a substitute for professional legal consultation. 
                            All legal information provided is based on Sri Lankan law as available at the time of development.
                        </p>
                        <p class="small text-muted mb-0">
                            Users are responsible for verifying the accuracy and currency of legal information before making any 
                            official decisions or taking legal action. The developers and administrators of this website assume 
                            no liability for any decisions made based on the information provided.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
/* Base Styles */
:root {
    --primary: #4e73df;
    --primary-soft: rgba(78, 115, 223, 0.1);
    --success: #1cc88a;
    --info: #36b9cc;
    --warning: #f6c23e;
    --danger: #e74a3b;
    --dark: #5a5c69;
    --light: #f8f9fc;
    --purple: #6f42c1;
}

body {
    font-family: 'Nunito', sans-serif;
    color: #5a5c69;
}

/* Section Styling */
section {
    position: relative;
}

.section-header {
    position: relative;
    z-index: 1;
}

/* Hero Section */
.hero-section {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    position: relative;
    overflow: hidden;
}

.hero-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-image: url('assets/images/pattern-shapes.png');
    opacity: 0.1;
    z-index: 0;
}

/* Cards */
.card {
    border: none;
    border-radius: 0.5rem;
    transition: all 0.3s ease;
}

.feature-card {
    background: white;
    border-radius: 0.5rem;
    transition: all 0.3s ease;
}

.feature-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 0.5rem 1.5rem rgba(0,0,0,0.1) !important;
}

.feature-card-large {
    background: white;
    border-radius: 0.5rem;
    box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.05);
    transition: all 0.3s ease;
}

.feature-card-large:hover {
    transform: translateY(-5px);
    box-shadow: 0 1rem 2rem rgba(0,0,0,0.1) !important;
}

.testimonial-card {
    background: white;
    border-radius: 0.5rem;
    box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.05);
    transition: all 0.3s ease;
}

.testimonial-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 1rem 2rem rgba(0,0,0,0.1) !important;
}

.testimonial-card-small {
    background: white;
    border-radius: 0.5rem;
    box-shadow: 0 0.25rem 0.5rem rgba(0,0,0,0.05);
    transition: all 0.3s ease;
}

.testimonial-card-small:hover {
    transform: translateY(-3px);
    box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.1) !important;
}

.contact-card {
    background: white;
    border-radius: 0.5rem;
    box-shadow: 0 0.25rem 0.5rem rgba(0,0,0,0.05);
    transition: all 0.3s ease;
}

.contact-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.1) !important;
}

/* Icons */
.icon-circle {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.icon-circle-lg {
    width: 70px;
    height: 70px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.tech-icon {
    width: 80px;
    height: 80px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
}

.tech-icon:hover {
    transform: scale(1.1) rotate(5deg);
}

.social-icon {
    color: var(--dark);
    transition: all 0.3s ease;
}

.social-icon:hover {
    color: var(--primary);
    transform: translateY(-3px);
}

/* Backgrounds */
.bg-primary-soft {
    background-color: var(--primary-soft);
}

.bg-dark-soft {
    background-color: rgba(0,0,0,0.05);
}

/* Typography */
.display-5 {
    font-size: 2.5rem;
    font-weight: 700;
}

.display-6 {
    font-size: 2rem;
    font-weight: 700;
}

.lead {
    font-size: 1.25rem;
    font-weight: 300;
}

.max-w-800 {
    max-width: 800px;
}

/* Rating Input */
.rating-input {
    display: flex;
    flex-direction: row-reverse;
    justify-content: flex-end;
    margin-bottom: 10px;
}

.rating-input input[type="radio"] {
    display: none;
}

.rating-input label {
    cursor: pointer;
    color: #ddd;
    font-size: 1.5rem;
    margin-right: 5px;
    transition: color 0.3s ease;
}

.rating-input label:hover,
.rating-input label:hover ~ label,
.rating-input input[type="radio"]:checked ~ label {
    color: #ffc107;
}

/* Star Rating */
.star-rating {
    color: #ffc107;
}

/* Testimonial Carousel */
.testimonial-carousel {
    position: relative;
}

.swiper-container {
    padding: 20px 0;
}

.swiper-slide {
    height: auto;
}

.swiper-pagination-bullet {
    width: 12px;
    height: 12px;
    background-color: rgba(0,0,0,0.2);
    opacity: 1;
}

.swiper-pagination-bullet-active {
    background-color: var(--primary);
}

/* Avatar Sizes */
.avatar-lg {
    width: 80px;
    height: 80px;
}

.avatar-xl {
    width: 120px;
    height: 120px;
}

/* Badges */
.badge {
    font-weight: 600;
    letter-spacing: 0.5px;
    padding: 0.5em 1em;
}

/* Hover Effects */
.hover-primary:hover {
    color: var(--primary) !important;
}

/* Responsive Adjustments */
@media (max-width: 768px) {
    .display-5 {
        font-size: 2rem;
    }
    
    .lead {
        font-size: 1.1rem;
    }
    
    .section-header {
        margin-bottom: 2rem;
    }
    
    .feature-card-large {
        padding: 2rem;
    }
    
    .testimonial-card {
        padding: 2rem;
    }
}

/* Animations */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fadeInUp {
    animation: fadeInUp 0.6s ease-out forwards;
}

.delay-1 {
    animation-delay: 0.1s;
}

.delay-2 {
    animation-delay: 0.2s;
}

.delay-3 {
    animation-delay: 0.3s;
}

.delay-4 {
    animation-delay: 0.4s;
}

.delay-5 {
    animation-delay: 0.5s;
}

.delay-6 {
    animation-delay: 0.6s;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize testimonial carousel if testimonials exist
    if (document.querySelector('.swiper-container')) {
        const swiper = new Swiper('.swiper-container', {
            loop: true,
            autoplay: {
                delay: 8000,
                disableOnInteraction: false,
            },
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
            },
            breakpoints: {
                768: {
                    slidesPerView: 1,
                    spaceBetween: 20
                },
                992: {
                    slidesPerView: 1,
                    spaceBetween: 30
                }
            }
        });
    }
    
    // Animate elements on scroll
    const animateOnScroll = function() {
        const elements = document.querySelectorAll('.animate-fadeInUp');
        elements.forEach(element => {
            const elementPosition = element.getBoundingClientRect().top;
            const windowHeight = window.innerHeight;
            
            if (elementPosition < windowHeight - 100) {
                element.style.opacity = '1';
                element.style.transform = 'translateY(0)';
            }
        });
    };
    
    // Set initial state for animated elements
    document.querySelectorAll('.animate-fadeInUp').forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(30px)';
        el.style.transition = 'all 0.6s ease-out';
    });
    
    // Add scroll event listener
    window.addEventListener('scroll', animateOnScroll);
    animateOnScroll(); // Run once on load
    
    // Form validation
    const testimonialForm = document.querySelector('form');
    if (testimonialForm) {
        testimonialForm.addEventListener('submit', function(e) {
            const name = document.getElementById('name').value.trim();
            const comment = document.getElementById('comment').value.trim();
            const rating = document.querySelector('input[name="rating"]:checked');
            
            if (!name || !comment || !rating) {
                e.preventDefault();
                alert('Please fill in all required fields (Name, Rating, and Comment).');
                return false;
            }
            
            if (comment.length < 10) {
                e.preventDefault();
                alert('Please provide a more detailed testimonial (at least 10 characters).');
                return false;
            }
        });
    }
    
    // Rating input interaction
    const ratingLabels = document.querySelectorAll('.rating-input label');
    if (ratingLabels.length > 0) {
        ratingLabels.forEach((label, index) => {
            label.addEventListener('mouseenter', function() {
                for (let i = ratingLabels.length - 1; i >= ratingLabels.length - 1 - index; i--) {
                    ratingLabels[i].style.color = '#ffc107';
                }
            });
            
            label.addEventListener('mouseleave', function() {
                const checkedInput = document.querySelector('.rating-input input[type="radio"]:checked');
                if (checkedInput) {
                    const checkedIndex = Array.from(document.querySelectorAll('.rating-input input[type="radio"]')).indexOf(checkedInput);
                    ratingLabels.forEach((lbl, idx) => {
                        lbl.style.color = idx >= ratingLabels.length - 1 - checkedIndex ? '#ffc107' : '#ddd';
                    });
                } else {
                    ratingLabels.forEach(lbl => {
                        lbl.style.color = '#ddd';
                    });
                }
            });
        });
    }
    
    // Add hover effects to social icons
    document.querySelectorAll('.social-icon').forEach(icon => {
        icon.addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.1) translateY(-3px)';
        });
        
        icon.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1) translateY(0)';
        });
    });
    
    // Add hover effects to tech icons
    document.querySelectorAll('.tech-icon').forEach(icon => {
        icon.addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.1) rotate(5deg)';
        });
        
        icon.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1) rotate(0deg)';
        });
    });
});

// Typing effect for hero heading
function typeWriter(element, text, speed = 50) {
    let i = 0;
    element.innerHTML = '';
    
    function type() {
        if (i < text.length) {
            element.innerHTML += text.charAt(i);
            i++;
            setTimeout(type, speed);
        }
    }
    
    type();
}

// Initialize typing effect when page loads
window.addEventListener('load', function() {
    const heading = document.querySelector('.hero-section h1');
    if (heading) {
        const originalText = heading.textContent;
        heading.textContent = '';
        setTimeout(() => {
            typeWriter(heading, originalText, 50);
        }, 500);
    }
});
</script>

<?php include 'includes/footer.php'; ?>