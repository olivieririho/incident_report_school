<?php
/**
 * Secure School Incident Reporting Platform
 * User Management Page
 */

require_once 'config.php';
require_once 'functions.php';
require_once 'access_control.php';

require_admin();

$user = new User();
$users = $user->get_all_users();

$page_title = 'User Management';
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
                        <i class="fas fa-users me-2 text-primary"></i>User Management
                    </h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                            <i class="fas fa-plus me-2"></i>Add User
                        </button>
                    </div>
                </div>
                
                <!-- Users Statistics -->
                <div class="row mb-4">
                    <div class="col-md-3 mb-3">
                        <div class="card stat-card primary">
                            <div class="stat-value"><?php echo count($users); ?></div>
                            <div class="stat-label">Total Users</div>
                            <div class="stat-trend">
                                <i class="fas fa-users me-1"></i>All registered users
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card stat-card success">
                            <div class="stat-value"><?php echo count(array_filter($users, fn($u) => $u['role'] === 'student')); ?></div>
                            <div class="stat-label">Students</div>
                            <div class="stat-trend">
                                <i class="fas fa-graduation-cap me-1"></i>Student accounts
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card stat-card info">
                            <div class="stat-value"><?php echo count(array_filter($users, fn($u) => $u['role'] === 'staff')); ?></div>
                            <div class="stat-label">Staff</div>
                            <div class="stat-trend">
                                <i class="fas fa-chalkboard-teacher me-1"></i>Staff members
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card stat-card secondary">
                            <div class="stat-value"><?php echo count(array_filter($users, fn($u) => $u['role'] === 'admin')); ?></div>
                            <div class="stat-label">Admins</div>
                            <div class="stat-trend">
                                <i class="fas fa-user-shield me-1"></i>Administrators
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Users Table -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-list me-2"></i>All Users
                            <span class="badge bg-primary ms-2"><?php echo count($users); ?></span>
                        </h5>
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-success" onclick="exportUsers()">
                                <i class="fas fa-download"></i> Export
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if (empty($users)): ?>
                            <div class="text-center py-5">
                                <div class="empty-state">
                                    <i class="fas fa-users fa-4x text-muted mb-3"></i>
                                    <h5 class="text-muted">No users found</h5>
                                    <p class="text-muted">No users have been registered yet.</p>
                                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                                        <i class="fas fa-plus me-2"></i>Add First User
                                    </button>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle" id="usersTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="min-width: 80px;">ID</th>
                                            <th style="min-width: 200px;">Name</th>
                                            <th style="min-width: 200px;">Email</th>
                                            <th style="min-width: 100px;">Role</th>
                                            <th style="min-width: 120px;">Status</th>
                                            <th style="min-width: 150px;">Created</th>
                                            <th style="min-width: 120px;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($users as $user_item): ?>
                                            <tr>
                                                <td>
                                                    <div class="fw-bold text-primary">#<?php echo str_pad($user_item['id'], 6, '0', STR_PAD_LEFT); ?></div>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                                            <i class="fas fa-user text-primary"></i>
                                                        </div>
                                                        <div>
                                                            <div class="fw-semibold text-gray-900"><?php echo htmlspecialchars($user_item['full_name']); ?></div>
                                                            <?php if ($user_item['id'] == $_SESSION['user_id']): ?>
                                                                <small class="text-muted">(You)</small>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <i class="fas fa-envelope text-muted me-2"></i>
                                                        <span><?php echo htmlspecialchars($user_item['email']); ?></span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <?php
                                                    $role_colors = [
                                                        'admin' => 'danger',
                                                        'staff' => 'info', 
                                                        'student' => 'success'
                                                    ];
                                                    $role_icons = [
                                                        'admin' => 'user-shield',
                                                        'staff' => 'chalkboard-teacher',
                                                        'student' => 'graduation-cap'
                                                    ];
                                                    $color = $role_colors[$user_item['role']] ?? 'secondary';
                                                    $icon = $role_icons[$user_item['role']] ?? 'user';
                                                    ?>
                                                    <span class="badge bg-<?php echo $color; ?> text-white">
                                                        <i class="fas fa-<?php echo $icon; ?> me-1"></i>
                                                        <?php echo ucfirst($user_item['role']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-success">
                                                        <i class="fas fa-check-circle me-1"></i>Active
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <i class="fas fa-calendar text-muted me-2"></i>
                                                        <span class="small"><?php echo format_date($user_item['created_at']); ?></span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        <button class="btn btn-outline-primary" onclick="editUser(<?php echo $user_item['id']; ?>)" title="Edit User">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <?php if ($user_item['id'] != $_SESSION['user_id'] && $user_item['role'] !== 'admin'): ?>
                                                            <button class="btn btn-outline-danger" onclick="deleteUser(<?php echo $user_item['id']; ?>)" title="Delete User">
                                                                <i class="fas fa-trash"></i>
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
    
    <!-- Add User Modal -->
    <div class="modal fade" id="addUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addUserForm">
                        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                        <div class="mb-3">
                            <label for="fullName" class="form-label">Full Name *</label>
                            <input type="text" class="form-control" id="fullName" name="full_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address *</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password *</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                            <div class="form-text">Minimum 6 characters</div>
                        </div>
                        <div class="mb-3">
                            <label for="role" class="form-label">Role *</label>
                            <select class="form-select" id="role" name="role" required>
                                <option value="">Select Role</option>
                                <option value="student">Student</option>
                                <option value="staff">Staff</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveUser">Add User</button>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://kit.fontawesome.com/your-fontawesome-kit.js" crossorigin="anonymous"></script>
    <script src="js/main.js"></script>
    
    <script>
        // Add User functionality
        document.getElementById('saveUser').addEventListener('click', function() {
            const form = document.getElementById('addUserForm');
            const formData = new FormData(form);
            
            // Validate form
            const fullName = document.getElementById('fullName').value;
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const role = document.getElementById('role').value;
            
            if (!fullName || !email || !password || !role) {
                alert('Please fill in all required fields.');
                return;
            }
            
            if (password.length < 6) {
                alert('Password must be at least 6 characters long.');
                return;
            }
            
            // Show loading state
            const saveBtn = document.getElementById('saveUser');
            const originalText = saveBtn.innerHTML;
            saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Adding...';
            saveBtn.disabled = true;
            
            fetch('add_user.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    bootstrap.Modal.getInstance(document.getElementById('addUserModal')).hide();
                    location.reload();
                } else {
                    alert('Error: ' + (data.message || 'Failed to add user'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while adding the user. Please try again.');
            })
            .finally(() => {
                saveBtn.innerHTML = originalText;
                saveBtn.disabled = false;
            });
        });
        
        // Edit User (placeholder)
        function editUser(userId) {
            alert('Edit user functionality will be implemented soon.');
        }
        
        // Delete User (placeholder)
        function deleteUser(userId) {
            if (confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
                alert('Delete user functionality will be implemented soon.');
            }
        }
        
        // Export Users (placeholder)
        function exportUsers() {
            alert('Export functionality will be implemented soon.');
        }
    </script>
</body>
</html>
