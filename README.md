# Secure School Incident Reporting Platform

A comprehensive web application for schools to safely report, track, and manage incidents including bullying, violence, harassment, and other safety concerns.

## Features

### 🔐 Security & Privacy
- **Anonymous Reporting**: Students can report incidents without revealing their identity
- **Role-Based Access**: Different access levels for students, staff, and administrators
- **Secure Authentication**: Password hashing, session management, and CSRF protection
- **Confidential Handling**: Sensitive information protected with proper access controls

### 📊 Incident Management
- **Comprehensive Categories**: Support for bullying, violence, harassment, theft, drug abuse, cyberbullying, vandalism, discrimination, misconduct, unsafe facilities, and emergencies
- **Priority Levels**: Low, Medium, High, and Critical priority classification
- **Status Tracking**: New → Under Review → Investigating → Resolved → Closed
- **Evidence Upload**: Support for JPG, PNG, and PDF file attachments

### 👥 User Roles
- **Students**: Report incidents, track status, view own reports
- **Staff/Teachers**: Receive assigned incidents, add notes, update progress
- **Administrators**: Full system management, user administration, analytics

### 📈 Analytics & Reporting
- **Real-time Dashboards**: Statistics, charts, and key metrics
- **Incident Trends**: Monthly reports, category analysis, location tracking
- **Export Functionality**: CSV and PDF export capabilities
- **Search & Filter**: Advanced filtering by status, priority, category, date

### 🔔 Notifications
- **Real-time Alerts**: New incident notifications, assignment alerts
- **Status Updates**: Automatic notifications when incident status changes
- **Unread Count**: Badge notifications for unread messages

## Technology Stack

- **Backend**: PHP 8+ with Object-Oriented Programming
- **Database**: MySQL with PDO prepared statements
- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **UI Framework**: Bootstrap 5
- **Charts**: Chart.js for data visualization
- **Icons**: Font Awesome

## Installation

### Prerequisites
- PHP 8.0 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- XAMPP/WAMP/MAMP (for local development)

### Step 1: Database Setup
1. Import the database schema:
   ```sql
   mysql -u root -p < secure_school.sql
   ```

2. Verify database creation:
   ```sql
   USE secure_school;
   SHOW TABLES;
   ```

### Step 2: Configuration
1. Copy and configure `config.php`:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'secure_school');
   define('DB_USER', 'your_db_user');
   define('DB_PASS', 'your_db_password');
   ```

2. Create uploads directory:
   ```bash
   mkdir -p uploads/evidence/
   chmod 755 uploads/evidence/
   ```

### Step 3: Web Server Setup
1. Place files in web root (`htdocs/projectines/incident/`)
2. Ensure PHP error reporting is enabled for development
3. Configure virtual host if needed

### Step 4: Access the Application
- URL: `http://localhost/projectines/incident/`
- Default Admin: `admin@school.edu` / `admin123`

## Default Accounts

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@school.edu | admin123 |
| Student | john@school.edu | password |
| Staff | jane@school.edu | password |
| Counselor | bob@school.edu | password |

## File Structure

```
incident/
├── secure_school.sql          # Database schema
├── config.php                 # Configuration settings
├── functions.php              # Core functions and classes
├── index.php                  # Landing page (redirects)
├── login.php                  # User login
├── register.php               # User registration
├── logout.php                 # User logout
├── dashboard.php              # Student/Staff dashboard
├── admin_dashboard.php        # Admin dashboard with analytics
├── report.php                 # Incident reporting form
├── incidents.php               # Incident listing
├── incident_details.php       # Incident details and updates
├── assign_incident.php        # AJAX handler for assignments
├── update_incident.php        # AJAX handler for status updates
├── export_incidents.php       # Export functionality
├── includes/
│   ├── navbar.php             # Navigation component
│   └── sidebar.php            # Sidebar navigation
├── css/
│   └── style.css              # Main stylesheet
├── js/
│   └── main.js                # JavaScript functionality
└── uploads/
    └── evidence/               # File upload directory
```

## Security Features

- **Password Hashing**: Uses `password_hash()` with bcrypt
- **Prepared Statements**: All database queries use PDO prepared statements
- **CSRF Protection**: CSRF tokens for all forms
- **Session Security**: Secure session configuration with HttpOnly cookies
- **Input Validation**: Server-side validation for all inputs
- **File Upload Security**: Type and size validation for evidence uploads
- **Role-Based Access**: Strict permission checks for all operations

## Usage Guide

### For Students
1. Register an account or login with existing credentials
2. Click "Report Incident" to submit a new report
3. Choose anonymous reporting if desired
4. Fill in incident details and upload evidence if available
5. Track incident status on your dashboard
6. Receive notifications about updates

### For Staff/Teachers
1. Login with staff credentials
2. View assigned incidents on dashboard
3. Update incident status and add notes
4. Communicate progress through incident updates
5. Mark incidents as resolved when appropriate

### For Administrators
1. Login with admin credentials
2. Monitor all incidents through admin dashboard
3. Assign incidents to appropriate staff members
4. View analytics and generate reports
5. Manage user accounts and system settings
6. Export data for external analysis

## API Endpoints

### AJAX Handlers
- `assign_incident.php` - Assign incident to staff member
- `update_incident.php` - Update incident status and add notes
- `export_incidents.php` - Export incident data (CSV/PDF)

### Data Flow
1. User submits incident report
2. System creates incident record
3. Admin receives notification
4. Admin assigns to staff member
5. Staff investigates and updates status
6. User receives status updates

## Customization

### Adding New Incident Categories
1. Update database schema in `secure_school.sql`
2. Modify category options in `report.php`
3. Update `get_category_label()` function in `functions.php`

### Customizing User Roles
1. Modify `users` table enum in database
2. Update role checks in `config.php`
3. Adjust permissions in various PHP files

### Branding
1. Update `APP_NAME` in `config.php`
2. Modify colors in `css/style.css`
3. Update logo and favicon

## Troubleshooting

### Common Issues

**Database Connection Error**
- Verify MySQL server is running
- Check database credentials in `config.php`
- Ensure database exists and user has permissions

**File Upload Not Working**
- Check `uploads/evidence/` directory permissions
- Verify PHP upload limits in `php.ini`
- Ensure sufficient disk space

**Session Issues**
- Check PHP session configuration
- Verify browser cookie settings
- Ensure proper session timeout settings

**Performance Issues**
- Optimize database queries with indexes
- Enable PHP OPcache
- Consider implementing caching for dashboard stats

### Error Logging
- Check PHP error logs: `/var/log/apache2/error.log` or XAMPP logs
- Enable error reporting in development mode
- Monitor browser console for JavaScript errors

## Development

### Code Standards
- Follow PSR-12 coding standards
- Use meaningful variable and function names
- Add comments for complex logic
- Maintain consistent indentation and formatting

### Testing
- Test all user roles and permissions
- Verify file upload functionality
- Test form validations
- Check responsive design on various devices

### Contributing
1. Fork the repository
2. Create feature branch
3. Make changes with proper testing
4. Submit pull request with description

## Support

For technical support or questions:
- Check troubleshooting section above
- Review error logs for specific issues
- Test with default accounts first
- Verify database connection and permissions

## License

This project is for educational purposes. Please ensure compliance with your institution's policies and local regulations regarding data privacy and incident reporting.

## Version History

- **v1.0.0** - Initial release with core functionality
- Complete incident reporting system
- User authentication and role management
- File upload and evidence handling
- Analytics and reporting dashboard
- Notification system
- Export functionality

---

**Note**: This platform handles sensitive information. Ensure proper security measures, regular updates, and compliance with privacy laws and educational institution policies.
