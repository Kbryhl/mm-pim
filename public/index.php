<?php
/**
 * PIM System - Product Information Management
 * Main Entry Point
 */

// Load configuration
require_once '../config/app.php';
require_once '../config/database.php';
require_once '../src/Utils/Auth.php';

use PIM\Utils\Auth;

// Start session
Auth::startSession();

// Parse request
$request = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$request = trim($request, '/');

// Check if user is trying to access protected routes
$publicRoutes = ['login', 'register', 'forgot-password'];
$isPublicRoute = in_array($request, $publicRoutes);

// Redirect to login if accessing protected route without auth
if (!$isPublicRoute && !Auth::isAuthenticated()) {
    header('Location: ' . BASE_URL . 'login');
    exit;
}

// Route to appropriate handler
if (empty($request) || $request === 'index.php') {
    if (!Auth::isAuthenticated()) {
        header('Location: ' . BASE_URL . 'login');
        exit;
    }
    require_once '../views/dashboard.php';
} elseif ($request === 'login') {
    require_once '../views/login.php';
} elseif ($request === 'register') {
    require_once '../views/register.php';
} elseif ($request === 'dashboard') {
    Auth::requireAuth();
    require_once '../views/dashboard.php';
} elseif (strpos($request, 'products') === 0) {
    Auth::requireAuth();
    if (strpos($request, 'products/new') === 0 || strpos($request, 'products/edit') === 0) {
        require_once '../views/product-form.php';
    } elseif (strpos($request, 'products/variants') === 0) {
        require_once '../views/variants.php';
    } elseif (strpos($request, 'products/pricing') === 0) {
        require_once '../views/pricing.php';
    } else {
        require_once '../views/products.php';
    }
} elseif (strpos($request, 'categories') === 0) {
    Auth::requireAuth();
    if (strpos($request, 'categories/new') === 0 || strpos($request, 'categories/edit') === 0) {
        require_once '../views/category-form.php';
    } else {
        require_once '../views/categories.php';
    }
} elseif ($request === 'attributes') {
    Auth::requireAuth();
    // Attributes page will be created in next step
    echo "<h1>Attributes Management - Coming Soon</h1>";
} elseif ($request === 'settings') {
    Auth::requireAuth();
    echo "<h1>Settings - Coming Soon</h1>";
} elseif ($request === 'analytics') {
    Auth::requireAuth();
    echo "<h1>Analytics - Coming Soon</h1>";
} elseif ($request === 'users') {
    Auth::requireAuth();
    echo "<h1>User Management - Coming Soon</h1>";
} else {
    // 404 Not Found
    http_response_code(404);
    echo "<h1>404 - Page Not Found</h1>";
    echo "<p><a href='" . BASE_URL . "'>Return to Dashboard</a></p>";
}

?>
