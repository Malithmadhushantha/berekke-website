<?php
require_once '../config/config.php';

// Require admin access
requireAdmin();

$error_message = '';
$success_message = '';
$search_query = '';
$current_page = 1;
$items_per_page = 20;

// Handle search
if (isset($_GET['search'])) {
    $search_query = cleanInput($_GET['search']);
}

// Handle pagination
if (isset($_GET['page'])) {
    $current_page = max(1, intval($_GET['page']));
}

$offset = ($current_page - 1) * $items_per_page;

// Handle delete
if (isset($_POST['delete_section']) && isset($_POST['section_id'])) {
    $section_id = intval($_POST['section_id']);
    try {
        // Delete related bookmarks first
        $stmt = $pdo->prepare("DELETE FROM user_bookmarks WHERE table_name = 'criminal_procedure_code' AND section_id = ?");
        $stmt->execute([$section_id]);
        
        // Delete the section
        $stmt = $pdo->prepare("DELETE FROM criminal_procedure_code WHERE id = ?");
        $stmt->execute([$section_id]);
        
        $success_message = 'Section deleted successfully.';
    } catch (PDOException $e) {
        $error_message = 'Error deleting section: ' . $e->getMessage();
    }
}

// Handle bulk delete
if (isset($_POST['bulk_delete']) && isset($_POST['selected_ids'])) {
    $selected_ids = array_map('intval', $_POST['selected_ids']);
    if (!empty($selected_ids)) {
        try {
            $placeholders = str_repeat('?,', count($selected_ids) - 1) . '?';
            
            // Delete related bookmarks first
            $stmt = $pdo->prepare("DELETE FROM user_bookmarks WHERE table_name = 'criminal_procedure_code' AND section_id IN ($placeholders)");
            $stmt->execute($selected_ids);
            
            // Delete sections
            $stmt = $pdo->prepare("DELETE FROM criminal_procedure_code WHERE id IN ($placeholders)");
            $stmt->execute($selected_ids);
            
            $success_message = count($selected_ids) . ' sections deleted successfully.';
        } catch (PDOException $e) {
            $error_message = 'Error deleting sections: ' . $e->getMessage();
        }
    }
}

// Build search query
$where_clause = '';
$search_params = [];
if (!empty($search_query)) {
    $where_clause = "WHERE section_number LIKE ? OR section_name LIKE ? OR section_topic LIKE ? OR section_text LIKE ?";
    $search_param = '%' . $search_query . '%';
    $search_params = [$search_param, $search_param, $search_param, $search_param];
}

// Get total count
$count_sql = "SELECT COUNT(*) FROM criminal_procedure_code $where_clause";
$stmt = $pdo->prepare($count_sql);
$stmt->execute($search_params);
$total_sections = $stmt->fetchColumn();

// Get sections
$sections_sql = "SELECT * FROM criminal_procedure_code $where_clause ORDER BY CAST(section_number AS UNSIGNED) ASC LIMIT $offset, $items_per_page";
$stmt = $pdo->prepare($sections_sql);
$stmt->execute($search_params);
$sections = $stmt->fetchAll();

// Calculate pagination
$total_pages = ceil($total_sections / $items_per_page);

$page_title = "Manage Criminal Procedure Code - Admin";
include '../includes/header.php';
?>

