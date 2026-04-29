<?php
/**
 * Secure School Incident Reporting Platform
 * Core Functions File
 */

require_once 'config.php';

// User management functions
class User {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function register($full_name, $email, $password, $role = 'student') {
        // Validate input
        if (empty($full_name) || empty($email) || empty($password)) {
            return ['success' => false, 'message' => 'All fields are required'];
        }
        
        if (!validate_email($email)) {
            return ['success' => false, 'message' => 'Invalid email address'];
        }
        
        if (strlen($password) < 6) {
            return ['success' => false, 'message' => 'Password must be at least 6 characters long'];
        }
        
        // Check if email already exists
        $stmt = $this->db->query("SELECT id FROM users WHERE email = ?", [$email]);
        if ($stmt->fetch()) {
            return ['success' => false, 'message' => 'Email already registered'];
        }
        
        // Hash password and create user
        $hashed_password = password_hash($password, HASH_ALGO);
        $stmt = $this->db->query(
            "INSERT INTO users (full_name, email, password, role) VALUES (?, ?, ?, ?)",
            [$full_name, $email, $hashed_password, $role]
        );
        
        if ($stmt) {
            return ['success' => true, 'message' => 'Registration successful'];
        } else {
            return ['success' => false, 'message' => 'Registration failed'];
        }
    }
    
