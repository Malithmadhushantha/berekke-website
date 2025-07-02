<?php
require_once 'config/config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    $_SESSION['login_required'] = true;
    $_SESSION['redirect_after_login'] = 'write_blog.php';
    header('Location: login.php');
    exit();
}

$user = getUserInfo();
$error_message = '';
$success_message = '';

// Debug: Test database connection
try {
    if (isset($pdo)) {
        $test_query = $pdo->query("SELECT 1");
        error_log("Database connection test successful");
    } else {
        error_log("PDO object not available");
    }
} catch (Exception $e) {
    error_log("Database connection test failed: " . $e->getMessage());
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Debug: Log all POST data
    error_log("POST data received: " . print_r($_POST, true));
    error_log("FILES data received: " . print_r($_FILES, true));
    
    if (isset($_POST['save_blog']) || isset($_POST['publish_blog']) || (isset($_POST['title']) && isset($_POST['content']))) {
        // If no button was detected but we have title and content, assume it's a save operation
        if (!isset($_POST['save_blog']) && !isset($_POST['publish_blog']) && isset($_POST['title']) && isset($_POST['content'])) {
            $_POST['save_blog'] = '1'; // Default to save as draft
            error_log("No button detected, defaulting to save_blog");
        }
        // Debug: Check if PDO connection exists
        if (!isset($pdo)) {
            $error_message = 'Database connection error. Please check your database configuration.';
            error_log("PDO connection not found in write_blog.php");
        } else {
            $title = cleanInput($_POST['title']);
            $content = $_POST['content']; // Don't clean HTML content yet
            $excerpt = cleanInput($_POST['excerpt']);
            $status = isset($_POST['publish_blog']) ? 'published' : 'draft';
            
            // Debug: Log the received data
            error_log("Form data received - Title: " . $title . ", Content: " . substr($content, 0, 100) . "..., Content length: " . strlen($content) . ", Status: " . $status);
            
            // If content is empty, try to get it from the content-editor (fallback)
            if (empty($content) && isset($_POST['content-editor'])) {
                $content = $_POST['content-editor'];
                error_log("Using fallback content from content-editor");
            }
            
            // Validation
            if (empty($title)) {
                $error_message = 'Please fill in the title field.';
            } elseif (strlen($title) > 255) {
                $error_message = 'Title must be less than 255 characters.';
            } elseif (empty($content) || trim(strip_tags($content)) === '') {
                $error_message = 'Please fill in the content field.';
            } else {
                // Clean the content after validation
                $content = cleanInput($content);
                
                // Handle featured image upload
                $featured_image = null;
                if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === UPLOAD_ERR_OK) {
                    // Check if BLOG_IMAGES_PATH is defined
                    if (!defined('BLOG_IMAGES_PATH')) {
                        error_log("BLOG_IMAGES_PATH constant not defined");
                        $upload_dir = 'uploads/blog_images/'; // fallback
                    } else {
                        $upload_dir = BLOG_IMAGES_PATH;
                    }
                    
                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0755, true);
                    }
                    
                    $file_extension = pathinfo($_FILES['featured_image']['name'], PATHINFO_EXTENSION);
                    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
                    $max_file_size = 5 * 1024 * 1024; // 5MB
                    
                    if ($_FILES['featured_image']['size'] > $max_file_size) {
                        $error_message = 'Featured image must be smaller than 5MB.';
                    } elseif (!in_array(strtolower($file_extension), $allowed_extensions)) {
                        $error_message = 'Featured image must be a valid image file (JPG, PNG, GIF).';
                    } else {
                        // Verify it's actually an image
                        $image_info = getimagesize($_FILES['featured_image']['tmp_name']);
                        if ($image_info === false) {
                            $error_message = 'Invalid image file.';
                        } else {
                            $featured_image = 'blog_' . uniqid() . '_' . time() . '.' . $file_extension;
                            $upload_path = $upload_dir . $featured_image;
                            
                            if (!move_uploaded_file($_FILES['featured_image']['tmp_name'], $upload_path)) {
                                $featured_image = null;
                                $error_message = 'Failed to upload featured image.';
                            }
                        }
                    }
                }
                
                if (!$error_message) {
                    try {
                        // Generate excerpt if not provided
                        if (empty($excerpt)) {
                            $excerpt = substr(strip_tags($content), 0, 200) . '...';
                        }
                        
                        // Debug: Log before database insert
                        error_log("Attempting database insert - User ID: " . $user['id'] . ", Status: " . $status);
                        
                        // Insert blog post
                        $stmt = $pdo->prepare("INSERT INTO blogs (user_id, title, content, excerpt, featured_image, status) VALUES (?, ?, ?, ?, ?, ?)");
                        $result = $stmt->execute([$user['id'], $title, $content, $excerpt, $featured_image, $status]);
                        
                        if ($result) {
                            $blog_id = $pdo->lastInsertId();
                            error_log("Blog inserted successfully with ID: " . $blog_id);
                            
                            if ($status === 'published') {
                                $success_message = 'Blog post published successfully!';
                                $_SESSION['blog_published'] = true;
                                header('Location: blog_detail.php?id=' . $blog_id);
                                exit();
                            } else {
                                $success_message = 'Blog post saved as draft successfully!';
                                $_SESSION['blog_saved'] = true;
                                header('Location: my_blogs.php');
                                exit();
                            }
                        } else {
                            $error_message = 'Failed to save blog post. Database insert failed.';
                            error_log("Database insert failed");
                        }
                    } catch (PDOException $e) {
                        $error_message = 'Failed to save blog post. Database error: ' . $e->getMessage();
                        error_log("PDO Error: " . $e->getMessage());
                    }
            }
        }
    }
}

