<?php
$page_title = "Privacy Policy";
include 'includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <!-- Header Section -->
            <div class="text-center mb-5 animate__animated animate__fadeInDown">
                <div class="legal-header-icon mb-4">
                    <i class="fas fa-shield-alt fa-4x text-success"></i>
                </div>
                <h1 class="display-4 fw-bold text-success mb-3">Privacy Policy</h1>
                <p class="lead text-muted">
                    Your privacy and data security are our top priorities at Berekke Website
                </p>
                <div class="divider mx-auto my-4"></div>
                <p class="text-muted">
                    <i class="fas fa-calendar-alt me-2"></i>
                    Last Updated: <?php echo date('F d, Y'); ?>
                    <span class="mx-2">|</span>
                    <i class="fas fa-shield-check me-2 text-success"></i>
                    GDPR Compliant
                </p>
            </div>

            <!-- Privacy Overview -->
            <div class="privacy-overview mb-5 animate__animated animate__fadeInUp" data-delay="0.1s">
                <div class="overview-card">
                    <div class="row align-items-center">
                        <div class="col-lg-8">
                            <h3 class="text-success mb-3">Our Commitment to Your Privacy</h3>
                            <p class="mb-0">
                                At Berekke Website, we understand the sensitive nature of law enforcement work. 
                                We are committed to protecting your personal information and maintaining the highest 
                                standards of data security and privacy.
                            </p>
                        </div>
                        <div class="col-lg-4 text-center">
                            <div class="privacy-badge">
                                <i class="fas fa-user-shield fa-3x text-success mb-2"></i>
                                <p class="small mb-0">Trusted by<br><strong>Sri Lankan Police</strong></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Privacy Content -->
            <div class="legal-content">
                <!-- 1. Information We Collect -->
                <section class="legal-section animate__animated animate__fadeInUp" data-delay="0.2s">
                    <div class="section-header">
                        <div class="section-number bg-success">1</div>
                        <h2>Information We Collect</h2>
                    </div>
                    <div class="section-content">
                        <p>
                            We collect information to provide better services to our users. The types of information 
                            we collect depend on how you use our Platform.
                        </p>

                        <div class="data-types-grid">
                            <div class="data-type-card personal">
                                <i class="fas fa-user text-primary"></i>
                                <h5>Personal Information</h5>
                                <ul>
                                    <li>Name and contact details</li>
                                    <li>Police service number</li>
                                    <li>Department and rank</li>
                                    <li>Email address</li>
                                    <li>Profile picture (optional)</li>
                                </ul>
                            </div>

                            <div class="data-type-card usage">
                                <i class="fas fa-chart-line text-info"></i>
                                <h5>Usage Information</h5>
                                <ul>
                                    <li>Pages visited and time spent</li>
                                    <li>Search queries performed</li>
                                    <li>Features used</li>
                                    <li>Login times and frequency</li>
                                    <li>Device and browser information</li>
                                </ul>
                            </div>

                            <div class="data-type-card technical">
                                <i class="fas fa-cogs text-warning"></i>
                                <h5>Technical Information</h5>
                                <ul>
                                    <li>IP address and location</li>
                                    <li>Device type and operating system</li>
                                    <li>Browser type and version</li>
                                    <li>Screen resolution</li>
                                    <li>Referral source</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- 2. How We Use Your Information -->
                <section class="legal-section animate__animated animate__fadeInUp" data-delay="0.3s">
                    <div class="section-header">
                        <div class="section-number bg-success">2</div>
                        <h2>How We Use Your Information</h2>
                    </div>
                    <div class="section-content">
                        <p>
                            We use the information we collect to provide, maintain, protect and improve our services.
                        </p>

                        <div class="usage-purposes">
                            <div class="purpose-item">
                                <div class="purpose-icon">
                                    <i class="fas fa-user-cog text-primary"></i>
                                </div>
                                <div class="purpose-content">
                                    <h5>Account Management</h5>
                                    <p>Creating and maintaining your account, authentication, and providing personalized experiences.</p>
                                </div>
                            </div>

                            <div class="purpose-item">
                                <div class="purpose-icon">
                                    <i class="fas fa-tools text-success"></i>
                                </div>
                                <div class="purpose-content">
                                    <h5>Service Provision</h5>
                                    <p>Providing access to legal databases, AI tools, and other platform features.</p>
                                </div>
                            </div>

                            <div class="purpose-item">
                                <div class="purpose-icon">
                                    <i class="fas fa-shield-alt text-info"></i>
                                </div>
                                <div class="purpose-content">
                                    <h5>Security & Safety</h5>
                                    <p>Protecting against fraud, abuse, and unauthorized access to maintain platform security.</p>
                                </div>
                            </div>

                            <div class="purpose-item">
                                <div class="purpose-icon">
                                    <i class="fas fa-chart-bar text-warning"></i>
                                </div>
                                <div class="purpose-content">
                                    <h5>Analytics & Improvement</h5>
                                    <p>Understanding how you use our services to improve functionality and user experience.</p>
                                </div>
                            </div>

                            <div class="purpose-item">
                                <div class="purpose-icon">
                                    <i class="fas fa-bell text-danger"></i>
                                </div>
                                <div class="purpose-content">
                                    <h5>Communications</h5>
                                    <p>Sending important updates, security alerts, and platform notifications.</p>
                                </div>
                            </div>

                            <div class="purpose-item">
                                <div class="purpose-icon">
                                    <i class="fas fa-gavel text-secondary"></i>
                                </div>
                                <div class="purpose-content">
                                    <h5>Legal Compliance</h5>
                                    <p>Meeting legal obligations and responding to lawful requests from authorities.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- 3. Data Sharing and Disclosure -->
                <section class="legal-section animate__animated animate__fadeInUp" data-delay="0.4s">
                    <div class="section-header">
                        <div class="section-number bg-success">3</div>
                        <h2>Data Sharing and Disclosure</h2>
                    </div>
                    <div class="section-content">
                        <div class="sharing-policy">
                            <div class="policy-statement">
                                <i class="fas fa-lock fa-2x text-success mb-3"></i>
                                <h4 class="text-success">We do not sell your personal information</h4>
                                <p>Your data is never sold to third parties for commercial purposes.</p>
                            </div>
                        </div>

                        <h4 class="subsection-title">Limited Sharing Circumstances:</h4>
                        
                        <div class="sharing-grid">
                            <div class="sharing-card authorized">
                                <i class="fas fa-check-circle text-success"></i>
                                <h5>Authorized Sharing</h5>
                                <ul>
                                    <li>With your explicit consent</li>
                                    <li>For legitimate law enforcement purposes</li>
                                    <li>To comply with legal obligations</li>
                                    <li>To protect rights and safety</li>
                                </ul>
                            </div>

                            <div class="sharing-card service-providers">
                                <i class="fas fa-handshake text-primary"></i>
                                <h5>Service Providers</h5>
                                <ul>
                                    <li>Cloud hosting services</li>
                                    <li>Security monitoring</li>
                                    <li>Technical support</li>
                                    <li>Analytics providers</li>
                                </ul>
                                <small class="text-muted">All providers are bound by strict confidentiality agreements.</small>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- 4. Data Security -->
                <section class="legal-section animate__animated animate__fadeInUp" data-delay="0.5s">
                    <div class="section-header">
                        <div class="section-number bg-success">4</div>
                        <h2>Data Security Measures</h2>
                    </div>
                    <div class="section-content">
                        <p>
                            We implement robust security measures to protect your information against unauthorized 
                            access, alteration, disclosure, or destruction.
                        </p>

                        <div class="security-measures">
                            <div class="security-category">
                                <h5><i class="fas fa-key text-primary me-2"></i>Encryption</h5>
                                <div class="security-items">
                                    <div class="security-item">
                                        <i class="fas fa-lock"></i>
                                        <span>256-bit SSL/TLS encryption for data in transit</span>
                                    </div>
                                    <div class="security-item">
                                        <i class="fas fa-database"></i>
                                        <span>AES-256 encryption for data at rest</span>
                                    </div>
                                    <div class="security-item">
                                        <i class="fas fa-user-secret"></i>
                                        <span>Password hashing with bcrypt</span>
                                    </div>
                                </div>
                            </div>

                            <div class="security-category">
                                <h5><i class="fas fa-shield-alt text-success me-2"></i>Access Control</h5>
                                <div class="security-items">
                                    <div class="security-item">
                                        <i class="fas fa-fingerprint"></i>
                                        <span>Multi-factor authentication</span>
                                    </div>
                                    <div class="security-item">
                                        <i class="fas fa-users-cog"></i>
                                        <span>Role-based access permissions</span>
                                    </div>
                                    <div class="security-item">
                                        <i class="fas fa-history"></i>
                                        <span>Access logging and monitoring</span>
                                    </div>
                                </div>
                            </div>

                            <div class="security-category">
                                <h5><i class="fas fa-server text-info me-2"></i>Infrastructure</h5>
                                <div class="security-items">
                                    <div class="security-item">
                                        <i class="fas fa-cloud"></i>
                                        <span>Secure cloud hosting</span>
                                    </div>
                                    <div class="security-item">
                                        <i class="fas fa-fire"></i>
                                        <span>Firewall protection</span>
                                    </div>
                                    <div class="security-item">
                                        <i class="fas fa-backup"></i>
                                        <span>Regular security backups</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- 5. Your Rights and Choices -->
                <section class="legal-section animate__animated animate__fadeInUp" data-delay="0.6s">
                    <div class="section-header">
                        <div class="section-number bg-success">5</div>
                        <h2>Your Rights and Choices</h2>
                    </div>
                    <div class="section-content">
                        <p>
                            You have certain rights regarding your personal information. We respect these rights 
                            and provide easy ways for you to exercise them.
                        </p>

                        <div class="rights-grid">
                            <div class="right-card">
                                <i class="fas fa-eye text-primary"></i>
                                <h5>Access</h5>
                                <p>Request a copy of the personal information we hold about you.</p>
                                <button class="btn btn-outline-primary btn-sm">Request Access</button>
                            </div>

                            <div class="right-card">
                                <i class="fas fa-edit text-success"></i>
                                <h5>Correction</h5>
                                <p>Update or correct any inaccurate personal information.</p>
                                <button class="btn btn-outline-success btn-sm">Update Info</button>
                            </div>

                            <div class="right-card">
                                <i class="fas fa-download text-info"></i>
                                <h5>Portability</h5>
                                <p>Export your data in a commonly used, machine-readable format.</p>
                                <button class="btn btn-outline-info btn-sm">Export Data</button>
                            </div>

                            <div class="right-card">
                                <i class="fas fa-trash text-danger"></i>
                                <h5>Deletion</h5>
                                <p>Request deletion of your personal information (subject to legal requirements).</p>
                                <button class="btn btn-outline-danger btn-sm">Delete Account</button>
                            </div>

                            <div class="right-card">
                                <i class="fas fa-ban text-warning"></i>
                                <h5>Restriction</h5>
                                <p>Restrict how we process your personal information.</p>
                                <button class="btn btn-outline-warning btn-sm">Restrict Processing</button>
                            </div>

                            <div class="right-card">
                                <i class="fas fa-times-circle text-secondary"></i>
                                <h5>Objection</h5>
                                <p>Object to certain types of processing of your personal information.</p>
                                <button class="btn btn-outline-secondary btn-sm">Object to Processing</button>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- 6. Data Retention -->
                <section class="legal-section animate__animated animate__fadeInUp" data-delay="0.7s">
                    <div class="section-header">
                        <div class="section-number bg-success">6</div>
                        <h2>Data Retention</h2>
                    </div>
                    <div class="section-content">
                        <p>
                            We retain your information only as long as necessary to provide our services 
                            and fulfill the purposes outlined in this Privacy Policy.
                        </p>

                        <div class="retention-timeline">
                            <div class="timeline-item">
                                <div class="timeline-marker active"></div>
                                <div class="timeline-content">
                                    <h5>Active Account</h5>
                                    <p>Data retained while your account is active and for legitimate law enforcement purposes.</p>
                                </div>
                            </div>

                            <div class="timeline-item">
                                <div class="timeline-marker inactive"></div>
                                <div class="timeline-content">
                                    <h5>Account Deactivation</h5>
                                    <p>Personal data deleted within 30 days, unless required for legal compliance.</p>
                                </div>
                            </div>

                            <div class="timeline-item">
                                <div class="timeline-marker backup"></div>
                                <div class="timeline-content">
                                    <h5>Backup Systems</h5>
                                    <p>Data in backup systems automatically deleted within 90 days.</p>
                                </div>
                            </div>

                            <div class="timeline-item">
                                <div class="timeline-marker legal"></div>
                                <div class="timeline-content">
                                    <h5>Legal Requirements</h5>
                                    <p>Some data may be retained longer for legal, regulatory, or security purposes.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- 7. Contact Information -->
                <section class="legal-section animate__animated animate__fadeInUp" data-delay="0.8s">
                    <div class="section-header">
                        <div class="section-number bg-success">7</div>
                        <h2>Contact Our Privacy Team</h2>
                    </div>
                    <div class="section-content">
                        <p>
                            If you have any questions about this Privacy Policy or our privacy practices, 
                            please contact our dedicated privacy team:
                        </p>

                        <div class="contact-options">
                            <div class="contact-option primary">
                                <div class="contact-icon">
                                    <i class="fas fa-envelope"></i>
                                </div>
                                <div class="contact-details">
                                    <h5>Privacy Officer</h5>
                                    <p><a href="mailto:privacy@berekke.lk">privacy@berekke.lk</a></p>
                                    <small>Response within 24 hours</small>
                                </div>
                            </div>

                            <div class="contact-option">
                                <div class="contact-icon">
                                    <i class="fas fa-phone"></i>
                                </div>
                                <div class="contact-details">
                                    <h5>Privacy Hotline</h5>
                                    <p><a href="tel:+94112345679">+94 11 234 5679</a></p>
                                    <small>Monday - Friday, 9 AM - 5 PM</small>
                                </div>
                            </div>

                            <div class="contact-option">
                                <div class="contact-icon">
                                    <i class="fas fa-comments"></i>
                                </div>
                                <div class="contact-details">
                                    <h5>Live Chat</h5>
                                    <p>Available on the platform</p>
                                    <small>Real-time support</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            <!-- Privacy Commitment Footer -->
            <div class="privacy-commitment text-center mt-5 animate__animated animate__fadeInUp" data-delay="0.9s">
                <div class="commitment-box">
                    <i class="fas fa-award fa-3x text-success mb-3"></i>
                    <h4>Our Privacy Commitment</h4>
                    <p>
                        We are committed to transparency, security, and respect for your privacy. 
                        Your trust is essential to our mission of serving the Sri Lankan Police Force.
                    </p>
                    <div class="commitment-badges">
                        <div class="badge-item">
                            <i class="fas fa-shield-check text-success"></i>
                            <span>Secure by Design</span>
                        </div>
                        <div class="badge-item">
                            <i class="fas fa-eye-slash text-primary"></i>
                            <span>Privacy First</span>
                        </div>
                        <div class="badge-item">
                            <i class="fas fa-handshake text-info"></i>
                            <span>Transparency</span>
                        </div>
                    </div>
                    <div class="mt-4">
                        <a href="index.php" class="btn btn-success btn-lg me-3">
                            <i class="fas fa-thumbs-up me-2"></i>I Understand
                        </a>
                        <a href="terms_of_service.php" class="btn btn-outline-light btn-lg">
                            <i class="fas fa-file-contract me-2"></i>View Terms
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Override section number background for privacy (green theme) */
.section-number.bg-success {
    background: linear-gradient(45deg, var(--bs-success), #20c997) !important;
}

.section-header h2 {
    color: var(--bs-success);
}

.divider {
    background: linear-gradient(90deg, var(--bs-success), #20c997);
}

/* Privacy Overview */
.overview-card {
    background: linear-gradient(135deg, rgba(25, 135, 84, 0.1), rgba(32, 201, 151, 0.05));
    border: 2px solid var(--bs-success);
    border-radius: 20px;
    padding: 2rem;
}

.privacy-badge {
    background: rgba(25, 135, 84, 0.1);
    border-radius: 15px;
    padding: 1.5rem;
}

/* Data Types Grid */
.data-types-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 1.5rem;
    margin-top: 1.5rem;
}

.data-type-card {
    background: var(--bs-body-bg);
    border: 2px solid var(--bs-border-color);
    border-radius: 15px;
    padding: 1.5rem;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.data-type-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, var(--bs-primary), var(--bs-success));
}

