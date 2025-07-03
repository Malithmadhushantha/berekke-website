<?php
require_once '../config/config.php';

// Check if user is admin
requireAdmin();

$error_message = '';
$success_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
        $error_message = 'Section number, name, and text are required fields.';
    } else {
        // Check if section already exists
        $stmt = $pdo->prepare("SELECT id FROM penal_code WHERE section_number = ?");
        $stmt->execute([$section_number]);
        if ($stmt->fetch()) {
            $error_message = 'A section with this number already exists.';
        } else {
            try {
                $sql = "INSERT INTO penal_code (
                    part_number, part_name, chapter, chapter_name, section_number, 
                    sub_section_number, section_name, section_topic, section_text,
                    explanation_1, illustrations_1, explanation_2, illustrations_2,
                    explanation_3, illustrations_3, explanation_4, illustrations_4, amendments
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $part_number, $part_name, $chapter, $chapter_name, $section_number,
                    $sub_section_number, $section_name, $section_topic, $section_text,
                    $explanation_1, $illustrations_1, $explanation_2, $illustrations_2,
                    $explanation_3, $illustrations_3, $explanation_4, $illustrations_4, $amendments
                ]);
                
                $success_message = 'Penal Code section added successfully!';
                
                // Clear form data
                $_POST = array();
            } catch (PDOException $e) {
                $error_message = 'Error adding section: ' . $e->getMessage();
            }
        }
    }
}

$page_title = "Add Penal Code Section";
include '../includes/header.php';
?>

<div class="container py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2>
                        <i class="fas fa-plus-circle me-2 text-primary"></i>
                        Add New Penal Code Section
                    </h2>
                    <p class="text-muted">Add a new section to the Sri Lankan Penal Code database</p>
                </div>
                <div>
                    <a href="admin_index.php" class="btn btn-outline-secondary me-2">
                        <i class="fas fa-arrow-left me-1"></i>Back to Dashboard
                    </a>
                    <a href="manage_penal_code.php" class="btn btn-outline-primary">
                        <i class="fas fa-list me-1"></i>View All Sections
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

    <!-- Form -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-gavel me-2"></i>
                        Penal Code Section Details
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <!-- Basic Information -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="text-primary mb-3">
                                    <i class="fas fa-info-circle me-2"></i>Basic Information
                                </h6>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="part_number" class="form-label">Part Number</label>
                                <input type="text" class="form-control" id="part_number" name="part_number" 
                                       value="<?php echo isset($_POST['part_number']) ? htmlspecialchars($_POST['part_number']) : ''; ?>"
                                       placeholder="e.g., I, II, III">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="part_name" class="form-label">Part Name</label>
                                <input type="text" class="form-control" id="part_name" name="part_name" 
                                       value="<?php echo isset($_POST['part_name']) ? htmlspecialchars($_POST['part_name']) : ''; ?>"
                                       placeholder="Part name in Sinhala">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="chapter" class="form-label">Chapter</label>
                                <input type="text" class="form-control" id="chapter" name="chapter" 
                                       value="<?php echo isset($_POST['chapter']) ? htmlspecialchars($_POST['chapter']) : ''; ?>"
                                       placeholder="e.g., I, II, III">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="chapter_name" class="form-label">Chapter Name</label>
                                <input type="text" class="form-control" id="chapter_name" name="chapter_name" 
                                       value="<?php echo isset($_POST['chapter_name']) ? htmlspecialchars($_POST['chapter_name']) : ''; ?>"
                                       placeholder="Chapter name in Sinhala">
                            </div>
                        </div>

                        <!-- Section Details -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="text-primary mb-3">
                                    <i class="fas fa-bookmark me-2"></i>Section Details
                                </h6>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="section_number" class="form-label">Section Number <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="section_number" name="section_number" 
                                       value="<?php echo isset($_POST['section_number']) ? htmlspecialchars($_POST['section_number']) : ''; ?>"
                                       placeholder="e.g., 302, 379" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="sub_section_number" class="form-label">Sub-section Number</label>
                                <input type="text" class="form-control" id="sub_section_number" name="sub_section_number" 
                                       value="<?php echo isset($_POST['sub_section_number']) ? htmlspecialchars($_POST['sub_section_number']) : ''; ?>"
                                       placeholder="e.g., (1), (2), (a)">
                            </div>
                            
                            <div class="col-12 mb-3">
                                <label for="section_name" class="form-label">Section Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="section_name" name="section_name" 
                                       value="<?php echo isset($_POST['section_name']) ? htmlspecialchars($_POST['section_name']) : ''; ?>"
                                       placeholder="Section name in Sinhala" required>
                            </div>
                            
                            <div class="col-12 mb-3">
                                <label for="section_topic" class="form-label">Section Topic</label>
                                <input type="text" class="form-control" id="section_topic" name="section_topic" 
                                       value="<?php echo isset($_POST['section_topic']) ? htmlspecialchars($_POST['section_topic']) : ''; ?>"
                                       placeholder="Brief topic or subject in Sinhala">
                            </div>
                            
                            <div class="col-12 mb-3">
                                <label for="section_text" class="form-label">Section Text <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="section_text" name="section_text" rows="6" 
                                          placeholder="Complete section text in Sinhala" required><?php echo isset($_POST['section_text']) ? htmlspecialchars($_POST['section_text']) : ''; ?></textarea>
                            </div>
                        </div>

                        <!-- Explanations and Illustrations -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="text-primary mb-3">
                                    <i class="fas fa-lightbulb me-2"></i>Explanations and Illustrations
                                </h6>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="explanation_1" class="form-label">Explanation 1</label>
                                <textarea class="form-control" id="explanation_1" name="explanation_1" rows="4" 
                                          placeholder="First explanation in Sinhala"><?php echo isset($_POST['explanation_1']) ? htmlspecialchars($_POST['explanation_1']) : ''; ?></textarea>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="illustrations_1" class="form-label">Illustrations 1</label>
                                <textarea class="form-control" id="illustrations_1" name="illustrations_1" rows="4" 
                                          placeholder="First illustration in Sinhala"><?php echo isset($_POST['illustrations_1']) ? htmlspecialchars($_POST['illustrations_1']) : ''; ?></textarea>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="explanation_2" class="form-label">Explanation 2</label>
                                <textarea class="form-control" id="explanation_2" name="explanation_2" rows="4" 
                                          placeholder="Second explanation in Sinhala"><?php echo isset($_POST['explanation_2']) ? htmlspecialchars($_POST['explanation_2']) : ''; ?></textarea>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="illustrations_2" class="form-label">Illustrations 2</label>
                                <textarea class="form-control" id="illustrations_2" name="illustrations_2" rows="4" 
                                          placeholder="Second illustration in Sinhala"><?php echo isset($_POST['illustrations_2']) ? htmlspecialchars($_POST['illustrations_2']) : ''; ?></textarea>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="explanation_3" class="form-label">Explanation 3</label>
                                <textarea class="form-control" id="explanation_3" name="explanation_3" rows="4" 
                                          placeholder="Third explanation in Sinhala"><?php echo isset($_POST['explanation_3']) ? htmlspecialchars($_POST['explanation_3']) : ''; ?></textarea>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="illustrations_3" class="form-label">Illustrations 3</label>
                                <textarea class="form-control" id="illustrations_3" name="illustrations_3" rows="4" 
                                          placeholder="Third illustration in Sinhala"><?php echo isset($_POST['illustrations_3']) ? htmlspecialchars($_POST['illustrations_3']) : ''; ?></textarea>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="explanation_4" class="form-label">Explanation 4</label>
                                <textarea class="form-control" id="explanation_4" name="explanation_4" rows="4" 
                                          placeholder="Fourth explanation in Sinhala"><?php echo isset($_POST['explanation_4']) ? htmlspecialchars($_POST['explanation_4']) : ''; ?></textarea>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="illustrations_4" class="form-label">Illustrations 4</label>
                                <textarea class="form-control" id="illustrations_4" name="illustrations_4" rows="4" 
                                          placeholder="Fourth illustration in Sinhala"><?php echo isset($_POST['illustrations_4']) ? htmlspecialchars($_POST['illustrations_4']) : ''; ?></textarea>
                            </div>
                        </div>

                        <!-- Amendments -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="text-primary mb-3">
                                    <i class="fas fa-edit me-2"></i>Amendments
                                </h6>
                            </div>
                            
                            <div class="col-12 mb-3">
                                <label for="amendments" class="form-label">Amendments</label>
                                <textarea class="form-control" id="amendments" name="amendments" rows="4" 
                                          placeholder="Any amendments to this section in Sinhala"><?php echo isset($_POST['amendments']) ? htmlspecialchars($_POST['amendments']) : ''; ?></textarea>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="row">
                            <div class="col-12">
                                <div class="d-flex justify-content-end gap-2">
                                    <button type="button" class="btn btn-secondary" onclick="history.back()">
                                        <i class="fas fa-times me-1"></i>Cancel
                                    </button>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i>Save Section
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.form-label {
    font-weight: 600;
    color: #495057;
}

