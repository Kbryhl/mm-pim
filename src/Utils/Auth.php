<?php
/**
 * Authentication Utility Class
 * Handles session and authentication logic
 */

namespace PIM\Utils;

class Auth {
    
    /**
     * Start session if not already started
     */
    public static function startSession() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Check if user is authenticated
     */
    public static function isAuthenticated() {
        self::startSession();
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }

    /**
     * Get current user
     */
    public static function getCurrentUser() {
        self::startSession();
        return $_SESSION['user'] ?? null;
    }

    /**
     * Get current user ID
     */
    public static function getCurrentUserId() {
        self::startSession();
        return $_SESSION['user_id'] ?? null;
    }

    /**
     * Set user session
     */
    public static function setUserSession($user) {
        self::startSession();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user'] = $user;
        $_SESSION['login_time'] = time();
    }

    /**
     * Logout user
     */
    public static function logout() {
        self::startSession();
        session_unset();
        session_destroy();
    }

    /**
     * Hash password
     */
    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * Verify password
     */
    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }

    /**
     * Require authentication
     */
    public static function requireAuth() {
        if (!self::isAuthenticated()) {
            header('Location: /login');
            exit;
        }
    }

    /**
     * Require admin role
     */
    public static function requireAdmin() {
        self::requireAuth();
        $user = self::getCurrentUser();
        if ($user['role'] !== 'admin') {
            http_response_code(403);
            echo "Access Denied";
            exit;
        }
    }

    /**
     * Check if user has role
     */
    public static function hasRole($role) {
        $user = self::getCurrentUser();
        return $user && $user['role'] === $role;
    }

    /**
     * Generate CSRF token
     */
    public static function generateCsrfToken() {
        self::startSession();
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Verify CSRF token
     */
    public static function verifyCsrfToken($token) {
        self::startSession();
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Generate random token
     */
    public static function generateToken($length = 32) {
        return bin2hex(random_bytes($length));
    }

    /**
     * Update last login time
     */
    public static function updateLastLogin($userId, $db) {
        $db->update('users', ['last_login' => date('Y-m-d H:i:s')], 'id = ?', [$userId]);
    }
}

?>
