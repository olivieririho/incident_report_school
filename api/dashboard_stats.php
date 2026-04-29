<?php
/**
 * Optimized Dashboard Stats API
 * Returns dashboard statistics with caching
 */

header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

require_once '../config.php';
require_once '../functions.php';

// Use session-based caching to reduce database queries
session_start();
$cache_key = 'dashboard_stats_' . date('Y-m-d-H'); // Cache per hour

// Check if we have cached data
if (isset($_SESSION[$cache_key]) && (time() - $_SESSION[$cache_key]['timestamp']) < 300) {
    echo json_encode($_SESSION[$cache_key]['data']);
    exit;
}

try {
    $incident = new Incident();
    $stats = $incident->get_statistics();
    
    // Prepare optimized data structure
    $dashboard_data = [
        'total_incidents' => (int)($stats['total_incidents'] ?? 0),
        'new_cases' => 0,
        'critical' => 0,
        'resolved_today' => (int)($stats['resolved_today'] ?? 0),
        'last_30_days' => (int)($stats['last_30_days'] ?? 0),
        'unread' => 0
    ];
    
    // Extract new cases from status breakdown
    if (!empty($stats['by_status'])) {
        foreach ($stats['by_status'] as $status) {
            if ($status['status'] === 'new') {
                $dashboard_data['new_cases'] = (int)$status['count'];
                break;
            }
        }
    }
    
    // Extract critical from priority breakdown
    if (!empty($stats['by_priority'])) {
        foreach ($stats['by_priority'] as $priority) {
            if ($priority['priority'] === 'critical') {
                $dashboard_data['critical'] = (int)$priority['count'];
                break;
            }
        }
    }
    
    // Get unread count if user is logged in
    if (is_logged_in()) {
        $notification = new Notification();
        $dashboard_data['unread'] = (int)$notification->get_unread_count($_SESSION['user_id']);
    }
    
    // Cache the data
    $_SESSION[$cache_key] = [
        'data' => $dashboard_data,
        'timestamp' => time()
    ];
    
    echo json_encode($dashboard_data);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to fetch dashboard statistics']);
}
?>
