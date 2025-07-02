<?php
// ==============================================
// CONFIGURATION FILE LOADER (ANIMATED LOADING EFFECT)
// ==============================================
echo '<style>#config-loader {position:fixed;top:0;left:0;width:100%;height:4px;background:linear-gradient(90deg, var(--bs-primary), var(--bs-secondary));z-index:9999;animation:pulse 1.5s infinite;}@keyframes pulse {0%{opacity:0.3;}50%{opacity:1;}100%{opacity:0.3;}}</style>';
echo '<div id="config-loader"></div>';

// Determine the correct path to config.php based on current directory
$config_paths = [
    'config/config.php',           // For root level files
    '../config/config.php',        // For admin/ level files  
    '../../config/config.php'      // For deeper nested files
];

$config_loaded = false;
foreach ($config_paths as $path) {
    if (file_exists($path)) {
        require_once $path;
        $config_loaded = true;
        break;
    }
}

echo '<script>document.getElementById("config-loader").style.display="none";</script>';

if (!$config_loaded) {
    die('<div class="container py-5"><div class="alert alert-danger text-center animate__animated animate__shakeX" role="alert">
        <i class="fas fa-exclamation-triangle fa-2x mb-3"></i>
        <h4>Configuration Error</h4>
        <p>Configuration file not found. Please ensure config/config.php exists.</p>
    </div></div>');
}

$user = getUserInfo();
?>
<!DOCTYPE html>
<html lang="si" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' . SITE_NAME : SITE_NAME; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Sinhala:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    
    <style>
        :root {
            --bs-primary: #0056b3;
            --bs-secondary: rgb(103, 185, 176);
            --navbar-height: 80px;
            --transition-time: 0.3s;
        }
        
        body {
            font-family: 'Noto Sans Sinhala', sans-serif;
            padding-top: var(--navbar-height);
            background-color: var(--bs-body-bg);
            color: var(--bs-body-color);
            transition: background-color var(--transition-time) ease, color var(--transition-time) ease;
        }
        
        /* Enhanced Navbar with Gradient and Animation */
        .navbar {
            height: var(--navbar-height);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            background: linear-gradient(135deg, var(--bs-primary), #003366);
            transition: all var(--transition-time) ease;
        }
        
        .navbar.scrolled {
            height: 60px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.15);
        }
        
        .navbar-brand img {
            height: 50px;
            width: auto;
            transition: all var(--transition-time) ease;
        }
        
        .navbar.scrolled .navbar-brand img {
            height: 40px;
        }
        
        /* Theme Toggle Button with Pulse Animation */
        .theme-toggle {
            background: none;
            border: none;
            color: var(--bs-navbar-color);
            font-size: 1.2rem;
            padding: 0.5rem;
            border-radius: 50%;
            transition: all var(--transition-time) ease;
            cursor: pointer;
        }
        
        .theme-toggle:hover {
            background-color: rgba(255,255,255,0.2);
            transform: scale(1.1);
            animation: pulse 1.5s infinite;
        }
        
        /* Dark Mode Styles */
        [data-bs-theme="dark"] {
            --bs-body-bg: #1a1a1a;
            --bs-body-color: #f8f9fa;
            --bs-primary: #0069d9;
        }
        
        .sticky-nav {
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1030;
            transition: all var(--transition-time) ease;
        }
        
        /* Enhanced Dropdown Menu */
        .navbar-nav .dropdown-menu {
            border: none;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            transform-origin: top;
            animation: fadeInDown 0.3s ease-out;
            border-radius: 0.5rem;
            margin-top: 0.5rem;
        }
        
        .dropdown-item {
            transition: all 0.2s ease;
            padding: 0.5rem 1rem;
            border-radius: 0.25rem;
            margin: 0.15rem;
        }
        
        .dropdown-item:hover {
            background-color: var(--bs-primary);
            color: white !important;
            transform: translateX(5px);
        }
        
        /* User Avatar with Glow Effect */
        .user-avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            object-fit: cover;
            transition: all var(--transition-time) ease;
            border: 2px solid rgba(255,255,255,0.3);
        }
        
        .user-avatar:hover {
            transform: scale(1.1);
            box-shadow: 0 0 10px rgba(255,255,255,0.5);
        }
        
        /* Notification Badge with Animation */
        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #dc3545;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: bounce 2s infinite;
        }
        
        /* Mobile Responsive Adjustments */
        @media (max-width: 992px) {
            :root {
                --navbar-height: 70px;
            }
            
            .navbar-collapse {
                background: linear-gradient(135deg, var(--bs-primary), #003366);
                padding: 1rem;
                margin-top: 0.5rem;
                border-radius: 0.5rem;
                box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            }
            
            .navbar-nav {
                padding-top: 1rem;
            }
            
            .nav-item {
                margin-bottom: 0.5rem;
            }
            
            .dropdown-menu {
                background-color: rgba(0,0,0,0.2);
                box-shadow: none;
            }
            
            .theme-toggle {
                margin-left: 0.5rem;
            }
        }
        
        /* Keyframe Animations */
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
            40% { transform: translateY(-10px); }
            60% { transform: translateY(-5px); }
        }
        
        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Smooth Scroll Behavior */
        html {
            scroll-behavior: smooth;
        }
    </style>
