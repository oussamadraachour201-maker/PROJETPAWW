# Attendance Management System - API Documentation

## Base URL
```
http://localhost/PROJETPAW/api/
```

## Authentication
All endpoints (except login/register) require active session via `auth.php?action=login`

### 1. AUTHENTICATION ENDPOINTS

#### Login
```
POST /auth.php?action=login
Content-Type: application/json

Request Body:
{
  "email": "user@university.edu",
  "password": "password123"
}

Response (200):
{
  "success": true,
  "user": {
    "id": 1,
    "user_id": "STU001",
    "first_name": "Ahmed",
    "last_name": "Hassan",
    "email": "ahmed@university.edu",
    "role": "student"
  }
}
```

#### Logout
```
POST /auth.php?action=logout

Response (200):
{
  "success": true,
  "message": "Logged out successfully"
}
```

#### Register User (Admin only)
```
POST /auth.php?action=register
Content-Type: application/json

Request Body:
{
  "user_id": "STU002",
  "first_name": "Amira",
  "last_name": "Yacine",
  "email": "amira@university.edu",
  "password": "securepass123",
  "role": "student"  // "student", "professor", "admin"
}

Response (201):
{
  "success": true,
  "message": "User registered successfully",
  "user_id": 2
}
```

#### Get Current User
```
GET /auth.php?action=me

Response (200):
{
  "success": true,
  "user": {
    "id": 1,
    "email": "user@university.edu",
    "role": "student",
    "first_name": "Ahmed",
    "last_name": "Hassan"
  }
}
```

---

### 2. ATTENDANCE SESSIONS ENDPOINTS

#### Create Session (Professor only)
```
POST /sessions.php?action=create
Content-Type: application/json

Request Body:
{
  "group_id": 5,
  "session_date": "2025-11-28",
  "session_time": "09:00:00",
  "notes": "Regular lecture session"
}

Response (201):
{
  "success": true,
  "message": "Session created successfully",
  "session_id": 12,
  "session_code": "SESSION-1732787890-4567"
}
```

#### Open Session (Professor only)
```
PUT /sessions.php?action=open
Content-Type: application/json

Request Body:
{
  "session_id": 12
}

Response (200):
{
  "success": true,
  "message": "Session opened successfully"
}
```

#### Close Session (Professor only)
```
PUT /sessions.php?action=close
Content-Type: application/json

Request Body:
{
  "session_id": 12
}

Response (200):
{
  "success": true,
  "message": "Session closed successfully"
}
```

#### List Sessions
```
GET /sessions.php?action=list&group_id=5

Response (200):
{
  "success": true,
  "sessions": [
    {
      "id": 12,
      "session_code": "SESSION-1732787890-4567",
      "group_id": 5,
      "session_date": "2025-11-28",
      "session_time": "09:00:00",
      "status": "open",
      "notes": "Regular lecture session",
      "created_at": "2025-11-27 10:00:00",
      "updated_at": "2025-11-27 10:15:00"
    }
  ]
}
```

#### Get Single Session
```
GET /sessions.php?action=get&session_id=12

Response (200):
{
  "success": true,
  "session": { ... }
}
```

---

### 3. ATTENDANCE MARKING ENDPOINTS

#### Mark Attendance (Professor only)
```
POST /attendance.php?action=mark
Content-Type: application/json

Request Body:
{
  "session_id": 12,
  "student_id": 3,
  "status": "present",  // "present", "absent", "late", "excused"
  "notes": "Student was 5 minutes late"
}

Response (200):
{
  "success": true,
  "message": "Attendance marked successfully"
}
```

#### Bulk Mark Attendance (Professor only)
```
POST /attendance.php?action=bulk
Content-Type: application/json

Request Body:
{
  "records": [
    {
      "session_id": 12,
      "student_id": 3,
      "status": "present"
    },
    {
      "session_id": 12,
      "student_id": 4,
      "status": "absent"
    }
  ]
}

Response (200):
{
  "success": true,
  "message": "Marked 2 records, 0 failed",
  "successful": 2,
  "failed": 0
}
```