.text-danger {
    font-weight: bold;
}

.card {
    border-radius: 10px;
}

.card-header {
    border-radius: 10px 10px 0 0 !important;
}

textarea {
    resize: vertical;
    min-height: 80px;
}

.btn {
    border-radius: 8px;
    padding: 0.5rem 1.5rem;
}

@media (max-width: 768px) {
    .d-flex.justify-content-between {
        flex-direction: column;
        gap: 1rem;
    }
    
    .d-flex.justify-content-end {
        justify-content: stretch !important;
    }
    
    .d-flex.justify-content-end .btn {
        flex: 1;
    }
}
</style>

<script>
// Auto-resize textareas
document.addEventListener('DOMContentLoaded', function() {
    const textareas = document.querySelectorAll('textarea');
    textareas.forEach(textarea => {
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = this.scrollHeight + 'px';
        });
    });
});

// Form validation
document.querySelector('form').addEventListener('submit', function(e) {
    const requiredFields = ['section_number', 'section_name', 'section_text'];
    let hasError = false;
    
    requiredFields.forEach(field => {
        const input = document.getElementById(field);
        if (!input.value.trim()) {
            input.classList.add('is-invalid');
            hasError = true;
        } else {
            input.classList.remove('is-invalid');
        }
    });
    
    if (hasError) {
        e.preventDefault();
        alert('Please fill in all required fields.');
    }
});

// Clear validation on input
document.querySelectorAll('input, textarea').forEach(element => {
    element.addEventListener('input', function() {
        this.classList.remove('is-invalid');
    });
});
</script>

<?php include '../includes/footer.php'; ?>