.data-type-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 15px 35px rgba(0,0,0,0.1);
    border-color: var(--bs-success);
}

.data-type-card i {
    font-size: 2.5rem;
    margin-bottom: 1rem;
}

.data-type-card h5 {
    color: var(--bs-success);
    font-weight: 600;
    margin-bottom: 1rem;
}

.data-type-card ul {
    padding-left: 1.2rem;
    margin: 0;
}

.data-type-card li {
    margin-bottom: 0.5rem;
    color: var(--bs-body-color);
}

/* Usage Purposes */
.usage-purposes {
    margin-top: 1.5rem;
}

.purpose-item {
    display: flex;
    align-items: flex-start;
    margin-bottom: 2rem;
    padding: 1.5rem;
    background: var(--bs-body-bg);
    border: 1px solid var(--bs-border-color);
    border-radius: 12px;
    transition: all 0.3s ease;
}

.purpose-item:hover {
    transform: translateX(10px);
    border-color: var(--bs-success);
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
}

.purpose-icon {
    background: linear-gradient(135deg, rgba(25, 135, 84, 0.1), rgba(32, 201, 151, 0.05));
    border-radius: 12px;
    width: 60px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 1.5rem;
    flex-shrink: 0;
}

.purpose-icon i {
    font-size: 1.5rem;
}

