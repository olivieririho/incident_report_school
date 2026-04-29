<?php
/**
 * Secure School Incident Reporting Platform
 * Settings Page
 */

require_once 'config.php';
require_once 'functions.php';

require_login();

$user = new User();
$current_user = $user->get_user_by_id($_SESSION['user_id']);

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = sanitize_input($_POST['action'] ?? '');
    
    // Validate CSRF token
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        switch ($action) {
            case 'update_profile':
                $full_name = sanitize_input($_POST['full_name'] ?? '');
                $email = sanitize_input($_POST['email'] ?? '');
                
                if (empty($full_name)) {
                    $errors[] = 'Full name is required';
                }
                
                if (empty($email)) {
                    $errors[] = 'Email is required';
                } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $errors[] = 'Invalid email address';
                }
                
                // Check if email is taken by another user
                if ($email !== $current_user['email']) {
                    $existing_user = $user->get_user_by_email($email);
                    if ($existing_user && $existing_user['id'] != $_SESSION['user_id']) {
                        $errors[] = 'Email address is already taken';
                    }
                }
                
                if (empty($errors)) {
                    $result = $user->update_user_profile($_SESSION['user_id'], $full_name, $email);
                    if ($result['success']) {
                        $success = 'Profile updated successfully!';
                        $current_user = $user->get_user_by_id($_SESSION['user_id']);
                        $_SESSION['user_name'] = $full_name;
                        $_SESSION['user_email'] = $email;
                    } else {
                        $errors[] = $result['message'] ?? 'Failed to update profile';
                    }
                }
                break;
                
            case 'change_password':
                $current_password = $_POST['current_password'] ?? '';
                $new_password = $_POST['new_password'] ?? '';
                $confirm_password = $_POST['confirm_password'] ?? '';
                
                if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
                    $errors[] = 'All password fields are required';
                } elseif (strlen($new_password) < 6) {
                    $errors[] = 'New password must be at least 6 characters long';
                } elseif ($new_password !== $confirm_password) {
                    $errors[] = 'New passwords do not match';
                } else {
                    // Verify current password
                    $login_result = $user->login($current_user['email'], $current_password);
                    if (!$login_result['success']) {
                        $errors[] = 'Current password is incorrect';
                    } else {
                        $result = $user->change_password($_SESSION['user_id'], $new_password);
                        if ($result['success']) {
                            $success = 'Password changed successfully!';
                        } else {
                            $errors[] = $result['message'] ?? 'Failed to change password';
                        }
                    }
                }
                break;
                
            case 'update_notifications':
                $email_notifications = isset($_POST['email_notifications']) ? 1 : 0;
                $desktop_notifications = isset($_POST['desktop_notifications']) ? 1 : 0;
                
                $result = $user->update_notification_settings($_SESSION['user_id'], $email_notifications, $desktop_notifications);
                if ($result['success']) {
                    $success = 'Notification settings updated successfully!';
                    $current_user = $user->get_user_by_id($_SESSION['user_id']);
                } else {
                    $errors[] = $result['message'] ?? 'Failed to update notification settings';
                }
                break;
        }
    }
}

