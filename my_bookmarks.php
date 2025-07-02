<?php
require_once 'config/config.php';

// Require login
requireLogin();

$page_title = "My Bookmarks";
$filter_type = isset($_GET['type']) ? cleanInput($_GET['type']) : 'all';

// Handle bookmark deletion
if (isset($_POST['delete_bookmark'])) {
    $bookmark_id = intval($_POST['bookmark_id']);
    $stmt = $pdo->prepare("DELETE FROM user_bookmarks WHERE id = ? AND user_id = ?");
    $stmt->execute([$bookmark_id, $_SESSION['user_id']]);
    
    echo json_encode(['status' => 'success']);
    exit();
}

// Handle note update
if (isset($_POST['update_note'])) {
    $bookmark_id = intval($_POST['bookmark_id']);
    $note = cleanInput($_POST['note']);
    
    $stmt = $pdo->prepare("UPDATE user_bookmarks SET notes = ? WHERE id = ? AND user_id = ?");
    $stmt->execute([$note, $bookmark_id, $_SESSION['user_id']]);
    
    echo json_encode(['status' => 'success']);
    exit();
}

// Fetch user bookmarks with section details
$where_clause = "";
$params = [$_SESSION['user_id']];

if ($filter_type !== 'all') {
    $where_clause = "AND ub.table_name = ?";
    $params[] = $filter_type;
}

$bookmarks_sql = "SELECT ub.*, 
                  CASE 
                    WHEN ub.table_name = 'penal_code' THEN pc.section_number
                    WHEN ub.table_name = 'criminal_procedure_code' THEN cpc.section_number
                    WHEN ub.table_name = 'evidence_ordinance' THEN eo.section_number
                  END as section_number,
                  CASE 
                    WHEN ub.table_name = 'penal_code' THEN pc.section_name
                    WHEN ub.table_name = 'criminal_procedure_code' THEN cpc.section_name
                    WHEN ub.table_name = 'evidence_ordinance' THEN eo.section_name
                  END as section_name,
                  CASE 
                    WHEN ub.table_name = 'penal_code' THEN pc.section_topic
                    WHEN ub.table_name = 'criminal_procedure_code' THEN cpc.section_topic
                    WHEN ub.table_name = 'evidence_ordinance' THEN eo.section_topic
                  END as section_topic
                  FROM user_bookmarks ub
                  LEFT JOIN penal_code pc ON ub.table_name = 'penal_code' AND ub.section_id = pc.id
                  LEFT JOIN criminal_procedure_code cpc ON ub.table_name = 'criminal_procedure_code' AND ub.section_id = cpc.id
                  LEFT JOIN evidence_ordinance eo ON ub.table_name = 'evidence_ordinance' AND ub.section_id = eo.id
                  WHERE ub.user_id = ?" . ($filter_type !== 'all' ? " AND ub.table_name = ?" : "") . "
                  ORDER BY ub.created_at DESC";

$stmt = $pdo->prepare($bookmarks_sql);
$stmt->execute($params);
$bookmarks = $stmt->fetchAll();

// Get bookmark counts by type
$counts = [
    'all' => $pdo->prepare("SELECT COUNT(*) FROM user_bookmarks WHERE user_id = ?"),
    'penal_code' => $pdo->prepare("SELECT COUNT(*) FROM user_bookmarks WHERE user_id = ? AND table_name = 'penal_code'"),
    'criminal_procedure_code' => $pdo->prepare("SELECT COUNT(*) FROM user_bookmarks WHERE user_id = ? AND table_name = 'criminal_procedure_code'"),
    'evidence_ordinance' => $pdo->prepare("SELECT COUNT(*) FROM user_bookmarks WHERE user_id = ? AND table_name = 'evidence_ordinance'")
];

foreach ($counts as $key => $stmt) {
    if ($key === 'all') {
        $stmt->execute([$_SESSION['user_id']]);
    } else {
        $stmt->execute([$_SESSION['user_id']]);
    }
    $counts[$key] = $stmt->fetchColumn();
}

include 'includes/header.php';
?>

