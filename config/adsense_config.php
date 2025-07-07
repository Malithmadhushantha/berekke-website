<?php
// ==============================================
// GOOGLE ADSENSE CONFIGURATION
// ==============================================

// AdSense Settings
define('ADSENSE_ENABLED', false); // Set to true when approved
define('ADSENSE_PUBLISHER_ID', 'ca-pub-XXXXXXXXXXXXXXXXX'); // Replace with your Publisher ID
define('ADSENSE_TEST_MODE', true); // Set to false in production

// Ad Unit IDs (Replace with your actual ad unit IDs)
define('ADSENSE_HEADER_AD_SLOT', 'XXXXXXXXXX');
define('ADSENSE_SIDEBAR_AD_SLOT', 'XXXXXXXXXX');
define('ADSENSE_FOOTER_AD_SLOT', 'XXXXXXXXXX');
define('ADSENSE_CONTENT_AD_SLOT', 'XXXXXXXXXX');
define('ADSENSE_MOBILE_AD_SLOT', 'XXXXXXXXXX');

// Ad Display Settings
define('ADSENSE_SHOW_ON_ADMIN', false); // Don't show ads in admin panel
define('ADSENSE_SHOW_ON_LOGIN', false); // Don't show ads on login/register pages
define('ADSENSE_MIN_CONTENT_LENGTH', 300); // Minimum content length to show ads

// Pages where ads should NOT appear
$adsense_disabled_pages = [
    'login.php',
    'register.php',
    'admin_index.php',
    'forget_password.php'
];

// User roles that should not see ads
$adsense_disabled_roles = [
    'admin' // Admins don't see ads
];

// ==============================================
// ADSENSE HELPER FUNCTIONS
// ==============================================

/**
 * Check if AdSense should be displayed
 */
function shouldShowAds() {
    global $adsense_disabled_pages, $adsense_disabled_roles;
    
    // Check if AdSense is enabled
    if (!ADSENSE_ENABLED) {
        return false;
    }
    
    // Check if in admin panel
    if (ADSENSE_SHOW_ON_ADMIN === false && basename(dirname($_SERVER['PHP_SELF'])) === 'admin') {
        return false;
    }
    
    // Check current page
    $current_page = basename($_SERVER['PHP_SELF']);
    if (in_array($current_page, $adsense_disabled_pages)) {
        return false;
    }
    
    // Check user role
    if (isLoggedIn()) {
        $user = getUserInfo();
        if ($user && in_array($user['role'], $adsense_disabled_roles)) {
            return false;
        }
    }
    
    return true;
}

/**
 * Get AdSense script tag
 */
function getAdSenseScript() {
    if (!shouldShowAds()) {
        return '';
    }
    
    $publisherId = ADSENSE_PUBLISHER_ID;
    $testMode = ADSENSE_TEST_MODE ? '&adtest=on' : '';
    
    return "
    <script async src=\"https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client={$publisherId}{$testMode}\"
            crossorigin=\"anonymous\"></script>
    ";
}

/**
 * Generate AdSense auto ads code
 */
function getAutoAdsCode() {
    if (!shouldShowAds()) {
        return '';
    }
    
    $publisherId = ADSENSE_PUBLISHER_ID;
    
    return "
    <script>
        (adsbygoogle = window.adsbygoogle || []).push({
            google_ad_client: \"{$publisherId}\",
            enable_page_level_ads: true
        });
    </script>
    ";
}

/**
 * Generate display ad unit
 */
function generateAdUnit($adSlot, $style = '', $format = 'auto', $responsive = true) {
    if (!shouldShowAds()) {
        return '';
    }
    
    $publisherId = ADSENSE_PUBLISHER_ID;
    $responsiveAttr = $responsive ? 'data-full-width-responsive="true"' : '';
    $testMode = ADSENSE_TEST_MODE ? 'data-adtest="on"' : '';
    
    return "
    <div class=\"ad-container\">
        <ins class=\"adsbygoogle\"
             style=\"display:block;{$style}\"
             data-ad-client=\"{$publisherId}\"
             data-ad-slot=\"{$adSlot}\"
             data-ad-format=\"{$format}\"
             {$responsiveAttr}
             {$testMode}></ins>
        <script>
            (adsbygoogle = window.adsbygoogle || []).push({});
        </script>
    </div>
    ";
}

/**
 * Header Ad
 */
