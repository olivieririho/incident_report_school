<?php
/**
 * Secure School Incident Reporting Platform
 * User Registration Page
 */

require_once 'config.php';
require_once 'functions.php';

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = sanitize_input($_POST['full_name'] ?? '');
    $email = sanitize_input($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $role = sanitize_input($_POST['role'] ?? 'student');
    
    // Validate CSRF token
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        // Validate inputs
        if (empty($full_name)) {
            $errors[] = 'Full name is required';
        }
        
        if (empty($email)) {
            $errors[] = 'Email is required';
        } elseif (!validate_email($email)) {
            $errors[] = 'Invalid email address';
        }
        
        if (empty($password)) {
            $errors[] = 'Password is required';
        } elseif (strlen($password) < 6) {
            $errors[] = 'Password must be at least 6 characters long';
        }
        
        if ($password !== $confirm_password) {
            $errors[] = 'Passwords do not match';
        }
        
        if (empty($errors)) {
            $user = new User();
            $result = $user->register($full_name, $email, $password, $role);
            
            if ($result['success']) {
                $success = $result['message'];
                // Redirect to login after successful registration
                header('refresh:2;url=login.php');
            } else {
                $errors[] = $result['message'];
            }
        }
    }
}

$csrf_token = generate_csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <style>
        .register-container {
            min-height: 100vh;
            background: linear-gradient(135deg, var(--primary-600) 0%, var(--primary-800) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        
        .register-card {
            background: white;
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow-xl);
            overflow: hidden;
            max-width: 500px;
            width: 100%;
        }
        
        .register-header {
            background: linear-gradient(135deg, var(--primary-50) 0%, white 100%);
            padding: 2.5rem 2rem;
            text-align: center;
        }
        
        .register-header i {
            font-size: 3rem;
            color: var(--primary-600);
            margin-bottom: 1rem;
        }
        
        .register-body {
            padding: 2.5rem 2rem;
        }
        
        @media (max-width: 576px) {
            .register-container {
                padding: 1rem;
            }
            
            .register-card {
                max-width: 100%;
            }
            
            .register-header, .register-body {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-card">
            <div class="register-header">
                <i class="fas fa-user-plus"></i>
                <h2 class="fw-bold mb-2">Create Account</h2>
                <p class="text-muted mb-0">Join our secure incident reporting platform</p>
            </div>
            
            <div class="register-body">
                <?php if (!empty($success)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-check-circle me-2"></i>
                            <div>
                                <strong>Success!</strong> <?php echo htmlspecialchars($success); ?>
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <div>
                                <strong>Error!</strong>
                                <ul class="mb-0 mt-1">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?php echo htmlspecialchars($error); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    
                    <div class="mb-3">
                        <label for="full_name" class="form-label fw-semibold">Full Name</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light">
                                <i class="fas fa-user text-muted"></i>
                            </span>
                            <input type="text" class="form-control" id="full_name" name="full_name" 
                                   value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>" 
                                   placeholder="Enter your full name" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label fw-semibold">Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light">
                                <i class="fas fa-envelope text-muted"></i>
                            </span>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" 
                                   placeholder="Enter your email" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="role" class="form-label fw-semibold">Role</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light">
                                <i class="fas fa-user-tag text-muted"></i>
                            </span>
                            <select class="form-select" id="role" name="role" required>
                                <option value="student" <?php echo (($_POST['role'] ?? 'student') === 'student') ? 'selected' : ''; ?>>Student</option>
                                <option value="staff" <?php echo (($_POST['role'] ?? '') === 'staff') ? 'selected' : ''; ?>>Staff/Teacher</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label fw-semibold">Password</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light">
                                <i class="fas fa-lock text-muted"></i>
                            </span>
                            <input type="password" class="form-control" id="password" name="password" 
                                   placeholder="Create a password" required>
                        </div>
                        <div class="form-text text-muted">Password must be at least 6 characters long</div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="confirm_password" class="form-label fw-semibold">Confirm Password</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light">
                                <i class="fas fa-lock text-muted"></i>
                            </span>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                   placeholder="Confirm your password" required>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100 py-2.5 mb-3 fw-semibold">
                        <i class="fas fa-user-plus me-2"></i> Create Account
                    </button>
                    
                    <div class="text-center">
                        <p class="mb-0 text-muted">Already have an account? 
                            <a href="login.php" class="text-primary fw-semibold text-decoration-none">Login here</a>
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://kit.fontawesome.com/your-fontawesome-kit.js" crossorigin="anonymous"></script>
    <script src="js/main.js"></script>
</body>
</html>
