<?php
require_once 'config/config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    $_SESSION['login_required'] = true;
    $_SESSION['redirect_after_login'] = 'doc_tools.php';
    header('Location: login.php');
    exit();
}

$user = getUserInfo();
$page_title = "Document Tools";
include 'includes/header.php';
?>

<div class="container py-5">
    <div class="row">
        <div class="col-12">
            <!-- Welcome Header -->
            <div class="bg-success text-white rounded-4 p-4 mb-5">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <h1 class="h2 mb-2">
                            <i class="fas fa-file-alt me-3"></i>
                            Welcome to Berekke Doc Tools
                        </h1>
                        <p class="mb-0 opacity-75">
                            Hi <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>! 
                            Create, edit, and manage official police documents with professional templates.
                        </p>
                    </div>
                    <div class="col-lg-4 text-end">
                        <i class="fas fa-file-signature fa-4x opacity-50"></i>
                    </div>
                </div>
            </div>

            <!-- Document Tools Grid -->
            <div class="row g-4">
                <!-- Report Templates -->
                <div class="col-lg-6 col-md-6">
                    <div class="card h-100 border-0 shadow-sm hover-card">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center mb-3">
                                <div class="feature-icon bg-primary text-white rounded-circle me-3" 
                                     style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-file-contract"></i>
                                </div>
                                <h5 class="card-title mb-0">Report Templates</h5>
                            </div>
                            <p class="card-text text-muted mb-4">
                                Access pre-designed templates for police reports, incident reports, and investigation documentation.
                            </p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge bg-success">15 Templates</span>
                                <button class="btn btn-primary btn-sm" onclick="launchTool('report-templates')">
                                    <i class="fas fa-plus me-1"></i>Create
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Document Editor -->
                <div class="col-lg-6 col-md-6">
                    <div class="card h-100 border-0 shadow-sm hover-card">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center mb-3">
                                <div class="feature-icon bg-success text-white rounded-circle me-3" 
                                     style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-edit"></i>
                                </div>
                                <h5 class="card-title mb-0">Document Editor</h5>
                            </div>
                            <p class="card-text text-muted mb-4">
                                Professional document editor with formatting tools, spell check, and collaborative features.
                            </p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge bg-success">Available</span>
                                <button class="btn btn-success btn-sm" onclick="launchTool('document-editor')">
                                    <i class="fas fa-pen me-1"></i>Edit
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Builder -->
                <div class="col-lg-6 col-md-6">
                    <div class="card h-100 border-0 shadow-sm hover-card">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center mb-3">
                                <div class="feature-icon bg-warning text-white rounded-circle me-3" 
                                     style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-wpforms"></i>
                                </div>
                                <h5 class="card-title mb-0">Form Builder</h5>
                            </div>
                            <p class="card-text text-muted mb-4">
                                Create custom forms for data collection, witness statements, and case information gathering.
                            </p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge bg-success">Available</span>
                                <button class="btn btn-warning btn-sm" onclick="launchTool('form-builder')">
                                    <i class="fas fa-hammer me-1"></i>Build
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- PDF Generator -->
                <div class="col-lg-6 col-md-6">
                    <div class="card h-100 border-0 shadow-sm hover-card">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center mb-3">
                                <div class="feature-icon bg-danger text-white rounded-circle me-3" 
                                     style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-file-pdf"></i>
                                </div>
                                <h5 class="card-title mb-0">PDF Generator</h5>
                            </div>
                            <p class="card-text text-muted mb-4">
                                Convert documents to PDF format with official letterheads, signatures, and security features.
                            </p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge bg-success">Available</span>
                                <button class="btn btn-danger btn-sm" onclick="launchTool('pdf-generator')">
                                    <i class="fas fa-download me-1"></i>Generate
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Document Scanner -->
                <div class="col-lg-6 col-md-6">
                    <div class="card h-100 border-0 shadow-sm hover-card">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center mb-3">
                                <div class="feature-icon bg-info text-white rounded-circle me-3" 
                                     style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-scanner"></i>
                                </div>
                                <h5 class="card-title mb-0">Document Scanner</h5>
                            </div>
                            <p class="card-text text-muted mb-4">
                                Scan physical documents, enhance image quality, and convert to searchable digital formats.
                            </p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge bg-warning">Beta</span>
                                <button class="btn btn-info btn-sm" onclick="launchTool('document-scanner')">
                                    <i class="fas fa-camera me-1"></i>Scan
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Digital Signatures -->
                <div class="col-lg-6 col-md-6">
                    <div class="card h-100 border-0 shadow-sm hover-card">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center mb-3">
                                <div class="feature-icon bg-secondary text-white rounded-circle me-3" 
                                     style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-signature"></i>
                                </div>
                                <h5 class="card-title mb-0">Digital Signatures</h5>
                            </div>
                            <p class="card-text text-muted mb-4">
                                Add secure digital signatures to documents with timestamp and verification capabilities.
                            </p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge bg-success">Secure</span>
                                <button class="btn btn-secondary btn-sm" onclick="launchTool('digital-signatures')">
                                    <i class="fas fa-pen-nib me-1"></i>Sign
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Documents -->
            <div class="row mt-5">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="fas fa-clock me-2"></i>
                                Recent Documents
                            </h5>
                            <button class="btn btn-sm btn-outline-primary">View All</button>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <div class="d-flex align-items-center p-2 rounded border">
                                        <i class="fas fa-file-pdf text-danger me-3 fa-2x"></i>
                                        <div>
                                            <h6 class="mb-1">Incident Report #2024-001</h6>
                                            <small class="text-muted">Modified 2 hours ago</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="d-flex align-items-center p-2 rounded border">
                                        <i class="fas fa-file-word text-primary me-3 fa-2x"></i>
                                        <div>
                                            <h6 class="mb-1">Investigation Notes</h6>
                                            <small class="text-muted">Modified 1 day ago</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="d-flex align-items-center p-2 rounded border">
                                        <i class="fas fa-file-alt text-success me-3 fa-2x"></i>
                                        <div>
                                            <h6 class="mb-1">Case Summary Draft</h6>
                                            <small class="text-muted">Modified 3 days ago</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Document Tool Modal -->
