<?php
/**
 * Secure School Incident Reporting Platform
 * Database Configuration File
 */

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'secure_school');
define('DB_USER', 'root');
define('DB_PASS', '');

// Application configuration
define('APP_NAME', 'Secure School Incident Reporting');
define('APP_URL', 'http://localhost/projectines/incident/');
define('UPLOAD_PATH', 'uploads/evidence/');
define('MAX_FILE_SIZE', 5242880); // 5MB in bytes

// Session configuration
define('SESSION_LIFETIME', 3600); // 1 hour in seconds

// Security configuration
define('HASH_ALGO', PASSWORD_DEFAULT);
define('HASH_COST', 12);

// Error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start secure session
function secure_session_start() {
    // Set secure session parameters
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS
    
    // Set session lifetime
    ini_set('session.gc_maxlifetime', SESSION_LIFETIME);
    
    // Start session
    session_start();
}

// Database connection class
class Database {
    private static $instance = null;
    private $connection;
    
    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_PERSISTENT => true
            ];
            
            $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    public function query($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log("Database query error: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function lastInsertId() {
        return $this->connection->lastInsertId();
    }
}

// Security functions
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function get_user_role() {
    return $_SESSION['user_role'] ?? null;
}

function is_admin() {
    return get_user_role() === 'admin';
}

function is_staff() {
    return get_user_role() === 'staff';
}

function is_student() {
    return get_user_role() === 'student';
}

function require_login() {
    if (!is_logged_in()) {
        header('Location: login.php');
        exit();
    }
}

function require_admin() {
    require_login();
    if (!is_admin()) {
        header('Location: dashboard.php');
        exit();
    }
}

function require_staff_or_admin() {
    require_login();
    if (!is_staff() && !is_admin()) {
        header('Location: dashboard.php');
        exit();
    }
}

// File upload validation
function validate_file_upload($file) {
    $allowed_types = ['image/jpeg', 'image/png', 'application/pdf'];
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'pdf'];
    
    // Check file size
    if ($file['size'] > MAX_FILE_SIZE) {
        return ['error' => 'File size exceeds maximum limit of 5MB'];
    }
    
    // Check file type
    if (!in_array($file['type'], $allowed_types)) {
        return ['error' => 'Invalid file type. Only JPG, PNG, and PDF files are allowed'];
    }
    
    // Check file extension
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, $allowed_extensions)) {
        return ['error' => 'Invalid file extension. Only JPG, PNG, and PDF files are allowed'];
    }
    
    return ['success' => true];
}

// Create upload directory if it doesn't exist
function ensure_upload_directory() {
    $upload_path = UPLOAD_PATH;
    if (!is_dir($upload_path)) {
        mkdir($upload_path, 0755, true);
    }
}

// Generate unique filename for uploads
function generate_unique_filename($original_name) {
    $extension = pathinfo($original_name, PATHINFO_EXTENSION);
    return uniqid() . '_' . time() . '.' . $extension;
}

// Initialize application
secure_session_start();
ensure_upload_directory();
?>
