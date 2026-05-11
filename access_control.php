<?php
/**
 * Access Control - Restrict access to sensitive pages
 * Note: require_staff(), require_admin(), require_staff_or_admin() are defined in config.php
 */

require_once 'config.php';

// Pages that should be restricted for non-admins
$admin_only_pages = [
    'users.php',
    'admin_dashboard.php',
    'email_templates.php',
    'email_logs.php'
];

// Pages that should be restricted for staff (they get staff dashboard)
$staff_restricted_pages = [
    'incidents.php'
];

$current_page = basename($_SERVER['PHP_SELF']);

// Redirect admin-only pages
if (in_array($current_page, $admin_only_pages) && !is_admin()) {
    if (is_staff()) {
        header('Location: staff_dashboard.php');
    } else {
        header('Location: dashboard.php');
    }
    exit();
}

// Redirect staff away from general incidents page to staff dashboard
if (in_array($current_page, $staff_restricted_pages) && is_staff()) {
    header('Location: staff_dashboard.php');
    exit();
}
?>
