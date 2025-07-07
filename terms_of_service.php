<?php
$page_title = "Terms of Service";
include 'includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <!-- Header Section -->
            <div class="text-center mb-5 animate__animated animate__fadeInDown">
                <div class="legal-header-icon mb-4">
                    <i class="fas fa-file-contract fa-4x text-primary"></i>
                </div>
                <h1 class="display-4 fw-bold text-primary mb-3">Terms of Service</h1>
                <p class="lead text-muted">
                    Please read these terms carefully before using the Berekke Website platform
                </p>
                <div class="divider mx-auto my-4"></div>
                <p class="text-muted">
                    <i class="fas fa-calendar-alt me-2"></i>
                    Last Updated: <?php echo date('F d, Y'); ?>
                </p>
            </div>

            <!-- Terms Content -->
            <div class="legal-content">
                <!-- 1. Acceptance of Terms -->
                <section class="legal-section animate__animated animate__fadeInUp" data-delay="0.1s">
                    <div class="section-header">
                        <div class="section-number">1</div>
                        <h2>Acceptance of Terms</h2>
                    </div>
                    <div class="section-content">
                        <p>
                            By accessing and using the Berekke Website ("the Platform"), you acknowledge that you have read, 
                            understood, and agree to be bound by these Terms of Service ("Terms"). These Terms constitute a 
                            legally binding agreement between you and Berekke Website.
                        </p>
                        <div class="highlight-box">
                            <i class="fas fa-info-circle text-info me-2"></i>
                            <strong>Important:</strong> If you do not agree to these Terms, please discontinue use of the Platform immediately.
                        </div>
                    </div>
                </section>

                <!-- 2. Eligibility and User Accounts -->
                <section class="legal-section animate__animated animate__fadeInUp" data-delay="0.2s">
                    <div class="section-header">
                        <div class="section-number">2</div>
                        <h2>Eligibility and User Accounts</h2>
                    </div>
                    <div class="section-content">
                        <h4 class="subsection-title">2.1 Eligibility Requirements</h4>
                        <ul class="custom-list">
                            <li>You must be a serving or retired member of the Sri Lankan Police Force</li>
                            <li>You must be at least 18 years of age</li>
                            <li>You must provide accurate and complete registration information</li>
                            <li>You must maintain the confidentiality of your account credentials</li>
                        </ul>

                        <h4 class="subsection-title">2.2 Account Responsibilities</h4>
                        <p>
                            You are responsible for all activities that occur under your account. You must immediately 
                            notify us of any unauthorized use of your account or any other breach of security.
                        </p>
                    </div>
                </section>

                <!-- 3. Permitted Use -->
                <section class="legal-section animate__animated animate__fadeInUp" data-delay="0.3s">
                    <div class="section-header">
                        <div class="section-number">3</div>
                        <h2>Permitted Use</h2>
                    </div>
                    <div class="section-content">
                        <p>The Platform is designed exclusively for professional law enforcement purposes. Permitted uses include:</p>
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="permission-card allowed">
                                    <i class="fas fa-check-circle text-success"></i>
                                    <h5>Allowed Activities</h5>
                                    <ul>
                                        <li>Accessing legal databases</li>
                                        <li>Using AI tools for case analysis</li>
                                        <li>Downloading approved resources</li>
                                        <li>Sharing professional insights</li>
                                        <li>Participating in forums</li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="permission-card prohibited">
                                    <i class="fas fa-times-circle text-danger"></i>
                                    <h5>Prohibited Activities</h5>
                                    <ul>
                                        <li>Sharing classified information</li>
                                        <li>Commercial exploitation</li>
                                        <li>Unauthorized data extraction</li>
                                        <li>Creating fake accounts</li>
                                        <li>Malicious activities</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- 4. Intellectual Property -->
                <section class="legal-section animate__animated animate__fadeInUp" data-delay="0.4s">
                    <div class="section-header">
                        <div class="section-number">4</div>
                        <h2>Intellectual Property Rights</h2>
                    </div>
                    <div class="section-content">
                        <p>
                            All content, features, and functionality of the Platform are owned by Berekke Website 
                            and are protected by international copyright, trademark, and other intellectual property laws.
                        </p>
                        
                        <div class="info-grid">
                            <div class="info-item">
                                <i class="fas fa-copyright text-primary"></i>
                                <h5>Content Ownership</h5>
                                <p>All original content remains the property of Berekke Website unless otherwise stated.</p>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-user-edit text-warning"></i>
                                <h5>User Content</h5>
                                <p>You retain rights to content you create, but grant us license to use it on the Platform.</p>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-balance-scale text-info"></i>
                                <h5>Legal Databases</h5>
                                <p>Legal content is provided for reference and remains property of the Government of Sri Lanka.</p>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- 5. Privacy and Data Protection -->
                <section class="legal-section animate__animated animate__fadeInUp" data-delay="0.5s">
                    <div class="section-header">
                        <div class="section-number">5</div>
                        <h2>Privacy and Data Protection</h2>
                    </div>
                    <div class="section-content">
                        <p>
                            Your privacy is important to us. Our collection, use, and protection of your personal 
                            information is governed by our <a href="privacy_policy.php" class="text-primary">Privacy Policy</a>, 
                            which is incorporated into these Terms by reference.
                        </p>
                        
                        <div class="privacy-highlights">
                            <div class="privacy-item">
                                <i class="fas fa-shield-alt text-success"></i>
                                <span>Data encryption and secure storage</span>
                            </div>
                            <div class="privacy-item">
                                <i class="fas fa-user-shield text-primary"></i>
                                <span>Limited data collection for functionality</span>
                            </div>
                            <div class="privacy-item">
                                <i class="fas fa-eye-slash text-warning"></i>
                                <span>No sharing with unauthorized third parties</span>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- 6. Limitation of Liability -->
                <section class="legal-section animate__animated animate__fadeInUp" data-delay="0.6s">
                    <div class="section-header">
                        <div class="section-number">6</div>
                        <h2>Limitation of Liability</h2>
                    </div>
                    <div class="section-content">
                        <div class="warning-box">
                            <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                            <strong>Important Disclaimer:</strong> The Platform is provided "as is" without warranties of any kind.
                        </div>
                        
                        <p>
                            To the fullest extent permitted by law, Berekke Website disclaims all warranties, express or implied, 
                            including but not limited to warranties of merchantability, fitness for a particular purpose, and non-infringement.
                        </p>
                        
                        <p>
                            We shall not be liable for any indirect, incidental, special, consequential, or punitive damages, 
                            including without limitation, loss of profits, data, use, goodwill, or other intangible losses.
                        </p>
                    </div>
                </section>

                <!-- 7. Termination -->
                <section class="legal-section animate__animated animate__fadeInUp" data-delay="0.7s">
                    <div class="section-header">
                        <div class="section-number">7</div>
                        <h2>Termination</h2>
                    </div>
                    <div class="section-content">
                        <p>
                            We may terminate or suspend your account and access to the Platform immediately, without prior notice, 
                            for any reason, including breach of these Terms.
                        </p>
                        
                        <div class="termination-reasons">
                            <h5>Grounds for Termination:</h5>
                            <ul class="styled-list">
                                <li>Violation of Terms of Service</li>
                                <li>Misuse of Platform resources</li>
                                <li>Sharing of classified information</li>
                                <li>Fraudulent or malicious activities</li>
                                <li>Extended period of inactivity</li>
                            </ul>
                        </div>
                    </div>
                </section>

                <!-- 8. Contact Information -->
                <section class="legal-section animate__animated animate__fadeInUp" data-delay="0.8s">
                    <div class="section-header">
                        <div class="section-number">8</div>
                        <h2>Contact Information</h2>
                    </div>
                    <div class="section-content">
                        <p>
                            If you have any questions about these Terms of Service, please contact us:
                        </p>
                        
                        <div class="contact-grid">
                            <div class="contact-item">
                                <i class="fas fa-envelope text-primary"></i>
                                <div>
                                    <strong>Email:</strong><br>
                                    <a href="mailto:legal@berekke.lk">legal@berekke.lk</a>
                                </div>
                            </div>
                            <div class="contact-item">
                                <i class="fas fa-phone text-success"></i>
                                <div>
                                    <strong>Phone:</strong><br>
                                    <a href="tel:+94112345678">+94 11 234 5678</a>
                                </div>
                            </div>
                            <div class="contact-item">
                                <i class="fas fa-map-marker-alt text-info"></i>
                                <div>
                                    <strong>Address:</strong><br>
                                    Colombo, Sri Lanka
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            <!-- Agreement Footer -->
            <div class="agreement-footer text-center mt-5 animate__animated animate__fadeInUp" data-delay="0.9s">
                <div class="agreement-box">
                    <i class="fas fa-handshake fa-3x text-primary mb-3"></i>
                    <h4>Agreement Acknowledgment</h4>
                    <p>
                        By continuing to use the Berekke Website, you acknowledge that you have read, 
                        understood, and agree to be bound by these Terms of Service.
                    </p>
                    <div class="mt-3">
                        <a href="index.php" class="btn btn-primary btn-lg me-3">
                            <i class="fas fa-check me-2"></i>I Agree
                        </a>
                        <a href="javascript:history.back()" class="btn btn-outline-secondary btn-lg">
                            <i class="fas fa-arrow-left me-2"></i>Go Back
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Base Styles */
.legal-content {
    line-height: 1.8;
}

