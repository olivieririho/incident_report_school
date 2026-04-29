<?php
/**
 * Secure School Incident Reporting Platform
 * Incident Reporting Form
 */

require_once 'config.php';
require_once 'functions.php';

require_login();

$errors = [];
$success = '';
$uploaded_file = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize_input($_POST['title'] ?? '');
    $description = sanitize_input($_POST['description'] ?? '');
    $category = sanitize_input($_POST['category'] ?? '');
    $priority = sanitize_input($_POST['priority'] ?? '');
    $incident_date = sanitize_input($_POST['incident_date'] ?? '');
    $incident_time = sanitize_input($_POST['incident_time'] ?? '');
    $location = sanitize_input($_POST['location'] ?? '');
    $anonymous = isset($_POST['anonymous']) ? 1 : 0;
    
    // Validate CSRF token
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        // Validate inputs
        if (empty($title)) {
            $errors[] = 'Incident title is required';
        }
        
        if (empty($description)) {
            $errors[] = 'Description is required';
        }
        
        if (empty($category)) {
            $errors[] = 'Category is required';
        }
        
        if (empty($priority)) {
            $errors[] = 'Priority level is required';
        }
        
        if (empty($incident_date)) {
            $errors[] = 'Incident date is required';
        }
        
        if (empty($incident_time)) {
            $errors[] = 'Incident time is required';
        }
        
        if (empty($location)) {
            $errors[] = 'Location is required';
        }
        
        // Handle file upload
        $evidence_file = null;
        if (isset($_FILES['evidence']) && $_FILES['evidence']['error'] !== UPLOAD_ERR_NO_FILE) {
            $file_validation = validate_file_upload($_FILES['evidence']);
            if (isset($file_validation['error'])) {
                $errors[] = $file_validation['error'];
            } else {
                // Upload file
                $filename = generate_unique_filename($_FILES['evidence']['name']);
                $upload_path = UPLOAD_PATH . $filename;
                
                if (move_uploaded_file($_FILES['evidence']['tmp_name'], $upload_path)) {
                    $evidence_file = $filename;
                } else {
                    $errors[] = 'Failed to upload file';
                }
            }
        }
        
        if (empty($errors)) {
            $incident = new Incident();
            $data = [
                'user_id' => $anonymous ? null : $_SESSION['user_id'],
                'title' => $title,
                'description' => $description,
                'category' => $category,
                'priority' => $priority,
                'incident_date' => $incident_date,
                'incident_time' => $incident_time,
                'location' => $location,
                'anonymous' => $anonymous,
                'evidence_file' => $evidence_file
            ];
            
            $result = $incident->create_incident($data);
            
            if ($result['success']) {
                $success = 'Incident reported successfully! Reference ID: #' . str_pad($result['incident_id'], 6, '0', STR_PAD_LEFT);
                
                // Clear form
                $_POST = [];
                
                // Redirect after 3 seconds
                header('refresh:3;url=dashboard.php');
            } else {
                $errors[] = $result['message'] ?? 'Failed to report incident';
            }
        }
    }
}

