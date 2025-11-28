# Attendance Management System - 3-Page Interface

## System Architecture

The application consists of **3 separate role-based pages** as required:

### 1. **LOGIN PAGE** (`login.html`)
- Landing page with role selection
- Three main options:
  - 👤 Login as Student
  - 👨‍🏫 Login as Professor  
  - ⚙️ Login as Administrator
- Demo mode for testing
- Responsive design with gradient background

### 2. **STUDENT PAGE** (`student.html`)
#### Features:
- **Dashboard Statistics**
  - Total Absences
  - Participations Count
  - Attendance Rate (%)
  - Current Status (Excellent/Good/Fair)

- **Attendance Record Table**
  - All 6 sessions displayed
  - Session dates
  - Attendance status (✓ Present / ✗ Absent)
  - Participation status

- **Justification System**
  - Submit absence justifications
  - Upload supporting documents
  - View submitted justifications with status
  - Track approval/rejection status

- **Attendance Chart**
  - Visual representation using Chart.js
  - Shows attendance and participation trends

- **Responsive Design**
  - Mobile-friendly layout
  - Dark theme with green accents for students

---

### 3. **PROFESSOR PAGE** (`professor.html`)
#### Features:
- **Class Statistics Dashboard**
  - Total students enrolled
  - Sessions created
  - Average class attendance (%)
  - Pending justifications count

- **Active Sessions Management**
  - View list of active/closed sessions
  - Create new sessions with auto-generated codes
  - Session status tracking

- **Mark Attendance**
  - Interactive table with all students
  - Checkboxes for attendance marking
  - Bulk actions (Mark All Present, Clear All)
  - Real-time absence/participation counters
  - Student search functionality

- **Pending Justifications Review**
  - View student justifications
  - Approve/Reject with comments
  - Track justification status

- **Analytics**
  - Attendance distribution chart (doughnut)
  - Student performance chart (bar)
  - Class-wide statistics

- **Color-Coded Rows**
  - 🟢 Green: Good attendance (≤1 absence)
  - 🟡 Yellow: Fair attendance (2-3 absences)
  - 🔴 Red: Poor attendance (≥4 absences)

---

### 4. **ADMIN PAGE** (`admin.html`)
#### Features:
- **System-wide Statistics**
  - Total users (students, professors, admins)
  - Active courses
  - Total groups/classes
  - Generated reports count

- **Tab-Based Management**
  
  **Users Tab:**
  - Add new users (students, professors, admins)
  - Edit/Delete users
  - View user details, roles, status
  - Creation dates

  **Courses Tab:**
  - Manage courses
  - Assign professors
  - Set credit hours and semesters
  - Edit/Delete courses

  **Groups Tab:**
  - Create new groups
  - Manage group enrollment
  - View capacity and enrollment status
  - Assign professors to groups

  **Reports Tab:**
  - System-wide attendance overview
  - User distribution pie chart
  - System statistics
  - Export capabilities

  **System Logs Tab:**
  - Track all user actions
  - View timestamps, user, action, details
  - Clear logs functionality
  - Monitor system activity

- **Additional Functions**
  - System settings configuration
  - Database backup functionality
  - User and role management
  - Course and class management
  - Report generation and export

---

## Navigation Flow

```
INDEX.HTML (Redirect)
    ↓
LOGIN.HTML (Role Selection)
    ├→ STUDENT.HTML (Student Dashboard)
    ├→ PROFESSOR.HTML (Professor Dashboard)
    └→ ADMIN.HTML (Administration Panel)
```

Each page has a **Logout** button that returns to the login page.

---

## Color Scheme by Role

- **Students**: Green & Blue accents (#0d9488, #4da6ff)
- **Professors**: Blue accents (#2563eb, #4da6ff)
- **Admins**: Red & Dark theme (#dc2626, #7f1d1d)

---

## Key Components Used

- **Bootstrap 5.3.2**: Responsive UI framework
- **jQuery 3.7.1**: DOM manipulation
- **Chart.js 4.4.0**: Data visualization
- **HTML5 Forms**: Data input validation
- **CSS3**: Modern styling with gradients and animations

---

## Database Connectivity

All pages are designed to connect to the PHP API backend:

```
API Endpoints:
  /api/auth.php          - Login, logout, authentication
  /api/sessions.php      - Session management
  /api/attendance.php    - Mark and retrieve attendance
  /api/justifications.php - Justification workflow
  /api/students.php      - Student management
  /api/reports.php       - Report generation
```

---

## File Structure

```
c:\PROJETPAW\
├── INDEX.HTML              (Redirect to login)
├── login.html              (Role selection)
├── student.html            (Student dashboard)
├── professor.html          (Professor dashboard)
├── admin.html              (Admin panel)
├── api/
│   ├── db.php             (Database connection)
│   ├── auth.php           (Authentication)
│   ├── sessions.php       (Session management)
│   ├── attendance.php     (Attendance marking)
│   ├── justifications.php (Justification workflow)
│   ├── participation.php  (Participation tracking)
│   ├── students.php       (Student enrollment)
│   └── reports.php        (Report generation)
├── database.sql           (Database schema)
├── API_DOCUMENTATION.md   (API reference)
├── SETUP_INSTRUCTIONS.md  (Setup guide)
└── uploads/
    └── justifications/    (File storage for justifications)
```

---

## Starting the System

1. **Open login.html in a web browser**
   - Can click "Login as Student", "Login as Professor", or "Login as Administrator"
   - Or use "View Demo" for sample data

2. **Each page loads with sample data**
   - Students see their attendance records
   - Professors can manage classes and mark attendance
   - Admins have full system control

3. **Use Logout to return to login page**
   - Each page has a logout button in the header

---

## Compliance with Requirements

✅ **3 Minimum Pages**
- Student Page: Full dashboard with attendance and justifications
- Professor Page: Class management and attendance marking
- Admin Page: System-wide management and reporting

✅ **Role-Based Access**
- Different UI and features for each role
- Separate dashboards and functionality

✅ **Responsive Design**
- Mobile-friendly layouts
- Dark theme for easy viewing

✅ **Data Visualization**
- Charts and statistics
- Color-coded attendance status

✅ **Backend Integration Ready**
- API endpoints defined
- Database schema created
- PHP files for backend

---

## Next Steps

1. Import `database.sql` into MySQL/MariaDB
2. Configure `api/db.php` with database credentials
3. Test API endpoints with postman or curl
4. Integrate jQuery fetch/AJAX calls to connect frontend to backend
5. Deploy to web server (Apache/Nginx with PHP)

---

**Attendance Management System**
Created for Algiers University
Secure • Fast • Reliable