.purpose-content h5 {
    color: var(--bs-success);
    margin-bottom: 0.5rem;
    font-weight: 600;
}

.purpose-content p {
    margin: 0;
    color: var(--bs-body-color);
}

/* Sharing Policy */
.sharing-policy {
    background: linear-gradient(135deg, rgba(25, 135, 84, 0.1), rgba(32, 201, 151, 0.05));
    border-radius: 15px;
    padding: 2rem;
    text-align: center;
    margin-bottom: 2rem;
}

.policy-statement h4 {
    font-weight: 600;
}

.sharing-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
    margin-top: 1.5rem;
}

.sharing-card {
    background: var(--bs-body-bg);
    border: 2px solid var(--bs-border-color);
    border-radius: 15px;
    padding: 1.5rem;
    transition: all 0.3s ease;
}

.sharing-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
}

.sharing-card.authorized {
    border-color: var(--bs-success);
}

.sharing-card.service-providers {
    border-color: var(--bs-primary);
}

.sharing-card i {
    font-size: 2rem;
    margin-bottom: 1rem;
}

.sharing-card h5 {
    font-weight: 600;
    margin-bottom: 1rem;
}

.sharing-card ul {
    padding-left: 1.2rem;
    margin-bottom: 1rem;
}

/* Security Measures */
.security-measures {
    margin-top: 1.5rem;
}

