<?php
/**
 * Secure School Incident Reporting Platform
 * Landing Page / Index
 */

require_once 'config.php';
require_once 'functions.php';

// Redirect to login if not logged in, otherwise to dashboard
if (is_logged_in()) {
    header('Location: dashboard.php');
    exit();
} else {
    header('Location: login.php');
    exit();
}
?>
