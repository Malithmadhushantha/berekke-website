<?php
require_once 'config/config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    $_SESSION['login_required'] = true;
    $_SESSION['redirect_after_login'] = 'running_chart_generator.php';
    header('Location: login.php');
    exit();
}

$user = getUserInfo();
$page_title = "Running Chart Generator";
include 'includes/header.php';
?>

<div class="container py-5">
    <div class="row">
        <div class="col-12">
            <!-- Welcome Header -->
            <div class="bg-warning text-dark rounded-4 p-4 mb-5">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <h1 class="h2 mb-2">
                            <i class="fas fa-chart-line me-3"></i>
                            Welcome to Berekke Running Chart Generator
                        </h1>
                        <p class="mb-0 opacity-75">
                            Hi <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>! 
                            Create visual timelines, analytics charts, and case progression diagrams for investigations.
                        </p>
                    </div>
                    <div class="col-lg-4 text-end">
                        <i class="fas fa-chart-bar fa-4x opacity-50"></i>
                    </div>
                </div>
            </div>

            <!-- Chart Templates -->
            <div class="row g-4 mb-5">
                <div class="col-12">
                    <h3 class="mb-4">Chart Templates</h3>
                </div>
                
                <!-- Timeline Chart -->
                <div class="col-lg-6 col-md-6">
                    <div class="card h-100 border-0 shadow-sm hover-card">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center mb-3">
                                <div class="feature-icon bg-primary text-white rounded-circle me-3" 
                                     style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <h5 class="card-title mb-0">Case Timeline</h5>
                            </div>
                            <p class="card-text text-muted mb-4">
                                Create chronological timelines of case events, evidence collection, and investigation milestones.
                            </p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge bg-success">Popular</span>
                                <button class="btn btn-primary btn-sm" onclick="createChart('timeline')">
                                    <i class="fas fa-plus me-1"></i>Create
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Crime Statistics -->
                <div class="col-lg-6 col-md-6">
                    <div class="card h-100 border-0 shadow-sm hover-card">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center mb-3">
                                <div class="feature-icon bg-success text-white rounded-circle me-3" 
                                     style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-chart-pie"></i>
                                </div>
                                <h5 class="card-title mb-0">Crime Statistics</h5>
                            </div>
                            <p class="card-text text-muted mb-4">
                                Generate statistical charts for crime patterns, frequency analysis, and trend visualization.
                            </p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge bg-success">Available</span>
                                <button class="btn btn-success btn-sm" onclick="createChart('statistics')">
                                    <i class="fas fa-chart-pie me-1"></i>Generate
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Evidence Flow -->
                <div class="col-lg-6 col-md-6">
                    <div class="card h-100 border-0 shadow-sm hover-card">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center mb-3">
                                <div class="feature-icon bg-warning text-white rounded-circle me-3" 
                                     style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-project-diagram"></i>
                                </div>
                                <h5 class="card-title mb-0">Evidence Flow Chart</h5>
                            </div>
                            <p class="card-text text-muted mb-4">
                                Map evidence collection, chain of custody, and analysis workflows in visual diagrams.
                            </p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge bg-success">Available</span>
                                <button class="btn btn-warning btn-sm" onclick="createChart('evidence-flow')">
                                    <i class="fas fa-sitemap me-1"></i>Map
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Incident Reports -->
                <div class="col-lg-6 col-md-6">
                    <div class="card h-100 border-0 shadow-sm hover-card">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center mb-3">
                                <div class="feature-icon bg-danger text-white rounded-circle me-3" 
                                     style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-chart-area"></i>
                                </div>
                                <h5 class="card-title mb-0">Incident Analysis</h5>
                            </div>
                            <p class="card-text text-muted mb-4">
                                Analyze incident patterns, geographic distribution, and temporal trends with dynamic charts.
                            </p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge bg-success">Available</span>
                                <button class="btn btn-danger btn-sm" onclick="createChart('incident-analysis')">
                                    <i class="fas fa-chart-area me-1"></i>Analyze
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Chart Builder -->
            <div class="row">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">
                                <i class="fas fa-magic me-2"></i>
                                Quick Chart Builder
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-semibold">Chart Type</label>
                                    <select class="form-select" id="chartType">
                                        <option value="line">Line Chart</option>
                                        <option value="bar">Bar Chart</option>
                                        <option value="pie">Pie Chart</option>
                                        <option value="timeline">Timeline</option>
                                        <option value="area">Area Chart</option>
                                        <option value="scatter">Scatter Plot</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-semibold">Data Source</label>
                                    <select class="form-select" id="dataSource">
                                        <option value="manual">Manual Entry</option>
                                        <option value="csv">CSV Upload</option>
                                        <option value="database">Database Query</option>
                                        <option value="case-data">Case Data</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-semibold">Time Period</label>
                                    <select class="form-select" id="timePeriod">
                                        <option value="week">Last Week</option>
                                        <option value="month">Last Month</option>
                                        <option value="quarter">Last Quarter</option>
                                        <option value="year">Last Year</option>
                                        <option value="custom">Custom Range</option>
                                    </select>
                                </div>
                            </div>
                            <div class="text-center">
                                <button class="btn btn-primary btn-lg" onclick="generateQuickChart()">
                                    <i class="fas fa-chart-line me-2"></i>
                                    Generate Chart
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Charts -->
            <div class="row mt-5">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="fas fa-history me-2"></i>
                                Recent Charts
                            </h5>
                            <button class="btn btn-sm btn-outline-primary">View All</button>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <div class="chart-preview border rounded p-3">
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="fas fa-chart-line text-primary me-2"></i>
                                            <h6 class="mb-0">Case Progress Timeline</h6>
                                        </div>
                                        <small class="text-muted">Created 2 hours ago</small>
                                        <div class="mt-2">
                                            <button class="btn btn-sm btn-outline-primary me-1">Edit</button>
                                            <button class="btn btn-sm btn-outline-success">Export</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="chart-preview border rounded p-3">
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="fas fa-chart-pie text-success me-2"></i>
                                            <h6 class="mb-0">Crime Type Distribution</h6>
                                        </div>
                                        <small class="text-muted">Created 1 day ago</small>
                                        <div class="mt-2">
                                            <button class="btn btn-sm btn-outline-primary me-1">Edit</button>
                                            <button class="btn btn-sm btn-outline-success">Export</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="chart-preview border rounded p-3">
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="fas fa-chart-bar text-warning me-2"></i>
                                            <h6 class="mb-0">Monthly Incident Report</h6>
                                        </div>
                                        <small class="text-muted">Created 3 days ago</small>
                                        <div class="mt-2">
                                            <button class="btn btn-sm btn-outline-primary me-1">Edit</button>
                                            <button class="btn btn-sm btn-outline-success">Export</button>
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

