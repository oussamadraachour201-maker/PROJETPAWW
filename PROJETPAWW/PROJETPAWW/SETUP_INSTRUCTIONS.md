# Attendance Management System - Setup & Installation

## Project Overview

A complete web-based Student Attendance Management System for Algiers University with role-based access for students, professors, and administrators.

**Frontend**: HTML5, CSS3, jQuery, Bootstrap 5, Chart.js  
**Backend**: PHP 7.4+  
**Database**: MariaDB/MySQL

---

## System Requirements

- PHP 7.4 or higher
- MariaDB 10.3+ or MySQL 5.7+
- Web Server (Apache/Nginx)
- Modern web browser (Chrome, Firefox, Safari, Edge)

---

## Installation Steps

### 1. Database Setup

#### Option A: Using PhpMyAdmin
1. Open PhpMyAdmin
2. Create a new database named `attendance_system`
3. Import the `database.sql` file into this database

#### Option B: Using MySQL Command Line
```bash
mysql -u root -p
CREATE DATABASE attendance_system;
USE attendance_system;
SOURCE database.sql;
```

### 2. Configure Database Connection

Edit `api/db.php` with your database credentials:

```php
$db_host = 'localhost';      // Your database host
$db_user = 'root';           // Your database user
$db_password = '';           // Your database password
$db_name = 'attendance_system';
```

### 3. Create Upload Directory

Ensure the `uploads/justifications/` directory exists and has write permissions:

```bash
mkdir -p uploads/justifications
chmod 755 uploads/justifications
```

### 4. Place Files in Web Root

Copy all project files to your web server root directory:
- `c:\PROJETPAW\` → `C:\xampp\htdocs\PROJETPAW\` (if using XAMPP)
- Or your server's web root directory

### 5. Enable Specific PHP Extensions

Ensure these extensions are enabled in `php.ini`:
- `extension=mysqli` (MySQLi extension)
- `extension=json` (JSON support)

---

## Initial Setup & Testing

### 1. Access the System

**Frontend**: `http://localhost/PROJETPAW/INDEX.HTML`  
**API Base URL**: `http://localhost/PROJETPAW/api/`

### 2. Create Admin User

First, manually insert an admin user into the database:

```sql
INSERT INTO users (user_id, first_name, last_name, email, password, role) 
VALUES ('ADMIN001', 'Admin', 'User', 'admin@university.edu', 
        SHA2('admin123', 256), 'admin');
```

Or use a registration form if implemented.

### 3. Login

Use the credentials:
- **Email**: admin@university.edu
- **Password**: admin123

### 4. Test API Endpoints

Use Postman or cURL to test API endpoints:

```bash
# Test login
curl -X POST http://localhost/PROJETPAW/api/auth.php?action=login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@university.edu","password":"admin123"}'

# Get current user
curl -X GET http://localhost/PROJETPAW/api/auth.php?action=me
```

---

## File Structure

```
PROJETPAW/
├── INDEX.HTML                 # Main frontend (single page)
├── api/
│   ├── db.php                # Database configuration
│   ├── auth.php              # Authentication endpoints
│   ├── sessions.php          # Session management
│   ├── attendance.php        # Attendance marking
│   ├── justifications.php    # Justification handling
│   ├── participation.php     # Participation tracking
│   ├── behavior.php          # Behavior tracking
│   ├── students.php          # Student management
│   └── reports.php           # Reporting/Statistics
├── uploads/
│   └── justifications/       # Uploaded justification files
├── database.sql              # Database schema
├── API_DOCUMENTATION.md      # API documentation
└── README.md                 # This file
```

---

## Default User Roles

### 1. Student
- View personal attendance
- Submit absence justifications
- Upload supporting documents
- View course attendance history

### 2. Professor
- Create and manage sessions
- Mark student attendance
- Record participation levels
- Track behavior
- Review and approve justifications
- Generate attendance reports

### 3. Administrator
- Manage users (create/delete)
- Manage courses and groups
- Import/export student lists (Progres Excel format)
- View system statistics
- Review all justifications

---

## Key Features

### ✅ Implemented

1. **Authentication & Authorization**
   - Login/Logout
   - Role-based access control
   - Secure session management

2. **Attendance Management**
   - Create/open/close sessions
   - Mark attendance (present, absent, late, excused)
   - Bulk attendance marking
   - Attendance summaries

3. **Justification Workflow**
   - Student submission with file upload
   - File management (PDF, DOC, images)
   - Review system for professors
   - Status tracking (pending, approved, rejected)

