<!-- Footer -->
    <footer class="bg-dark text-blue mt-5">
        <div class="container py-5">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <h5 class="text-primary mb-3">
                        <img src="assets/images/logo.png" alt="Logo" style="height: 30px;" class="me-2">
                        Berekke Website
                    </h5>
                    <p class="text-light">
                        A special website created for Sri Lankan Police officers. Your trusted source for legal documents, tools and all the necessary information.
                    </p>
                    <div class="d-flex">
                        <a href="#" class="text-blue me-3"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-blue me-3"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-blue me-3"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#" class="text-blue"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
                
                <div class="col-lg-2 col-md-6 mb-4">
                    <h6 class="text-white mb-3">Quick Links</h6>
                    <ul class="list-unstyled">
                        <li><a href="index.php" class="text-blue text-decoration-none">Home</a></li>
                        <li><a href="about_us.php" class="text-blue text-decoration-none">About Us</a></li>
                        <li><a href="blogs.php" class="text-blue text-decoration-none">Blogs</a></li>
                        <li><a href="downloads.php" class="text-blue text-decoration-none">Downloads</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-4">
                    <h6 class="text-white mb-3">Legal Resources</h6>
                    <ul class="list-unstyled">
                        <li><a href="penal_code.php" class="text-blue text-decoration-none">Penal Code</a></li>
                        <li><a href="criminal_procedure_code_act.php" class="text-blue text-decoration-none">Criminal Procedure Code</a></li>
                        <li><a href="evidence_ordinance.php" class="text-blue text-decoration-none">Evidence Ordinance</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-3 mb-4">
                    <h6 class="text-white mb-3">Contact Info</h6>
                    <ul class="list-unstyled">
                        <li class="text-success mb-2">
                            <i class="fas fa-map-marker-alt me-2"></i>
                            Colombo, Sri Lanka
                        </li>
                        <li class="text-success mb-2">
                            <i class="fas fa-envelope me-2"></i>
                            info@berekke.lk
                        </li>
                        <li class="text-success mb-2">
                            <i class="fas fa-phone me-2"></i>
                            +94 11 234 5678
                        </li>
                    </ul>
                </div>
            </div>
            
            <hr class="border-secondary">
            
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="text-info mb-0">
                        &copy; <?php echo date('Y'); ?> Berekke Website. All rights reserved.
                    </p>
                </div>
                <div class="col-md-6 text-md-end">
                    <a href="#" class="text-info text-decoration-none me-3">Privacy Policy</a>
                    <a href="#" class="text-info text-decoration-none">Terms of Service</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Back to Top Button -->
    <button id="backToTop" class="btn btn-primary position-fixed" 
            style="bottom: 20px; right: 20px; z-index: 1000; display: none; border-radius: 50%; width: 50px; height: 50px;">
        <i class="fas fa-arrow-up"></i>
    </button>

    <script>
        // Back to Top Button
        window.addEventListener('scroll', function() {
            const backToTop = document.getElementById('backToTop');
            if (window.scrollY > 300) {
                backToTop.style.display = 'block';
            } else {
                backToTop.style.display = 'none';
            }
        });
        
        document.getElementById('backToTop').addEventListener('click', function() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    </script>
</body>
</html>