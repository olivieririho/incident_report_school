/**
 * Secure School Incident Reporting Platform
 * Main JavaScript File
 */

// Global variables
let csrfToken = '';
let currentIncidentId = null;

// Initialize on document ready
document.addEventListener('DOMContentLoaded', function() {
    initializeApp();
});

function initializeApp() {
    // Get CSRF token from meta tag or form
    csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
                document.querySelector('input[name="csrf_token"]')?.value || '';
    
    // Initialize tooltips
    initializeTooltips();
    
    // Initialize file uploads
    initializeFileUploads();
    
    // Initialize form validations
    initializeFormValidations();
    
    // Initialize notifications
    initializeNotifications();
    
    // Initialize auto-refresh for dashboard
    if (document.querySelector('.dashboard-stats')) {
        initializeAutoRefresh();
    }
}

// Tooltips
function initializeTooltips() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

// File Upload Handling with Drag and Drop
function initializeFileUploads() {
    const fileInputs = document.querySelectorAll('input[type="file"]');
    
    fileInputs.forEach(input => {
        input.addEventListener('change', function(e) {
            handleFileSelect(e.target);
        });
    });
    
    // Initialize drag and drop for file upload area
    const fileUploadArea = document.getElementById('fileUploadArea');
    const fileInput = document.getElementById('evidence');
    const fileUploadPlaceholder = document.getElementById('fileUploadPlaceholder');
    
    if (fileUploadArea && fileInput && fileUploadPlaceholder) {
        // Click to browse
        fileUploadArea.addEventListener('click', function() {
            fileInput.click();
        });
        
        // Drag and drop events
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            fileUploadArea.addEventListener(eventName, preventDefaults, false);
        });
        
        ['dragenter', 'dragover'].forEach(eventName => {
            fileUploadArea.addEventListener(eventName, highlight, false);
        });
        
        ['dragleave', 'drop'].forEach(eventName => {
            fileUploadArea.addEventListener(eventName, unhighlight, false);
        });
        
        fileUploadArea.addEventListener('drop', handleDrop, false);
    }
}

function preventDefaults(e) {
    e.preventDefault();
    e.stopPropagation();
}

function highlight(e) {
    const fileUploadArea = document.getElementById('fileUploadArea');
    if (fileUploadArea) {
        fileUploadArea.classList.add('dragover');
    }
}

function unhighlight(e) {
    const fileUploadArea = document.getElementById('fileUploadArea');
    if (fileUploadArea) {
        fileUploadArea.classList.remove('dragover');
    }
}

function handleDrop(e) {
    const fileInput = document.getElementById('evidence');
    if (!fileInput) return;
    
    const dt = e.dataTransfer;
    const files = dt.files;
    
    if (files.length > 0) {
        fileInput.files = files;
        handleFileSelect(fileInput);
    }
}

function handleFileSelect(input) {
    const file = input.files[0];
    if (!file) return;
    
    // Validate file size (5MB max)
    const maxSize = 5 * 1024 * 1024; // 5MB
    if (file.size > maxSize) {
        showNotification('File size exceeds 5MB limit', 'danger');
        input.value = '';
        return;
    }
    
    // Validate file type
    const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'];
    if (!allowedTypes.includes(file.type)) {
        showNotification('Invalid file type. Only JPG, PNG, and PDF files are allowed', 'danger');
        input.value = '';
        return;
    }
    
    // Update file upload placeholder
    const fileUploadPlaceholder = document.getElementById('fileUploadPlaceholder');
    if (fileUploadPlaceholder) {
        fileUploadPlaceholder.innerHTML = `
            <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
            <p class="mb-2 fw-semibold">${file.name}</p>
            <small class="text-muted">${formatFileSize(file.size)}</small>
        `;
    }
    
    // Show file info for regular file inputs
    if (!fileUploadPlaceholder) {
        const fileInfo = document.createElement('div');
        fileInfo.className = 'mt-2 p-2 bg-light rounded';
        fileInfo.innerHTML = `
            <small class="text-muted">
                <strong>Selected file:</strong> ${file.name} 
                (${formatFileSize(file.size)})
            </small>
        `;
        
        // Remove existing file info
        const existing = input.parentNode.querySelector('.file-info');
        if (existing) existing.remove();
        
        fileInfo.classList.add('file-info');
        input.parentNode.appendChild(fileInfo);
    }
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

// Form Validations
function initializeFormValidations() {
    const forms = document.querySelectorAll('form');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateForm(form)) {
                e.preventDefault();
                return false;
            }
        });
    });
}