4. **Participation & Behavior**
   - Record participation levels
   - Track student behavior
   - Generate summaries and statistics

5. **Reporting**
   - Attendance reports by group/student
   - Participation reports
   - System-wide statistics
   - Exportable data

6. **Student Management**
   - Add/remove students from groups
   - Import student lists (Excel compatible)
   - Export student lists (Progres Excel format)

---

## API Endpoints Summary

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/auth.php?action=login` | User login |
| POST | `/auth.php?action=logout` | User logout |
| POST | `/auth.php?action=register` | Register new user (Admin) |
| GET | `/auth.php?action=me` | Get current user |
| POST | `/sessions.php?action=create` | Create session |
| PUT | `/sessions.php?action=open` | Open session |
| PUT | `/sessions.php?action=close` | Close session |
| GET | `/sessions.php?action=list` | List sessions |
| POST | `/attendance.php?action=mark` | Mark attendance |
| POST | `/attendance.php?action=bulk` | Bulk mark |
| GET | `/attendance.php?action=list` | Get attendance |
| GET | `/attendance.php?action=summary` | Attendance summary |
| POST | `/justifications.php?action=submit` | Submit justification |
| GET | `/justifications.php?action=pending` | Get pending |
| PUT | `/justifications.php?action=review` | Review justification |
| GET | `/students.php?action=list` | List students |
| POST | `/students.php?action=add` | Add student |
| POST | `/students.php?action=import` | Import students |
| GET | `/students.php?action=export` | Export students |
| GET | `/reports.php?action=attendance` | Attendance report |
| GET | `/reports.php?action=participation` | Participation report |
| GET | `/reports.php?action=statistics` | System statistics |

See `API_DOCUMENTATION.md` for full endpoint documentation.

---

## Troubleshooting

### Database Connection Error
- Check database credentials in `api/db.php`
- Ensure MariaDB/MySQL is running
- Check database exists: `CREATE DATABASE attendance_system;`

### File Upload Error
- Ensure `uploads/justifications/` directory exists
- Check folder write permissions: `chmod 755 uploads/justifications`
- Verify max_upload_size in php.ini is at least 5MB

### Session Issues
- Clear browser cookies
- Ensure `session.save_path` is writable in php.ini
- Check PHP session timeout settings

### API 404 Errors
- Verify API files are in `/api/` directory
- Check file permissions
- Ensure `.php` extension is recognized

---

## Security Recommendations

1. **Change Default Credentials**
   - Change admin password after first login
   - Update database user password

2. **File Upload Security**
   - Current limit: 5MB per file
   - Allowed types: PDF, DOC, DOCX, JPG, JPEG, PNG
   - Files stored outside web root (recommended)

3. **Database Security**
   - Use strong database passwords
   - Restrict database user privileges
   - Enable SSL for database connections

4. **SSL/HTTPS**
   - Deploy on HTTPS in production
   - Update CORS headers if needed

5. **Input Validation**
   - All inputs validated server-side
   - Prepared statements prevent SQL injection
   - File type and size validation

---

## Performance Optimization

- Database indexes created on frequently queried fields
- Connection pooling recommended for production
- Implement caching for reports (Redis/Memcached)
- Optimize file storage for large deployments

---

## Backup & Maintenance

### Regular Backups
```bash
# Backup database
mysqldump -u root -p attendance_system > backup_$(date +%Y%m%d).sql

# Backup uploaded files
tar -czf uploads_$(date +%Y%m%d).tar.gz uploads/
```

### Database Maintenance
```sql
-- Optimize tables
OPTIMIZE TABLE users, courses, groups, sessions, attendance_records, justifications;

-- Check table integrity
CHECK TABLE attendance_records;
REPAIR TABLE attendance_records;
```

---

## Support & Documentation

- Full API documentation: `API_DOCUMENTATION.md`
- Database schema: `database.sql`
- Frontend code: `INDEX.HTML`
- Backend code: `api/*.php`

---

## Version Information

- **Frontend Framework**: Bootstrap 5.3.2, jQuery 3.7.1, Chart.js 4.4.0
- **Backend**: PHP 7.4+
- **Database**: MariaDB 10.3+ / MySQL 5.7+
- **Created**: November 2025

---

## License & Credits

Attendance Management System for Algiers University  
Project submission for evaluation

---

**Installation Complete!** 🎉

The system is ready to use. Start with creating admin users, courses, and groups to begin managing attendance.
