<?php
require_once 'config/config.php';

// Helper function to get correct profile picture path
function getProfilePicturePath($profile_picture) {
    if (empty($profile_picture) || $profile_picture === 'default_avatar.jpg') {
        return 'assets/images/avatars/default_avatar.jpg';
    }
    
    // Check if it's a user profile picture (from uploads/profiles/)
    if (file_exists(PROFILE_PICS_PATH . $profile_picture)) {
        return PROFILE_PICS_PATH . $profile_picture;
    }
    
    // Check if it's in avatars directory
    if (file_exists('assets/images/avatars/' . $profile_picture)) {
        return 'assets/images/avatars/' . $profile_picture;
    }
    
    // Default fallback
    return 'assets/images/avatars/default_avatar.jpg';
}

$success_message = '';
$error_message = '';

// Handle testimonial submission
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
            $profile_picture = 'default_avatar.jpg'; // Default avatar
            
            // If user is logged in, get their profile picture
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
            
            // Clear form data
            $_POST = array();
        } catch (PDOException $e) {
            $error_message = 'Error submitting testimonial. Please try again.';
        }
    }
}

// Get approved testimonials
$stmt = $pdo->prepare("SELECT * FROM testimonials WHERE status = 'approved' ORDER BY is_featured DESC, created_at DESC LIMIT 10");
$stmt->execute();
$testimonials = $stmt->fetchAll();

// Get featured testimonials for carousel
$stmt = $pdo->prepare("SELECT * FROM testimonials WHERE status = 'approved' AND is_featured = 1 ORDER BY created_at DESC");
$stmt->execute();
$featured_testimonials = $stmt->fetchAll();

$page_title = "About Us";
include 'includes/header.php';
?>

<div class="container py-5">
    <!-- Hero Section -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="text-center">
                <h1 class="display-4 fw-bold text-primary mb-3">About Berekke Website</h1>
                <p class="lead text-muted mb-4">
                    Empowering Sri Lankan law enforcement with digital tools and comprehensive legal resources
                </p>
                <div class="col-md-8 mx-auto">
                    <p class="text-muted">