$page_title = "Write New Blog";
include 'includes/header.php';
?>

<div class="container py-5">
    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-pen-alt me-2"></i>
                        Write New Blog Post
                    </h4>
                </div>
                <div class="card-body p-4">
                    <!-- Debug information for development -->
                    <?php if (isset($_POST) && !empty($_POST)): ?>
                        <div class="alert alert-info" style="font-family: monospace; font-size: 12px;">
                            <strong>Debug Info:</strong><br>
                            Form submitted: <?php echo $_SERVER['REQUEST_METHOD']; ?><br>
                            Title length: <?php echo isset($_POST['title']) ? strlen($_POST['title']) : 'Not set'; ?><br>
                            Content length: <?php echo isset($_POST['content']) ? strlen($_POST['content']) : 'Not set'; ?><br>
                            Save button: <?php echo isset($_POST['save_blog']) ? 'YES' : 'NO'; ?><br>
                            Publish button: <?php echo isset($_POST['publish_blog']) ? 'YES' : 'NO'; ?><br>
                            All POST keys: <?php echo implode(', ', array_keys($_POST)); ?>
                        </div>
                    <?php endif; ?>
                    
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
                    
                    <form method="POST" action="" enctype="multipart/form-data" id="blogForm">
                        <div class="mb-4">
                            <label for="title" class="form-label fw-semibold">Blog Title *</label>
                            <input type="text" class="form-control form-control-lg" id="title" name="title" 
                                   value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>" 
                                   placeholder="Enter an engaging title for your blog post..." 
                                   required maxlength="255">
                            <div class="form-text">
                                <span id="titleCount">0</span>/255 characters
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="excerpt" class="form-label fw-semibold">Excerpt (Optional)</label>
                            <textarea class="form-control" id="excerpt" name="excerpt" rows="3" 
                                      placeholder="Brief summary of your blog post (will be auto-generated if left empty)..." 
                                      maxlength="500"><?php echo isset($_POST['excerpt']) ? htmlspecialchars($_POST['excerpt']) : ''; ?></textarea>
                            <div class="form-text">
                                <span id="excerptCount">0</span>/500 characters
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="featured_image" class="form-label fw-semibold">Featured Image (Optional)</label>
                            <input type="file" class="form-control" id="featured_image" name="featured_image" 
                                   accept="image/jpeg,image/jpg,image/png,image/gif">
                            <div class="form-text">
                                Recommended size: 1200x600px. Max file size: 5MB. Formats: JPG, PNG, GIF
                            </div>
                            <div id="imagePreview" class="mt-3" style="display: none;">
                                <img id="preview" src="" alt="Preview" class="img-fluid rounded" style="max-height: 200px;">
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="content" class="form-label fw-semibold">Content *</label>
                            <div id="editor-toolbar" class="border rounded-top p-2 bg-light">
                                <div class="btn-group me-2" role="group">
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="formatText('bold')">
                                        <i class="fas fa-bold"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="formatText('italic')">
                                        <i class="fas fa-italic"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="formatText('underline')">
                                        <i class="fas fa-underline"></i>
                                    </button>
                                </div>
                                <div class="btn-group me-2" role="group">
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="formatText('insertUnorderedList')">
                                        <i class="fas fa-list-ul"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="formatText('insertOrderedList')">
                                        <i class="fas fa-list-ol"></i>
                                    </button>
                                </div>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="formatText('createLink')">
                                        <i class="fas fa-link"></i>
                                    </button>
                                </div>
                            </div>
                            <div id="content-editor" class="form-control" style="min-height: 400px; border-top: none; border-top-left-radius: 0; border-top-right-radius: 0;" 
                                 contenteditable="true" placeholder="Write your blog content here...">
                                <?php echo isset($_POST['content']) ? $_POST['content'] : ''; ?>
                            </div>
                            <textarea id="content" name="content" style="display: none;" required></textarea>
                            <div class="form-text">
                                <span id="contentCount">0</span> characters
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <div>
                                <a href="blogs.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-times me-2"></i>Cancel
                                </a>
                            </div>
                            <div>
                                <button type="submit" name="save_blog" class="btn btn-outline-primary me-2" onclick="updateContentBeforeSubmit()">
                                    <i class="fas fa-save me-2"></i>Save as Draft
                                </button>
                                <button type="submit" name="publish_blog" class="btn btn-primary" onclick="updateContentBeforeSubmit()">
                                    <i class="fas fa-paper-plane me-2"></i>Publish Now
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Author Info -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="fas fa-user me-2"></i>Author Information
                    </h6>
                </div>
                <div class="card-body text-center">
                    <img src="<?php echo PROFILE_PICS_PATH . $user['profile_picture']; ?>" 
                         class="rounded-circle mb-3" width="80" height="80" alt="Your profile">
                    <h6><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h6>
                    <small class="text-muted">
                        <?php echo ucfirst($user['role']); ?> • Writing since <?php echo date('M Y', strtotime($user['created_at'])); ?>
                    </small>
                </div>
            </div>
            
            <!-- Writing Tips -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="fas fa-lightbulb me-2"></i>Writing Tips
                    </h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            <small>Use clear, engaging headlines</small>
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            <small>Break content into short paragraphs</small>
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            <small>Include relevant examples</small>
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            <small>Proofread before publishing</small>
                        </li>
                        <li class="mb-0">
                            <i class="fas fa-check text-success me-2"></i>
                            <small>Add a compelling featured image</small>
                        </li>
                    </ul>
                </div>
            </div>
            
            <!-- Blog Statistics -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="fas fa-chart-bar me-2"></i>Your Blog Stats
                    </h6>
                </div>
                <div class="card-body">
                    <?php
                    // Get user's blog statistics
                    $stats_sql = "SELECT 
                                    COUNT(*) as total_blogs,
                                    SUM(CASE WHEN status = 'published' THEN 1 ELSE 0 END) as published_blogs,
                                    SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) as draft_blogs,
                                    COALESCE(SUM(views), 0) as total_views
                                  FROM blogs WHERE user_id = ?";
                    $stmt = $pdo->prepare($stats_sql);
                    $stmt->execute([$user['id']]);
                    $stats = $stmt->fetch();
                    
                    // Get total likes
                    $likes_sql = "SELECT COUNT(*) as total_likes 
                                 FROM blog_likes bl 
                                 JOIN blogs b ON bl.blog_id = b.id 
                                 WHERE b.user_id = ?";
                    $stmt = $pdo->prepare($likes_sql);
                    $stmt->execute([$user['id']]);
                    $likes_stats = $stmt->fetch();
                    ?>
                    
                    <div class="row text-center">
                        <div class="col-6 mb-3">
                            <div class="p-2">
                                <h5 class="text-primary mb-1"><?php echo number_format($stats['published_blogs'] ?? 0); ?></h5>
                                <small class="text-muted">Published</small>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="p-2">
                                <h5 class="text-warning mb-1"><?php echo number_format($stats['draft_blogs'] ?? 0); ?></h5>
                                <small class="text-muted">Drafts</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-2">
                                <h5 class="text-success mb-1"><?php echo number_format($stats['total_views'] ?? 0); ?></h5>
                                <small class="text-muted">Total Views</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-2">
                                <h5 class="text-danger mb-1"><?php echo number_format($likes_stats['total_likes'] ?? 0); ?></h5>
                                <small class="text-muted">Total Likes</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-center mt-3">
                        <a href="my_blogs.php" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-list me-1"></i>View My Blogs
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Categories -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="fas fa-tags me-2"></i>Popular Topics
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-wrap gap-2">
                        <span class="badge bg-primary cursor-pointer" onclick="addTag('Criminal Law')">Criminal Law</span>
                        <span class="badge bg-success cursor-pointer" onclick="addTag('Investigation')">Investigation</span>
                        <span class="badge bg-warning cursor-pointer" onclick="addTag('Evidence')">Evidence</span>
                        <span class="badge bg-info cursor-pointer" onclick="addTag('Court Procedures')">Court Procedures</span>
                        <span class="badge bg-danger cursor-pointer" onclick="addTag('Case Studies')">Case Studies</span>
                        <span class="badge bg-secondary cursor-pointer" onclick="addTag('Legal Updates')">Legal Updates</span>
                        <span class="badge bg-dark cursor-pointer" onclick="addTag('Training')">Training</span>
                        <span class="badge bg-primary cursor-pointer" onclick="addTag('Technology')">Technology</span>
                    </div>
                    <small class="text-muted d-block mt-2">Click tags to add them to your content</small>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.cursor-pointer {
    cursor: pointer;
}