function validateForm(form) {
    let isValid = true;
    const requiredFields = form.querySelectorAll('[required]');
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            showFieldError(field, 'This field is required');
            isValid = false;
        } else {
            clearFieldError(field);
        }
    });
    
    // Validate email fields
    const emailFields = form.querySelectorAll('input[type="email"]');
    emailFields.forEach(field => {
        if (field.value && !isValidEmail(field.value)) {
            showFieldError(field, 'Please enter a valid email address');
            isValid = false;
        }
    });
    
    return isValid;
}

function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

function showFieldError(field, message) {
    clearFieldError(field);
    
    field.classList.add('is-invalid');
    
    const errorDiv = document.createElement('div');
    errorDiv.className = 'invalid-feedback';
    errorDiv.textContent = message;
    
    field.parentNode.appendChild(errorDiv);
}

function clearFieldError(field) {
    field.classList.remove('is-invalid');
    const errorDiv = field.parentNode.querySelector('.invalid-feedback');
    if (errorDiv) errorDiv.remove();
}

// Notifications
function initializeNotifications() {
    // Auto-hide success notifications after 5 seconds
    const successAlerts = document.querySelectorAll('.alert-success');
    successAlerts.forEach(alert => {
        setTimeout(() => {
            if (alert.parentNode) {
                alert.remove();
            }
        }, 5000);
    });
    
    // Handle notification dismissal
    const dismissButtons = document.querySelectorAll('.btn-close');
    dismissButtons.forEach(button => {
        button.addEventListener('click', function() {
            const alert = button.closest('.alert');
            if (alert) alert.remove();
        });
    });
}

function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(notification);
    
    // Auto-hide after 5 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 5000);
}

// Optimized auto-refresh for dashboard
function initializeAutoRefresh() {
    let refreshInterval = null;
    let isRefreshing = false;
    
    // Only start auto-refresh if user is active
    function startAutoRefresh() {
        if (refreshInterval) return;
        
        refreshInterval = setInterval(() => {
            if (document.visibilityState === 'visible' && !isRefreshing) {
                refreshDashboardStats();
            }
        }, 60000); // Increased to 60 seconds to reduce CPU usage
    }
    
    function stopAutoRefresh() {
        if (refreshInterval) {
            clearInterval(refreshInterval);
            refreshInterval = null;
        }
    }
    
    // Start auto-refresh
    startAutoRefresh();
    
    // Stop refresh when page is hidden
    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            stopAutoRefresh();
        } else {
            startAutoRefresh();
        }
    });
    
    // Stop refresh when user is inactive
    let inactivityTimer;
    function resetInactivityTimer() {
        clearTimeout(inactivityTimer);
        inactivityTimer = setTimeout(() => {
            stopAutoRefresh();
        }, 300000); // 5 minutes of inactivity
    }
    
    ['mousedown', 'keypress', 'scroll', 'touchstart'].forEach(event => {
        document.addEventListener(event, resetInactivityTimer, true);
    });
    
    resetInactivityTimer();
}

function refreshDashboardStats() {
    // Only refresh if we're on a dashboard page
    if (!document.querySelector('.stat-value')) return;
    
    // Prevent multiple simultaneous requests
    if (window.isRefreshing) return;
    window.isRefreshing = true;
    
    // Use a simple timestamp cache to prevent unnecessary requests
    const now = Date.now();
    if (window.lastRefresh && (now - window.lastRefresh) < 30000) {
        window.isRefreshing = false;
        return;
    }
    
    fetch('api/dashboard_stats.php', {
        method: 'GET',
        headers: {
            'Cache-Control': 'no-cache',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (!response.ok) throw new Error('Network response was not ok');
        return response.json();
    })
    .then(data => {
        updateDashboardStats(data);
        window.lastRefresh = now;
    })
    .catch(error => {
        console.error('Error refreshing dashboard stats:', error);
    })
    .finally(() => {
        window.isRefreshing = false;
    });
}

function updateDashboardStats(stats) {
    // Use requestAnimationFrame for smoother updates
    requestAnimationFrame(() => {
        // Update statistics cards with minimal DOM manipulation
        const statCards = document.querySelectorAll('.stat-card');
        statCards.forEach(card => {
            const statType = card.dataset.statType;
            const value = stats[statType];
            if (value !== undefined) {
                const valueElement = card.querySelector('.stat-value');
                if (valueElement && valueElement.textContent !== value.toString()) {
                    valueElement.textContent = value;
                    // Simple fade animation
                    valueElement.style.opacity = '0.5';
                    setTimeout(() => {
                        valueElement.style.opacity = '1';
                    }, 100);
                }
            }
        });
    });
}

// Incident Management
function assignIncident(incidentId) {
    currentIncidentId = incidentId;
    
    const modal = new bootstrap.Modal(document.getElementById('assignmentModal'));
    modal.show();
}

function saveAssignment() {
    const assignedTo = document.getElementById('assignedTo').value;
    
    if (!assignedTo) {
        showNotification('Please select a staff member', 'warning');
        return;
    }
    
    const formData = new FormData();
    formData.append('incident_id', currentIncidentId);
    formData.append('assigned_to', assignedTo);
    formData.append('csrf_token', csrfToken);
    
    fetch('assign_incident.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Incident assigned successfully', 'success');
            bootstrap.Modal.getInstance(document.getElementById('assignmentModal')).hide();
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification('Error: ' + data.message, 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred while assigning the incident', 'danger');
    });
}

