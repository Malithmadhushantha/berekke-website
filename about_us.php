<?php
require_once 'config/config.php';

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
                        වර්තමාන ඩිජිටල් යුගයේ නීතිමය ක්ෂේත්‍රයේ කාර්යක්ෂමතාව වැඩි දියුණු කිරීම සඳහා නිර්මාණය කරන ලද 
                        විශේෂ වේදිකාවකි. ශ්‍රී ලංකා පොලිස් සේවයේ නිලධාරීන් සඳහා නීතිමය සම්පත්, නවීන මෙවලම් සහ 
                        ප්‍රායෝගික විසඳුම් ලබා දෙන අරමුණින් මෙම වෙබ් අඩවිය සකස් කර ඇත.
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

    <!-- Developer Information -->
    <div class="row mb-5">
        <div class="col-lg-8 mx-auto">
            <div class="card border-0 shadow-lg">
                <div class="card-header bg-primary text-white text-center">
                    <h4 class="mb-0">
                        <i class="fas fa-user-tie me-2"></i>
                        Development Team
                    </h4>
                </div>
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <div class="mb-3">
                            <i class="fas fa-code fa-4x text-primary"></i>
                        </div>
                        <h4>Professional Development Team</h4>
                        <p class="text-muted">
                            Experienced developers specializing in law enforcement technology solutions
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
</script>

<?php include 'includes/footer.php'; ?>