A special platform created to improve the efficiency of the legal sector in today's digital era. This website has been developed with the aim of providing legal resources, modern tools and practical solutions for officers of the Sri Lanka Police Service.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Mission Section -->
    <div class="row mb-5">
        <div class="col-lg-8 mx-auto">
            <div class="card border-0 shadow-lg">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <i class="fas fa-bullseye fa-4x text-primary mb-3"></i>
                        <h2 class="fw-bold">Our Mission</h2>
                    </div>
                    <p class="lead text-center mb-4">
                        To revolutionize law enforcement operations in Sri Lanka through innovative digital solutions, 
                        comprehensive legal databases, and user-friendly tools that enhance efficiency and effectiveness.
                    </p>
                    <div class="row text-center">
                        <div class="col-md-4 mb-3">
                            <i class="fas fa-shield-alt fa-2x text-success mb-2"></i>
                            <h6>Security First</h6>
                            <small class="text-muted">Protecting sensitive information with enterprise-grade security</small>
                        </div>
                        <div class="col-md-4 mb-3">
                            <i class="fas fa-users fa-2x text-info mb-2"></i>
                            <h6>User-Centric</h6>
                            <small class="text-muted">Designed specifically for law enforcement professionals</small>
                        </div>
                        <div class="col-md-4 mb-3">
                            <i class="fas fa-rocket fa-2x text-warning mb-2"></i>
                            <h6>Innovation</h6>
                            <small class="text-muted">Leveraging latest technology for better outcomes</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Developer Profile Section -->
    <div class="row mb-5">
        <div class="col-12">
            <h2 class="text-center fw-bold mb-5">Meet the Developer</h2>
        </div>
        <div class="col-lg-8 mx-auto">
            <div class="card border-0 shadow-lg">
                <div class="card-header bg-gradient" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <div class="text-center fw-bold py-3">
                        <h4 class="mb-0">
                            <i class="fas fa-user-tie me-2"></i>
                            Developer Profile
                        </h4>
                    </div>
                </div>
                <div class="card-body p-5">
                    <div class="row align-items-center">
                        <div class="col-md-4 text-center mb-4 mb-md-0">
                            <div class="developer-photo mb-3">
                                <img src="assets/images/developer/malith_madhushantha.jpg" 
                                     alt="Malith Madhushantha" 
                                     class="img-fluid rounded-circle border border-5 border-primary"
                                     style="width: 200px; height: 200px; object-fit: cover;"
                                     onerror="this.src='assets/images/developer/default_developer.jpg'">
                            </div>
                            <div class="social-links">
                                <a href="tel:+94719803639" class="btn btn-outline-success btn-sm me-2">
                                    <i class="fas fa-phone"></i>
                                </a>
                                <a href="mailto:malith@berekke.lk" class="btn btn-outline-primary btn-sm me-2">
                                    <i class="fas fa-envelope"></i>
                                </a>
                                <a href="#" class="btn btn-outline-info btn-sm">
                                    <i class="fab fa-linkedin"></i>
                                </a>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <h3 class="text-primary mb-2">Malith Madhushantha</h3>
                            <h5 class="text-muted mb-3">
                                         <i class="fas fa-id-card me-2" style="color: #3A51E5FF;"></i>
                                Police Officer & Full-Stack Developer
                            </h5>
                            
                            <p class="mb-4">
                                A dedicated law enforcement officer with a passion for technology and innovation. 
                                Combining field experience in policing with advanced technical skills to create 
                                practical solutions for the Sri Lankan Police Force.
                            </p>
                            
                            <!-- Education & Qualifications -->
                            <div class="mb-4">
                                <h6 class="text-primary mb-3">
                                    <i class="fas fa-graduation-cap me-2"></i>
                                    Education & Qualifications
                                </h6>
                                <div class="qualification-item mb-2">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-certificate text-warning me-2"></i>
                                        <strong>Diploma in Human Resource Management</strong>
                                    </div>
                                    <small class="text-muted ms-4">University of Colombo</small>
                                </div>
                                <div class="qualification-item mb-2">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-laptop-code text-info me-2"></i>
                                        <strong>Diploma in Information Technology</strong>
                                    </div>
                                    <small class="text-muted ms-4">National Youth Services Council (NYSC)</small>
                                </div>
                                <div class="qualification-item mb-2">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-code text-success me-2"></i>
                                        <strong>MERN Full Stack Development Certificate</strong>
                                    </div>
                                    <small class="text-muted ms-4">Skyrek - Web Development</small>
                                </div>
                            </div>
                            
                            <!-- Contact Information -->
                            <div class="contact-details bg-light rounded p-3">
                                <h6 class="text-primary mb-3">
                                    <i class="fas fa-address-card me-2"></i>
                                    Contact Details
                                </h6>
                                <div class="row">
                                    <div class="col-sm-6 mb-2">
                                        <i class="fas fa-phone text-success me-2"></i>
                                        <strong>Phone:</strong><br>
                                        <small>+94 71 980 3639</small>
                                    </div>
                                    <div class="col-sm-6 mb-2">
                                        <i class="fas fa-map-marker-alt text-danger me-2"></i>
                                        <strong>Location:</strong><br>
                                        <small>Puttalam, Saliyawewa</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Skills & Expertise -->
                    <hr class="my-4">
                    <div class="skills-section">
                        <h6 class="text-primary mb-3">
                            <i class="fas fa-tools me-2"></i>
                            Technical Expertise
                        </h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="skill-item">
                                    <span class="badge bg-primary me-2">PHP</span>
                                    <span class="badge bg-success me-2">MySQL</span>
                                    <span class="badge bg-info me-2">JavaScript</span>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="skill-item">
                                    <span class="badge bg-warning me-2">React.js</span>
                                    <span class="badge bg-danger me-2">Node.js</span>
                                    <span class="badge bg-secondary me-2">MongoDB</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Features Section -->
    <div class="row mb-5">
        <div class="col-12">
            <h2 class="text-center fw-bold mb-5">Key Features & Capabilities</h2>
        </div>
        
        <div class="col-lg-6 mb-4">
            <div class="card h-100 border-0 shadow-sm feature-card">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="feature-icon bg-primary text-white rounded-circle me-3">
                            <i class="fas fa-database"></i>
                        </div>
                        <h5 class="mb-0">Comprehensive Legal Database</h5>
                    </div>
                    <p class="text-muted mb-3">
                        Complete searchable database of Sri Lankan Penal Code, Criminal Procedure Code, and Evidence Ordinance 
                        with advanced search functionality and bookmark capabilities.
                    </p>
                    <ul class="list-unstyled">
                        <li class="mb-1"><i class="fas fa-check text-success me-2"></i>Instant search across all legal texts</li>
                        <li class="mb-1"><i class="fas fa-check text-success me-2"></i>Personal bookmarking system</li>
                        <li class="mb-1"><i class="fas fa-check text-success me-2"></i>Note-taking capabilities</li>
                        <li><i class="fas fa-check text-success me-2"></i>Mobile-responsive design</li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="col-lg-6 mb-4">
            <div class="card h-100 border-0 shadow-sm feature-card">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="feature-icon bg-success text-white rounded-circle me-3">
                            <i class="fas fa-robot"></i>
                        </div>
                        <h5 class="mb-0">AI-Powered Tools</h5>
                    </div>
                    <p class="text-muted mb-3">
                        Advanced artificial intelligence tools designed specifically for law enforcement tasks, 
                        including document analysis, case summarization, and legal research assistance.
                    </p>
                    <ul class="list-unstyled">
                        <li class="mb-1"><i class="fas fa-check text-success me-2"></i>Document analysis and extraction</li>
                        <li class="mb-1"><i class="fas fa-check text-success me-2"></i>Automated case summarization</li>
                        <li class="mb-1"><i class="fas fa-check text-success me-2"></i>Legal research assistant</li>
                        <li><i class="fas fa-check text-success me-2"></i>Evidence analyzer (coming soon)</li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="col-lg-6 mb-4">
            <div class="card h-100 border-0 shadow-sm feature-card">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="feature-icon bg-warning text-white rounded-circle me-3">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <h5 class="mb-0">Document Management</h5>
                    </div>
                    <p class="text-muted mb-3">
                        Professional document creation, editing, and management tools with templates, 
                        digital signatures, and collaborative features for efficient paperwork handling.
                    </p>
                    <ul class="list-unstyled">
                        <li class="mb-1"><i class="fas fa-check text-success me-2"></i>Pre-built report templates</li>
                        <li class="mb-1"><i class="fas fa-check text-success me-2"></i>Advanced document editor</li>
                        <li class="mb-1"><i class="fas fa-check text-success me-2"></i>PDF generation capabilities</li>
                        <li><i class="fas fa-check text-success me-2"></i>Digital signature support</li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="col-lg-6 mb-4">
            <div class="card h-100 border-0 shadow-sm feature-card">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="feature-icon bg-info text-white rounded-circle me-3">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h5 class="mb-0">Analytics & Reporting</h5>
                    </div>
                    <p class="text-muted mb-3">
                        Powerful analytics and reporting tools for generating insights, creating visual charts, 
                        and tracking case progress with customizable dashboards and export capabilities.
                    </p>
                    <ul class="list-unstyled">
                        <li class="mb-1"><i class="fas fa-check text-success me-2"></i>Interactive chart generation</li>
                        <li class="mb-1"><i class="fas fa-check text-success me-2"></i>Case timeline visualization</li>
                        <li class="mb-1"><i class="fas fa-check text-success me-2"></i>Statistical analysis tools</li>
                        <li><i class="fas fa-check text-success me-2"></i>Customizable dashboards</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- User Testimonials Section -->
    <div class="row mb-5">
        <div class="col-12">
            <h2 class="text-center fw-bold mb-5">
                <i class="fas fa-quote-left text-primary me-2"></i>
                What Our Users Say
            </h2>
        </div>
        
        <!-- Featured Testimonials Carousel -->
        <?php if (!empty($featured_testimonials)): ?>
        <div class="col-12 mb-5">
            <div id="testimonialsCarousel" class="carousel slide" data-bs-ride="carousel">
                <div class="carousel-inner">
                    <?php foreach ($featured_testimonials as $index => $testimonial): ?>
                    <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                        <div class="row justify-content-center">
                            <div class="col-lg-8">
                                <div class="card border-0 shadow-lg">
                                    <div class="card-body p-5 text-center">
                                        <div class="mb-4">
                                            <img src="<?php echo getProfilePicturePath($testimonial['profile_picture']); ?>" 
                                             alt="<?php echo htmlspecialchars($testimonial['name']); ?>"
                                                 class="rounded-circle border border-3 border-primary"
                                                 style="width: 80px; height: 80px; object-fit: cover;"
                                                 onerror="this.src='assets/images/avatars/default_avatar.jpg'">
                                        </div>
                                        <div class="star-rating mb-3">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <i class="fas fa-star <?php echo $i <= $testimonial['rating'] ? 'text-warning' : 'text-muted'; ?>"></i>
                                            <?php endfor; ?>
                                        </div>
                                        <blockquote class="blockquote mb-4">
                                            <p class="lead">"<?php echo htmlspecialchars($testimonial['comment']); ?>"</p>
                                        </blockquote>
                                        <footer class="blockquote-footer">
                                            <strong><?php echo htmlspecialchars($testimonial['name']); ?></strong>
                                            <br>
                                            <cite title="Position"><?php echo htmlspecialchars($testimonial['position']); ?></cite>
                                            <?php if (!empty($testimonial['organization'])): ?>
                                            <br><small class="text-white"><?php echo htmlspecialchars($testimonial['organization']); ?></small>
                                            <?php endif; ?>
                                        </footer>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <button class="carousel-control-prev" type="button" data-bs-target="#testimonialsCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Previous</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#testimonialsCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Next</span>
                </button>
            </div>
        </div>
        <?php endif; ?>

        <!-- All Testimonials Grid -->
        <div class="col-12">
            <h4 class="text-center mb-4">Recent Testimonials</h4>
            <div class="row">
                <?php if (!empty($testimonials)): ?>
                    <?php foreach (array_slice($testimonials, 0, 6) as $testimonial): ?>
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="card h-100 border-0 shadow-sm testimonial-card">
                            <div class="card-body p-4">
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
                                <div class="star-rating mb-2">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star <?php echo $i <= $testimonial['rating'] ? 'text-warning' : 'text-muted'; ?> fa-sm"></i>
                                    <?php endfor; ?>
                                </div>
                                <p class="text-muted small mb-0">
                                    "<?php echo htmlspecialchars(substr($testimonial['comment'], 0, 150)) . (strlen($testimonial['comment']) > 150 ? '...' : ''); ?>"
                                </p>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12 text-center">
                        <p class="text-muted">No testimonials yet. Be the first to share your experience!</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Add Testimonial Section -->
    <div class="row mb-5">
        <div class="col-lg-8 mx-auto">
            <div class="card border-0 shadow-lg">
                <div class="card-header bg-primary text-white text-center">
                    <h4 class="mb-0">
                        <i class="fas fa-comment-dots me-2"></i>
                        Share Your Experience
                    </h4>
                </div>
                <div class="card-body p-5">
                    <?php if ($success_message): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        <?php echo $success_message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($error_message): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <?php echo $error_message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" required
                                       value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label fw-semibold">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email"
                                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="position" class="form-label fw-semibold">Position/Rank</label>
                                <input type="text" class="form-control" id="position" name="position" 
                                       placeholder="e.g., Police Inspector"
                                       value="<?php echo isset($_POST['position']) ? htmlspecialchars($_POST['position']) : ''; ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="organization" class="form-label fw-semibold">Organization</label>
                                <input type="text" class="form-control" id="organization" name="organization" 
                                       placeholder="e.g., Colombo Police Station"
                                       value="<?php echo isset($_POST['organization']) ? htmlspecialchars($_POST['organization']) : ''; ?>">
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label fw-semibold">Rating <span class="text-danger">*</span></label>
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
                                <label for="comment" class="form-label fw-semibold">Your Testimonial <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="comment" name="comment" rows="4" required
                                          placeholder="Share your experience with the Berekke platform..."><?php echo isset($_POST['comment']) ? htmlspecialchars($_POST['comment']) : ''; ?></textarea>
                            </div>
                        </div>
                        <div class="text-center">
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

    <!-- Technology Stack -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-dark text-white text-center">
                    <h4 class="mb-0">
                        <i class="fas fa-cogs me-2"></i>
                        Technology Stack
                    </h4>
                </div>
                <div class="card-body p-4">
                    <div class="row text-center">
                        <div class="col-md-3 col-6 mb-3">
                            <i class="fab fa-php fa-3x text-primary mb-2"></i>
                            <h6>PHP 8.x</h6>
                            <small class="text-muted">Backend Development</small>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <i class="fas fa-database fa-3x text-success mb-2"></i>
                            <h6>MySQL 8.0</h6>
                            <small class="text-muted">Database Management</small>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <i class="fab fa-bootstrap fa-3x text-info mb-2"></i>
                            <h6>Bootstrap 5</h6>
                            <small class="text-muted">Frontend Framework</small>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <i class="fab fa-js-square fa-3x text-warning mb-2"></i>
                            <h6>JavaScript ES6+</h6>
                            <small class="text-muted">Interactive Features</small>
                        </div>
                    </div>
                    <div class="row text-center mt-3">
                        <div class="col-md-4 mb-3">
                            <i class="fas fa-shield-alt fa-2x text-danger mb-2"></i>
                            <h6>Advanced Security</h6>
                            <small class="text-muted">Multi-layer protection</small>
                        </div>
                        <div class="col-md-4 mb-3">
                            <i class="fas fa-mobile-alt fa-2x text-purple mb-2"></i>
                            <h6>Mobile Responsive</h6>
                            <small class="text-muted">Works on all devices</small>
                        </div>
                        <div class="col-md-4 mb-3">
                            <i class="fas fa-cloud fa-2x text-secondary mb-2"></i>
                            <h6>Cloud Ready</h6>
                            <small class="text-muted">Scalable deployment</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Development Team Information -->
    <div class="row mb-5">
        <div class="col-lg-8 mx-auto">
            <div class="card border-0 shadow-lg">
                <div class="card-header bg-primary text-white text-center">
                    <h4 class="mb-0">
                        <i class="fas fa-users me-2"></i>
                        Development Approach
                    </h4>
                </div>
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <div class="mb-3">
                            <i class="fas fa-code fa-4x text-primary"></i>
                        </div>
                        <h4>Professional Development Standards</h4>
                        <p class="text-muted">
                            Built with modern technologies and best practices for law enforcement solutions
                        </p>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-laptop-code fa-2x text-primary me-3"></i>
                                <div>
                                    <h6 class="mb-1">Full-Stack Development</h6>
                                    <small class="text-muted">Complete web application development</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-shield-alt fa-2x text-success me-3"></i>
                                <div>
                                    <h6 class="mb-1">Security Expertise</h6>
                                    <small class="text-muted">Enterprise-grade security implementation</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-brain fa-2x text-info me-3"></i>
                                <div>
                                    <h6 class="mb-1">AI Integration</h6>
                                    <small class="text-muted">Artificial intelligence and machine learning</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-users fa-2x text-warning me-3"></i>
                                <div>
                                    <h6 class="mb-1">User Experience</h6>
                                    <small class="text-muted">User-centered design and usability</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Contact Information -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="card border-0 shadow-lg">
                <div class="card-header bg-info text-white text-center">
                    <h4 class="mb-0">
                        <i class="fas fa-envelope me-2"></i>
                        Contact Information
                    </h4>
                </div>
                <div class="card-body p-5">
                    <div class="row">
                        <div class="col-lg-6 mb-4">
                            <h5 class="text-info mb-3">Get in Touch</h5>
                            <div class="contact-info">
                                <div class="d-flex align-items-center mb-3">
                                    <i class="fas fa-envelope fa-lg text-primary me-3"></i>
                                    <div>
                                        <strong>Email:</strong><br>
                                        <a href="mailto:info@berekke.lk" class="text-decoration-none">info@berekke.lk</a><br>
                                        <a href="mailto:support@berekke.lk" class="text-decoration-none">support@berekke.lk</a>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center mb-3">
                                    <i class="fas fa-phone fa-lg text-success me-3"></i>
                                    <div>
                                        <strong>Phone:</strong><br>
                                        +94 11 234 5678<br>
                                        +94 77 123 4567 (Mobile)
                                    </div>
                                </div>
                                <div class="d-flex align-items-center mb-3">
                                    <i class="fas fa-map-marker-alt fa-lg text-danger me-3"></i>
                                    <div>
                                        <strong>Address:</strong><br>
                                        Colombo, Sri Lanka<br>
                                        (Exact address available upon request)
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <h5 class="text-info mb-3">Support & Feedback</h5>
                            <p class="text-muted mb-3">
                                We value your feedback and are committed to continuously improving our platform. 
                                Please don't hesitate to reach out with:
                            </p>
                            <ul class="list-unstyled">
                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i>
                                    Technical support requests
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i>
                                    Feature suggestions and improvements
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i>
                                    Bug reports and issues
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i>
                                    Training and implementation assistance
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i>
                                    General inquiries and partnership opportunities
                                </li>
                            </ul>
                        </div>
                    </div>
                    
                    <hr class="my-4">
                    
                    <div class="text-center">
                        <h6 class="text-muted mb-3">Follow Our Development</h6>
                        <div class="d-flex justify-content-center gap-3">
                            <a href="#" class="text-decoration-none">
                                <i class="fab fa-github fa-2x text-dark"></i>
                            </a>
                            <a href="#" class="text-decoration-none">
                                <i class="fab fa-linkedin fa-2x text-primary"></i>
                            </a>
                            <a href="#" class="text-decoration-none">
                                <i class="fab fa-twitter fa-2x text-info"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Legal Notice -->
    <div class="row">
        <div class="col-12">
            <div class="card border-warning">
                <div class="card-header bg-warning text-dark">
                    <h6 class="mb-0">
                        <i class="fas fa-gavel me-2"></i>
                        Legal Notice & Disclaimer
                    </h6>
                </div>
                <div class="card-body">
                    <p class="small text-muted mb-2">
                        <strong>Important:</strong> This website is designed as a tool to assist law enforcement professionals 
                        and should not be considered as official legal advice or a substitute for professional legal consultation. 
                        All legal information provided is based on Sri Lankan law as available at the time of development.
                    </p>
                    <p class="small text-muted mb-2">
                        Users are responsible for verifying the accuracy and currency of legal information before making any 
                        official decisions or taking legal action. The developers and administrators of this website assume 
                        no liability for any decisions made based on the information provided.
                    </p>
                    <p class="small text-muted mb-0">
                        For official legal matters, always consult with qualified legal professionals and refer to the 
                        most current official legal documents and statutes.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.feature-card {
    transition: all 0.3s ease;
}

