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
                            Document Processing Tools
                        </h1>
                        <p class="mb-0 opacity-75">
                            Hi <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>! 
                            Convert, merge, split, and process documents with professional-grade tools.
                        </p>
                    </div>
                    <div class="col-lg-4 text-end">
                        <a href="running_chart_generator.php" class="btn btn-outline-light">
                            <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                        </a>
                    </div>
                </div>
            </div>

            <!-- Document Tools Dashboard -->
            <div class="row g-4 mb-5">
                <!-- PDF Page Delete -->
                <div class="col-lg-6 col-md-6">
                    <div class="card h-100 border-0 shadow-sm hover-card">
                        <div class="card-body p-4 text-center">
                            <div class="feature-icon bg-danger text-white rounded-circle mx-auto mb-3" 
                                 style="width: 80px; height: 80px; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-scissors fa-2x"></i>
                            </div>
                            <h4 class="card-title mb-3">PDF Page Delete</h4>
                            <p class="card-text text-muted mb-4">
                                Remove unwanted pages from PDF documents. Select specific pages to delete and create a new PDF.
                            </p>
                            <button class="btn btn-danger btn-lg px-4" onclick="openTool('pdf-delete')">
                                <i class="fas fa-cut me-2"></i>Delete PDF Pages
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Word to PDF -->
                <div class="col-lg-6 col-md-6">
                    <div class="card h-100 border-0 shadow-sm hover-card">
                        <div class="card-body p-4 text-center">
                            <div class="feature-icon bg-primary text-white rounded-circle mx-auto mb-3" 
                                 style="width: 80px; height: 80px; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-file-word fa-2x"></i>
                            </div>
                            <h4 class="card-title mb-3">Word to PDF</h4>
                            <p class="card-text text-muted mb-4">
                                Convert Microsoft Word documents (.doc, .docx) to high-quality PDF format with preserved formatting.
                            </p>
                            <button class="btn btn-primary btn-lg px-4" onclick="openTool('word-to-pdf')">
                                <i class="fas fa-exchange-alt me-2"></i>Convert to PDF
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Merge PDFs -->
                <div class="col-lg-6 col-md-6">
                    <div class="card h-100 border-0 shadow-sm hover-card">
                        <div class="card-body p-4 text-center">
                            <div class="feature-icon bg-warning text-white rounded-circle mx-auto mb-3" 
                                 style="width: 80px; height: 80px; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-object-group fa-2x"></i>
                            </div>
                            <h4 class="card-title mb-3">Merge PDFs</h4>
                            <p class="card-text text-muted mb-4">
                                Combine multiple PDF files into a single document. Arrange pages in your preferred order.
                            </p>
                            <button class="btn btn-warning btn-lg px-4" onclick="openTool('merge-pdf')">
                                <i class="fas fa-plus-circle me-2"></i>Merge PDFs
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Image to PDF -->
                <div class="col-lg-6 col-md-6">
                    <div class="card h-100 border-0 shadow-sm hover-card">
                        <div class="card-body p-4 text-center">
                            <div class="feature-icon bg-info text-white rounded-circle mx-auto mb-3" 
                                 style="width: 80px; height: 80px; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-image fa-2x"></i>
                            </div>
                            <h4 class="card-title mb-3">Image to PDF</h4>
                            <p class="card-text text-muted mb-4">
                                Convert images (JPG, PNG, GIF) to PDF format. Combine multiple images into a single PDF document.
                            </p>
                            <button class="btn btn-info btn-lg px-4" onclick="openTool('image-to-pdf')">
                                <i class="fas fa-images me-2"></i>Convert Images
                            </button>
                        </div>
                    </div>
                </div>

                <!-- PDF to Word -->
                <div class="col-lg-6 col-md-6">
                    <div class="card h-100 border-0 shadow-sm hover-card">
                        <div class="card-body p-4 text-center">
                            <div class="feature-icon bg-secondary text-white rounded-circle mx-auto mb-3" 
                                 style="width: 80px; height: 80px; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-file-pdf fa-2x"></i>
                            </div>
                            <h4 class="card-title mb-3">PDF to Word</h4>
                            <p class="card-text text-muted mb-4">
                                Convert PDF documents to editable Word format. Extract text and maintain document structure.
                            </p>
                            <button class="btn btn-secondary btn-lg px-4" onclick="openTool('pdf-to-word')">
                                <i class="fas fa-file-export me-2"></i>Convert to Word
                            </button>
                        </div>
                    </div>
                </div>

                <!-- PDF Split -->
                <div class="col-lg-6 col-md-6">
                    <div class="card h-100 border-0 shadow-sm hover-card">
                        <div class="card-body p-4 text-center">
                            <div class="feature-icon bg-success text-white rounded-circle mx-auto mb-3" 
                                 style="width: 80px; height: 80px; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-cut fa-2x"></i>
                            </div>
                            <h4 class="card-title mb-3">Split PDF</h4>
                            <p class="card-text text-muted mb-4">
                                Split large PDF documents into separate files. Extract specific page ranges or individual pages.
                            </p>
                            <button class="btn btn-success btn-lg px-4" onclick="openTool('split-pdf')">
                                <i class="fas fa-scissors me-2"></i>Split PDF
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="row g-4 mb-5">
                <div class="col-12">
                    <h3 class="mb-4">
                        <i class="fas fa-chart-bar me-2"></i>
                        Usage Statistics
                    </h3>
                </div>
                
                <div class="col-lg-3 col-md-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center p-4">
                            <div class="d-flex align-items-center justify-content-center mb-3">
                                <div class="bg-primary text-white rounded-circle" 
                                     style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-file-pdf fa-2x"></i>
                                </div>
                            </div>
                            <h4 class="text-primary mb-1" id="pdfProcessed">0</h4>
                            <p class="text-muted mb-0">PDFs Processed</p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center p-4">
                            <div class="d-flex align-items-center justify-content-center mb-3">
                                <div class="bg-success text-white rounded-circle" 
                                     style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-exchange-alt fa-2x"></i>
                                </div>
                            </div>
                            <h4 class="text-success mb-1" id="conversions">0</h4>
                            <p class="text-muted mb-0">Conversions</p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center p-4">
                            <div class="d-flex align-items-center justify-content-center mb-3">
                                <div class="bg-warning text-white rounded-circle" 
                                     style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-object-group fa-2x"></i>
                                </div>
                            </div>
                            <h4 class="text-warning mb-1" id="merges">0</h4>
                            <p class="text-muted mb-0">Files Merged</p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center p-4">
                            <div class="d-flex align-items-center justify-content-center mb-3">
                                <div class="bg-info text-white rounded-circle" 
                                     style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-download fa-2x"></i>
                                </div>
                            </div>
                            <h4 class="text-info mb-1" id="downloads">0</h4>
                            <p class="text-muted mb-0">Downloads</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="row">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="fas fa-history me-2"></i>
                                Recent Activity
                            </h5>
                            <button class="btn btn-sm btn-outline-primary" onclick="clearHistory()">
                                Clear History
                            </button>
                        </div>
                        <div class="card-body">
                            <div id="recentActivity">
                                <div class="text-center py-4 text-muted">
                                    <i class="fas fa-clock fa-2x mb-3"></i>
                                    <p>No recent activity. Start by processing some documents!</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Document Processing Modal -->
