<?php
// CORS Headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once 'db.php';
session_start();

// Check if user is authenticated
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    die(json_encode(['error' => 'Not authenticated']));
}

/**
 * Get attendance report for a group
 * GET /api/reports.php?action=attendance&group_id=1
 */
function getAttendanceReport($conn) {
    try {
        if (!isset($_GET['group_id'])) {
            http_response_code(400);
            die(json_encode(['error' => 'Missing group_id']));
        }
        
        $group_id = $_GET['group_id'];
        
        // Verify user can access this group
        if ($_SESSION['role'] === 'professor') {
            $verify_query = "SELECT g.id FROM groups g JOIN courses c ON g.course_id = c.id WHERE g.id = ? AND c.professor_id = ?";
            $verify_stmt = $conn->prepare($verify_query);
            $verify_stmt->bind_param('ii', $group_id, $_SESSION['user_id']);
            $verify_stmt->execute();
            $result = fetchOne($verify_stmt);
            $verify_stmt->close();
            
            if (!$result) {
                http_response_code(403);
                die(json_encode(['error' => 'Unauthorized']));
            }
        }
        
        $query = "SELECT 
                    u.id, u.user_id, u.first_name, u.last_name,
                    COUNT(CASE WHEN ar.status = 'present' THEN 1 END) as present,
                    COUNT(CASE WHEN ar.status = 'absent' THEN 1 END) as absent,
                    COUNT(CASE WHEN ar.status = 'late' THEN 1 END) as late,
                    COUNT(CASE WHEN ar.status = 'excused' THEN 1 END) as excused,
                    COUNT(ar.id) as total_sessions,
                    ROUND((COUNT(CASE WHEN ar.status = 'present' THEN 1 END) / COUNT(ar.id) * 100), 2) as attendance_percentage
                  FROM users u
                  JOIN group_members gm ON u.id = gm.student_id
                  LEFT JOIN attendance_records ar ON u.id = ar.student_id 
                    AND ar.session_id IN (SELECT id FROM sessions WHERE group_id = ?)
                  WHERE gm.group_id = ? AND u.role = 'student'
                  GROUP BY u.id, u.user_id, u.first_name, u.last_name
                  ORDER BY u.last_name, u.first_name";
        $stmt = $conn->prepare($query);
        
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $stmt->bind_param('ii', $group_id, $group_id);
        $stmt->execute();
        
        $report = fetchAll($stmt);
        $stmt->close();
        
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'report' => $report
        ]);
    } catch (Exception $e) {
        error_log("Get Attendance Report Error: " . $e->getMessage());
        http_response_code(500);
        die(json_encode(['error' => 'Failed to generate attendance report']));
    }
}

/**
 * Get participation report for a group
 * GET /api/reports.php?action=participation&group_id=1
 */
function getParticipationReport($conn) {
    try {
        if (!isset($_GET['group_id'])) {
            http_response_code(400);
            die(json_encode(['error' => 'Missing group_id']));
        }
        
        $group_id = $_GET['group_id'];
        
        $query = "SELECT 
                    u.id, u.user_id, u.first_name, u.last_name,
                    COUNT(CASE WHEN p.participation_level = 'active' THEN 1 END) as active,
                    COUNT(CASE WHEN p.participation_level = 'moderate' THEN 1 END) as moderate,
                    COUNT(CASE WHEN p.participation_level = 'passive' THEN 1 END) as passive,
                    COUNT(CASE WHEN p.participation_level = 'none' THEN 1 END) as none,
                    COUNT(p.id) as total_sessions
                  FROM users u
                  JOIN group_members gm ON u.id = gm.student_id
                  LEFT JOIN participation p ON u.id = p.student_id 
                    AND p.session_id IN (SELECT id FROM sessions WHERE group_id = ?)
                  WHERE gm.group_id = ? AND u.role = 'student'
                  GROUP BY u.id
                  ORDER BY u.last_name, u.first_name";
        $stmt = $conn->prepare($query);
        
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $stmt->bind_param('ii', $group_id, $group_id);
        $stmt->execute();
        
        $report = fetchAll($stmt);
        $stmt->close();
        
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'report' => $report
        ]);
    } catch (Exception $e) {
        error_log("Get Participation Report Error: " . $e->getMessage());
        http_response_code(500);
        die(json_encode(['error' => 'Failed to generate participation report']));
    }
}

/**
 * Get overall statistics (Admin)
 * GET /api/reports.php?action=statistics
 */
function getStatistics($conn) {
    try {
        if ($_SESSION['role'] !== 'admin') {
            http_response_code(403);
            die(json_encode(['error' => 'Admin only']));
        }
        
        // Total students
        $student_query = "SELECT COUNT(*) as count FROM users WHERE role = 'student'";
        $student_result = $conn->query($student_query);
        $students = $student_result->fetch_assoc()['count'];
        
        // Total professors
        $prof_query = "SELECT COUNT(*) as count FROM users WHERE role = 'professor'";
        $prof_result = $conn->query($prof_query);
        $professors = $prof_result->fetch_assoc()['count'];
        
        // Total courses
        $course_query = "SELECT COUNT(*) as count FROM courses";
        $course_result = $conn->query($course_query);
        $courses = $course_result->fetch_assoc()['count'];
        
        // Total sessions
        $session_query = "SELECT COUNT(*) as count FROM sessions";
        $session_result = $conn->query($session_query);
        $sessions = $session_result->fetch_assoc()['count'];
        
        // Pending justifications
        $justify_query = "SELECT COUNT(*) as count FROM justifications WHERE status = 'pending'";
        $justify_result = $conn->query($justify_query);
        $pending_justifications = $justify_result->fetch_assoc()['count'];
        
        // Average attendance rate
        $avg_query = "SELECT ROUND(AVG(CASE WHEN status = 'present' THEN 1 ELSE 0 END) * 100, 2) as avg_rate FROM attendance_records";
        $avg_result = $conn->query($avg_query);
        $avg_attendance = $avg_result->fetch_assoc()['avg_rate'] ?? 0;
        
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'statistics' => [
                'total_students' => intval($students),
                'total_professors' => intval($professors),
                'total_courses' => intval($courses),
                'total_sessions' => intval($sessions),
                'pending_justifications' => intval($pending_justifications),
                'average_attendance_rate' => floatval($avg_attendance)
            ]
        ]);
    } catch (Exception $e) {
        error_log("Get Statistics Error: " . $e->getMessage());
        http_response_code(500);
        die(json_encode(['error' => 'Failed to generate statistics']));
    }
}

// Route requests
$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {
    case 'attendance':
        getAttendanceReport($conn);
        break;
    case 'participation':
        getParticipationReport($conn);
        break;
    case 'statistics':
        getStatistics($conn);
        break;
    default:
        http_response_code(404);
        echo json_encode(['error' => 'Invalid action']);
}

$conn->close();
?>
