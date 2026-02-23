<?php
/**
 * User Model
 * Handles user data and operations
 */

namespace PIM\Models;

use PIM\Utils\Database;
use PIM\Utils\Auth;

class User {
    private $db;

    public function __construct(Database $database) {
        $this->db = $database;
    }

    /**
     * Create new user
     */
    public function create($data) {
        // Validate input
        if (empty($data['username']) || empty($data['email']) || empty($data['password'])) {
            throw new \Exception("Missing required fields");
        }

        // Check if user already exists
        if ($this->getUserByEmail($data['email'])) {
            throw new \Exception("Email already registered");
        }

        if ($this->getUserByUsername($data['username'])) {
            throw new \Exception("Username already taken");
        }

        // Prepare user data
        $userData = [
            'username' => $data['username'],
            'email' => $data['email'],
            'password_hash' => Auth::hashPassword($data['password']),
            'first_name' => $data['first_name'] ?? '',
            'last_name' => $data['last_name'] ?? '',
            'role' => $data['role'] ?? 'viewer',
            'is_active' => 1
        ];

        // Insert user
        return $this->db->insert('users', $userData);
    }

    /**
     * Get user by ID
     */
    public function getById($id) {
        return $this->db->getRow('SELECT id, username, email, first_name, last_name, role, is_active, last_login, created_at FROM users WHERE id = ?', [$id]);
    }

    /**
     * Get user by email
     */
    public function getUserByEmail($email) {
        return $this->db->getRow('SELECT id, username, email, first_name, last_name, role, is_active, password_hash, last_login, created_at FROM users WHERE email = ?', [$email]);
    }

    /**
     * Get user by username
     */
    public function getUserByUsername($username) {
        return $this->db->getRow('SELECT id, username, email, first_name, last_name, role, is_active, password_hash, last_login, created_at FROM users WHERE username = ?', [$username]);
    }

    /**
     * Authenticate user
     */
    public function authenticate($email, $password) {
        $user = $this->getUserByEmail($email);
        
        if (!$user) {
            throw new \Exception("Invalid email or password");
        }

        if (!$user['is_active']) {
            throw new \Exception("Account is inactive");
        }

        if (!Auth::verifyPassword($password, $user['password_hash'])) {
            throw new \Exception("Invalid email or password");
        }

        // Remove password hash from returned user
        unset($user['password_hash']);
        
        return $user;
    }

    /**
     * Update user
     */
    public function update($id, $data) {
        // Remove sensitive fields if present
        unset($data['id']);
        unset($data['password_hash']);

        // Handle password separately
        $password = $data['password'] ?? null;
        unset($data['password']);

        if ($password) {
            $data['password_hash'] = Auth::hashPassword($password);
        }

        $data['updated_at'] = date('Y-m-d H:i:s');

        return $this->db->update('users', $data, 'id = ?', [$id]);
    }

    /**
     * Get all users
     */
    public function getAll($limit = 50, $offset = 0) {
        $sql = 'SELECT id, username, email, first_name, last_name, role, is_active, last_login, created_at FROM users LIMIT ? OFFSET ?';
        return $this->db->getRows($sql, [$limit, $offset]);
    }

    /**
     * Count users
     */
    public function count() {
        $result = $this->db->getRow('SELECT COUNT(*) as total FROM users');
        return $result['total'] ?? 0;
    }

    /**
     * Delete user
     */
    public function delete($id) {
        return $this->db->delete('users', 'id = ?', [$id]);
    }

    /**
     * Check if user exists by email
     */
    public function emailExists($email) {
        return (bool) $this->getUserByEmail($email);
    }

    /**
     * Check if username exists
     */
    public function usernameExists($username) {
        return (bool) $this->getUserByUsername($username);
    }

    /**
     * Activate user
     */
    public function activate($id) {
        return $this->db->update('users', ['is_active' => 1], 'id = ?', [$id]);
    }

    /**
     * Deactivate user
     */
    public function deactivate($id) {
        return $this->db->update('users', ['is_active' => 0], 'id = ?', [$id]);
    }

    /**
     * Change user role
     */
    public function changeRole($id, $role) {
        $validRoles = ['admin', 'manager', 'editor', 'viewer'];
        
        if (!in_array($role, $validRoles)) {
            throw new \Exception("Invalid role: $role");
        }

        return $this->db->update('users', ['role' => $role], 'id = ?', [$id]);
    }
}

?>
