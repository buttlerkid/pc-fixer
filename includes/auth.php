<?php
/**
 * Authentication Functions
 * Handles user login, registration, and session management
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';

/**
 * Register a new user
 * @param string $email User email
 * @param string $name User name
 * @param string $password User password
 * @return array Result with success status and message
 */
function register($email, $name, $password) {
    $db = new Database();
    $conn = $db->connect();
    
    // Validate input
    if (empty($email) || empty($name) || empty($password)) {
        return ['success' => false, 'message' => 'All fields are required'];
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'message' => 'Invalid email format'];
    }
    
    if (strlen($password) < 6) {
        return ['success' => false, 'message' => 'Password must be at least 6 characters'];
    }
    
    // Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        return ['success' => false, 'message' => 'Email already registered'];
    }
    
    // Hash password and create user
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (email, name, password, role) VALUES (?, ?, ?, 'customer')");
    
    try {
        $stmt->execute([$email, $name, $hashedPassword]);
        return ['success' => true, 'message' => 'Registration successful'];
    } catch (PDOException $e) {
        error_log("Registration error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Registration failed. Please try again.'];
    }
}

/**
 * Login user
 * @param string $email User email
 * @param string $password User password
 * @return array Result with success status and message
 */
function login($email, $password) {
    $db = new Database();
    $conn = $db->connect();
    
    if (empty($email) || empty($password)) {
        return ['success' => false, 'message' => 'Email and password are required'];
    }
    
    $stmt = $conn->prepare("SELECT id, email, name, password, role FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['logged_in'] = true;
        
        // Regenerate session ID for security
        session_regenerate_id(true);
        
        return ['success' => true, 'message' => 'Login successful', 'role' => $user['role']];
    }
    
    return ['success' => false, 'message' => 'Invalid email or password'];
}

/**
 * Check if user is logged in
 * @return bool True if logged in, false otherwise
 */
function isLoggedIn() {
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

/**
 * Get current user ID
 * @return int|null User ID or null if not logged in
 */
function getUserId() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current user role
 * @return string|null User role or null if not logged in
 */
function getUserRole() {
    return $_SESSION['user_role'] ?? null;
}

/**
 * Check if current user is admin
 * @return bool True if admin, false otherwise
 */
function isAdmin() {
    return getUserRole() === 'admin';
}

/**
 * Require user to be logged in (redirect if not)
 * @param string $redirect Redirect URL if not logged in
 */
function requireLogin($redirect = '/public/login.php') {
    if (!isLoggedIn()) {
        header('Location: ' . $redirect);
        exit;
    }
}

/**
 * Require user to be admin (redirect if not)
 * @param string $redirect Redirect URL if not admin
 */
function requireAdmin($redirect = '/public/index.php') {
    requireLogin();
    if (!isAdmin()) {
        header('Location: ' . $redirect);
        exit;
    }
}

/**
 * Logout user
 */
function logout() {
    $_SESSION = [];
    session_destroy();
    setcookie(session_name(), '', time() - 3600, '/');
}

/**
 * Get user data by ID
 * @param int $userId User ID
 * @return array|null User data or null if not found
 */
function getUserById($userId) {
    $db = new Database();
    $conn = $db->connect();
    
    $stmt = $conn->prepare("SELECT id, email, name, role, created_at FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    return $stmt->fetch();
}