.cursor-pointer:hover {
    opacity: 0.8;
}

#content-editor {
    outline: none;
    font-family: inherit;
    line-height: 1.6;
}

#content-editor:empty:before {
    content: attr(placeholder);
    color: #6c757d;
    font-style: italic;
}

#content-editor:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

.alert {
    border-radius: 10px;
    border: none;
}

@media (max-width: 768px) {
    #editor-toolbar {
        overflow-x: auto;
        white-space: nowrap;
    }
    
    #editor-toolbar .btn-group {
        display: inline-flex;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const titleInput = document.getElementById('title');
    const excerptInput = document.getElementById('excerpt');
    const contentEditor = document.getElementById('content-editor');
    const contentHidden = document.getElementById('content');
    const titleCount = document.getElementById('titleCount');
    const excerptCount = document.getElementById('excerptCount');
    const contentCount = document.getElementById('contentCount');
    const form = document.getElementById('blogForm');
    
    // Character counting
    function updateCounts() {
        if (titleInput && titleCount) {
            titleCount.textContent = titleInput.value.length;
        }
        if (excerptInput && excerptCount) {
            excerptCount.textContent = excerptInput.value.length;
        }
        if (contentEditor && contentCount) {
            contentCount.textContent = contentEditor.textContent.length;
        }
    }
    
    // Update hidden content field - FIXED
    function updateContentField() {
        if (contentEditor && contentHidden) {
            // Get innerHTML and ensure it's not empty
            const editorContent = contentEditor.innerHTML.trim();
            if (editorContent === '' || editorContent === '<br>' || editorContent === '<p><br></p>') {
                contentHidden.value = '';
            } else {
                contentHidden.value = editorContent;
            }
            console.log('Content updated:', contentHidden.value); // Debug log
        }
    }
    
    // Event listeners - IMPROVED
    titleInput?.addEventListener('input', updateCounts);
    excerptInput?.addEventListener('input', updateCounts);
    
    // Better event handling for content editor
    if (contentEditor) {
        contentEditor.addEventListener('input', function() {
            updateCounts();
            updateContentField();
        });
        
        contentEditor.addEventListener('blur', updateContentField);
        contentEditor.addEventListener('keyup', updateContentField);
        contentEditor.addEventListener('paste', function() {
            setTimeout(updateContentField, 100); // Delay to allow paste to complete
        });
    }
    
    // File upload preview
    const fileInput = document.getElementById('featured_image');
    const imagePreview = document.getElementById('imagePreview');
    const preview = document.getElementById('preview');
    
    fileInput?.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            // Validate file size
            if (file.size > 5 * 1024 * 1024) {
                alert('File size must be less than 5MB');
                this.value = '';
                imagePreview.style.display = 'none';
                return;
            }
            
            // Validate file type
            const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
            if (!allowedTypes.includes(file.type)) {
                alert('Please select a valid image file (JPG, PNG, GIF)');
                this.value = '';
                imagePreview.style.display = 'none';
                return;
            }
            
            // Show preview
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                imagePreview.style.display = 'block';
            };
            reader.readAsDataURL(file);
        } else {
            imagePreview.style.display = 'none';
        }
    });
    
    // Form submission - IMPROVED
    form?.addEventListener('submit', function(e) {
        // Ensure content is updated before validation
        updateContentField();
        
        // Enhanced validation
        if (!titleInput.value.trim()) {
            e.preventDefault();
            alert('Please enter a title for your blog post.');
            titleInput.focus();
            return;
        }
        
        // Check both the editor content and hidden field
        const editorText = contentEditor.textContent.trim();
        const hiddenContent = contentHidden.value.trim();
        
        if (!editorText || editorText === '' || (!hiddenContent || hiddenContent === '')) {
            e.preventDefault();
            alert('Please enter some content for your blog post.');
            contentEditor.focus();
            return;
        }
        
        console.log('Form submitting with content:', hiddenContent); // Debug log
        
        // Don't disable buttons immediately - let the form submit first
        setTimeout(() => {
            const submitButtons = form.querySelectorAll('button[type="submit"]');
            submitButtons.forEach(button => {
                button.disabled = true;
                const originalText = button.innerHTML;
                button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Saving...';
                
                // Re-enable after 10 seconds to prevent permanent lock
                setTimeout(() => {
                    button.disabled = false;
                    button.innerHTML = originalText;
                }, 10000);
            });
        }, 100);
    });
    
    // Auto-save draft every 2 minutes
    let autoSaveTimer;
    function resetAutoSaveTimer() {
        clearTimeout(autoSaveTimer);
        autoSaveTimer = setTimeout(autoSaveDraft, 2 * 60 * 1000); // 2 minutes
    }
    
    function autoSaveDraft() {
        updateContentField(); // Ensure content is up to date
        if (titleInput.value.trim() && contentEditor.textContent.trim()) {
            console.log('Auto-saving draft...');
            // You could implement auto-save functionality here
        }
        resetAutoSaveTimer();
    }
    
    // Start auto-save timer
    titleInput?.addEventListener('input', resetAutoSaveTimer);
    contentEditor?.addEventListener('input', resetAutoSaveTimer);
    resetAutoSaveTimer();
    
    // Initial setup
    updateCounts();
    updateContentField(); // Ensure content field is populated on page load
});

