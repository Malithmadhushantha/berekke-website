<?php
require_once 'config/config.php';

$page_title = "Criminal Procedure Code of Sri Lanka";
$search_query = '';
$search_results = [];
$total_results = 0;
$current_page = 1;
$items_per_page = 10;

// Handle search
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search_query = cleanInput($_GET['search']);
    $current_page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $offset = ($current_page - 1) * $items_per_page;
    
    // Search in multiple fields
    $search_sql = "SELECT * FROM criminal_procedure_code WHERE 
                   section_number LIKE :search OR 
                   section_name LIKE :search OR 
                   section_topic LIKE :search OR 
                   section_text LIKE :search OR
                   part_name LIKE :search OR
                   chapter_name LIKE :search
                   ORDER BY CAST(section_number AS UNSIGNED) ASC
                   LIMIT :offset, :limit";
    
    $stmt = $pdo->prepare($search_sql);
    $search_param = '%' . $search_query . '%';
    $stmt->bindParam(':search', $search_param);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindParam(':limit', $items_per_page, PDO::PARAM_INT);
    $stmt->execute();
    $search_results = $stmt->fetchAll();
    
    // Get total count for pagination
    $count_sql = "SELECT COUNT(*) FROM criminal_procedure_code WHERE 
                  section_number LIKE :search OR 
                  section_name LIKE :search OR 
                  section_topic LIKE :search OR 
                  section_text LIKE :search OR
                  part_name LIKE :search OR
                  chapter_name LIKE :search";
    $stmt = $pdo->prepare($count_sql);
    $stmt->bindParam(':search', $search_param);
    $stmt->execute();
    $total_results = $stmt->fetchColumn();
}

// Handle bookmark toggle
if (isset($_POST['toggle_bookmark']) && isLoggedIn()) {
    $section_id = intval($_POST['section_id']);
    $user_id = $_SESSION['user_id'];
    
    // Check if bookmark exists
    $stmt = $pdo->prepare("SELECT id FROM user_bookmarks WHERE user_id = ? AND table_name = 'criminal_procedure_code' AND section_id = ?");
    $stmt->execute([$user_id, $section_id]);
    $bookmark = $stmt->fetch();
    
    if ($bookmark) {
        // Remove bookmark
        $stmt = $pdo->prepare("DELETE FROM user_bookmarks WHERE id = ?");
        $stmt->execute([$bookmark['id']]);
        $bookmark_status = 'removed';
    } else {
        // Add bookmark
        $stmt = $pdo->prepare("INSERT INTO user_bookmarks (user_id, table_name, section_id) VALUES (?, 'criminal_procedure_code', ?)");
        $stmt->execute([$user_id, $section_id]);
        $bookmark_status = 'added';
    }
    
    echo json_encode(['status' => $bookmark_status]);
    exit();
}

