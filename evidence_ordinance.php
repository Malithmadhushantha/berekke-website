<?php
require_once 'config/config.php';

$page_title = "Evidence Ordinance of Sri Lanka";
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
    $search_sql = "SELECT * FROM evidence_ordinance WHERE 
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
    $count_sql = "SELECT COUNT(*) FROM evidence_ordinance WHERE 
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
    $stmt = $pdo->prepare("SELECT id FROM user_bookmarks WHERE user_id = ? AND table_name = 'evidence_ordinance' AND section_id = ?");
    $stmt->execute([$user_id, $section_id]);
    $bookmark = $stmt->fetch();
    
    if ($bookmark) {
        // Remove bookmark
        $stmt = $pdo->prepare("DELETE FROM user_bookmarks WHERE id = ?");
        $stmt->execute([$bookmark['id']]);
        $bookmark_status = 'removed';
    } else {
        // Add bookmark
        $stmt = $pdo->prepare("INSERT INTO user_bookmarks (user_id, table_name, section_id) VALUES (?, 'evidence_ordinance', ?)");
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
    $stmt = $pdo->prepare("SELECT id FROM user_bookmarks WHERE user_id = ? AND table_name = 'evidence_ordinance' AND section_id = ?");
    $stmt->execute([$user_id, $section_id]);
    $bookmark = $stmt->fetch();
    
    if ($bookmark) {
        $stmt = $pdo->prepare("UPDATE user_bookmarks SET notes = ? WHERE id = ?");
        $stmt->execute([$note, $bookmark['id']]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO user_bookmarks (user_id, table_name, section_id, notes) VALUES (?, 'evidence_ordinance', ?, ?)");
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
            <div class="bg-warning text-dark rounded-4 p-4">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <h1 class="h2 mb-2">
                            <i class="fas fa-search me-3"></i>
                            Sri Lanka Evidence Ordinance
                        </h1>
                        <p class="mb-0 opacity-75">
                            සාක්ෂි ආඥාපනත - Complete searchable database of Evidence Ordinance sections
                        </p>
                    </div>
                    <div class="col-lg-4 text-end">
                        <i class="fas fa-microscope fa-4x opacity-50"></i>
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
                                    <i class="fas fa-search me-2"></i>Search Evidence Ordinance
                                </label>
                                <input type="text" class="form-control form-control-lg" id="search" name="search" 
                                       value="<?php echo htmlspecialchars($search_query); ?>"
                                       placeholder="Enter section number, evidence type, or topic...">
                            </div>
                            <div class="col-lg-2 col-md-3 mb-3">
                                <button type="submit" class="btn btn-warning btn-lg w-100">
                                    <i class="fas fa-search me-2"></i>Search
                                </button>
                            </div>
                            <div class="col-lg-2 col-md-2 mb-3">
                                <a href="evidence_ordinance.php" class="btn btn-outline-secondary btn-lg w-100">
                                    <i class="fas fa-times me-2"></i>Clear
                                </a>
                            </div>
                        </div>
                    </form>
                    
                    <?php if (isLoggedIn()): ?>
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="d-flex gap-2">
                                <a href="my_bookmarks.php?type=evidence_ordinance" class="btn btn-outline-warning btn-sm">
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
                        <span class="badge bg-warning text-dark"><?php echo $total_results; ?> results</span>
                    </h4>
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
                                <span class="badge bg-warning text-dark me-2">Section <?php echo htmlspecialchars($section['section_number']); ?></span>
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
                            <h6 class="text-warning mb-3"><?php echo htmlspecialchars($section['section_topic']); ?></h6>
                            <?php endif; ?>
                            
                            <div class="section-text">
                                <?php echo nl2br(htmlspecialchars($section['section_text'])); ?>
                            </div>
                            
                            <?php if (!empty($section['explanation_1'])): ?>
                            <div class="mt-3">
                                <h6 class="text-info">Explanation:</h6>
                                <div class="explanation-text">
                                    <?php echo nl2br(htmlspecialchars($section['explanation_1'])); ?>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($section['illustrations_1'])): ?>
                            <div class="mt-3">
                                <h6 class="text-primary">Illustrations:</h6>
                                <div class="illustration-text">
                                    <?php echo nl2br(htmlspecialchars($section['illustrations_1'])); ?>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
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
            <div class="col-lg-4 col-md-6">
                <div class="card h-100 border-0 shadow-sm hover-card">
                    <div class="card-body text-center p-4">
                        <i class="fas fa-eye fa-3x text-warning mb-3"></i>
                        <h5 class="card-title">Direct Evidence</h5>
                        <p class="card-text text-muted">Eye witness testimony and direct observations</p>
                        <button class="btn btn-warning" onclick="searchCategory('direct OR witness OR testimony')">
                            <i class="fas fa-search me-1"></i>Explore
                        </button>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="card h-100 border-0 shadow-sm hover-card">
                    <div class="card-body text-center p-4">
                        <i class="fas fa-puzzle-piece fa-3x text-primary mb-3"></i>
                        <h5 class="card-title">Circumstantial Evidence</h5>
                        <p class="card-text text-muted">Indirect evidence and circumstantial proof</p>
                        <button class="btn btn-primary" onclick="searchCategory('circumstantial OR indirect')">
                            <i class="fas fa-search me-1"></i>Explore
                        </button>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="card h-100 border-0 shadow-sm hover-card">
                    <div class="card-body text-center p-4">
                        <i class="fas fa-file-alt fa-3x text-success mb-3"></i>
                        <h5 class="card-title">Documentary Evidence</h5>
                        <p class="card-text text-muted">Documents, records, and written evidence</p>
                        <button class="btn btn-success" onclick="searchCategory('document OR record OR written')">
                            <i class="fas fa-search me-1"></i>Explore
                        </button>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="card h-100 border-0 shadow-sm hover-card">
                    <div class="card-body text-center p-4">
                        <i class="fas fa-flask fa-3x text-info mb-3"></i>
                        <h5 class="card-title">Scientific Evidence</h5>
                        <p class="card-text text-muted">Forensic analysis and scientific proof</p>
                        <button class="btn btn-info" onclick="searchCategory('scientific OR forensic OR analysis')">
                            <i class="fas fa-search me-1"></i>Explore
                        </button>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="card h-100 border-0 shadow-sm hover-card">
                    <div class="card-body text-center p-4">
                        <i class="fas fa-ban fa-3x text-danger mb-3"></i>
                        <h5 class="card-title">Inadmissible Evidence</h5>
                        <p class="card-text text-muted">Hearsay, privileged, and excluded evidence</p>
                        <button class="btn btn-danger" onclick="searchCategory('inadmissible OR hearsay OR privileged')">
                            <i class="fas fa-search me-1"></i>Explore
                        </button>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="card h-100 border-0 shadow-sm hover-card">
                    <div class="card-body text-center p-4">
                        <i class="fas fa-question-circle fa-3x text-secondary mb-3"></i>
                        <h5 class="card-title">Burden of Proof</h5>
                        <p class="card-text text-muted">Standards of proof and burden requirements</p>
                        <button class="btn btn-secondary" onclick="searchCategory('burden OR proof OR standard')">
                            <i class="fas fa-search me-1"></i>Explore
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Popular Sections -->
        <div class="row mt-5">
            <div class="col-12">
                <h4 class="mb-4">Important Evidence Provisions</h4>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="d-flex align-items-center p-3 border rounded">
                            <div class="me-3">
                                <span class="badge bg-warning text-dark fs-6">32</span>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1">Admissions</h6>
                                <small class="text-muted">Statements by parties</small>
                            </div>
                            <button class="btn btn-sm btn-outline-warning" onclick="searchSection('32')">
                                View
                            </button>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="d-flex align-items-center p-3 border rounded">
                            <div class="me-3">
                                <span class="badge bg-primary fs-6">60</span>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1">Documentary Evidence</h6>
                                <small class="text-muted">Proof of documents</small>
                            </div>
                            <button class="btn btn-sm btn-outline-primary" onclick="searchSection('60')">
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
                <button type="button" class="btn btn-warning" onclick="saveNote()">Save Note</button>
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

.explanation-text, .illustration-text {
    background-color: rgba(0,0,0,0.05);
    padding: 1rem;
    border-radius: 0.5rem;
    line-height: 1.6;
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
    
    fetch('evidence_ordinance.php', {
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
    
    const formData = new FormData();
    formData.append('save_note', '1');
    formData.append('section_id', sectionId);
    formData.append('note', note);
    
    fetch('evidence_ordinance.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'saved') {
            alert('Note saved successfully!');
            bootstrap.Modal.getInstance(document.getElementById('noteModal')).hide();
        }
    });
}

function showQuickSearch() {
    alert('Quick search for Evidence Ordinance sections will be displayed.');
}
</script>

<?php include 'includes/footer.php'; ?>