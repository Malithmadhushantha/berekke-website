<?php
require_once 'config/config.php';

$page_title = "Penal Code of Sri Lanka";
$search_query = '';
$search_results = [];
$total_results = 0;
$current_page = 1;
$items_per_page = 10;

// Handle AJAX search suggestions
if (isset($_GET['ajax_search']) && !empty($_GET['q'])) {
    $query = cleanInput($_GET['q']);
    $suggestions_sql = "SELECT section_number, sub_section_number, section_name, id 
                       FROM penal_code 
                       WHERE section_number LIKE :query 
                       OR section_name LIKE :query 
                       OR section_topic LIKE :query
                       ORDER BY CAST(section_number AS UNSIGNED) ASC 
                       LIMIT 10";
    
    $stmt = $pdo->prepare($suggestions_sql);
    $search_param = '%' . $query . '%';
    $stmt->bindParam(':query', $search_param);
    $stmt->execute();
    $suggestions = $stmt->fetchAll();
    
    $response = [];
    foreach ($suggestions as $suggestion) {
        $display_text = $suggestion['section_number'];
        if (!empty($suggestion['sub_section_number'])) {
            $display_text .= ' : ' . $suggestion['sub_section_number'];
        }
        $display_text .= ' : ' . $suggestion['section_name'];
        
        $response[] = [
            'id' => $suggestion['id'],
            'text' => $display_text,
            'section_number' => $suggestion['section_number'],
            'section_name' => $suggestion['section_name']
        ];
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

// Handle search
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search_query = cleanInput($_GET['search']);
    $current_page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $offset = ($current_page - 1) * $items_per_page;
    
    // Enhanced search in multiple fields
    $search_sql = "SELECT * FROM penal_code WHERE 
                   section_number LIKE :search OR 
                   section_name LIKE :search OR 
                   section_topic LIKE :search OR 
                   section_text LIKE :search OR
                   part_name LIKE :search OR
                   chapter_name LIKE :search OR
                   explanation_1 LIKE :search OR
                   explanation_2 LIKE :search OR
                   explanation_3 LIKE :search OR
                   explanation_4 LIKE :search OR
                   illustrations_1 LIKE :search OR
                   illustrations_2 LIKE :search OR
                   illustrations_3 LIKE :search OR
                   illustrations_4 LIKE :search OR
                   amendments LIKE :search
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
    $count_sql = "SELECT COUNT(*) FROM penal_code WHERE 
                  section_number LIKE :search OR 
                  section_name LIKE :search OR 
                  section_topic LIKE :search OR 
                  section_text LIKE :search OR
                  part_name LIKE :search OR
                  chapter_name LIKE :search OR
                  explanation_1 LIKE :search OR
                  explanation_2 LIKE :search OR
                  explanation_3 LIKE :search OR
                  explanation_4 LIKE :search OR
                  illustrations_1 LIKE :search OR
                  illustrations_2 LIKE :search OR
                  illustrations_3 LIKE :search OR
                  illustrations_4 LIKE :search OR
                  amendments LIKE :search";
    $stmt = $pdo->prepare($count_sql);
    $stmt->bindParam(':search', $search_param);
    $stmt->execute();
    $total_results = $stmt->fetchColumn();
}

// Handle specific section view
if (isset($_GET['section_id']) && !empty($_GET['section_id'])) {
    $section_id = intval($_GET['section_id']);
    $stmt = $pdo->prepare("SELECT * FROM penal_code WHERE id = ?");
    $stmt->execute([$section_id]);
    $single_section = $stmt->fetch();
    
    if ($single_section) {
        $search_results = [$single_section];
        $total_results = 1;
        $search_query = 'Section ' . $single_section['section_number'];
    }
}

// Handle bookmark toggle
if (isset($_POST['toggle_bookmark']) && isLoggedIn()) {
    $section_id = intval($_POST['section_id']);
    $user_id = $_SESSION['user_id'];
    
    // Check if bookmark exists
    $stmt = $pdo->prepare("SELECT id FROM user_bookmarks WHERE user_id = ? AND table_name = 'penal_code' AND section_id = ?");
    $stmt->execute([$user_id, $section_id]);
    $bookmark = $stmt->fetch();
    
    if ($bookmark) {
        // Remove bookmark
        $stmt = $pdo->prepare("DELETE FROM user_bookmarks WHERE id = ?");
        $stmt->execute([$bookmark['id']]);
        $bookmark_status = 'removed';
    } else {
        // Add bookmark
        $stmt = $pdo->prepare("INSERT INTO user_bookmarks (user_id, table_name, section_id) VALUES (?, 'penal_code', ?)");
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
    $stmt = $pdo->prepare("SELECT id FROM user_bookmarks WHERE user_id = ? AND table_name = 'penal_code' AND section_id = ?");
    $stmt->execute([$user_id, $section_id]);
    $bookmark = $stmt->fetch();
    
    if ($bookmark) {
        $stmt = $pdo->prepare("UPDATE user_bookmarks SET notes = ? WHERE id = ?");
        $stmt->execute([$note, $bookmark['id']]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO user_bookmarks (user_id, table_name, section_id, notes) VALUES (?, 'penal_code', ?, ?)");
        $stmt->execute([$user_id, $section_id, $note]);
    }
    
    echo json_encode(['status' => 'saved']);
    exit();
}

include 'includes/header.php';
?>

<!-- Hero Section -->
<div class="hero-section bg-primary text-white py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="display-4 fw-bold mb-3 animate__animated animate__fadeInDown">
                    <i class="fas fa-gavel me-2"></i>
                    Sri Lanka Penal Code
                </h1>
                <p class="lead mb-4 animate__animated animate__fadeInLeft">
                    දණ්ඩ නීති සංග්‍රහය - Complete searchable database of Sri Lankan Penal Code sections
                </p>
                <div class="d-flex gap-3 animate__animated animate__fadeInUp">
                    <a href="#search-section" class="btn btn-light btn-lg px-4">
                        <i class="fas fa-search me-2"></i>Search Sections
                    </a>
                    <a href="#browse-section" class="btn btn-outline-light btn-lg px-4">
                        <i class="fas fa-list me-2"></i>Browse Categories
                    </a>
                </div>
            </div>
            <div class="col-lg-4 d-none d-lg-block animate__animated animate__fadeIn">
                <div class="hero-image-container">
                    <i class="fas fa-balance-scale fa-8x opacity-25"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Stats Bar -->
<div class="stats-bar bg-dark text-white py-3">
    <div class="container">
        <div class="row text-center">
            <div class="col-md-4 mb-3 mb-md-0">
                <div class="stat-item">
                    <i class="fas fa-database fs-2 mb-2"></i>
                    <h3 class="mb-1"><?php echo number_format($pdo->query("SELECT COUNT(*) FROM penal_code")->fetchColumn()); ?></h3>
                    <p class="mb-0 text-muted">Total Sections</p>
                </div>
            </div>
            <div class="col-md-4 mb-3 mb-md-0">
                <div class="stat-item">
                    <i class="fas fa-book-open fs-2 mb-2"></i>
                    <h3 class="mb-1">15+</h3>
                    <p class="mb-0 text-muted">Categories</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-item">
                    <i class="fas fa-calendar-alt fs-2 mb-2"></i>
                    <h3 class="mb-1"><?php echo date('Y'); ?></h3>
                    <p class="mb-0 text-muted">Updated</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Search Section -->
<section id="search-section" class="py-5 bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card shadow-lg border-0 animate__animated animate__fadeIn">
                    <div class="card-header bg-white border-bottom-0 pb-0">
                        <h2 class="h4 mb-0 text-center">
                            <i class="fas fa-search text-primary me-2"></i>
                            Search Penal Code
                        </h2>
                    </div>
                    <div class="card-body p-4 position-relative">
                        <form method="GET" action="" id="searchForm">
                            <div class="input-group input-group-lg mb-3">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="fas fa-search text-muted"></i>
                                </span>
                                <input type="text" class="form-control border-start-0 search-input" 
                                       id="search" name="search" 
                                       value="<?php echo htmlspecialchars($search_query); ?>"
                                       placeholder="Enter section number, keywords, or topic..."
                                       autocomplete="off">
                                <button type="submit" class="btn btn-primary px-4">
                                    <i class="fas fa-search me-2"></i>Search
                                </button>
                            </div>
                            <div id="searchSuggestions" class="search-suggestions position-absolute bg-white border rounded shadow-lg d-none"></div>
                            
                            <div class="d-flex justify-content-between align-items-center mt-2">
                                <small class="text-muted">
                                    <i class="fas fa-lightbulb me-1"></i>
                                    Try: "murder", "302", "theft OR burglary", or "crimes against person"
                                </small>
                                <?php if (!empty($search_query)): ?>
                                <a href="penal_code.php" class="btn btn-sm btn-outline-secondary">
                                    <i class="fas fa-times me-1"></i>Clear Search
                                </a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Main Content Section -->
<section class="py-5">
    <div class="container">
        <?php if (!empty($search_query)): ?>
            <!-- Search Results -->
            <div class="row mb-5 animate__animated animate__fadeIn">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2 class="h4 mb-0">
                            <i class="fas fa-search me-2 text-primary"></i>
                            Search Results
                        </h2>
                        <div class="d-flex gap-2">
                            <?php if (isLoggedIn()): ?>
                            <button class="btn btn-sm btn-outline-primary" onclick="exportResults()">
                                <i class="fas fa-download me-1"></i>Export
                            </button>
                            <?php endif; ?>
                            <button class="btn btn-sm btn-outline-secondary" onclick="toggleViewMode()">
                                <i class="fas fa-th-list me-1"></i>
                                <span id="viewModeText">Compact View</span>
                            </button>
                        </div>
                    </div>
                    
                    <div class="alert alert-info mb-4">
                        <i class="fas fa-info-circle me-2"></i>
                        Found <strong><?php echo number_format($total_results); ?></strong> results for 
                        "<strong><?php echo htmlspecialchars($search_query); ?></strong>"
                    </div>
                    
                    <?php if (!empty($search_results)): ?>
                        <div class="results-container" id="resultsContainer">
                            <?php foreach ($search_results as $index => $section): ?>
                            <div class="section-card card mb-4 border-0 shadow-sm animate__animated animate__fadeInUp" style="animation-delay: <?php echo $index * 0.1; ?>s;">
                                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                    <div>
                                        <h3 class="h5 mb-1">
                                            <span class="badge bg-primary me-2">
                                                Section <?php echo htmlspecialchars($section['section_number']); ?>
                                                <?php if (!empty($section['sub_section_number'])): ?>
                                                .<?php echo htmlspecialchars($section['sub_section_number']); ?>
                                                <?php endif; ?>
                                            </span>
                                            <?php echo htmlspecialchars($section['section_name']); ?>
                                        </h3>
                                        <?php if (!empty($section['part_name']) || !empty($section['chapter_name'])): ?>
                                        <div class="text-muted small">
                                            <?php if (!empty($section['part_name'])): ?>
                                            <i class="fas fa-folder me-1"></i><?php echo htmlspecialchars($section['part_name']); ?>
                                            <?php endif; ?>
                                            <?php if (!empty($section['chapter_name'])): ?>
                                            <span class="mx-2">|</span>
                                            <i class="fas fa-book me-1"></i><?php echo htmlspecialchars($section['chapter_name']); ?>
                                            <?php endif; ?>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <?php if (isLoggedIn()): ?>
                                            <li><a class="dropdown-item" href="#" onclick="toggleBookmark(<?php echo $section['id']; ?>)">
                                                <i class="fas fa-bookmark text-warning me-2"></i>Bookmark
                                            </a></li>
                                            <li><a class="dropdown-item" href="#" onclick="addNote(<?php echo $section['id']; ?>)">
                                                <i class="fas fa-sticky-note text-info me-2"></i>Add Note
                                            </a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <?php endif; ?>
                                            <li><a class="dropdown-item" href="?section_id=<?php echo $section['id']; ?>">
                                                <i class="fas fa-external-link-alt text-primary me-2"></i>View Full
                                            </a></li>
                                            <li><a class="dropdown-item" href="#" onclick="shareSection(<?php echo $section['id']; ?>)">
                                                <i class="fas fa-share text-success me-2"></i>Share
                                            </a></li>
                                        </ul>
                                    </div>
                                </div>
                                
                                <div class="card-body">
                                    <?php if (!empty($section['section_topic'])): ?>
                                    <div class="alert alert-primary py-2 small mb-3">
                                        <i class="fas fa-tag me-2"></i>
                                        <strong>Topic:</strong> <?php echo htmlspecialchars($section['section_topic']); ?>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <div class="section-content mb-3">
                                        <h5 class="text-primary mb-3 border-bottom pb-2">
                                            <i class="fas fa-paragraph me-2"></i>Section Text
                                        </h5>
                                        <div class="content-body">
                                            <?php echo nl2br(htmlspecialchars($section['section_text'])); ?>
                                        </div>
                                    </div>
                                    
                                    <!-- Explanations and Illustrations -->
                                    <?php for ($i = 1; $i <= 4; $i++): ?>
                                        <?php if (!empty($section["explanation_$i"]) || !empty($section["illustrations_$i"])): ?>
                                        <div class="mt-4">
                                            <?php if (!empty($section["explanation_$i"])): ?>
                                            <div class="explanation-content mb-3">
                                                <h5 class="text-success mb-3 border-bottom pb-2">
                                                    <i class="fas fa-lightbulb me-2"></i>Explanation <?php echo $i; ?>
                                                </h5>
                                                <div class="content-body bg-light p-3 rounded">
                                                    <?php echo nl2br(htmlspecialchars($section["explanation_$i"])); ?>
                                                </div>
                                            </div>
                                            <?php endif; ?>
                                            
                                            <?php if (!empty($section["illustrations_$i"])): ?>
                                            <div class="illustration-content">
                                                <h5 class="text-info mb-3 border-bottom pb-2">
                                                    <i class="fas fa-example me-2"></i>Illustrations <?php echo $i; ?>
                                                </h5>
                                                <div class="content-body bg-light p-3 rounded">
                                                    <?php echo nl2br(htmlspecialchars($section["illustrations_$i"])); ?>
                                                </div>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                        <?php endif; ?>
                                    <?php endfor; ?>
                                    
                                    <?php if (!empty($section['amendments'])): ?>
                                    <div class="mt-4">
                                        <h5 class="text-warning mb-3 border-bottom pb-2">
                                            <i class="fas fa-edit me-2"></i>Amendments
                                        </h5>
                                        <div class="content-body bg-light p-3 rounded">
                                            <?php echo nl2br(htmlspecialchars($section['amendments'])); ?>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="card-footer bg-white text-muted small">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <i class="fas fa-hashtag me-1"></i>ID: <?php echo $section['id']; ?>
                                            <span class="mx-2">•</span>
                                            <i class="fas fa-calendar me-1"></i>
                                            Last Updated
                                        </div>
                                        <div>
                                            <span class="badge bg-light text-dark">Penal Code</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <!-- Pagination -->
                        <?php if ($total_results > $items_per_page): ?>
                        <nav aria-label="Search results pagination" class="mt-5">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <small class="text-muted">
                                    Showing <?php echo (($current_page - 1) * $items_per_page) + 1; ?> to 
                                    <?php echo min($current_page * $items_per_page, $total_results); ?> of 
                                    <?php echo number_format($total_results); ?> results
                                </small>
                            </div>
                            
                            <ul class="pagination justify-content-center">
                                <?php
                                $total_pages = ceil($total_results / $items_per_page);
                                $start_page = max(1, $current_page - 2);
                                $end_page = min($total_pages, $start_page + 4);
                                
                                if ($current_page > 1):
                                ?>
                                <li class="page-item">
                                    <a class="page-link" href="?search=<?php echo urlencode($search_query); ?>&page=<?php echo $current_page - 1; ?>">
                                        <i class="fas fa-chevron-left"></i> Previous
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
                                        Next <i class="fas fa-chevron-right"></i>
                                    </a>
                                </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                        <?php endif; ?>
                    <?php else: ?>
                        <!-- No Results -->
                        <div class="text-center py-5 my-5">
                            <div class="mb-4">
                                <i class="fas fa-search fa-4x text-muted opacity-50"></i>
                            </div>
                            <h4 class="mb-3">No results found</h4>
                            <p class="text-muted mb-4">
                                We couldn't find any sections matching "<strong><?php echo htmlspecialchars($search_query); ?></strong>"
                            </p>
                            <div class="d-flex justify-content-center gap-2 mb-4 flex-wrap">
                                <button class="btn btn-outline-primary" onclick="searchSection('302')">Murder (302)</button>
                                <button class="btn btn-outline-primary" onclick="searchSection('379')">Theft (379)</button>
                                <button class="btn btn-outline-primary" onclick="searchSection('322')">Assault (322)</button>
                            </div>
                            <a href="penal_code.php" class="btn btn-primary">
                                <i class="fas fa-home me-2"></i>Browse All Sections
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php else: ?>
            <!-- Browse Categories -->
            <section id="browse-section" class="mb-5 animate__animated animate__fadeIn">
                <div class="row mb-4">
                    <div class="col-12">
                        <h2 class="h4 mb-0">
                            <i class="fas fa-th-large text-primary me-2"></i>
                            Browse by Category
                        </h2>
                        <p class="text-muted">Explore the Penal Code by major categories</p>
                    </div>
                </div>
                
                <div class="row g-4">
                    <div class="col-lg-4 col-md-6">
                        <div class="category-card card h-100 border-0 shadow-sm hover-card">
                            <div class="card-body text-center p-4">
                                <div class="category-icon mb-3 text-primary">
                                    <i class="fas fa-user-shield fa-3x"></i>
                                </div>
                                <h5 class="card-title">Crimes Against Person</h5>
                                <p class="card-text text-muted">Murder, culpable homicide, assault, and related offenses</p>
                                <div class="category-stats mb-3">
                                    <small class="text-muted">
                                        <i class="fas fa-chart-bar me-1"></i>
                                        Sections 299-377
                                    </small>
                                </div>
                                <button class="btn btn-primary" onclick="searchCategory('murder OR assault OR homicide')">
                                    <i class="fas fa-search me-1"></i>Explore
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-4 col-md-6">
                        <div class="category-card card h-100 border-0 shadow-sm hover-card">
                            <div class="card-body text-center p-4">
                                <div class="category-icon mb-3 text-success">
                                    <i class="fas fa-home fa-3x"></i>
                                </div>
                                <h5 class="card-title">Property Crimes</h5>
                                <p class="card-text text-muted">Theft, burglary, criminal trespass, and property offenses</p>
                                <div class="category-stats mb-3">
                                    <small class="text-muted">
                                        <i class="fas fa-chart-bar me-1"></i>
                                        Sections 378-462
                                    </small>
                                </div>
                                <button class="btn btn-success" onclick="searchCategory('theft OR burglary OR trespass')">
                                    <i class="fas fa-search me-1"></i>Explore
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-4 col-md-6">
                        <div class="category-card card h-100 border-0 shadow-sm hover-card">
                            <div class="card-body text-center p-4">
                                <div class="category-icon mb-3 text-warning">
                                    <i class="fas fa-handshake fa-3x"></i>
                                </div>
                                <h5 class="card-title">Public Order</h5>
                                <p class="card-text text-muted">Unlawful assembly, rioting, and public nuisance offenses</p>
                                <div class="category-stats mb-3">
                                    <small class="text-muted">
                                        <i class="fas fa-chart-bar me-1"></i>
                                        Sections 141-171
                                    </small>
                                </div>
                                <button class="btn btn-warning" onclick="searchCategory('assembly OR riot OR nuisance')">
                                    <i class="fas fa-search me-1"></i>Explore
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-4 col-md-6">
                        <div class="category-card card h-100 border-0 shadow-sm hover-card">
                            <div class="card-body text-center p-4">
                                <div class="category-icon mb-3 text-info">
                                    <i class="fas fa-file-contract fa-3x"></i>
                                </div>
                                <h5 class="card-title">Forgery & Fraud</h5>
                                <p class="card-text text-muted">Document forgery, cheating, and fraudulent activities</p>
                                <div class="category-stats mb-3">
                                    <small class="text-muted">
                                        <i class="fas fa-chart-bar me-1"></i>
                                        Sections 463-489
                                    </small>
                                </div>
                                <button class="btn btn-info" onclick="searchCategory('forgery OR fraud OR cheating')">
                                    <i class="fas fa-search me-1"></i>Explore
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-4 col-md-6">
                        <div class="category-card card h-100 border-0 shadow-sm hover-card">
                            <div class="card-body text-center p-4">
                                <div class="category-icon mb-3 text-danger">
                                    <i class="fas fa-gavel fa-3x"></i>
                                </div>
                                <h5 class="card-title">Contempt & Perjury</h5>
                                <p class="card-text text-muted">False evidence, perjury, and contempt of authority</p>
                                <div class="category-stats mb-3">
                                    <small class="text-muted">
                                        <i class="fas fa-chart-bar me-1"></i>
                                        Sections 172-229
                                    </small>
                                </div>
                                <button class="btn btn-danger" onclick="searchCategory('perjury OR contempt OR false')">
                                    <i class="fas fa-search me-1"></i>Explore
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-4 col-md-6">
                        <div class="category-card card h-100 border-0 shadow-sm hover-card">
                            <div class="card-body text-center p-4">
                                <div class="category-icon mb-3 text-secondary">
                                    <i class="fas fa-shield-alt fa-3x"></i>
                                </div>
                                <h5 class="card-title">General Provisions</h5>
                                <p class="card-text text-muted">General principles, definitions, and explanations</p>
                                <div class="category-stats mb-3">
                                    <small class="text-muted">
                                        <i class="fas fa-chart-bar me-1"></i>
                                        Sections 1-52
                                    </small>
                                </div>
                                <button class="btn btn-secondary" onclick="searchCategory('general OR definition OR principle')">
                                    <i class="fas fa-search me-1"></i>Explore
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            
            <!-- Popular Sections -->
            <section class="mb-5 animate__animated animate__fadeIn">
                <div class="row mb-4">
                    <div class="col-12">
                        <h2 class="h4 mb-0">
                            <i class="fas fa-star text-warning me-2"></i>
                            Popular Sections
                        </h2>
                        <p class="text-muted">Most frequently referenced sections</p>
                    </div>
                </div>
                
                <div class="row g-3">
                    <?php
                    $popular_sections = [
                        ['number' => '302', 'name' => 'Murder', 'color' => 'primary'],
                        ['number' => '379', 'name' => 'Theft', 'color' => 'success'],
                        ['number' => '322', 'name' => 'Voluntarily causing hurt', 'color' => 'info'],
                        ['number' => '363', 'name' => 'Kidnapping', 'color' => 'warning'],
                        ['number' => '415', 'name' => 'Cheating', 'color' => 'danger'],
                        ['number' => '463', 'name' => 'Forgery', 'color' => 'secondary']
                    ];
                    
                    foreach ($popular_sections as $popular): ?>
                    <div class="col-md-4">
                        <div class="popular-section-card d-flex align-items-center p-3 border rounded hover-item">
                            <div class="section-number me-3">
                                <span class="badge bg-<?php echo $popular['color']; ?> fs-6">
                                    <?php echo $popular['number']; ?>
                                </span>
                            </div>
                            <div class="section-info flex-grow-1">
                                <h6 class="mb-1"><?php echo $popular['name']; ?></h6>
                                <small class="text-muted">Section <?php echo $popular['number']; ?></small>
                            </div>
                            <button class="btn btn-sm btn-outline-<?php echo $popular['color']; ?>" onclick="searchSection('<?php echo $popular['number']; ?>')">
                                <i class="fas fa-arrow-right"></i>
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>
    </div>
</section>

<!-- Note Modal -->
<div class="modal fade" id="noteModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-sticky-note me-2"></i>Add Personal Note
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="noteForm">
                    <input type="hidden" id="noteSectionId">
                    <div class="mb-3">
                        <label for="noteText" class="form-label fw-semibold">Your Personal Note</label>
                        <textarea class="form-control" id="noteText" rows="6" 
                                  placeholder="Add your personal notes, observations, or reminders about this section..."></textarea>
                        <div class="form-text">
                            <i class="fas fa-info-circle me-1"></i>
                            Your notes are private and only visible to you.
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Cancel
                </button>
                <button type="button" class="btn btn-primary" onclick="saveNote()">
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
                <div class="quick-search-grid">
                    <h6 class="mb-3">Popular Searches:</h6>
                    <div class="row g-2">
                        <?php
                        $quick_searches = [
                            'Murder (302)', 'Theft (379)', 'Assault (322)', 'Kidnapping (359)',
                            'Rape (363)', 'Cheating (415)', 'Criminal breach of trust (405)',
                            'Forgery (463)', 'Defamation (479)', 'Trespass (441)'
                        ];
                        
                        foreach ($quick_searches as $search_term): ?>
                        <div class="col-md-6 col-lg-4">
                            <button class="btn btn-outline-primary btn-sm w-100 mb-2" onclick="quickSearchSelect('<?php echo explode(' (', $search_term)[0]; ?>')">
                                <?php echo $search_term; ?>
                            </button>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Hero Section */
.hero-section {
    position: relative;
    overflow: hidden;
    background: linear-gradient(135deg, var(--bs-primary) 0%, #0066cc 100%);
}

.hero-section::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -20%;
    width: 100%;
    height: 200%;
    background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 50%);
    animation: float 8s ease-in-out infinite;
}

.hero-image-container {
    text-align: center;
    animation: float 6s ease-in-out infinite;
}

/* Stats Bar */
.stats-bar {
    background: linear-gradient(135deg, #212529 0%, #343a40 100%);
}

.stat-item {
    transition: transform 0.3s ease;
}

.stat-item:hover {
    transform: translateY(-5px);
}

/* Search Section */
.search-suggestions {
    top: 100%;
    left: 0;
    right: 0;
    max-height: 300px;
    overflow-y: auto;
    z-index: 1050;
}

.search-suggestions .suggestion-item {
    padding: 12px 16px;
    border-bottom: 1px solid #eee;
    cursor: pointer;
    transition: background-color 0.2s ease;
}

.search-suggestions .suggestion-item:hover {
    background-color: #f8f9fa;
}

.search-suggestions .suggestion-item:last-child {
    border-bottom: none;
}

/* Search Input */
.search-input:focus {
    border-color: var(--bs-primary);
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

/* Section Cards */
.section-card {
    transition: all 0.3s ease;
    border-left: 4px solid var(--bs-primary) !important;
}

.section-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15) !important;
}

.section-card .content-body {
    line-height: 1.8;
    text-align: justify;
}

.section-card .explanation-content,
.section-card .illustration-content {
    border-left: 3px solid var(--bs-success);
    padding-left: 1rem;
}

/* Category Cards */
.category-card {
    transition: all 0.3s ease;
    cursor: pointer;
}

.hover-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 15px 35px rgba(0,0,0,0.15) !important;
}

.category-icon {
    transition: all 0.3s ease;
}

.hover-card:hover .category-icon {
    transform: scale(1.1) rotate(5deg);
}

/* Popular Section Cards */
.popular-section-card {
    transition: all 0.3s ease;
    cursor: pointer;
}

.hover-item:hover {
    background-color: var(--bs-light);
    border-color: var(--bs-primary) !important;
    transform: translateX(5px);
}

/* View Mode Styles */
.compact-view .section-card .card-body > div:not(.section-content) {
    display: none;
}

.compact-view .section-card .content-body {
    max-height: 100px;
    overflow: hidden;
    position: relative;
}

.compact-view .section-card .content-body::after {
    content: '...';
    position: absolute;
    bottom: 0;
    right: 0;
    background: white;
    padding-left: 10px;
}

/* Dark Mode Adjustments */
[data-bs-theme="dark"] .hero-section {
    background: linear-gradient(135deg, #0d47a1 0%, #1565c0 100%);
}

[data-bs-theme="dark"] .stats-bar {
    background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
}

[data-bs-theme="dark"] .section-card {
    background: rgba(255,255,255,0.05);
    border-color: rgba(255,255,255,0.1) !important;
}

[data-bs-theme="dark"] .search-suggestions {
    background: var(--bs-dark);
    border-color: rgba(255,255,255,0.2);
}

[data-bs-theme="dark"] .search-suggestions .suggestion-item:hover {
    background-color: rgba(255,255,255,0.1);
}

[data-bs-theme="dark"] .popular-section-card:hover {
    background-color: rgba(255,255,255,0.1);
}

/* Animations */
@keyframes float {
    0%, 100% { 
        transform: translateY(0px) rotate(0deg); 
    }
    33% { 
        transform: translateY(-10px) rotate(1deg); 
    }
    66% { 
        transform: translateY(-5px) rotate(-1deg); 
    }
}

/* Search highlighting */
.highlight {
    background-color: yellow;
    padding: 0.1rem 0.2rem;
    border-radius: 0.2rem;
}

/* Responsive Design */
@media (max-width: 768px) {
    .hero-section .display-4 {
        font-size: 2rem;
    }
    
    .hero-section .lead {
        font-size: 1rem;
    }
    
    .section-card .card-header {
        flex-direction: column;
        align-items: flex-start !important;
        gap: 1rem;
    }
    
    .category-card {
        margin-bottom: 1rem;
    }
    
    .popular-section-card {
        flex-direction: column;
        text-align: center;
        gap: 0.5rem;
    }
    
    .stats-bar .row {
        text-align: center;
    }
    
    .d-flex.gap-3 {
        flex-direction: column;
        gap: 0.5rem !important;
    }
}

@media (max-width: 576px) {
    .search-input {
        font-size: 16px; /* Prevents zoom on iOS */
    }
    
    .input-group-lg .form-control {
        font-size: 16px;
    }
}

/* Print Styles */
@media print {
    .hero-section,
    .stats-bar,
    #search-section,
    .modal,
    .btn,
    .dropdown,
    .pagination {
        display: none !important;
    }
    
    .section-card {
        break-inside: avoid;
        border: 1px solid #000 !important;
        margin-bottom: 1rem;
    }
    
    .section-card .card-header {
        background: #f8f9fa !important;
        border-bottom: 1px solid #000;
    }
}

/* Loading States */
.loading {
    opacity: 0.6;
    pointer-events: none;
}

.skeleton {
    animation: skeleton-loading 1s linear infinite alternate;
}

@keyframes skeleton-loading {
    0% {
        background-color: hsl(200, 20%, 80%);
    }
    100% {
        background-color: hsl(200, 20%, 95%);
    }
}

/* Accessibility */
.btn:focus,
.form-control:focus {
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

/* High contrast mode support */
@media (prefers-contrast: high) {
    .section-card {
        border: 2px solid black !important;
    }
    
    .btn {
        border: 2px solid black;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Search suggestions functionality
    const searchInput = document.getElementById('search');
    const suggestionsContainer = document.getElementById('searchSuggestions');
    let searchTimeout;

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const query = this.value.trim();
            
            if (query.length >= 2) {
                searchTimeout = setTimeout(() => {
                    fetchSuggestions(query);
                }, 300);
            } else {
                hideSuggestions();
            }
        });

        searchInput.addEventListener('blur', function() {
            setTimeout(hideSuggestions, 200);
        });

        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                hideSuggestions();
            }
        });
    }

    function fetchSuggestions(query) {
        fetch(`?ajax_search=1&q=${encodeURIComponent(query)}`)
            .then(response => response.json())
            .then(data => {
                showSuggestions(data);
            })
            .catch(error => {
                console.error('Error fetching suggestions:', error);
                hideSuggestions();
            });
    }

    function showSuggestions(suggestions) {
        if (suggestions.length === 0) {
            hideSuggestions();
            return;
        }

        let html = '';
        suggestions.forEach(suggestion => {
            html += `
                <div class="suggestion-item" onclick="selectSuggestion('${suggestion.text}', ${suggestion.id})">
                    <div class="fw-semibold">${suggestion.text}</div>
                </div>
            `;
        });

        suggestionsContainer.innerHTML = html;
        suggestionsContainer.classList.remove('d-none');
    }

    function hideSuggestions() {
        suggestionsContainer.classList.add('d-none');
    }

    // Smooth scrolling for anchor links
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

    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Animate cards on scroll
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

    // Observe cards for animation
    document.querySelectorAll('.category-card, .popular-section-card, .section-card').forEach(card => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(30px)';
        card.style.transition = 'all 0.6s ease';
        observer.observe(card);
    });
});

