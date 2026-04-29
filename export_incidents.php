<?php
/**
 * Secure School Incident Reporting Platform
 * Export Incidents Handler
 */

require_once 'config.php';
require_once 'functions.php';

require_admin();

$incident = new Incident();
$incidents = $incident->get_all_incidents();

$format = $_GET['format'] ?? 'csv';

if ($format === 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="incidents_' . date('Y-m-d') . '.csv"');
    
    $output = fopen('php://output', 'w');
    
    // CSV header
    fputcsv($output, [
        'ID', 'Title', 'Reporter', 'Category', 'Priority', 'Status', 
        'Location', 'Incident Date', 'Incident Time', 'Assigned To', 
        'Created At', 'Updated At', 'Anonymous'
    ]);
    
    // CSV data
    foreach ($incidents as $inc) {
        fputcsv($output, [
            '#' . str_pad($inc['id'], 6, '0', STR_PAD_LEFT),
            $inc['title'],
            $inc['anonymous'] ? 'Anonymous' : ($inc['reporter_name'] ?? 'Unknown'),
            get_category_label($inc['category']),
            ucfirst($inc['priority']),
            ucfirst(str_replace('_', ' ', $inc['status'])),
            $inc['location'],
            $inc['incident_date'],
            $inc['incident_time'],
            $inc['assigned_name'] ?? 'Unassigned',
            $inc['created_at'],
            $inc['updated_at'],
            $inc['anonymous'] ? 'Yes' : 'No'
        ]);
    }
    
    fclose($output);
    exit();
} elseif ($format === 'pdf') {
    // Simple PDF export (requires proper PDF library for production)
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="incidents_' . date('Y-m-d') . '.pdf"');
    
    echo '<h1>Incidents Report</h1>';
    echo '<table border="1">';
    echo '<tr><th>ID</th><th>Title</th><th>Category</th><th>Status</th></tr>';
    
    foreach ($incidents as $inc) {
        echo '<tr>';
        echo '<td>#' . str_pad($inc['id'], 6, '0', STR_PAD_LEFT) . '</td>';
        echo '<td>' . htmlspecialchars($inc['title']) . '</td>';
        echo '<td>' . get_category_label($inc['category']) . '</td>';
        echo '<td>' . ucfirst(str_replace('_', ' ', $inc['status'])) . '</td>';
        echo '</tr>';
    }
    
    echo '</table>';
    exit();
} else {
    header('Location: incidents.php');
    exit();
}
?>