.security-category {
    margin-bottom: 2rem;
    background: var(--bs-body-bg);
    border: 1px solid var(--bs-border-color);
    border-radius: 12px;
    padding: 1.5rem;
    transition: all 0.3s ease;
}

.security-category:hover {
    border-color: var(--bs-success);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.security-category h5 {
    font-weight: 600;
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid var(--bs-success);
}

.security-items {
    display: grid;
    gap: 0.75rem;
}

.security-item {
    display: flex;
    align-items: center;
    padding: 0.75rem;
    background: rgba(25, 135, 84, 0.05);
    border-radius: 8px;
    transition: all 0.3s ease;
}

.security-item:hover {
    background: rgba(25, 135, 84, 0.1);
    transform: translateX(5px);
}

.security-item i {
    color: var(--bs-success);
    margin-right: 0.75rem;
    width: 20px;
    text-align: center;
}

/* Rights Grid */
.rights-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-top: 1.5rem;
}

.right-card {
    background: var(--bs-body-bg);
    border: 2px solid var(--bs-border-color);
    border-radius: 15px;
    padding: 1.5rem;
    text-align: center;
    transition: all 0.3s ease;
}

.right-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    border-color: var(--bs-success);
}

.right-card i {
    font-size: 2.5rem;
    margin-bottom: 1rem;
}

