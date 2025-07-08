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

// Load AdSense configuration
$adsense_config_paths = [
    'config/adsense_config.php',
    '../config/adsense_config.php',
    '../../config/adsense_config.php'
];

foreach ($adsense_config_paths as $path) {
    if (file_exists($path)) {
        require_once $path;
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

// ==============================================
// DETECT CURRENT DIRECTORY AND SET PATHS
// ==============================================
$is_admin = (basename(dirname($_SERVER['PHP_SELF'])) === 'admin');
$base_path = $is_admin ? '../' : '';

// Set correct paths based on directory level
$logo_path = $base_path . 'assets/images/logo.png';
$favicon_path = $base_path . 'assets/images/favicon.ico';
$apple_touch_icon_path = $base_path . 'assets/images/apple-touch-icon.png';
$favicon_svg_path = $base_path . 'assets/images/favicon.svg';
$favicon_96_path = $base_path . 'assets/images/favicon-96x96.png';
$manifest_path = $base_path . 'assets/images/site.webmanifest';
$default_avatar_path = $base_path . 'assets/images/avatars/default_avatar.jpg';

// Fix profile picture path
if ($is_admin) {
    $profile_pics_url = '../' . PROFILE_PICS_PATH;
} else {
    $profile_pics_url = PROFILE_PICS_PATH;
}

$user = getUserInfo();
$current_page = basename($_SERVER['PHP_SELF']);

// Simplified cache buster (only for development)
$cache_buster = '';
if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
    $cache_buster = '?v=' . date('YmdHis');
}
?>
<!DOCTYPE html>
<html lang="si" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    
    <!-- TITLE MUST BE FIRST --> 
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) . ' - ' . SITE_NAME : SITE_NAME; ?></title>
    
    <!-- FAVICONS MUST BE IMMEDIATELY AFTER TITLE -->
    <!-- Standard Favicon (MOST IMPORTANT - MUST BE FIRST) -->
    <link rel="icon" type="image/x-icon" href="<?php echo $favicon_path . $cache_buster; ?>">
    <link rel="shortcut icon" type="image/x-icon" href="<?php echo $favicon_path . $cache_buster; ?>">
    
    <!-- PNG Favicons -->
    <link rel="icon" type="image/png" sizes="16x16" href="<?php echo $base_path; ?>assets/images/favicon-16x16.png<?php echo $cache_buster; ?>">
    <link rel="icon" type="image/png" sizes="32x32" href="<?php echo $base_path; ?>assets/images/favicon-32x32.png<?php echo $cache_buster; ?>">
    <link rel="icon" type="image/png" sizes="96x96" href="<?php echo $favicon_96_path . $cache_buster; ?>">
    
    <!-- Apple Touch Icons -->
    <link rel="apple-touch-icon" sizes="180x180" href="<?php echo $apple_touch_icon_path . $cache_buster; ?>">
    <link rel="apple-touch-icon" sizes="152x152" href="<?php echo $base_path; ?>assets/images/apple-touch-icon-152x152.png<?php echo $cache_buster; ?>">
    <link rel="apple-touch-icon" sizes="144x144" href="<?php echo $base_path; ?>assets/images/apple-touch-icon-144x144.png<?php echo $cache_buster; ?>">
    <link rel="apple-touch-icon" sizes="120x120" href="<?php echo $base_path; ?>assets/images/apple-touch-icon-120x120.png<?php echo $cache_buster; ?>">
    <link rel="apple-touch-icon" sizes="114x114" href="<?php echo $base_path; ?>assets/images/apple-touch-icon-114x114.png<?php echo $cache_buster; ?>">
    <link rel="apple-touch-icon" sizes="76x76" href="<?php echo $base_path; ?>assets/images/apple-touch-icon-76x76.png<?php echo $cache_buster; ?>">
    <link rel="apple-touch-icon" sizes="72x72" href="<?php echo $base_path; ?>assets/images/apple-touch-icon-72x72.png<?php echo $cache_buster; ?>">
    <link rel="apple-touch-icon" sizes="60x60" href="<?php echo $base_path; ?>assets/images/apple-touch-icon-60x60.png<?php echo $cache_buster; ?>">
    <link rel="apple-touch-icon" sizes="57x57" href="<?php echo $base_path; ?>assets/images/apple-touch-icon-57x57.png<?php echo $cache_buster; ?>">
    
    <!-- Android Chrome Icons -->
    <link rel="icon" type="image/png" sizes="192x192" href="<?php echo $base_path; ?>assets/images/android-chrome-192x192.png<?php echo $cache_buster; ?>">
    <link rel="icon" type="image/png" sizes="512x512" href="<?php echo $base_path; ?>assets/images/android-chrome-512x512.png<?php echo $cache_buster; ?>">
    
    <!-- Modern Browsers (SVG comes after PNG) -->
    <link rel="icon" type="image/svg+xml" href="<?php echo $favicon_svg_path . $cache_buster; ?>">
    
    <!-- Safari Pinned Tab -->
    <link rel="mask-icon" href="<?php echo $base_path; ?>assets/images/safari-pinned-tab.svg" color="#0056b3">
    
    <!-- PWA Manifest -->
    <link rel="manifest" href="<?php echo $manifest_path . $cache_buster; ?>">
    
    <!-- Microsoft Tiles -->
    <meta name="msapplication-TileImage" content="<?php echo $base_path; ?>assets/images/mstile-144x144.png">
    <meta name="msapplication-TileColor" content="#0056b3">
    <meta name="msapplication-config" content="<?php echo $base_path; ?>assets/images/browserconfig.xml">
    
    <!-- SEO Meta Tags -->
    <meta name="description" content="<?php echo isset($meta_description) ? htmlspecialchars($meta_description) : 'Berekke Website - A comprehensive digital platform for Sri Lankan Police officers with legal documents, tools, and resources.'; ?>">
    <meta name="keywords" content="Sri Lankan Police, Legal Documents, Penal Code, Criminal Procedure Code, Evidence Ordinance, Police Tools, Law Enforcement">
    <meta name="author" content="Berekke Development Team">
    <meta name="robots" content="index, follow">
    
    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="<?php echo isset($page_title) ? htmlspecialchars($page_title) . ' - ' . SITE_NAME : SITE_NAME; ?>">
    <meta property="og:description" content="<?php echo isset($meta_description) ? htmlspecialchars($meta_description) : 'A comprehensive digital platform for Sri Lankan Police officers'; ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo SITE_URL . $_SERVER['REQUEST_URI']; ?>">
    <meta property="og:image" content="<?php echo SITE_URL . '/' . $logo_path; ?>">
    <meta property="og:site_name" content="<?php echo SITE_NAME; ?>">
    
    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo isset($page_title) ? htmlspecialchars($page_title) . ' - ' . SITE_NAME : SITE_NAME; ?>">
    <meta name="twitter:description" content="<?php echo isset($meta_description) ? htmlspecialchars($meta_description) : 'A comprehensive digital platform for Sri Lankan Police officers'; ?>">
    <meta name="twitter:image" content="<?php echo SITE_URL . '/' . $logo_path; ?>">
    
    <!-- Mobile App Meta Tags -->
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Berekke">
    <meta name="application-name" content="Berekke">
    <meta name="theme-color" content="#0056b3">
    <meta name="msapplication-navbutton-color" content="#0056b3">
    <meta name="msapplication-tooltip" content="Berekke - Sri Lankan Police Platform">
    
    <!-- Preconnect for Performance -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdn.jsdelivr.net">
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">
    
    <!-- AdSense Preconnect (when enabled) -->
    <?php if (function_exists('shouldShowAds') && shouldShowAds()): ?>
    <link rel="preconnect" href="https://pagead2.googlesyndication.com">
    <link rel="preconnect" href="https://googleads.g.doubleclick.net">
    <link rel="preconnect" href="https://partner.googleadservices.com">
    <?php endif; ?>
    
    <!-- DNS Prefetch for External Resources -->
    <link rel="dns-prefetch" href="//fonts.googleapis.com">
    <link rel="dns-prefetch" href="//cdn.jsdelivr.net">
    <link rel="dns-prefetch" href="//cdnjs.cloudflare.com">
    
    <!-- Google AdSense -->
    <?php if (function_exists('getAdSenseScript')) echo getAdSenseScript(); ?>
    
    <!-- Stylesheets -->
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Sinhala:wght@300;400;500;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" integrity="sha512-c42qTSw/wPZ3/5LBzD+Bw5f7bSF2oxou6wEb+I/lqeaKV5FDIfMvvRp772y4jcJLKuGUOpbJMdg/BTl50fJYAw==" crossorigin="anonymous">
    
    <!-- AdSense CSS -->
    <?php if (function_exists('getAdSenseCSS')) echo getAdSenseCSS(); ?>
    
    <!-- JSON-LD Structured Data -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "WebSite",
        "name": "<?php echo SITE_NAME; ?>",
        "url": "<?php echo SITE_URL; ?>",
        "description": "A comprehensive digital platform for Sri Lankan Police officers",
        "publisher": {
            "@type": "Organization",
            "name": "Berekke Development Team"
        }
    }
    </script>
    
    <!-- AdSense Auto Ads -->
    <?php if (function_exists('getAutoAdsCode')) echo getAutoAdsCode(); ?>
    
    <style>
        :root {
            --bs-primary: #0056b3;
            --bs-secondary: rgb(103, 185, 176);
            --navbar-height: 80px;
            --transition-time: 0.3s;
        }
        
        body {
            font-family: 'Inter', 'Noto Sans Sinhala', sans-serif;
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
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }
        
        .navbar.scrolled {
            height: 60px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.15);
            background: linear-gradient(135deg, rgba(0, 86, 179, 0.95), rgba(0, 51, 102, 0.95));
        }
        
        .navbar-brand img {
            height: 50px;
            width: auto;
            transition: all var(--transition-time) ease;
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));
        }
        
        .navbar.scrolled .navbar-brand img {
            height: 40px;
        }
        
        /* Admin badge indicator */
        .admin-indicator {
            background: linear-gradient(45deg, #ff6b6b, #ffd93d);
            color: #000;
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.2rem 0.5rem;
            border-radius: 0.5rem;
            margin-left: 0.5rem;
            animation: glow 2s ease-in-out infinite alternate;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        @keyframes glow {
            from { box-shadow: 0 0 5px rgba(255, 107, 107, 0.5); }
            to { box-shadow: 0 0 20px rgba(255, 107, 107, 0.8); }
        }
        
        /* Navigation Link Active States */
        .nav-link.active {
            background-color: rgba(255, 255, 255, 0.2);
            border-radius: 0.375rem;
            font-weight: 600;
        }
        
        .nav-link {
            transition: all var(--transition-time) ease;
            border-radius: 0.375rem;
            margin: 0 0.2rem;
        }
        
        .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
            transform: translateY(-1px);
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
        
        [data-bs-theme="dark"] .navbar {
            background: linear-gradient(135deg, #1a1a1a, #2d3748);
        }
        
        [data-bs-theme="dark"] .navbar.scrolled {
            background: linear-gradient(135deg, rgba(26, 26, 26, 0.95), rgba(45, 55, 72, 0.95));
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
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
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
        
        /* Loading Spinner */
        .loading-spinner {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 9999;
            display: none;
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
                backdrop-filter: blur(10px);
                -webkit-backdrop-filter: blur(10px);
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
            
            .admin-indicator {
                font-size: 0.65rem;
                padding: 0.1rem 0.3rem;
            }
        }
        
        @media (max-width: 576px) {
            .navbar-brand img {
                height: 40px;
            }
            
            .user-avatar {
                width: 30px;
                height: 30px;
            }
            
            .navbar-brand span {
                font-size: 0.9rem;
            }
        }
        
        /* Keyframe Animations */
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
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
        
        /* Print Styles */
        @media print {
            .navbar, .sticky-nav, .ad-container {
                display: none !important;
            }
            
            body {
                padding-top: 0 !important;
            }
        }
    </style>
</head>
<body>
    <!-- Loading Spinner -->
    <div class="loading-spinner" id="loadingSpinner">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

    <!-- Header Ad -->
    <?php if (function_exists('shouldShowAds') && shouldShowAds() && !$is_admin): ?>
    <div class="header-ad">
        <?php echo showHeaderAd(); ?>
    </div>
    <?php endif; ?>

    <!-- Navigation Bar with Enhanced Animations -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-nav" role="navigation" aria-label="Main Navigation">
        <div class="container">
            <!-- Logo with Hover Effect -->
            <a class="navbar-brand d-flex align-items-center" href="<?php echo $base_path; ?>index.php" style="transition: all 0.3s ease;" aria-label="Berekke Home">
                <img src="<?php echo $logo_path; ?>" alt="Berekke Logo" class="me-2 animate__animated animate__fadeIn" loading="eager">
                <?php if ($is_admin): ?>
                <span class="admin-indicator" aria-label="Admin Mode">ADMIN</span>
                <?php endif; ?>
            </a>
            
            <!-- Animated Mobile Toggle -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <!-- Navigation Items -->
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto" role="menubar">
                    <li class="nav-item" role="none">
                        <a class="nav-link animate__animated animate__fadeIn <?php echo ($current_page === 'index.php') ? 'active' : ''; ?>" 
                           href="<?php echo $base_path; ?>index.php" style="animation-delay: 0.1s;" role="menuitem">
                            <i class="fas fa-home me-1" aria-hidden="true"></i>Home
                        </a>
                    </li>
                    
                    <!-- Tools Dropdown with Animation -->
                    <li class="nav-item dropdown" role="none">
                        <a class="nav-link dropdown-toggle animate__animated animate__fadeIn" href="#" role="button" data-bs-toggle="dropdown" 
                           style="animation-delay: 0.2s;" aria-expanded="false" aria-haspopup="true">
                            <i class="fas fa-tools me-1" aria-hidden="true"></i>Tools
                        </a>
                        <ul class="dropdown-menu animate__animated animate__fadeIn" role="menu">
                            <li role="none"><a class="dropdown-item" href="<?php echo $base_path; ?>ai_tools.php" role="menuitem">
                                <i class="fas fa-robot me-2" aria-hidden="true"></i>AI Tools
                            </a></li>
                            <li role="none"><a class="dropdown-item" href="<?php echo $base_path; ?>doc_tools.php" role="menuitem">
                                <i class="fas fa-file-alt me-2" aria-hidden="true"></i>Doc Tools
                            </a></li>
                            <li role="none"><a class="dropdown-item" href="<?php echo $base_path; ?>image_tool.php" role="menuitem">
                                <i class="fas fa-image me-2" aria-hidden="true"></i>Image Tools
                            </a></li>
                            <li role="none"><a class="dropdown-item" href="<?php echo $base_path; ?>running_chart_generator.php" role="menuitem">
                                <i class="fas fa-chart-line me-2" aria-hidden="true"></i>Running Chart Generator
                            </a></li>
                            <li role="none"><a class="dropdown-item" href="<?php echo $base_path; ?>create_report.php" role="menuitem">
                                <i class="fas fa-clipboard me-2" aria-hidden="true"></i>Report Generator
                            </a></li>
                        </ul>
                    </li>
                    
                    <!-- Quick Tools Dropdown with Animation -->
                    <li class="nav-item dropdown" role="none">
                        <a class="nav-link dropdown-toggle animate__animated animate__fadeIn" href="#" role="button" data-bs-toggle="dropdown" 
                           style="animation-delay: 0.3s;" aria-expanded="false" aria-haspopup="true">
                            <i class="fas fa-gavel me-1" aria-hidden="true"></i>Quick Tools
                        </a>
                        <ul class="dropdown-menu animate__animated animate__fadeIn" role="menu">
                            <li role="none"><a class="dropdown-item" href="<?php echo $base_path; ?>penal_code.php" role="menuitem">
                                <i class="fas fa-balance-scale me-2" aria-hidden="true"></i>Penal Code
                            </a></li>
                            <li role="none"><a class="dropdown-item" href="<?php echo $base_path; ?>criminal_procedure_code_act.php" role="menuitem">
                                <i class="fas fa-clipboard-list me-2" aria-hidden="true"></i>Criminal Procedure Code
                            </a></li>
                            <li role="none"><a class="dropdown-item" href="<?php echo $base_path; ?>evidence_ordinance.php" role="menuitem">
                                <i class="fas fa-search me-2" aria-hidden="true"></i>Evidence Ordinance
                            </a></li>
                            <li role="none"><a class="dropdown-item" href="<?php echo $base_path; ?>police_station_directory.php" role="menuitem">
                                <i class="fas fa-building me-2" aria-hidden="true"></i>Police Station Details
                             </a></li>
                            <li role="none"><a class="dropdown-item" href="<?php echo $base_path; ?>station_book_list.php" role="menuitem">
                                <i class="fas fa-book me-2" aria-hidden="true"></i>Police Station Book List
                             </a></li>
                        </ul>
                    </li>
                    
                    <li class="nav-item" role="none">
                        <a class="nav-link animate__animated animate__fadeIn <?php echo ($current_page === 'downloads.php') ? 'active' : ''; ?>" 
                           href="<?php echo $base_path; ?>downloads.php" style="animation-delay: 0.4s;" role="menuitem">
                            <i class="fas fa-download me-1" aria-hidden="true"></i>Downloads
                        </a>
                    </li>
                    
                    <li class="nav-item" role="none">
                        <a class="nav-link animate__animated animate__fadeIn <?php echo ($current_page === 'blogs.php') ? 'active' : ''; ?>" 
                           href="<?php echo $base_path; ?>blogs.php" style="animation-delay: 0.5s;" role="menuitem">
                            <i class="fas fa-blog me-1" aria-hidden="true"></i>Blogs
                        </a>
                    </li>
                    
                    <li class="nav-item" role="none">
                        <a class="nav-link animate__animated animate__fadeIn <?php echo ($current_page === 'about_us.php') ? 'active' : ''; ?>" 
                           href="<?php echo $base_path; ?>about_us.php" style="animation-delay: 0.6s;" role="menuitem">
                            <i class="fas fa-info-circle me-1" aria-hidden="true"></i>About Us
                        </a>
                    </li>
                </ul>
                
                <!-- Right Side Items with Animation -->
                <div class="d-flex align-items-center animate__animated animate__fadeIn" style="animation-delay: 0.7s;">
                    <!-- Theme Toggle with Tooltip -->
                    <button class="theme-toggle me-3" onclick="toggleTheme()" data-bs-toggle="tooltip" 
                            data-bs-placement="bottom" title="Toggle Dark/Light Mode" aria-label="Toggle Theme">
                        <i class="fas fa-sun" id="theme-icon" aria-hidden="true"></i>
                    </button>
                    
                    <?php if ($user): ?>
                        <!-- User Menu with Animation -->
                        <div class="dropdown">
                            <button class="btn btn-link dropdown-toggle text-white text-decoration-none p-0 d-flex align-items-center" 
                                    type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="User Menu">
                                <img src="<?php echo $profile_pics_url . $user['profile_picture']; ?>" 
                                     alt="<?php echo htmlspecialchars($user['first_name']); ?>'s Profile" 
                                     class="user-avatar me-2" 
                                     onerror="this.src='<?php echo $default_avatar_path; ?>'" loading="lazy">
                                <span><?php echo htmlspecialchars($user['first_name']); ?></span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end animate__animated animate__fadeIn" role="menu">
                                <li role="none"><a class="dropdown-item" href="<?php echo $base_path; ?>profile.php" role="menuitem">
                                    <i class="fas fa-user me-2" aria-hidden="true"></i>Profile
                                </a></li>
                                <li role="none"><a class="dropdown-item" href="<?php echo $base_path; ?>my_bookmarks.php" role="menuitem">
                                    <i class="fas fa-bookmark me-2" aria-hidden="true"></i>My Bookmarks
                                </a></li>
                                <?php if ($user['role'] === 'admin'): ?>
                                <li><hr class="dropdown-divider"></li>
                                <?php if ($is_admin): ?>
                                <li role="none"><a class="dropdown-item" href="admin_index.php" role="menuitem">
                                    <i class="fas fa-tachometer-alt me-2" aria-hidden="true"></i>Admin Dashboard
                                </a></li>
                                <li role="none"><a class="dropdown-item" href="<?php echo $base_path; ?>index.php" role="menuitem">
                                    <i class="fas fa-home me-2" aria-hidden="true"></i>Back to Main Site
                                </a></li>
                                <?php else: ?>
                                <li role="none"><a class="dropdown-item" href="admin/admin_index.php" role="menuitem">
                                    <i class="fas fa-cog me-2" aria-hidden="true"></i>Admin Dashboard
                                </a></li>
                                <?php endif; ?>
                                <?php endif; ?>
                                <li><hr class="dropdown-divider"></li>
                                <li role="none"><a class="dropdown-item" href="<?php echo $base_path; ?>logout.php" role="menuitem">
                                    <i class="fas fa-sign-out-alt me-2" aria-hidden="true"></i>Logout
                                </a></li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <!-- Login Button with Animation -->
                        <a href="<?php echo $base_path; ?>login.php" class="btn btn-outline-light animate__animated animate__pulse" 
                           style="animation-iteration-count: 2;" aria-label="Login to Berekke">
                            <i class="fas fa-sign-in-alt me-1" aria-hidden="true"></i>Login
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Mobile Ad (Fixed Bottom) -->
    <?php if (function_exists('shouldShowAds') && shouldShowAds()): ?>
    <div class="mobile-ad">
        <?php echo showMobileAd(); ?>
    </div>
    <?php endif; ?>

    <!-- Bootstrap JS with Integrity -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" 
            integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" 
            crossorigin="anonymous"></script>
    
    <!-- AdSense Ad Blocker Detection -->
    <?php if (function_exists('getAdBlockerDetectionScript')) echo getAdBlockerDetectionScript(); ?>
    
    <script>
        // Enhanced Theme Toggle Functionality
        function toggleTheme() {
            const html = document.documentElement;
            const currentTheme = html.getAttribute('data-bs-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            const icon = document.getElementById('theme-icon');
            
            html.classList.add('theme-transition');
            setTimeout(() => html.classList.remove('theme-transition'), 300);
            
            html.setAttribute('data-bs-theme', newTheme);
            icon.className = newTheme === 'dark' ? 'fas fa-moon' : 'fas fa-sun';
            
            icon.classList.add('animate__animated', 'animate__rubberBand');
            setTimeout(() => icon.classList.remove('animate__animated', 'animate__rubberBand'), 1000);
            
            localStorage.setItem('theme', newTheme);
            window.dispatchEvent(new CustomEvent('themeChanged', { detail: { theme: newTheme } }));
        }
        
        // Load saved theme and initialize
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('loadingSpinner').style.display = 'none';
            
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
            
            const savedTheme = localStorage.getItem('theme') || 
                               (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
            const html = document.documentElement;
            const icon = document.getElementById('theme-icon');
            
            html.setAttribute('data-bs-theme', savedTheme);
            icon.className = savedTheme === 'dark' ? 'fas fa-moon' : 'fas fa-sun';
            
            const navItems = document.querySelectorAll('.animate__fadeIn');
            navItems.forEach((item, index) => {
                item.style.animationDelay = `${index * 0.1}s`;
            });
            
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
                if (!localStorage.getItem('theme')) {
                    const newTheme = e.matches ? 'dark' : 'light';
                    html.setAttribute('data-bs-theme', newTheme);
                    icon.className = newTheme === 'dark' ? 'fas fa-moon' : 'fas fa-sun';
                }
            });
        });
        
        // Enhanced Navbar scroll effect
        let ticking = false;
        function updateNavbar() {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
            ticking = false;
        }
        
        window.addEventListener('scroll', function() {
            if (!ticking) {
                requestAnimationFrame(updateNavbar);
                ticking = true;
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
        
        // Keyboard navigation support
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                const openDropdowns = document.querySelectorAll('.dropdown-menu.show');
                openDropdowns.forEach(dropdown => {
                    const dropdownInstance = bootstrap.Dropdown.getInstance(dropdown.previousElementSibling);
                    if (dropdownInstance) dropdownInstance.hide();
                });
                
                const navbarCollapse = document.querySelector('.navbar-collapse.show');
                if (navbarCollapse) {
                    const bsCollapse = bootstrap.Collapse.getInstance(navbarCollapse);
                    if (bsCollapse) bsCollapse.hide();
                }
            }
        });
        
        // Add loading states for navigation links
        document.querySelectorAll('a[href]:not([href^="#"]):not([href^="javascript:"])').forEach(link => {
            link.addEventListener('click', function(e) {
                if (this.hostname === window.location.hostname) {
                    document.getElementById('loadingSpinner').style.display = 'block';
                }
            });
        });
        
        // Debug favicon loading
        console.log('Favicon path:', '<?php echo $favicon_path; ?>');
        console.log('Base path:', '<?php echo $base_path; ?>');
    </script>
</body>
</html>