<?php
/**
 * Secure School Incident Reporting Platform
 * Staff Dashboard - Student Assignment View
 */

require_once 'config.php';
require_once 'functions.php';
require_once 'access_control.php';

require_staff();

$incident = new Incident();
$user = new User();

// Get only students for staff to assign incidents to
$students = $user->get_users_by_role('student');
$assigned_incidents = $incident->get_incidents_by_user($_SESSION['user_id']);

// Get statistics for staff view
$stats = [
    'total_students' => count($students),
    'assigned_incidents' => count($assigned_incidents),
    'pending_incidents' => count(array_filter($assigned_incidents, fn($i) => $i['status'] === 'under_review' || $i['status'] === 'investigating')),
    'resolved_incidents' => count(array_filter($assigned_incidents, fn($i) => $i['status'] === 'resolved' || $i['status'] === 'closed'))
];

$page_title = 'Staff Dashboard';
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
                        <i class="fas fa-chalkboard-teacher me-2 text-primary"></i>Staff Dashboard
                    </h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <span class="badge bg-success me-2">Staff Access</span>
                    </div>
                </div>
                
                <!-- Welcome Message -->
                <div class="alert alert-info">
                    <h5><i class="fas fa-info-circle me-2"></i>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</h5>
                    <p class="mb-0">As a staff member, you can view assigned incidents and manage student cases. You have access to student information for assignment purposes only.</p>
                </div>
                
                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="card stat-card primary">
                            <div class="stat-value"><?php echo $stats['total_students']; ?></div>
                            <div class="stat-label">Total Students</div>
                            <div class="stat-icon">
                                <i class="fas fa-graduation-cap"></i>
                            </div>
                            <div class="stat-trend">
                                <span class="text-info"><i class="fas fa-users"></i> Available</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="card stat-card secondary">
                            <div class="stat-value"><?php echo $stats['assigned_incidents']; ?></div>
                            <div class="stat-label">Assigned Cases</div>
                            <div class="stat-icon">
                                <i class="fas fa-briefcase"></i>
                            </div>
                            <div class="stat-trend">
                                <span class="text-primary"><i class="fas fa-tasks"></i> Active</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="card stat-card warning">
                            <div class="stat-value"><?php echo $stats['pending_incidents']; ?></div>
                            <div class="stat-label">Pending Review</div>
                            <div class="stat-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="stat-trend">
                                <span class="text-warning"><i class="fas fa-hourglass-half"></i> In Progress</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="card stat-card success">
                            <div class="stat-value"><?php echo $stats['resolved_incidents']; ?></div>
                            <div class="stat-label">Resolved</div>
                            <div class="stat-icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="stat-trend">
                                <span class="text-success"><i class="fas fa-trophy"></i> Completed</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <!-- Students List -->
                    <div class="col-lg-6 mb-4">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">
                                    <i class="fas fa-graduation-cap me-2 text-primary"></i>Students
                                    <span class="badge bg-primary ms-2"><?php echo count($students); ?></span>
                                </h5>
                                <div class="input-group input-group-sm" style="width: 200px;">
                                    <input type="text" class="form-control" id="studentSearch" placeholder="Search students...">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-light sticky-top">
                                            <tr>
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody id="studentsTable">
                                            <?php if (empty($students)): ?>
                                                <tr>
                                                    <td colspan="3" class="text-center py-4">
                                                        <i class="fas fa-user-graduate fa-2x text-muted mb-2"></i>
                                                        <div class="text-muted">No students found</div>
                                                    </td>
                                                </tr>
                                            <?php else: ?>
                                                <?php foreach ($students as $student): ?>
                                                    <tr>
                                                        <td>
                                                            <div class="d-flex align-items-center">
                                                                <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px;">
                                                                    <i class="fas fa-user text-primary small"></i>
                                                                </div>
                                                                <div>
                                                                    <div class="fw-semibold"><?php echo htmlspecialchars($student['full_name']); ?></div>
                                                                    <small class="text-muted">ID: <?php echo str_pad($student['id'], 6, '0', STR_PAD_LEFT); ?></small>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <small><?php echo htmlspecialchars($student['email']); ?></small>
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-success">Active</span>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Assigned Incidents -->
                    <div class="col-lg-6 mb-4">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">
                                    <i class="fas fa-briefcase me-2 text-warning"></i>My Assigned Cases
                                    <span class="badge bg-warning ms-2"><?php echo count($assigned_incidents); ?></span>
                                </h5>
                                <button class="btn btn-sm btn-outline-primary" onclick="refreshCases()">
                                    <i class="fas fa-sync-alt"></i> Refresh
                                </button>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-light sticky-top">
                                            <tr>
                                                <th>Incident</th>
                                                <th>Status</th>
                                                <th>Priority</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($assigned_incidents)): ?>
                                                <tr>
                                                    <td colspan="4" class="text-center py-4">
                                                        <i class="fas fa-briefcase fa-2x text-muted mb-2"></i>
                                                        <div class="text-muted">No assigned cases</div>
                                                    </td>
                                                </tr>
                                            <?php else: ?>
                                                <?php foreach ($assigned_incidents as $incident): ?>
                                                    <tr>
                                                        <td>
                                                            <div>
                                                                <div class="fw-semibold text-truncate" style="max-width: 150px;" title="<?php echo htmlspecialchars($incident['title']); ?>">
                                                                    <?php echo htmlspecialchars($incident['title']); ?>
                                                                </div>
                                                                <small class="text-muted">#<?php echo str_pad($incident['id'], 6, '0', STR_PAD_LEFT); ?></small>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <?php
                                                            $status_colors = [
                                                                'under_review' => 'info',
                                                                'investigating' => 'warning',
                                                                'resolved' => 'success',
                                                                'closed' => 'secondary'
                                                            ];
                                                            $status_color = $status_colors[$incident['status']] ?? 'secondary';
                                                            ?>
                                                            <span class="badge bg-<?php echo $status_color; ?> text-capitalize">
                                                                <?php echo str_replace('_', ' ', $incident['status']); ?>
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
                                                            $priority_color = $priority_colors[$incident['priority']] ?? 'secondary';
                                                            ?>
                                                            <span class="badge bg-<?php echo $priority_color; ?> text-capitalize">
                                                                <?php echo $incident['priority']; ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <div class="btn-group btn-group-sm">
                                                                <a href="incident_details.php?id=<?php echo $incident['id']; ?>" 
                                                                   class="btn btn-outline-primary" title="View Details">
                                                                    <i class="fas fa-eye"></i>
                                                                </a>
                                                                <button class="btn btn-outline-success" onclick="updateStatus(<?php echo $incident['id']; ?>)" title="Update Status">
                                                                    <i class="fas fa-edit"></i>
                                                                </button>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Actions -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-bolt me-2 text-warning"></i>Quick Actions
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3 mb-3">
                                        <button class="btn btn-primary w-100" onclick="window.location.href='report.php'">
                                            <i class="fas fa-plus me-2"></i>New Incident Report
                                        </button>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <button class="btn btn-outline-info w-100" onclick="exportStudentList()">
                                            <i class="fas fa-download me-2"></i>Export Student List
                                        </button>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <button class="btn btn-outline-success w-100" onclick="generateReport()">
                                            <i class="fas fa-chart-bar me-2"></i>Generate Report
                                        </button>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <button class="btn btn-outline-secondary w-100" onclick="window.location.href='settings.php'">
                                            <i class="fas fa-cog me-2"></i>Settings
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
        // Student search functionality
        document.getElementById('studentSearch').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const rows = document.querySelectorAll('#studentsTable tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });
        
        // Refresh cases
        function refreshCases() {
            location.reload();
        }
        
        // Update incident status
        function updateStatus(incidentId) {
            window.location.href = `incident_details.php?id=${incidentId}`;
        }
        
        // Export student list
        function exportStudentList() {
            alert('Export functionality will be implemented soon.');
        }
        
        // Generate report
        function generateReport() {
            alert('Report generation will be implemented soon.');
        }
    </script>
</body>
</html>