// Function to update content before form submission (called by onclick)
function updateContentBeforeSubmit() {
    const contentEditor = document.getElementById('content-editor');
    const contentHidden = document.getElementById('content');
    if (contentEditor && contentHidden) {
        const editorContent = contentEditor.innerHTML.trim();
        if (editorContent === '' || editorContent === '<br>' || editorContent === '<p><br></p>') {
            contentHidden.value = '';
        } else {
            contentHidden.value = editorContent;
        }
        console.log('Content updated before submit:', contentHidden.value);
    }
}

// Text formatting functions
function formatText(command, value = null) {
    document.execCommand(command, false, value);
    document.getElementById('content-editor').focus();
    
    // Update content field after formatting
    setTimeout(function() {
        const contentEditor = document.getElementById('content-editor');
        const contentHidden = document.getElementById('content');
        if (contentEditor && contentHidden) {
            contentHidden.value = contentEditor.innerHTML;
        }
    }, 10);
}

// Add tag to content
function addTag(tag) {
    const editor = document.getElementById('content-editor');
    const selection = window.getSelection();
    
    if (selection.rangeCount > 0) {
        const range = selection.getRangeAt(0);
        const tagElement = document.createElement('span');
        tagElement.className = 'badge bg-primary me-1';
        tagElement.textContent = tag;
        
        range.insertNode(tagElement);
        range.collapse(false);
    } else {
        // If no selection, add at the end
        const tagElement = document.createElement('span');
        tagElement.className = 'badge bg-primary me-1';
        tagElement.textContent = tag + ' ';
        editor.appendChild(tagElement);
    }
    
    editor.focus();
    
    // Update content field after adding tag
    setTimeout(function() {
        const contentHidden = document.getElementById('content');
        if (contentHidden) {
            contentHidden.value = editor.innerHTML;
        }
    }, 10);
}