#### Get Attendance for Session
```
GET /attendance.php?action=list&session_id=12

Response (200):
{
  "success": true,
  "attendance": [
    {
      "id": 1,
      "session_id": 12,
      "student_id": 3,
      "user_id": "STU001",
      "first_name": "Ahmed",
      "last_name": "Hassan",
      "status": "present",
      "marked_at": "2025-11-27 10:15:00",
      "notes": "Student was 5 minutes late"
    }
  ]
}
```

#### Get Student Attendance Summary
```
GET /attendance.php?action=summary&student_id=3&group_id=5

Response (200):
{
  "success": true,
  "summary": {
    "present": 15,
    "absent": 2,
    "late": 1,
    "excused": 1,
    "total_sessions": 19
  }
}
```

---

### 4. JUSTIFICATION ENDPOINTS

#### Submit Justification (Student)
```
POST /justifications.php?action=submit
Content-Type: multipart/form-data

Form Data:
  session_id: 12
  justification_text: "I was sick that day with doctor's note"
  file: [PDF/DOC file, optional]

Response (201):
{
  "success": true,
  "message": "Justification submitted successfully",
  "justification_id": 8
}
```

#### Get My Justifications (Student)
```
GET /justifications.php?action=student

Response (200):
{
  "success": true,
  "justifications": [
    {
      "id": 8,
      "student_id": 3,
      "session_id": 12,
      "justification_text": "I was sick that day",
      "file_path": "uploads/justifications/justify_3_1732787890.pdf",
      "status": "pending",
      "submitted_at": "2025-11-27 10:20:00",
      "reviewed_at": null,
      "review_notes": null
    }
  ]
}
```

#### Get Pending Justifications (Professor/Admin)
```
GET /justifications.php?action=pending

Response (200):
{
  "success": true,
  "justifications": [
    {
      "id": 8,
      "student_id": 3,
      "user_id": "STU001",
      "first_name": "Ahmed",
      "last_name": "Hassan",
      "session_id": 12,
      "justification_text": "I was sick that day",
      "file_path": "uploads/justifications/justify_3_1732787890.pdf",
      "status": "pending",
      "submitted_at": "2025-11-27 10:20:00"
    }
  ]
}
```

#### Review Justification (Professor/Admin)
```
PUT /justifications.php?action=review
Content-Type: application/json

Request Body:
{
  "justification_id": 8,
  "status": "approved",  // "approved" or "rejected"
  "review_notes": "Doctor's note verified"
}

Response (200):
{
  "success": true,
  "message": "Justification reviewed successfully"
}
```

#### Download Justification File
```
GET /justifications.php?action=download&file_id=8

Response: File download
```

---

### 5. PARTICIPATION ENDPOINTS

#### Record Participation (Professor only)
```
POST /participation.php?action=record
Content-Type: application/json

Request Body:
{
  "session_id": 12,
  "student_id": 3,
  "participation_level": "active",  // "active", "moderate", "passive", "none"
  "notes": "Answered questions correctly"
}

Response (200):
{
  "success": true,
  "message": "Participation recorded successfully"
}
```

#### Get Session Participation
```
GET /participation.php?action=list&session_id=12

Response (200):
{
  "success": true,
  "participation": [
    {
      "id": 1,
      "session_id": 12,
      "student_id": 3,
      "user_id": "STU001",
      "first_name": "Ahmed",
      "last_name": "Hassan",
      "participation_level": "active",
      "notes": "Answered questions correctly",
      "recorded_at": "2025-11-27 10:25:00"
    }
  ]
}
```

---

### 6. BEHAVIOR ENDPOINTS

#### Record Behavior (Professor only)
```
POST /behavior.php?action=record
Content-Type: application/json

Request Body:
{
  "session_id": 12,
  "student_id": 3,
  "behavior_type": "positive",  // "positive", "neutral", "negative"
  "description": "Helped other students understand the concept"
}

Response (201):
{
  "success": true,
  "message": "Behavior recorded successfully"
}
```

#### Get Behavior Records
```
GET /behavior.php?action=list&session_id=12

Response (200):
{
  "success": true,
  "behavior": [
    {
      "id": 1,
      "session_id": 12,
      "student_id": 3,
      "user_id": "STU001",
      "first_name": "Ahmed",
      "last_name": "Hassan",
      "behavior_type": "positive",
      "description": "Helped other students",
      "recorded_at": "2025-11-27 10:25:00"
    }
  ]
}
```

