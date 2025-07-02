<?php
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

if (!$config_loaded) {
    die('Configuration file not found. Please ensure config/config.php exists.');
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
    
    <style>
        :root {
            --bs-primary: #0056b3;
            --bs-secondary: #6c757d;
            --navbar-height: 80px;
        }
        
        body {
            font-family: 'Noto Sans Sinhala', sans-serif;
            padding-top: var(--navbar-height);
        }
        
        .navbar {
            height: var(--navbar-height);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .navbar-brand img {
            height: 50px;
            width: auto;
        }
        
        .theme-toggle {
            background: none;
            border: none;
            color: var(--bs-navbar-color);
            font-size: 1.2rem;
            padding: 0.5rem;
            border-radius: 50%;
            transition: all 0.3s ease;
        }
        
        .theme-toggle:hover {
            background-color: var(--bs-navbar-hover-color);
            color: var(--bs-navbar-active-color);
        }
        
        [data-bs-theme="dark"] {
            --bs-body-bg: #1a1a1a;
            --bs-body-color: #fff;
        }
        
        .sticky-nav {
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1030;
            transition: all 0.3s ease;
        }
        
        .navbar-nav .dropdown-menu {
            border: none;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
        
        .user-avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            object-fit: cover;
        }
        
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
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-nav">
        <div class="container">
            <!-- Logo -->
            <a class="navbar-brand d-flex align-items-center" href="index.php">
                <img src="assets/images/logo.png" alt="Berekke Logo" class="me-2">
                <span class="fw-bold">Berekke</span>
            </a>
            
            <!-- Mobile Toggle -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <!-- Navigation Items -->
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">
                            <i class="fas fa-home me-1"></i>Home
                        </a>
                    </li>
                    
                    <!-- Tools Dropdown -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-tools me-1"></i>Tools
                        </a>
                        <ul class="dropdown-menu">
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
                    
                    <!-- Quick Tools Dropdown -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-gavel me-1"></i>Quick Tools
                        </a>
                        <ul class="dropdown-menu">
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
                        <a class="nav-link" href="downloads.php">
                            <i class="fas fa-download me-1"></i>Downloads
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link" href="blogs.php">
                            <i class="fas fa-blog me-1"></i>Blogs
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link" href="about_us.php">
                            <i class="fas fa-info-circle me-1"></i>About Us
                        </a>
                    </li>
                </ul>
                
                <!-- Right Side Items -->
                <div class="d-flex align-items-center">
                    <!-- Theme Toggle -->
                    <button class="theme-toggle me-3" onclick="toggleTheme()" title="Toggle Dark/Light Mode">
                        <i class="fas fa-sun" id="theme-icon"></i>
                    </button>
                    
                    <?php if ($user): ?>
                        <!-- User Menu -->
                        <div class="dropdown">
                            <button class="btn btn-link dropdown-toggle text-white text-decoration-none p-0" 
                                    type="button" data-bs-toggle="dropdown">
                                <img src="<?php echo PROFILE_PICS_PATH . $user['profile_picture']; ?>" 
                                     alt="Profile" class="user-avatar me-2">
                                <span><?php echo htmlspecialchars($user['first_name']); ?></span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
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
                        <!-- Login Button -->
                        <a href="login.php" class="btn btn-outline-light">
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
        // Theme Toggle Functionality
        function toggleTheme() {
            const html = document.documentElement;
            const currentTheme = html.getAttribute('data-bs-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            const icon = document.getElementById('theme-icon');
            
            html.setAttribute('data-bs-theme', newTheme);
            icon.className = newTheme === 'dark' ? 'fas fa-moon' : 'fas fa-sun';
            
            // Save preference
            localStorage.setItem('theme', newTheme);
        }
        
        // Load saved theme
        document.addEventListener('DOMContentLoaded', function() {
            const savedTheme = localStorage.getItem('theme') || 'light';
            const html = document.documentElement;
            const icon = document.getElementById('theme-icon');
            
            html.setAttribute('data-bs-theme', savedTheme);
            icon.className = savedTheme === 'dark' ? 'fas fa-moon' : 'fas fa-sun';
        });
        
        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 100) {
                navbar.style.backgroundColor = 'rgba(0, 86, 179, 0.95)';
            } else {
                navbar.style.backgroundColor = '';
            }
        });
    </script>