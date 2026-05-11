<?php
/**
 * Secure School Incident Reporting Platform
 * Email Logs - View sent email history
 */

require_once 'config.php';
require_once 'functions.php';
require_once 'access_control.php';

require_admin();

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Filters
$filter_type = isset($_GET['type']) ? sanitize_input($_GET['type']) : '';
$filter_status = isset($_GET['status']) ? sanitize_input($_GET['status']) : '';

// Get email logs from database (if table exists)
$email_logs = [];
$total_logs = 0;

try {
    $db = Database::getInstance();
    
    // Check if email_logs table exists
    $stmt = $db->query("SHOW TABLES LIKE 'email_logs'");
    $table_exists = $stmt->fetch();
    
    if ($table_exists) {
        // Build query with filters
        $where_clauses = [];
        $params = [];
        
        if ($filter_type) {
            $where_clauses[] = "template_type = ?";
            $params[] = $filter_type;
        }
        
        if ($filter_status) {
            $where_clauses[] = "status = ?";
            $params[] = $filter_status;
        }
        
        $where_sql = !empty($where_clauses) ? "WHERE " . implode(" AND ", $where_clauses) : "";
        
        // Get total count
        $count_sql = "SELECT COUNT(*) as total FROM email_logs $where_sql";
        $stmt = $db->query($count_sql, $params);
        $total_logs = $stmt->fetch()['total'];
        
        // Get logs
        $sql = "SELECT el.*, u.full_name as recipient_name 
                FROM email_logs el 
                LEFT JOIN users u ON el.recipient_id = u.id 
                $where_sql 
                ORDER BY el.sent_at DESC 
                LIMIT $per_page OFFSET $offset";
        $stmt = $db->query($sql, $params);
        $email_logs = $stmt->fetchAll();
    }
    
} catch (Exception $e) {
    error_log("Error fetching email logs: " . $e->getMessage());
}

// Calculate total pages
$total_pages = ceil($total_logs / $per_page);

