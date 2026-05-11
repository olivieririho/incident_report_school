<?php
/**
 * Secure School Incident Reporting Platform
 * Professional Sidebar Navigation Component
 */

require_once 'config.php';
require_once 'functions.php';

// Determine current dashboard based on role
$dashboard_url = 'dashboard.php';
$current_page = basename($_SERVER['PHP_SELF']);

if (is_staff()) {
    $dashboard_url = 'staff_dashboard.php';
}
?>
<nav id="sidebar" class="sidebar d-print-none">
    <div class="sidebar-header">
        <a href="<?php echo $dashboard_url; ?>" class="sidebar-brand">
            <i class="fas fa-shield-alt"></i>
            <span>SecureSchool</span>
        </a>
    </div>
    
    <?php if (is_logged_in()): ?>
    <!-- User Profile Card -->
    <div class="sidebar-user">
        <div class="user-avatar">
            <div class="avatar-circle">
                <i class="fas fa-user"></i>
            </div>
        </div>
        <div class="user-info">
            <h6 class="user-name"><?php echo htmlspecialchars($_SESSION['user_name']); ?></h6>
            <span class="user-role badge bg-primary"><?php echo ucfirst($_SESSION['user_role']); ?></span>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Navigation Menu -->
    <ul class="sidebar-nav">
        <!-- Main Dashboard Link -->
        <li class="nav-item">
            <a class="nav-link <?php echo ($current_page === 'dashboard.php' || $current_page === 'staff_dashboard.php') ? 'active' : ''; ?>" 
               href="<?php echo $dashboard_url; ?>">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
        </li>
        
        <?php if (is_student() || is_staff()): ?>
        <!-- Report Incident -->
        <li class="nav-item">
            <a class="nav-link <?php echo $current_page === 'report.php' ? 'active' : ''; ?>" href="report.php">
                <i class="fas fa-plus-circle"></i>
                <span>Report Incident</span>
            </a>
        </li>
        <?php endif; ?>
        
        <!-- Personal Section -->
        <li class="nav-heading">Personal</li>
        
        <li class="nav-item">
            <a class="nav-link <?php echo $current_page === 'my_reports.php' ? 'active' : ''; ?>" href="my_reports.php">
                <i class="fas fa-file-alt"></i>
                <span>My Reports</span>
            </a>
        </li>
        
        <li class="nav-item">
            <a class="nav-link <?php echo $current_page === 'settings.php' ? 'active' : ''; ?>" href="settings.php">
                <i class="fas fa-cog"></i>
                <span>Settings</span>
            </a>
        </li>
        
        <!-- Admin Section (Admin Only) -->
        <?php if (is_admin()): ?>
        <li class="nav-heading">Administration</li>
        
        <li class="nav-item">
            <a class="nav-link <?php echo $current_page === 'users.php' ? 'active' : ''; ?>" href="users.php">
                <i class="fas fa-users"></i>
                <span>Manage Users</span>
            </a>
        </li>
        
        <li class="nav-item">
            <a class="nav-link <?php echo $current_page === 'admin_dashboard.php' ? 'active' : ''; ?>" href="admin_dashboard.php">
                <i class="fas fa-chart-line"></i>
                <span>Analytics</span>
            </a>
        </li>
        
        <li class="nav-item">
            <a class="nav-link <?php echo in_array($current_page, ['email_templates.php', 'email_logs.php']) ? 'active' : ''; ?>" href="email_templates.php">
                <i class="fas fa-envelope"></i>
                <span>Email System</span>
            </a>
        </li>
        <?php endif; ?>
        
        <!-- Support Section -->
        <li class="nav-heading">Support</li>
        
        <li class="nav-item">
            <a class="nav-link" href="help.php">
                <i class="fas fa-question-circle"></i>
                <span>Help Center</span>
            </a>
        </li>
        
        <li class="nav-item">
            <a class="nav-link" href="logout.php">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </li>
    </ul>
    
    <!-- Emergency Contact Card -->
    <div class="emergency-card">
        <div class="emergency-header">
            <i class="fas fa-exclamation-triangle"></i>
            <span>Emergency</span>
        </div>
        <div class="emergency-body">
            <div class="emergency-item">
                <i class="fas fa-phone"></i>
                <span>911</span>
            </div>
            <div class="emergency-item">
                <i class="fas fa-shield-alt"></i>
                <span>Security: 1234</span>
            </div>
        </div>
    </div>
    
    <!-- Sidebar Footer -->
    <div class="sidebar-footer">
        <small>&copy; <?php echo date('Y'); ?> SecureSchool</small>
    </div>
</nav>