<div class="modal fade" id="docProcessingModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="processingModalTitle">Document Tool</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="processingModalBody" style="min-height: 500px;">
                <!-- Tool content will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="processButton" style="display: none;">
                    <i class="fas fa-cogs me-2"></i>Process
                </button>
            </div>
        </div>
    </div>
</div>

<!-- PDF.js and Processing Libraries -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf-lib/1.17.1/pdf-lib.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/mammoth/1.4.2/mammoth.browser.min.js"></script>

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

.file-drop-zone {
    border: 3px dashed #dee2e6;
    border-radius: 10px;
    padding: 40px;
    text-align: center;
    transition: all 0.3s ease;
    cursor: pointer;
}

.file-drop-zone:hover,
.file-drop-zone.dragover {
    border-color: #0d6efd;
    background-color: rgba(13, 110, 253, 0.05);
}

.page-thumbnail {
    width: 150px;
    height: 200px;
    border: 2px solid #dee2e6;
    border-radius: 8px;
    margin: 10px;
    cursor: pointer;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.page-thumbnail:hover {
    border-color: #0d6efd;
    transform: scale(1.05);
}

.page-thumbnail.selected {
    border-color: #dc3545;
    background-color: rgba(220, 53, 69, 0.1);
}

.page-thumbnail .page-number {
    position: absolute;
    top: 5px;
    left: 5px;
    background: rgba(0,0,0,0.7);
    color: white;
    padding: 2px 6px;
    border-radius: 4px;
    font-size: 12px;
}

.page-thumbnail .delete-indicator {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    color: #dc3545;
    font-size: 24px;
    display: none;
}

.page-thumbnail.selected .delete-indicator {
    display: block;
}

.processing-status {
    display: none;
    text-align: center;
    padding: 20px;
}

.processing-status.active {
    display: block;
}

@media (max-width: 768px) {
    .bg-success.rounded-4 .col-lg-4 {
        text-align: center !important;
        margin-top: 1rem;
    }
    
    .feature-icon {
        width: 60px !important;
        height: 60px !important;
    }
    
    .feature-icon i {
        font-size: 1.5rem !important;
    }
    
    .page-thumbnail {
        width: 120px;
        height: 160px;
    }
}
</style>

<script>
// Global variables
let currentFiles = [];
let processedPages = [];
let currentTool = '';

// Tool configurations
const toolConfigs = {
    'pdf-delete': {
        title: 'Delete PDF Pages',
        acceptedFiles: '.pdf',
        description: 'Select pages to delete from your PDF document'
    },
    'word-to-pdf': {
        title: 'Word to PDF Converter',
        acceptedFiles: '.doc,.docx',
        description: 'Convert Word documents to PDF format'
    },
    'merge-pdf': {
        title: 'Merge PDF Files',
        acceptedFiles: '.pdf',
        description: 'Combine multiple PDF files into one document'
    },
    'image-to-pdf': {
        title: 'Image to PDF Converter',
        acceptedFiles: '.jpg,.jpeg,.png,.gif,.bmp',
        description: 'Convert images to PDF format'
    },
    'pdf-to-word': {
        title: 'PDF to Word Converter',
        acceptedFiles: '.pdf',
        description: 'Convert PDF documents to editable Word format'
    },
    'split-pdf': {
        title: 'Split PDF Document',
        acceptedFiles: '.pdf',
        description: 'Split PDF into separate files'
    }
};

function openTool(toolType) {
    currentTool = toolType;
    const config = toolConfigs[toolType];
    
    const modal = new bootstrap.Modal(document.getElementById('docProcessingModal'));
    const modalTitle = document.getElementById('processingModalTitle');
    const modalBody = document.getElementById('processingModalBody');
    const processButton = document.getElementById('processButton');
    
    modalTitle.textContent = config.title;
    processButton.style.display = 'inline-block';
    
    // Generate tool interface
    modalBody.innerHTML = generateToolInterface(toolType, config);
    
    // Initialize file handlers
    initializeFileHandlers(toolType);
    
    modal.show();
}

function generateToolInterface(toolType, config) {
    return `
        <div class="row">
            <div class="col-12 mb-4">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    ${config.description}
                </div>
            </div>
            <div class="col-12">
                <div class="file-drop-zone" id="fileDropZone">
                    <i class="fas fa-cloud-upload-alt fa-3x text-primary mb-3"></i>
                    <h5>Drop files here or click to browse</h5>
                    <p class="text-muted">Accepted formats: ${config.acceptedFiles}</p>
                    <input type="file" id="fileInput" multiple 
                           accept="${config.acceptedFiles}" 
                           class="d-none">
                    <button class="btn btn-primary" onclick="document.getElementById('fileInput').click()">
                        <i class="fas fa-folder-open me-2"></i>Choose Files
                    </button>
                </div>
            </div>
            <div class="col-12 mt-4">
                <div id="fileList" class="d-none">
                    <h6>Selected Files:</h6>
                    <div id="selectedFiles"></div>
                </div>
            </div>
            <div class="col-12 mt-4" id="processingArea" style="display: none;">
                ${generateProcessingArea(toolType)}
            </div>
            <div class="col-12 mt-4">
                <div class="processing-status" id="processingStatus">
                    <div class="spinner-border text-primary mb-3" role="status">
                        <span class="visually-hidden">Processing...</span>
                    </div>
                    <p>Processing your documents, please wait...</p>
                </div>
            </div>
        </div>
    `;
}

function generateProcessingArea(toolType) {
    switch(toolType) {
        case 'pdf-delete':
            return `
                <h6>Select pages to delete:</h6>
                <div id="pdfPages" class="d-flex flex-wrap justify-content-center"></div>
                <div class="mt-3">
                    <button class="btn btn-danger me-2" onclick="selectAllPages()">Select All</button>
                    <button class="btn btn-secondary" onclick="clearSelection()">Clear Selection</button>
                </div>
            `;
        case 'merge-pdf':
            return `
                <h6>Arrange PDF files in desired order:</h6>
                <div id="pdfOrder" class="sortable-list"></div>
                <small class="text-muted">Drag and drop to reorder files</small>
            `;
        case 'split-pdf':
            return `
                <h6>Split options:</h6>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="splitOption" id="splitByPages" value="pages" checked>
                            <label class="form-check-label" for="splitByPages">
                                Split by page range
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="splitOption" id="splitBySize" value="size">
                            <label class="form-check-label" for="splitBySize">
                                Split by file size
                            </label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div id="splitOptions">
                            <label class="form-label">Page range (e.g., 1-5, 6-10):</label>
                            <input type="text" class="form-control" id="pageRange" placeholder="1-5">
                        </div>
                    </div>
                </div>
            `;
        default:
            return '<div class="text-center"><p>Ready to process files</p></div>';
    }
}

function initializeFileHandlers(toolType) {
    const dropZone = document.getElementById('fileDropZone');
    const fileInput = document.getElementById('fileInput');
    
    // Drag and drop handlers
    dropZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropZone.classList.add('dragover');
    });
    
    dropZone.addEventListener('dragleave', () => {
        dropZone.classList.remove('dragover');
    });
    
    dropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropZone.classList.remove('dragover');
        handleFiles(e.dataTransfer.files);
    });
    
    dropZone.addEventListener('click', () => {
        fileInput.click();
    });
    
    fileInput.addEventListener('change', (e) => {
        handleFiles(e.target.files);
    });
}

