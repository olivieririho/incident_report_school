<?php
/**
 * Secure School Incident Reporting Platform
 * Email Templates Management
 */

require_once 'config.php';
require_once 'functions.php';
require_once 'access_control.php';

require_admin();

// Define email templates
$email_templates = [
    'resolution' => [
        'name' => 'Case Resolution',
        'subject' => 'Your Incident Has Been Resolved - SecureSchool',
        'description' => 'Sent when an incident is marked as resolved or closed',
        'variables' => ['{user_name}', '{incident_ref}', '{incident_title}', '{status}', '{resolution_date}', '{incident_url}']
    ],
    'assignment' => [
        'name' => 'Case Assignment',
        'subject' => 'New Incident Assigned to You - SecureSchool',
        'description' => 'Sent when staff is assigned a new incident',
        'variables' => ['{staff_name}', '{incident_ref}', '{incident_title}', '{incident_url}']
    ],
    'new_incident' => [
        'name' => 'New Incident Reported',
        'subject' => 'New Incident Report - SecureSchool',
        'description' => 'Sent to admins when a new incident is reported',
        'variables' => ['{incident_ref}', '{incident_title}', '{reporter_name}', '{incident_url}']
    ],
    'status_update' => [
        'name' => 'Status Update',
        'subject' => 'Incident Status Updated - SecureSchool',
        'description' => 'Sent when incident status changes',
        'variables' => ['{user_name}', '{incident_ref}', '{incident_title}', '{old_status}', '{new_status}', '{incident_url}']
    ]
];

// Get preview data
$preview_template = isset($_GET['preview']) ? $_GET['preview'] : 'resolution';
$sample_data = [
    'user_name' => 'John Doe',
    'staff_name' => 'Jane Smith',
    'incident_ref' => 'INC-000123',
    'incident_title' => 'Sample Incident Title',
    'status' => 'Resolved',
    'old_status' => 'Under Review',
    'new_status' => 'Investigating',
    'resolution_date' => date('F j, Y g:i A'),
    'reporter_name' => 'Anonymous',
    'incident_url' => APP_URL . 'incident_details.php?id=123'
];