.legal-header-icon {
    position: relative;
}

.legal-header-icon::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 120px;
    height: 120px;
    background: linear-gradient(45deg, var(--bs-primary), var(--bs-secondary));
    border-radius: 50%;
    opacity: 0.1;
    z-index: -1;
}

.divider {
    width: 100px;
    height: 3px;
    background: linear-gradient(90deg, var(--bs-primary), var(--bs-secondary));
    border-radius: 2px;
}

/* Legal Sections */
.legal-section {
    margin-bottom: 3rem;
    opacity: 0;
    transform: translateY(30px);
    transition: all 0.6s ease;
}

.legal-section.animate__fadeInUp {
    opacity: 1;
    transform: translateY(0);
}

.section-header {
    display: flex;
    align-items: center;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid var(--bs-primary);
}

.section-number {
    background: linear-gradient(45deg, var(--bs-primary), var(--bs-secondary));
    color: white;
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 1.2rem;
    margin-right: 1rem;
    flex-shrink: 0;
}

.section-header h2 {
    color: var(--bs-primary);
    margin: 0;
    font-weight: 600;
}

.section-content {
    padding-left: 66px;
}

/* Subsections */
.subsection-title {
    color: var(--bs-secondary);
    margin-top: 1.5rem;
    margin-bottom: 1rem;
    font-weight: 500;
}

