<?php
require_once 'config/config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    $_SESSION['login_required'] = true;
    $_SESSION['redirect_after_login'] = 'image_tool.php';
    header('Location: login.php');
    exit();
}

$user = getUserInfo();
$page_title = "Image Tools";
include 'includes/header.php';
?>

<div class="container py-5">
    <div class="row">
        <div class="col-12">
            <!-- Welcome Header -->
            <div class="bg-info text-white rounded-4 p-4 mb-5">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <h1 class="h2 mb-2">
                            <i class="fas fa-image me-3"></i>
                            Welcome to Berekke Image Tools
                        </h1>
                        <p class="mb-0 opacity-75">
                            Hi <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>! 
                            Process, analyze, and enhance images for investigations and documentation.
                        </p>
                    </div>
                    <div class="col-lg-4 text-end">
                        <i class="fas fa-camera fa-4x opacity-50"></i>
                    </div>
                </div>
            </div>

            <!-- Image Tools Grid -->
            <div class="row g-4">
                <!-- Photo Enhancement -->
                <div class="col-lg-6 col-md-6">
                    <div class="card h-100 border-0 shadow-sm hover-card">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center mb-3">
                                <div class="feature-icon bg-primary text-white rounded-circle me-3" 
                                     style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-magic"></i>
                                </div>
                                <h5 class="card-title mb-0">Photo Enhancement</h5>
                            </div>
                            <p class="card-text text-muted mb-4">
                                Enhance low-quality images, improve brightness, contrast, and clarity for better evidence documentation.
                            </p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge bg-success">AI Powered</span>
                                <button class="btn btn-primary btn-sm" onclick="launchTool('photo-enhancement')">
                                    <i class="fas fa-upload me-1"></i>Enhance
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Face Recognition -->
                <div class="col-lg-6 col-md-6">
                    <div class="card h-100 border-0 shadow-sm hover-card">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center mb-3">
                                <div class="feature-icon bg-success text-white rounded-circle me-3" 
                                     style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-user-check"></i>
                                </div>
                                <h5 class="card-title mb-0">Face Recognition</h5>
                            </div>
                            <p class="card-text text-muted mb-4">
                                Identify and match faces in photos with database records for suspect identification and verification.
                            </p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge bg-warning">Restricted</span>
                                <button class="btn btn-success btn-sm" onclick="launchTool('face-recognition')">
                                    <i class="fas fa-search me-1"></i>Identify
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Evidence Photography -->
                <div class="col-lg-6 col-md-6">
                    <div class="card h-100 border-0 shadow-sm hover-card">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center mb-3">
                                <div class="feature-icon bg-warning text-white rounded-circle me-3" 
                                     style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-camera-retro"></i>
                                </div>
                                <h5 class="card-title mb-0">Evidence Photography</h5>
                            </div>
                            <p class="card-text text-muted mb-4">
                                Capture and document evidence with proper metadata, timestamps, and chain of custody information.
                            </p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge bg-success">Available</span>
                                <button class="btn btn-warning btn-sm" onclick="launchTool('evidence-photography')">
                                    <i class="fas fa-camera me-1"></i>Capture
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Image Analysis -->
                <div class="col-lg-6 col-md-6">
                    <div class="card h-100 border-0 shadow-sm hover-card">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center mb-3">
                                <div class="feature-icon bg-danger text-white rounded-circle me-3" 
                                     style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-search-plus"></i>
                                </div>
                                <h5 class="card-title mb-0">Image Analysis</h5>
                            </div>
                            <p class="card-text text-muted mb-4">
                                Analyze images for hidden details, metadata extraction, and forensic examination features.
                            </p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge bg-success">Available</span>
                                <button class="btn btn-danger btn-sm" onclick="launchTool('image-analysis')">
                                    <i class="fas fa-microscope me-1"></i>Analyze
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sketch Generator -->
                <div class="col-lg-6 col-md-6">
                    <div class="card h-100 border-0 shadow-sm hover-card">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center mb-3">
                                <div class="feature-icon bg-info text-white rounded-circle me-3" 
                                     style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-pencil-alt"></i>
                                </div>
                                <h5 class="card-title mb-0">Sketch Generator</h5>
                            </div>
                            <p class="card-text text-muted mb-4">
                                Create suspect sketches and crime scene diagrams with digital drawing tools and templates.
                            </p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge bg-success">Available</span>
                                <button class="btn btn-info btn-sm" onclick="launchTool('sketch-generator')">
                                    <i class="fas fa-palette me-1"></i>Draw
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Batch Processing -->
                <div class="col-lg-6 col-md-6">
                    <div class="card h-100 border-0 shadow-sm hover-card">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center mb-3">
                                <div class="feature-icon bg-secondary text-white rounded-circle me-3" 
                                     style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-images"></i>
                                </div>
                                <h5 class="card-title mb-0">Batch Processing</h5>
                            </div>
                            <p class="card-text text-muted mb-4">
                                Process multiple images simultaneously for resizing, watermarking, and format conversion.
                            </p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge bg-success">Available</span>
                                <button class="btn btn-secondary btn-sm" onclick="launchTool('batch-processing')">
                                    <i class="fas fa-layer-group me-1"></i>Process
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Upload Zone -->
            <div class="row mt-5">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">
                                <i class="fas fa-cloud-upload-alt me-2"></i>
                                Quick Image Upload
                            </h5>
                        </div>
                        <div class="card-body">
                            <div id="drop-zone" class="border-dashed border-primary rounded p-5 text-center">
                                <i class="fas fa-cloud-upload-alt fa-3x text-primary mb-3"></i>
                                <h5>Drag & Drop Images Here</h5>
                                <p class="text-muted mb-3">or click to browse files</p>
                                <input type="file" id="image-upload" multiple accept="image/*" class="d-none">
                                <button class="btn btn-primary" onclick="document.getElementById('image-upload').click()">
                                    <i class="fas fa-folder-open me-2"></i>Browse Files
                                </button>
                                <div class="mt-3">
                                    <small class="text-muted">Supported formats: JPG, PNG, GIF, TIFF (Max 10MB each)</small>
                                </div>
                            </div>
                            <div id="upload-progress" class="mt-3" style="display: none;">
                                <div class="progress">
                                    <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Image Tool Modal -->