<div class="container py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2>
                        <i class="fas fa-bookmark me-2 text-warning"></i>
                        My Bookmarks
                    </h2>
                    <p class="text-muted">Your saved legal sections and notes</p>
                </div>
                <div>
                    <button class="btn btn-primary" onclick="exportBookmarks()">
                        <i class="fas fa-download me-1"></i>Export
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Tabs -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <nav class="nav nav-pills nav-justified">
                        <a class="nav-link <?php echo $filter_type === 'all' ? 'active' : ''; ?>" 
                           href="?type=all">
                            <i class="fas fa-bookmark me-2"></i>
                            All Bookmarks
                            <span class="badge bg-secondary ms-2"><?php echo $counts['all']; ?></span>
                        </a>
                        <a class="nav-link <?php echo $filter_type === 'penal_code' ? 'active' : ''; ?>" 
                           href="?type=penal_code">
                            <i class="fas fa-gavel me-2"></i>
                            Penal Code
                            <span class="badge bg-primary ms-2"><?php echo $counts['penal_code']; ?></span>
                        </a>
                        <a class="nav-link <?php echo $filter_type === 'criminal_procedure_code' ? 'active' : ''; ?>" 
                           href="?type=criminal_procedure_code">
                            <i class="fas fa-clipboard-list me-2"></i>
                            Criminal Procedure
                            <span class="badge bg-success ms-2"><?php echo $counts['criminal_procedure_code']; ?></span>
                        </a>
                        <a class="nav-link <?php echo $filter_type === 'evidence_ordinance' ? 'active' : ''; ?>" 
                           href="?type=evidence_ordinance">
                            <i class="fas fa-search me-2"></i>
                            Evidence Ordinance
                            <span class="badge bg-warning ms-2"><?php echo $counts['evidence_ordinance']; ?></span>
                        </a>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <!-- Bookmarks List -->
    <?php if (!empty($bookmarks)): ?>
        <div class="row">
            <?php foreach ($bookmarks as $bookmark): ?>
            <div class="col-lg-6 mb-4">
                <div class="card border-0 shadow-sm bookmark-card" data-bookmark-id="<?php echo $bookmark['id']; ?>">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <span class="badge 
                                <?php 
                                switch($bookmark['table_name']) {
                                    case 'penal_code': echo 'bg-primary'; break;
                                    case 'criminal_procedure_code': echo 'bg-success'; break;
                                    case 'evidence_ordinance': echo 'bg-warning'; break;
                                }
                                ?> me-2">
                                Section <?php echo htmlspecialchars($bookmark['section_number']); ?>
                            </span>
                            <span class="text-muted">
                                <?php 
                                switch($bookmark['table_name']) {
                                    case 'penal_code': echo 'Penal Code'; break;
                                    case 'criminal_procedure_code': echo 'Criminal Procedure'; break;
                                    case 'evidence_ordinance': echo 'Evidence Ordinance'; break;
                                }
                                ?>
                            </span>
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item" href="<?php echo $bookmark['table_name']; ?>.php?search=<?php echo urlencode($bookmark['section_number']); ?>">
                                        <i class="fas fa-external-link-alt me-2"></i>View Section
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="#" onclick="editNote(<?php echo $bookmark['id']; ?>)">
                                        <i class="fas fa-edit me-2"></i>Edit Note
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item text-danger" href="#" onclick="deleteBookmark(<?php echo $bookmark['id']; ?>)">
                                        <i class="fas fa-trash me-2"></i>Delete
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="card-body">
                        <h6 class="card-title"><?php echo htmlspecialchars($bookmark['section_name']); ?></h6>
                        <?php if (!empty($bookmark['section_topic'])): ?>
                        <p class="text-muted small mb-3"><?php echo htmlspecialchars($bookmark['section_topic']); ?></p>
                        <?php endif; ?>
                        
                        <div class="notes-section">
                            <h6 class="text-primary">
                                <i class="fas fa-sticky-note me-1"></i>
                                Personal Notes
                            </h6>
                            <div class="note-content p-3 bg-light rounded">
                                <?php if (!empty($bookmark['notes'])): ?>
                                    <p class="mb-0"><?php echo nl2br(htmlspecialchars($bookmark['notes'])); ?></p>
                                <?php else: ?>
                                    <p class="text-muted mb-0 fst-italic">No notes added yet.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-light">
                        <small class="text-muted">
                            <i class="fas fa-clock me-1"></i>
                            Bookmarked on <?php echo date('M j, Y \a\t g:i A', strtotime($bookmark['created_at'])); ?>
                        </small>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <!-- No Bookmarks -->
        <div class="row">
            <div class="col-12">
                <div class="text-center py-5">
                    <i class="fas fa-bookmark fa-4x text-muted mb-3"></i>
                    <h4>No Bookmarks Found</h4>
                    <p class="text-muted mb-4">
                        You haven't bookmarked any legal sections yet. 
                        Start exploring our legal databases to save important sections.
                    </p>
                    <div class="d-flex justify-content-center gap-3">
                        <a href="penal_code.php" class="btn btn-primary">
                            <i class="fas fa-gavel me-2"></i>Explore Penal Code
                        </a>
                        <a href="criminal_procedure_code_act.php" class="btn btn-success">
                            <i class="fas fa-clipboard-list me-2"></i>Criminal Procedure
                        </a>
                        <a href="evidence_ordinance.php" class="btn btn-warning">
                            <i class="fas fa-search me-2"></i>Evidence Ordinance
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Edit Note Modal -->
<div class="modal fade" id="editNoteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Note</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editNoteForm">
                    <input type="hidden" id="editBookmarkId">
                    <div class="mb-3">
                        <label for="editNoteText" class="form-label">Personal Note</label>
                        <textarea class="form-control" id="editNoteText" rows="5" 
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
.bookmark-card {
    transition: all 0.3s ease;
}

