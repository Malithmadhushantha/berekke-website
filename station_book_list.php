<?php
require_once 'config/config.php';

// Get search query if provided
$search_query = isset($_GET['search']) ? cleanInput($_GET['search']) : '';

// Get all branches
$stmt = $pdo->prepare("SELECT DISTINCT branch_name FROM station_books ORDER BY branch_name");
$stmt->execute();
$branches = $stmt->fetchAll();

// Get books by branch (for JavaScript)
$books_by_branch = [];
foreach ($branches as $branch) {
    $stmt = $pdo->prepare("SELECT * FROM station_books WHERE branch_name = ? ORDER BY c_no ASC");
    $stmt->execute([$branch['branch_name']]);
    $books_by_branch[$branch['branch_name']] = $stmt->fetchAll();
}

// Search functionality
$search_results = [];
if (!empty($search_query)) {
    $stmt = $pdo->prepare("SELECT * FROM station_books WHERE book_name LIKE ? OR branch_name LIKE ? ORDER BY branch_name, c_no");
    $search_param = '%' . $search_query . '%';
    $stmt->execute([$search_param, $search_param]);
    $search_results = $stmt->fetchAll();
}

$page_title = "Station Book List";
?>
<!DOCTYPE html>
<html lang="si">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title . ' - ' . SITE_NAME; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts for Sinhala -->
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Sinhala:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --bs-primary: #0056b3;
            --navbar-height: 80px;
        }
        
        body {
            font-family: 'Noto Sans Sinhala', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        
        .main-container {
            background: white;
            margin: 2rem auto;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
            max-width: 1200px;
        }
        
        .header-section {
            background: linear-gradient(135deg, #0056b3 0%, #007bff 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        
        .header-section h1 {
            margin-bottom: 0.5rem;
            font-weight: 700;
        }
        
        .search-section {
            background: #f8f9fa;
            padding: 2rem;
            border-bottom: 1px solid #dee2e6;
        }
        
        .branch-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 1.5rem;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        .branch-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }
        
        .branch-header {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 1.5rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .branch-header:hover {
            background: linear-gradient(135deg, #218838 0%, #1da589 100%);
        }
        
        .branch-header h5 {
            margin: 0;
            font-weight: 600;
        }
        
        .book-list {
            display: none;
            padding: 0;
        }
        
        .book-list.show {
            display: block;
            animation: slideDown 0.5s ease-out;
        }
        
        .book-item {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #e9ecef;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: between;
        }
        
        .book-item:hover {
            background: #f8f9fa;
            transform: translateX(10px);
        }
        
        .book-item:last-child {
            border-bottom: none;
        }
        
        .book-number {
            background: #007bff;
            color: white;
            border-radius: 50%;
            width: 35px;
            height: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 1rem;
            font-size: 0.9rem;
        }
        
        .book-name {
            flex-grow: 1;
            font-weight: 500;
            font-size: 1.1rem;
        }
        
        .search-results {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin: 2rem 0;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .print-btn {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: #ffc107;
            border: none;
            border-radius: 8px;
            padding: 0.5rem 1rem;
            color: #000;
            transition: all 0.3s ease;
        }
        
        .print-btn:hover {
            background: #ffb302;
            transform: scale(1.05);
        }
        
        .branch-header {
            position: relative;
        }
        
        .expand-icon {
            transition: transform 0.3s ease;
            float: right;
            margin-top: 2px;
        }
        
        .expand-icon.rotated {
            transform: rotate(180deg);
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                max-height: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                max-height: 1000px;
                transform: translateY(0);
            }
        }
        
        .no-results {
            text-align: center;
            padding: 3rem;
            color: #6c757d;
        }
        
        .back-btn {
            position: fixed;
            top: 2rem;
            left: 2rem;
            z-index: 1000;
            background: rgba(255,255,255,0.9);
            border: none;
            border-radius: 50px;
            padding: 1rem 1.5rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            transition: all 0.3s ease;
        }
        
        .back-btn:hover {
            background: white;
            transform: translateY(-2px);
        }
        
        @media (max-width: 768px) {
            .main-container {
                margin: 1rem;
                border-radius: 15px;
            }
            
            .header-section {
                padding: 1.5rem;
            }
            
            .search-section {
                padding: 1.5rem;
            }
            
            .back-btn {
                position: static;
                margin: 1rem;
                width: calc(100% - 2rem);
                border-radius: 10px;
            }
        }
        
        @media print {
            body {
                background: white !important;
            }
            
            .main-container {
                box-shadow: none !important;
                margin: 0 !important;
            }
            
            .search-section,
            .back-btn,
            .print-btn {
                display: none !important;
            }
            
            .branch-card {
                break-inside: avoid;
                box-shadow: none !important;
                border: 1px solid #ddd !important;
            }
            
            .book-list {
                display: block !important;
            }
        }
    </style>
</head>
<body>
    <!-- Back Button -->
    <a href="index.php" class="btn back-btn">
        <i class="fas fa-arrow-left me-2"></i>Back to Home
    </a>

    <div class="main-container">
        <!-- Header Section -->
        <div class="header-section">
            <h1>
                <i class="fas fa-book me-3"></i>
                Police Station Book List
            </h1>
            <p class="mb-0 opacity-75">
                පොලිස් ස්ථානයේ අංශ අනුව පොත්පත් ලැයිස්තුව
            </p>
        </div>

        <!-- Search Section -->
        <div class="search-section">
            <div class="row align-items-center">
                <div class="col-lg-8 col-md-7 mb-3 mb-md-0">
                    <form method="GET" action="">
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-search"></i>
                            </span>
                            <input type="text" class="form-control form-control-lg" name="search" 
                                   value="<?php echo htmlspecialchars($search_query); ?>"
                                   placeholder="Search books by name or branch... / පොත් නම හෝ අංශය අනුව සොයන්න...">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-2"></i>Search
                            </button>
                        </div>
                    </form>
                </div>
                <div class="col-lg-4 col-md-5 text-end">
                    <button class="btn btn-success me-2" onclick="printAllBranches()">
                        <i class="fas fa-print me-2"></i>Print All
                    </button>
                    <button class="btn btn-info" onclick="showAllBooks()">
                        <i class="fas fa-eye me-2"></i>Show All
                    </button>
                </div>
            </div>
            
            <?php if (!empty($search_query)): ?>
            <div class="mt-3">
                <a href="station_book_list.php" class="btn btn-outline-secondary">
                    <i class="fas fa-times me-2"></i>Clear Search
                </a>
            </div>
            <?php endif; ?>
        </div>

        <!-- Content Section -->
        <div class="p-4">
            <?php if (!empty($search_query)): ?>
                <!-- Search Results -->
                <div class="search-results">
                    <h4 class="mb-4">
                        <i class="fas fa-search me-2"></i>
                        Search Results for "<?php echo htmlspecialchars($search_query); ?>"
                        <span class="badge bg-primary ms-2"><?php echo count($search_results); ?> results</span>
                    </h4>
                    
                    <?php if (!empty($search_results)): ?>
                        <?php 
                        $current_branch = '';
                        foreach ($search_results as $book): 
                            if ($current_branch !== $book['branch_name']):
                                if ($current_branch !== '') echo '</div></div>';
                                $current_branch = $book['branch_name'];
                        ?>
                        <div class="branch-card">
                            <div class="branch-header">
                                <h5><?php echo htmlspecialchars($book['branch_name']); ?></h5>
                            </div>
                            <div class="book-list show">
                        <?php endif; ?>
                                <div class="book-item">
                                    <div class="book-number"><?php echo $book['c_no']; ?></div>
                                    <div class="book-name"><?php echo htmlspecialchars($book['book_name']); ?></div>
                                </div>
                        <?php endforeach; ?>
                        <?php if ($current_branch !== '') echo '</div></div>'; ?>
                    <?php else: ?>
                        <div class="no-results">
                            <i class="fas fa-search fa-4x mb-3"></i>
                            <h5>No books found</h5>
                            <p>Try different search terms or browse all branches below.</p>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <!-- Branch List -->
            <?php if (empty($search_query)): ?>
            <div class="row">
                <div class="col-12">
                    <h4 class="mb-4">
                        <i class="fas fa-list me-2"></i>
                        Browse by Branch / අංශ අනුව පිරික්සන්න
                        <small class="text-muted">(Click to expand / විහිදවීමට ක්ලික් කරන්න)</small>
                    </h4>
                </div>
            </div>
            <?php endif; ?>

            <?php foreach ($branches as $index => $branch): ?>
            <div class="branch-card" data-branch="<?php echo htmlspecialchars($branch['branch_name']); ?>">
                <div class="branch-header" onclick="toggleBranch(this)">
                    <button class="print-btn" onclick="event.stopPropagation(); printBranch('<?php echo htmlspecialchars($branch['branch_name']); ?>')">
                        <i class="fas fa-print me-1"></i>Print
                    </button>
                    <h5>
                        <?php echo htmlspecialchars($branch['branch_name']); ?>
                        <span class="badge bg-light text-dark ms-2">
                            <?php echo count($books_by_branch[$branch['branch_name']]); ?> books
                        </span>
                        <i class="fas fa-chevron-down expand-icon"></i>
                    </h5>
                </div>
                <div class="book-list" id="books-<?php echo $index; ?>">
                    <?php foreach ($books_by_branch[$branch['branch_name']] as $book): ?>
                    <div class="book-item">
                        <div class="book-number"><?php echo $book['c_no']; ?></div>
                        <div class="book-name"><?php echo htmlspecialchars($book['book_name']); ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>

            <?php if (empty($branches)): ?>
            <div class="no-results">
                <i class="fas fa-book fa-4x mb-3"></i>
                <h5>No books available</h5>
                <p>No station books have been added to the system yet.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Books data for JavaScript
        const booksData = <?php echo json_encode($books_by_branch); ?>;
        
        function toggleBranch(header) {
            const card = header.parentElement;
            const bookList = card.querySelector('.book-list');
            const icon = header.querySelector('.expand-icon');
            
            if (bookList.classList.contains('show')) {
                bookList.classList.remove('show');
                icon.classList.remove('rotated');
            } else {
                bookList.classList.add('show');
                icon.classList.add('rotated');
            }
        }
        
        function showAllBooks() {
            const allBookLists = document.querySelectorAll('.book-list');
            const allIcons = document.querySelectorAll('.expand-icon');
            
            allBookLists.forEach(list => list.classList.add('show'));
            allIcons.forEach(icon => icon.classList.add('rotated'));
        }
        
        function printBranch(branchName) {
            const printWindow = window.open('', '_blank');
            const books = booksData[branchName] || [];
            
            let content = `
                <html>
                <head>
                    <title>Station Books - ${branchName}</title>
                    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Sinhala:wght@300;400;500;600;700&display=swap" rel="stylesheet">
                    <style>
                        body { 
                            font-family: 'Noto Sans Sinhala', sans-serif; 
                            margin: 20px; 
                            line-height: 1.6; 
                        }
                        .header { 
                            text-align: center; 
                            margin-bottom: 30px; 
                            border-bottom: 2px solid #000; 
                            padding-bottom: 20px; 
                        }
                        .branch-title { 
                            font-size: 24px; 
                            font-weight: bold; 
                            margin-bottom: 10px; 
                        }
                        .book-table { 
                            width: 100%; 
                            border-collapse: collapse; 
                            margin-top: 20px; 
                        }
                        .book-table th, .book-table td { 
                            border: 1px solid #ddd; 
                            padding: 12px; 
                            text-align: left; 
                        }
                        .book-table th { 
                            background-color: #f5f5f5; 
                            font-weight: bold; 
                        }
                        .book-number { 
                            text-align: center; 
                            font-weight: bold; 
                        }
                        .footer { 
                            margin-top: 40px; 
                            text-align: center; 
                            font-size: 12px; 
                            color: #666; 
                        }
                    </style>
                </head>
                <body>
                    <div class="header">
                        <h1>Police Station Book List</h1>
                        <div class="branch-title">${branchName}</div>
                        <p>පොලිස් ස්ථානයේ පොත්පත් ලැයිස්තුව</p>
                    </div>
                    
                    <table class="book-table">
                        <thead>
                            <tr>
                                <th style="width: 100px;">Book No.</th>
                                <th>Book Name / පොත් නම</th>
                            </tr>
                        </thead>
                        <tbody>
            `;
            
            books.forEach(book => {
                content += `
                    <tr>
                        <td class="book-number">${book.c_no}</td>
                        <td>${book.book_name}</td>
                    </tr>
                `;
            });
            
            content += `
                        </tbody>
                    </table>
                    
                    <div class="footer">
                        <p>Generated on ${new Date().toLocaleDateString()} | Total Books: ${books.length}</p>
                        <p>Berekke Website - Police Station Management System</p>
                    </div>
                </body>
                </html>
            `;
            
            printWindow.document.write(content);
            printWindow.document.close();
            printWindow.print();
        }
        
        function printAllBranches() {
            window.print();
        }
        
        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            // Add smooth animations to cards
            const cards = document.querySelectorAll('.branch-card');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                
                setTimeout(() => {
                    card.style.transition = 'all 0.6s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });
            
            // Auto-expand if search results
            <?php if (!empty($search_query)): ?>
            showAllBooks();
            <?php endif; ?>
        });
        
        // Add keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.key === 'p') {
                e.preventDefault();
                printAllBranches();
            }
            if (e.ctrlKey && e.key === 'f') {
                e.preventDefault();
                document.querySelector('input[name="search"]').focus();
            }
        });
    </script>
</body>
</html>