$page_title = 'Settings';
$csrf_token = generate_csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="container-fluid" style="padding-top: 80px;">
        <div class="row">
            <?php include 'includes/sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">
                        <i class="fas fa-cog me-2 text-primary"></i>Settings
                    </h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="dashboard.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Dashboard
                        </a>
                    </div>
                </div>
                
                <?php if (!empty($success)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <strong>Success!</strong> <?php echo htmlspecialchars($success); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <strong>Error!</strong>
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <div class="row">
                    <!-- Profile Settings -->
                    <div class="col-lg-8 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-user me-2"></i>Profile Information
                                </h5>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="">
                                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                    <input type="hidden" name="action" value="update_profile">
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="full_name" class="form-label fw-semibold">Full Name</label>
                                            <input type="text" class="form-control" id="full_name" name="full_name" 
                                                   value="<?php echo htmlspecialchars($current_user['full_name']); ?>" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="email" class="form-label fw-semibold">Email Address</label>
                                            <input type="email" class="form-control" id="email" name="email" 
                                                   value="<?php echo htmlspecialchars($current_user['email']); ?>" required>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-semibold">User Role</label>
                                            <input type="text" class="form-control" value="<?php echo ucfirst($current_user['role']); ?>" readonly>
                                            <div class="form-text">Your role cannot be changed</div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-semibold">Member Since</label>
                                            <input type="text" class="form-control" value="<?php echo format_date($current_user['created_at']); ?>" readonly>
                                        </div>
                                    </div>
                                    
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-2"></i>Update Profile
                                        </button>
                                        <button type="reset" class="btn btn-outline-secondary">
                                            <i class="fas fa-undo me-2"></i>Reset
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <!-- User Avatar -->
                    <div class="col-lg-4 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-image me-2"></i>Profile Picture
                                </h5>
                            </div>
                            <div class="card-body text-center">
                                <div class="mb-3">
                                    <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center mx-auto" 
                                         style="width: 120px; height: 120px;">
                                        <i class="fas fa-user fa-3x text-primary"></i>
                                    </div>
                                </div>
                                <h6 class="fw-semibold"><?php echo htmlspecialchars($current_user['full_name']); ?></h6>
                                <p class="text-muted"><?php echo htmlspecialchars($current_user['email']); ?></p>
                                <button class="btn btn-outline-primary btn-sm" disabled>
                                    <i class="fas fa-camera me-2"></i>Change Avatar
                                </button>
                                <div class="form-text text-muted mt-2">Avatar upload coming soon</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <!-- Password Settings -->
                    <div class="col-lg-8 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-lock me-2"></i>Change Password
                                </h5>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="">
                                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                    <input type="hidden" name="action" value="change_password">
                                    
                                    <div class="mb-3">
                                        <label for="current_password" class="form-label fw-semibold">Current Password</label>
                                        <input type="password" class="form-control" id="current_password" name="current_password" required>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="new_password" class="form-label fw-semibold">New Password</label>
                                            <input type="password" class="form-control" id="new_password" name="new_password" required>
                                            <div class="form-text">Minimum 6 characters</div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="confirm_password" class="form-label fw-semibold">Confirm New Password</label>
                                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                        </div>
                                    </div>
                                    
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-key me-2"></i>Change Password
                                        </button>
                                        <button type="reset" class="btn btn-outline-secondary">
                                            <i class="fas fa-undo me-2"></i>Clear
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Notification Settings -->
                    <div class="col-lg-4 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-bell me-2"></i>Notifications
                                </h5>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="">
                                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                    <input type="hidden" name="action" value="update_notifications">
                                    
                                    <div class="mb-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="email_notifications" 
                                                   name="email_notifications" <?php echo ($current_user['email_notifications'] ?? 1) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="email_notifications">
                                                <strong>Email Notifications</strong>
                                                <div class="form-text">Receive updates via email</div>
                                            </label>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="desktop_notifications" 
                                                   name="desktop_notifications" <?php echo ($current_user['desktop_notifications'] ?? 0) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="desktop_notifications">
                                                <strong>Desktop Notifications</strong>
                                                <div class="form-text">Browser notifications</div>
                                            </label>
                                        </div>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-primary btn-sm">
                                        <i class="fas fa-save me-2"></i>Save Settings
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Account Actions -->
                <div class="row">
                    <div class="col-12">
                        <div class="card border-danger">
                            <div class="card-header bg-danger bg-opacity-10 border-danger">
                                <h5 class="mb-0 text-danger">
                                    <i class="fas fa-exclamation-triangle me-2"></i>Danger Zone
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-md-8">
                                        <h6 class="fw-semibold">Delete Account</h6>
                                        <p class="text-muted mb-0">Once you delete your account, there is no going back. Please be certain.</p>
                                    </div>
                                    <div class="col-md-4 text-md-end">
                                        <button class="btn btn-outline-danger" onclick="confirmDeleteAccount()">
                                            <i class="fas fa-trash me-2"></i>Delete Account
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://kit.fontawesome.com/your-fontawesome-kit.js" crossorigin="anonymous"></script>
    <script src="js/main.js"></script>
    
    <script>
        // Password confirmation validation
        const newPassword = document.getElementById('new_password');
        const confirmPassword = document.getElementById('confirm_password');
        
        if (newPassword && confirmPassword) {
            function validatePasswordMatch() {
                if (confirmPassword.value && newPassword.value !== confirmPassword.value) {
                    confirmPassword.setCustomValidity('Passwords do not match');
                } else {
                    confirmPassword.setCustomValidity('');
                }
            }
            
            newPassword.addEventListener('input', validatePasswordMatch);
            confirmPassword.addEventListener('input', validatePasswordMatch);
        }
        
        // Delete account confirmation
        function confirmDeleteAccount() {
            if (confirm('Are you sure you want to delete your account? This action cannot be undone.')) {
                if (confirm('This will permanently delete all your data including incidents and reports. Are you absolutely sure?')) {
                    alert('Account deletion functionality will be implemented soon.');
                }
            }
        }
        
        // Form submission loading states
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function(e) {
                const submitBtn = this.querySelector('button[type="submit"]');
                if (submitBtn && !submitBtn.disabled) {
                    const originalText = submitBtn.innerHTML;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Saving...';
                    submitBtn.disabled = true;
                    
                    // Reset button after 5 seconds (in case of error)
                    setTimeout(() => {
                        submitBtn.innerHTML = originalText;
                        submitBtn.disabled = false;
                    }, 5000);
                }
            });
        });
        
        // Email validation feedback
        const emailInput = document.getElementById('email');
        if (emailInput) {
            emailInput.addEventListener('input', function() {
                const email = this.value;
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                
                if (email && !emailRegex.test(email)) {
                    this.classList.add('is-invalid');
                } else {
                    this.classList.remove('is-invalid');
                }
            });
        }
    </script>
</body>
</html>
