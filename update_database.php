<?php
/**
 * Secure School Incident Reporting Platform
 * Database Update Script - Add notification columns
 */

require_once 'config.php';

echo "<h2>Database Update Script</h2>";
echo "<p>Adding notification columns to users table...</p>";

try {
    $db = Database::getInstance();
    
    // Check if email_notifications column exists
    $stmt = $db->query("SHOW COLUMNS FROM users LIKE 'email_notifications'");
    $email_col_exists = $stmt->fetch();
    
    if (!$email_col_exists) {
        $db->query("ALTER TABLE users ADD COLUMN email_notifications TINYINT(1) DEFAULT 1");
        echo "<p style='color: green;'>✓ Added email_notifications column</p>";
    } else {
        echo "<p style='color: blue;'>ℹ email_notifications column already exists</p>";
    }
    
    // Check if desktop_notifications column exists
    $stmt = $db->query("SHOW COLUMNS FROM users LIKE 'desktop_notifications'");
    $desktop_col_exists = $stmt->fetch();
    
    if (!$desktop_col_exists) {
        $db->query("ALTER TABLE users ADD COLUMN desktop_notifications TINYINT(1) DEFAULT 0");
        echo "<p style='color: green;'>✓ Added desktop_notifications column</p>";
    } else {
        echo "<p style='color: blue;'>ℹ desktop_notifications column already exists</p>";
    }
    
    // Update all existing users to have email notifications enabled by default
    $db->query("UPDATE users SET email_notifications = 1 WHERE email_notifications IS NULL");
    echo "<p style='color: green;'>✓ Set default email notifications for existing users</p>";
    
    echo "<h3 style='color: green;'>Database update completed successfully!</h3>";
    echo "<p><a href='dashboard.php' style='padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px;'>Go to Dashboard</a></p>";
    
} catch (Exception $e) {
    echo "<h3 style='color: red;'>Error updating database:</h3>";
    echo "<p style='color: red;'>" . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
