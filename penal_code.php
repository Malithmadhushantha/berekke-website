<?php
require_once 'config/config.php';

$page_title = "Penal Code of Sri Lanka";
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
    $search_sql = "SELECT * FROM penal_code WHERE 
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
    $count_sql = "SELECT COUNT(*) FROM penal_code WHERE 
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

<div class="container py-4">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="bg-primary text-white rounded-4 p-4">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <h1 class="h2 mb-2">
                            <i class="fas fa-gavel me-3"></i>
                            Sri Lanka Penal Code
                        </h1>
                        <p class="mb-0 opacity-75">
                            දණ්ඩ නීති සංග්‍රහය - Complete searchable database of Sri Lankan Penal Code sections
                        </p>
                    </div>
                    <div class="col-lg-4 text-end">
                        <i class="fas fa-balance-scale fa-4x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Search Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form method="GET" action="">
                        <div class="row align-items-end">
                            <div class="col-lg-8 col-md-7 mb-3">
                                <label for="search" class="form-label fw-semibold">
                                    <i class="fas fa-search me-2"></i>Search Penal Code
                                </label>
                                <input type="text" class="form-control form-control-lg" id="search" name="search" 
                                       value="<?php echo htmlspecialchars($search_query); ?>"
                                       placeholder="Enter section number, keywords, or topic...">
                            </div>
                            <div class="col-lg-2 col-md-3 mb-3">
                                <button type="submit" class="btn btn-primary btn-lg w-100">
                                    <i class="fas fa-search me-2"></i>Search
                                </button>
                            </div>
                            <div class="col-lg-2 col-md-2 mb-3">
                                <a href="penal_code.php" class="btn btn-outline-secondary btn-lg w-100">
                                    <i class="fas fa-times me-2"></i>Clear
                                </a>
                            </div>
                        </div>
                    </form>
                    
                    <?php if (isLoggedIn()): ?>
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="d-flex gap-2">
                                <a href="my_bookmarks.php?type=penal_code" class="btn btn-outline-warning btn-sm">
                                    <i class="fas fa-bookmark me-1"></i>My Bookmarks
                                </a>
                                <button class="btn btn-outline-info btn-sm" onclick="showQuickSearch()">
                                    <i class="fas fa-bolt me-1"></i>Quick Search
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Search Results or Browse -->
    <?php if (!empty($search_query)): ?>
        <!-- Search Results -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4>
                        Search Results for "<?php echo htmlspecialchars($search_query); ?>"
                        <span class="badge bg-primary"><?php echo $total_results; ?> results</span>
                    </h4>
                    <div>
                        <?php if (isLoggedIn()): ?>
                        <button class="btn btn-outline-success btn-sm" onclick="exportResults()">
                            <i class="fas fa-download me-1"></i>Export Results
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <?php if (!empty($search_results)): ?>
            <div class="row">
                <?php foreach ($search_results as $section): ?>
                <div class="col-12 mb-4">
                    <div class="card border-0 shadow-sm section-card">
                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <span class="badge bg-primary me-2">Section <?php echo htmlspecialchars($section['section_number']); ?></span>
                                <?php echo htmlspecialchars($section['section_name']); ?>
                            </h5>
                            <?php if (isLoggedIn()): ?>
                            <div class="dropdown">
                                <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" 
                                        data-bs-toggle="dropdown">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a class="dropdown-item" href="#" onclick="toggleBookmark(<?php echo $section['id']; ?>)">
                                            <i class="fas fa-bookmark me-2"></i>Bookmark
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="#" onclick="addNote(<?php echo $section['id']; ?>)">
                                            <i class="fas fa-sticky-note me-2"></i>Add Note
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="#" onclick="shareSection(<?php echo $section['id']; ?>)">
                                            <i class="fas fa-share me-2"></i>Share
                                        </a>
                                    </li>
                                </ul>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($section['part_name'])): ?>
                            <div class="mb-2">
                                <small class="text-muted">
                                    <strong>Part:</strong> <?php echo htmlspecialchars($section['part_name']); ?>
                                    <?php if (!empty($section['chapter_name'])): ?>
                                    | <strong>Chapter:</strong> <?php echo htmlspecialchars($section['chapter_name']); ?>
                                    <?php endif; ?>
                                </small>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($section['section_topic'])): ?>
                            <h6 class="text-primary mb-3"><?php echo htmlspecialchars($section['section_topic']); ?></h6>
                            <?php endif; ?>
                            
                            <div class="section-text">
                                <?php echo nl2br(htmlspecialchars($section['section_text'])); ?>
                            </div>
                            
                            <?php if (!empty($section['explanation_1'])): ?>
                            <div class="mt-3">
                                <h6 class="text-success">Explanation:</h6>
                                <div class="explanation-text">
                                    <?php echo nl2br(htmlspecialchars($section['explanation_1'])); ?>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($section['illustrations_1'])): ?>
                            <div class="mt-3">
                                <h6 class="text-info">Illustrations:</h6>
                                <div class="illustration-text">
                                    <?php echo nl2br(htmlspecialchars($section['illustrations_1'])); ?>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($section['amendments'])): ?>
                            <div class="mt-3">
                                <h6 class="text-warning">Amendments:</h6>
                                <div class="amendment-text">
                                    <?php echo nl2br(htmlspecialchars($section['amendments'])); ?>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="card-footer bg-light">
                            <small class="text-muted">
                                <i class="fas fa-hashtag me-1"></i>Section ID: <?php echo $section['id']; ?>
                                <?php if (!empty($section['sub_section_number'])): ?>
                                | Sub-section: <?php echo htmlspecialchars($section['sub_section_number']); ?>
                                <?php endif; ?>
                            </small>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($total_results > $items_per_page): ?>
            <div class="row">
                <div class="col-12">
                    <nav aria-label="Search results pagination">
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
                </div>
            </div>
            <?php endif; ?>

        <?php else: ?>
            <div class="row">
                <div class="col-12">
                    <div class="text-center py-5">
                        <i class="fas fa-search fa-4x text-muted mb-3"></i>
                        <h4>No results found</h4>
                        <p class="text-muted">Try searching with different keywords or check your spelling.</p>
                        <a href="penal_code.php" class="btn btn-primary">Browse All Sections</a>
                    </div>
                </div>
            </div>
        <?php endif; ?>

    <?php else: ?>
        <!-- Browse by Category -->
        <div class="row">
            <div class="col-12">
                <h4 class="mb-4">Browse by Category</h4>
            </div>
        </div>

        <div class="row g-4">
            <!-- Quick Access Cards -->
            <div class="col-lg-4 col-md-6">
                <div class="card h-100 border-0 shadow-sm hover-card">
                    <div class="card-body text-center p-4">
                        <i class="fas fa-user-shield fa-3x text-primary mb-3"></i>
                        <h5 class="card-title">Crimes Against Person</h5>
                        <p class="card-text text-muted">Murder, culpable homicide, assault, and related offenses</p>
                        <button class="btn btn-primary" onclick="searchCategory('murder OR assault OR homicide')">
                            <i class="fas fa-search me-1"></i>Explore
                        </button>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="card h-100 border-0 shadow-sm hover-card">
                    <div class="card-body text-center p-4">
                        <i class="fas fa-home fa-3x text-success mb-3"></i>
                        <h5 class="card-title">Property Crimes</h5>
                        <p class="card-text text-muted">Theft, burglary, criminal trespass, and property offenses</p>
                        <button class="btn btn-success" onclick="searchCategory('theft OR burglary OR trespass')">
                            <i class="fas fa-search me-1"></i>Explore
                        </button>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="card h-100 border-0 shadow-sm hover-card">
                    <div class="card-body text-center p-4">
                        <i class="fas fa-handshake fa-3x text-warning mb-3"></i>
                        <h5 class="card-title">Public Order</h5>
                        <p class="card-text text-muted">Unlawful assembly, rioting, and public nuisance offenses</p>
                        <button class="btn btn-warning" onclick="searchCategory('assembly OR riot OR nuisance')">
                            <i class="fas fa-search me-1"></i>Explore
                        </button>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="card h-100 border-0 shadow-sm hover-card">
                    <div class="card-body text-center p-4">
                        <i class="fas fa-file-contract fa-3x text-info mb-3"></i>
                        <h5 class="card-title">Forgery & Fraud</h5>
                        <p class="card-text text-muted">Document forgery, cheating, and fraudulent activities</p>
                        <button class="btn btn-info" onclick="searchCategory('forgery OR fraud OR cheating')">
                            <i class="fas fa-search me-1"></i>Explore
                        </button>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="card h-100 border-0 shadow-sm hover-card">
                    <div class="card-body text-center p-4">
                        <i class="fas fa-gavel fa-3x text-danger mb-3"></i>
                        <h5 class="card-title">Contempt & Perjury</h5>
                        <p class="card-text text-muted">False evidence, perjury, and contempt of authority</p>
                        <button class="btn btn-danger" onclick="searchCategory('perjury OR contempt OR false')">
                            <i class="fas fa-search me-1"></i>Explore
                        </button>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="card h-100 border-0 shadow-sm hover-card">
                    <div class="card-body text-center p-4">
                        <i class="fas fa-shield-alt fa-3x text-secondary mb-3"></i>
                        <h5 class="card-title">General Provisions</h5>
                        <p class="card-text text-muted">General principles, definitions, and explanations</p>
                        <button class="btn btn-secondary" onclick="searchCategory('general OR definition OR principle')">
                            <i class="fas fa-search me-1"></i>Explore
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Popular Sections -->
        <div class="row mt-5">
            <div class="col-12">
                <h4 class="mb-4">Popular Sections</h4>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="d-flex align-items-center p-3 border rounded">
                            <div class="me-3">
                                <span class="badge bg-primary fs-6">302</span>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1">Murder</h6>
                                <small class="text-muted">Punishment for murder</small>
                            </div>
                            <button class="btn btn-sm btn-outline-primary" onclick="searchSection('302')">
                                View
                            </button>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="d-flex align-items-center p-3 border rounded">
                            <div class="me-3">
                                <span class="badge bg-success fs-6">379</span>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1">Theft</h6>
                                <small class="text-muted">Punishment for theft</small>
                            </div>
                            <button class="btn btn-sm btn-outline-success" onclick="searchSection('379')">
                                View
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
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Note</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="noteForm">
                    <input type="hidden" id="noteSectionId">
                    <div class="mb-3">
                        <label for="noteText" class="form-label">Your Note</label>
                        <textarea class="form-control" id="noteText" rows="5" 
                                  placeholder="Add your personal notes about this section..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveNote()">Save Note</button>
            </div>
        </div>
    </div>