function updateIncidentStatus(incidentId) {
    currentIncidentId = incidentId;
    
    const modal = new bootstrap.Modal(document.getElementById('updateModal'));
    modal.show();
}

function saveStatusUpdate() {
    const status = document.getElementById('newStatus').value;
    const note = document.getElementById('updateNote').value;
    
    if (!status || !note) {
        showNotification('Please provide both status and note', 'warning');
        return;
    }
    
    const formData = new FormData();
    formData.append('incident_id', currentIncidentId);
    formData.append('status', status);
    formData.append('note', note);
    formData.append('csrf_token', csrfToken);
    
    fetch('update_incident.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Status updated successfully', 'success');
            bootstrap.Modal.getInstance(document.getElementById('updateModal')).hide();
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification('Error: ' + data.message, 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred while updating the incident', 'danger');
    });
}

// Search and Filter
function initializeSearch() {
    const searchInput = document.getElementById('searchInput');
    if (!searchInput) return;
    
    let searchTimeout;
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            performSearch(searchInput.value);
        }, 300);
    });
}

function performSearch(query) {
    if (!query) {
        // Reset search
        const rows = document.querySelectorAll('tbody tr');
        rows.forEach(row => row.style.display = '');
        return;
    }
    
    const rows = document.querySelectorAll('tbody tr');
    const lowerQuery = query.toLowerCase();
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(lowerQuery) ? '' : 'none';
    });
}

// Export functionality
function exportIncidents(format = 'csv') {
    const url = `export_incidents.php?format=${format}`;
    window.open(url, '_blank');
}

// Print functionality
function printIncident(incidentId) {
    const url = `print_incident.php?id=${incidentId}`;
    window.open(url, '_blank');
}

// Utility functions
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

function formatDateTime(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function confirmAction(message, callback) {
    if (confirm(message)) {
        callback();
    }
}

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    // Ctrl/Cmd + K for search
    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
        e.preventDefault();
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.focus();
        }
    }
    
    // Escape to close modals
    if (e.key === 'Escape') {
        const openModal = document.querySelector('.modal.show');
        if (openModal) {
            bootstrap.Modal.getInstance(openModal).hide();
        }
    }
});

// Page visibility handling
document.addEventListener('visibilitychange', function() {
    if (document.visibilityState === 'visible') {
        // Refresh data when page becomes visible
        if (document.querySelector('.dashboard-stats')) {
            refreshDashboardStats();
        }
    }
});

// Error handling
window.addEventListener('error', function(e) {
    console.error('JavaScript error:', e.error);
    // You could send this to an error logging service
});

// Performance monitoring
window.addEventListener('load', function() {
    if (performance.timing) {
        const loadTime = performance.timing.loadEventEnd - performance.timing.navigationStart;
        console.log('Page load time:', loadTime + 'ms');
    }
});

// Initialize everything when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeApp);
} else {
    initializeApp();
}

// Mobile menu toggle functionality
document.addEventListener('DOMContentLoaded', function() {
    const mobileMenuToggle = document.getElementById('mobileMenuToggle');
    const sidebar = document.querySelector('.sidebar');
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    
    if (mobileMenuToggle && sidebar && sidebarOverlay) {
        mobileMenuToggle.addEventListener('click', function() {
            sidebar.classList.toggle('active');
            sidebarOverlay.classList.toggle('active');
        });
        
        sidebarOverlay.addEventListener('click', function() {
            sidebar.classList.remove('active');
            sidebarOverlay.classList.remove('active');
        });
    }
});