    public function login($email, $password) {
        if (empty($email) || empty($password)) {
            return ['success' => false, 'message' => 'Email and password are required'];
        }
        
        $stmt = $this->db->query("SELECT * FROM users WHERE email = ?", [$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['full_name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            
            return ['success' => true, 'message' => 'Login successful', 'role' => $user['role']];
        } else {
            return ['success' => false, 'message' => 'Invalid email or password'];
        }
    }
    
    public function logout() {
        session_destroy();
        unset($_SESSION);
        return ['success' => true, 'message' => 'Logged out successfully'];
    }
    
    public function get_user_by_id($id) {
        $stmt = $this->db->query("SELECT id, full_name, email, role, created_at FROM users WHERE id = ?", [$id]);
        return $stmt->fetch();
    }
    
    public function get_all_users($role = null) {
        $sql = "SELECT id, full_name, email, role, created_at FROM users";
        $params = [];
        
        if ($role) {
            $sql .= " WHERE role = ?";
            $params[] = $role;
        }
        
        $sql .= " ORDER BY created_at DESC";
        
        $stmt = $this->db->query($sql, $params);
        return $stmt->fetchAll();
    }
    
    public function get_user_by_email($email) {
        $stmt = $this->db->query("SELECT id, full_name, email, role, created_at FROM users WHERE email = ?", [$email]);
        return $stmt->fetch();
    }
    
    public function create_user($full_name, $email, $password, $role = 'student') {
        // Validate input
        if (empty($full_name) || empty($email) || empty($password)) {
            return ['success' => false, 'message' => 'All fields are required'];
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Invalid email address'];
        }
        
        if (strlen($password) < 6) {
            return ['success' => false, 'message' => 'Password must be at least 6 characters long'];
        }
        
        if (!in_array($role, ['student', 'staff', 'admin'])) {
            return ['success' => false, 'message' => 'Invalid role'];
        }
        
        // Check if email already exists
        if ($this->get_user_by_email($email)) {
            return ['success' => false, 'message' => 'Email address already exists'];
        }
        
        // Hash password and create user
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->db->query(
            "INSERT INTO users (full_name, email, password, role) VALUES (?, ?, ?, ?)",
            [$full_name, $email, $hashed_password, $role]
        );
        
        if ($stmt) {
            return ['success' => true, 'message' => 'User created successfully'];
        } else {
            return ['success' => false, 'message' => 'Failed to create user'];
        }
    }
    
    public function update_user_profile($user_id, $full_name, $email) {
        // Validate input
        if (empty($full_name) || empty($email)) {
            return ['success' => false, 'message' => 'Full name and email are required'];
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Invalid email address'];
        }
        
        // Check if email is taken by another user
        $existing_user = $this->get_user_by_email($email);
        if ($existing_user && $existing_user['id'] != $user_id) {
            return ['success' => false, 'message' => 'Email address is already taken'];
        }
        
        $stmt = $this->db->query(
            "UPDATE users SET full_name = ?, email = ? WHERE id = ?",
            [$full_name, $email, $user_id]
        );
        
        if ($stmt) {
            return ['success' => true, 'message' => 'Profile updated successfully'];
        } else {
            return ['success' => false, 'message' => 'Failed to update profile'];
        }
    }
    
    public function change_password($user_id, $new_password) {
        if (empty($new_password) || strlen($new_password) < 6) {
            return ['success' => false, 'message' => 'Password must be at least 6 characters long'];
        }
        
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $this->db->query(
            "UPDATE users SET password = ? WHERE id = ?",
            [$hashed_password, $user_id]
        );
        
        if ($stmt) {
            return ['success' => true, 'message' => 'Password changed successfully'];
        } else {
            return ['success' => false, 'message' => 'Failed to change password'];
        }
    }
    
    public function update_notification_settings($user_id, $email_notifications, $desktop_notifications) {
        // Check if notification settings column exists, if not add it
        $this->db->query("ALTER TABLE users ADD COLUMN IF NOT EXISTS email_notifications TINYINT(1) DEFAULT 1");
        $this->db->query("ALTER TABLE users ADD COLUMN IF NOT EXISTS desktop_notifications TINYINT(1) DEFAULT 0");
        
        $stmt = $this->db->query(
            "UPDATE users SET email_notifications = ?, desktop_notifications = ? WHERE id = ?",
            [$email_notifications, $desktop_notifications, $user_id]
        );
        
        if ($stmt) {
            return ['success' => true, 'message' => 'Notification settings updated successfully'];
        } else {
            return ['success' => false, 'message' => 'Failed to update notification settings'];
        }
    }
}

// Incident management functions
class Incident {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function create_incident($data) {
        try {
            $stmt = $this->db->query(
                "INSERT INTO incidents (user_id, title, description, category, priority, incident_date, incident_time, location, anonymous, evidence_file, status) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                [
                    $data['user_id'] ?? null,
                    $data['title'],
                    $data['description'],
                    $data['category'],
                    $data['priority'],
                    $data['incident_date'],
                    $data['incident_time'],
                    $data['location'],
                    $data['anonymous'],
                    $data['evidence_file'] ?? null,
                    'new'
                ]
            );
            
            $incident_id = $this->db->lastInsertId();
            
            // Create notification for admin
            $this->create_notification_admin('New incident reported: ' . $data['title'], $incident_id);
            
            return ['success' => true, 'incident_id' => $incident_id];
        } catch (Exception $e) {
            error_log("Error creating incident: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to create incident'];
        }
    }
    
    public function get_incident_by_id($id) {
        $stmt = $this->db->query(
            "SELECT i.*, u.full_name as reporter_name, u.email as reporter_email, 
                    a.full_name as assigned_name 
             FROM incidents i 
             LEFT JOIN users u ON i.user_id = u.id 
             LEFT JOIN users a ON i.assigned_to = a.id 
             WHERE i.id = ?",
            [$id]
        );
        return $stmt->fetch();
    }
    
    public function get_incidents_by_user($user_id, $include_anonymous = false) {
        $sql = "SELECT i.*, u.full_name as assigned_name 
                FROM incidents i 
                LEFT JOIN users u ON i.assigned_to = u.id 
                WHERE i.user_id = ? OR i.anonymous = ?";
        $stmt = $this->db->query($sql, [$user_id, $include_anonymous]);
        return $stmt->fetchAll();
    }
    
    public function get_assigned_incidents($user_id) {
        $stmt = $this->db->query(
            "SELECT i.*, u.full_name as reporter_name 
             FROM incidents i 
             LEFT JOIN users u ON i.user_id = u.id 
             WHERE i.assigned_to = ? 
             ORDER BY i.created_at DESC",
            [$user_id]
        );
        return $stmt->fetchAll();
    }
    
    public function get_all_incidents($filters = []) {
        $sql = "SELECT i.*, u.full_name as reporter_name, a.full_name as assigned_name 
                FROM incidents i 
                LEFT JOIN users u ON i.user_id = u.id 
                LEFT JOIN users a ON i.assigned_to = a.id 
                WHERE 1=1";
        $params = [];
        
        if (!empty($filters['status'])) {
            $sql .= " AND i.status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['priority'])) {
            $sql .= " AND i.priority = ?";
            $params[] = $filters['priority'];
        }
        
        if (!empty($filters['category'])) {
            $sql .= " AND i.category = ?";
            $params[] = $filters['category'];
        }
        
        $sql .= " ORDER BY i.created_at DESC";
        
        $stmt = $this->db->query($sql, $params);
        return $stmt->fetchAll();
    }
    
    public function update_incident_status($incident_id, $status, $updated_by, $note = '') {
        try {
            $this->db->query(
                "UPDATE incidents SET status = ?, assigned_to = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?",
                [$status, $updated_by, $incident_id]
            );
            
            // Add update record
            $this->add_incident_update($incident_id, $updated_by, $note, $status);
            
            // Create notification
            $incident = $this->get_incident_by_id($incident_id);
            if ($incident && !$incident['anonymous'] && $incident['user_id']) {
                $this->create_notification($incident['user_id'], "Your incident status has been updated to: $status", $incident_id);
            }
            
            return ['success' => true];
        } catch (Exception $e) {
            error_log("Error updating incident: " . $e->getMessage());
            return ['success' => false];
        }
    }
    
    public function assign_incident($incident_id, $assigned_to) {
        try {
            $this->db->query(
                "UPDATE incidents SET assigned_to = ?, status = 'under_review', updated_at = CURRENT_TIMESTAMP WHERE id = ?",
                [$assigned_to, $incident_id]
            );
            
            // Create notification for assigned user
            $user = (new User())->get_user_by_id($assigned_to);
            if ($user) {
                $this->create_notification($assigned_to, "You have been assigned a new incident", $incident_id);
            }
            
            return ['success' => true];
        } catch (Exception $e) {
            error_log("Error assigning incident: " . $e->getMessage());
            return ['success' => false];
        }
    }
    
    public function add_incident_update($incident_id, $updated_by, $note, $status) {
        $stmt = $this->db->query(
            "INSERT INTO incident_updates (incident_id, updated_by, note, status) VALUES (?, ?, ?, ?)",
            [$incident_id, $updated_by, $note, $status]
        );
        return $stmt;
    }
    
    public function get_incident_updates($incident_id) {
        $stmt = $this->db->query(
            "SELECT iu.*, u.full_name as updated_by_name 
             FROM incident_updates iu 
             JOIN users u ON iu.updated_by = u.id 
             WHERE iu.incident_id = ? 
             ORDER BY iu.created_at ASC",
            [$incident_id]
        );
        return $stmt->fetchAll();
    }
    
    private function create_notification($user_id, $message, $incident_id = null) {
        $stmt = $this->db->query(
            "INSERT INTO notifications (user_id, message, incident_id) VALUES (?, ?, ?)",
            [$user_id, $message, $incident_id]
        );
        return $stmt;
    }
    
    private function create_notification_admin($message, $incident_id = null) {
        // Get all admin users
        $stmt = $this->db->query("SELECT id FROM users WHERE role = 'admin'");
        $admins = $stmt->fetchAll();
        
        foreach ($admins as $admin) {
            $this->create_notification($admin['id'], $message, $incident_id);
        }
    }
    
    public function get_statistics() {
        $stats = [];
        
        // Total incidents
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM incidents");
        $stats['total_incidents'] = $stmt->fetch()['total'];
        
        // By status
        $stmt = $this->db->query("SELECT status, COUNT(*) as count FROM incidents GROUP BY status");
        $stats['by_status'] = $stmt->fetchAll();
        
        // By priority
        $stmt = $this->db->query("SELECT priority, COUNT(*) as count FROM incidents GROUP BY priority");
        $stats['by_priority'] = $stmt->fetchAll();
        
        // By category
        $stmt = $this->db->query("SELECT category, COUNT(*) as count FROM incidents GROUP BY category");
        $stats['by_category'] = $stmt->fetchAll();
        
        // Recent (last 30 days)
        $stmt = $this->db->query("SELECT COUNT(*) as count FROM incidents WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
        $stats['last_30_days'] = $stmt->fetch()['count'];
        
        // Resolved today
        $stmt = $this->db->query("SELECT COUNT(*) as count FROM incidents WHERE status = 'resolved' AND DATE(updated_at) = CURDATE()");
        $stats['resolved_today'] = $stmt->fetch()['count'];
        
        return $stats;
    }
}

// Notification functions
class Notification {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function get_user_notifications($user_id, $limit = 10) {
        $stmt = $this->db->query(
            "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT ?",
            [$user_id, $limit]
        );
        return $stmt->fetchAll();
    }
    
    public function mark_as_read($notification_id) {
        $stmt = $this->db->query(
            "UPDATE notifications SET seen = TRUE WHERE id = ?",
            [$notification_id]
        );
        return $stmt;
    }
    
    public function get_unread_count($user_id) {
        $stmt = $this->db->query(
            "SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND seen = FALSE",
            [$user_id]
        );
        return $stmt->fetch()['count'];
    }
}

// Helper functions for UI
function get_priority_badge($priority) {
    $classes = [
        'low' => 'bg-green-100 text-green-800',
        'medium' => 'bg-yellow-100 text-yellow-800',
        'high' => 'bg-orange-100 text-orange-800',
        'critical' => 'bg-red-100 text-red-800'
    ];
    
    return '<span class="px-2 py-1 text-xs rounded-full ' . $classes[$priority] . '">' . ucfirst($priority) . '</span>';
}

function get_status_badge($status) {
    $classes = [
        'new' => 'bg-blue-100 text-blue-800',
        'under_review' => 'bg-purple-100 text-purple-800',
        'investigating' => 'bg-indigo-100 text-indigo-800',
        'resolved' => 'bg-green-100 text-green-800',
        'closed' => 'bg-gray-100 text-gray-800'
    ];
    
    $labels = [
        'new' => 'New',
        'under_review' => 'Under Review',
        'investigating' => 'Investigating',
        'resolved' => 'Resolved',
        'closed' => 'Closed'
    ];
    
    return '<span class="px-2 py-1 text-xs rounded-full ' . $classes[$status] . '">' . $labels[$status] . '</span>';
}

function format_date($date) {
    return date('M j, Y', strtotime($date));
}

function format_datetime($datetime) {
    return date('M j, Y h:i A', strtotime($datetime));
}

function get_category_label($category) {
    $labels = [
        'bullying' => 'Bullying',
        'physical_violence' => 'Physical Violence',
        'sexual_harassment' => 'Sexual Harassment',
        'theft' => 'Theft',
        'drug_abuse' => 'Drug Abuse',
        'cyberbullying' => 'Cyberbullying',
        'vandalism' => 'Vandalism',
        'discrimination' => 'Discrimination',
        'teacher_misconduct' => 'Teacher Misconduct',
        'unsafe_facilities' => 'Unsafe Facilities',
        'emergency_threats' => 'Emergency Threats',
        'other' => 'Other'
    ];
    
    return $labels[$category] ?? ucfirst($category);
}
?>
