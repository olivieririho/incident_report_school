<?php
/**
 * Secure School Incident Reporting Platform
 * Assign Incident Handler
 */

require_once 'config.php';
require_once 'functions.php';

require_staff_or_admin();

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

$incident_id = sanitize_input($_POST['incident_id'] ?? '');
$assigned_to = sanitize_input($_POST['assigned_to'] ?? '');

if (empty($incident_id) || empty($assigned_to) || !is_numeric($incident_id) || !is_numeric($assigned_to)) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit();
}

$incident = new Incident();
$user = new User();

// Verify incident exists
$incident_details = $incident->get_incident_by_id($incident_id);
if (!$incident_details) {
    echo json_encode(['success' => false, 'message' => 'Incident not found']);
    exit();
}

// Verify assigned user exists and is staff
$assigned_user = $user->get_user_by_id($assigned_to);
if (!$assigned_user || $assigned_user['role'] !== 'staff') {
    echo json_encode(['success' => false, 'message' => 'Invalid staff member']);
    exit();
}

// Only admins can assign incidents (or staff if they have permission)
if (!is_admin()) {
    echo json_encode(['success' => false, 'message' => 'Permission denied']);
    exit();
}

$result = $incident->assign_incident($incident_id, $assigned_to);

header('Content-Type: application/json');
echo json_encode($result);
?>