// Handle note saving
if (isset($_POST['save_note']) && isLoggedIn()) {
    $section_id = intval($_POST['section_id']);
    $note = cleanInput($_POST['note']);
    $user_id = $_SESSION['user_id'];
    
    // Update or insert bookmark with note
    $stmt = $pdo->prepare("SELECT id FROM user_bookmarks WHERE user_id = ? AND table_name = 'criminal_procedure_code' AND section_id = ?");
    $stmt->execute([$user_id, $section_id]);
    $bookmark = $stmt->fetch();
    
    if ($bookmark) {
        $stmt = $pdo->prepare("UPDATE user_bookmarks SET notes = ? WHERE id = ?");
        $stmt->execute([$note, $bookmark['id']]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO user_bookmarks (user_id, table_name, section_id, notes) VALUES (?, 'criminal_procedure_code', ?, ?)");
        $stmt->execute([$user_id, $section_id, $note]);
    }
    
    echo json_encode(['status' => 'saved']);
    exit();
}

include 'includes/header.php';
?>

<div class="container py-4">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="header-section animate__animated animate__fadeInDown">
                <div class="header-background">
                    <div class="row align-items-center">
                        <div class="col-lg-8">
                            <div class="header-content">
                                <div class="header-icon mb-3">
                                    <i class="fas fa-clipboard-list"></i>
                                </div>
                                <h1 class="header-title mb-2">
                                    Sri Lanka Criminal Procedure Code
                                </h1>
                                <p class="header-subtitle mb-0">
                                    අපරාධ නඩු විධාන සංග්‍රහය - Complete searchable database of Criminal Procedure Code sections
                                </p>
                                <div class="header-stats mt-3">
                                    <div class="stat-item">
                                        <i class="fas fa-book"></i>
                                        <span>Comprehensive Database</span>
                                    </div>
                                    <div class="stat-item">
                                        <i class="fas fa-search"></i>
                                        <span>Advanced Search</span>
                                    </div>
                                    <div class="stat-item">
                                        <i class="fas fa-bookmark"></i>
                                        <span>Personal Notes</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 text-end">
                            <div class="header-illustration">
                                <div class="illustration-circle">
                                    <i class="fas fa-balance-scale fa-4x"></i>
                                </div>
                                <div class="floating-icons">
                                    <i class="fas fa-gavel floating-icon icon-1"></i>
                                    <i class="fas fa-handcuffs floating-icon icon-2"></i>
                                    <i class="fas fa-user-shield floating-icon icon-3"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Advanced Search Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="search-section animate__animated animate__fadeInUp" data-delay="0.2s">
                <div class="card border-0 shadow-lg">
                    <div class="card-header bg-gradient-success text-white">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-search-plus me-3 fa-lg"></i>
                            <h5 class="mb-0">Advanced Search & Navigation</h5>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <form method="GET" action="" class="search-form">
                            <div class="row align-items-end">
                                <div class="col-lg-8 col-md-7 mb-3">
                                    <label for="search" class="form-label fw-semibold">
                                        <i class="fas fa-search me-2 text-success"></i>Search Criminal Procedure Code
                                    </label>
                                    <div class="input-group input-group-lg">
                                        <span class="input-group-text bg-success text-white">
                                            <i class="fas fa-clipboard-list"></i>
                                        </span>
                                        <input type="text" class="form-control" id="search" name="search" 
                                               value="<?php echo htmlspecialchars($search_query); ?>"
                                               placeholder="Search by section number, procedure, topic, or keywords...">
                                        <button type="button" class="btn btn-outline-secondary" onclick="clearSearch()" title="Clear search">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                    <div class="form-text">
                                        <i class="fas fa-lightbulb me-1 text-warning"></i>
                                        Try searching: "arrest", "bail", "investigation", "court procedure", or section numbers
                                    </div>
                                </div>
                                <div class="col-lg-2 col-md-3 mb-3">
                                    <button type="submit" class="btn btn-success btn-lg w-100 search-btn">
                                        <i class="fas fa-search me-2"></i>Search
                                    </button>
                                </div>
                                <div class="col-lg-2 col-md-2 mb-3">
                                    <a href="criminal_procedure_code_act.php" class="btn btn-outline-secondary btn-lg w-100">
                                        <i class="fas fa-refresh me-2"></i>Reset
                                    </a>
                                </div>
                            </div>
                        </form>
                        
                        <?php if (isLoggedIn()): ?>
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="quick-actions">
                                    <h6 class="text-success mb-3">
                                        <i class="fas fa-bolt me-2"></i>Quick Actions
                                    </h6>
                                    <div class="d-flex flex-wrap gap-2">
                                        <a href="my_bookmarks.php?type=criminal_procedure_code" class="btn btn-outline-warning btn-sm">
                                            <i class="fas fa-bookmark me-1"></i>My Bookmarks
                                        </a>
                                        <button class="btn btn-outline-info btn-sm" onclick="showQuickSearch()">
                                            <i class="fas fa-bolt me-1"></i>Quick Search
                                        </button>
                                        <button class="btn btn-outline-primary btn-sm" onclick="showAdvancedFilters()">
                                            <i class="fas fa-filter me-1"></i>Advanced Filters
                                        </button>
                                        <button class="btn btn-outline-success btn-sm" onclick="exportResults()">
                                            <i class="fas fa-download me-1"></i>Export Results
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Search Results or Browse Categories -->
    <?php if (!empty($search_query)): ?>
        <!-- Search Results Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="results-header animate__animated animate__fadeInUp" data-delay="0.3s">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h3 class="text-success mb-2">
                                <i class="fas fa-search-plus me-2"></i>Search Results
                            </h3>
                            <p class="text-muted mb-0">
                                Found <strong class="text-success"><?php echo $total_results; ?></strong> sections for 
                                "<strong><?php echo htmlspecialchars($search_query); ?></strong>"
                            </p>
                        </div>
                        <div class="results-actions">
                            <?php if (isLoggedIn() && !empty($search_results)): ?>
                            <button class="btn btn-outline-success btn-sm me-2" onclick="bookmarkAllResults()">
                                <i class="fas fa-bookmark me-1"></i>Bookmark All
                            </button>
                            <button class="btn btn-outline-primary btn-sm" onclick="exportResults()">
                                <i class="fas fa-download me-1"></i>Export Results
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <?php if ($total_results > 0): ?>
                    <div class="search-summary">
                        <div class="summary-stats">
                            <div class="stat-badge">
                                <i class="fas fa-file-alt text-primary"></i>
                                <span><?php echo count($search_results); ?> sections shown</span>
                            </div>
                            <div class="stat-badge">
                                <i class="fas fa-pages text-info"></i>
                                <span>Page <?php echo $current_page; ?> of <?php echo ceil($total_results / $items_per_page); ?></span>
                            </div>
                            <div class="stat-badge">
                                <i class="fas fa-clock text-warning"></i>
                                <span>Updated <?php echo date('M Y'); ?></span>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Search Results Display -->
        <?php if (!empty($search_results)): ?>
            <div class="search-results">
                <?php foreach ($search_results as $index => $section): ?>
                <div class="section-card animate__animated animate__fadeInUp" data-delay="<?php echo ($index * 0.1 + 0.4); ?>s">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header section-header">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="section-info">
                                    <div class="section-badge">
                                        <span class="badge bg-success fs-6">Section <?php echo htmlspecialchars($section['section_number']); ?></span>
                                        <?php if (!empty($section['sub_section_number'])): ?>
                                        <span class="badge bg-outline-success ms-1">Sub <?php echo htmlspecialchars($section['sub_section_number']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <h5 class="section-title mt-2 mb-0">
                                        <?php echo htmlspecialchars($section['section_name']); ?>
                                    </h5>
                                </div>
                                <?php if (isLoggedIn()): ?>
                                <div class="section-actions">
                                    <div class="dropdown">
                                        <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" 
                                                data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li>
                                                <a class="dropdown-item" href="#" onclick="toggleBookmark(<?php echo $section['id']; ?>)">
                                                    <i class="fas fa-bookmark me-2 text-warning"></i>Toggle Bookmark
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="#" onclick="addNote(<?php echo $section['id']; ?>)">
                                                    <i class="fas fa-sticky-note me-2 text-info"></i>Add Note
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="#" onclick="shareSection(<?php echo $section['id']; ?>)">
                                                    <i class="fas fa-share me-2 text-primary"></i>Share Section
                                                </a>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <a class="dropdown-item" href="#" onclick="printSection(<?php echo $section['id']; ?>)">
                                                    <i class="fas fa-print me-2 text-secondary"></i>Print Section
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="card-body section-content">
                            <!-- Section Metadata -->
                            <?php if (!empty($section['part_name']) || !empty($section['chapter_name'])): ?>
                            <div class="section-metadata mb-3">
                                <?php if (!empty($section['part_name'])): ?>
                                <span class="metadata-item">
                                    <i class="fas fa-layer-group me-1 text-primary"></i>
                                    <strong>Part:</strong> <?php echo htmlspecialchars($section['part_name']); ?>
                                </span>
                                <?php endif; ?>
                                <?php if (!empty($section['chapter_name'])): ?>
                                <span class="metadata-item">
                                    <i class="fas fa-book-open me-1 text-info"></i>
                                    <strong>Chapter:</strong> <?php echo htmlspecialchars($section['chapter_name']); ?>
                                </span>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                            
                            <!-- Section Topic -->
                            <?php if (!empty($section['section_topic'])): ?>
                            <div class="section-topic mb-3">
                                <h6 class="text-success fw-semibold">
                                    <i class="fas fa-tag me-2"></i><?php echo htmlspecialchars($section['section_topic']); ?>
                                </h6>
                            </div>
                            <?php endif; ?>
                            
                            <!-- Main Section Text -->
                            <div class="section-text">
                                <?php echo nl2br(htmlspecialchars($section['section_text'])); ?>
                            </div>
                            
                            <!-- Explanations and Illustrations -->
                            <?php if (!empty($section['explanation_1'])): ?>
                            <div class="section-explanation mt-4">
                                <h6 class="explanation-title">
                                    <i class="fas fa-lightbulb me-2 text-warning"></i>Explanation
                                </h6>
                                <div class="explanation-content">
                                    <?php echo nl2br(htmlspecialchars($section['explanation_1'])); ?>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($section['illustrations_1'])): ?>
                            <div class="section-illustration mt-4">
                                <h6 class="illustration-title">
                                    <i class="fas fa-example me-2 text-info"></i>Illustrations
                                </h6>
                                <div class="illustration-content">
                                    <?php echo nl2br(htmlspecialchars($section['illustrations_1'])); ?>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($section['amendments'])): ?>
                            <div class="section-amendments mt-4">
                                <h6 class="amendments-title">
                                    <i class="fas fa-edit me-2 text-danger"></i>Amendments
                                </h6>
                                <div class="amendments-content">
                                    <?php echo nl2br(htmlspecialchars($section['amendments'])); ?>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="card-footer section-footer">
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    <i class="fas fa-hashtag me-1"></i>ID: <?php echo $section['id']; ?>
                                    <span class="mx-2">|</span>
                                    <i class="fas fa-calendar me-1"></i>Last Updated: Recently
                                </small>
                                <div class="section-tags">
                                    <span class="badge bg-light text-dark">Criminal Law</span>
                                    <span class="badge bg-light text-dark">Procedure</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($total_results > $items_per_page): ?>
            <div class="pagination-section mt-5 animate__animated animate__fadeInUp" data-delay="0.8s">
                <nav aria-label="Search results pagination">
                    <ul class="pagination justify-content-center pagination-lg">
                        <?php
                        $total_pages = ceil($total_results / $items_per_page);
                        $start_page = max(1, $current_page - 2);
                        $end_page = min($total_pages, $start_page + 4);
                        
                        if ($current_page > 1):
                        ?>
                        <li class="page-item">
                            <a class="page-link" href="?search=<?php echo urlencode($search_query); ?>&page=<?php echo $current_page - 1; ?>">
                                <i class="fas fa-chevron-left me-1"></i>Previous
                            </a>
                        </li>
                        <?php endif; ?>
                        
                        <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                        <li class="page-item <?php echo $i == $current_page ? 'active' : ''; ?>">
                            <a class="page-link" href="?search=<?php echo urlencode($search_query); ?>&page=<?php echo $i; ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                        <?php endfor; ?>
                        
                        <?php if ($current_page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?search=<?php echo urlencode($search_query); ?>&page=<?php echo $current_page + 1; ?>">
                                Next<i class="fas fa-chevron-right ms-1"></i>
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </nav>
                
                <div class="pagination-info text-center mt-3">
                    <small class="text-muted">
                        Showing <?php echo (($current_page - 1) * $items_per_page) + 1; ?> to 
                        <?php echo min($current_page * $items_per_page, $total_results); ?> of 
                        <?php echo $total_results; ?> results
                    </small>
                </div>
            </div>
            <?php endif; ?>

        <?php else: ?>
            <!-- No Results Found -->
            <div class="no-results text-center py-5 animate__animated animate__fadeInUp" data-delay="0.4s">
                <div class="no-results-icon mb-4">
                    <i class="fas fa-search fa-4x text-muted"></i>
                </div>
                <h4 class="text-muted mb-3">No Results Found</h4>
                <p class="text-muted mb-4">
                    We couldn't find any sections matching "<strong><?php echo htmlspecialchars($search_query); ?></strong>"
                </p>
                <div class="search-suggestions">
                    <h6 class="text-success mb-3">Try these suggestions:</h6>
                    <div class="d-flex flex-wrap justify-content-center gap-2">
                        <button class="btn btn-outline-success btn-sm" onclick="searchSuggestion('arrest')">Arrest Procedures</button>
                        <button class="btn btn-outline-primary btn-sm" onclick="searchSuggestion('bail')">Bail Provisions</button>
                        <button class="btn btn-outline-info btn-sm" onclick="searchSuggestion('investigation')">Investigation</button>
                        <button class="btn btn-outline-warning btn-sm" onclick="searchSuggestion('court')">Court Procedures</button>
                    </div>
                </div>
                <div class="mt-4">
                    <a href="criminal_procedure_code_act.php" class="btn btn-success">
                        <i class="fas fa-arrow-left me-2"></i>Browse All Categories
                    </a>
                </div>
            </div>
        <?php endif; ?>

    <?php else: ?>
        <!-- Browse by Categories -->
        <div class="browse-categories">
            <div class="row mb-4">
                <div class="col-12">
                    <div class="categories-header text-center animate__animated animate__fadeInUp" data-delay="0.3s">
                        <h3 class="text-success mb-3">
                            <i class="fas fa-th-large me-2"></i>Browse by Category
                        </h3>
                        <p class="text-muted">
                            Explore Criminal Procedure Code sections organized by key legal areas
                        </p>
                    </div>
                </div>
            </div>

            <div class="categories-grid">
                <div class="row g-4">
                    <!-- Arrest Procedures -->
                    <div class="col-lg-4 col-md-6">
                        <div class="category-card animate__animated animate__fadeInUp" data-delay="0.4s">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-body text-center p-4">
                                    <div class="category-icon mb-3">
                                        <i class="fas fa-handcuffs fa-3x text-success"></i>
                                    </div>
                                    <h5 class="card-title text-success">Arrest Procedures</h5>
                                    <p class="card-text text-muted mb-4">
                                        Arrest warrants, procedures, suspect rights, and custody regulations
                                    </p>
                                    <div class="category-stats mb-3">
                                        <span class="badge bg-success bg-opacity-10 text-success me-2">
                                            <i class="fas fa-file-alt me-1"></i>45+ Sections
                                        </span>
                                        <span class="badge bg-info bg-opacity-10 text-info">
                                            <i class="fas fa-star me-1"></i>Essential
                                        </span>
                                    </div>
                                    <button class="btn btn-success" onclick="searchCategory('arrest OR warrant OR custody')">
                                        <i class="fas fa-search me-2"></i>Explore
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Investigation Procedures -->
                    <div class="col-lg-4 col-md-6">
                        <div class="category-card animate__animated animate__fadeInUp" data-delay="0.5s">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-body text-center p-4">
                                    <div class="category-icon mb-3">
                                        <i class="fas fa-search fa-3x text-primary"></i>
                                    </div>
                                    <h5 class="card-title text-primary">Investigation</h5>
                                    <p class="card-text text-muted mb-4">
                                        Search, seizure, investigation procedures, and evidence collection
                                    </p>
                                    <div class="category-stats mb-3">
                                        <span class="badge bg-primary bg-opacity-10 text-primary me-2">
                                            <i class="fas fa-file-alt me-1"></i>60+ Sections
                                        </span>
                                        <span class="badge bg-warning bg-opacity-10 text-warning">
                                            <i class="fas fa-certificate me-1"></i>Core
                                        </span>
                                    </div>
                                    <button class="btn btn-primary" onclick="searchCategory('investigation OR search OR seizure OR evidence')">
                                        <i class="fas fa-search me-2"></i>Explore
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Court Procedures -->
                    <div class="col-lg-4 col-md-6">
                        <div class="category-card animate__animated animate__fadeInUp" data-delay="0.6s">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-body text-center p-4">
                                    <div class="category-icon mb-3">
                                        <i class="fas fa-university fa-3x text-warning"></i>
                                    </div>
                                    <h5 class="card-title text-warning">Court Procedures</h5>
                                    <p class="card-text text-muted mb-4">
                                        Court proceedings, bail, trial procedures, and judicial processes
                                    </p>
                                    <div class="category-stats mb-3">
                                        <span class="badge bg-warning bg-opacity-10 text-warning me-2">
                                            <i class="fas fa-file-alt me-1"></i>55+ Sections
                                        </span>
                                        <span class="badge bg-danger bg-opacity-10 text-danger">
                                            <i class="fas fa-gavel me-1"></i>Critical
                                        </span>
                                    </div>
                                    <button class="btn btn-warning" onclick="searchCategory('court OR trial OR bail OR proceeding')">
                                        <i class="fas fa-search me-2"></i>Explore
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Bail and Remand -->
                    <div class="col-lg-4 col-md-6">
                        <div class="category-card animate__animated animate__fadeInUp" data-delay="0.7s">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-body text-center p-4">
                                    <div class="category-icon mb-3">
                                        <i class="fas fa-balance-scale fa-3x text-info"></i>
                                    </div>
                                    <h5 class="card-title text-info">Bail & Remand</h5>
                                    <p class="card-text text-muted mb-4">
                                        Bail provisions, remand procedures, and pre-trial detention
                                    </p>
                                    <div class="category-stats mb-3">
                                        <span class="badge bg-info bg-opacity-10 text-info me-2">
                                            <i class="fas fa-file-alt me-1"></i>25+ Sections
                                        </span>
                                        <span class="badge bg-success bg-opacity-10 text-success">
                                            <i class="fas fa-shield-alt me-1"></i>Rights
                                        </span>
                                    </div>
                                    <button class="btn btn-info" onclick="searchCategory('bail OR remand OR detention')">
                                        <i class="fas fa-search me-2"></i>Explore
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Appeals & Reviews -->
                    <div class="col-lg-4 col-md-6">
                        <div class="category-card animate__animated animate__fadeInUp" data-delay="0.8s">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-body text-center p-4">
                                    <div class="category-icon mb-3">
                                        <i class="fas fa-file-invoice fa-3x text-secondary"></i>
                                    </div>
                                    <h5 class="card-title text-secondary">Appeals & Reviews</h5>
                                    <p class="card-text text-muted mb-4">
                                        Appeal procedures, judicial review, and higher court processes
                                    </p>
                                    <div class="category-stats mb-3">
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary me-2">
                                            <i class="fas fa-file-alt me-1"></i>30+ Sections
                                        </span>
                                        <span class="badge bg-primary bg-opacity-10 text-primary">
                                            <i class="fas fa-arrow-up me-1"></i>Advanced
                                        </span>
                                    </div>
                                    <button class="btn btn-secondary" onclick="searchCategory('appeal OR review OR revision')">
                                        <i class="fas fa-search me-2"></i>Explore
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- General Provisions -->
                    <div class="col-lg-4 col-md-6">
                        <div class="category-card animate__animated animate__fadeInUp" data-delay="0.9s">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-body text-center p-4">
                                    <div class="category-icon mb-3">
                                        <i class="fas fa-cogs fa-3x text-dark"></i>
                                    </div>
                                    <h5 class="card-title text-dark">General Provisions</h5>
                                    <p class="card-text text-muted mb-4">
                                        Definitions, general principles, and foundational procedures
                                    </p>
                                    <div class="category-stats mb-3">
                                        <span class="badge bg-dark bg-opacity-10 text-dark me-2">
                                            <i class="fas fa-file-alt me-1"></i>20+ Sections
                                        </span>
                                        <span class="badge bg-info bg-opacity-10 text-info">
                                            <i class="fas fa-info me-1"></i>Basic
                                        </span>
                                    </div>
                                    <button class="btn btn-dark" onclick="searchCategory('general OR definition OR interpretation')">
                                        <i class="fas fa-search me-2"></i>Explore
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Popular Sections -->
        <div class="popular-sections mt-5">
            <div class="row">
                <div class="col-12">
                    <div class="popular-header text-center mb-4 animate__animated animate__fadeInUp" data-delay="1.0s">
                        <h4 class="text-success mb-3">
                            <i class="fas fa-star me-2"></i>Frequently Referenced Sections
                        </h4>
                        <p class="text-muted">Most accessed sections by law enforcement professionals</p>
                    </div>
                </div>
            </div>
            
            <div class="row g-3">
                <div class="col-md-6 animate__animated animate__fadeInUp" data-delay="1.1s">
                    <div class="popular-section-card">
                        <div class="d-flex align-items-center p-3 border rounded">
                            <div class="section-number-badge me-3">
                                <span class="badge bg-success fs-6">41</span>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1">Arrest without Warrant</h6>
                                <small class="text-muted">When police may arrest without warrant</small>
                            </div>
                            <button class="btn btn-sm btn-outline-success" onclick="searchSection('41')">
                                <i class="fas fa-eye me-1"></i>View
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 animate__animated animate__fadeInUp" data-delay="1.2s">
                    <div class="popular-section-card">
                        <div class="d-flex align-items-center p-3 border rounded">
                            <div class="section-number-badge me-3">
                                <span class="badge bg-primary fs-6">154</span>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1">Police to Report Cases</h6>
                                <small class="text-muted">Information to be recorded by police</small>
                            </div>
                            <button class="btn btn-sm btn-outline-primary" onclick="searchSection('154')">
                                <i class="fas fa-eye me-1"></i>View
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 animate__animated animate__fadeInUp" data-delay="1.3s">
                    <div class="popular-section-card">
                        <div class="d-flex align-items-center p-3 border rounded">
                            <div class="section-number-badge me-3">
                                <span class="badge bg-warning fs-6">161</span>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1">Examination of Witnesses</h6>
                                <small class="text-muted">Police examination of witnesses</small>
                            </div>
                            <button class="btn btn-sm btn-outline-warning" onclick="searchSection('161')">
                                <i class="fas fa-eye me-1"></i>View
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 animate__animated animate__fadeInUp" data-delay="1.4s">
                    <div class="popular-section-card">
                        <div class="d-flex align-items-center p-3 border rounded">
                            <div class="section-number-badge me-3">
                                <span class="badge bg-info fs-6">167</span>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1">Investigation by Police</h6>
                                <small class="text-muted">Police investigation procedures</small>
                            </div>
                            <button class="btn btn-sm btn-outline-info" onclick="searchSection('167')">
                                <i class="fas fa-eye me-1"></i>View
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Note Modal -->
<div class="modal fade" id="noteModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="fas fa-sticky-note me-2"></i>Add Personal Note
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="noteForm">
                    <input type="hidden" id="noteSectionId">
                    <div class="mb-3">
                        <label for="noteText" class="form-label fw-semibold">Your Note</label>
                        <textarea class="form-control" id="noteText" rows="5" 
                                  placeholder="Add your personal notes, observations, or reminders about this section..."></textarea>
                        <div class="form-text">
                            <i class="fas fa-info-circle me-1"></i>
                            Your notes are private and will help you reference important sections quickly.
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="noteCategory" class="form-label fw-semibold">Category (Optional)</label>
                        <select class="form-select" id="noteCategory">
                            <option value="">Select category...</option>
                            <option value="important">Important</option>
                            <option value="reference">Quick Reference</option>
                            <option value="case-related">Case Related</option>
                            <option value="training">Training Material</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Cancel
                </button>
                <button type="button" class="btn btn-success" onclick="saveNote()">
                    <i class="fas fa-save me-1"></i>Save Note
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Quick Search Modal -->
<div class="modal fade" id="quickSearchModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">
                    <i class="fas fa-bolt me-2"></i>Quick Search
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-4">Search commonly referenced procedures and sections:</p>
                <div class="quick-search-grid">
                    <div class="row g-2">
                        <div class="col-md-6">
                            <button class="btn btn-outline-primary w-100 mb-2" onclick="quickSearch('arrest without warrant')">
                                Arrest without Warrant
                            </button>
                        </div>
                        <div class="col-md-6">
                            <button class="btn btn-outline-success w-100 mb-2" onclick="quickSearch('investigation')">
                                Investigation Procedures
                            </button>
                        </div>
                        <div class="col-md-6">
                            <button class="btn btn-outline-warning w-100 mb-2" onclick="quickSearch('bail')">
                                Bail Provisions
                            </button>
                        </div>
                        <div class="col-md-6">
                            <button class="btn btn-outline-info w-100 mb-2" onclick="quickSearch('search seizure')">
                                Search & Seizure
                            </button>
                        </div>
                        <div class="col-md-6">
                            <button class="btn btn-outline-secondary w-100 mb-2" onclick="quickSearch('witness examination')">
                                Witness Examination
                            </button>
                        </div>
                        <div class="col-md-6">
                            <button class="btn btn-outline-danger w-100 mb-2" onclick="quickSearch('court proceedings')">
                                Court Proceedings
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Header Section Styles */
.header-section {
    margin-bottom: 2rem;
}

.header-background {
    background: linear-gradient(135deg, #198754, #20c997);
    color: white;
    border-radius: 20px;
    padding: 2rem;
    position: relative;
    overflow: hidden;
}

.header-background::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -50%;
    width: 100%;
    height: 100%;
    background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
    animation: float 6s ease-in-out infinite;
}

.header-content {
    position: relative;
    z-index: 2;
}

.header-icon i {
    font-size: 3rem;
    opacity: 0.9;
}

.header-title {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
}

.header-subtitle {
    font-size: 1.1rem;
    opacity: 0.9;
    line-height: 1.6;
}

.header-stats {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}

.stat-item {
    display: flex;
    align-items: center;
    background: rgba(255,255,255,0.1);
    padding: 0.5rem 1rem;
    border-radius: 25px;
    backdrop-filter: blur(10px);
    font-size: 0.9rem;
}

.stat-item i {
    margin-right: 0.5rem;
}

.header-illustration {
    position: relative;
    text-align: center;
}

.illustration-circle {
    background: rgba(255,255,255,0.1);
    border-radius: 50%;
    width: 120px;
    height: 120px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
    backdrop-filter: blur(10px);
}

.floating-icons {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
}

.floating-icon {
    position: absolute;
    opacity: 0.3;
    animation: float 4s ease-in-out infinite;
}

.icon-1 {
    top: 10%;
    right: 20%;
    animation-delay: 0s;
}

.icon-2 {
    bottom: 20%;
    left: 10%;
    animation-delay: 1.3s;
}

.icon-3 {
    top: 60%;
    right: 10%;
    animation-delay: 2.6s;
}

/* Search Section Styles */
.search-section {
    margin-bottom: 2rem;
}

.bg-gradient-success {
    background: linear-gradient(45deg, #198754, #20c997) !important;
}

.search-form .input-group-text {
    border: none;
    background: #198754;
}

.search-btn {
    background: linear-gradient(45deg, #198754, #20c997);
    border: none;
    transition: all 0.3s ease;
}

.search-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(25, 135, 84, 0.3);
}

.quick-actions {
    background: rgba(25, 135, 84, 0.05);
    border-radius: 10px;
    padding: 1rem;
}

/* Results Section Styles */
.results-header {
    background: linear-gradient(135deg, rgba(25, 135, 84, 0.1), rgba(32, 201, 151, 0.05));
    border-radius: 15px;
    padding: 1.5rem;
    border: 2px solid rgba(25, 135, 84, 0.2);
}

.search-summary {
    margin-top: 1rem;
}

.summary-stats {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}

.stat-badge {
    display: flex;
    align-items: center;
    background: var(--bs-body-bg);
    border: 1px solid var(--bs-border-color);
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.875rem;
}

.stat-badge i {
    margin-right: 0.5rem;
}

/* Section Card Styles */
.section-card {
    margin-bottom: 2rem;
    opacity: 0;
    transform: translateY(30px);
    transition: all 0.6s ease;
}

.section-card.animate__fadeInUp {
    opacity: 1;
    transform: translateY(0);
}

.section-card .card {
    transition: all 0.3s ease;
    border-radius: 15px;
    overflow: hidden;
}

.section-card .card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(0,0,0,0.1) !important;
}

.section-header {
    background: linear-gradient(135deg, rgba(25, 135, 84, 0.1), rgba(32, 201, 151, 0.05));
    border-bottom: 3px solid #198754;
}

.section-badge .badge {
    font-size: 0.8rem;
    padding: 0.5rem 1rem;
}

.section-title {
    color: var(--bs-success);
    font-weight: 600;
    margin-top: 0.5rem;
}

.section-content {
    padding: 1.5rem;
}

.section-metadata {
    background: rgba(25, 135, 84, 0.05);
    border-radius: 8px;
    padding: 0.75rem;
    border-left: 4px solid #198754;
}

.metadata-item {
    display: inline-block;
    margin-right: 1rem;
    font-size: 0.9rem;
}

.section-topic h6 {
    background: linear-gradient(45deg, #198754, #20c997);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.section-text {
    line-height: 1.8;
    text-align: justify;
    font-size: 1rem;
    color: var(--bs-body-color);
}

/* Explanation and Illustration Styles */
.section-explanation,
.section-illustration,
.section-amendments {
    background: var(--bs-body-bg);
    border: 1px solid var(--bs-border-color);
    border-radius: 10px;
    padding: 1rem;
    margin-top: 1rem;
}

.explanation-title,
.illustration-title,
.amendments-title {
    font-weight: 600;
    margin-bottom: 0.75rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid rgba(25, 135, 84, 0.2);
}

.explanation-content,
.illustration-content,
.amendments-content {
    line-height: 1.6;
    color: var(--bs-body-color);
}

.section-footer {
    background: rgba(0,0,0,0.02);
    border-top: 1px solid var(--bs-border-color);
}

.section-tags .badge {
    font-size: 0.75rem;
}

/* Category Card Styles */
.category-card {
    opacity: 0;
    transform: translateY(30px);
    transition: all 0.6s ease;
}

.category-card.animate__fadeInUp {
    opacity: 1;
    transform: translateY(0);
}

.category-card .card {
    transition: all 0.3s ease;
    border-radius: 15px;
    overflow: hidden;
}

.category-card .card:hover {
    transform: translateY(-10px);
    box-shadow: 0 20px 40px rgba(0,0,0,0.1) !important;
}

.category-icon {
    transition: all 0.3s ease;
}

.category-card:hover .category-icon i {
    transform: scale(1.1);
}

.category-stats {
    margin: 1rem 0;
}

.category-stats .badge {
    font-size: 0.75rem;
    padding: 0.4rem 0.8rem;
    border-radius: 15px;
}

/* Popular Sections */
.popular-section-card {
    transition: all 0.3s ease;
}

.popular-section-card:hover {
    transform: translateY(-3px);
}

.popular-section-card .border {
    transition: all 0.3s ease;
}

.popular-section-card:hover .border {
    border-color: #198754 !important;
    box-shadow: 0 5px 15px rgba(25, 135, 84, 0.2);
}

.section-number-badge .badge {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    font-size: 1rem;
    font-weight: bold;
}

/* No Results Styles */
.no-results {
    background: var(--bs-body-bg);
    border-radius: 15px;
    border: 2px dashed var(--bs-border-color);
}

.no-results-icon {
    opacity: 0.5;
}

.search-suggestions .btn {
    margin: 0.25rem;
}

/* Pagination Styles */
.pagination-section {
    margin-top: 3rem;
}

.pagination-lg .page-link {
    padding: 0.75rem 1.25rem;
    font-size: 1rem;
    border-radius: 10px;
    margin: 0 0.25rem;
    transition: all 0.3s ease;
}

.pagination-lg .page-item.active .page-link {
    background: linear-gradient(45deg, #198754, #20c997);
    border-color: #198754;
}

.pagination-lg .page-link:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(25, 135, 84, 0.2);
}

/* Modal Styles */
.modal-content {
    border-radius: 15px;
    border: none;
    box-shadow: 0 20px 60px rgba(0,0,0,0.2);
}

.modal-header {
    border-radius: 15px 15px 0 0;
    border-bottom: none;
}

.quick-search-grid .btn {
    border-radius: 10px;
    transition: all 0.3s ease;
}

.quick-search-grid .btn:hover {
    transform: translateY(-2px);
}

/* Dark Mode Adjustments */
[data-bs-theme="dark"] .header-background {
    background: linear-gradient(135deg, #198754, #20c997);
}

[data-bs-theme="dark"] .section-card .card,
[data-bs-theme="dark"] .category-card .card {
    background: rgba(255,255,255,0.05);
    border-color: rgba(255,255,255,0.1);
}

[data-bs-theme="dark"] .section-header {
    background: linear-gradient(135deg, rgba(25, 135, 84, 0.2), rgba(32, 201, 151, 0.1));
}

[data-bs-theme="dark"] .section-explanation,
[data-bs-theme="dark"] .section-illustration,
[data-bs-theme="dark"] .section-amendments {
    background: rgba(255,255,255,0.05);
    border-color: rgba(255,255,255,0.1);
}

[data-bs-theme="dark"] .quick-actions {
    background: rgba(25, 135, 84, 0.1);
}

[data-bs-theme="dark"] .results-header {
    background: linear-gradient(135deg, rgba(25, 135, 84, 0.2), rgba(32, 201, 151, 0.1));
    border-color: rgba(25, 135, 84, 0.3);
}

[data-bs-theme="dark"] .stat-badge {
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
    .header-title {
        font-size: 1.5rem;
    }
    
    .header-subtitle {
        font-size: 1rem;
    }
    
    .header-stats {
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .stat-item {
        justify-content: center;
    }
    
    .floating-icons {
        display: none;
    }
    
    .summary-stats {
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .results-actions {
        margin-top: 1rem;
    }
    
    .section-metadata .metadata-item {
        display: block;
        margin-bottom: 0.5rem;
    }
    
    .quick-search-grid .btn {
        margin-bottom: 0.5rem;
    }
}

/* Print Styles */
@media print {
    .search-section,
    .results-actions,
    .section-actions,
    .pagination-section {
        display: none;
    }
    
    .section-card .card {
        box-shadow: none;
        border: 1px solid #ddd;
    }
}
</style>

<script>
// Search and Navigation Functions
function searchCategory(query) {
    document.getElementById('search').value = query;
    document.querySelector('.search-form').submit();
}

function searchSection(sectionNumber) {
    document.getElementById('search').value = sectionNumber;
    document.querySelector('.search-form').submit();
}

function searchSuggestion(query) {
    document.getElementById('search').value = query;
    document.querySelector('.search-form').submit();
}

function clearSearch() {
    document.getElementById('search').value = '';
    document.getElementById('search').focus();
}

// Bookmark Functions
function toggleBookmark(sectionId) {
    <?php if (!isLoggedIn()): ?>
    showLoginPrompt();
    return;
    <?php else: ?>
    const formData = new FormData();
    formData.append('toggle_bookmark', '1');
    formData.append('section_id', sectionId);
    
    fetch('criminal_procedure_code_act.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'added') {
            showNotification('Section bookmarked successfully!', 'success');
        } else {
            showNotification('Bookmark removed.', 'info');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred while toggling bookmark.', 'error');
    });
    <?php endif; ?>
}

function bookmarkAllResults() {
    if (confirm('Bookmark all search results? This will add all visible sections to your bookmarks.')) {
        const sectionCards = document.querySelectorAll('.section-card');
        sectionCards.forEach((card, index) => {
            setTimeout(() => {
                const sectionId = card.querySelector('[onclick*="toggleBookmark"]')?.getAttribute('onclick')?.match(/\d+/)?.[0];
                if (sectionId) {
                    toggleBookmark(parseInt(sectionId));
                }
            }, index * 200);
        });
    }
}

// Note Functions
function addNote(sectionId) {
    <?php if (!isLoggedIn()): ?>
    showLoginPrompt();
    return;
    <?php endif; ?>
    
    document.getElementById('noteSectionId').value = sectionId;
    document.getElementById('noteText').value = '';
    document.getElementById('noteCategory').value = '';
    const modal = new bootstrap.Modal(document.getElementById('noteModal'));
    modal.show();
}

function saveNote() {
    const sectionId = document.getElementById('noteSectionId').value;
    const note = document.getElementById('noteText').value;
    const category = document.getElementById('noteCategory').value;
    
    if (!note.trim()) {
        showNotification('Please enter a note.', 'warning');
        return;
    }
    
    const formData = new FormData();
    formData.append('save_note', '1');
    formData.append('section_id', sectionId);
    formData.append('note', note);
    
    fetch('criminal_procedure_code_act.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'saved') {
            showNotification('Note saved successfully!', 'success');
            bootstrap.Modal.getInstance(document.getElementById('noteModal')).hide();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred while saving the note.', 'error');
    });
}

// Quick Search Functions
function showQuickSearch() {
    const modal = new bootstrap.Modal(document.getElementById('quickSearchModal'));
    modal.show();
}

function quickSearch(query) {
    document.getElementById('search').value = query;
    bootstrap.Modal.getInstance(document.getElementById('quickSearchModal')).hide();
    document.querySelector('.search-form').submit();
}

// Utility Functions
function shareSection(sectionId) {
    const url = window.location.origin + '/criminal_procedure_code_act.php?section=' + sectionId;
    if (navigator.share) {
        navigator.share({
            title: 'Criminal Procedure Code Section',
            url: url
        });
    } else {
        navigator.clipboard.writeText(url).then(() => {
            showNotification('Section link copied to clipboard!', 'success');
        });
    }
}

function printSection(sectionId) {
    window.print();
}

function exportResults() {
    showNotification('Export functionality will be implemented soon.', 'info');
}

function showAdvancedFilters() {
    showNotification('Advanced filters coming soon!', 'info');
}

function showLoginPrompt() {
    if (confirm('Please login to use this feature. Would you like to go to the login page?')) {
        window.location.href = 'login.php';
    }
}

function showNotification(message, type = 'info') {
    const alertClass = type === 'success' ? 'alert-success' : 
                      type === 'error' ? 'alert-danger' : 
                      type === 'warning' ? 'alert-warning' : 'alert-info';
    
    const notification = document.createElement('div');
    notification.className = `alert ${alertClass} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 100px; right: 20px; z-index: 9999; min-width: 300px;';
    notification.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-triangle' : 'info-circle'} me-2"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 5000);
}

// Animation and Interaction Effects
document.addEventListener('DOMContentLoaded', function() {
    // Intersection Observer for animations
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

    // Observe elements for animation
    document.querySelectorAll('.search-section, .section-card, .category-card, .popular-sections, .results-header, .pagination-section').forEach(element => {
        observer.observe(element);
    });

    // Enhanced search input
    const searchInput = document.getElementById('search');
    if (searchInput) {
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                document.querySelector('.search-form').submit();
            }
        });
        
        // Auto-focus on search input
        searchInput.focus();
    }

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

    // Highlight search terms in results
    const searchQuery = '<?php echo addslashes($search_query); ?>';
    if (searchQuery) {
        highlightSearchTerms(searchQuery);
    }
});

function highlightSearchTerms(query) {
    const terms = query.split(' ');
    terms.forEach(term => {
        if (term.length > 2) {
            highlightTerm(term);
        }
    });
}

function highlightTerm(term) {
    const regex = new RegExp(`(${term})`, 'gi');
    const elements = document.querySelectorAll('.section-text, .explanation-content, .illustration-content');
    
    elements.forEach(element => {
        element.innerHTML = element.innerHTML.replace(regex, '<mark>$1</mark>');
    });
}

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    // Ctrl/Cmd + K for quick search
    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
        e.preventDefault();
        document.getElementById('search').focus();
    }
    
    // Escape to clear search
    if (e.key === 'Escape') {
        const searchInput = document.getElementById('search');
        if (searchInput === document.activeElement) {
            clearSearch();
        }
    }
});
</script>

<?php include 'includes/footer.php'; ?>