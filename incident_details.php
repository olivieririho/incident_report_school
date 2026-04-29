<?php
/**
 * Secure School Incident Reporting Platform
 * Incident Details Page
 */

require_once 'config.php';
require_once 'functions.php';

require_login();

$incident_id = $_GET['id'] ?? 0;
if (!$incident_id || !is_numeric($incident_id)) {
    header('Location: incidents.php');
    exit();
}

$incident = new Incident();
$notification = new Notification();
$user = new User();

$user_role = $_SESSION['user_role'];
$user_id = $_SESSION['user_id'];

// Get incident details
$incident_details = $incident->get_incident_by_id($incident_id);
if (!$incident_details) {
    $_SESSION['error'] = 'Incident not found';
    header('Location: incidents.php');
    exit();
}

// Check access permissions
if ($user_role === 'student') {
    // Students can only view their own incidents or anonymous ones
    if (!$incident_details['anonymous'] && $incident_details['user_id'] != $user_id) {
        $_SESSION['error'] = 'Access denied';
        header('Location: incidents.php');
        exit();
    }
} elseif ($user_role === 'staff') {
    // Staff can only view assigned incidents
    if ($incident_details['assigned_to'] != $user_id) {
        $_SESSION['error'] = 'Access denied';
        header('Location: incidents.php');
        exit();
    }
}

// Get incident updates
$updates = $incident->get_incident_updates($incident_id);

// Get staff users for assignment
$staff_users = $user->get_all_users('staff');

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $new_status = sanitize_input($_POST['status'] ?? '');
    $note = sanitize_input($_POST['note'] ?? '');
    
    if (!empty($new_status) && !empty($note)) {
        $result = $incident->update_incident_status($incident_id, $new_status, $user_id, $note);
        
        if ($result['success']) {
            $_SESSION['success'] = 'Status updated successfully';
            header('Location: incident_details.php?id=' . $incident_id);
            exit();
        } else {
            $_SESSION['error'] = 'Failed to update status';
        }
    } else {
        $_SESSION['error'] = 'Status and note are required';
    }
}

