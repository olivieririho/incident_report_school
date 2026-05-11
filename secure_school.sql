-- Secure School Incident Reporting Platform Database Schema
-- Created for professional school safety management

CREATE DATABASE IF NOT EXISTS secure_school;
USE secure_school;

-- Users table for authentication and role management
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('student', 'staff', 'admin') NOT NULL DEFAULT 'student',
    email_notifications TINYINT(1) DEFAULT 1,
    desktop_notifications TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role)
);

-- Incidents table for storing all incident reports
CREATE TABLE incidents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    category ENUM('bullying', 'physical_violence', 'sexual_harassment', 'theft', 'drug_abuse', 'cyberbullying', 'vandalism', 'discrimination', 'teacher_misconduct', 'unsafe_facilities', 'emergency_threats', 'other') NOT NULL,
    priority ENUM('low', 'medium', 'high', 'critical') NOT NULL DEFAULT 'medium',
    incident_date DATE NOT NULL,
    incident_time TIME NOT NULL,
    location VARCHAR(255) NOT NULL,
    anonymous BOOLEAN DEFAULT FALSE,
    evidence_file VARCHAR(255) NULL,
    status ENUM('new', 'under_review', 'investigating', 'resolved', 'closed') NOT NULL DEFAULT 'new',
    assigned_to INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_status (status),
    INDEX idx_priority (priority),
    INDEX idx_category (category),
    INDEX idx_assigned_to (assigned_to),
    INDEX idx_created_at (created_at)
);

-- Incident updates table for tracking progress and notes
CREATE TABLE incident_updates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    incident_id INT NOT NULL,
    updated_by INT NOT NULL,
    note TEXT NOT NULL,
    status ENUM('new', 'under_review', 'investigating', 'resolved', 'closed') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (incident_id) REFERENCES incidents(id) ON DELETE CASCADE,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_incident_id (incident_id),
    INDEX idx_updated_by (updated_by)
);

-- Notifications table for user alerts
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    message TEXT NOT NULL,
    seen BOOLEAN DEFAULT FALSE,
    incident_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (incident_id) REFERENCES incidents(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_seen (seen),
    INDEX idx_incident_id (incident_id)
);

-- Insert default admin user (password: admin123)
INSERT INTO users (full_name, email, password, role) VALUES 
('System Administrator', 'admin@school.edu', '$2y$10$zDbccJIdsnDX4XNJulLLG.5uRW7oaAjbxyaFvpj2BUv/ymkNXJsq.', 'admin');

-- Insert sample users for testing
INSERT INTO users (full_name, email, password, role) VALUES 
('John Student', 'john@school.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student'),
('Jane Teacher', 'jane@school.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'staff'),
('Bob Counselor', 'bob@school.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'staff');

-- Create uploads directory structure note
-- The following directories should be created:
-- uploads/evidence/

