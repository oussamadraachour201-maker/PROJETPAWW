Simplified ER Diagram (text form)

Entities:
- users(id, username, password_hash, role, fullname, email)
- students(id, user_id -> users.id, matricule, group_name)
- courses(id, code, title)
- enrollments(id, student_id -> students.id, course_id -> courses.id)
- sessions(id, course_id -> courses.id, date, opened_by -> users.id, status)
- attendance(id, session_id -> sessions.id, student_id -> students.id, status, remark)
- justifications(id, student_id -> students.id, attendance_id -> attendance.id, reason, attachment, status)

Notes:
- `users.role` is an enum: admin, professor, student. No separate `roles` table to keep things simple.
- `students.user_id` is optional: in quick setups you can create student accounts in `users` and link them; otherwise students can be managed in `students` directly.
- Attendance records link a student to a session and store status; justifications optionally link to an attendance record.

Recommended minimal indexes:
- users.username UNIQUE
- students.matricule UNIQUE
- attendance(session_id), attendance(student_id)
- sessions(course_id, date)

Use `db_schema_simple.sql` to create these tables quickly for a lightweight deployment.