function showHeaderAd() {
    return generateAdUnit(
        ADSENSE_HEADER_AD_SLOT,
        'width:100%;height:90px;',
        'horizontal'
    );
}

/**
 * Sidebar Ad
 */
function showSidebarAd() {
    return generateAdUnit(
        ADSENSE_SIDEBAR_AD_SLOT,
        'width:300px;height:250px;',
        'rectangle'
    );
}

/**
 * Footer Ad
 */
function showFooterAd() {
    return generateAdUnit(
        ADSENSE_FOOTER_AD_SLOT,
        'width:100%;height:90px;',
        'horizontal'
    );
}

/**
 * In-Content Ad
 */
function showContentAd() {
    return generateAdUnit(
        ADSENSE_CONTENT_AD_SLOT,
        'width:100%;height:280px;',
        'rectangle'
    );
}

/**
 * Mobile Ad
 */
function showMobileAd() {
    return generateAdUnit(
        ADSENSE_MOBILE_AD_SLOT,
        'width:320px;height:50px;',
        'banner'
    );
}

/**
 * Responsive Ad (Auto-sizing)
 */
function showResponsiveAd($adSlot) {
    return generateAdUnit($adSlot, '', 'auto', true);
}

// ==============================================
// AD PLACEMENT CSS
// ==============================================
function getAdSenseCSS() {
    return "
    <style>
    .ad-container {
        margin: 20px 0;
        text-align: center;
        position: relative;
    }
    
    .ad-container::before {
        content: 'Advertisement';
        display: block;
        font-size: 12px;
        color: #666;
        margin-bottom: 5px;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    
    .header-ad {
        margin: 10px 0;
        border-top: 1px solid #eee;
        border-bottom: 1px solid #eee;
        padding: 10px 0;
    }
    
    .sidebar-ad {
        margin: 20px 0;
        position: sticky;
        top: 100px;
    }
    
    .content-ad {
        margin: 30px 0;
        clear: both;
    }
    
    .footer-ad {
        margin: 20px 0;
        border-top: 1px solid #eee;
        padding-top: 20px;
    }
    
    .mobile-ad {
        display: none;
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        z-index: 1000;
        background: white;
        box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
    }
    
    @media (max-width: 768px) {
        .mobile-ad {
            display: block;
        }
        
        .sidebar-ad {
            position: static;
            margin: 15px 0;
        }
        
        body {
            padding-bottom: 60px; /* Space for mobile ad */
        }
    }
    
    /* Dark mode compatibility */
    [data-bs-theme=\"dark\"] .ad-container::before {
        color: #ccc;
    }
    
    [data-bs-theme=\"dark\"] .header-ad,
    [data-bs-theme=\"dark\"] .footer-ad {
        border-color: #333;
    }
    
    [data-bs-theme=\"dark\"] .mobile-ad {
        background: #2d3748;
    }
    
    /* Ad blocker detection */
    .ad-blocked-message {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        padding: 1rem;
        text-align: center;
        margin: 20px 0;
        color: #6c757d;
    }
    
    [data-bs-theme=\"dark\"] .ad-blocked-message {
        background: #2d3748;
        border-color: #4a5568;
        color: #a0aec0;
    }
    </style>
    ";
}

// ==============================================
// AD BLOCKER DETECTION
// ==============================================
function getAdBlockerDetectionScript() {
    return "
    <script>
    // Simple ad blocker detection
    function detectAdBlocker() {
        const adTest = document.createElement('div');
        adTest.innerHTML = '&nbsp;';
        adTest.className = 'adsbox';
        adTest.style.position = 'absolute';
        adTest.style.left = '-10000px';
        document.body.appendChild(adTest);
        
        setTimeout(function() {
            if (adTest.offsetHeight === 0) {
                // Ad blocker detected
                showAdBlockerMessage();
            }
            document.body.removeChild(adTest);
        }, 100);
    }
    
    function showAdBlockerMessage() {
        const adContainers = document.querySelectorAll('.ad-container');
        adContainers.forEach(container => {
            if (container.querySelector('.adsbygoogle')) {
                container.innerHTML = `
                    <div class=\"ad-blocked-message\">
                        <i class=\"fas fa-info-circle me-2\"></i>
                        Please consider disabling your ad blocker to support our website.
                    </div>
                `;
            }
        });
    }
    
    // Run detection when DOM is loaded
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', detectAdBlocker);
    } else {
        detectAdBlocker();
    }
    </script>
    ";
}
?>