$page_title = 'Email Logs';
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
                        <i class="fas fa-history me-2 text-primary"></i><?php echo $page_title; ?>
                    </h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="email_templates.php" class="btn btn-outline-primary">
                            <i class="fas fa-envelope me-2"></i>Email Templates
                        </a>
                    </div>
                </div>
                
                <?php if (!$table_exists): ?>
                    <div class="alert alert-warning">
                        <h5><i class="fas fa-exclamation-triangle me-2"></i>Email Logging Not Setup</h5>
                        <p>The email logging table does not exist. Please run the database update to enable email logging.</p>
                        <a href="setup_email_logs.php" class="btn btn-primary">Setup Email Logging</a>
                    </div>
                <?php else: ?>
                    
                    <!-- Filters -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <form method="GET" action="">
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label class="form-label">Template Type</label>
                                        <select name="type" class="form-select">
                                            <option value="">All Types</option>
                                            <option value="resolution" <?php echo $filter_type === 'resolution' ? 'selected' : ''; ?>>Resolution</option>
                                            <option value="assignment" <?php echo $filter_type === 'assignment' ? 'selected' : ''; ?>>Assignment</option>
                                            <option value="status_update" <?php echo $filter_type === 'status_update' ? 'selected' : ''; ?>>Status Update</option>
                                            <option value="new_incident" <?php echo $filter_type === 'new_incident' ? 'selected' : ''; ?>>New Incident</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Status</label>
                                        <select name="status" class="form-select">
                                            <option value="">All Statuses</option>
                                            <option value="sent" <?php echo $filter_status === 'sent' ? 'selected' : ''; ?>>Sent</option>
                                            <option value="failed" <?php echo $filter_status === 'failed' ? 'selected' : ''; ?>>Failed</option>
                                            <option value="pending" <?php echo $filter_status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">&nbsp;</label>
                                        <button type="submit" class="btn btn-primary w-100">
                                            <i class="fas fa-filter me-2"></i>Filter
                                        </button>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">&nbsp;</label>
                                        <a href="email_logs.php" class="btn btn-outline-secondary w-100">
                                            <i class="fas fa-undo me-2"></i>Reset
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Email Logs Table -->
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="fas fa-list me-2"></i>Sent Emails
                            </h5>
                            <span class="badge bg-primary"><?php echo $total_logs; ?> total</span>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Date</th>
                                            <th>Template</th>
                                            <th>Recipient</th>
                                            <th>Subject</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($email_logs)): ?>
                                            <tr>
                                                <td colspan="6" class="text-center py-5">
                                                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                                    <p class="text-muted">No email logs found</p>
                                                </td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($email_logs as $log): ?>
                                                <tr>
                                                    <td>
                                                        <small><?php echo format_date($log['sent_at']); ?></small>
                                                        <br>
                                                        <small class="text-muted"><?php echo date('g:i A', strtotime($log['sent_at'])); ?></small>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-info text-capitalize">
                                                            <?php echo str_replace('_', ' ', $log['template_type']); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <strong><?php echo htmlspecialchars($log['recipient_name'] ?? 'Unknown'); ?></strong>
                                                        <br>
                                                        <small class="text-muted"><?php echo htmlspecialchars($log['recipient_email']); ?></small>
                                                    </td>
                                                    <td>
                                                        <span class="text-truncate d-inline-block" style="max-width: 250px;" title="<?php echo htmlspecialchars($log['subject']); ?>">
                                                            <?php echo htmlspecialchars($log['subject']); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <?php
                                                        $status_colors = [
                                                            'sent' => 'success',
                                                            'failed' => 'danger',
                                                            'pending' => 'warning'
                                                        ];
                                                        $status_color = $status_colors[$log['status']] ?? 'secondary';
                                                        ?>
                                                        <span class="badge bg-<?php echo $status_color; ?> text-capitalize">
                                                            <i class="fas fa-<?php echo $log['status'] === 'sent' ? 'check' : ($log['status'] === 'failed' ? 'times' : 'clock'); ?> me-1"></i>
                                                            <?php echo $log['status']; ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <div class="btn-group btn-group-sm">
                                                            <button class="btn btn-outline-primary" onclick="viewEmail(<?php echo $log['id']; ?>)" title="View Email">
                                                                <i class="fas fa-eye"></i>
                                                            </button>
                                                            <button class="btn btn-outline-info" onclick="resendEmail(<?php echo $log['id']; ?>)" title="Resend">
                                                                <i class="fas fa-redo"></i>
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
                        
                        <?php if ($total_pages > 1): ?>
                            <div class="card-footer">
                                <nav aria-label="Email logs pagination">
                                    <ul class="pagination justify-content-center mb-0">
                                        <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $page - 1; ?>&type=<?php echo $filter_type; ?>&status=<?php echo $filter_status; ?>">Previous</a>
                                        </li>
                                        
                                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                            <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                                <a class="page-link" href="?page=<?php echo $i; ?>&type=<?php echo $filter_type; ?>&status=<?php echo $filter_status; ?>"><?php echo $i; ?></a>
                                            </li>
                                        <?php endfor; ?>
                                        
                                        <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $page + 1; ?>&type=<?php echo $filter_type; ?>&status=<?php echo $filter_status; ?>">Next</a>
                                        </li>
                                    </ul>
                                </nav>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Statistics Cards -->
                    <div class="row mt-4">
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body text-center">
                                    <i class="fas fa-check-circle fa-2x mb-2"></i>
                                    <h4><?php echo count(array_filter($email_logs, fn($l) => $l['status'] === 'sent')); ?></h4>
                                    <small>Sent</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-danger text-white">
                                <div class="card-body text-center">
                                    <i class="fas fa-times-circle fa-2x mb-2"></i>
                                    <h4><?php echo count(array_filter($email_logs, fn($l) => $l['status'] === 'failed')); ?></h4>
                                    <small>Failed</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-dark">
                                <div class="card-body text-center">
                                    <i class="fas fa-clock fa-2x mb-2"></i>
                                    <h4><?php echo count(array_filter($email_logs, fn($l) => $l['status'] === 'pending')); ?></h4>
                                    <small>Pending</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body text-center">
                                    <i class="fas fa-percentage fa-2x mb-2"></i>
                                    <h4><?php 
                                        $sent = count(array_filter($email_logs, fn($l) => $l['status'] === 'sent'));
                                        $total = count($email_logs);
                                        echo $total > 0 ? round(($sent / $total) * 100) : 0; 
                                    ?>%</h4>
                                    <small>Success Rate</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                <?php endif; ?>
            </main>
        </div>
    </div>
    
    <!-- View Email Modal -->
    <div class="modal fade" id="viewEmailModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-envelope me-2"></i>Email Preview
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="emailPreviewContent">
                        <div class="text-center py-5">
                            <i class="fas fa-spinner fa-spin fa-2x"></i>
                            <p class="mt-2">Loading email content...</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function viewEmail(logId) {
            const modal = new bootstrap.Modal(document.getElementById('viewEmailModal'));
            modal.show();
            // In a real implementation, this would fetch the email content via AJAX
            document.getElementById('emailPreviewContent').innerHTML = 
                '<div class="alert alert-info">Email content would be loaded here for log ID: ' + logId + '</div>';
        }
        
        function resendEmail(logId) {
            if (confirm('Are you sure you want to resend this email?')) {
                alert('Resend functionality would be implemented here for log ID: ' + logId);
            }
        }
    </script>
</body>
</html>