.right-card h5 {
    font-weight: 600;
    margin-bottom: 1rem;
    color: var(--bs-success);
}

.right-card p {
    margin-bottom: 1.5rem;
    color: var(--bs-body-color);
}

/* Retention Timeline */
.retention-timeline {
    margin-top: 1.5rem;
    position: relative;
    padding-left: 2rem;
}

.retention-timeline::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: linear-gradient(to bottom, var(--bs-success), var(--bs-secondary));
}

.timeline-item {
    position: relative;
    margin-bottom: 2rem;
    padding-left: 2rem;
}

.timeline-marker {
    position: absolute;
    left: -2rem;
    top: 0.5rem;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    border: 3px solid var(--bs-success);
    background: var(--bs-body-bg);
    display: flex;
    align-items: center;
    justify-content: center;
}

.timeline-marker.active {
    background: var(--bs-success);
}

.timeline-marker.inactive {
    background: var(--bs-warning);
    border-color: var(--bs-warning);
}

.timeline-marker.backup {
    background: var(--bs-info);
    border-color: var(--bs-info);
}

.timeline-marker.legal {
    background: var(--bs-secondary);
    border-color: var(--bs-secondary);
}

.timeline-content {
    background: var(--bs-body-bg);
    border: 1px solid var(--bs-border-color);
    border-radius: 10px;
    padding: 1rem 1.5rem;
}

