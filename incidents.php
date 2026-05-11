<?php
/**
 * Secure School Incident Reporting Platform
 * Incidents Listing Page
 */

require_once 'config.php';
require_once 'functions.php';
require_once 'access_control.php';

require_login();

$incident = new Incident();
$user = new User();

// Get user role and data
$user_role = $_SESSION['user_role'];
$user_id = $_SESSION['user_id'];

// Get incidents based on user role
if ($user_role === 'admin') {
    $incidents = $incident->get_all_incidents();
    $page_title = 'All Incidents';
} elseif ($user_role === 'staff') {
    $incidents = $incident->get_assigned_incidents($user_id);
    $page_title = 'Assigned Incidents';
} else {
    $incidents = $incident->get_incidents_by_user($user_id);
    $page_title = 'My Reports';
}

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

if (!empty($filters)) {
    $incidents = $incident->get_all_incidents($filters);
}

// Get staff users for assignment
$staff_users = $user->get_all_users('staff');
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
                        <?php if ($user_role === 'student' || $user_role === 'staff'): ?>
                            <a href="report.php" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Report Incident
                            </a>
                        <?php endif; ?>
                        <?php if ($user_role === 'admin'): ?>
                            <a href="admin_dashboard.php" class="btn btn-outline-secondary">
                                <i class="fas fa-chart-bar"></i> Dashboard
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Advanced Filters -->
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-filter me-2"></i>Advanced Filters
                        </h5>
                        <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#filterCollapse">
                            <i class="fas fa-chevron-down"></i>
                        </button>
                    </div>
                    <div class="collapse show" id="filterCollapse">
                        <div class="card-body">
                            <form method="GET" action="">
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label for="status" class="form-label fw-semibold">Status</label>
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
                                        <label for="priority" class="form-label fw-semibold">Priority</label>
                                        <select class="form-select" id="priority" name="priority">
                                            <option value="">All Priority</option>
                                            <option value="low" <?php echo (($_GET['priority'] ?? '') === 'low') ? 'selected' : ''; ?>>Low</option>
                                            <option value="medium" <?php echo (($_GET['priority'] ?? '') === 'medium') ? 'selected' : ''; ?>>Medium</option>
                                            <option value="high" <?php echo (($_GET['priority'] ?? '') === 'high') ? 'selected' : ''; ?>>High</option>
                                            <option value="critical" <?php echo (($_GET['priority'] ?? '') === 'critical') ? 'selected' : ''; ?>>Critical</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="category" class="form-label fw-semibold">Category</label>
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
                                        <button type="submit" class="btn btn-primary me-2 flex-grow-1">
                                            <i class="fas fa-search me-2"></i>Apply Filters
                                        </button>
                                        <a href="incidents.php" class="btn btn-outline-secondary">
                                            <i class="fas fa-times"></i>
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Incidents Table -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-list me-2"></i>
                            <?php echo $page_title; ?> 
                            <span class="badge bg-primary ms-2"><?php echo count($incidents); ?></span>
                        </h5>
                        <?php if ($user_role === 'admin'): ?>
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-success" onclick="exportData()">
                                    <i class="fas fa-download"></i> Export
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <?php if (empty($incidents)): ?>
                            <div class="text-center py-5">
                                <div class="empty-state">
                                    <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                                    <h5 class="text-muted">No incidents found</h5>
                                    <p class="text-muted">
                                        <?php 
                                        if ($user_role === 'student') {
                                            echo 'You haven\'t reported any incidents yet.';
                                        } elseif ($user_role === 'staff') {
                                            echo 'No incidents have been assigned to you yet.';
                                        } else {
                                            echo 'No incidents match the current filters.';
                                        }
                                        ?>
                                    </p>
                                    <?php if ($user_role === 'student' || $user_role === 'staff'): ?>
                                        <a href="report.php" class="btn btn-primary">
                                            <i class="fas fa-plus me-2"></i>Report an Incident
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle" id="incidentsTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="min-width: 100px;">ID</th>
                                            <th style="min-width: 200px;">Title</th>
                                            <?php if ($user_role === 'admin'): ?>
                                                <th style="min-width: 120px;">Reporter</th>
                                            <?php endif; ?>
                                            <th style="min-width: 120px;">Category</th>
                                            <th style="min-width: 80px;">Priority</th>
                                            <th style="min-width: 100px;">Status</th>
                                            <?php if ($user_role === 'admin'): ?>
                                                <th style="min-width: 120px;">Assigned To</th>
                                            <?php endif; ?>
                                            <th style="min-width: 120px;">Location</th>
                                            <th style="min-width: 100px;">Date</th>
                                            <th style="min-width: 120px;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($incidents as $incident_item): ?>
                                            <tr class="incident-row priority-<?php echo $incident_item['priority']; ?>">
                                                <td>
                                                    <div class="fw-bold text-primary">#<?php echo str_pad($incident_item['id'], 6, '0', STR_PAD_LEFT); ?></div>
                                                    <?php if ($incident_item['anonymous']): ?>
                                                        <span class="badge bg-secondary mt-1">Anonymous</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div class="fw-semibold text-gray-900"><?php echo htmlspecialchars($incident_item['title']); ?></div>
                                                    <small class="text-muted d-block mt-1">
                                                        <?php 
                                                        $description = $incident_item['description'];
                                                        echo strlen($description) > 80 ? substr($description, 0, 80) . '...' : $description;
                                                        ?>
                                                    </small>
                                                </td>
                                                <?php if ($user_role === 'admin'): ?>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <?php 
                                                            if ($incident_item['anonymous']) {
                                                                echo '<i class="fas fa-user-secret text-muted me-2"></i>';
                                                                echo '<span class="text-muted">Anonymous</span>';
                                                            } else {
                                                                echo '<i class="fas fa-user text-muted me-2"></i>';
                                                                echo '<span>' . htmlspecialchars($incident_item['reporter_name'] ?? 'Unknown') . '</span>';
                                                            }
                                                            ?>
                                                        </div>
                                                    </td>
                                                <?php endif; ?>
                                                <td>
                                                    <span class="badge bg-info text-white">
                                                        <?php echo get_category_label($incident_item['category']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo get_priority_badge($incident_item['priority']); ?></td>
                                                <td><?php echo get_status_badge($incident_item['status']); ?></td>
                                                <?php if ($user_role === 'admin'): ?>
                                                    <td>
                                                        <?php 
                                                        if ($incident_item['assigned_name']) {
                                                            echo '<div class="d-flex align-items-center">';
                                                            echo '<i class="fas fa-user-check text-success me-2"></i>';
                                                            echo '<span class="text-success fw-medium">' . htmlspecialchars($incident_item['assigned_name']) . '</span>';
                                                            echo '</div>';
                                                        } else {
                                                            echo '<div class="d-flex align-items-center">';
                                                            echo '<i class="fas fa-user-minus text-muted me-2"></i>';
                                                            echo '<span class="text-muted">Unassigned</span>';
                                                            echo '</div>';
                                                        }
                                                        ?>
                                                    </td>
                                                <?php endif; ?>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <i class="fas fa-map-marker-alt text-muted me-2"></i>
                                                        <span class="small"><?php echo htmlspecialchars($incident_item['location']); ?></span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <i class="fas fa-calendar text-muted me-2"></i>
                                                        <span class="small"><?php echo format_date($incident_item['created_at']); ?></span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        <a href="incident_details.php?id=<?php echo $incident_item['id']; ?>" 
                                                           class="btn btn-outline-primary" title="View Details">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <?php if ($user_role === 'admin' && !$incident_item['assigned_to']): ?>
                                                            <button class="btn btn-outline-success assign-btn" 
                                                                    data-id="<?php echo $incident_item['id']; ?>" title="Assign">
                                                                <i class="fas fa-user-plus"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                        <?php if ($user_role === 'admin' || ($user_role === 'staff' && $incident_item['assigned_to'] == $user_id)): ?>
                                                            <button class="btn btn-outline-warning update-btn" 
                                                                    data-id="<?php echo $incident_item['id']; ?>" title="Update Status">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
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
    
    <!-- Update Status Modal -->
    <div class="modal fade" id="updateModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Update Incident Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="updateForm">
                        <input type="hidden" id="updateIncidentId" name="incident_id">
                        <div class="mb-3">
                            <label for="newStatus" class="form-label">New Status</label>
                            <select class="form-select" id="newStatus" name="status" required>
                                <option value="">Select Status</option>
                                <option value="new">New</option>
                                <option value="under_review">Under Review</option>
                                <option value="investigating">Investigating</option>
                                <option value="resolved">Resolved</option>
                                <option value="closed">Closed</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="updateNote" class="form-label">Update Note</label>
                            <textarea class="form-control" id="updateNote" name="note" rows="3" required></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveUpdate">Update</button>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://kit.fontawesome.com/your-fontawesome-kit.js" crossorigin="anonymous"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="js/main.js"></script>
    
    <script>
        // Initialize DataTable
        $(document).ready(function() {
            $('#incidentsTable').DataTable({
                responsive: true,
                pageLength: 25,
                order: [[0, 'desc']]
            });
        });
        
        // Assignment functionality
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('assign-btn') || e.target.closest('.assign-btn')) {
                const btn = e.target.classList.contains('assign-btn') ? e.target : e.target.closest('.assign-btn');
                const incidentId = btn.dataset.id;
                document.getElementById('incidentId').value = incidentId;
                new bootstrap.Modal(document.getElementById('assignmentModal')).show();
            }
        });
        
        document.getElementById('saveAssignment').addEventListener('click', function() {
            const form = document.getElementById('assignmentForm');
            const formData = new FormData(form);
            
            fetch('assign_incident.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    bootstrap.Modal.getInstance(document.getElementById('assignmentModal')).hide();
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while assigning the incident');
            });
        });
        
        // Update status functionality
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('update-btn') || e.target.closest('.update-btn')) {
                const btn = e.target.classList.contains('update-btn') ? e.target : e.target.closest('.update-btn');
                const incidentId = btn.dataset.id;
                document.getElementById('updateIncidentId').value = incidentId;
                new bootstrap.Modal(document.getElementById('updateModal')).show();
            }
        });
        
        document.getElementById('saveUpdate').addEventListener('click', function() {
            const form = document.getElementById('updateForm');
            const formData = new FormData(form);
            
            fetch('update_incident.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    bootstrap.Modal.getInstance(document.getElementById('updateModal')).hide();
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while updating the incident');
            });
        });
        
        // Export functionality
        function exportData() {
            window.location.href = 'export_incidents.php';
        }
    </script>
</body>
</html>