async function handleFiles(files) {
    currentFiles = Array.from(files);
    displaySelectedFiles();
    
    if (currentTool === 'pdf-delete' && files.length > 0) {
        await loadPdfPages(files[0]);
    }
    
    document.getElementById('processingArea').style.display = 'block';
}

function displaySelectedFiles() {
    const fileList = document.getElementById('fileList');
    const selectedFiles = document.getElementById('selectedFiles');
    
    if (currentFiles.length > 0) {
        fileList.classList.remove('d-none');
        selectedFiles.innerHTML = currentFiles.map((file, index) => `
            <div class="d-flex align-items-center justify-content-between p-2 border rounded mb-2">
                <div class="d-flex align-items-center">
                    <i class="fas fa-file me-2"></i>
                    <span>${file.name}</span>
                    <small class="text-muted ms-2">(${formatFileSize(file.size)})</small>
                </div>
                <button class="btn btn-sm btn-outline-danger" onclick="removeFile(${index})">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `).join('');
    } else {
        fileList.classList.add('d-none');
    }
}

function removeFile(index) {
    currentFiles.splice(index, 1);
    displaySelectedFiles();
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

async function loadPdfPages(pdfFile) {
    try {
        const arrayBuffer = await pdfFile.arrayBuffer();
        const pdfDoc = await PDFLib.PDFDocument.load(arrayBuffer);
        const pageCount = pdfDoc.getPageCount();
        
        const pagesContainer = document.getElementById('pdfPages');
        pagesContainer.innerHTML = '';
        
        for (let i = 0; i < pageCount; i++) {
            const pageDiv = document.createElement('div');
            pageDiv.className = 'page-thumbnail';
            pageDiv.dataset.pageIndex = i;
            pageDiv.innerHTML = `
                <div class="page-number">${i + 1}</div>
                <div class="d-flex align-items-center justify-content-center h-100">
                    <i class="fas fa-file-pdf fa-3x text-primary"></i>
                </div>
                <div class="delete-indicator">
                    <i class="fas fa-trash"></i>
                </div>
            `;
            
            pageDiv.addEventListener('click', () => togglePageSelection(pageDiv));
            pagesContainer.appendChild(pageDiv);
        }
    } catch (error) {
        console.error('Error loading PDF:', error);
        alert('Error loading PDF file. Please try again.');
    }
}

function togglePageSelection(pageElement) {
    pageElement.classList.toggle('selected');
}

function selectAllPages() {
    const pages = document.querySelectorAll('.page-thumbnail');
    pages.forEach(page => page.classList.add('selected'));
}

function clearSelection() {
    const pages = document.querySelectorAll('.page-thumbnail');
    pages.forEach(page => page.classList.remove('selected'));
}

// Process button handler
document.getElementById('processButton').addEventListener('click', async () => {
    if (currentFiles.length === 0) {
        alert('Please select files to process.');
        return;
    }
    
    const processingStatus = document.getElementById('processingStatus');
    processingStatus.classList.add('active');
    
    try {
        let result;
        switch (currentTool) {
            case 'pdf-delete':
                result = await deletePdfPages();
                break;
            case 'word-to-pdf':
                result = await convertWordToPdf();
                break;
            case 'merge-pdf':
                result = await mergePdfFiles();
                break;
            case 'image-to-pdf':
                result = await convertImagesToPdf();
                break;
            case 'pdf-to-word':
                result = await convertPdfToWord();
                break;
            case 'split-pdf':
                result = await splitPdfFile();
                break;
        }
        
        if (result) {
            await downloadProcessedFile(result);
            updateStats();
            addToRecentActivity();
        }
    } catch (error) {
        console.error('Processing error:', error);
        alert('An error occurred while processing the file. Please try again.');
    } finally {
        processingStatus.classList.remove('active');
    }
});

async function deletePdfPages() {
    const selectedPages = Array.from(document.querySelectorAll('.page-thumbnail.selected'))
                              .map(page => parseInt(page.dataset.pageIndex));
    
    if (selectedPages.length === 0) {
        alert('Please select pages to delete.');
        return null;
    }
    
    const pdfFile = currentFiles[0];
    const arrayBuffer = await pdfFile.arrayBuffer();
    const pdfDoc = await PDFLib.PDFDocument.load(arrayBuffer);
    
    // Create new PDF with remaining pages
    const newPdf = await PDFLib.PDFDocument.create();
    const pageCount = pdfDoc.getPageCount();
    
    for (let i = 0; i < pageCount; i++) {
        if (!selectedPages.includes(i)) {
            const [copiedPage] = await newPdf.copyPages(pdfDoc, [i]);
            newPdf.addPage(copiedPage);
        }
    }
    
    const pdfBytes = await newPdf.save();
    return {
        data: pdfBytes,
        filename: `${pdfFile.name.replace('.pdf', '')}_edited.pdf`,
        type: 'application/pdf'
    };
}

async function mergePdfFiles() {
    if (currentFiles.length < 2) {
        alert('Please select at least 2 PDF files to merge.');
        return null;
    }
    
    const mergedPdf = await PDFLib.PDFDocument.create();
    
    for (const file of currentFiles) {
        const arrayBuffer = await file.arrayBuffer();
        const pdf = await PDFLib.PDFDocument.load(arrayBuffer);
        const pageIndices = Array.from({ length: pdf.getPageCount() }, (_, i) => i);
        const copiedPages = await mergedPdf.copyPages(pdf, pageIndices);
        copiedPages.forEach(page => mergedPdf.addPage(page));
    }
    
    const pdfBytes = await mergedPdf.save();
    return {
        data: pdfBytes,
        filename: 'merged_document.pdf',
        type: 'application/pdf'
    };
}

async function convertImagesToPdf() {
    const { jsPDF } = window.jspdf;
    const pdf = new jsPDF();
    
    for (let i = 0; i < currentFiles.length; i++) {
        const file = currentFiles[i];
        
        // Create image element
        const img = new Image();
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
        
        await new Promise((resolve) => {
            img.onload = () => {
                // Calculate dimensions to fit page
                const pageWidth = pdf.internal.pageSize.getWidth();
                const pageHeight = pdf.internal.pageSize.getHeight();
                
                let { width, height } = img;
                const ratio = Math.min(pageWidth / width, pageHeight / height);
                width *= ratio;
                height *= ratio;
                
                canvas.width = width;
                canvas.height = height;
                ctx.drawImage(img, 0, 0, width, height);
                
                const imgData = canvas.toDataURL('image/jpeg', 0.8);
                
                if (i > 0) pdf.addPage();
                pdf.addImage(imgData, 'JPEG', 0, 0, width, height);
                resolve();
            };
            
            img.src = URL.createObjectURL(file);
        });
    }
    
    const pdfBytes = pdf.output('arraybuffer');
    return {
        data: new Uint8Array(pdfBytes),
        filename: 'converted_images.pdf',
        type: 'application/pdf'
    };
}

async function convertWordToPdf() {
    // Note: This is a simplified conversion using mammoth.js for .docx files
    // For production use, you'd want a more robust server-side solution
    
    const file = currentFiles[0];
    if (!file.name.toLowerCase().endsWith('.docx')) {
        alert('This demo only supports .docx files. For .doc files, please use a server-side converter.');
        return null;
    }
    
    try {
        const arrayBuffer = await file.arrayBuffer();
        const result = await mammoth.convertToHtml({ arrayBuffer });
        
        // Create PDF from HTML using jsPDF
        const { jsPDF } = window.jspdf;
        const pdf = new jsPDF();
        
        // This is a basic conversion - in production you'd want better HTML to PDF conversion
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = result.value;
        tempDiv.style.width = '210mm'; // A4 width
        tempDiv.style.fontSize = '12pt';
        tempDiv.style.lineHeight = '1.4';
        
        // Simple text extraction and PDF generation
        const text = tempDiv.textContent;
        const lines = pdf.splitTextToSize(text, 180);
        pdf.text(lines, 15, 20);
        
        const pdfBytes = pdf.output('arraybuffer');
        return {
            data: new Uint8Array(pdfBytes),
            filename: `${file.name.replace(/\.(doc|docx)$/i, '')}.pdf`,
            type: 'application/pdf'
        };
    } catch (error) {
        console.error('Conversion error:', error);
        alert('Error converting Word document. Please try again.');
        return null;
    }
}

async function convertPdfToWord() {
    // Note: This is a placeholder - PDF to Word conversion requires server-side processing
    // or specialized libraries that aren't available in browser
    alert('PDF to Word conversion requires server-side processing. This feature will be implemented with backend support.');
    return null;
}

async function splitPdfFile() {
    const pdfFile = currentFiles[0];
    const pageRange = document.getElementById('pageRange').value;
    
    if (!pageRange) {
        alert('Please specify page range (e.g., 1-5).');
        return null;
    }
    
    try {
        const arrayBuffer = await pdfFile.arrayBuffer();
        const pdfDoc = await PDFLib.PDFDocument.load(arrayBuffer);
        
        // Parse page range
        const ranges = pageRange.split(',').map(range => {
            const [start, end] = range.trim().split('-').map(n => parseInt(n) - 1);
            return { start, end: end || start };
        });
        
        const newPdf = await PDFLib.PDFDocument.create();
        
        for (const range of ranges) {
            for (let i = range.start; i <= range.end; i++) {
                if (i < pdfDoc.getPageCount()) {
                    const [copiedPage] = await newPdf.copyPages(pdfDoc, [i]);
                    newPdf.addPage(copiedPage);
                }
            }
        }
        
        const pdfBytes = await newPdf.save();
        return {
            data: pdfBytes,
            filename: `${pdfFile.name.replace('.pdf', '')}_split.pdf`,
            type: 'application/pdf'
        };
    } catch (error) {
        console.error('Split error:', error);
        alert('Error splitting PDF. Please check your page range format.');
        return null;
    }
}

async function downloadProcessedFile(result) {
    const blob = new Blob([result.data], { type: result.type });
    const url = URL.createObjectURL(blob);
    
    const a = document.createElement('a');
    a.href = url;
    a.download = result.filename;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
}

function updateStats() {
    // Update usage statistics
    const stats = JSON.parse(localStorage.getItem('docToolsStats') || '{}');
    
    switch (currentTool) {
        case 'pdf-delete':
        case 'split-pdf':
            stats.pdfProcessed = (stats.pdfProcessed || 0) + 1;
            break;
        case 'word-to-pdf':
        case 'pdf-to-word':
        case 'image-to-pdf':
            stats.conversions = (stats.conversions || 0) + 1;
            break;
        case 'merge-pdf':
            stats.merges = (stats.merges || 0) + 1;
            break;
    }
    
    stats.downloads = (stats.downloads || 0) + 1;
    
    localStorage.setItem('docToolsStats', JSON.stringify(stats));
    displayStats();
}

function displayStats() {
    const stats = JSON.parse(localStorage.getItem('docToolsStats') || '{}');
    
    document.getElementById('pdfProcessed').textContent = stats.pdfProcessed || 0;
    document.getElementById('conversions').textContent = stats.conversions || 0;
    document.getElementById('merges').textContent = stats.merges || 0;
    document.getElementById('downloads').textContent = stats.downloads || 0;
}

function addToRecentActivity() {
    const activities = JSON.parse(localStorage.getItem('recentActivities') || '[]');
    const config = toolConfigs[currentTool];
    
    activities.unshift({
        tool: config.title,
        time: new Date().toLocaleString(),
        files: currentFiles.length
    });
    
    // Keep only last 10 activities
    activities.splice(10);
    
    localStorage.setItem('recentActivities', JSON.stringify(activities));
    displayRecentActivity();
}

function displayRecentActivity() {
    const activities = JSON.parse(localStorage.getItem('recentActivities') || '[]');
    const container = document.getElementById('recentActivity');
    
    if (activities.length === 0) {
        container.innerHTML = `
            <div class="text-center py-4 text-muted">
                <i class="fas fa-clock fa-2x mb-3"></i>
                <p>No recent activity. Start by processing some documents!</p>
            </div>
        `;
        return;
    }
    
    container.innerHTML = activities.map(activity => `
        <div class="d-flex align-items-center justify-content-between p-3 border-bottom">
            <div>
                <h6 class="mb-1">${activity.tool}</h6>
                <small class="text-muted">${activity.files} file(s) processed</small>
            </div>
            <small class="text-muted">${activity.time}</small>
        </div>
    `).join('');
}

function clearHistory() {
    localStorage.removeItem('recentActivities');
    localStorage.removeItem('docToolsStats');
    displayRecentActivity();
    displayStats();
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    displayStats();
    displayRecentActivity();
    
    // Animation on load
    const cards = document.querySelectorAll('.hover-card');
    cards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        
        setTimeout(() => {
            card.style.transition = 'all 0.6s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 150);
    });
});
</script>

<?php include 'includes/footer.php'; ?>