/* Custom Lists */
.custom-list {
    padding-left: 0;
    list-style: none;
}

.custom-list li {
    position: relative;
    padding-left: 2rem;
    margin-bottom: 0.5rem;
}

.custom-list li::before {
    content: '\f00c';
    font-family: 'Font Awesome 6 Free';
    font-weight: 900;
    position: absolute;
    left: 0;
    color: var(--bs-success);
}

.styled-list {
    padding-left: 0;
    list-style: none;
}

.styled-list li {
    position: relative;
    padding-left: 2rem;
    margin-bottom: 0.5rem;
}

.styled-list li::before {
    content: '\f0da';
    font-family: 'Font Awesome 6 Free';
    font-weight: 900;
    position: absolute;
    left: 0;
    color: var(--bs-primary);
}

/* Permission Cards */
.permission-card {
    background: var(--bs-body-bg);
    border: 2px solid;
    border-radius: 15px;
    padding: 1.5rem;
    height: 100%;
    transition: all 0.3s ease;
}

.permission-card.allowed {
    border-color: var(--bs-success);
    background: linear-gradient(135deg, rgba(25, 135, 84, 0.05), rgba(25, 135, 84, 0.1));
}

.permission-card.prohibited {
    border-color: var(--bs-danger);
    background: linear-gradient(135deg, rgba(220, 53, 69, 0.05), rgba(220, 53, 69, 0.1));
}

.permission-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
}

.permission-card i {
    font-size: 2rem;
    margin-bottom: 1rem;
}

.permission-card h5 {
    font-weight: 600;
    margin-bottom: 1rem;
}

.permission-card ul {
    padding-left: 1rem;
    margin: 0;
}

/* Info Grid */
.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-top: 1.5rem;
}

.info-item {
    background: var(--bs-body-bg);
    border: 1px solid var(--bs-border-color);
    border-radius: 10px;
    padding: 1.5rem;
    text-align: center;
    transition: all 0.3s ease;
}

.info-item:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.1);
    border-color: var(--bs-primary);
}

.info-item i {
    font-size: 2.5rem;
    margin-bottom: 1rem;
}

.info-item h5 {
    color: var(--bs-primary);
    margin-bottom: 1rem;
    font-weight: 600;
}