</div>

<style>
.hover-card {
    transition: all 0.3s ease;
}

.hover-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 0.5rem 1.5rem rgba(0,0,0,0.15) !important;
}

.section-card {
    transition: all 0.3s ease;
}

.section-card:hover {
    box-shadow: 0 0.5rem 1.5rem rgba(0,0,0,0.15) !important;
}

.section-text {
    line-height: 1.6;
    text-align: justify;
}

.explanation-text, .illustration-text, .amendment-text {
    background-color: rgba(0,0,0,0.05);
    padding: 1rem;
    border-radius: 0.5rem;
    line-height: 1.6;
}

mark {
    background-color: yellow;
    padding: 0.1rem 0.2rem;
}

@media (max-width: 768px) {
    .card-header h5 {
        font-size: 1rem;
    }
    
    .badge.fs-6 {
        font-size: 0.875rem !important;
    }
}
</style>

<script>
function searchCategory(query) {
    document.getElementById('search').value = query;
    document.querySelector('form').submit();
}

function searchSection(sectionNumber) {
    document.getElementById('search').value = sectionNumber;
    document.querySelector('form').submit();
}

function toggleBookmark(sectionId) {
    <?php if (!isLoggedIn()): ?>
    alert('Please login to bookmark sections.');
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
            alert('Section bookmarked successfully!');
        } else {
            alert('Bookmark removed.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while toggling bookmark.');
    });
    <?php endif; ?>
}

