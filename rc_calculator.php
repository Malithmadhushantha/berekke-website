<?php
require_once 'config/config.php';

// Get category filter
$selected_category = isset($_GET['category']) ? intval($_GET['category']) : '';

// Get all categories
$stmt = $pdo->prepare("SELECT * FROM download_categories ORDER BY name");
$stmt->execute();
$categories = $stmt->fetchAll();

// Get downloads based on category
if ($selected_category) {
    $stmt = $pdo->prepare("SELECT d.*, dc.name as category_name FROM downloads d 
                          JOIN download_categories dc ON d.category_id = dc.id 
                          WHERE d.category_id = ? ORDER BY d.created_at DESC");
    $stmt->execute([$selected_category]);
    $downloads = $stmt->fetchAll();
    $page_title = "Downloads - Category";
} else {
    // Get recent downloads for each category (3 per category + browse all)
    $downloads_by_category = [];
    foreach ($categories as $category) {
        $stmt = $pdo->prepare("SELECT d.*, dc.name as category_name FROM downloads d 
                              JOIN download_categories dc ON d.category_id = dc.id 
                              WHERE d.category_id = ? ORDER BY d.created_at DESC LIMIT 3");
        $stmt->execute([$category['id']]);
        $downloads_by_category[$category['id']] = $stmt->fetchAll();
    }
    $page_title = "Downloads";
}

// Handle download counter increment
if (isset($_GET['download']) && is_numeric($_GET['download'])) {
    $download_id = intval($_GET['download']);
    
    // Get download info
    $stmt = $pdo->prepare("SELECT * FROM downloads WHERE id = ?");
    $stmt->execute([$download_id]);
    $download = $stmt->fetch();
    
    if ($download) {
        // Increment download counter
        $stmt = $pdo->prepare("UPDATE downloads SET download_count = download_count + 1 WHERE id = ?");
        $stmt->execute([$download