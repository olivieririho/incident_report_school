<?php
/**
 * Secure School Incident Reporting Platform
 * Navigation Bar Component
 */

require_once 'config.php';
require_once 'functions.php';

$notification = new Notification();
$unread_count = is_logged_in() ? $notification->get_unread_count($_SESSION['user_id']) : 0;
$notifications = $notification->get_user_notifications($_SESSION['user_id'], 5);
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
    <div class="container-fluid">
        <button class="btn btn-outline-light d-lg-none me-2 mobile-menu-toggle" id="mobileMenuToggle">
            <i class="fas fa-bars"></i>
        </button>
        <a class="navbar-brand" href="dashboard.php">
            <i class="fas fa-shield-alt me-2"></i><?php echo APP_NAME; ?>
        </a>
        <button class="navbar-toggler d-lg-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <?php if (is_logged_in()): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">
                            <i class="fas fa-home"></i> Dashboard
                        </a>
                    </li>
                    <?php if (is_admin()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="admin_dashboard.php">
                                <i class="fas fa-chart-bar"></i> Admin
                            </a>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link" href="incidents.php">
                            <i class="fas fa-list"></i> Incidents
                        </a>
                    </li>
                    <?php if (is_student() || is_staff()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="report.php">
                                <i class="fas fa-plus"></i> Report
                            </a>
                        </li>
                    <?php endif; ?>
                    
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="notificationDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-bell"></i>
                            <?php if ($unread_count > 0): ?>
                                <span class="badge bg-danger notification-badge"><?php echo $unread_count; ?></span>
                            <?php endif; ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <?php if (empty($notifications)): ?>
                                <li><span class="dropdown-item text-muted">No notifications</span></li>
                            <?php else: ?>
                                <?php foreach ($notifications as $notif): ?>
                                    <li>
                                        <a class="dropdown-item <?php echo $notif['seen'] ? '' : 'bg-light'; ?>" 
                                           href="<?php echo $notif['incident_id'] ? 'incident_details.php?id=' . $notif['incident_id'] : '#'; ?>">
                                            <div class="d-flex justify-content-between">
                                                <small><?php echo htmlspecialchars($notif['message']); ?></small>
                                                <?php if (!$notif['seen']): ?>
                                                    <span class="badge bg-primary">New</span>
                                                <?php endif; ?>
                                            </div>
                                            <small class="text-muted"><?php echo format_datetime($notif['created_at']); ?></small>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item text-center" href="incidents.php">
                                        View All Notifications
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </li>
                    
                    <!-- User Menu -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user"></i> <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><h6 class="dropdown-header">
                                <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                                <br><small class="text-muted"><?php echo ucfirst($_SESSION['user_role']); ?></small>
                            </h6></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item" href="report.php">
                                    <i class="fas fa-plus me-2"></i>Report Incident
                                </a>
                            </li>
                            <?php if (is_admin()): ?>
                                <li>
                                    <a class="dropdown-item" href="users.php">
                                        <i class="fas fa-users"></i> Manage Users
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="reports.php">
                                        <i class="fas fa-chart-line"></i> Reports & Analytics
                                    </a>
                                </li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-danger" href="logout.php">
                                    <i class="fas fa-sign-out-alt"></i> Logout
                                </a>
                            </li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="register.php">
                            <i class="fas fa-user-plus"></i> Register
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<div class="sidebar-overlay" id="sidebarOverlay"></div>