function addNote(sectionId) {
    <?php if (!isLoggedIn()): ?>
    alert('Please login to add notes.');
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
        alert('Please enter a note.');
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
            alert('Note saved successfully!');
            bootstrap.Modal.getInstance(document.getElementById('noteModal')).hide();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while saving the note.');
    });
}

function shareSection(sectionId) {
    const url = window.location.origin + '/penal_code.php?section=' + sectionId;
    navigator.clipboard.writeText(url).then(() => {
        alert('Section link copied to clipboard!');
    });
}

function exportResults() {
    alert('Exporting search results... This feature will be implemented.');
}

function showQuickSearch() {
    const quickSearchTerms = [
        'Murder (302)', 'Theft (379)', 'Assault (322)', 'Kidnapping (359)',
        'Rape (363)', 'Cheating (415)', 'Criminal breach of trust (405)',
        'Forgery (463)', 'Defamation (479)', 'Trespass (441)'
    ];
    
    let searchHtml = '<div class="quick-search-grid">';
    quickSearchTerms.forEach(term => {
        const [name, section] = term.split(' (');
        const sectionNum = section.replace(')', '');
        searchHtml += `<button class="btn btn-outline-primary btn-sm me-2 mb-2" onclick="searchSection('${sectionNum}')">${term}</button>`;
    });
    searchHtml += '</div>';
    
    // Show in a modal or alert
    alert('Quick search options will be displayed in a modal.');
}

// Highlight search terms in results
document.addEventListener('DOMContentLoaded', function() {
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
    const elements = document.querySelectorAll('.section-text, .explanation-text, .illustration-text');
    
    elements.forEach(element => {
        element.innerHTML = element.innerHTML.replace(regex, '<mark>$1</mark>');
    });
}
</script>

<?php include 'includes/footer.php'; ?>