<div class="container-fluid py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="../index.php">Home</a></li>
            <li class="breadcrumb-item"><a href="admin_index.php">Admin Dashboard</a></li>
            <li class="breadcrumb-item active">Manage Criminal Procedure Code</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2>
                        <i class="fas fa-clipboard-list me-2 text-success"></i>
                        Manage Criminal Procedure Code
                    </h2>
                    <p class="text-muted">Add, edit, and manage Criminal Procedure Code sections</p>
                </div>
                <div>
                    <a href="add_criminal_procedure_section.php" class="btn btn-success">
                        <i class="fas fa-plus me-1"></i>Add New Section
                    </a>
                    <a href="../criminal_procedure_code_act.php" class="btn btn-outline-secondary">
                        <i class="fas fa-eye me-1"></i>View Public
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Messages -->
    <?php if ($error_message): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <?php echo $error_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($success_message): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            <?php echo $success_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Search and Filter -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form method="GET" action="" class="row g-3">
                        <div class="col-md-8">
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-search"></i>
                                </span>
                                <input type="text" class="form-control" name="search" 
                                       value="<?php echo htmlspecialchars($search_query); ?>"
                                       placeholder="Search by section number, name, topic, or content...">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-success w-100">
                                <i class="fas fa-search me-1"></i>Search
                            </button>
                        </div>
                        <div class="col-md-2">
                            <a href="manage_criminal_procedure.php" class="btn btn-outline-secondary w-100">
                                <i class="fas fa-times me-1"></i>Clear
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-list-ol fa-2x text-success mb-2"></i>
                    <h4 class="mb-0"><?php echo number_format($total_sections); ?></h4>
                    <small class="text-muted">Total Sections</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-bookmark fa-2x text-primary mb-2"></i>
                    <h4 class="mb-0">
                        <?php 
                        $bookmarked = $pdo->query("SELECT COUNT(*) FROM user_bookmarks WHERE table_name = 'criminal_procedure_code'")->fetchColumn();
                        echo number_format($bookmarked); 
                        ?>
                    </h4>
                    <small class="text-muted">Bookmarked</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-sticky-note fa-2x text-warning mb-2"></i>
                    <h4 class="mb-0">
                        <?php 
                        $notes = $pdo->query("SELECT COUNT(*) FROM user_bookmarks WHERE table_name = 'criminal_procedure_code' AND notes IS NOT NULL AND notes != ''")->fetchColumn();
                        echo number_format($notes); 
                        ?>
                    </h4>
                    <small class="text-muted">With Notes</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-gavel fa-2x text-info mb-2"></i>
                    <h4 class="mb-0"><?php echo !empty($search_query) ? count($sections) : $total_sections; ?></h4>
                    <small class="text-muted">
                        <?php echo !empty($search_query) ? 'Search Results' : 'Procedures'; ?>
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="fas fa-bolt me-2"></i>Quick Actions
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-2">
                            <button class="btn btn-outline-primary btn-sm w-100" onclick="filterByCategory('arrest')">
                                <i class="fas fa-handcuffs me-1"></i>Arrest Procedures
                            </button>
                        </div>
                        <div class="col-md-3 mb-2">
                            <button class="btn btn-outline-info btn-sm w-100" onclick="filterByCategory('investigation')">
                                <i class="fas fa-search me-1"></i>Investigation
                            </button>
                        </div>
                        <div class="col-md-3 mb-2">
                            <button class="btn btn-outline-warning btn-sm w-100" onclick="filterByCategory('court')">
                                <i class="fas fa-university me-1"></i>Court Procedures
                            </button>
                        </div>
                        <div class="col-md-3 mb-2">
                            <button class="btn btn-outline-success btn-sm w-100" onclick="filterByCategory('bail')">
                                <i class="fas fa-balance-scale me-1"></i>Bail Procedures
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sections Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-light">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-table me-2"></i>
                    Criminal Procedure Code Sections
                    <?php if (!empty($search_query)): ?>
                        <small class="text-muted">- Search results for "<?php echo htmlspecialchars($search_query); ?>"</small>
                    <?php endif; ?>
                </h5>
                <div>
                    <button type="button" class="btn btn-danger btn-sm" onclick="bulkDelete()" id="bulkDeleteBtn" style="display: none;">
                        <i class="fas fa-trash me-1"></i>Delete Selected
                    </button>
                    <button type="button" class="btn btn-info btn-sm" onclick="exportData()">
                        <i class="fas fa-download me-1"></i>Export
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <?php if (!empty($sections)): ?>
            <form id="bulkForm" method="POST">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="50">
                                    <input type="checkbox" class="form-check-input" id="selectAll">
                                </th>
                                <th width="100">Section</th>
                                <th>Section Name</th>
                                <th>Topic</th>
                                <th width="150">Part/Chapter</th>
                                <th width="120">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($sections as $section): ?>
                            <tr>
                                <td>
                                    <input type="checkbox" class="form-check-input section-checkbox" 
                                           name="selected_ids[]" value="<?php echo $section['id']; ?>">
                                </td>
                                <td>
                                    <span class="badge bg-success"><?php echo htmlspecialchars($section['section_number']); ?></span>
                                    <?php if (!empty($section['sub_section_number'])): ?>
                                    <br><small class="text-muted">Sub: <?php echo htmlspecialchars($section['sub_section_number']); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($section['section_name']); ?></strong>
                                    <?php if (!empty($section['section_text'])): ?>
                                    <br><small class="text-muted">
                                        <?php echo htmlspecialchars(substr($section['section_text'], 0, 100)) . '...'; ?>
                                    </small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($section['section_topic'])): ?>
                                        <span class="text-success"><?php echo htmlspecialchars($section['section_topic']); ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($section['part_name'])): ?>
                                        <small><strong>Part:</strong> <?php echo htmlspecialchars($section['part_name']); ?></small><br>
                                    <?php endif; ?>
                                    <?php if (!empty($section['chapter_name'])): ?>
                                        <small><strong>Chapter:</strong> <?php echo htmlspecialchars($section['chapter_name']); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="edit_criminal_procedure_section.php?id=<?php echo $section['id']; ?>" 
                                           class="btn btn-sm btn-outline-success" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="../criminal_procedure_code_act.php?search=<?php echo urlencode($section['section_number']); ?>" 
                                           class="btn btn-sm btn-outline-info" title="View" target="_blank">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-danger" 
                                                onclick="deleteSection(<?php echo $section['id']; ?>, '<?php echo addslashes($section['section_name']); ?>')" 
                                                title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </form>
            <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-clipboard-list fa-4x text-muted mb-3"></i>
                <h5>No sections found</h5>
                <p class="text-muted">
                    <?php if (!empty($search_query)): ?>
                        Try adjusting your search terms or 
                        <a href="manage_criminal_procedure.php">view all sections</a>.
                    <?php else: ?>
                        Start by <a href="add_criminal_procedure_section.php">adding a new section</a>.
                    <?php endif; ?>
                </p>
            </div>
            <?php endif; ?>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <div class="card-footer">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    Showing <?php echo number_format($offset + 1); ?> to 
                    <?php echo number_format(min($offset + $items_per_page, $total_sections)); ?> 
                    of <?php echo number_format($total_sections); ?> sections
                </div>
                <nav aria-label="Sections pagination">
                    <ul class="pagination mb-0">
                        <?php if ($current_page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $current_page - 1; ?><?php echo !empty($search_query) ? '&search=' . urlencode($search_query) : ''; ?>">
                                <i class="fas fa-chevron-left"></i> Previous
                            </a>
                        </li>
                        <?php endif; ?>
                        
                        <?php
                        $start_page = max(1, $current_page - 2);
                        $end_page = min($total_pages, $start_page + 4);
                        
                        for ($i = $start_page; $i <= $end_page; $i++):
                        ?>
                        <li class="page-item <?php echo $i == $current_page ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?><?php echo !empty($search_query) ? '&search=' . urlencode($search_query) : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                        <?php endfor; ?>
                        
                        <?php if ($current_page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $current_page + 1; ?><?php echo !empty($search_query) ? '&search=' . urlencode($search_query) : ''; ?>">
                                Next <i class="fas fa-chevron-right"></i>
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this section?</p>
                <p><strong id="sectionName"></strong></p>
                <p class="text-warning small">
                    <i class="fas fa-exclamation-triangle me-1"></i>
                    This action cannot be undone. All related bookmarks and notes will also be deleted.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="section_id" id="deleteSectionId">
                    <button type="submit" name="delete_section" class="btn btn-danger">
                        <i class="fas fa-trash me-1"></i>Delete Section
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.table th {
    border-top: none;
    font-weight: 600;
    background-color: #f8f9fa;
}

.table td {
    vertical-align: middle;
}

.badge {
    font-size: 0.875rem;
}

.btn-group .btn {
    padding: 0.25rem 0.5rem;
}

@media (max-width: 768px) {
    .table-responsive {
        font-size: 0.875rem;
    }
    
    .btn-group .btn {
        padding: 0.125rem 0.25rem;
        font-size: 0.75rem;
    }
}
</style>

<script>
// Select all functionality
document.getElementById('selectAll').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.section-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
    });
    toggleBulkDeleteButton();
});

