<?php
/**
 * Reset Admin Password Script
 * Run this file to reset the admin password to: admin123
 */

// Database configuration
$host = 'localhost';
$dbname = 'secure_school';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // New password hash for "admin123"
    $newPasswordHash = password_hash('admin123', PASSWORD_DEFAULT);
    
    // Update admin password
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = 'admin@school.edu'");
    $stmt->execute([$newPasswordHash]);
    
    echo "✅ Admin password has been reset successfully!\n";
    echo "Email: admin@school.edu\n";
    echo "Password: admin123\n";
    echo "\nYou can now login with these credentials.\n";
    echo "Please delete this file after use for security.\n";
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Make sure the database 'secure_school' exists and is properly configured.\n";
}
?>