<!-- Chart Builder Modal -->
<div class="modal fade" id="chartBuilderModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="chartModalTitle">Chart Builder</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="chartModalBody" style="height: 70vh; overflow-y: auto;">
                <!-- Chart builder content will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary">Save Chart</button>
                <button type="button" class="btn btn-success">Export</button>
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

.chart-preview {
    transition: all 0.3s ease;
    cursor: pointer;
}

.chart-preview:hover {
    border-color: var(--bs-primary) !important;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

@media (max-width: 768px) {
    .bg-warning.rounded-4 .col-lg-4 {
        text-align: center !important;
        margin-top: 1rem;
    }
}
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
function createChart(chartType) {
    const modal = new bootstrap.Modal(document.getElementById('chartBuilderModal'));
    const modalTitle = document.getElementById('chartModalTitle');
    const modalBody = document.getElementById('chartModalBody');
    
    const chartConfig = {
        'timeline': {
            title: 'Case Timeline Builder',
            content: generateTimelineBuilder()
        },
        'statistics': {
            title: 'Crime Statistics Chart',
            content: generateStatisticsBuilder()
        },
        'evidence-flow': {
            title: 'Evidence Flow Chart',
            content: generateFlowChartBuilder()
        },
        'incident-analysis': {
            title: 'Incident Analysis Chart',
            content: generateIncidentAnalysisBuilder()
        }
    };
    
    const config = chartConfig[chartType] || {
        title: 'Chart Builder',
        content: '<div class="p-4"><p>Chart type not found.</p></div>'
    };
    
    modalTitle.textContent = config.title;
    modalBody.innerHTML = config.content;
    
    // Initialize chart after modal is shown
    modal._element.addEventListener('shown.bs.modal', function() {
        initializeChart(chartType);
    });
    
    modal.show();
}

function generateTimelineBuilder() {
    return `
        <div class="row">
            <div class="col-md-4">
                <h6 class="mb-3">Timeline Events</h6>
                <div class="mb-3">
                    <label class="form-label">Event Title</label>
                    <input type="text" class="form-control" placeholder="Enter event title">
                </div>
                <div class="mb-3">
                    <label class="form-label">Date & Time</label>
                    <input type="datetime-local" class="form-control">
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea class="form-control" rows="3" placeholder="Event description"></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Event Type</label>
                    <select class="form-select">
                        <option>Investigation Start</option>
                        <option>Evidence Collection</option>
                        <option>Witness Interview</option>
                        <option>Arrest</option>
                        <option>Court Hearing</option>
                        <option>Case Closure</option>
                    </select>
                </div>
                <button class="btn btn-primary w-100">Add Event</button>
                
                <hr class="my-3">
                
                <h6>Added Events</h6>
                <div class="timeline-events">
                    <div class="event-item p-2 border rounded mb-2">
                        <small class="text-muted">2024-01-15 09:00</small>
                        <div class="fw-semibold">Case Opened</div>
                    </div>
                    <div class="event-item p-2 border rounded mb-2">
                        <small class="text-muted">2024-01-16 14:30</small>
                        <div class="fw-semibold">Evidence Collected</div>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <h6 class="mb-3">Timeline Preview</h6>
                <div class="border rounded p-3" style="height: 400px;">
                    <canvas id="timelineChart"></canvas>
                </div>
            </div>
        </div>
    `;
}

function generateStatisticsBuilder() {
    return `
        <div class="row">
            <div class="col-md-4">
                <h6 class="mb-3">Data Configuration</h6>
                <div class="mb-3">
                    <label class="form-label">Chart Type</label>
                    <select class="form-select">
                        <option>Bar Chart</option>
                        <option>Pie Chart</option>
                        <option>Line Chart</option>
                        <option>Doughnut Chart</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Data Categories</label>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" checked>
                        <label class="form-check-label">Theft</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" checked>
                        <label class="form-check-label">Assault</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" checked>
                        <label class="form-check-label">Drug Offenses</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox">
                        <label class="form-check-label">Traffic Violations</label>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Time Period</label>
                    <select class="form-select">
                        <option>Last 30 Days</option>
                        <option>Last 3 Months</option>
                        <option>Last 6 Months</option>
                        <option>Last Year</option>
                    </select>
                </div>
                <button class="btn btn-primary w-100">Update Chart</button>
            </div>
            <div class="col-md-8">
                <h6 class="mb-3">Statistics Chart</h6>
                <div class="border rounded p-3" style="height: 400px;">
                    <canvas id="statisticsChart"></canvas>
                </div>
            </div>
        </div>
    `;
}

function generateFlowChartBuilder() {
    return `
        <div class="text-center p-5">
            <i class="fas fa-project-diagram fa-4x text-warning mb-3"></i>
            <h5>Evidence Flow Chart Builder</h5>
            <p class="text-muted">Drag and drop interface for creating evidence flow diagrams</p>
            <button class="btn btn-warning">Launch Flow Chart Designer</button>
        </div>
    `;
}

function generateIncidentAnalysisBuilder() {
    return `
        <div class="row">
            <div class="col-md-4">
                <h6 class="mb-3">Analysis Parameters</h6>
                <div class="mb-3">
                    <label class="form-label">Geographic Area</label>
                    <select class="form-select">
                        <option>Colombo District</option>
                        <option>Gampaha District</option>
                        <option>Kalutara District</option>
                        <option>All Areas</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Incident Types</label>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" checked>
                        <label class="form-check-label">Burglary</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" checked>
                        <label class="form-check-label">Vehicle Theft</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox">
                        <label class="form-check-label">Vandalism</label>
                    </div>
                </div>
                <button class="btn btn-primary w-100">Generate Analysis</button>
            </div>
            <div class="col-md-8">
                <h6 class="mb-3">Incident Analysis</h6>
                <div class="border rounded p-3" style="height: 400px;">
                    <canvas id="incidentChart"></canvas>
                </div>
            </div>
        </div>
    `;
}

function initializeChart(chartType) {
    // Sample chart initialization based on type
    if (chartType === 'statistics') {
        const ctx = document.getElementById('statisticsChart');
        if (ctx) {
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Theft', 'Assault', 'Drug Offenses', 'Fraud'],
                    datasets: [{
                        label: 'Number of Cases',
                        data: [45, 23, 12, 8],
                        backgroundColor: [
                            'rgba(54, 162, 235, 0.8)',
                            'rgba(255, 99, 132, 0.8)',
                            'rgba(255, 206, 86, 0.8)',
                            'rgba(75, 192, 192, 0.8)'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        }
    }
}

function generateQuickChart() {
    const chartType = document.getElementById('chartType').value;
    const dataSource = document.getElementById('dataSource').value;
    const timePeriod = document.getElementById('timePeriod').value;
    
    alert(`Generating ${chartType} chart using ${dataSource} data for ${timePeriod}...`);
    // Implementation for quick chart generation
}

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