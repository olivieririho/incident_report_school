<?php
/**
 * Secure School Incident Reporting Platform
 * My Reports - User's personal incident reports
 */

require_once 'config.php';
require_once 'functions.php';

require_login();

$incident = new Incident();
$user_id = $_SESSION['user_id'];
$my_reports = $incident->get_user_incidents($user_id);

$page_title = 'My Reports';
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
                        <i class="fas fa-file-alt me-2 text-primary"></i>My Reports
                    </h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="report.php" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>New Report
                        </a>
                    </div>
                </div>
                
                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-3 mb-3">
                        <div class="card stat-card primary">
                            <div class="stat-value"><?php echo count($my_reports); ?></div>
                            <div class="stat-label">Total Reports</div>
                            <div class="stat-trend">
                                <i class="fas fa-file-alt me-1"></i>All my incidents
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card stat-card warning">
                            <div class="stat-value"><?php echo count(array_filter($my_reports, fn($r) => $r['status'] === 'new' || $r['status'] === 'under_review')); ?></div>
                            <div class="stat-label">Pending</div>
                            <div class="stat-trend">
                                <i class="fas fa-clock me-1"></i>Awaiting review
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card stat-card info">
                            <div class="stat-value"><?php echo count(array_filter($my_reports, fn($r) => $r['status'] === 'investigating')); ?></div>
                            <div class="stat-label">Investigating</div>
                            <div class="stat-trend">
                                <i class="fas fa-search me-1"></i>Under investigation
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card stat-card success">
                            <div class="stat-value"><?php echo count(array_filter($my_reports, fn($r) => $r['status'] === 'resolved' || $r['status'] === 'closed')); ?></div>
                            <div class="stat-label">Resolved</div>
                            <div class="stat-trend">
                                <i class="fas fa-check-circle me-1"></i>Completed
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Reports Table -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-list me-2"></i>My Incident Reports
                            <span class="badge bg-primary ms-2"><?php echo count($my_reports); ?></span>
                        </h5>
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-success" onclick="exportReports()">
                                <i class="fas fa-download"></i> Export
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if (empty($my_reports)): ?>
                            <div class="text-center py-5">
                                <div class="empty-state">
                                    <i class="fas fa-file-alt fa-4x text-muted mb-3"></i>
                                    <h5 class="text-muted">No reports found</h5>
                                    <p class="text-muted">You haven't reported any incidents yet.</p>
                                    <a href="report.php" class="btn btn-primary">
                                        <i class="fas fa-plus me-2"></i>Report Your First Incident
                                    </a>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle" id="reportsTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="min-width: 80px;">ID</th>
                                            <th style="min-width: 200px;">Title</th>
                                            <th style="min-width: 120px;">Category</th>
                                            <th style="min-width: 100px;">Priority</th>
                                            <th style="min-width: 120px;">Status</th>
                                            <th style="min-width: 150px;">Date</th>
                                            <th style="min-width: 100px;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($my_reports as $report): ?>
                                            <tr>
                                                <td>
                                                    <div class="fw-bold text-primary">#<?php echo str_pad($report['id'], 6, '0', STR_PAD_LEFT); ?></div>
                                                </td>
                                                <td>
                                                    <div class="fw-semibold text-gray-900"><?php echo htmlspecialchars($report['title']); ?></div>
                                                </td>
                                                <td>
                                                    <span class="badge bg-info">
                                                        <?php echo ucfirst(str_replace('_', ' ', $report['category'])); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php
                                                    $priority_colors = [
                                                        'low' => 'success',
                                                        'medium' => 'warning',
                                                        'high' => 'danger',
                                                        'critical' => 'danger'
                                                    ];
                                                    $color = $priority_colors[$report['priority']] ?? 'secondary';
                                                    ?>
                                                    <span class="badge bg-<?php echo $color; ?>">
                                                        <?php echo ucfirst($report['priority']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php
                                                    $status_colors = [
                                                        'new' => 'primary',
                                                        'under_review' => 'info',
                                                        'investigating' => 'warning',
                                                        'resolved' => 'success',
                                                        'closed' => 'secondary'
                                                    ];
                                                    $status_color = $status_colors[$report['status']] ?? 'secondary';
                                                    ?>
                                                    <span class="badge bg-<?php echo $status_color; ?>">
                                                        <?php echo ucfirst(str_replace('_', ' ', $report['status'])); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="small"><?php echo format_date($report['created_at']); ?></div>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        <a href="incident_details.php?id=<?php echo $report['id']; ?>" 
                                                           class="btn btn-outline-primary" title="View Details">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
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
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://kit.fontawesome.com/your-fontawesome-kit.js" crossorigin="anonymous"></script>
    <script src="js/main.js"></script>
    
    <script>
        // Export functionality (placeholder)
        function exportReports() {
            alert('Export functionality will be implemented soon.');
        }
    </script>
</body>
</html>
