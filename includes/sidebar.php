<?php
/**
 * Secure School Incident Reporting Platform
 * Sidebar Navigation Component
 */

require_once 'config.php';
require_once 'functions.php';
?>
<div class="sidebar d-print-none">
    <div class="position-sticky">
        <!-- User Profile Section -->
        <?php if (is_logged_in()): ?>
            <div class="px-4 pb-4 mb-4 border-bottom border-white-10">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="rounded-circle bg-white-10 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="fas fa-user text-white fa-lg"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="text-white mb-0 fw-semibold"><?php echo htmlspecialchars($_SESSION['user_name']); ?></h6>
                        <small class="text-gray-400"><?php echo ucfirst($_SESSION['user_role']); ?></small>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : ''; ?>" 
                   href="dashboard.php">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
            </li>
            
            <?php if (is_admin()): ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'admin_dashboard.php' ? 'active' : ''; ?>" 
                       href="admin_dashboard.php">
                        <i class="fas fa-chart-bar"></i> Admin Dashboard
                    </a>
                </li>
            <?php endif; ?>
            
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'incidents.php' ? 'active' : ''; ?>" 
                   href="incidents.php">
                    <i class="fas fa-list"></i> Incidents
                </a>
            </li>
            
            <?php if (is_student() || is_staff()): ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'report.php' ? 'active' : ''; ?>" 
                       href="report.php">
                        <i class="fas fa-plus-circle me-2"></i>Report Incident
                    </a>
                </li>
            <?php endif; ?>
            
            <li class="nav-heading">Management</li>
            
            <?php if (is_admin()): ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'users.php' ? 'active' : ''; ?>" 
                       href="users.php">
                        <i class="fas fa-users"></i> Users
                    </a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'reports.php' ? 'active' : ''; ?>" 
                       href="reports.php">
                        <i class="fas fa-chart-line"></i> Reports
                    </a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'settings.php' ? 'active' : ''; ?>" 
                       href="settings.php">
                        <i class="fas fa-cog"></i> Settings
                    </a>
                </li>
            <?php endif; ?>
            
            <?php if (is_staff()): ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'my_cases.php' ? 'active' : ''; ?>" 
                       href="incidents.php">
                        <i class="fas fa-briefcase"></i> My Cases
                    </a>
                </li>
            <?php endif; ?>
            
            <?php if (is_student()): ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'my_reports.php' ? 'active' : ''; ?>" 
                       href="incidents.php">
                        <i class="fas fa-file-alt"></i> My Reports
                    </a>
                </li>
            <?php endif; ?>
            
            <li class="nav-heading">Support</li>
            
            <li class="nav-item">
                <a class="nav-link" href="help.php">
                    <i class="fas fa-question-circle"></i> Help
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link" href="contact.php">
                    <i class="fas fa-envelope"></i> Contact
                </a>
            </li>
        </ul>
        
        <!-- Quick Stats -->
        <?php if (is_logged_in()): ?>
            <div class="mt-4 p-3 bg-light rounded">
                <h6 class="sidebar-heading">
                    <i class="fas fa-chart-pie me-2"></i>Quick Stats
                </h6>
                <?php
                $incident = new Incident();
                if (is_admin()) {
                    $stats = $incident->get_statistics();
                    echo '<div class="small">';
                    echo '<div><i class="fas fa-file-alt me-2"></i>Total: ' . $stats['total_incidents'] . '</div>';
                    echo '<div><i class="fas fa-exclamation-triangle me-2"></i>New: ' . ($stats['by_status'][0]['count'] ?? 0) . '</div>';
                    echo '<div><i class="fas fa-check-circle me-2"></i>Today: ' . $stats['resolved_today'] . '</div>';
                    echo '</div>';
                } elseif (is_staff()) {
                    $assigned = $incident->get_assigned_incidents($_SESSION['user_id']);
                    $pending = array_filter($assigned, function($i) {
                        return in_array($i['status'], ['new', 'under_review']);
                    });
                    echo '<div class="small">';
                    echo '<div><i class="fas fa-briefcase me-2"></i>Assigned: ' . count($assigned) . '</div>';
                    echo '<div><i class="fas fa-clock me-2"></i>Pending: ' . count($pending) . '</div>';
                    echo '</div>';
                } else {
                    $my_incidents = $incident->get_incidents_by_user($_SESSION['user_id']);
                    $resolved = array_filter($my_incidents, function($i) {
                        return in_array($i['status'], ['resolved', 'closed']);
                    });
                    echo '<div class="small">';
                    echo '<div><i class="fas fa-file-alt me-2"></i>Reports: ' . count($my_incidents) . '</div>';
                    echo '<div><i class="fas fa-check-circle me-2"></i>Resolved: ' . count($resolved) . '</div>';
                    echo '</div>';
                }
                ?>
            </div>
        <?php endif; ?>
        
        <!-- Emergency Contact -->
        <div class="mt-4 p-3 bg-danger text-white rounded">
            <h6 class="text-white mb-3">
                <i class="fas fa-exclamation-triangle me-2"></i>Emergency
            </h6>
            <div class="small">
                <div class="mb-2"><i class="fas fa-phone me-2"></i> 911</div>
                <div class="mb-2"><i class="fas fa-shield-alt me-2"></i> Security: Ext. 1234</div>
                <div><i class="fas fa-user-nurse me-2"></i> Nurse: Ext. 5678</div>
            </div>
        </div>
    </div>
</div>
