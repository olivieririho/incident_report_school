<?php
/**
 * Secure School Incident Reporting Platform
 * User Login Page
 */

require_once 'config.php';
require_once 'functions.php';

// Redirect if already logged in
if (is_logged_in()) {
    header('Location: dashboard.php');
    exit();
}

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize_input($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Validate CSRF token
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        if (empty($email) || empty($password)) {
            $errors[] = 'Email and password are required';
        } else {
            $user = new User();
            $result = $user->login($email, $password);
            
            if ($result['success']) {
                $success = $result['message'];
                // Redirect to appropriate dashboard
                switch ($result['role']) {
                    case 'admin':
                        header('Location: admin_dashboard.php');
                        break;
                    case 'staff':
                        header('Location: dashboard.php');
                        break;
                    default:
                        header('Location: dashboard.php');
                        break;
                }
                exit();
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
    <title>Login - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <style>
        .login-container {
            min-height: 100vh;
            background: linear-gradient(135deg, var(--primary-600) 0%, var(--primary-800) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        
        .login-card {
            background: white;
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow-xl);
            overflow: hidden;
            max-width: 450px;
            width: 100%;
        }
        
        .login-header {
            background: linear-gradient(135deg, var(--primary-50) 0%, white 100%);
            padding: 2.5rem 2rem;
            text-align: center;
        }
        
        .login-header i {
            font-size: 3rem;
            color: var(--primary-600);
            margin-bottom: 1rem;
        }
        
        .login-body {
            padding: 2.5rem 2rem;
        }
        
        .demo-accounts {
            background: var(--gray-50);
            border-radius: var(--border-radius);
            padding: 1.5rem;
            margin-top: 1.5rem;
        }
        
        .demo-item {
            padding: 0.5rem 0;
            border-bottom: 1px solid var(--gray-200);
        }
        
        .demo-item:last-child {
            border-bottom: none;
        }
        
        @media (max-width: 576px) {
            .login-container {
                padding: 1rem;
            }
            
            .login-card {
                max-width: 100%;
            }
            
            .login-header, .login-body {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <i class="fas fa-shield-alt"></i>
                <h2 class="fw-bold mb-2">Welcome Back</h2>
                <p class="text-muted mb-0">Secure School Incident Reporting</p>
            </div>
            
            <div class="login-body">
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
                        <label for="password" class="form-label fw-semibold">Password</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light">
                                <i class="fas fa-lock text-muted"></i>
                            </span>
                            <input type="password" class="form-control" id="password" name="password" 
                                   placeholder="Enter your password" required>
                        </div>
                    </div>
                    
                    <div class="mb-3 d-flex justify-content-between align-items-center">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="remember">
                            <label class="form-check-label" for="remember">Remember me</label>
                        </div>
                        <a href="#" class="text-primary text-decoration-none small">Forgot password?</a>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100 py-2.5 mb-3 fw-semibold">
                        <i class="fas fa-sign-in-alt me-2"></i> Login
                    </button>
                    
                    <div class="text-center">
                        <p class="mb-0 text-muted">Don't have an account? 
                            <a href="register.php" class="text-primary fw-semibold text-decoration-none">Register here</a>
                        </p>
                    </div>
                </form>
                
                <div class="demo-accounts">
                    <h6 class="fw-semibold mb-3">
                        <i class="fas fa-user-circle me-2"></i>Demo Accounts
                    </h6>
                    <div class="demo-item">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Admin</span>
                            <small class="text-muted">admin@school.edu</small>
                        </div>
                        <small class="text-muted">admin123</small>
                    </div>
                    <div class="demo-item">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Student</span>
                            <small class="text-muted">john@school.edu</small>
                        </div>
                        <small class="text-muted">password</small>
                    </div>
                    <div class="demo-item">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Staff</span>
                            <small class="text-muted">jane@school.edu</small>
                        </div>
                        <small class="text-muted">password</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://kit.fontawesome.com/your-fontawesome-kit.js" crossorigin="anonymous"></script>
    <script src="js/main.js"></script>
</body>
</html>