.bookmark-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 0.5rem 1.5rem rgba(0,0,0,0.15) !important;
}

.nav-pills .nav-link {
    color: #6c757d;
    background: none;
    border: 1px solid #e0e0e0;
    margin: 0 2px;
}

.nav-pills .nav-link.active {
    background-color: var(--bs-primary);
    border-color: var(--bs-primary);
}

.note-content {
    max-height: 150px;
    overflow-y: auto;
}

.note-content::-webkit-scrollbar {
    width: 4px;
}

.note-content::-webkit-scrollbar-track {
    background: #f1f1f1;
}

.note-content::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 2px;
}

@media (max-width: 768px) {
    .nav-pills {
        flex-direction: column;
    }
    
    .nav-pills .nav-link {
        margin: 2px 0;
    }
}
</style>

<script>
function deleteBookmark(bookmarkId) {
    if (confirm('Are you sure you want to delete this bookmark?')) {
        const formData = new FormData();
        formData.append('delete_bookmark', '1');
        formData.append('bookmark_id', bookmarkId);
        
        fetch('my_bookmarks.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                // Remove the bookmark card with animation
                const card = document.querySelector(`[data-bookmark-id="${bookmarkId}"]`);
                card.style.transition = 'all 0.3s ease';
                card.style.transform = 'scale(0)';
                card.style.opacity = '0';
                
                setTimeout(() => {
                    card.remove();
                    // Reload page if no bookmarks left
                    if (document.querySelectorAll('.bookmark-card').length === 0) {
                        location.reload();
                    }
                }, 300);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to delete bookmark. Please try again.');
        });
    }
}

function editNote(bookmarkId) {
    const card = document.querySelector(`[data-bookmark-id="${bookmarkId}"]`);
    const noteContent = card.querySelector('.note-content p').textContent;
    
    document.getElementById('editBookmarkId').value = bookmarkId;
    document.getElementById('editNoteText').value = noteContent === 'No notes added yet.' ? '' : noteContent;
    
    const modal = new bootstrap.Modal(document.getElementById('editNoteModal'));
    modal.show();
}

function saveNote() {
    const bookmarkId = document.getElementById('editBookmarkId').value;
    const note = document.getElementById('editNoteText').value;
    
    const formData = new FormData();
    formData.append('update_note', '1');
    formData.append('bookmark_id', bookmarkId);
    formData.append('note', note);
    
    fetch('my_bookmarks.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            // Update the note content in the card
            const card = document.querySelector(`[data-bookmark-id="${bookmarkId}"]`);
            const noteElement = card.querySelector('.note-content p');
            
            if (note.trim()) {
                noteElement.textContent = note;
                noteElement.classList.remove('text-muted', 'fst-italic');
            } else {
                noteElement.textContent = 'No notes added yet.';
                noteElement.classList.add('text-muted', 'fst-italic');
            }
            
            bootstrap.Modal.getInstance(document.getElementById('editNoteModal')).hide();
            
            // Show success message
            showToast('Note updated successfully!', 'success');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to update note. Please try again.');
    });
}

function exportBookmarks() {
    const bookmarks = [];
    document.querySelectorAll('.bookmark-card').forEach(card => {
        const bookmarkId = card.getAttribute('data-bookmark-id');
        const sectionNumber = card.querySelector('.badge').textContent.trim();
        const sectionName = card.querySelector('.card-title').textContent;
        const note = card.querySelector('.note-content p').textContent;
        
        bookmarks.push({
            id: bookmarkId,
            section: sectionNumber,
            title: sectionName,
            note: note === 'No notes added yet.' ? '' : note
        });
    });
    
    const dataStr = "data:text/json;charset=utf-8," + encodeURIComponent(JSON.stringify(bookmarks, null, 2));
    const downloadAnchorNode = document.createElement('a');
    downloadAnchorNode.setAttribute("href", dataStr);
    downloadAnchorNode.setAttribute("download", "my_bookmarks_" + new Date().toISOString().split('T')[0] + ".json");
    document.body.appendChild(downloadAnchorNode);
    downloadAnchorNode.click();
    downloadAnchorNode.remove();
    
    showToast('Bookmarks exported successfully!', 'success');
}

function showToast(message, type = 'info') {
    // Simple toast notification
    const toast = document.createElement('div');
    toast.className = `alert alert-${type} position-fixed top-0 end-0 m-3`;
    toast.style.zIndex = '9999';
    toast.innerHTML = `
        ${message}
        <button type="button" class="btn-close" onclick="this.parentElement.remove()"></button>
    `;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        if (toast.parentNode) {
            toast.remove();
        }
    }, 3000);
}

// Animate cards on load
document.addEventListener('DOMContentLoaded', function() {
    const cards = document.querySelectorAll('.bookmark-card');
    cards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        
        setTimeout(() => {
            card.style.transition = 'all 0.6s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 100);
    });
});
</script>

<?php include 'includes/footer.php'; ?>