.timeline-content h5 {
    color: var(--bs-success);
    font-weight: 600;
    margin-bottom: 0.5rem;
}

/* Contact Options */
.contact-options {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-top: 1.5rem;
}

.contact-option {
    display: flex;
    align-items: flex-start;
    background: var(--bs-body-bg);
    border: 2px solid var(--bs-border-color);
    border-radius: 15px;
    padding: 1.5rem;
    transition: all 0.3s ease;
}

.contact-option:hover {
    transform: translateY(-5px);
    border-color: var(--bs-success);
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
}

.contact-option.primary {
    border-color: var(--bs-success);
    background: linear-gradient(135deg, rgba(25, 135, 84, 0.05), rgba(32, 201, 151, 0.02));
}

.contact-icon {
    background: linear-gradient(135deg, var(--bs-success), #20c997);
    color: white;
    width: 50px;
    height: 50px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 1rem;
    flex-shrink: 0;
}

.contact-details h5 {
    color: var(--bs-success);
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.contact-details a {
    color: var(--bs-primary);
    text-decoration: none;
    font-weight: 500;
}

.contact-details a:hover {
    text-decoration: underline;
}

.contact-details small {
    color: var(--bs-text-muted);
}

/* Privacy Commitment */
.commitment-box {
    background: linear-gradient(135deg, var(--bs-success), #20c997);
    color: white;
    padding: 3rem 2rem;
    border-radius: 20px;
    position: relative;
    overflow: hidden;
}

.commitment-box::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -50%;
    width: 100%;
    height: 100%;
    background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
    animation: float 6s ease-in-out infinite;
}

.commitment-badges {
    display: flex;
    justify-content: center;
    gap: 2rem;
    margin: 2rem 0;
    flex-wrap: wrap;
}

.badge-item {
    display: flex;
    align-items: center;
    background: rgba(255,255,255,0.1);
    padding: 0.75rem 1.5rem;
    border-radius: 25px;
    backdrop-filter: blur(10px);
}

.badge-item i {
    margin-right: 0.5rem;
    font-size: 1.2rem;
}

/* Dark Mode Adjustments */
[data-bs-theme="dark"] .overview-card {
    background: linear-gradient(135deg, rgba(25, 135, 84, 0.15), rgba(32, 201, 151, 0.1));
    border-color: var(--bs-success);
}

[data-bs-theme="dark"] .data-type-card,
[data-bs-theme="dark"] .purpose-item,
[data-bs-theme="dark"] .security-category,
[data-bs-theme="dark"] .right-card,
[data-bs-theme="dark"] .timeline-content,
[data-bs-theme="dark"] .contact-option {
    background: rgba(255,255,255,0.05);
    border-color: rgba(255,255,255,0.1);
}

[data-bs-theme="dark"] .sharing-policy {
    background: linear-gradient(135deg, rgba(25, 135, 84, 0.15), rgba(32, 201, 151, 0.1));
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .commitment-badges {
        flex-direction: column;
        align-items: center;
        gap: 1rem;
    }
    
    .data-types-grid,
    .rights-grid,
    .contact-options {
        grid-template-columns: 1fr;
    }
    
    .purpose-item {
        flex-direction: column;
        text-align: center;
    }
    
    .purpose-icon {
        margin-right: 0;
        margin-bottom: 1rem;
    }
    
    .retention-timeline {
        padding-left: 1rem;
    }
    
    .timeline-marker {
        left: -1rem;
    }
}
</style>

<script>
// Same animation script as Terms of Service
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

    // Observe all sections
    document.querySelectorAll('.legal-section, .privacy-overview, .privacy-commitment').forEach(section => {
        observer.observe(section);
    });

    // Interactive elements
    const cards = document.querySelectorAll('.data-type-card, .right-card, .contact-option, .purpose-item, .security-category');
    cards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = this.classList.contains('purpose-item') ? 'translateX(10px)' : 'translateY(-8px)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = this.classList.contains('purpose-item') ? 'translateX(0)' : 'translateY(0)';
        });
    });

    // Rights action buttons
    document.querySelectorAll('.right-card button').forEach(button => {
        button.addEventListener('click', function() {
            const action = this.textContent.trim();
            alert(`${action} request submitted. Our privacy team will contact you within 24 hours.`);
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?>