<div class="modal fade" id="docToolModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="toolModalTitle">Document Tool</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="toolModalBody" style="height: 70vh; overflow-y: auto;">
                <div class="text-center p-5">
                    <div class="spinner-border text-success mb-3" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p>Loading document tool...</p>
                </div>
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

.feature-icon {
    transition: all 0.3s ease;
}

.hover-card:hover .feature-icon {
    transform: scale(1.1);
}

@media (max-width: 768px) {
    .bg-success.rounded-4 .col-lg-4 {
        text-align: center !important;
        margin-top: 1rem;
    }
}
</style>

<script>
function launchTool(toolType) {
    const modal = new bootstrap.Modal(document.getElementById('docToolModal'));
    const modalTitle = document.getElementById('toolModalTitle');
    const modalBody = document.getElementById('toolModalBody');
    
    // Set tool-specific content
    const toolConfig = {
        'report-templates': {
            title: 'Report Templates',
            content: `
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <div class="card h-100 template-card" onclick="selectTemplate('incident')">
                            <div class="card-body text-center">
                                <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                                <h6>Incident Report</h6>
                                <p class="small text-muted">Standard incident documentation template</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card h-100 template-card" onclick="selectTemplate('investigation')">
                            <div class="card-body text-center">
                                <i class="fas fa-search fa-3x text-info mb-3"></i>
                                <h6>Investigation Report</h6>
                                <p class="small text-muted">Detailed investigation findings template</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card h-100 template-card" onclick="selectTemplate('evidence')">
                            <div class="card-body text-center">
                                <i class="fas fa-box fa-3x text-primary mb-3"></i>
                                <h6>Evidence Log</h6>
                                <p class="small text-muted">Evidence collection and tracking template</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card h-100 template-card" onclick="selectTemplate('witness')">
                            <div class="card-body text-center">
                                <i class="fas fa-user-tie fa-3x text-success mb-3"></i>
                                <h6>Witness Statement</h6>
                                <p class="small text-muted">Witness interview documentation template</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card h-100 template-card" onclick="selectTemplate('arrest')">
                            <div class="card-body text-center">
                                <i class="fas fa-handcuffs fa-3x text-danger mb-3"></i>
                                <h6>Arrest Report</h6>
                                <p class="small text-muted">Arrest documentation and Miranda rights</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card h-100 template-card" onclick="selectTemplate('case-closure')">
                            <div class="card-body text-center">
                                <i class="fas fa-check-circle fa-3x text-secondary mb-3"></i>
                                <h6>Case Closure</h6>
                                <p class="small text-muted">Case resolution and closure template</p>
                            </div>
                        </div>
                    </div>
                </div>
            `
        },
        'document-editor': {
            title: 'Document Editor',
            content: `
                <div class="row">
                    <div class="col-12">
                        <div class="toolbar mb-3 p-2 bg-light rounded">
                            <div class="btn-group me-2" role="group">
                                <button type="button" class="btn btn-sm btn-outline-secondary"><i class="fas fa-bold"></i></button>
                                <button type="button" class="btn btn-sm btn-outline-secondary"><i class="fas fa-italic"></i></button>
                                <button type="button" class="btn btn-sm btn-outline-secondary"><i class="fas fa-underline"></i></button>
                            </div>
                            <div class="btn-group me-2" role="group">
                                <button type="button" class="btn btn-sm btn-outline-secondary"><i class="fas fa-align-left"></i></button>
                                <button type="button" class="btn btn-sm btn-outline-secondary"><i class="fas fa-align-center"></i></button>
                                <button type="button" class="btn btn-sm btn-outline-secondary"><i class="fas fa-align-right"></i></button>
                            </div>
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-sm btn-outline-primary"><i class="fas fa-save"></i> Save</button>
                                <button type="button" class="btn btn-sm btn-outline-success"><i class="fas fa-download"></i> Export</button>
                            </div>
                        </div>
                        <textarea class="form-control" rows="15" placeholder="Start writing your document here..."></textarea>
                    </div>
                </div>
            `
        }
    };
    
    const config = toolConfig[toolType] || {
        title: 'Document Tool',
        content: '<div class="p-4"><p>Tool configuration not found.</p></div>'
    };
    
    modalTitle.textContent = config.title;
    modalBody.innerHTML = config.content;
    
    modal.show();
}

function selectTemplate(templateType) {
    alert('Template "' + templateType + '" selected. Opening in editor...');
    // Here you would load the actual template content
}

// Add CSS for template cards
const style = document.createElement('style');
style.textContent = `
    .template-card {
        cursor: pointer;
        transition: all 0.3s ease;
        border: 2px solid transparent;
    }
    
    .template-card:hover {
        border-color: var(--bs-primary);
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
`;
document.head.appendChild(style);

// Animation on load
document.addEventListener('DOMContentLoaded', function() {
    const cards = document.querySelectorAll('.hover-card');
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