// Mark notifications as read
if ($user_role !== 'student') {
    $notification_stmt = $notification->get_user_notifications($user_id);
    foreach ($notification_stmt as $notif) {
        if ($notif['incident_id'] == $incident_id && !$notif['seen']) {
            $notification->mark_as_read($notif['id']);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Incident Details #<?php echo str_pad($incident_id, 6, '0', STR_PAD_LEFT); ?> - <?php echo APP_NAME; ?></title>
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
                    <h1 class="h2">Incident Details #<?php echo str_pad($incident_id, 6, '0', STR_PAD_LEFT); ?></h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="incidents.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Incidents
                        </a>
                    </div>
                </div>
                
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <div class="row">
                    <div class="col-lg-8">
                        <!-- Incident Details Card -->
                        <div class="card mb-4">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">
                                    <i class="fas fa-info-circle me-2"></i>Incident Information
                                </h5>
                                <div class="d-flex gap-2">
                                    <?php echo get_status_badge($incident_details['status']); ?>
                                    <?php echo get_priority_badge($incident_details['priority']); ?>
                                    <?php if ($incident_details['anonymous']): ?>
                                        <span class="badge bg-secondary">Anonymous</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row g-4">
                                    <div class="col-md-6">
                                        <div class="info-item">
                                            <label class="text-muted small text-uppercase">Title</label>
                                            <div class="fw-semibold fs-5"><?php echo htmlspecialchars($incident_details['title']); ?></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="info-item">
                                            <label class="text-muted small text-uppercase">Category</label>
                                            <div><?php echo get_category_label($incident_details['category']); ?></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="info-item">
                                            <label class="text-muted small text-uppercase">Priority</label>
                                            <div><?php echo get_priority_badge($incident_details['priority']); ?></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="info-item">
                                            <label class="text-muted small text-uppercase">Status</label>
                                            <div><?php echo get_status_badge($incident_details['status']); ?></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="info-item">
                                            <label class="text-muted small text-uppercase">Location</label>
                                            <div>
                                                <i class="fas fa-map-marker-alt text-muted me-2"></i>
                                                <?php echo htmlspecialchars($incident_details['location']); ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="info-item">
                                            <label class="text-muted small text-uppercase">Date & Time</label>
                                            <div>
                                                <i class="fas fa-calendar text-muted me-2"></i>
                                                <?php echo format_date($incident_details['incident_date']); ?> at <?php echo date('h:i A', strtotime($incident_details['incident_time'])); ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="info-item">
                                            <label class="text-muted small text-uppercase">Reported On</label>
                                            <div>
                                                <i class="fas fa-clock text-muted me-2"></i>
                                                <?php echo format_datetime($incident_details['created_at']); ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="info-item">
                                            <label class="text-muted small text-uppercase">Last Updated</label>
                                            <div>
                                                <i class="fas fa-sync text-muted me-2"></i>
                                                <?php echo format_datetime($incident_details['updated_at']); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <hr class="my-4">
                                
                                <div class="info-item">
                                    <label class="text-muted small text-uppercase">Description</label>
                                    <div class="p-3 bg-light rounded">
                                        <?php echo nl2br(htmlspecialchars($incident_details['description'])); ?>
                                    </div>
                                </div>
                                
                                <?php if ($incident_details['evidence_file']): ?>
                                    <hr class="my-4">
                                    <div class="info-item">
                                        <label class="text-muted small text-uppercase">Evidence File</label>
                                        <div>
                                            <a href="<?php echo UPLOAD_PATH . $incident_details['evidence_file']; ?>" 
                                               target="_blank" class="btn btn-outline-primary">
                                                <i class="fas fa-download me-2"></i> Download Evidence
                                            </a>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($user_role === 'admin' || $user_role === 'staff'): ?>
                                    <hr class="my-4">
                                    <div class="info-item">
                                        <label class="text-muted small text-uppercase">Reporter Information</label>
                                        <?php if ($incident_details['anonymous']): ?>
                                            <div class="alert alert-info">
                                                <i class="fas fa-user-secret me-2"></i>
                                                This report was submitted anonymously
                                            </div>
                                        <?php else: ?>
                                            <div class="p-3 bg-light rounded">
                                                <div>
                                                    <strong>Name:</strong> <?php echo htmlspecialchars($incident_details['reporter_name'] ?? 'Unknown'); ?>
                                                </div>
                                                <div>
                                                    <strong>Email:</strong> <?php echo htmlspecialchars($incident_details['reporter_email'] ?? 'Unknown'); ?>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Updates Timeline -->
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-history me-2"></i>Updates Timeline
                                </h5>
                            </div>
                            <div class="card-body">
                                <?php if (empty($updates)): ?>
                                    <div class="text-center py-4">
                                        <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">No updates yet</p>
                                    </div>
                                <?php else: ?>
                                    <div class="activity-timeline">
                                        <?php foreach ($updates as $update): ?>
                                            <div class="activity-item mb-4">
                                                <div class="activity-icon bg-primary bg-opacity-10 text-primary">
                                                    <i class="fas fa-user"></i>
                                                </div>
                                                <div class="card border-0 shadow-sm">
                                                    <div class="card-body">
                                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                                            <div>
                                                                <h6 class="mb-1 fw-semibold">
                                                                    <?php echo htmlspecialchars($update['updated_by_name']); ?>
                                                                </h6>
                                                                <p class="mb-2"><?php echo nl2br(htmlspecialchars($update['note'])); ?></p>
                                                                <div><?php echo get_status_badge($update['status']); ?></div>
                                                            </div>
                                                            <small class="text-muted">
                                                                <i class="fas fa-clock"></i> <?php echo format_datetime($update['created_at']); ?>
                                                            </small>
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
                    
                    <div class="col-lg-4">
                        <!-- Actions Card -->
                        <?php if ($user_role === 'admin' || ($user_role === 'staff' && $incident_details['assigned_to'] == $user_id)): ?>
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="fas fa-cog me-2"></i>Actions
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="d-grid gap-2">
                                        <?php if ($user_role === 'admin' && !$incident_details['assigned_to']): ?>
                                            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#assignmentModal">
                                                <i class="fas fa-user-plus me-2"></i> Assign to Staff
                                            </button>
                                        <?php endif; ?>
                                        
                                        <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#updateModal">
                                            <i class="fas fa-edit me-2"></i> Update Status
                                        </button>
                                        
                                        <?php if ($user_role === 'admin'): ?>
                                            <a href="export_incident.php?id=<?php echo $incident_id; ?>" class="btn btn-outline-primary">
                                                <i class="fas fa-download me-2"></i> Export PDF
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Assignment Information -->
                        <?php if ($user_role === 'admin' || $user_role === 'staff'): ?>
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="fas fa-user-check me-2"></i>Assignment Information
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <?php if ($incident_details['assigned_to']): ?>
                                        <div class="info-item mb-3">
                                            <label class="text-muted small text-uppercase">Assigned To</label>
                                            <div class="fw-semibold">
                                                <i class="fas fa-user text-primary me-2"></i>
                                                <?php echo htmlspecialchars($incident_details['assigned_name']); ?>
                                            </div>
                                        </div>
                                        <div class="info-item">
                                            <label class="text-muted small text-uppercase">Assignment Date</label>
                                            <div>
                                                <i class="fas fa-calendar text-muted me-2"></i>
                                                <?php echo format_datetime($incident_details['updated_at']); ?>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <div class="text-center py-3">
                                            <i class="fas fa-user-minus fa-2x text-muted mb-2"></i>
                                            <p class="text-muted mb-0">Not assigned yet</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Reference Information -->
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-fingerprint me-2"></i>Reference Information
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="info-item mb-3">
                                    <label class="text-muted small text-uppercase">Incident ID</label>
                                    <div class="fw-bold fs-5">#<?php echo str_pad($incident_id, 6, '0', STR_PAD_LEFT); ?></div>
                                </div>
                                <div class="info-item mb-3">
                                    <label class="text-muted small text-uppercase">Reference Code</label>
                                    <div>
                                        <code class="bg-light p-2 rounded">INC-<?php echo date('Y'); ?>-<?php echo str_pad($incident_id, 6, '0', STR_PAD_LEFT); ?></code>
                                    </div>
                                </div>
                                <?php if ($incident_details['anonymous']): ?>
                                    <div class="alert alert-info">
                                        <i class="fas fa-user-secret me-2"></i>
                                        <strong>Anonymous Report:</strong> The reporter's identity is protected.
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <!-- Assignment Modal -->
    <?php if ($user_role === 'admin'): ?>
        <div class="modal fade" id="assignmentModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Assign Incident</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form method="POST" action="assign_incident.php">
                            <input type="hidden" name="incident_id" value="<?php echo $incident_id; ?>">
                            <div class="mb-3">
                                <label for="assigned_to" class="form-label">Assign To</label>
                                <select class="form-select" id="assigned_to" name="assigned_to" required>
                                    <option value="">Select Staff Member</option>
                                    <?php foreach ($staff_users as $staff): ?>
                                        <option value="<?php echo $staff['id']; ?>">
                                            <?php echo htmlspecialchars($staff['full_name']); ?> (<?php echo htmlspecialchars($staff['email']); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary">Assign</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
    
    <!-- Update Status Modal -->
    <div class="modal fade" id="updateModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Update Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="">
                        <input type="hidden" name="update_status" value="1">
                        <div class="mb-3">
                            <label for="status" class="form-label">New Status</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="">Select Status</option>
                                <option value="new">New</option>
                                <option value="under_review">Under Review</option>
                                <option value="investigating">Investigating</option>
                                <option value="resolved">Resolved</option>
                                <option value="closed">Closed</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="note" class="form-label">Update Note</label>
                            <textarea class="form-control" id="note" name="note" rows="4" required 
                                      placeholder="Describe what actions were taken or what the current status means..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Update Status</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://kit.fontawesome.com/your-fontawesome-kit.js" crossorigin="anonymous"></script>
    <script src="js/main.js"></script>
</body>
</html>
