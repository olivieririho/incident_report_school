<?php
/**
 * Secure School Incident Reporting Platform
 * Student/Staff Dashboard
 */

require_once 'config.php';
require_once 'functions.php';

require_login();

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'];

$incident = new Incident();
$notification = new Notification();

// Get user-specific data
if ($user_role === 'student') {
    $incidents = $incident->get_incidents_by_user($user_id);
    $page_title = 'Student Dashboard';
} else {
    $incidents = $incident->get_assigned_incidents($user_id);
    $page_title = 'Staff Dashboard';
}

$notifications = $notification->get_user_notifications($user_id, 5);
$unread_count = $notification->get_unread_count($user_id);
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
                    <h1 class="h2"><?php echo $page_title; ?></h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <?php if ($user_role === 'student'): ?>
                            <a href="report.php" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Report Incident
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Professional Student Dashboard -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card bg-gradient-primary text-white border-0">
                            <div class="card-body py-4">
                                <div class="row align-items-center">
                                    <div class="col-md-8">
                                        <h2 class="mb-2">Welcome back, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</h2>
                                        <p class="mb-0 opacity-75">
                                            <?php echo $user_role === 'student' ? 
                                                'Your safety is our priority. Report any incidents and track their progress here.' : 
                                                'Manage your assigned incidents and help maintain a safe school environment.'; ?>
                                        </p>
                                    </div>
                                    <div class="col-md-4 text-md-end">
                                        <?php if ($user_role === 'student'): ?>
                                            <a href="report.php" class="btn btn-light btn-lg">
                                                <i class="fas fa-plus"></i> Report Incident
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Statistics Overview -->
                <div class="row mb-4">
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="stat-card primary">
                            <div class="stat-value"><?php echo count($incidents); ?></div>
                            <div class="stat-label">
                                <?php echo $user_role === 'student' ? 'Total Reports' : 'Assigned Cases'; ?>
                            </div>
                            <div class="stat-icon">
                                <i class="fas fa-file-alt"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="stat-card warning">
                            <div class="stat-value">
                                <?php 
                                $pending = array_filter($incidents, function($i) {
                                    return in_array($i['status'], ['new', 'under_review']);
                                });
                                echo count($pending);
                                ?>
                            </div>
                            <div class="stat-label">Pending</div>
                            <div class="stat-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="stat-card info">
                            <div class="stat-value">
                                <?php 
                                $investigating = array_filter($incidents, function($i) {
                                    return $i['status'] === 'investigating';
                                });
                                echo count($investigating);
                                ?>
                            </div>
                            <div class="stat-label">Investigating</div>
                            <div class="stat-icon">
                                <i class="fas fa-search"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="stat-card success">
                            <div class="stat-value">
                                <?php 
                                $resolved = array_filter($incidents, function($i) {
                                    return in_array($i['status'], ['resolved', 'closed']);
                                });
                                echo count($resolved);
                                ?>
                            </div>
                            <div class="stat-label">Resolved</div>
                            <div class="stat-icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Main Content Area -->
                <div class="row">
                    <div class="col-lg-8">
                        <!-- Recent Incidents with Modern Design -->
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">
                                    <?php echo $user_role === 'student' ? 'My Recent Reports' : 'Assigned Incidents'; ?>
                                </h5>
                                <div>
                                    <span class="badge bg-primary me-2"><?php echo count($incidents); ?> Total</span>
                                    <a href="incidents.php" class="btn btn-sm btn-outline-primary">View All</a>
                                </div>
                            </div>
                            <div class="card-body">
                                <?php if (empty($incidents)): ?>
                                    <div class="text-center py-5">
                                        <div class="empty-state">
                                            <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                                            <h5 class="text-muted">
                                                <?php echo $user_role === 'student' ? 'No incidents reported yet' : 'No incidents assigned to you'; ?>
                                            </h5>
                                            <p class="text-muted mb-4">
                                                <?php echo $user_role === 'student' ? 
                                                    'Start by reporting your first incident to help maintain school safety.' : 
                                                    'Check back later for newly assigned incidents.'; ?>
                                            </p>
                                            <?php if ($user_role === 'student'): ?>
                                                <a href="report.php" class="btn btn-primary btn-lg">
                                                    <i class="fas fa-plus"></i> Report Your First Incident
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="incident-list">
                                        <?php 
                                        $display_incidents = array_slice($incidents, 0, 5);
                                        foreach ($display_incidents as $incident_item): 
                                        ?>
                                            <div class="incident-item card mb-3 border-0 shadow-sm">
                                                <div class="card-body">
                                                    <div class="row align-items-center">
                                                        <div class="col-md-8">
                                                            <div class="d-flex align-items-center mb-2">
                                                                <h6 class="mb-0 me-2">
                                                                    <?php echo htmlspecialchars($incident_item['title']); ?>
                                                                </h6>
                                                                <?php if ($incident_item['anonymous']): ?>
                                                                    <span class="badge bg-secondary">Anonymous</span>
                                                                <?php endif; ?>
                                                            </div>
                                                            <div class="d-flex gap-2 mb-2">
                                                                <?php echo get_category_label($incident_item['category']); ?>
                                                                <?php echo get_priority_badge($incident_item['priority']); ?>
                                                                <?php echo get_status_badge($incident_item['status']); ?>
                                                            </div>
                                                            <div class="text-muted small">
                                                                <i class="fas fa-calendar"></i> <?php echo format_date($incident_item['created_at']); ?>
                                                                <span class="ms-3"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($incident_item['location']); ?></span>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4 text-md-end">
                                                            <div class="d-flex flex-column align-items-md-end gap-2">
                                                                <span class="text-muted small">ID: #<?php echo str_pad($incident_item['id'], 6, '0', STR_PAD_LEFT); ?></span>
                                                                <a href="incident_details.php?id=<?php echo $incident_item['id']; ?>" 
                                                                   class="btn btn-sm btn-primary">
                                                                    <i class="fas fa-eye"></i> View Details
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    
                                    <?php if (count($incidents) > 5): ?>
                                        <div class="text-center mt-3">
                                            <a href="incidents.php" class="btn btn-outline-primary">
                                                View All <?php echo count($incidents); ?> Incidents
                                                <i class="fas fa-arrow-right ms-2"></i>
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Sidebar Content -->
                    <div class="col-lg-4">
                        <!-- Notifications Panel -->
                        <div class="card mb-4">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Notifications</h5>
                                <?php if ($unread_count > 0): ?>
                                    <span class="badge bg-danger pulse-animation"><?php echo $unread_count; ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="card-body" style="max-height: 300px; overflow-y: auto;">
                                <?php if (empty($notifications)): ?>
                                    <div class="text-center py-3">
                                        <i class="fas fa-bell-slash fa-2x text-muted mb-2"></i>
                                        <p class="text-muted small">No notifications</p>
                                    </div>
                                <?php else: ?>
                                    <div class="notification-feed">
                                        <?php foreach ($notifications as $notif): ?>
                                            <div class="notification-item <?php echo $notif['seen'] ? '' : 'unread'; ?> mb-3 p-3 border rounded">
                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                    <small class="text-muted">
                                                        <i class="fas fa-clock"></i> <?php echo format_datetime($notif['created_at']); ?>
                                                    </small>
                                                    <?php if (!$notif['seen']): ?>
                                                        <span class="badge bg-primary">New</span>
                                                    <?php endif; ?>
                                                </div>
                                                <p class="mb-2 small"><?php echo htmlspecialchars($notif['message']); ?></p>
                                                <?php if ($notif['incident_id']): ?>
                                                    <a href="incident_details.php?id=<?php echo $notif['incident_id']; ?>" 
                                                       class="btn btn-xs btn-outline-primary">
                                                        <i class="fas fa-external-link-alt"></i> View
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Quick Actions Panel -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">Quick Actions</h5>
                            </div>
                            <div class="card-body">
                                <div class="d-grid gap-2">
                                    <?php if ($user_role === 'student'): ?>
                                        <a href="report.php" class="btn btn-primary">
                                            <i class="fas fa-plus"></i> New Report
                                        </a>
                                        <a href="incidents.php" class="btn btn-outline-primary">
                                            <i class="fas fa-list"></i> View All Reports
                                        </a>
                                        <button class="btn btn-outline-info" onclick="showSafetyGuide()">
                                            <i class="fas fa-shield-alt"></i> Safety Guide
                                        </button>
                                    <?php else: ?>
                                        <a href="incidents.php" class="btn btn-primary">
                                            <i class="fas fa-list"></i> All Incidents
                                        </a>
                                        <a href="report.php" class="btn btn-outline-primary">
                                            <i class="fas fa-plus"></i> Report Incident
                                        </a>
                                        <button class="btn btn-outline-success" onclick="showAssignmentStats()">
                                            <i class="fas fa-chart-pie"></i> Assignment Stats
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Safety Information -->
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Emergency Contacts</h5>
                            </div>
                            <div class="card-body">
                                <div class="emergency-contacts">
                                    <div class="contact-item d-flex justify-content-between align-items-center mb-2">
                                        <span><i class="fas fa-phone text-danger"></i> Emergency</span>
                                        <strong>911</strong>
                                    </div>
                                    <div class="contact-item d-flex justify-content-between align-items-center mb-2">
                                        <span><i class="fas fa-shield-alt text-primary"></i> Security</span>
                                        <strong>Ext. 1234</strong>
                                    </div>
                                    <div class="contact-item d-flex justify-content-between align-items-center">
                                        <span><i class="fas fa-user-nurse text-success"></i> Nurse</span>
                                        <strong>Ext. 5678</strong>
                                    </div>
                                </div>
                                <hr>
                                <div class="text-center">
                                    <small class="text-muted">
                                        <i class="fas fa-info-circle"></i> 
                                        For immediate threats, call emergency services first
                                    </small>
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
</body>
</html>
