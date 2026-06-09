<?php
require_once 'Database.php';

class User {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
    }

    /**
     * Register a new user in the database.
     * @return int|false The new user ID on success, or false on failure.
     */
    public function register($firstName, $lastName, $email, $password) {
        // Check if email already exists
        $stmt = $this->pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            return false; // Email already registered
        }

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->pdo->prepare("INSERT INTO users (first_name, last_name, email, password_hash) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$firstName, $lastName, $email, $hashedPassword])) {
            return $this->pdo->lastInsertId();
        }
        return false;
    }

    /**
     * Authenticate a user and set up session variables.
     * @return array|false The user details on success, or false on failure.
     */
    public function login($email, $password) {
        $stmt = $this->pdo->prepare("SELECT id, first_name, last_name, password_hash, role FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['first_name'];
            $_SESSION['user_role'] = $user['role'];
            return $user;
        }
        return false;
    }

    /**
     * Get a user's details by their ID.
     * @return array|null User details or null if not found.
     */
    public function getProfile($userId) {
        $stmt = $this->pdo->prepare("SELECT id, first_name, last_name, email, role, created_at FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Update user details. Option to change password if provided.
     * @return bool
     */
    public function updateProfile($userId, $firstName, $lastName, $email, $password = null) {
        if ($password) {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $this->pdo->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ?, password_hash = ? WHERE id = ?");
            return $stmt->execute([$firstName, $lastName, $email, $hashedPassword, $userId]);
        } else {
            $stmt = $this->pdo->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ? WHERE id = ?");
            return $stmt->execute([$firstName, $lastName, $email, $userId]);
        }
    }

    /**
     * Check if a specific user ID is an admin.
     * @return bool
     */
    public function isAdmin($userId) {
        $stmt = $this->pdo->prepare("SELECT role FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        return ($user && $user['role'] === 'admin');
    }

    /**
     * Delete a user account by ID.
     * @return bool
     */
    public function deleteAccount($userId) {
        $stmt = $this->pdo->prepare("DELETE FROM users WHERE id = ?");
        return $stmt->execute([$userId]);
    }

    /**
     * Get total count of registered users (non-admin).
     * @return int
     */
    public function getUserCount() {
        $stmt = $this->pdo->query("SELECT COUNT(*) as total FROM users WHERE role = 'user'");
        return (int)$stmt->fetch()['total'];
    }
}