// Auto-resize editor
function autoResize() {
    const editor = document.getElementById('content-editor');
    if (editor.scrollHeight > editor.clientHeight) {
        editor.style.height = Math.min(editor.scrollHeight, 600) + 'px';
    }
}

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    if (e.ctrlKey || e.metaKey) {
        switch (e.key) {
            case 'b':
                e.preventDefault();
                formatText('bold');
                break;
            case 'i':
                e.preventDefault();
                formatText('italic');
                break;
            case 'u':
                e.preventDefault();
                formatText('underline');
                break;
            case 's':
                e.preventDefault();
                // Update content before saving
                const contentEditor = document.getElementById('content-editor');
                const contentHidden = document.getElementById('content');
                if (contentEditor && contentHidden) {
                    contentHidden.value = contentEditor.innerHTML;
                }
                
                // Add hidden input to simulate save button click
                const form = document.getElementById('blogForm');
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'save_blog';
                hiddenInput.value = '1';
                form.appendChild(hiddenInput);
                
                // Submit the form
                form.submit();
                break;
        }
    }
});

// Warn before leaving page with unsaved changes
let hasUnsavedChanges = false;

document.getElementById('title')?.addEventListener('input', () => hasUnsavedChanges = true);
document.getElementById('content-editor')?.addEventListener('input', () => hasUnsavedChanges = true);

window.addEventListener('beforeunload', function(e) {
    if (hasUnsavedChanges) {
        const message = 'You have unsaved changes. Are you sure you want to leave?';
        e.returnValue = message;
        return message;
    }
});

// Clear unsaved changes flag on form submit
document.getElementById('blogForm')?.addEventListener('submit', () => hasUnsavedChanges = false);
</script>

<?php include 'includes/footer.php'; ?>