</head>
<body>
    <!-- Navigation Bar with Enhanced Animations -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-nav">
        <div class="container">
            <!-- Logo with Hover Effect -->
            <a class="navbar-brand d-flex align-items-center" href="index.php" style="transition: all 0.3s ease;">
                <img src="assets/images/logo.png" alt="Berekke Logo" class="me-2 animate__animated animate__fadeIn">
            </a>
            
            <!-- Animated Mobile Toggle -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <!-- Navigation Items -->
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link animate__animated animate__fadeIn" href="index.php" style="animation-delay: 0.1s;">
                            <i class="fas fa-home me-1"></i>Home
                        </a>
                    </li>
                    
                    <!-- Tools Dropdown with Animation -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle animate__animated animate__fadeIn" href="#" role="button" data-bs-toggle="dropdown" style="animation-delay: 0.2s;">
                            <i class="fas fa-tools me-1"></i>Tools
                        </a>
                        <ul class="dropdown-menu animate__animated animate__fadeIn">
                            <li><a class="dropdown-item" href="ai_tools.php">
                                <i class="fas fa-robot me-2"></i>AI Tools
                            </a></li>
                            <li><a class="dropdown-item" href="doc_tools.php">
                                <i class="fas fa-file-alt me-2"></i>Doc Tools
                            </a></li>
                            <li><a class="dropdown-item" href="image_tool.php">
                                <i class="fas fa-image me-2"></i>Image Tools
                            </a></li>
                            <li><a class="dropdown-item" href="running_chart_generator.php">
                                <i class="fas fa-chart-line me-2"></i>Running Chart Generator
                            </a></li>
                        </ul>
                    </li>
                    
                    <!-- Quick Tools Dropdown with Animation -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle animate__animated animate__fadeIn" href="#" role="button" data-bs-toggle="dropdown" style="animation-delay: 0.3s;">
                            <i class="fas fa-gavel me-1"></i>Quick Tools
                        </a>
                        <ul class="dropdown-menu animate__animated animate__fadeIn">
                            <li><a class="dropdown-item" href="penal_code.php">
                                <i class="fas fa-balance-scale me-2"></i>Penal Code
                            </a></li>
                            <li><a class="dropdown-item" href="criminal_procedure_code_act.php">
                                <i class="fas fa-clipboard-list me-2"></i>Criminal Procedure Code
                            </a></li>
                            <li><a class="dropdown-item" href="evidence_ordinance.php">
                                <i class="fas fa-search me-2"></i>Evidence Ordinance
                            </a></li>
                        </ul>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link animate__animated animate__fadeIn" href="downloads.php" style="animation-delay: 0.4s;">
                            <i class="fas fa-download me-1"></i>Downloads
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link animate__animated animate__fadeIn" href="blogs.php" style="animation-delay: 0.5s;">
                            <i class="fas fa-blog me-1"></i>Blogs
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link animate__animated animate__fadeIn" href="about_us.php" style="animation-delay: 0.6s;">
                            <i class="fas fa-info-circle me-1"></i>About Us
                        </a>
                    </li>
                </ul>
                
                <!-- Right Side Items with Animation -->
                <div class="d-flex align-items-center animate__animated animate__fadeIn" style="animation-delay: 0.7s;">
                    <!-- Theme Toggle with Tooltip -->
                    <button class="theme-toggle me-3" onclick="toggleTheme()" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Toggle Dark/Light Mode">
                        <i class="fas fa-sun" id="theme-icon"></i>
                    </button>
                    
                    <?php if ($user): ?>
                        <!-- User Menu with Animation -->
                        <div class="dropdown">
                            <button class="btn btn-link dropdown-toggle text-white text-decoration-none p-0 d-flex align-items-center" 
                                    type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <img src="<?php echo PROFILE_PICS_PATH . $user['profile_picture']; ?>" 
                                     alt="Profile" class="user-avatar me-2">
                                <span><?php echo htmlspecialchars($user['first_name']); ?></span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end animate__animated animate__fadeIn">
                                <li><a class="dropdown-item" href="profile.php">
                                    <i class="fas fa-user me-2"></i>Profile
                                </a></li>
                                <li><a class="dropdown-item" href="my_bookmarks.php">
                                    <i class="fas fa-bookmark me-2"></i>My Bookmarks
                                </a></li>
                                <?php if ($user['role'] === 'admin'): ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="admin/admin_index.php">
                                    <i class="fas fa-cog me-2"></i>Admin Dashboard
                                </a></li>
                                <?php endif; ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="logout.php">
                                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                                </a></li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <!-- Login Button with Animation -->
                        <a href="login.php" class="btn btn-outline-light animate__animated animate__pulse" style="animation-iteration-count: 2;">
                            <i class="fas fa-sign-in-alt me-1"></i>Login
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Enhanced Theme Toggle Functionality
        function toggleTheme() {
            const html = document.documentElement;
            const currentTheme = html.getAttribute('data-bs-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            const icon = document.getElementById('theme-icon');
            
            // Add transition class
            html.classList.add('theme-transition');
            
            // Set timeout to remove transition class after animation
            setTimeout(() => {
                html.classList.remove('theme-transition');
            }, 300);
            
            html.setAttribute('data-bs-theme', newTheme);
            icon.className = newTheme === 'dark' ? 'fas fa-moon' : 'fas fa-sun';
            
            // Add animation to icon
            icon.classList.add('animate__animated', 'animate__rubberBand');
            setTimeout(() => {
                icon.classList.remove('animate__animated', 'animate__rubberBand');
            }, 1000);
            
            // Save preference
            localStorage.setItem('theme', newTheme);
        }
        
        // Load saved theme
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize tooltips
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
            
            const savedTheme = localStorage.getItem('theme') || 'light';
            const html = document.documentElement;
            const icon = document.getElementById('theme-icon');
            
            html.setAttribute('data-bs-theme', savedTheme);
            icon.className = savedTheme === 'dark' ? 'fas fa-moon' : 'fas fa-sun';
            
            // Animate navbar items sequentially
            const navItems = document.querySelectorAll('.animate__fadeIn');
            navItems.forEach((item, index) => {
                item.style.animationDelay = `${index * 0.1}s`;
            });
        });
        
        // Enhanced Navbar scroll effect
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });
        
        // Mobile menu close on click
        document.querySelectorAll('.nav-link:not(.dropdown-toggle)').forEach(link => {
            link.addEventListener('click', () => {
                const navbarCollapse = document.querySelector('.navbar-collapse');
                if (navbarCollapse.classList.contains('show')) {
                    const bsCollapse = new bootstrap.Collapse(navbarCollapse);
                    bsCollapse.hide();
                }
            });
        });
    </script>
</body>
</html>