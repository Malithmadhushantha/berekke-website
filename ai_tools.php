<?php
require_once 'config/config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    $_SESSION['login_required'] = true;
    $_SESSION['redirect_after_login'] = 'ai_tools.php';
    header('Location: login.php');
    exit();
}

$user = getUserInfo();
$page_title = "AI Tools";
include 'includes/header.php';
?>

<div class="container py-5">
    <div class="row">
        <div class="col-12">
            <!-- Welcome Header -->
            <div class="bg-primary text-white rounded-4 p-4 mb-5">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <h1 class="h2 mb-2">
                            <i class="fas fa-robot me-3"></i>
                            Welcome to Berekke AI Tools
                        </h1>
                        <p class="mb-0 opacity-75">
                            Hi <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>! 
                            Access powerful AI-driven tools designed specifically for law enforcement professionals.
                        </p>
                    </div>
                    <div class="col-lg-4 text-end">
                        <i class="fas fa-brain fa-4x opacity-50"></i>
                    </div>
                </div>
            </div>

            <!-- AI Tools Grid -->
            <div class="row g-4">
                <!-- AI Legal Assistant - NEW FEATURED TOOL -->
                <div class="col-lg-12 mb-4">
                    <div class="card border-0 shadow-lg featured-ai-tool">
                        <div class="card-body p-4">
                            <div class="row align-items-center">
                                <div class="col-lg-8">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="feature-icon bg-gradient-primary text-white rounded-circle me-3" 
                                             style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
                                            <i class="fas fa-brain fa-2x"></i>
                                        </div>
                                        <div>
                                            <h4 class="card-title mb-1">AI Legal Assistant</h4>
                                            <span class="badge bg-warning text-dark me-2">NEW</span>
                                            <span class="badge bg-success">Featured</span>
                                        </div>
                                    </div>
                                    <p class="card-text text-muted mb-3">
                                        Chat with an AI assistant trained on Sri Lankan law. Get instant answers about Penal Code, 
                                        Criminal Procedure Code, and Evidence Ordinance. Ask questions in English or Sinhala!
                                    </p>
                                    <div class="d-flex gap-2 mb-2">
                                        <span class="badge bg-primary">Penal Code Database</span>
                                        <span class="badge bg-success">Criminal Procedure</span>
                                        <span class="badge bg-warning text-dark">Evidence Ordinance</span>
                                    </div>
                                </div>
                                <div class="col-lg-4 text-center">
                                    <a href="ai_legal_assistant.php" class="btn btn-primary btn-lg px-4">
                                        <i class="fas fa-comments me-2"></i>Start Chat
                                    </a>
                                    <p class="text-muted small mt-2 mb-0">Powered by Google Gemini AI</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Document Analysis AI -->
                <div class="col-lg-6 col-md-6">
                    <div class="card h-100 border-0 shadow-sm hover-card">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center mb-3">
                                <div class="feature-icon bg-primary text-white rounded-circle me-3" 
                                     style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-file-search"></i>
                                </div>
                                <h5 class="card-title mb-0">Document Analysis AI</h5>
                            </div>
                            <p class="card-text text-muted mb-4">
                                Analyze legal documents, extract key information, and identify important clauses using advanced AI technology.
                            </p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge bg-success">Available</span>
                                <button class="btn btn-primary btn-sm" onclick="launchTool('document-analysis')">
                                    <i class="fas fa-play me-1"></i>Launch
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Case Summary Generator -->
                <div class="col-lg-6 col-md-6">
                    <div class="card h-100 border-0 shadow-sm hover-card">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center mb-3">
                                <div class="feature-icon bg-success text-white rounded-circle me-3" 
                                     style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-clipboard-list"></i>
                                </div>
                                <h5 class="card-title mb-0">Case Summary Generator</h5>
                            </div>
                            <p class="card-text text-muted mb-4">
                                Generate comprehensive case summaries from investigation notes and evidence using AI-powered analysis.
                            </p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge bg-success">Available</span>
                                <button class="btn btn-success btn-sm" onclick="launchTool('case-summary')">
                                    <i class="fas fa-play me-1"></i>Launch
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Legal Research Assistant -->
                <div class="col-lg-6 col-md-6">
                    <div class="card h-100 border-0 shadow-sm hover-card">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center mb-3">
                                <div class="feature-icon bg-warning text-white rounded-circle me-3" 
                                     style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-gavel"></i>
                                </div>
                                <h5 class="card-title mb-0">Legal Research Assistant</h5>
                            </div>
                            <p class="card-text text-muted mb-4">
                                Get instant answers to legal questions and find relevant sections from Sri Lankan law databases.
                            </p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge bg-success">Available</span>
                                <button class="btn btn-warning btn-sm" onclick="launchTool('legal-research')">
                                    <i class="fas fa-play me-1"></i>Launch
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Evidence Analyzer -->
                <div class="col-lg-6 col-md-6">
                    <div class="card h-100 border-0 shadow-sm hover-card">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center mb-3">
                                <div class="feature-icon bg-info text-white rounded-circle me-3" 
                                     style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-microscope"></i>
                                </div>
                                <h5 class="card-title mb-0">Evidence Analyzer</h5>
                            </div>
                            <p class="card-text text-muted mb-4">
                                Analyze digital evidence, photos, and documents to extract meaningful insights for investigations.
                            </p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge bg-warning">Coming Soon</span>
                                <button class="btn btn-info btn-sm" disabled>
                                    <i class="fas fa-clock me-1"></i>Soon
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Report Generator -->
                <div class="col-lg-6 col-md-6">
                    <div class="card h-100 border-0 shadow-sm hover-card">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center mb-3">
                                <div class="feature-icon bg-danger text-white rounded-circle me-3" 
                                     style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-file-pdf"></i>
                                </div>
                                <h5 class="card-title mb-0">Report Generator</h5>
                            </div>
                            <p class="card-text text-muted mb-4">
                                Generate professional police reports and documentation with AI assistance and proper formatting.
                            </p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge bg-success">Available</span>
                                <button class="btn btn-danger btn-sm" onclick="launchTool('report-generator')">
                                    <i class="fas fa-play me-1"></i>Launch
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Language Translator -->
                <div class="col-lg-6 col-md-6">
                    <div class="card h-100 border-0 shadow-sm hover-card">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center mb-3">
                                <div class="feature-icon bg-secondary text-white rounded-circle me-3" 
                                     style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-language"></i>
                                </div>
                                <h5 class="card-title mb-0">Language Translator</h5>
                            </div>
                            <p class="card-text text-muted mb-4">
                                Translate documents and communications between Sinhala, Tamil, and English for better understanding.
                            </p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge bg-success">Available</span>
                                <button class="btn btn-secondary btn-sm" onclick="launchTool('translator')">
                                    <i class="fas fa-play me-1"></i>Launch
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Usage Statistics -->
            <div class="row mt-5">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">
                                <i class="fas fa-chart-bar me-2"></i>
                                Your AI Tools Usage
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-md-3 col-6 mb-3">
                                    <div class="p-3">
                                        <h4 class="text-primary mb-1">24</h4>
                                        <small class="text-muted">Documents Analyzed</small>
                                    </div>
                                </div>
                                <div class="col-md-3 col-6 mb-3">
                                    <div class="p-3">
                                        <h4 class="text-success mb-1">12</h4>
                                        <small class="text-muted">Reports Generated</small>
                                    </div>
                                </div>
                                <div class="col-md-3 col-6 mb-3">
                                    <div class="p-3">
                                        <h4 class="text-warning mb-1">8</h4>
                                        <small class="text-muted">Legal Queries</small>
                                    </div>
                                </div>
                                <div class="col-md-3 col-6 mb-3">
                                    <div class="p-3">
                                        <h4 class="text-info mb-1">156</h4>
                                        <small class="text-muted">Total Minutes Saved</small>
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

