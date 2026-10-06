<?php
/**
 * Authentication Functions
 * Task Manager Application
 */

require_once __DIR__ . '/../config/database.php';

/**
 * Start session if not already started
 */
function startSession() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

/**
 * Check if user is logged in
 * 
 * @return bool
 */
function isLoggedIn() {
    startSession();
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Get current logged in user data
 * 
 * @return array|null
 */
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT id, name, email, created_at FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

/**
 * Require user to be logged in
 * Redirect to login page if not authenticated
 */
function requireAuth() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

/**
 * Redirect authenticated users away from auth pages
 */
function redirectIfLoggedIn() {
    if (isLoggedIn()) {
        header('Location: dashboard.php');
        exit;
    }
}

/**
 * Login user
 * 
 * @param string $email
 * @param string $password
 * @return array ['success' => bool, 'message' => string]
 */
function loginUser($email, $password) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT id, name, email, password FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        startSession();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        return ['success' => true, 'message' => 'Login successful'];
    }
    
    return ['success' => false, 'message' => 'Invalid email or password'];
}

/**
 * Register new user
 * 
 * @param string $name
 * @param string $email
 * @param string $password
 * @param string $confirmPassword
 * @return array ['success' => bool, 'message' => string]
 */
function registerUser($name, $email, $password, $confirmPassword) {
    // Validate inputs
    $errors = [];
    
    if (empty(trim($name))) {
        $errors[] = 'Name is required';
    } elseif (strlen(trim($name)) > 100) {
        $errors[] = 'Name must be less than 100 characters';
    }
    
    if (empty(trim($email))) {
        $errors[] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format';
    } elseif (strlen(trim($email)) > 150) {
        $errors[] = 'Email must be less than 150 characters';
    }
    
    if (empty($password)) {
        $errors[] = 'Password is required';
    } elseif (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters';
    }
    
    if ($password !== $confirmPassword) {
        $errors[] = 'Passwords do not match';
    }
    
    if (!empty($errors)) {
        return ['success' => false, 'message' => implode('<br>', $errors)];
    }
    
    // Check if email already exists
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    
    if ($stmt->fetch()) {
        return ['success' => false, 'message' => 'Email already registered'];
    }
    
    // Hash password and insert user
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
    
    if ($stmt->execute([trim($name), trim($email), $hashedPassword])) {
        return ['success' => true, 'message' => 'Registration successful. Please login.'];
    }
    
    return ['success' => false, 'message' => 'Registration failed. Please try again.'];
}

/**
 * Logout user
 */
function logoutUser() {
    startSession();
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
}

/**
 * Sanitize output for HTML
 * 
 * @param string $string
 * @return string
 */
function escape($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Format date for display
 * 
 * @param string $date
 * @return string
 */
function formatDate($date) {
    if (empty($date) || $date === '0000-00-00') {
        return 'Not set';
    }
    return date('M d, Y', strtotime($date));
}

/**
 * Format datetime for display
 * 
 * @param string $datetime
 * @return string
 */
function formatDateTime($datetime) {
    if (empty($datetime)) {
        return 'Not set';
    }
    return date('M d, Y H:i', strtotime($datetime));
}

/**
 * Get priority badge class
 * 
 * @param string $priority
 * @return string
 */
function getPriorityClass($priority) {
    switch ($priority) {
        case 'High':
            return 'priority-high';
        case 'Medium':
            return 'priority-medium';
        case 'Low':
            return 'priority-low';
        default:
            return '';
    }
}

/**
 * Get status badge class
 * 
 * @param string $status
 * @return string
 */
function getStatusClass($status) {
    switch ($status) {
        case 'Completed':
            return 'status-completed';
        case 'In Progress':
            return 'status-in-progress';
        case 'Pending':
        default:
            return 'status-pending';
    }
}

/**
 * Check if task is overdue
 * 
 * @param string $dueDate
 * @param string $status
 * @return bool
 */
function isOverdue($dueDate, $status) {
    if (empty($dueDate) || $status === 'Completed') {
        return false;
    }
    return strtotime($dueDate) < strtotime(date('Y-m-d'));
}