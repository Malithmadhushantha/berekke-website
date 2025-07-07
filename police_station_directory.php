<?php
require_once 'config/config.php';

$page_title = "Police Station Directory";

// Handle AJAX requests
if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    
    if ($_GET['action'] === 'search') {
        $search_term = cleanInput($_GET['term']);
        $stmt = $pdo->prepare("
            SELECT * FROM police_stations_details 
            WHERE province LIKE ? 
               OR police_division LIKE ? 
               OR police_station LIKE ? 
               OR address LIKE ?
            ORDER BY province, police_division, police_station
            LIMIT 50
        ");
        $search_param = '%' . $search_term . '%';
        $stmt->execute([$search_param, $search_param, $search_param, $search_param]);
        $results = $stmt->fetchAll();
        echo json_encode($results);
        exit();
    }
    
    if ($_GET['action'] === 'get_divisions') {
        $province = cleanInput($_GET['province']);
        $stmt = $pdo->prepare("
            SELECT DISTINCT police_division, COUNT(*) as station_count 
            FROM police_stations_details 
            WHERE province = ? 
            GROUP BY police_division 
            ORDER BY police_division
        ");
        $stmt->execute([$province]);
        $divisions = $stmt->fetchAll();
        echo json_encode($divisions);
        exit();
    }
    
    if ($_GET['action'] === 'get_stations') {
        $division = cleanInput($_GET['division']);
        $stmt = $pdo->prepare("
            SELECT * FROM police_stations_details 
            WHERE police_division = ? 
            ORDER BY police_station
        ");
        $stmt->execute([$division]);
        $stations = $stmt->fetchAll();
        echo json_encode($stations);
        exit();
    }
    
    if ($_GET['action'] === 'get_station_details') {
        $station_id = intval($_GET['id']);
        $stmt = $pdo->prepare("SELECT * FROM police_stations_details WHERE id = ?");
        $stmt->execute([$station_id]);
        $station = $stmt->fetch();
        echo json_encode($station);
        exit();
    }
}

// Get provinces with station counts
$stmt = $pdo->query("
    SELECT province, COUNT(*) as station_count, COUNT(DISTINCT police_division) as division_count 
    FROM police_stations_details 
    GROUP BY province 
    ORDER BY province
");
$provinces = $stmt->fetchAll();

// Get total statistics
$stmt = $pdo->query("
    SELECT 
        COUNT(*) as total_stations,
        COUNT(DISTINCT province) as total_provinces,
        COUNT(DISTINCT police_division) as total_divisions
    FROM police_stations_details
");
$stats = $stmt->fetch();

include 'includes/header.php';
?>

<div class="container py-5">
    <!-- Header Section -->
    <div class="text-center mb-5 animate__animated animate__fadeInDown">
        <div class="directory-header-icon mb-4">
            <i class="fas fa-building-shield fa-4x text-primary"></i>
        </div>
        <h1 class="display-4 fw-bold text-primary mb-3">Police Station Directory</h1>
        <p class="lead text-muted">
            Complete directory of police stations across all provinces in Sri Lanka
        </p>
        <div class="divider mx-auto my-4"></div>
        
        <!-- Statistics -->
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="stats-container">
                    <div class="stat-item">
                        <div class="stat-number"><?php echo $stats['total_provinces']; ?></div>
                        <div class="stat-label">Provinces</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number"><?php echo $stats['total_divisions']; ?></div>
                        <div class="stat-label">Police Divisions</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number"><?php echo $stats['total_stations']; ?></div>
                        <div class="stat-label">Police Stations</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Search Section -->
    <div class="row justify-content-center mb-5">
        <div class="col-lg-8">
            <div class="search-container animate__animated animate__fadeInUp" data-delay="0.2s">
                <div class="search-box">
                    <div class="search-input-group">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" id="searchInput" class="search-input" 
                               placeholder="Search by province, division, or police station name...">
                        <button class="search-clear" id="clearSearch" style="display: none;">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="search-suggestions" id="searchSuggestions" style="display: none;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading Indicator -->
    <div class="loading-container" id="loadingIndicator" style="display: none;">
        <div class="loading-spinner">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2 text-muted">Loading police stations...</p>
        </div>
    </div>

    <!-- Search Results -->
    <div class="search-results" id="searchResults" style="display: none;">
        <div class="results-header">
            <h4><i class="fas fa-search-location me-2"></i>Search Results</h4>
            <button class="btn btn-outline-secondary btn-sm" id="backToDirectory">
                <i class="fas fa-arrow-left me-1"></i>Back to Directory
            </button>
        </div>
        <div class="results-grid" id="resultsGrid"></div>
    </div>

    <!-- Province Directory -->
    <div class="directory-container" id="directoryContainer">
        <div class="row g-4">
            <?php foreach ($provinces as $province): ?>
            <div class="col-lg-6 col-md-6 animate__animated animate__fadeInUp" data-delay="<?php echo 0.1 + array_search($province, $provinces) * 0.1; ?>s">
                <div class="province-card" data-province="<?php echo htmlspecialchars($province['province']); ?>">
                    <div class="province-header">
                        <div class="province-info">
                            <h4 class="province-name">
                                <i class="fas fa-map-marker-alt me-2"></i>
                                <?php echo htmlspecialchars($province['province']); ?>
                            </h4>
                            <div class="province-stats">
                                <span class="stat-badge divisions">
                                    <i class="fas fa-building me-1"></i>
                                    <?php echo $province['division_count']; ?> Divisions
                                </span>
                                <span class="stat-badge stations">
                                    <i class="fas fa-shield-alt me-1"></i>
                                    <?php echo $province['station_count']; ?> Stations
                                </span>
                            </div>
                        </div>
                        <div class="province-toggle">
                            <i class="fas fa-chevron-down"></i>
                        </div>
                    </div>
                    
                    <div class="divisions-container" style="display: none;">
                        <div class="loading-divisions">
                            <div class="spinner-border spinner-border-sm text-primary me-2"></div>
                            Loading divisions...
                        </div>
                        <div class="divisions-list"></div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Station Details Modal -->
<div class="modal fade" id="stationModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-building-shield me-2"></i>
                    Police Station Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="stationModalBody">
                <!-- Station details will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="getDirections">
                    <i class="fas fa-directions me-1"></i>Get Directions
                </button>
            </div>
        </div>
    </div>
</div>

<style>
/* Header Styles */
.directory-header-icon {
    position: relative;
}

.directory-header-icon::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 120px;
    height: 120px;
    background: linear-gradient(45deg, var(--bs-primary), var(--bs-info));
    border-radius: 50%;
    opacity: 0.1;
    z-index: -1;
}

.divider {
    width: 100px;
    height: 3px;
    background: linear-gradient(90deg, var(--bs-primary), var(--bs-info));
    border-radius: 2px;
}

/* Statistics */
.stats-container {
    display: flex;
    justify-content: center;
    gap: 3rem;
    background: var(--bs-body-bg);
    border: 2px solid var(--bs-primary);
    border-radius: 20px;
    padding: 2rem;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}

.stat-item {
    text-align: center;
}

.stat-number {
    font-size: 2.5rem;
    font-weight: 700;
    color: var(--bs-primary);
    display: block;
}

.stat-label {
    color: var(--bs-text-muted);
    font-weight: 500;
    font-size: 0.9rem;
}

/* Search Container */
.search-container {
    position: relative;
}

.search-box {
    background: var(--bs-body-bg);
    border: 2px solid var(--bs-primary);
    border-radius: 25px;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
}

.search-box:focus-within {
    box-shadow: 0 15px 40px rgba(13, 110, 253, 0.2);
    transform: translateY(-2px);
}

.search-input-group {
    display: flex;
    align-items: center;
    padding: 0.75rem 1.5rem;
}

.search-icon {
    color: var(--bs-primary);
    font-size: 1.2rem;
    margin-right: 1rem;
}

.search-input {
    flex: 1;
    border: none;
    outline: none;
    font-size: 1.1rem;
    background: transparent;
    color: var(--bs-body-color);
}

.search-input::placeholder {
    color: var(--bs-text-muted);
}

.search-clear {
    background: none;
    border: none;
    color: var(--bs-text-muted);
    font-size: 1.1rem;
    cursor: pointer;
    padding: 0.5rem;
    border-radius: 50%;
    transition: all 0.3s ease;
}

.search-clear:hover {
    background: var(--bs-danger);
    color: white;
}

/* Search Suggestions */
.search-suggestions {
    border-top: 1px solid var(--bs-border-color);
    max-height: 300px;
    overflow-y: auto;
}

.suggestion-item {
    padding: 1rem 1.5rem;
    border-bottom: 1px solid var(--bs-border-color);
    cursor: pointer;
    transition: all 0.3s ease;
}

.suggestion-item:hover {
    background: var(--bs-primary);
    color: white;
}

.suggestion-item:last-child {
    border-bottom: none;
}

/* Province Cards */
.province-card {
    background: var(--bs-body-bg);
    border: 2px solid var(--bs-border-color);
    border-radius: 20px;
    overflow: hidden;
    transition: all 0.3s ease;
    position: relative;
}

.province-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 15px 40px rgba(0,0,0,0.15);
    border-color: var(--bs-primary);
}

.province-card.expanded {
    border-color: var(--bs-primary);
    box-shadow: 0 10px 30px rgba(13, 110, 253, 0.2);
}

.province-header {
    display: flex;
    justify-content: between;
    align-items: center;
    padding: 1.5rem;
    cursor: pointer;
    background: linear-gradient(135deg, rgba(13, 110, 253, 0.05), rgba(13, 110, 253, 0.02));
}

.province-info {
    flex: 1;
}

.province-name {
    color: var(--bs-primary);
    margin-bottom: 0.75rem;
    font-weight: 600;
    font-size: 1.3rem;
}

.province-stats {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}

.stat-badge {
    background: var(--bs-primary);
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 15px;
    font-size: 0.85rem;
    font-weight: 500;
}

.stat-badge.divisions {
    background: var(--bs-success);
}

.stat-badge.stations {
    background: var(--bs-info);
}

.province-toggle {
    color: var(--bs-primary);
    font-size: 1.2rem;
    transition: transform 0.3s ease;
}

.province-card.expanded .province-toggle {
    transform: rotate(180deg);
}

/* Divisions Container */
.divisions-container {
    border-top: 1px solid var(--bs-border-color);
    background: rgba(0,0,0,0.02);
}

.loading-divisions {
    padding: 1rem 1.5rem;
    color: var(--bs-text-muted);
    text-align: center;
}

.divisions-list {
    padding: 0;
}

.division-item {
    border-bottom: 1px solid var(--bs-border-color);
    transition: all 0.3s ease;
}

.division-item:last-child {
    border-bottom: none;
}

.division-header {
    display: flex;
    justify-content: between;
    align-items: center;
    padding: 1rem 1.5rem;
    cursor: pointer;
    background: transparent;
    transition: all 0.3s ease;
}

.division-header:hover {
    background: rgba(13, 110, 253, 0.1);
}

.division-info h5 {
    color: var(--bs-success);
    margin-bottom: 0.25rem;
    font-weight: 600;
}

.division-info small {
    color: var(--bs-text-muted);
}

.division-toggle {
    color: var(--bs-success);
    transition: transform 0.3s ease;
}

.division-item.expanded .division-toggle {
    transform: rotate(180deg);
}

/* Stations List */
.stations-list {
    background: rgba(0,0,0,0.02);
    padding: 0;
}

.station-item {
    display: flex;
    justify-content: between;
    align-items: center;
    padding: 0.75rem 2rem;
    border-bottom: 1px solid rgba(0,0,0,0.1);
    cursor: pointer;
    transition: all 0.3s ease;
}

.station-item:hover {
    background: var(--bs-primary);
    color: white;
    transform: translateX(10px);
}

.station-item:last-child {
    border-bottom: none;
}

.station-name {
    font-weight: 500;
    display: flex;
    align-items: center;
}

.station-name i {
    margin-right: 0.5rem;
    opacity: 0.7;
}

.view-details {
    font-size: 0.85rem;
    opacity: 0.8;
}

/* Search Results */
.search-results {
    margin-bottom: 2rem;
}

.results-header {
    display: flex;
    justify-content: between;
    align-items: center;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid var(--bs-primary);
}

.results-header h4 {
    color: var(--bs-primary);
    margin: 0;
}

.results-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 1.5rem;
}

.result-card {
    background: var(--bs-body-bg);
    border: 2px solid var(--bs-border-color);
    border-radius: 15px;
    padding: 1.5rem;
    cursor: pointer;
    transition: all 0.3s ease;
}

.result-card:hover {
    transform: translateY(-5px);
    border-color: var(--bs-primary);
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
}

.result-station {
    color: var(--bs-primary);
    font-weight: 600;
    font-size: 1.1rem;
    margin-bottom: 0.5rem;
}

.result-hierarchy {
    color: var(--bs-text-muted);
    font-size: 0.9rem;
    line-height: 1.4;
}

.result-contacts {
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid var(--bs-border-color);
}

.contact-item {
    display: flex;
    align-items: center;
    margin-bottom: 0.25rem;
    font-size: 0.85rem;
}

.contact-item i {
    margin-right: 0.5rem;
    width: 15px;
    color: var(--bs-success);
}

/* Loading States */
.loading-container {
    text-align: center;
    padding: 3rem 0;
}

/* Modal Styles */
.modal-content {
    border-radius: 15px;
    overflow: hidden;
}

.station-details {
    padding: 1rem 0;
}

.detail-section {
    margin-bottom: 1.5rem;
}

.detail-section h6 {
    color: var(--bs-primary);
    font-weight: 600;
    margin-bottom: 0.75rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid rgba(13, 110, 253, 0.2);
}

.detail-item {
    display: flex;
    align-items: center;
    margin-bottom: 0.75rem;
    padding: 0.5rem;
    background: rgba(0,0,0,0.02);
    border-radius: 8px;
}

.detail-item i {
    color: var(--bs-primary);
    width: 25px;
    margin-right: 0.75rem;
}

.detail-label {
    font-weight: 500;
    color: var(--bs-text-muted);
    min-width: 120px;
}

.detail-value {
    color: var(--bs-body-color);
    font-weight: 500;
}

.contact-link {
    color: var(--bs-success);
    text-decoration: none;
    font-weight: 500;
}

.contact-link:hover {
    text-decoration: underline;
}

/* Dark Mode Adjustments */
[data-bs-theme="dark"] .province-card,
[data-bs-theme="dark"] .result-card {
    background: rgba(255,255,255,0.05);
    border-color: rgba(255,255,255,0.1);
}

[data-bs-theme="dark"] .search-box {
    background: rgba(255,255,255,0.05);
    border-color: var(--bs-primary);
}

[data-bs-theme="dark"] .stats-container {
    background: rgba(255,255,255,0.05);
    border-color: var(--bs-primary);
}

[data-bs-theme="dark"] .divisions-container,
[data-bs-theme="dark"] .stations-list {
    background: rgba(255,255,255,0.02);
}

[data-bs-theme="dark"] .detail-item {
    background: rgba(255,255,255,0.05);
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .stats-container {
        flex-direction: column;
        gap: 1.5rem;
        padding: 1.5rem;
    }
    
    .stat-number {
        font-size: 2rem;
    }
    
    .province-header {
        padding: 1rem;
    }
    
    .province-name {
        font-size: 1.1rem;
    }
    
    .province-stats {
        gap: 0.5rem;
    }
    
    .results-grid {
        grid-template-columns: 1fr;
    }
    
    .results-header {
        flex-direction: column;
        gap: 1rem;
        text-align: center;
    }
    
    .station-item {
        padding: 0.75rem 1rem;
    }
    
    .division-header {
        padding: 0.75rem 1rem;
    }
}

/* Animation Classes */
.animate__fadeInUp {
    opacity: 0;
    transform: translateY(30px);
    transition: all 0.6s ease;
}

.animate__fadeInUp.animated {
    opacity: 1;
    transform: translateY(0);
}
</style>

<script>
class PoliceStationDirectory {
    constructor() {
        this.currentStationData = null;
        this.searchTimeout = null;
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.setupAnimations();
    }

    setupEventListeners() {
        // Search functionality
        const searchInput = document.getElementById('searchInput');
        const clearSearch = document.getElementById('clearSearch');
        const backToDirectory = document.getElementById('backToDirectory');

        searchInput.addEventListener('input', (e) => this.handleSearch(e.target.value));
        clearSearch.addEventListener('click', () => this.clearSearch());
        backToDirectory.addEventListener('click', () => this.showDirectory());

        // Province expansion
        document.addEventListener('click', (e) => {
            if (e.target.closest('.province-header')) {
                const provinceCard = e.target.closest('.province-card');
                this.toggleProvince(provinceCard);
            }
            
            if (e.target.closest('.division-header')) {
                const divisionItem = e.target.closest('.division-item');
                this.toggleDivision(divisionItem);
            }
            
            if (e.target.closest('.station-item') || e.target.closest('.result-card')) {
                const stationElement = e.target.closest('.station-item') || e.target.closest('.result-card');
                const stationId = stationElement.dataset.stationId;
                if (stationId) {
                    this.showStationDetails(stationId);
                }
            }
        });

        // Get directions button
        document.getElementById('getDirections').addEventListener('click', () => {
            if (this.currentStationData) {
                this.getDirections();
            }
        });
    }

    setupAnimations() {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const delay = entry.target.dataset.delay || 0;
                    setTimeout(() => {
                        entry.target.classList.add('animated');
                    }, delay * 1000);
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1 });

        document.querySelectorAll('.animate__fadeInUp').forEach(el => {
            observer.observe(el);
        });
    }

    async handleSearch(term) {
        const searchInput = document.getElementById('searchInput');
        const clearSearch = document.getElementById('clearSearch');
        const searchSuggestions = document.getElementById('searchSuggestions');

        clearTimeout(this.searchTimeout);
        
        if (term.length === 0) {
            clearSearch.style.display = 'none';
            searchSuggestions.style.display = 'none';
            this.showDirectory();
            return;
        }

        clearSearch.style.display = 'block';

        if (term.length < 2) return;

        this.searchTimeout = setTimeout(async () => {
            this.showLoading();
            
            try {
                const response = await fetch(`?action=search&term=${encodeURIComponent(term)}`);
                const results = await response.json();
                this.displaySearchResults(results);
            } catch (error) {
                console.error('Search error:', error);
                this.hideLoading();
            }
        }, 300);
    }

    clearSearch() {
        document.getElementById('searchInput').value = '';
        document.getElementById('clearSearch').style.display = 'none';
        document.getElementById('searchSuggestions').style.display = 'none';
        this.showDirectory();
    }

    async toggleProvince(provinceCard) {
        const isExpanded = provinceCard.classList.contains('expanded');
        const divisionsContainer = provinceCard.querySelector('.divisions-container');
        const divisionsList = provinceCard.querySelector('.divisions-list');
        
        if (isExpanded) {
            provinceCard.classList.remove('expanded');
            divisionsContainer.style.display = 'none';
            return;
        }

        const provinceName = provinceCard.dataset.province;
        provinceCard.classList.add('expanded');
        divisionsContainer.style.display = 'block';
        
        try {
            const response = await fetch(`?action=get_divisions&province=${encodeURIComponent(provinceName)}`);
            const divisions = await response.json();
            
            divisionsList.innerHTML = divisions.map(division => `
                <div class="division-item" data-division="${division.police_division}">
                    <div class="division-header">
                        <div class="division-info">
                            <h5><i class="fas fa-building me-2"></i>${division.police_division}</h5>
                            <small><i class="fas fa-shield-alt me-1"></i>${division.station_count} stations</small>
                        </div>
                        <div class="division-toggle">
                            <i class="fas fa-chevron-down"></i>
                        </div>
                    </div>
                    <div class="stations-container" style="display: none;">
                        <div class="loading-stations">
                            <div class="text-center p-3">
                                <div class="spinner-border spinner-border-sm text-success me-2"></div>
                                Loading stations...
                            </div>
                        </div>
                        <div class="stations-list"></div>
                    </div>
                </div>
            `).join('');
            
        } catch (error) {
            console.error('Error loading divisions:', error);
            divisionsList.innerHTML = '<div class="p-3 text-center text-danger">Error loading divisions</div>';
        }
    }

    async toggleDivision(divisionItem) {
        const isExpanded = divisionItem.classList.contains('expanded');
        const stationsContainer = divisionItem.querySelector('.stations-container');
        const stationsList = divisionItem.querySelector('.stations-list');
        
        if (isExpanded) {
            divisionItem.classList.remove('expanded');
            stationsContainer.style.display = 'none';
            return;
        }

        const divisionName = divisionItem.dataset.division;
        divisionItem.classList.add('expanded');
        stationsContainer.style.display = 'block';
        
        try {
            const response = await fetch(`?action=get_stations&division=${encodeURIComponent(divisionName)}`);
            const stations = await response.json();
            
            stationsList.innerHTML = stations.map(station => `
                <div class="station-item" data-station-id="${station.id}">
                    <div class="station-name">
                        <i class="fas fa-shield-alt"></i>
                        ${station.police_station}
                    </div>
                    <div class="view-details">
                        <i class="fas fa-eye me-1"></i>View Details
                    </div>
                </div>
            `).join('');
            
        } catch (error) {
            console.error('Error loading stations:', error);
            stationsList.innerHTML = '<div class="p-3 text-center text-danger">Error loading stations</div>';
        }
    }

    displaySearchResults(results) {
        this.hideLoading();
        
        const searchResults = document.getElementById('searchResults');
        const directoryContainer = document.getElementById('directoryContainer');
        const resultsGrid = document.getElementById('resultsGrid');
        
        directoryContainer.style.display = 'none';
        searchResults.style.display = 'block';
        
        if (results.length === 0) {
            resultsGrid.innerHTML = `
                <div class="col-12 text-center py-5">
                    <i class="fas fa-search fa-4x text-muted mb-3"></i>
                    <h4>No Results Found</h4>
                    <p class="text-muted">Try searching with different keywords</p>
                </div>
            `;
            return;
        }

        resultsGrid.innerHTML = results.map(station => `
            <div class="result-card" data-station-id="${station.id}">
                <div class="result-station">
                    <i class="fas fa-shield-alt me-2"></i>
                    ${station.police_station}
                </div>
                <div class="result-hierarchy">
                    <div><strong>Province:</strong> ${station.province}</div>
                    <div><strong>Division:</strong> ${station.police_division}</div>
                    ${station.address ? `<div><strong>Address:</strong> ${station.address}</div>` : ''}
                </div>
                <div class="result-contacts">
                    ${station.oic_mobile ? `
                        <div class="contact-item">
                            <i class="fas fa-mobile-alt"></i>
                            <span>OIC: ${station.oic_mobile}</span>
                        </div>
                    ` : ''}
                    ${station.office_mobile ? `
                        <div class="contact-item">
                            <i class="fas fa-phone"></i>
                            <span>Office: ${station.office_mobile}</span>
                        </div>
                    ` : ''}
                </div>
            </div>
        `).join('');
    }

    async showStationDetails(stationId) {
        try {
            const response = await fetch(`?action=get_station_details&id=${stationId}`);
            const station = await response.json();
            this.currentStationData = station;
            
            const modalBody = document.getElementById('stationModalBody');
            modalBody.innerHTML = `
                <div class="station-details">
                    <div class="detail-section">
                        <h6><i class="fas fa-map-marker-alt me-2"></i>Location Information</h6>
                        <div class="detail-item">
                            <i class="fas fa-shield-alt"></i>
                            <span class="detail-label">Police Station:</span>
                            <span class="detail-value">${station.police_station}</span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-building"></i>
                            <span class="detail-label">Police Division:</span>
                            <span class="detail-value">${station.police_division}</span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-map"></i>
                            <span class="detail-label">Province:</span>
                            <span class="detail-value">${station.province}</span>
                        </div>
                        ${station.address ? `
                            <div class="detail-item">
                                <i class="fas fa-location-dot"></i>
                                <span class="detail-label">Address:</span>
                                <span class="detail-value">${station.address}</span>
                            </div>
                        ` : ''}
                    </div>
                    
                    <div class="detail-section">
                        <h6><i class="fas fa-phone me-2"></i>Contact Information</h6>
                        ${station.oic_mobile ? `
                            <div class="detail-item">
                                <i class="fas fa-mobile-alt"></i>
                                <span class="detail-label">OIC Mobile:</span>
                                <span class="detail-value">
                                    <a href="tel:${station.oic_mobile}" class="contact-link">${station.oic_mobile}</a>
                                </span>
                            </div>
                        ` : ''}
                        ${station.office_mobile ? `
                            <div class="detail-item">
                                <i class="fas fa-phone"></i>
                                <span class="detail-label">Office Mobile:</span>
                                <span class="detail-value">
                                    <a href="tel:${station.office_mobile}" class="contact-link">${station.office_mobile}</a>
                                </span>
                            </div>
                        ` : ''}
                        ${!station.oic_mobile && !station.office_mobile ? `
                            <div class="detail-item">
                                <i class="fas fa-exclamation-triangle text-warning"></i>
                                <span class="detail-value">Contact information not available</span>
                            </div>
                        ` : ''}
                    </div>
                </div>
            `;
            
            const modal = new bootstrap.Modal(document.getElementById('stationModal'));
            modal.show();
            
        } catch (error) {
            console.error('Error loading station details:', error);
            alert('Error loading station details. Please try again.');
        }
    }

    getDirections() {
        if (!this.currentStationData) return;
        
        const station = this.currentStationData;
        const query = encodeURIComponent(`${station.police_station}, ${station.address || station.police_division}, ${station.province}, Sri Lanka`);
        const mapsUrl = `https://www.google.com/maps/search/?api=1&query=${query}`;
        window.open(mapsUrl, '_blank');
    }

    showDirectory() {
        document.getElementById('searchResults').style.display = 'none';
        document.getElementById('directoryContainer').style.display = 'block';
    }

    showLoading() {
        document.getElementById('loadingIndicator').style.display = 'block';
        document.getElementById('searchResults').style.display = 'none';
        document.getElementById('directoryContainer').style.display = 'none';
    }

    hideLoading() {
        document.getElementById('loadingIndicator').style.display = 'none';
    }
}

// Initialize the directory when the page loads
document.addEventListener('DOMContentLoaded', () => {
    new PoliceStationDirectory();
});
</script>

<?php include 'includes/footer.php'; ?>