<!-- AI Tool Modal -->
<div class="modal fade" id="aiToolModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="toolModalTitle">AI Tool</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="toolModalBody">
                <div class="text-center p-5">
                    <div class="spinner-border text-primary mb-3" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p>Loading AI tool...</p>
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
    .bg-primary.rounded-4 .col-lg-4 {
        text-align: center !important;
        margin-top: 1rem;
    }
}
</style>

<script>
function launchTool(toolType) {
    const modal = new bootstrap.Modal(document.getElementById('aiToolModal'));
    const modalTitle = document.getElementById('toolModalTitle');
    const modalBody = document.getElementById('toolModalBody');
    
    // Set tool-specific content
    const toolConfig = {
        'document-analysis': {
            title: 'Document Analysis AI',
            content: `
                <div class="p-4">
                    <h6 class="mb-3">Upload Document for Analysis</h6>
                    <div class="mb-3">
                        <input type="file" class="form-control" accept=".pdf,.doc,.docx,.txt" id="documentFile">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Analysis Type</label>
                        <select class="form-select">
                            <option>Legal Content Analysis</option>
                            <option>Key Information Extraction</option>
                            <option>Summary Generation</option>
                            <option>Entity Recognition</option>
                        </select>
                    </div>
                    <button class="btn btn-primary w-100">
                        <i class="fas fa-cogs me-2"></i>Analyze Document
                    </button>
                </div>
            `
        },
        'case-summary': {
            title: 'Case Summary Generator',
            content: `
                <div class="p-4">
                    <h6 class="mb-3">Generate Case Summary</h6>
                    <div class="mb-3">
                        <label class="form-label">Case Details</label>
                        <textarea class="form-control" rows="4" placeholder="Enter case details, evidence, and investigation notes..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Summary Type</label>
                        <select class="form-select">
                            <option>Comprehensive Summary</option>
                            <option>Executive Summary</option>
                            <option>Evidence Summary</option>
                            <option>Timeline Summary</option>
                        </select>
                    </div>
                    <button class="btn btn-success w-100">
                        <i class="fas fa-magic me-2"></i>Generate Summary
                    </button>
                </div>
            `
        },
        'legal-research': {
            title: 'Legal Research Assistant',
            content: `
                <div class="p-4">
                    <h6 class="mb-3">Ask Legal Question</h6>
                    <div class="mb-3">
                        <label class="form-label">Your Question</label>
                        <textarea class="form-control" rows="3" placeholder="Ask any question about Sri Lankan law..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Search Scope</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="penal_code" checked>
                            <label class="form-check-label">Penal Code</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="criminal_procedure" checked>
                            <label class="form-check-label">Criminal Procedure Code</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="evidence_ordinance" checked>
                            <label class="form-check-label">Evidence Ordinance</label>
                        </div>
                    </div>
                    <button class="btn btn-warning w-100">
                        <i class="fas fa-search me-2"></i>Research
                    </button>
                </div>
            `
        },
        'report-generator': {
            title: 'Report Generator',
            content: `
                <div class="p-4">
                    <h6 class="mb-3">Generate Police Report</h6>
                    <div class="mb-3">
                        <label class="form-label">Report Type</label>
                        <select class="form-select">
                            <option>Incident Report</option>
                            <option>Investigation Report</option>
                            <option>Evidence Report</option>
                            <option>Case Closure Report</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Key Information</label>
                        <textarea class="form-control" rows="4" placeholder="Enter key information for the report..."></textarea>
                    </div>
                    <button class="btn btn-danger w-100">
                        <i class="fas fa-file-pdf me-2"></i>Generate Report
                    </button>
                </div>
            `
        },
        'translator': {
            title: 'Language Translator',
            content: `
                <div class="p-4">
                    <h6 class="mb-3">Translate Text</h6>
                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="form-label">From</label>
                            <select class="form-select">
                                <option>Sinhala</option>
                                <option>Tamil</option>
                                <option>English</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label">To</label>
                            <select class="form-select">
                                <option>English</option>
                                <option>Sinhala</option>
                                <option>Tamil</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Text to Translate</label>
                        <textarea class="form-control" rows="4" placeholder="Enter text to translate..."></textarea>
                    </div>
                    <button class="btn btn-secondary w-100">
                        <i class="fas fa-language me-2"></i>Translate
                    </button>
                </div>
            `
        }
    };
    
    const config = toolConfig[toolType] || {
        title: 'AI Tool',
        content: '<div class="p-4"><p>Tool configuration not found.</p></div>'
    };
    
    modalTitle.textContent = config.title;
    modalBody.innerHTML = config.content;
    
    modal.show();
}

// Show welcome animation
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