$page_title = 'Email Templates';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <style>
        .email-preview-frame {
            border: 1px solid #ddd;
            border-radius: 8px;
            background: white;
            min-height: 500px;
        }
        .template-card {
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .template-card:hover, .template-card.active {
            border-color: var(--primary-500);
            background: var(--primary-50);
        }
        .variable-badge {
            background: var(--secondary-100);
            color: var(--secondary-800);
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            margin: 0.125rem;
            display: inline-block;
        }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="container-fluid" style="padding-top: 80px;">
        <div class="row">
            <?php include 'includes/sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">
                        <i class="fas fa-envelope me-2 text-primary"></i><?php echo $page_title; ?>
                    </h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="email_logs.php" class="btn btn-outline-primary">
                            <i class="fas fa-history me-2"></i>Email Logs
                        </a>
                    </div>
                </div>
                
                <div class="row">
                    <!-- Templates List -->
                    <div class="col-lg-4 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-file-alt me-2"></i>Email Templates
                                </h5>
                            </div>
                            <div class="card-body p-0">
                                <div class="list-group list-group-flush">
                                    <?php foreach ($email_templates as $key => $template): ?>
                                        <a href="?preview=<?php echo $key; ?>" 
                                           class="list-group-item list-group-item-action template-card <?php echo $preview_template === $key ? 'active' : ''; ?>">
                                            <div class="d-flex w-100 justify-content-between">
                                                <h6 class="mb-1"><?php echo $template['name']; ?></h6>
                                                <small class="text-muted"><?php echo count($template['variables']); ?> vars</small>
                                            </div>
                                            <p class="mb-1 small text-muted"><?php echo $template['description']; ?></p>
                                            <small class="text-primary"><?php echo $template['subject']; ?></small>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Available Variables -->
                        <div class="card mt-3">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="fas fa-code me-2"></i>Available Variables
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="d-flex flex-wrap">
                                    <?php foreach ($email_templates[$preview_template]['variables'] as $variable): ?>
                                        <span class="variable-badge"><?php echo $variable; ?></span>
                                    <?php endforeach; ?>
                                </div>
                                <hr>
                                <small class="text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    These variables will be replaced with actual values when sending emails.
                                </small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Email Preview -->
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">
                                    <i class="fas fa-eye me-2"></i>Email Preview
                                </h5>
                                <span class="badge bg-primary">
                                    <?php echo $email_templates[$preview_template]['name']; ?>
                                </span>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <strong>Subject:</strong> 
                                    <span class="text-primary"><?php echo $email_templates[$preview_template]['subject']; ?></span>
                                </div>
                                
                                <div class="email-preview-frame p-0">
                                    <?php echo getEmailPreview($preview_template, $sample_data); ?>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Test Email -->
                        <div class="card mt-3">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="fas fa-paper-plane me-2"></i>Send Test Email
                                </h6>
                            </div>
                            <div class="card-body">
                                <form id="testEmailForm">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Recipient Email</label>
                                                <input type="email" class="form-control" id="testEmail" 
                                                       value="<?php echo $_SESSION['user_email']; ?>" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Template</label>
                                                <select class="form-select" id="testTemplate">
                                                    <?php foreach ($email_templates as $key => $template): ?>
                                                        <option value="<?php echo $key; ?>" <?php echo $preview_template === $key ? 'selected' : ''; ?>>
                                                            <?php echo $template['name']; ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-send me-2"></i>Send Test
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('testEmailForm').addEventListener('submit', function(e) {
            e.preventDefault();
            alert('Test email feature would send a test email to: ' + document.getElementById('testEmail').value);
        });
    </script>
</body>
</html>

<?php
function getEmailPreview($template, $data) {
    $subject = '';
    $body = '';
    
    switch($template) {
        case 'resolution':
            $subject = "Your Incident Has Been Resolved - SecureSchool";
            $body = getResolutionEmailTemplate($data);
            break;
        case 'assignment':
            $subject = "New Incident Assigned to You - SecureSchool";
            $body = getAssignmentEmailTemplate($data);
            break;
        case 'new_incident':
            $subject = "New Incident Report - SecureSchool";
            $body = getNewIncidentEmailTemplate($data);
            break;
        case 'status_update':
            $subject = "Incident Status Updated - SecureSchool";
            $body = getStatusUpdateEmailTemplate($data);
            break;
    }
    
    return $body;
}

function getResolutionEmailTemplate($data) {
    return '<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; }
        .container { max-width: 600px; margin: 0 auto; background: #fff; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; }
        .header h2 { margin: 0; font-size: 24px; }
        .content { padding: 30px; background: #f9f9f9; }
        .content h3 { color: #333; margin-top: 0; }
        .incident-box { background: white; padding: 20px; margin: 20px 0; border-left: 4px solid #667eea; border-radius: 4px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .incident-box h4 { margin-top: 0; color: #667eea; }
        .detail-row { margin: 10px 0; }
        .detail-label { font-weight: bold; color: #666; }
        .btn { display: inline-block; padding: 12px 24px; background: #667eea; color: white; text-decoration: none; border-radius: 5px; margin-top: 20px; }
        .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; background: #f0f0f0; }
        .status-badge { display: inline-block; padding: 5px 15px; background: #28a745; color: white; border-radius: 20px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>🔒 SecureSchool Incident Management</h2>
        </div>
        <div class="content">
            <h3>Hello ' . htmlspecialchars($data['user_name']) . ',</h3>
            <p>We are pleased to inform you that your incident has been successfully <span class="status-badge">' . htmlspecialchars($data['status']) . '</span>.</p>
            
            <div class="incident-box">
                <h4>📋 Incident Details</h4>
                <div class="detail-row">
                    <span class="detail-label">Reference:</span> ' . htmlspecialchars($data['incident_ref']) . '
                </div>
                <div class="detail-row">
                    <span class="detail-label">Title:</span> ' . htmlspecialchars($data['incident_title']) . '
                </div>
                <div class="detail-row">
                    <span class="detail-label">Status:</span> <span style="color: #28a745; font-weight: bold;">' . htmlspecialchars($data['status']) . '</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Date Resolved:</span> ' . htmlspecialchars($data['resolution_date']) . '
                </div>
            </div>
            
            <p>If you have any questions or need further assistance, please don\'t hesitate to contact us. You can view the full details of your incident by clicking the button below.</p>
            
            <a href="' . htmlspecialchars($data['incident_url']) . '" class="btn">View Incident Details</a>
        </div>
        <div class="footer">
            <p>This is an automated message from SecureSchool Incident Reporting System.</p>
            <p>&copy; ' . date('Y') . ' SecureSchool. All rights reserved.</p>
            <p><small>Need help? Contact support at support@secureschool.edu</small></p>
        </div>
    </div>
</body>
</html>';
}

function getAssignmentEmailTemplate($data) {
    return '<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; }
        .container { max-width: 600px; margin: 0 auto; background: #fff; }
        .header { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; padding: 30px; text-align: center; }
        .content { padding: 30px; background: #f9f9f9; }
        .btn { display: inline-block; padding: 12px 24px; background: #f5576c; color: white; text-decoration: none; border-radius: 5px; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>🔒 New Assignment</h2>
        </div>
        <div class="content">
            <h3>Hello ' . htmlspecialchars($data['staff_name']) . ',</h3>
            <p>You have been assigned a new incident to handle.</p>
            <p><strong>Incident:</strong> ' . htmlspecialchars($data['incident_ref']) . ' - ' . htmlspecialchars($data['incident_title']) . '</p>
            <a href="' . htmlspecialchars($data['incident_url']) . '" class="btn">View Assignment</a>
        </div>
    </div>
</body>
</html>';
}

function getNewIncidentEmailTemplate($data) {
    return '<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; }
        .container { max-width: 600px; margin: 0 auto; background: #fff; }
        .header { background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); color: white; padding: 30px; text-align: center; }
        .content { padding: 30px; background: #f9f9f9; }
        .btn { display: inline-block; padding: 12px 24px; background: #fa709a; color: white; text-decoration: none; border-radius: 5px; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>🚨 New Incident Reported</h2>
        </div>
        <div class="content">
            <h3>Admin Alert</h3>
            <p>A new incident has been reported and requires your attention.</p>
            <p><strong>Reference:</strong> ' . htmlspecialchars($data['incident_ref']) . '</p>
            <p><strong>Title:</strong> ' . htmlspecialchars($data['incident_title']) . '</p>
            <p><strong>Reporter:</strong> ' . htmlspecialchars($data['reporter_name']) . '</p>
            <a href="' . htmlspecialchars($data['incident_url']) . '" class="btn">Review Incident</a>
        </div>
    </div>
</body>
</html>';
}

function getStatusUpdateEmailTemplate($data) {
    return '<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; }
        .container { max-width: 600px; margin: 0 auto; background: #fff; }
        .header { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; padding: 30px; text-align: center; }
        .content { padding: 30px; background: #f9f9f9; }
        .btn { display: inline-block; padding: 12px 24px; background: #4facfe; color: white; text-decoration: none; border-radius: 5px; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>🔄 Status Update</h2>
        </div>
        <div class="content">
            <h3>Hello ' . htmlspecialchars($data['user_name']) . ',</h3>
            <p>Your incident status has been updated.</p>
            <p><strong>Incident:</strong> ' . htmlspecialchars($data['incident_ref']) . '</p>
            <p><strong>Previous Status:</strong> ' . htmlspecialchars($data['old_status']) . '</p>
            <p><strong>New Status:</strong> <span style="color: #4facfe; font-weight: bold;">' . htmlspecialchars($data['new_status']) . '</span></p>
            <a href="' . htmlspecialchars($data['incident_url']) . '" class="btn">View Details</a>
        </div>
    </div>
</body>
</html>';
}
?>
