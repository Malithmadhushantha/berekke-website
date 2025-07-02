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
            <div class="bg-success text-white rounded-4 p-4">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <h1 class="h2 mb-2">
                            <i class="fas fa-clipboard-list me-3"></i>
                            Sri Lanka Criminal Procedure Code
                        </h1>
                        <p class="mb-0 opacity-75">
                            අපරාධ නඩු විධාන සංග්‍රහය - Complete searchable database of Criminal Procedure Code sections
                        </p>
                    </div>
                    <div class="col-lg-4 text-end">
                        <i class="fas fa-procedures fa-4x opacity-50"></i>
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
                                    <i class="fas fa-search me-2"></i>Search Criminal Procedure Code
                                </label>
                                <input type="text" class="form-control form-control-lg" id="search" name="search" 
                                       value="<?php echo htmlspecialchars($search_query); ?>"
                                       placeholder="Enter section number, procedure, or topic...">
                            </div>
                            <div class="col-lg-2 col-md-3 mb-3">
                                <button type="submit" class="btn btn-success btn-lg w-100">
                                    <i class="fas fa-search me-2"></i>Search
                                </button>
                            </div>
                            <div class="col-lg-2 col-md-2 mb-3">
                                <a href="criminal_procedure_code_act.php" class="btn btn-outline-secondary btn-lg w-100">
                                    <i class="fas fa-times me-2"></i>Clear
                                </a>
                            </div>
                        </div>
                    </form>
                    
                    <?php if (isLoggedIn()): ?>
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="d-flex gap-2">
                                <a href="my_bookmarks.php?type=criminal_procedure_code" class="btn btn-outline-warning btn-sm">
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
        <!-- Search Results (Similar structure to penal_code.php) -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4>
                        Search Results for "<?php echo htmlspecialchars($search_query); ?>"
                        <span class="badge bg-success"><?php echo $total_results; ?> results</span>
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
                                <span class="badge bg-success me-2">Section <?php echo htmlspecialchars($section['section_number']); ?></span>
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
                            <h6 class="text-success mb-3"><?php echo htmlspecialchars($section['section_topic']); ?></h6>
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
                        <i class="fas fa-user-check fa-3x text-success mb-3"></i>
                        <h5 class="card-title">Arrest Procedures</h5>
                        <p class="card-text text-muted">Arrest warrants, procedures, and suspect rights</p>
                        <button class="btn btn-success" onclick="searchCategory('arrest OR warrant')">
                            <i class="fas fa-search me-1"></i>Explore
                        </button>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="card h-100 border-0 shadow-sm hover-card">
                    <div class="card-body text-center p-4">
                        <i class="fas fa-search fa-3x text-primary mb-3"></i>
                        <h5 class="card-title">Investigation</h5>
                        <p class="card-text text-muted">Search, seizure, and investigation procedures</p>
                        <button class="btn btn-primary" onclick="searchCategory('investigation OR search OR seizure')">
                            <i class="fas fa-search me-1"></i>Explore
                        </button>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="card h-100 border-0 shadow-sm hover-card">
                    <div class="card-body text-center p-4">
                        <i class="fas fa-university fa-3x text-warning mb-3"></i>
                        <h5 class="card-title">Court Procedures</h5>
                        <p class="card-text text-muted">Court proceedings, bail, and trial procedures</p>
                        <button class="btn btn-warning" onclick="searchCategory('court OR trial OR bail')">
                            <i class="fas fa-search me-1"></i>Explore
                        </button>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Note Modal (Same as penal_code.php) -->
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
                <button type="button" class="btn btn-success" onclick="saveNote()">Save Note</button>
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

.explanation-text {
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

function toggleBookmark(sectionId) {
    <?php if (!isLoggedIn()): ?>
    alert('Please login to bookmark sections.');
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
    
    fetch('criminal_procedure_code_act.php', {
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
    alert('Quick search for Criminal Procedure Code sections will be displayed.');
}
</script>

<?php include 'includes/footer.php'; ?>