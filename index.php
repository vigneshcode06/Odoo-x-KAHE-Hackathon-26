<?php
// Main Router
session_start();
require_once 'includes/csrf.php';

// Temporarily enabled to debug the 500 error
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// Get the requested URL
$url = isset($_GET['url']) ? rtrim($_GET['url'], '/') : '';
$urlParts = explode('/', $url);

// Check if it's an API request
if ($urlParts[0] === 'api') {
    header('Content-Type: application/json');
    require_once 'config/db.php';
    
    $apiRoute = isset($urlParts[1]) ? $urlParts[1] : '';
    $apiFile = 'api/routes/' . $apiRoute . '.php';
    
    if (file_exists($apiFile)) {
        require_once $apiFile;
    } else {
        http_response_code(404);
        echo json_encode(["error" => "API endpoint not found"]);
    }
    exit;
}

// Frontend routing
// Default to landing page
$page = empty($urlParts[0]) ? 'index' : $urlParts[0];

// Basic auth check for protected routes (will enhance later)
$protectedPages = ['dashboard', 'create-trip', 'my-trips', 'itinerary', 'trip-tools', 'profile', 'budget'];
if (in_array($page, $protectedPages) && !isset($_SESSION['user_id'])) {
    header('Location: /login');
    exit;
}

$pageFile = 'pages/' . $page . '.php';

if (file_exists($pageFile)) {
    require_once $pageFile;
} else {
    // 404 page
    http_response_code(404);
    echo "404 - Page Not Found";
}
