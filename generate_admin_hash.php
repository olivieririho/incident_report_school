<?php
/**
 * Generate correct bcrypt hash for admin password
 */

$password = "admin123";
$hash = password_hash($password, PASSWORD_BCRYPT);

echo "Password: " . $password . "\n";
echo "Bcrypt Hash: " . $hash . "\n";

// Verify the hash
if (password_verify($password, $hash)) {
    echo "Hash verification: SUCCESS\n";
} else {
    echo "Hash verification: FAILED\n";
}
?>
