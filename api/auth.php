<?php
/**
 * Authentication API Endpoints
 */

// Load configuration
require_once '../config/app.php';
require_once '../config/database.php';

// Load utility classes
require_once '../src/Utils/Database.php';
require_once '../src/Utils/Auth.php';
require_once '../src/Models/User.php';

use PIM\Utils\Database as DB;
use PIM\Utils\Auth;
use PIM\Models\User;

// Start session
Auth::startSession();

// Get request method and path
$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Set response header
header('Content-Type: application/json');

try {
    // Initialize database and models
    $db = new DB($conn);
    $userModel = new User($db);

    if ($path === '/api/auth/login' && $method === 'POST') {
        handleLogin($userModel, $db);
    } elseif ($path === '/api/auth/register' && $method === 'POST') {
        handleRegister($userModel);
    } elseif ($path === '/api/auth/logout' && $method === 'POST') {
        handleLogout();
    } elseif ($path === '/api/auth/me' && $method === 'GET') {
        handleGetCurrentUser();
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Endpoint not found']);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

/**
 * Handle login request
 */
function handleLogin($userModel, $db) {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input || !isset($input['email']) || !isset($input['password'])) {
        http_response_code(400);
        echo json_encode(['message' => 'Email and password are required']);
        return;
    }

    try {
        // Authenticate user
        $user = $userModel->authenticate($input['email'], $input['password']);

        // Update last login
        Auth::updateLastLogin($user['id'], $db);

        // Set session
        Auth::setUserSession($user);

        http_response_code(200);
        echo json_encode([
            'message' => 'Login successful',
            'user' => $user
        ]);

    } catch (Exception $e) {
        http_response_code(401);
        echo json_encode(['message' => $e->getMessage()]);
    }
}

/**
 * Handle registration request
 */
function handleRegister($userModel) {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        http_response_code(400);
        echo json_encode(['message' => 'Invalid JSON input']);
        return;
    }

    // Validate required fields
    if (empty($input['email']) || empty($input['password']) || empty($input['username'])) {
        http_response_code(400);
        echo json_encode(['message' => 'Email, username, and password are required']);
        return;
    }

    // Validate email format
    if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['message' => 'Invalid email format']);
        return;
    }

    // Validate password length
    if (strlen($input['password']) < 8) {
        http_response_code(400);
        echo json_encode(['message' => 'Password must be at least 8 characters long']);
        return;
    }

    // Validate username
    if (strlen($input['username']) < 3) {
        http_response_code(400);
        echo json_encode(['message' => 'Username must be at least 3 characters long']);
        return;
    }

    try {
        // Create user
        $userId = $userModel->create([
            'username' => $input['username'],
            'email' => $input['email'],
            'password' => $input['password'],
            'first_name' => $input['first_name'] ?? '',
            'last_name' => $input['last_name'] ?? '',
            'role' => 'viewer'
        ]);

        http_response_code(201);
        echo json_encode([
            'message' => 'Registration successful',
            'user_id' => $userId
        ]);

    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['message' => $e->getMessage()]);
    }
}

/**
 * Handle logout request
 */
function handleLogout() {
    Auth::logout();
    http_response_code(200);
    echo json_encode(['message' => 'Logout successful']);
}

/**
 * Handle get current user request
 */
function handleGetCurrentUser() {
    if (!Auth::isAuthenticated()) {
        http_response_code(401);
        echo json_encode(['message' => 'Not authenticated']);
        return;
    }

    $user = Auth::getCurrentUser();
    http_response_code(200);
    echo json_encode(['user' => $user]);
}

?>
