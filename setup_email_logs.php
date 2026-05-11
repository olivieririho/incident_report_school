<?php
/**
 * Secure School Incident Reporting Platform
 * Setup Email Logging Table
 */

require_once 'config.php';

$page_title = 'Setup Email Logging';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .setup-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            padding: 40px;
            max-width: 600px;
            width: 100%;
        }
        .setup-icon {
            font-size: 64px;
            color: #667eea;
            margin-bottom: 20px;
        }
        .step {
            display: flex;
            align-items: center;
            padding: 15px;
            margin: 10px 0;
            background: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid #667eea;
        }
        .step-success {
            border-left-color: #28a745;
            background: #d4edda;
        }
        .step-icon {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: #667eea;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-weight: bold;
        }
        .step-success .step-icon {
            background: #28a745;
        }
    </style>
</head>
<body>
    <div class="setup-card">
        <div class="text-center">
            <div class="setup-icon">
                <i class="fas fa-envelope"></i>
            </div>
            <h2>Email Logging Setup</h2>
            <p class="text-muted">Setting up email logging system...</p>
        </div>
        
        <div id="setupSteps">
            <?php
            $steps = [];
            
            try {
                $db = Database::getInstance();
                
                // Step 1: Check if email_logs table exists
                $steps[] = "Checking email_logs table...";
                $stmt = $db->query("SHOW TABLES LIKE 'email_logs'");
                $table_exists = $stmt->fetch();
                
                if (!$table_exists) {
                    // Create email_logs table
                    $steps[] = "Creating email_logs table...";
                    $db->query("CREATE TABLE email_logs (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        recipient_id INT NULL,
                        recipient_email VARCHAR(255) NOT NULL,
                        template_type VARCHAR(50) NOT NULL,
                        subject VARCHAR(255) NOT NULL,
                        content TEXT NOT NULL,
                        status ENUM('sent', 'failed', 'pending') DEFAULT 'pending',
                        error_message TEXT NULL,
                        incident_id INT NULL,
                        sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        INDEX idx_recipient (recipient_id),
                        INDEX idx_template (template_type),
                        INDEX idx_status (status),
                        INDEX idx_sent_at (sent_at),
                        INDEX idx_incident (incident_id)
                    )");
                    $steps[] = "email_logs table created successfully!";
                } else {
                    $steps[] = "email_logs table already exists.";
                }
                
                // Step 2: Check columns in users table
                $steps[] = "Checking users notification columns...";
                
                $stmt = $db->query("SHOW COLUMNS FROM users LIKE 'email_notifications'");
                $email_col = $stmt->fetch();
                
                if (!$email_col) {
                    $db->query("ALTER TABLE users ADD COLUMN email_notifications TINYINT(1) DEFAULT 1");
                    $steps[] = "Added email_notifications column to users table.";
                }
                
                $stmt = $db->query("SHOW COLUMNS FROM users LIKE 'desktop_notifications'");
                $desktop_col = $stmt->fetch();
                
                if (!$desktop_col) {
                    $db->query("ALTER TABLE users ADD COLUMN desktop_notifications TINYINT(1) DEFAULT 0");
                    $steps[] = "Added desktop_notifications column to users table.";
                }
                
                // Step 3: Set default values
                $steps[] = "Setting default notification preferences...";
                $db->query("UPDATE users SET email_notifications = 1 WHERE email_notifications IS NULL");
                $db->query("UPDATE users SET desktop_notifications = 0 WHERE desktop_notifications IS NULL");
                $steps[] = "Default preferences set successfully!";
                
                // Success
                echo '<div class="alert alert-success mt-4">';
                echo '<h5><i class="fas fa-check-circle me-2"></i>Setup Complete!</h5>';
                echo '<p>Email logging system is now ready to use.</p>';
                echo '</div>';
                
            } catch (Exception $e) {
                echo '<div class="alert alert-danger mt-4">';
                echo '<h5><i class="fas fa-exclamation-triangle me-2"></i>Setup Error</h5>';
                echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
                echo '</div>';
            }
            
            // Display steps
            foreach ($steps as $index => $step) {
                $stepClass = strpos($step, 'successfully') !== false || strpos($step, 'already exists') !== false ? 'step-success' : '';
                echo '<div class="step ' . $stepClass . '">';
                echo '<div class="step-icon">' . ($index + 1) . '</div>';
                echo '<div>' . htmlspecialchars($step) . '</div>';
                echo '</div>';
            }
            ?>
        </div>
        
        <div class="text-center mt-4">
            <a href="email_logs.php" class="btn btn-primary btn-lg">
                <i class="fas fa-arrow-right me-2"></i>View Email Logs
            </a>
            <a href="admin_dashboard.php" class="btn btn-outline-secondary btn-lg ms-2">
                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
            </a>
        </div>
    </div>
    
    <script src="https://kit.fontawesome.com/your-fontawesome-kit.js" crossorigin="anonymous"></script>
</body>
</html>
