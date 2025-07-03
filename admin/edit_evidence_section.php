<?php
require_once '../config/config.php';

// Check if user is admin
requireAdmin();

$error_message = '';
$success_message = '';
$section_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$section_data = null;

// Fetch section data if editing
if ($section_id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM evidence_ordinance WHERE id = ?");
    $stmt->execute([$section_id]);
    $section_data = $stmt->fetch();
    
    if (!$section_data) {
        $_SESSION['error_message'] = 'Section not found.';
        header('Location: manage_evidence_ordinance.php');
        exit();
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $part_number = cleanInput($_POST['part_number']);
    $part_name = cleanInput($_POST['part_name']);
    $chapter = cleanInput($_POST['chapter']);
    $chapter_name = cleanInput($_POST['chapter_name']);
    $section_number = cleanInput($_POST['section_number']);
    $sub_section_number = cleanInput($_POST['sub_section_number']);
    $section_name = cleanInput($_POST['section_name']);
    $section_topic = cleanInput($_POST['section_topic']);
    $section_text = cleanInput($_POST['section_text']);
    $explanation_1 = cleanInput($_POST['explanation_1']);
    $illustrations_1 = cleanInput($_POST['illustrations_1']);
    $explanation_2 = cleanInput($_POST['explanation_2']);
    $illustrations_2 = cleanInput($_POST['illustrations_2']);
    $explanation_3 = cleanInput($_POST['explanation_3']);
    $illustrations_3 = cleanInput($_POST['illustrations_3']);
    $explanation_4 = cleanInput($_POST['explanation_4']);
    $illustrations_4 = cleanInput($_POST['illustrations_4']);
    $amendments = cleanInput($_POST['amendments']);
    
    // Validation
    if (empty($section_number) || empty($section_name) || empty($section_text)) {
        $error_message = 'Section number, section name, and section text are required fields.';
    } else {
        try {
            if ($section_id > 0) {
                // Update existing section
                $sql = "UPDATE evidence_ordinance SET 
                        part_number = ?, part_name = ?, chapter = ?, chapter_name = ?, 
                        section_number = ?, sub_section_number = ?, section_name = ?, 
                        section_topic = ?, section_text = ?, explanation_1 = ?, 
                        illustrations_1 = ?, explanation_2 = ?, illustrations_2 = ?, 
                        explanation_3 = ?, illustrations_3 = ?, explanation_4 = ?, 
                        illustrations_4 = ?, amendments = ?
                        WHERE id = ?";
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $part_number, $part_name, $chapter, $chapter_name,
                    $section_number, $sub_section_number, $section_name,
                    $section_topic, $section_text, $explanation_1,
                    $illustrations_1, $explanation_2, $illustrations_2,
                    $explanation_3, $illustrations_3, $explanation_4,
                    $illustrations_4, $amendments, $section_id
                ]);
                
                $success_message = 'Evidence Ordinance section updated successfully!';
                
                // Refresh section data
                $stmt = $pdo->prepare("SELECT * FROM evidence_ordinance WHERE id = ?");
                $stmt->execute([$section_id]);
                $section_data = $stmt->fetch();
                
            } else {
                // Insert new section
                $sql = "INSERT INTO evidence_ordinance 
                        (part_number, part_name, chapter, chapter_name, section_number, 
                         sub_section_number, section_name, section_topic, section_text, 
                         explanation_1, illustrations_1, explanation_2, illustrations_2, 
                         explanation_3, illustrations_3, explanation_4, illustrations_4, amendments) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $part_number, $part_name, $chapter, $chapter_name,
                    $section_number, $sub_section_number, $section_name,
                    $section_topic, $section_text, $explanation_1,
                    $illustrations_1, $explanation_2, $illustrations_2,
                    $explanation_3, $illustrations_3, $explanation_4,
                    $illustrations_4, $amendments
                ]);
                
                $new_section_id = $pdo->lastInsertId();
                $success_message = 'New Evidence Ordinance section created successfully!';
                
                // Redirect to edit the new section
                header("Location: edit_evidence_section.php?id=$new_section_id&success=1");
                exit();
            }
        } catch (PDOException $e) {
            $error_message = 'Database error: ' . $e->getMessage();
        }
    }
}

// Check for success parameter
if (isset($_GET['success'])) {
    $success_message = 'Evidence Ordinance section created successfully!';
}

$page_title = $section_id > 0 ? "Edit Evidence Ordinance Section" : "Add New Evidence Ordinance Section";
include '../includes/header.php';
?>