// Global functions
function selectSuggestion(text, id) {
    document.getElementById('search').value = text;
    document.getElementById('searchSuggestions').classList.add('d-none');
    document.getElementById('searchForm').submit();
}

function searchCategory(query) {
    document.getElementById('search').value = query;
    document.getElementById('searchForm').submit();
}

function searchSection(sectionNumber) {
    document.getElementById('search').value = sectionNumber;
    document.getElementById('searchForm').submit();
}

function quickSearchSelect(query) {
    document.getElementById('search').value = query;
    bootstrap.Modal.getInstance(document.getElementById('quickSearchModal')).hide();
    document.getElementById('searchForm').submit();
}

function toggleViewMode() {
    const container = document.getElementById('resultsContainer');
    const button = document.getElementById('viewModeText');
    
    if (container) {
        container.classList.toggle('compact-view');
        if (container.classList.contains('compact-view')) {
            button.textContent = 'Full View';
        } else {
            button.textContent = 'Compact View';
        }
    }
}

function toggleBookmark(sectionId) {
    <?php if (!isLoggedIn()): ?>
    showLoginAlert();
    return;
    <?php else: ?>
    
    const formData = new FormData();
    formData.append('toggle_bookmark', '1');
    formData.append('section_id', sectionId);
    
    fetch('penal_code.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'added') {
            showAlert('Section bookmarked successfully!', 'success');
        } else {
            showAlert('Bookmark removed.', 'info');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('An error occurred while toggling bookmark.', 'danger');
    });
    <?php endif; ?>
}

