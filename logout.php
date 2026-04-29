<?php
/**
 * Secure School Incident Reporting Platform
 * Logout Handler
 */

require_once 'config.php';
require_once 'functions.php';

$user = new User();
$result = $user->logout();

// Redirect to login page
header('Location: login.php');
exit();
?>