#### Get Behavior Summary
```
GET /behavior.php?action=summary&student_id=3&group_id=5

Response (200):
{
  "success": true,
  "summary": {
    "positive": 8,
    "neutral": 3,
    "negative": 0,
    "total_records": 11
  }
}
```

---

### 7. STUDENT MANAGEMENT ENDPOINTS

#### List Students in Group
```
GET /students.php?action=list&group_id=5

Response (200):
{
  "success": true,
  "students": [
    {
      "id": 3,
      "user_id": "STU001",
      "first_name": "Ahmed",
      "last_name": "Hassan",
      "email": "ahmed@university.edu",
      "enrollment_date": "2025-09-01 08:00:00"
    }
  ]
}
```

#### Add Student to Group (Admin only)
```
POST /students.php?action=add
Content-Type: application/json

Request Body:
{
  "group_id": 5,
  "student_id": 3
}

Response (201):
{
  "success": true,
  "message": "Student added to group"
}
```

#### Remove Student from Group (Admin only)
```
DELETE /students.php?action=remove
Content-Type: application/json

Request Body:
{
  "group_id": 5,
  "student_id": 3
}

Response (200):
{
  "success": true,
  "message": "Student removed from group"
}
```

#### Import Students (Admin only)
```
POST /students.php?action=import
Content-Type: application/json

Request Body:
{
  "group_id": 5,
  "students": [
    {"student_id": "STU001"},
    {"student_id": "STU002"}
  ]
}

Response (200):
{
  "success": true,
  "message": "Imported 2 students, 0 failed",
  "successful": 2,
  "failed": 0
}
```

#### Export Students (Admin only - CSV)
```
GET /students.php?action=export&group_id=5

Response: CSV file download (Progres Excel compatible)
Columns: ID | First Name | Last Name | Email | Enrollment Date
```

---

### 8. REPORTING ENDPOINTS

#### Attendance Report
```
GET /reports.php?action=attendance&group_id=5

Response (200):
{
  "success": true,
  "report": [
    {
      "id": 3,
      "user_id": "STU001",
      "first_name": "Ahmed",
      "last_name": "Hassan",
      "present": 15,
      "absent": 2,
      "late": 1,
      "excused": 1,
      "total_sessions": 19,
      "attendance_percentage": 78.95
    }
  ]
}
```

#### Participation Report
```
GET /reports.php?action=participation&group_id=5

Response (200):
{
  "success": true,
  "report": [
    {
      "id": 3,
      "user_id": "STU001",
      "first_name": "Ahmed",
      "last_name": "Hassan",
      "active": 10,
      "moderate": 5,
      "passive": 3,
      "none": 1,
      "total_sessions": 19
    }
  ]
}
```

#### System Statistics (Admin only)
```
GET /reports.php?action=statistics

Response (200):
{
  "success": true,
  "statistics": {
    "total_students": 250,
    "total_professors": 15,
    "total_courses": 8,
    "total_sessions": 156,
    "pending_justifications": 5,
    "average_attendance_rate": 82.5
  }
}
```

---

## Error Responses

```json
{
  "error": "Description of the error"
}
```

### HTTP Status Codes:
- `200`: Success
- `201`: Created
- `400`: Bad Request
- `401`: Unauthorized (Not authenticated)
- `403`: Forbidden (Insufficient permissions)
- `404`: Not Found
- `409`: Conflict (e.g., duplicate entry)
- `500`: Server Error

---

## Database Tables

1. **users** - User accounts (students, professors, admins)
2. **courses** - Course information
3. **groups** - Class groups
4. **group_members** - Student enrollment in groups
5. **sessions** - Attendance sessions
6. **attendance_records** - Attendance marks
7. **participation** - Participation tracking
8. **behavior** - Behavior records
9. **justifications** - Absence justifications with file storage

---

## Setup Instructions

1. Import `database.sql` into MariaDB/MySQL
2. Update database credentials in `api/db.php`
3. Ensure `uploads/justifications/` directory exists with write permissions
4. Access the API endpoints using authenticated sessions

---

## Security Notes

- All endpoints use prepared statements to prevent SQL injection
- Session-based authentication with role-based access control
- File uploads validated for type and size (max 5MB)
- Error logging to server error log
- CORS headers configured for API access