function addNote(sectionId) {
    <?php if (!isLoggedIn()): ?>
    showLoginAlert();
    return;
    <?php endif; ?>
    
    document.getElementById('noteSectionId').value = sectionId;
    document.getElementById('noteText').value = '';
    const modal = new bootstrap.Modal(document.getElementById('noteModal'));
    modal.show();
}

function saveNote() {
    const sectionId = document.getElementById('noteSectionId').value;
    const note = document.getElementById('noteText').value;
    
    if (!note.trim()) {
        showAlert('Please enter a note.', 'warning');
        return;
    }
    
    const formData = new FormData();
    formData.append('save_note', '1');
    formData.append('section_id', sectionId);
    formData.append('note', note);
    
    fetch('penal_code.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'saved') {
            showAlert('Note saved successfully!', 'success');
            bootstrap.Modal.getInstance(document.getElementById('noteModal')).hide();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('An error occurred while saving the note.', 'danger');
    });
}

function shareSection(sectionId) {
    const url = window.location.origin + window.location.pathname + '?section_id=' + sectionId;
    
    if (navigator.share) {
        navigator.share({
            title: 'Penal Code Section',
            url: url
        });
    } else if (navigator.clipboard) {
        navigator.clipboard.writeText(url).then(() => {
            showAlert('Section link copied to clipboard!', 'success');
        });
    } else {
        // Fallback
        const textArea = document.createElement('textarea');
        textArea.value = url;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        showAlert('Section link copied to clipboard!', 'success');
    }
}