<div class="modal fade" id="imageToolModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="toolModalTitle">Image Tool</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="toolModalBody" style="height: 70vh; overflow-y: auto;">
                <div class="text-center p-5">
                    <div class="spinner-border text-info mb-3" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p>Loading image tool...</p>
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

.border-dashed {
    border: 2px dashed #dee2e6 !important;
    transition: all 0.3s ease;
}

.border-dashed:hover {
    border-color: var(--bs-primary) !important;
    background-color: rgba(13, 110, 253, 0.05);
}

#drop-zone.dragover {
    border-color: var(--bs-primary) !important;
    background-color: rgba(13, 110, 253, 0.1);
}

@media (max-width: 768px) {
    .bg-info.rounded-4 .col-lg-4 {
        text-align: center !important;
        margin-top: 1rem;
    }
}
</style>

<script>
function launchTool(toolType) {
    const modal = new bootstrap.Modal(document.getElementById('imageToolModal'));
    const modalTitle = document.getElementById('toolModalTitle');
    const modalBody = document.getElementById('toolModalBody');
    
    // Set tool-specific content
    const toolConfig = {
        'photo-enhancement': {
            title: 'Photo Enhancement',
            content: `
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="mb-3">Original Image</h6>
                        <div class="border rounded p-3 text-center bg-light" style="height: 300px;">
                            <div class="d-flex align-items-center justify-content-center h-100">
                                <div>
                                    <i class="fas fa-image fa-3x text-muted mb-2"></i>
                                    <p class="text-muted">Upload image to enhance</p>
                                    <input type="file" class="form-control" accept="image/*">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6 class="mb-3">Enhanced Image</h6>
                        <div class="border rounded p-3 text-center bg-light" style="height: 300px;">
                            <div class="d-flex align-items-center justify-content-center h-100">
                                <p class="text-muted">Enhanced image will appear here</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 mt-3">
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">Brightness</label>
                                <input type="range" class="form-range" min="-100" max="100" value="0">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Contrast</label>
                                <input type="range" class="form-range" min="-100" max="100" value="0">
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <label class="form-label">Sharpness</label>
                                <input type="range" class="form-range" min="0" max="200" value="100">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Noise Reduction</label>
                                <input type="range" class="form-range" min="0" max="100" value="0">
                            </div>
                        </div>
                        <div class="text-center mt-3">
                            <button class="btn btn-primary me-2">Apply Enhancement</button>
                            <button class="btn btn-success">Download Enhanced</button>
                        </div>
                    </div>
                </div>
            `
        },
        'evidence-photography': {
            title: 'Evidence Photography',
            content: `
                <div class="row">
                    <div class="col-md-8">
                        <div class="camera-viewport border rounded bg-dark text-white text-center" style="height: 400px;">
                            <div class="d-flex align-items-center justify-content-center h-100">
                                <div>
                                    <i class="fas fa-camera fa-4x mb-3"></i>
                                    <p>Camera viewport will appear here</p>
                                    <button class="btn btn-primary">Start Camera</button>
                                </div>
                            </div>
                        </div>
                        <div class="text-center mt-3">
                            <button class="btn btn-warning btn-lg">
                                <i class="fas fa-camera me-2"></i>Capture Evidence
                            </button>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <h6>Evidence Details</h6>
                        <div class="mb-3">
                            <label class="form-label">Case Number</label>
                            <input type="text" class="form-control" placeholder="Enter case number">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Evidence Type</label>
                            <select class="form-select">
                                <option>Physical Evidence</option>
                                <option>Digital Evidence</option>
                                <option>Document</option>
                                <option>Scene Photo</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" rows="3" placeholder="Describe the evidence"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Location</label>
                            <input type="text" class="form-control" placeholder="Evidence location">
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" checked>
                            <label class="form-check-label">
                                Auto-timestamp
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" checked>
                            <label class="form-check-label">
                                GPS coordinates
                            </label>
                        </div>
                    </div>
                </div>
            `
        }
    };
    
    const config = toolConfig[toolType] || {
        title: 'Image Tool',
        content: '<div class="p-4"><p>Tool configuration not found.</p></div>'
    };
    
    modalTitle.textContent = config.title;
    modalBody.innerHTML = config.content;
    
    modal.show();
}

// Drag and drop functionality
document.addEventListener('DOMContentLoaded', function() {
    const dropZone = document.getElementById('drop-zone');
    const fileInput = document.getElementById('image-upload');
    
    // Prevent default drag behaviors
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, preventDefaults, false);
        document.body.addEventListener(eventName, preventDefaults, false);
    });
    
    // Highlight drop zone when item is dragged over it
    ['dragenter', 'dragover'].forEach(eventName => {
        dropZone.addEventListener(eventName, highlight, false);
    });
    
    ['dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, unhighlight, false);
    });
    
    // Handle dropped files
    dropZone.addEventListener('drop', handleDrop, false);
    
    // Handle file input change
    fileInput.addEventListener('change', handleFiles, false);
    
    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }
    
    function highlight(e) {
        dropZone.classList.add('dragover');
    }
    
    function unhighlight(e) {
        dropZone.classList.remove('dragover');
    }
    
    function handleDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        handleFiles({ target: { files: files } });
    }
    
    function handleFiles(e) {
        const files = Array.from(e.target.files);
        files.forEach(file => {
            if (file.type.startsWith('image/')) {
                console.log('Processing file:', file.name);
                // Here you would handle file upload
            }
        });
    }

    // Animation on load
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