/* Highlight Boxes */
.highlight-box {
    background: linear-gradient(135deg, rgba(13, 110, 253, 0.1), rgba(13, 110, 253, 0.05));
    border-left: 4px solid var(--bs-primary);
    padding: 1rem 1.5rem;
    margin: 1.5rem 0;
    border-radius: 0 8px 8px 0;
}

.warning-box {
    background: linear-gradient(135deg, rgba(255, 193, 7, 0.1), rgba(255, 193, 7, 0.05));
    border-left: 4px solid var(--bs-warning);
    padding: 1rem 1.5rem;
    margin: 1.5rem 0;
    border-radius: 0 8px 8px 0;
}

/* Privacy Highlights */
.privacy-highlights {
    background: var(--bs-body-bg);
    border: 1px solid var(--bs-border-color);
    border-radius: 10px;
    padding: 1.5rem;
    margin-top: 1.5rem;
}

.privacy-item {
    display: flex;
    align-items: center;
    margin-bottom: 1rem;
}

.privacy-item:last-child {
    margin-bottom: 0;
}

.privacy-item i {
    font-size: 1.5rem;
    margin-right: 1rem;
    width: 30px;
}

/* Contact Grid */
.contact-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
    margin-top: 1.5rem;
}

.contact-item {
    display: flex;
    align-items: flex-start;
    padding: 1rem;
    background: var(--bs-body-bg);
    border: 1px solid var(--bs-border-color);
    border-radius: 10px;
    transition: all 0.3s ease;
}

.contact-item:hover {
    border-color: var(--bs-primary);
    transform: translateY(-2px);
}

.contact-item i {
    font-size: 1.5rem;
    margin-right: 1rem;
    margin-top: 0.25rem;
}

.contact-item a {
    color: var(--bs-primary);
    text-decoration: none;
}

.contact-item a:hover {
    text-decoration: underline;
}

/* Agreement Footer */
.agreement-footer {
    margin-top: 3rem;
}

.agreement-box {
    background: linear-gradient(135deg, var(--bs-primary), var(--bs-secondary));
    color: white;
    padding: 3rem 2rem;
    border-radius: 20px;
    position: relative;
    overflow: hidden;
}

.agreement-box::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -50%;
    width: 100%;
    height: 100%;
    background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
    animation: float 6s ease-in-out infinite;
}

.agreement-box h4 {
    font-weight: 600;
    margin-bottom: 1rem;
}

/* Dark Mode Adjustments */
[data-bs-theme="dark"] .legal-section {
    border-color: rgba(255,255,255,0.1);
}

[data-bs-theme="dark"] .permission-card {
    background: rgba(255,255,255,0.05);
}

[data-bs-theme="dark"] .info-item {
    background: rgba(255,255,255,0.05);
    border-color: rgba(255,255,255,0.1);
}

[data-bs-theme="dark"] .contact-item {
    background: rgba(255,255,255,0.05);
    border-color: rgba(255,255,255,0.1);
}

[data-bs-theme="dark"] .privacy-highlights {
    background: rgba(255,255,255,0.05);
    border-color: rgba(255,255,255,0.1);
}

/* Animations */
@keyframes float {
    0%, 100% { transform: translateY(0px) rotate(0deg); }
    50% { transform: translateY(-20px) rotate(5deg); }
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .section-content {
        padding-left: 0;
    }
    
    .section-header {
        flex-direction: column;
        text-align: center;
    }
    
    .section-number {
        margin-right: 0;
        margin-bottom: 1rem;
    }
    
    .info-grid {
        grid-template-columns: 1fr;
    }
    
    .contact-grid {
        grid-template-columns: 1fr;
    }
    
    .agreement-box {
        padding: 2rem 1rem;
    }
}
</style>

<script>
// Intersection Observer for animations
document.addEventListener('DOMContentLoaded', function() {
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const delay = entry.target.dataset.delay || 0;
                setTimeout(() => {
                    entry.target.classList.add('animate__fadeInUp');
                }, delay * 1000);
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);

    // Observe all legal sections
    document.querySelectorAll('.legal-section').forEach(section => {
        observer.observe(section);
    });

    // Smooth scroll for internal links
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

    // Add hover effects to cards
    const cards = document.querySelectorAll('.permission-card, .info-item, .contact-item');
    cards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?>