.feature-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 0.5rem 1.5rem rgba(0,0,0,0.15) !important;
}

.testimonial-card {
    transition: all 0.3s ease;
}

.testimonial-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15) !important;
}

.feature-icon {
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
}

.contact-info .fas {
    width: 20px;
    text-align: center;
}

.text-purple {
    color: #6f42c1 !important;
}

/* Developer photo styles */
.developer-photo img {
    transition: all 0.3s ease;
}

.developer-photo:hover img {
    transform: scale(1.05);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

/* Rating input styles */
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

/* Carousel custom styles */
.carousel-control-prev,
.carousel-control-next {
    width: 5%;
}

.carousel-control-prev-icon,
.carousel-control-next-icon {
    background-color: rgba(0,0,0,0.5);
    border-radius: 50%;
    padding: 20px;
}

@media (max-width: 768px) {
    .display-4 {
        font-size: 2rem;
    }
    
    .feature-icon {
        width: 40px;
        height: 40px;
        font-size: 1.2rem;
    }
    
    .contact-info .d-flex {
        flex-direction: column;
        text-align: center;
    }
    
    .contact-info .fas {
        margin-bottom: 0.5rem;
    }
    
    .developer-photo img {
        width: 150px !important;
        height: 150px !important;
    }
    
    .rating-input {
        justify-content: center;
    }
}

/* Animation for feature cards */
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

.feature-card {
    animation: fadeInUp 0.6s ease-out;
}

.feature-card:nth-child(1) { animation-delay: 0.1s; }
.feature-card:nth-child(2) { animation-delay: 0.2s; }
.feature-card:nth-child(3) { animation-delay: 0.3s; }
.feature-card:nth-child(4) { animation-delay: 0.4s; }

.testimonial-card {
    animation: fadeInUp 0.6s ease-out;
}

.testimonial-card:nth-child(1) { animation-delay: 0.1s; }
.testimonial-card:nth-child(2) { animation-delay: 0.2s; }
.testimonial-card:nth-child(3) { animation-delay: 0.3s; }
.testimonial-card:nth-child(4) { animation-delay: 0.4s; }
.testimonial-card:nth-child(5) { animation-delay: 0.5s; }
.testimonial-card:nth-child(6) { animation-delay: 0.6s; }

/* Skills badges */
.skill-item .badge {
    font-size: 0.8rem;
    margin-bottom: 0.25rem;
}

/* Social links hover effect */
.social-links a {
    transition: all 0.3s ease;
}

.social-links a:hover {
    transform: translateY(-2px);
}

</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Smooth scrolling for internal links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
    
    // Animate elements on scroll
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);
    
    // Observe all cards for animation
    document.querySelectorAll('.card').forEach(card => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        card.style.transition = 'all 0.6s ease';
        observer.observe(card);
    });
    
    // Add hover effects to social links
    document.querySelectorAll('.fab').forEach(icon => {
        icon.addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.2) rotateY(180deg)';
            this.style.transition = 'transform 0.3s ease';
        });
        
        icon.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1) rotateY(0deg)';
        });
    });
    
    // Counter animation for technology stack
    const techIcons = document.querySelectorAll('.fab, .fas');
    techIcons.forEach(icon => {
        icon.addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.1) rotate(5deg)';
            this.style.transition = 'transform 0.2s ease';
        });
        
        icon.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1) rotate(0deg)';
        });
    });
    
    // Star rating interaction
    const ratingInputs = document.querySelectorAll('.rating-input input[type="radio"]');
    const ratingLabels = document.querySelectorAll('.rating-input label');
    
    ratingLabels.forEach((label, index) => {
        label.addEventListener('mouseenter', function() {
            // Highlight stars up to the hovered one
            for (let i = ratingLabels.length - 1; i >= ratingLabels.length - 1 - index; i--) {
                ratingLabels[i].style.color = '#ffc107';
            }
        });
        
        label.addEventListener('mouseleave', function() {
            // Reset colors based on selection
            const checkedInput = document.querySelector('.rating-input input[type="radio"]:checked');
            if (checkedInput) {
                const checkedIndex = Array.from(ratingInputs).indexOf(checkedInput);
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
    
    // Auto-advance testimonials carousel
    const carousel = document.getElementById('testimonialsCarousel');
    if (carousel) {
        setInterval(() => {
            const nextButton = carousel.querySelector('.carousel-control-next');
            if (nextButton) {
                nextButton.click();
            }
        }, 8000); // Change slide every 8 seconds
    }
});

// Add typing effect to the main heading
function typeWriter(element, text, speed = 100) {
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
    const heading = document.querySelector('.display-4');
    if (heading) {
        const originalText = heading.textContent;
        setTimeout(() => {
            typeWriter(heading, originalText, 50);
        }, 500);
    }
});

// Form validation
document.querySelector('form').addEventListener('submit', function(e) {
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
</script>

<?php include 'includes/footer.php'; ?>