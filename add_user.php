<?php
/**
 * Secure School Incident Reporting Platform
 * Add New User Handler
 */

require_once 'config.php';
require_once 'functions.php';

require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

// Validate CSRF token
if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit();
}

$full_name = sanitize_input($_POST['full_name'] ?? '');
$email = sanitize_input($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$role = sanitize_input($_POST['role'] ?? '');

// Validate inputs
if (empty($full_name) || empty($email) || empty($password) || empty($role)) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email address']);
    exit();
}

if (strlen($password) < 6) {
    echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters long']);
    exit();
}

if (!in_array($role, ['student', 'staff', 'admin'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid role']);
    exit();
}

$user = new User();

// Check if email already exists
if ($user->get_user_by_email($email)) {
    echo json_encode(['success' => false, 'message' => 'Email address already exists']);
    exit();
}

// Create user
$result = $user->create_user($full_name, $email, $password, $role);

header('Content-Type: application/json');
echo json_encode($result);
?>