// Individual checkbox change
document.querySelectorAll('.section-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', toggleBulkDeleteButton);
});

function toggleBulkDeleteButton() {
    const selectedBoxes = document.querySelectorAll('.section-checkbox:checked');
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
    
    if (selectedBoxes.length > 0) {
        bulkDeleteBtn.style.display = 'inline-block';
    } else {
        bulkDeleteBtn.style.display = 'none';
    }
    
    // Update select all checkbox
    const allCheckboxes = document.querySelectorAll('.section-checkbox');
    const selectAllCheckbox = document.getElementById('selectAll');
    selectAllCheckbox.checked = selectedBoxes.length === allCheckboxes.length;
    selectAllCheckbox.indeterminate = selectedBoxes.length > 0 && selectedBoxes.length < allCheckboxes.length;
}

function deleteSection(id, name) {
    document.getElementById('deleteSectionId').value = id;
    document.getElementById('sectionName').textContent = name;
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}

function bulkDelete() {
    const selectedBoxes = document.querySelectorAll('.section-checkbox:checked');
    if (selectedBoxes.length === 0) {
        alert('Please select sections to delete.');
        return;
    }
    
    if (confirm(`Are you sure you want to delete ${selectedBoxes.length} selected sections? This action cannot be undone.`)) {
        const form = document.getElementById('bulkForm');
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'bulk_delete';
        input.value = '1';
        form.appendChild(input);
        form.submit();
    }
}

function filterByCategory(category) {
    const searchInput = document.querySelector('input[name="search"]');
    searchInput.value = category;
    searchInput.form.submit();
}

function exportData() {
    alert('Export functionality will be implemented. This will export the current view as CSV/Excel.');
}

// Auto-dismiss alerts
setTimeout(() => {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        if (alert.querySelector('.btn-close')) {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }
    });
}, 5000);

// Search on Enter key
document.querySelector('input[name="search"]').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        this.form.submit();
    }
});
</script>

<?php include '../includes/footer.php'; ?>