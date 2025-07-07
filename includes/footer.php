<?php
// Detect current directory and set paths (same logic as header)
$is_admin = (basename(dirname($_SERVER['PHP_SELF'])) === 'admin');
$base_path = $is_admin ? '../' : '';
$logo_path = $base_path . 'assets/images/logo.png';
?>

    <!-- Footer -->
    <footer class="bg-dark text-light mt-5">
        <div class="container py-5">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <h5 class="text-primary mb-3">
                        <img src="<?php echo $logo_path; ?>" alt="Berekke Logo" style="height: 30px;" class="me-2">
                        Berekke Website
                    </h5>
                    <p class="text-light opacity-75">
                        Sri Lankan Police officers සඳහා නිර්මාණය කළ විශේෂ වෙබ් අඩවියකි. 
                        නීතිමය ප්‍රලේඛන, මෙවලම් සහ අවශ්‍ය සියලුම තොරතුරු සඳහා ඔබගේ විශ්වසනීය මූලාශ්‍රය.
                    </p>
                    <div class="d-flex">
                        <a href="#" class="text-primary me-3 social-link" title="Facebook">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="text-primary me-3 social-link" title="Twitter">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="text-primary me-3 social-link" title="LinkedIn">
                            <i class="fab fa-linkedin-in"></i>
                        </a>
                        <a href="#" class="text-primary social-link" title="YouTube">
                            <i class="fab fa-youtube"></i>
                        </a>
                    </div>
                </div>
                
                <div class="col-lg-2 col-md-6 mb-4">
                    <h6 class="text-white mb-3">Quick Links</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <a href="<?php echo $base_path; ?>index.php" class="text-light text-decoration-none footer-link">
                                <i class="fas fa-home me-1"></i>Home
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="<?php echo $base_path; ?>about_us.php" class="text-light text-decoration-none footer-link">
                                <i class="fas fa-info-circle me-1"></i>About Us
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="<?php echo $base_path; ?>blogs.php" class="text-light text-decoration-none footer-link">
                                <i class="fas fa-blog me-1"></i>Blogs
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="<?php echo $base_path; ?>downloads.php" class="text-light text-decoration-none footer-link">
                                <i class="fas fa-download me-1"></i>Downloads
                            </a>
                        </li>
                    </ul>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-4">
                    <h6 class="text-white mb-3">Legal Resources</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <a href="<?php echo $base_path; ?>penal_code.php" class="text-light text-decoration-none footer-link">
                                <i class="fas fa-balance-scale me-1"></i>Penal Code
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="<?php echo $base_path; ?>criminal_procedure_code_act.php" class="text-light text-decoration-none footer-link">
                                <i class="fas fa-clipboard-list me-1"></i>Criminal Procedure Code
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="<?php echo $base_path; ?>evidence_ordinance.php" class="text-light text-decoration-none footer-link">
                                <i class="fas fa-search me-1"></i>Evidence Ordinance
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="<?php echo $base_path; ?>station_book_list.php" class="text-light text-decoration-none footer-link">
                                <i class="fas fa-book me-1"></i>Station Book List
                            </a>
                        </li>
                    </ul>
                </div>
                
                <div class="col-lg-3 mb-4">
                    <h6 class="text-white mb-3">Contact Info</h6>
                    <ul class="list-unstyled">
                        <li class="text-light mb-2 d-flex align-items-center">
                            <i class="fas fa-map-marker-alt me-2 text-primary"></i>
                            <span>Colombo, Western Province, Sri Lanka</span>
                        </li>
                        <li class="text-light mb-2 d-flex align-items-center">
                            <i class="fas fa-envelope me-2 text-primary"></i>
                            <a href="mailto:info@berekke.lk" class="text-light text-decoration-none">info@berekke.lk</a>
                        </li>
                        <li class="text-light mb-2 d-flex align-items-center">
                            <i class="fas fa-phone me-2 text-primary"></i>
                            <a href="tel:+94112345678" class="text-light text-decoration-none">+94 11 234 5678</a>
                        </li>
                        <li class="text-light mb-2 d-flex align-items-center">
                            <i class="fas fa-clock me-2 text-primary"></i>
                            <span>24/7 Support Available</span>
                        </li>
                    </ul>
                </div>
            </div>
            
            <hr class="border-secondary my-4">
            
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="text-light mb-2 mb-md-0">
                        <i class="fas fa-copyright me-1"></i>
                        <?php echo date('Y'); ?> Berekke Website. All rights reserved.
                    </p>
                    <small class="text-white">
                        Built with <i class="fas fa-heart text-danger"></i> for Sri Lankan Police
                    </small>
                </div>
                <div class="col-md-6 text-md-end">
                    <div class="footer-links">
                        <a href="<?php echo $base_path; ?>privacy_policy.php" class="text-light text-decoration-none footer-link me-3">
                            <i class="fas fa-shield-alt me-1"></i>Privacy Policy
                        </a>
                        <a href="<?php echo $base_path; ?>terms_of_service.php" class="text-light text-decoration-none footer-link">
                            <i class="fas fa-file-contract me-1"></i>Terms of Service
                        </a>
                    </div>
                    <?php if ($is_admin): ?>
                    <div class="mt-2">
                        <small class="text-warning">
                            <i class="fas fa-user-shield me-1"></i>Admin Panel Active
                        </small>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Additional Footer Info -->

        </div>
    </footer>

    <!-- Enhanced Back to Top Button -->
    <button id="backToTop" class="btn btn-primary position-fixed shadow-lg" 
            style="bottom: 20px; right: 20px; z-index: 1000; display: none; border-radius: 50%; width: 55px; height: 55px; transition: all 0.3s ease;"
            title="Back to Top">
        <i class="fas fa-arrow-up"></i>
    </button>

    <!-- Scroll Progress Bar -->
    <div id="scrollProgress" style="position: fixed; top: 0; left: 0; width: 0%; height: 3px; background: linear-gradient(90deg, var(--bs-primary), var(--bs-secondary)); z-index: 9999; transition: width 0.3s ease;"></div>

    <style>
        /* Enhanced Footer Styles */
        footer {
            background: linear-gradient(135deg, #212529 0%, #343a40 100%) !important;
            position: relative;
            overflow: hidden;
        }
        
        footer::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, var(--bs-primary), transparent);
        }
        
        .footer-link {
            transition: all 0.3s ease;
            position: relative;
        }
        
        .footer-link:hover {
            color: var(--bs-primary) !important;
            transform: translateX(5px);
        }
        
        .footer-link::before {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--bs-primary);
            transition: width 0.3s ease;
        }
        
        .footer-link:hover::before {
            width: 100%;
        }
        
        .social-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
        }
        
        .social-link:hover {
            background: var(--bs-primary);
            color: white !important;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 86, 179, 0.4);
        }
        
        #backToTop {
            opacity: 0;
            visibility: hidden;
            transform: scale(0.8);
        }
        
        #backToTop.show {
            opacity: 1;
            visibility: visible;
            transform: scale(1);
        }
        
        #backToTop:hover {
            transform: scale(1.1);
            box-shadow: 0 8px 25px rgba(0, 86, 179, 0.3);
        }
        
        .footer-stats {
            padding: 1rem;
            background: rgba(0, 0, 0, 0.2);
            border-radius: 0.5rem;
            margin-top: 1rem;
        }
        
        /* Dark mode adjustments */
        [data-bs-theme="dark"] footer {
            background: linear-gradient(135deg, #0d1117 0%, #21262d 100%) !important;
        }
        
        /* Mobile responsive */
        @media (max-width: 768px) {
            footer .container {
                padding: 2rem 1rem;
            }
            
            .footer-links {
                text-align: center !important;
                margin-top: 1rem;
            }
            
            .social-link {
                width: 35px;
                height: 35px;
            }
            
            #backToTop {
                bottom: 15px;
                right: 15px;
                width: 45px;
                height: 45px;
            }
        }
    </style>

    <script>
        // Enhanced Back to Top Button with Scroll Progress
        function updateScrollProgress() {
            const winScroll = document.body.scrollTop || document.documentElement.scrollTop;
            const height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
            const scrolled = (winScroll / height) * 100;
            document.getElementById('scrollProgress').style.width = scrolled + '%';
        }
        
        window.addEventListener('scroll', function() {
            const backToTop = document.getElementById('backToTop');
            const scrollY = window.scrollY;
            
            // Update scroll progress
            updateScrollProgress();
            
            // Show/hide back to top button
            if (scrollY > 300) {
                backToTop.classList.add('show');
            } else {
                backToTop.classList.remove('show');
            }
        });
        
        document.getElementById('backToTop').addEventListener('click', function() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
        
        // Visitor counter simulation (replace with actual analytics)
        document.addEventListener('DOMContentLoaded', function() {
            // Simulate visitor count (replace with real data)
            const visitorCount = localStorage.getItem('visitorCount') || Math.floor(Math.random() * 10000) + 5000;
            document.getElementById('visitor-count').textContent = `Visitors: ${parseInt(visitorCount).toLocaleString()}`;
            
            // Increment for next visit
            localStorage.setItem('visitorCount', parseInt(visitorCount) + 1);
            
            // Add entrance animation to footer elements
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };
            
            const observer = new IntersectionObserver(function(entries) {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }
                });
            }, observerOptions);
            
            // Observe footer elements
            document.querySelectorAll('footer .col-lg-4, footer .col-lg-2, footer .col-lg-3').forEach(el => {
                el.style.opacity = '0';
                el.style.transform = 'translateY(30px)';
                el.style.transition = 'all 0.6s ease';
                observer.observe(el);
            });
        });
        
        // Smooth scroll for footer links
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
    </script>
</body>
</html>