function exportResults() {
    showAlert('Export functionality will be implemented soon.', 'info');
}

function showLoginAlert() {
    showAlert('Please login to use this feature.', 'warning');
}

function showAlert(message, type = 'info') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    alertDiv.style.cssText = 'top: 100px; right: 20px; z-index: 9999; min-width: 300px;';
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(alertDiv);
    
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.parentNode.removeChild(alertDiv);
        }
    }, 5000);
}

// Highlight search terms in results
<?php if (!empty($search_query)): ?>
document.addEventListener('DOMContentLoaded', function() {
    highlightSearchTerms('<?php echo addslashes($search_query); ?>');
});

function highlightSearchTerms(query) {
    const terms = query.split(' ').filter(term => term.length > 2);
    terms.forEach(term => {
        if (term.toLowerCase() !== 'or' && term.toLowerCase() !== 'and') {
            highlightTerm(term);
        }
    });
}

function highlightTerm(term) {
    const regex = new RegExp(`(${term})`, 'gi');
    const elements = document.querySelectorAll('.content-body');
    
    elements.forEach(element => {
        if (element.innerHTML.indexOf('<mark>') === -1) { // Avoid double highlighting
            element.innerHTML = element.innerHTML.replace(regex, '<mark class="highlight">$1</mark>');
        }
    });
}
<?php endif; ?>

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    if (e.ctrlKey || e.metaKey) {
        switch(e.key) {
            case 'k':
                e.preventDefault();
                document.getElementById('search').focus();
                break;
            case 'Enter':
                if (document.activeElement === document.getElementById('search')) {
                    e.preventDefault();
                    document.getElementById('searchForm').submit();
                }
                break;
        }
    }
    
    if (e.key === 'Escape') {
        // Close modals
        const modals = document.querySelectorAll('.modal.show');
        modals.forEach(modal => {
            bootstrap.Modal.getInstance(modal).hide();
        });
    }
});

// Service Worker for offline functionality (optional)
if ('serviceWorker' in navigator) {
    window.addEventListener('load', function() {
        navigator.serviceWorker.register('/sw.js').then(function(registration) {
            console.log('SW registered: ', registration);
        }).catch(function(registrationError) {
            console.log('SW registration failed: ', registrationError);
        });
    });
}
</script>

<?php include 'includes/footer.php'; ?>