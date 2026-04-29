<?php
/**
 * Secure School Incident Reporting Platform
 * Update Incident Handler
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
$status = sanitize_input($_POST['status'] ?? '');
$note = sanitize_input($_POST['note'] ?? '');

if (empty($incident_id) || empty($status) || empty($note) || !is_numeric($incident_id)) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit();
}

$incident = new Incident();

// Verify incident exists
$incident_details = $incident->get_incident_by_id($incident_id);
if (!$incident_details) {
    echo json_encode(['success' => false, 'message' => 'Incident not found']);
    exit();
}

// Check permissions
if (is_staff()) {
    // Staff can only update assigned incidents
    if ($incident_details['assigned_to'] != $_SESSION['user_id']) {
        echo json_encode(['success' => false, 'message' => 'Permission denied']);
        exit();
    }
}

$result = $incident->update_incident_status($incident_id, $status, $_SESSION['user_id'], $note);

header('Content-Type: application/json');
echo json_encode($result);
?>