<div class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="admin_index.php">Admin Dashboard</a></li>
            <li class="breadcrumb-item"><a href="manage_evidence_ordinance.php">Evidence Ordinance</a></li>
            <li class="breadcrumb-item active"><?php echo $section_id > 0 ? 'Edit Section' : 'Add Section'; ?></li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h2 mb-1">
                        <i class="fas fa-search me-2"></i>
                        <?php echo $page_title; ?>
                    </h1>
                    <?php if ($section_data): ?>
                        <p class="text-muted mb-0">Section <?php echo htmlspecialchars($section_data['section_number']); ?></p>
                    <?php endif; ?>
                </div>
                <div>
                    <a href="manage_evidence_ordinance.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Back to List
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Alerts -->
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

    <!-- Form -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-edit me-2"></i>
                        Section Details
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="" id="sectionForm">
                        <div class="row">
                            <!-- Basic Information -->
                            <div class="col-lg-6">
                                <h6 class="text-primary mb-3">Basic Information</h6>
                                
                                <div class="mb-3">
                                    <label for="part_number" class="form-label">Part Number</label>
                                    <input type="text" class="form-control" id="part_number" name="part_number" 
                                           value="<?php echo $section_data ? htmlspecialchars($section_data['part_number']) : ''; ?>"
                                           placeholder="e.g., I, II, III">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="part_name" class="form-label">Part Name</label>
                                    <input type="text" class="form-control" id="part_name" name="part_name" 
                                           value="<?php echo $section_data ? htmlspecialchars($section_data['part_name']) : ''; ?>"
                                           placeholder="Part name in Sinhala">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="chapter" class="form-label">Chapter Number</label>
                                    <input type="text" class="form-control" id="chapter" name="chapter" 
                                           value="<?php echo $section_data ? htmlspecialchars($section_data['chapter']) : ''; ?>"
                                           placeholder="e.g., 1, 2, 3">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="chapter_name" class="form-label">Chapter Name</label>
                                    <input type="text" class="form-control" id="chapter_name" name="chapter_name" 
                                           value="<?php echo $section_data ? htmlspecialchars($section_data['chapter_name']) : ''; ?>"
                                           placeholder="Chapter name in Sinhala">
                                </div>
                            </div>
                            
                            <!-- Section Details -->
                            <div class="col-lg-6">
                                <h6 class="text-success mb-3">Section Details</h6>
                                
                                <div class="mb-3">
                                    <label for="section_number" class="form-label">Section Number <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="section_number" name="section_number" 
                                           value="<?php echo $section_data ? htmlspecialchars($section_data['section_number']) : ''; ?>"
                                           placeholder="e.g., 1, 2, 3" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="sub_section_number" class="form-label">Sub-section Number</label>
                                    <input type="text" class="form-control" id="sub_section_number" name="sub_section_number" 
                                           value="<?php echo $section_data ? htmlspecialchars($section_data['sub_section_number']) : ''; ?>"
                                           placeholder="e.g., (1), (2), (a)">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="section_name" class="form-label">Section Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="section_name" name="section_name" 
                                           value="<?php echo $section_data ? htmlspecialchars($section_data['section_name']) : ''; ?>"
                                           placeholder="Section name in Sinhala" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="section_topic" class="form-label">Section Topic</label>
                                    <input type="text" class="form-control" id="section_topic" name="section_topic" 
                                           value="<?php echo $section_data ? htmlspecialchars($section_data['section_topic']) : ''; ?>"
                                           placeholder="Brief topic description">
                                </div>
                            </div>
                        </div>
                        
                        <!-- Section Text -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <h6 class="text-warning mb-3">Section Content</h6>
                                
                                <div class="mb-4">
                                    <label for="section_text" class="form-label">Section Text <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="section_text" name="section_text" rows="6" 
                                              placeholder="Enter the complete section text in Sinhala" required><?php echo $section_data ? htmlspecialchars($section_data['section_text']) : ''; ?></textarea>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Explanations and Illustrations -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <h6 class="text-info mb-3">Explanations and Illustrations</h6>
                                
                                <!-- Explanation 1 & Illustrations 1 -->
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label for="explanation_1" class="form-label">Explanation 1</label>
                                            <textarea class="form-control" id="explanation_1" name="explanation_1" rows="4" 
                                                      placeholder="First explanation in Sinhala"><?php echo $section_data ? htmlspecialchars($section_data['explanation_1']) : ''; ?></textarea>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label for="illustrations_1" class="form-label">Illustrations 1</label>
                                            <textarea class="form-control" id="illustrations_1" name="illustrations_1" rows="4" 
                                                      placeholder="First illustration in Sinhala"><?php echo $section_data ? htmlspecialchars($section_data['illustrations_1']) : ''; ?></textarea>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Explanation 2 & Illustrations 2 -->
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label for="explanation_2" class="form-label">Explanation 2</label>
                                            <textarea class="form-control" id="explanation_2" name="explanation_2" rows="4" 
                                                      placeholder="Second explanation in Sinhala"><?php echo $section_data ? htmlspecialchars($section_data['explanation_2']) : ''; ?></textarea>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label for="illustrations_2" class="form-label">Illustrations 2</label>
                                            <textarea class="form-control" id="illustrations_2" name="illustrations_2" rows="4" 
                                                      placeholder="Second illustration in Sinhala"><?php echo $section_data ? htmlspecialchars($section_data['illustrations_2']) : ''; ?></textarea>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Explanation 3 & Illustrations 3 -->
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label for="explanation_3" class="form-label">Explanation 3</label>
                                            <textarea class="form-control" id="explanation_3" name="explanation_3" rows="4" 
                                                      placeholder="Third explanation in Sinhala"><?php echo $section_data ? htmlspecialchars($section_data['explanation_3']) : ''; ?></textarea>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label for="illustrations_3" class="form-label">Illustrations 3</label>
                                            <textarea class="form-control" id="illustrations_3" name="illustrations_3" rows="4" 
                                                      placeholder="Third illustration in Sinhala"><?php echo $section_data ? htmlspecialchars($section_data['illustrations_3']) : ''; ?></textarea>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Explanation 4 & Illustrations 4 -->
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label for="explanation_4" class="form-label">Explanation 4</label>
                                            <textarea class="form-control" id="explanation_4" name="explanation_4" rows="4" 
                                                      placeholder="Fourth explanation in Sinhala"><?php echo $section_data ? htmlspecialchars($section_data['explanation_4']) : ''; ?></textarea>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label for="illustrations_4" class="form-label">Illustrations 4</label>
                                            <textarea class="form-control" id="illustrations_4" name="illustrations_4" rows="4" 
                                                      placeholder="Fourth illustration in Sinhala"><?php echo $section_data ? htmlspecialchars($section_data['illustrations_4']) : ''; ?></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Amendments -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <h6 class="text-secondary mb-3">Amendments</h6>
                                
                                <div class="mb-4">
                                    <label for="amendments" class="form-label">Amendments</label>
                                    <textarea class="form-control" id="amendments" name="amendments" rows="4" 
                                              placeholder="Any amendments to this section in Sinhala"><?php echo $section_data ? htmlspecialchars($section_data['amendments']) : ''; ?></textarea>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Action Buttons -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <a href="manage_evidence_ordinance.php" class="btn btn-outline-secondary">
                                            <i class="fas fa-times me-1"></i>Cancel
                                        </a>
                                    </div>
                                    <div>
                                        <button type="button" class="btn btn-outline-primary me-2" onclick="previewSection()">
                                            <i class="fas fa-eye me-1"></i>Preview
                                        </button>
                                        <button type="submit" class="btn btn-success" id="saveBtn">
                                            <i class="fas fa-save me-1"></i>
                                            <?php echo $section_id > 0 ? 'Update Section' : 'Create Section'; ?>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Section Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="previewContent">
                <!-- Preview content will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="$('#previewModal').modal('hide'); $('#saveBtn').click();">
                    <i class="fas fa-save me-1"></i>Save Section
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.form-label {
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.card-header h6 {
    margin-bottom: 0;
}

textarea.form-control {
    resize: vertical;
    min-height: 100px;
}

.text-danger {
    color: #dc3545 !important;
}

.preview-section {
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    padding: 1rem;
    background-color: #f8f9fa;
}

.preview-label {
    font-weight: 600;
    color: #495057;
    margin-bottom: 0.5rem;
}

.preview-content {
    margin-bottom: 1rem;
    line-height: 1.6;
}

@media (max-width: 768px) {
    .d-flex.justify-content-between {
        flex-direction: column;
        gap: 1rem;
    }
    
    .d-flex.justify-content-between > div {
        text-align: center;
    }
}
</style>

<script>
// Form validation
document.getElementById('sectionForm').addEventListener('submit', function(e) {
    const requiredFields = ['section_number', 'section_name', 'section_text'];
    let isValid = true;
    
    requiredFields.forEach(field => {
        const input = document.getElementById(field);
        if (!input.value.trim()) {
            isValid = false;
            input.classList.add('is-invalid');
        } else {
            input.classList.remove('is-invalid');
        }
    });
    
    if (!isValid) {
        e.preventDefault();
        alert('Please fill in all required fields.');
        return false;
    }
    
    // Show loading state
    const saveBtn = document.getElementById('saveBtn');
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Saving...';
});

// Auto-save draft functionality
let autoSaveTimer;
function autoSave() {
    clearTimeout(autoSaveTimer);
    autoSaveTimer = setTimeout(() => {
        // Save as draft to localStorage
        const formData = new FormData(document.getElementById('sectionForm'));
        const draftData = {};
        for (let [key, value] of formData.entries()) {
            draftData[key] = value;
        }
        localStorage.setItem('evidence_section_draft', JSON.stringify(draftData));
        
        // Show draft saved indicator
        showDraftSaved();
    }, 30000); // Auto-save every 30 seconds
}

function showDraftSaved() {
    const indicator = document.createElement('div');
    indicator.className = 'alert alert-info alert-dismissible fade show position-fixed';
    indicator.style.top = '20px';
    indicator.style.right = '20px';
    indicator.style.zIndex = '9999';
    indicator.innerHTML = `
        <i class="fas fa-save me-2"></i>Draft saved automatically
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.body.appendChild(indicator);
    
    setTimeout(() => {
        if (indicator.parentNode) {
            indicator.remove();
        }
    }, 3000);
}

// Load draft on page load
document.addEventListener('DOMContentLoaded', function() {
    const savedDraft = localStorage.getItem('evidence_section_draft');
    if (savedDraft && !<?php echo $section_id > 0 ? 'true' : 'false'; ?>) {
        const draftData = JSON.parse(savedDraft);
        if (confirm('A draft was found. Would you like to restore it?')) {
            Object.keys(draftData).forEach(key => {
                const input = document.getElementById(key);
                if (input) {
                    input.value = draftData[key];
                }
            });
        }
    }
    
    // Start auto-save
    document.querySelectorAll('input, textarea').forEach(input => {
        input.addEventListener('input', autoSave);
    });
});

// Preview functionality
function previewSection() {
    const formData = new FormData(document.getElementById('sectionForm'));
    const previewContent = document.getElementById('previewContent');
    
    let previewHtml = `
        <div class="preview-section">
            <div class="row">
                <div class="col-md-6">
                    <div class="preview-label">Section Number:</div>
                    <div class="preview-content">${formData.get('section_number') || 'Not specified'}</div>
                </div>
                <div class="col-md-6">
                    <div class="preview-label">Section Name:</div>
                    <div class="preview-content">${formData.get('section_name') || 'Not specified'}</div>
                </div>
            </div>
            
            ${formData.get('section_topic') ? `
            <div class="preview-label">Section Topic:</div>
            <div class="preview-content">${formData.get('section_topic')}</div>
            ` : ''}
            
            <div class="preview-label">Section Text:</div>
            <div class="preview-content">${formData.get('section_text').replace(/\n/g, '<br>') || 'No content'}</div>
            
            ${formData.get('explanation_1') ? `
            <div class="preview-label">Explanation 1:</div>
            <div class="preview-content">${formData.get('explanation_1').replace(/\n/g, '<br>')}</div>
            ` : ''}
            
            ${formData.get('illustrations_1') ? `
            <div class="preview-label">Illustrations 1:</div>
            <div class="preview-content">${formData.get('illustrations_1').replace(/\n/g, '<br>')}</div>
            ` : ''}
            
            ${formData.get('amendments') ? `
            <div class="preview-label">Amendments:</div>
            <div class="preview-content">${formData.get('amendments').replace(/\n/g, '<br>')}</div>
            ` : ''}
        </div>
    `;
    
    previewContent.innerHTML = previewHtml;
    const modal = new bootstrap.Modal(document.getElementById('previewModal'));
    modal.show();
}

// Clear draft when form is successfully submitted
<?php if ($success_message): ?>
localStorage.removeItem('evidence_section_draft');
<?php endif; ?>

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    // Ctrl+S to save
    if (e.ctrlKey && e.key === 's') {
        e.preventDefault();
        document.getElementById('saveBtn').click();
    }
    
    // Ctrl+P to preview
    if (e.ctrlKey && e.key === 'p') {
        e.preventDefault();
        previewSection();
    }
});
</script>

<?php include '../includes/footer.php'; ?>