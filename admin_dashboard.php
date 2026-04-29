<?php
/**
 * Secure School Incident Reporting Platform
 * Admin Dashboard with Analytics
 */

require_once 'config.php';
require_once 'functions.php';

require_admin();

$incident = new Incident();
$notification = new Notification();
$user = new User();

// Get statistics
$stats = $incident->get_statistics();
$all_incidents = $incident->get_all_incidents();
$notifications = $notification->get_user_notifications($_SESSION['user_id'], 5);
$unread_count = $notification->get_unread_count($_SESSION['user_id']);

// Get staff users for assignment
$staff_users = $user->get_all_users('staff');

// Process filters
$filters = [];
if (isset($_GET['status']) && !empty($_GET['status'])) {
    $filters['status'] = $_GET['status'];
}
if (isset($_GET['priority']) && !empty($_GET['priority'])) {
    $filters['priority'] = $_GET['priority'];
}
if (isset($_GET['category']) && !empty($_GET['category'])) {
    $filters['category'] = $_GET['category'];
}

$filtered_incidents = $incident->get_all_incidents($filters);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="container-fluid" style="padding-top: 80px;">
        <div class="row">
            <?php include 'includes/sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <!-- Professional Header -->
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4">
                    <div>
                        <h1 class="h2 fw-bold mb-1">
                            <i class="fas fa-tachometer-alt me-2 text-primary"></i>Admin Dashboard
                        </h1>
                        <p class="text-muted mb-0">Welcome back! Here's what's happening today.</p>
                    </div>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <a href="incidents.php" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-list me-1"></i> All Incidents
                            </a>
                            <a href="report.php" class="btn btn-sm btn-primary">
                                <i class="fas fa-plus me-1"></i> New Report
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Professional Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
                        <div class="stat-card primary">
                            <div class="stat-value" data-stat-type="total_incidents"><?php echo $stats['total_incidents']; ?></div>
                            <div class="stat-label">Total Reports</div>
                            <div class="stat-icon">
                                <i class="fas fa-file-alt"></i>
                            </div>
                            <div class="stat-trend">
                                <span class="text-success"><i class="fas fa-arrow-up"></i> 12%</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
                        <div class="stat-card warning">
                            <div class="stat-value">
                                <?php 
                                $new_count = array_filter($stats['by_status'], function($s) {
                                    return $s['status'] === 'new';
                                });
                                echo $new_count[0]['count'] ?? 0;
                                ?>
                            </div>
                            <div class="stat-label">New Cases</div>
                            <div class="stat-icon">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                            <div class="stat-trend">
                                <span class="text-warning"><i class="fas fa-clock"></i> Pending</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
                        <div class="stat-card danger">
                            <div class="stat-value">
                                <?php 
                                $critical_count = array_filter($stats['by_priority'], function($s) {
                                    return $s['priority'] === 'critical';
                                });
                                echo $critical_count[0]['count'] ?? 0;
                                ?>
                            </div>
                            <div class="stat-label">Critical</div>
                            <div class="stat-icon">
                                <i class="fas fa-fire"></i>
                            </div>
                            <div class="stat-trend">
                                <span class="text-danger"><i class="fas fa-exclamation-circle"></i> Urgent</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
                        <div class="stat-card info">
                            <div class="stat-value"><?php echo $stats['resolved_today']; ?></div>
                            <div class="stat-label">Resolved Today</div>
                            <div class="stat-icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="stat-trend">
                                <span class="text-success"><i class="fas fa-check"></i> +5</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
                        <div class="stat-card success">
                            <div class="stat-value"><?php echo $stats['last_30_days']; ?></div>
                            <div class="stat-label">Last 30 Days</div>
                            <div class="stat-icon">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                            <div class="stat-trend">
                                <span class="text-success"><i class="fas fa-arrow-up"></i> 8%</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
                        <div class="stat-card secondary">
                            <div class="stat-value"><?php echo $unread_count; ?></div>
                            <div class="stat-label">Unread</div>
                            <div class="stat-icon">
                                <i class="fas fa-bell"></i>
                            </div>
                            <div class="stat-trend">
                                <span class="text-primary"><i class="fas fa-envelope"></i> Alerts</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Compact Analytics Dashboard -->
                <div class="row mb-3">
                    <div class="col-lg-4 mb-2">
                        <div class="card border-0 shadow-sm" style="height: 280px;">
                            <div class="card-header bg-white border-0 py-2">
                                <h6 class="mb-0 fw-semibold">
                                    <i class="fas fa-chart-pie me-2 text-primary"></i>Status Distribution
                                </h6>
                            </div>
                            <div class="card-body p-2">
                                <canvas id="statusChart" height="180"></canvas>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-4 mb-2">
                        <div class="card border-0 shadow-sm" style="height: 280px;">
                            <div class="card-header bg-white border-0 py-2">
                                <h6 class="mb-0 fw-semibold">
                                    <i class="fas fa-chart-bar me-2 text-warning"></i>Priority Analysis
                                </h6>
                            </div>
                            <div class="card-body p-2">
                                <canvas id="priorityChart" height="180"></canvas>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-4 mb-2">
                        <div class="card border-0 shadow-sm" style="height: 280px;">
                            <div class="card-header bg-white border-0 py-2">
                                <h6 class="mb-0 fw-semibold">
                                    <i class="fas fa-chart-line me-2 text-success"></i>Category Trends
                                </h6>
                            </div>
                            <div class="card-body p-2">
                                <canvas id="categoryChart" height="180"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Activity Feed & Quick Actions -->
                <div class="row mb-4">
                    <div class="col-lg-8 mb-3">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                                <h5 class="mb-0 fw-semibold">
                                    <i class="fas fa-history me-2 text-primary"></i>Recent Activity
                                </h5>
                                <span class="badge bg-primary rounded-pill"><?php echo count($notifications); ?> New</span>
                            </div>
                            <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                                <?php if (empty($notifications)): ?>
                                    <div class="text-center py-4">
                                        <i class="fas fa-bell-slash fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">No recent notifications</p>
                                    </div>
                                <?php else: ?>
                                    <div class="activity-timeline">
                                        <?php foreach ($notifications as $notif): ?>
                                            <div class="activity-item d-flex mb-3">
                                                <div class="flex-shrink-0">
                                                    <div class="activity-icon bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center" 
                                                         style="width: 40px; height: 40px;">
                                                        <i class="fas fa-bell"></i>
                                                    </div>
                                                </div>
                                                <div class="flex-grow-1 ms-3">
                                                    <div class="card border-0 shadow-sm">
                                                        <div class="card-body p-3">
                                                            <div class="d-flex justify-content-between align-items-start">
                                                                <div>
                                                                    <h6 class="mb-1 fw-semibold"><?php echo htmlspecialchars($notif['message']); ?></h6>
                                                                    <small class="text-muted">
                                                                        <i class="fas fa-clock me-1"></i> <?php echo format_datetime($notif['created_at']); ?>
                                                                    </small>
                                                                </div>
                                                                <?php if (!$notif['seen']): ?>
                                                                    <span class="badge bg-primary rounded-pill pulse-animation">New</span>
                                                                <?php endif; ?>
                                                            </div>
                                                            <?php if ($notif['incident_id']): ?>
                                                                <div class="mt-2">
                                                                    <a href="incident_details.php?id=<?php echo $notif['incident_id']; ?>" 
                                                                       class="btn btn-sm btn-outline-primary">
                                                                        <i class="fas fa-eye me-1"></i> View Details
                                                                    </a>
                                                                </div>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-4 mb-3">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-header bg-white border-0">
                                <h5 class="mb-0 fw-semibold">
                                    <i class="fas fa-bolt me-2 text-warning"></i>Quick Actions
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="d-grid gap-2">
                                    <button class="btn btn-primary" onclick="window.location.href='report.php'">
                                        <i class="fas fa-plus me-2"></i> New Incident Report
                                    </button>
                                    <button class="btn btn-outline-primary" onclick="window.location.href='incidents.php'">
                                        <i class="fas fa-list me-2"></i> View All Incidents
                                    </button>
                                    <button class="btn btn-outline-success" onclick="window.location.href='users.php'">
                                        <i class="fas fa-users me-2"></i> Manage Users
                                    </button>
                                    <button class="btn btn-outline-info" onclick="window.location.href='report.php'">
                                        <i class="fas fa-chart-line me-2"></i> Generate Reports
                                    </button>
                                </div>
                                
                                <hr class="my-4">
                                
                                <h6 class="text-muted mb-3 fw-semibold">
                                    <i class="fas fa-heartbeat me-2"></i>System Health
                                </h6>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <small class="text-muted">Database</small>
                                    <span class="badge bg-success rounded-pill"><i class="fas fa-check me-1"></i>Online</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <small class="text-muted">File Storage</small>
                                    <span class="badge bg-success rounded-pill"><i class="fas fa-check me-1"></i>Available</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">Last Backup</small>
                                    <span class="badge bg-warning rounded-pill"><i class="fas fa-clock me-1"></i>2 hours ago</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Incidents with Filters -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-semibold">
                            <i class="fas fa-list-alt me-2 text-primary"></i>Recent Incidents
                        </h5>
                        <div>
                            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#filterCollapse">
                                <i class="fas fa-filter me-1"></i> Filters
                            </button>
                            <a href="incidents.php" class="btn btn-sm btn-primary ms-2">
                                <i class="fas fa-arrow-right me-1"></i> View All
                            </a>
                        </div>
                    </div>
                    
                    <div class="collapse" id="filterCollapse">
                        <div class="card-body border-bottom">
                            <form method="GET" action="">
                                <div class="row">
                                    <div class="col-md-3">
                                        <label for="status" class="form-label">Status</label>
                                        <select class="form-select" id="status" name="status">
                                            <option value="">All Status</option>
                                            <option value="new" <?php echo (($_GET['status'] ?? '') === 'new') ? 'selected' : ''; ?>>New</option>
                                            <option value="under_review" <?php echo (($_GET['status'] ?? '') === 'under_review') ? 'selected' : ''; ?>>Under Review</option>
                                            <option value="investigating" <?php echo (($_GET['status'] ?? '') === 'investigating') ? 'selected' : ''; ?>>Investigating</option>
                                            <option value="resolved" <?php echo (($_GET['status'] ?? '') === 'resolved') ? 'selected' : ''; ?>>Resolved</option>
                                            <option value="closed" <?php echo (($_GET['status'] ?? '') === 'closed') ? 'selected' : ''; ?>>Closed</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="priority" class="form-label">Priority</label>
                                        <select class="form-select" id="priority" name="priority">
                                            <option value="">All Priority</option>
                                            <option value="low" <?php echo (($_GET['priority'] ?? '') === 'low') ? 'selected' : ''; ?>>Low</option>
                                            <option value="medium" <?php echo (($_GET['priority'] ?? '') === 'medium') ? 'selected' : ''; ?>>Medium</option>
                                            <option value="high" <?php echo (($_GET['priority'] ?? '') === 'high') ? 'selected' : ''; ?>>High</option>
                                            <option value="critical" <?php echo (($_GET['priority'] ?? '') === 'critical') ? 'selected' : ''; ?>>Critical</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="category" class="form-label">Category</label>
                                        <select class="form-select" id="category" name="category">
                                            <option value="">All Categories</option>
                                            <option value="bullying" <?php echo (($_GET['category'] ?? '') === 'bullying') ? 'selected' : ''; ?>>Bullying</option>
                                            <option value="physical_violence" <?php echo (($_GET['category'] ?? '') === 'physical_violence') ? 'selected' : ''; ?>>Physical Violence</option>
                                            <option value="sexual_harassment" <?php echo (($_GET['category'] ?? '') === 'sexual_harassment') ? 'selected' : ''; ?>>Sexual Harassment</option>
                                            <option value="theft" <?php echo (($_GET['category'] ?? '') === 'theft') ? 'selected' : ''; ?>>Theft</option>
                                            <option value="drug_abuse" <?php echo (($_GET['category'] ?? '') === 'drug_abuse') ? 'selected' : ''; ?>>Drug Abuse</option>
                                            <option value="cyberbullying" <?php echo (($_GET['category'] ?? '') === 'cyberbullying') ? 'selected' : ''; ?>>Cyberbullying</option>
                                            <option value="vandalism" <?php echo (($_GET['category'] ?? '') === 'vandalism') ? 'selected' : ''; ?>>Vandalism</option>
                                            <option value="discrimination" <?php echo (($_GET['category'] ?? '') === 'discrimination') ? 'selected' : ''; ?>>Discrimination</option>
                                            <option value="teacher_misconduct" <?php echo (($_GET['category'] ?? '') === 'teacher_misconduct') ? 'selected' : ''; ?>>Teacher Misconduct</option>
                                            <option value="unsafe_facilities" <?php echo (($_GET['category'] ?? '') === 'unsafe_facilities') ? 'selected' : ''; ?>>Unsafe Facilities</option>
                                            <option value="emergency_threats" <?php echo (($_GET['category'] ?? '') === 'emergency_threats') ? 'selected' : ''; ?>>Emergency Threats</option>
                                            <option value="other" <?php echo (($_GET['category'] ?? '') === 'other') ? 'selected' : ''; ?>>Other</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3 d-flex align-items-end">
                                        <button type="submit" class="btn btn-primary me-2">Apply Filters</button>
                                        <a href="admin_dashboard.php" class="btn btn-secondary">Clear</a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <div class="card-body">
                        <?php if (empty($filtered_incidents)): ?>
                            <div class="text-center py-4">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No incidents found matching the filters</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Title</th>
                                            <th>Reporter</th>
                                            <th>Category</th>
                                            <th>Priority</th>
                                            <th>Status</th>
                                            <th>Assigned To</th>
                                            <th>Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $display_incidents = array_slice($filtered_incidents, 0, 10);
                                        foreach ($display_incidents as $incident_item): 
                                        ?>
                                            <tr>
                                                <td>#<?php echo str_pad($incident_item['id'], 6, '0', STR_PAD_LEFT); ?></td>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($incident_item['title']); ?></strong>
                                                    <?php if ($incident_item['anonymous']): ?>
                                                        <span class="badge bg-secondary ms-1">Anonymous</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php 
                                                    if ($incident_item['anonymous']) {
                                                        echo 'Anonymous';
                                                    } else {
                                                        echo htmlspecialchars($incident_item['reporter_name'] ?? 'Unknown');
                                                    }
                                                    ?>
                                                </td>
                                                <td><?php echo get_category_label($incident_item['category']); ?></td>
                                                <td><?php echo get_priority_badge($incident_item['priority']); ?></td>
                                                <td><?php echo get_status_badge($incident_item['status']); ?></td>
                                                <td>
                                                    <?php 
                                                    if ($incident_item['assigned_name']) {
                                                        echo htmlspecialchars($incident_item['assigned_name']);
                                                    } else {
                                                        echo '<span class="text-muted">Unassigned</span>';
                                                    }
                                                    ?>
                                                </td>
                                                <td><?php echo format_date($incident_item['created_at']); ?></td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <a href="incident_details.php?id=<?php echo $incident_item['id']; ?>" 
                                                           class="btn btn-outline-primary">View</a>
                                                        <?php if (!$incident_item['assigned_to'] && is_admin()): ?>
                                                            <button class="btn btn-outline-success assign-btn" 
                                                                    data-id="<?php echo $incident_item['id']; ?>">Assign</button>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <!-- Assignment Modal -->
    <div class="modal fade" id="assignmentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Assign Incident</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="assignmentForm">
                        <input type="hidden" id="incidentId" name="incident_id">
                        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                        <div class="mb-3">
                            <label for="assignedTo" class="form-label">Assign To</label>
                            <select class="form-select" id="assignedTo" name="assigned_to" required>
                                <option value="">Select Staff Member</option>
                                <?php foreach ($staff_users as $staff): ?>
                                    <option value="<?php echo $staff['id']; ?>">
                                        <?php echo htmlspecialchars($staff['full_name']); ?> (<?php echo htmlspecialchars($staff['email']); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveAssignment">Assign</button>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://kit.fontawesome.com/your-fontawesome-kit.js" crossorigin="anonymous"></script>
    <script src="js/main.js"></script>
    
    <script>
        // Prepare chart data with error handling
        const statusData = <?php echo isset($stats['by_status']) ? json_encode(array_column($stats['by_status'], 'count', 'status')) : '{}'; ?>;
        const priorityData = <?php echo isset($stats['by_priority']) ? json_encode(array_column($stats['by_priority'], 'count', 'priority')) : '{}'; ?>;
        const categoryData = <?php echo isset($stats['by_category']) ? json_encode(array_column($stats['by_category'], 'count', 'category')) : '{}'; ?>;
        
        // Helper function to create charts with error handling
        function createChart(canvasId, config) {
            try {
                const canvas = document.getElementById(canvasId);
                if (!canvas) {
                    console.error('Canvas element not found:', canvasId);
                    return;
                }
                
                const ctx = canvas.getContext('2d');
                if (!ctx) {
                    console.error('Could not get canvas context:', canvasId);
                    return;
                }
                
                new Chart(ctx, config);
            } catch (error) {
                console.error('Error creating chart:', canvasId, error);
            }
        }
        
        // Status Distribution Chart
        createChart('statusChart', {
            type: 'doughnut',
            data: {
                labels: ['New', 'Under Review', 'Investigating', 'Resolved', 'Closed'],
                datasets: [{
                    data: [
                        statusData.new || 0,
                        statusData.under_review || 0,
                        statusData.investigating || 0,
                        statusData.resolved || 0,
                        statusData.closed || 0
                    ],
                    backgroundColor: ['#0ea5e9', '#8b5cf6', '#a855f7', '#22c55e', '#6b7280'],
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: {
                    duration: 1000,
                    easing: 'easeInOutQuart'
                },
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 8,
                            usePointStyle: true,
                            font: {
                                size: 11
                            }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.parsed || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((value / total) * 100).toFixed(1);
                                return `${label}: ${value} (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });
        
        // Priority Analysis Chart
        createChart('priorityChart', {
            type: 'bar',
            data: {
                labels: ['Low', 'Medium', 'High', 'Critical'],
                datasets: [{
                    data: [
                        priorityData.low || 0,
                        priorityData.medium || 0,
                        priorityData.high || 0,
                        priorityData.critical || 0
                    ],
                    backgroundColor: ['#22c55e', '#eab308', '#f97316', '#ef4444'],
                    borderRadius: 8,
                    borderWidth: 0,
                    hoverBackgroundColor: ['#16a34a', '#ca8a04', '#ea580c', '#dc2626']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: {
                    duration: 1000,
                    easing: 'easeInOutQuart'
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `${context.label}: ${context.parsed.y} incidents`;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            display: false
                        },
                        ticks: {
                            stepSize: 1,
                            font: {
                                size: 11
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                size: 11
                            }
                        }
                    }
                }
            }
        });
        
        // Category Trends Chart
        const categoryLabels = Object.keys(categoryData).length > 0 
            ? Object.keys(categoryData).map(k => k.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()))
            : ['No Data'];
        const categoryValues = Object.keys(categoryData).length > 0 
            ? Object.values(categoryData)
            : [0];
        
        createChart('categoryChart', {
            type: 'bar',
            data: {
                labels: categoryLabels,
                datasets: [{
                    data: categoryValues,
                    backgroundColor: '#0ea5e9',
                    borderRadius: 8,
                    borderWidth: 0,
                    hoverBackgroundColor: '#0284c7'
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                animation: {
                    duration: 1000,
                    easing: 'easeInOutQuart'
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `${context.label}: ${context.parsed.x} incidents`;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        grid: {
                            display: false
                        },
                        ticks: {
                            stepSize: 1,
                            font: {
                                size: 11
                            }
                        }
                    },
                    y: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                size: 11
                            }
                        }
                    }
                }
            }
        });
        
        // Assignment functionality
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('assign-btn')) {
                const incidentId = e.target.dataset.id;
                document.getElementById('incidentId').value = incidentId;
                new bootstrap.Modal(document.getElementById('assignmentModal')).show();
            }
        });
        
        document.getElementById('saveAssignment').addEventListener('click', function() {
            const form = document.getElementById('assignmentForm');
            const formData = new FormData(form);
            
            // Validate form
            const assignedTo = document.getElementById('assignedTo').value;
            const incidentId = document.getElementById('incidentId').value;
            
            if (!assignedTo) {
                alert('Please select a staff member to assign this incident to.');
                return;
            }
            
            if (!incidentId) {
                alert('Invalid incident ID. Please try again.');
                return;
            }
            
            // Show loading state
            const saveBtn = document.getElementById('saveAssignment');
            const originalText = saveBtn.innerHTML;
            saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Assigning...';
            saveBtn.disabled = true;
            
            fetch('assign_incident.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    bootstrap.Modal.getInstance(document.getElementById('assignmentModal')).hide();
                    // Show success message
                    const successAlert = document.createElement('div');
                    successAlert.className = 'alert alert-success alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3';
                    successAlert.style.zIndex = '9999';
                    successAlert.innerHTML = `
                        <i class="fas fa-check-circle me-2"></i>
                        Incident assigned successfully!
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    `;
                    document.body.appendChild(successAlert);
                    
                    // Remove alert after 3 seconds
                    setTimeout(() => {
                        if (successAlert.parentNode) {
                            successAlert.remove();
                        }
                    }, 3000);
                    
                    location.reload();
                } else {
                    alert('Error: ' + (data.message || 'Failed to assign incident'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while assigning the incident. Please try again.');
            })
            .finally(() => {
                // Reset button state
                saveBtn.innerHTML = originalText;
                saveBtn.disabled = false;
            });
        });
    </script>
</body>
</html>