$csrf_token = generate_csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report New Incident - <?php echo APP_NAME; ?></title>
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
                    <h1 class="h2">Report New Incident</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="dashboard.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Dashboard
                        </a>
                    </div>
                </div>
                
                <?php if (!empty($success)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <strong>Success!</strong> <?php echo htmlspecialchars($success); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <strong>Error!</strong>
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <div class="row">
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-edit me-2"></i>Incident Details
                                </h5>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="" enctype="multipart/form-data" id="incidentForm">
                                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="title" class="form-label fw-semibold">Incident Title *</label>
                                                <input type="text" class="form-control" id="title" name="title" 
                                                       value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>" 
                                                       placeholder="Brief title of the incident" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="category" class="form-label fw-semibold">Category *</label>
                                                <select class="form-select" id="category" name="category" required>
                                                    <option value="">Select Category</option>
                                                    <option value="bullying" <?php echo (($_POST['category'] ?? '') === 'bullying') ? 'selected' : ''; ?>>Bullying</option>
                                                    <option value="physical_violence" <?php echo (($_POST['category'] ?? '') === 'physical_violence') ? 'selected' : ''; ?>>Physical Violence</option>
                                                    <option value="sexual_harassment" <?php echo (($_POST['category'] ?? '') === 'sexual_harassment') ? 'selected' : ''; ?>>Sexual Harassment</option>
                                                    <option value="theft" <?php echo (($_POST['category'] ?? '') === 'theft') ? 'selected' : ''; ?>>Theft</option>
                                                    <option value="drug_abuse" <?php echo (($_POST['category'] ?? '') === 'drug_abuse') ? 'selected' : ''; ?>>Drug Abuse</option>
                                                    <option value="cyberbullying" <?php echo (($_POST['category'] ?? '') === 'cyberbullying') ? 'selected' : ''; ?>>Cyberbullying</option>
                                                    <option value="vandalism" <?php echo (($_POST['category'] ?? '') === 'vandalism') ? 'selected' : ''; ?>>Vandalism</option>
                                                    <option value="discrimination" <?php echo (($_POST['category'] ?? '') === 'discrimination') ? 'selected' : ''; ?>>Discrimination</option>
                                                    <option value="teacher_misconduct" <?php echo (($_POST['category'] ?? '') === 'teacher_misconduct') ? 'selected' : ''; ?>>Teacher Misconduct</option>
                                                    <option value="unsafe_facilities" <?php echo (($_POST['category'] ?? '') === 'unsafe_facilities') ? 'selected' : ''; ?>>Unsafe Facilities</option>
                                                    <option value="emergency_threats" <?php echo (($_POST['category'] ?? '') === 'emergency_threats') ? 'selected' : ''; ?>>Emergency Threats</option>
                                                    <option value="other" <?php echo (($_POST['category'] ?? '') === 'other') ? 'selected' : ''; ?>>Other</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="description" class="form-label fw-semibold">Description *</label>
                                        <textarea class="form-control" id="description" name="description" rows="5" 
                                                  placeholder="Provide detailed information about what happened" required><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                                        <div class="form-text text-muted">Please provide as much detail as possible to help with investigation</div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="priority" class="form-label fw-semibold">Priority Level *</label>
                                                <select class="form-select" id="priority" name="priority" required>
                                                    <option value="">Select Priority</option>
                                                    <option value="low" <?php echo (($_POST['priority'] ?? '') === 'low') ? 'selected' : ''; ?>>Low</option>
                                                    <option value="medium" <?php echo (($_POST['priority'] ?? '') === 'medium') ? 'selected' : ''; ?>>Medium</option>
                                                    <option value="high" <?php echo (($_POST['priority'] ?? '') === 'high') ? 'selected' : ''; ?>>High</option>
                                                    <option value="critical" <?php echo (($_POST['priority'] ?? '') === 'critical') ? 'selected' : ''; ?>>Critical</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="location" class="form-label fw-semibold">Location *</label>
                                                <input type="text" class="form-control" id="location" name="location" 
                                                       value="<?php echo htmlspecialchars($_POST['location'] ?? ''); ?>" 
                                                       placeholder="Where did this happen?" required>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="incident_date" class="form-label fw-semibold">Incident Date *</label>
                                                <input type="date" class="form-control" id="incident_date" name="incident_date" 
                                                       value="<?php echo htmlspecialchars($_POST['incident_date'] ?? date('Y-m-d')); ?>" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="incident_time" class="form-label fw-semibold">Incident Time *</label>
                                                <input type="time" class="form-control" id="incident_time" name="incident_time" 
                                                       value="<?php echo htmlspecialchars($_POST['incident_time'] ?? date('H:i')); ?>" required>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="evidence" class="form-label fw-semibold">Evidence File (Optional)</label>
                                        <div class="file-upload-area" id="fileUploadArea">
                                            <input type="file" class="form-control" id="evidence" name="evidence" accept=".jpg,.jpeg,.png,.pdf" style="display: none;">
                                            <div class="text-center p-4" id="fileUploadPlaceholder">
                                                <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                                                <p class="mb-2">Drag and drop files here or click to browse</p>
                                                <small class="text-muted">JPG, PNG, or PDF (Max 5MB)</small>
                                            </div>
                                        </div>
                                        <div class="form-text text-muted">Upload screenshots, photos, or documents related to the incident</div>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <div class="form-check card p-3 bg-light">
                                            <input type="checkbox" class="form-check-input" id="anonymous" name="anonymous" <?php echo (isset($_POST['anonymous']) && $_POST['anonymous']) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="anonymous">
                                                <strong class="text-primary">
                                                    <i class="fas fa-user-secret me-2"></i>Report Anonymously
                                                </strong>
                                                <div class="small text-muted mt-1">Your identity will be hidden from the report and only visible to administrators</div>
                                            </label>
                                        </div>
                                    </div>
                                    
                                    <div class="d-flex gap-2 flex-wrap">
                                        <button type="submit" class="btn btn-primary px-4 py-2.5 fw-semibold">
                                            <i class="fas fa-paper-plane me-2"></i> Submit Report
                                        </button>
                                        <a href="dashboard.php" class="btn btn-outline-secondary px-4 py-2.5">
                                            <i class="fas fa-times me-2"></i> Cancel
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-4">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="fas fa-info-circle me-2"></i>Reporting Guidelines
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="guideline-item mb-3">
                                    <div class="d-flex align-items-start">
                                        <div class="guideline-icon bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 32px; height: 32px; flex-shrink: 0;">
                                            <i class="fas fa-clock"></i>
                                        </div>
                                        <div>
                                            <strong class="d-block">Report Promptly</strong>
                                            <small class="text-muted">Report incidents as soon as possible for faster response</small>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="guideline-item mb-3">
                                    <div class="d-flex align-items-start">
                                        <div class="guideline-icon bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 32px; height: 32px; flex-shrink: 0;">
                                            <i class="fas fa-check"></i>
                                        </div>
                                        <div>
                                            <strong class="d-block">Be Accurate</strong>
                                            <small class="text-muted">Provide accurate and detailed information</small>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="guideline-item mb-3">
                                    <div class="d-flex align-items-start">
                                        <div class="guideline-icon bg-info bg-opacity-10 text-info rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 32px; height: 32px; flex-shrink: 0;">
                                            <i class="fas fa-map-marker-alt"></i>
                                        </div>
                                        <div>
                                            <strong class="d-block">Include Details</strong>
                                            <small class="text-muted">Dates, times, and locations are crucial</small>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="guideline-item">
                                    <div class="d-flex align-items-start">
                                        <div class="guideline-icon bg-warning bg-opacity-10 text-warning rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 32px; height: 32px; flex-shrink: 0;">
                                            <i class="fas fa-upload"></i>
                                        </div>
                                        <div>
                                            <strong class="d-block">Upload Evidence</strong>
                                            <small class="text-muted">Include relevant evidence if available</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="fas fa-route me-2"></i>What Happens Next
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="timeline-simple">
                                    <div class="timeline-item-simple d-flex mb-3">
                                        <div class="timeline-number bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 28px; height: 28px; flex-shrink: 0;">1</div>
                                        <div>
                                            <strong class="d-block">Review</strong>
                                            <small class="text-muted">Your report will be reviewed by administrators</small>
                                        </div>
                                    </div>
                                    
                                    <div class="timeline-item-simple d-flex mb-3">
                                        <div class="timeline-number bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 28px; height: 28px; flex-shrink: 0;">2</div>
                                        <div>
                                            <strong class="d-block">Reference</strong>
                                            <small class="text-muted">You'll receive a reference number</small>
                                        </div>
                                    </div>
                                    
                                    <div class="timeline-item-simple d-flex mb-3">
                                        <div class="timeline-number bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 28px; height: 28px; flex-shrink: 0;">3</div>
                                        <div>
                                            <strong class="d-block">Assignment</strong>
                                            <small class="text-muted">Appropriate staff will be assigned</small>
                                        </div>
                                    </div>
                                    
                                    <div class="timeline-item-simple d-flex">
                                        <div class="timeline-number bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 28px; height: 28px; flex-shrink: 0;">4</div>
                                        <div>
                                            <strong class="d-block">Tracking</strong>
                                            <small class="text-muted">You can track the status online</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card bg-danger bg-opacity-10 border-danger">
                            <div class="card-body">
                                <div class="d-flex align-items-start">
                                    <i class="fas fa-exclamation-triangle text-danger fa-2x me-3"></i>
                                    <div>
                                        <h6 class="fw-bold text-danger">Emergency?</h6>
                                        <p class="small text-muted mb-0">For immediate threats, contact school security or emergency services directly.</p>
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
        // File Upload Functionality
        const fileUploadArea = document.getElementById('fileUploadArea');
        const fileInput = document.getElementById('evidence');
        const filePlaceholder = document.getElementById('fileUploadPlaceholder');
        
        if (fileUploadArea && fileInput && filePlaceholder) {
            // Click to upload
            filePlaceholder.addEventListener('click', (e) => {
                e.preventDefault();
                fileInput.click();
            });
            
            // Prevent default drag behaviors
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                fileUploadArea.addEventListener(eventName, preventDefaults, false);
                document.body.addEventListener(eventName, preventDefaults, false);
            });
            
            // Highlight drop area when item is dragged over it
            ['dragenter', 'dragover'].forEach(eventName => {
                fileUploadArea.addEventListener(eventName, highlight, false);
            });
            
            ['dragleave', 'drop'].forEach(eventName => {
                fileUploadArea.addEventListener(eventName, unhighlight, false);
            });
            
            // Handle dropped files
            fileUploadArea.addEventListener('drop', handleDrop, false);
            
            // File input change
            fileInput.addEventListener('change', (e) => {
                if (e.target.files.length > 0) {
                    handleFileSelect(e.target.files[0]);
                }
            });
            
            function preventDefaults(e) {
                e.preventDefault();
                e.stopPropagation();
            }
            
            function highlight(e) {
                fileUploadArea.classList.add('bg-light', 'border-primary', 'border-2');
            }
            
            function unhighlight(e) {
                fileUploadArea.classList.remove('bg-light', 'border-primary', 'border-2');
            }
            
            function handleDrop(e) {
                const dt = e.dataTransfer;
                const files = dt.files;
                
                if (files.length > 0) {
                    handleFileSelect(files[0]);
                }
            }
            
            function handleFileSelect(file) {
                // Show loading state
                fileUploadArea.classList.add('file-upload-loading');
                
                // Validate file type
                const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'];
                if (!allowedTypes.includes(file.type)) {
                    fileUploadArea.classList.remove('file-upload-loading');
                    alert('Invalid file type. Please upload JPG, PNG, or PDF files only.');
                    return;
                }
                
                // Validate file size (5MB)
                const maxSize = 5 * 1024 * 1024;
                if (file.size > maxSize) {
                    fileUploadArea.classList.remove('file-upload-loading');
                    alert('File size too large. Maximum file size is 5MB.');
                    return;
                }
                
                // Get file icon based on type
                let fileIcon = 'fa-file';
                let fileColor = 'text-primary';
                if (file.type.startsWith('image/')) {
                    fileIcon = 'fa-file-image';
                    fileColor = 'text-success';
                } else if (file.type === 'application/pdf') {
                    fileIcon = 'fa-file-pdf';
                    fileColor = 'text-danger';
                }
                
                // Update placeholder with file info
                setTimeout(() => {
                    fileUploadArea.classList.remove('file-upload-loading');
                    filePlaceholder.innerHTML = `
                        <i class="fas ${fileIcon} fa-3x ${fileColor} mb-3"></i>
                        <p class="mb-2 fw-semibold text-truncate" style="max-width: 200px;" title="${file.name}">${file.name}</p>
                        <small class="text-muted">${formatFileSize(file.size)}</small>
                        <div class="mt-2">
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="clearFile(event)">
                                <i class="fas fa-times me-1"></i> Remove
                            </button>
                        </div>
                    `;
                }, 500);
            }
            
            function clearFile(event) {
                if (event) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                fileInput.value = '';
                filePlaceholder.innerHTML = `
                    <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                    <p class="mb-2">Drag and drop files here or click to browse</p>
                    <small class="text-muted">JPG, PNG, or PDF (Max 5MB)</small>
                `;
            }
            
            function formatFileSize(bytes) {
                if (bytes === 0) return '0 Bytes';
                const k = 1024;
                const sizes = ['Bytes', 'KB', 'MB', 'GB'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
            }
        }
        
        // Form validation
        document.getElementById('incidentForm').addEventListener('submit', function(e) {
            const description = document.getElementById('description').value;
            const title = document.getElementById('title').value;
            
            if (description.length < 10) {
                e.preventDefault();
                alert('Please provide a more detailed description (at least 10 characters).');
                return false;
            }
            
            if (title.length < 5) {
                e.preventDefault();
                alert('Please provide a more descriptive title (at least 5 characters).');
                return false;
            }
            
            // Show loading state
            const submitBtn = e.target.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Submitting...';
            submitBtn.disabled = true;
            
            // Reset button after 5 seconds (in case of error)
            setTimeout(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }, 5000);
        });
        
        // Character counter for description
        const descriptionField = document.getElementById('description');
        if (descriptionField) {
            const counter = document.createElement('div');
            counter.className = 'form-text text-muted';
            counter.style.textAlign = 'right';
            counter.style.fontSize = '0.875rem';
            descriptionField.parentNode.appendChild(counter);
            
            function updateCounter() {
                const length = descriptionField.value.length;
                counter.textContent = `${length} characters`;
                
                if (length < 10) {
                    counter.className = 'form-text text-danger';
                } else if (length > 500) {
                    counter.className = 'form-text text-warning';
                } else {
                    counter.className = 'form-text text-muted';
                }
            }
            
            descriptionField.addEventListener('input', updateCounter);
            updateCounter();
        }
        
        // Priority color coding
        const prioritySelect = document.getElementById('priority');
        if (prioritySelect) {
            prioritySelect.addEventListener('change', function() {
                const colors = {
                    'low': 'success',
                    'medium': 'warning', 
                    'high': 'danger',
                    'critical': 'danger'
                };
                
                // Remove existing color classes
                this.classList.remove('border-success', 'border-warning', 'border-danger');
                
                // Add new color class
                if (colors[this.value]) {
                    this.classList.add(`border-${colors[this.value]}`);
                    this.style.borderWidth = '2px';
                }
            });
        }
    </script>
</body>
</html>
