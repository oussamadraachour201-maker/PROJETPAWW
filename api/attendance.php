<?php
// CORS Headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, PUT, DELETE, OPTIONS');
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
 * Mark attendance (Professor only)
 * POST /api/attendance.php?action=mark
 */
function markAttendance($conn) {
    try {
        if ($_SESSION['role'] !== 'professor') {
            http_response_code(403);
            die(json_encode(['error' => 'Only professors can mark attendance']));
        }
        
        $data = json_decode(file_get_contents("php://input"), true);
        
        $required = ['session_id', 'student_id', 'status'];
        foreach ($required as $field) {
            if (!isset($data[$field])) {
                http_response_code(400);
                die(json_encode(['error' => "Missing field: $field"]));
            }
        }
        
        if (!in_array($data['status'], ['present', 'absent', 'late', 'excused'])) {
            http_response_code(400);
            die(json_encode(['error' => 'Invalid status']));
        }
        
        // Check if session exists and professor owns it
        $verify_query = "SELECT s.id FROM sessions s JOIN groups g ON s.group_id = g.id 
                         JOIN courses c ON g.course_id = c.id WHERE s.id = ? AND c.professor_id = ?";
        $verify_stmt = $conn->prepare($verify_query);
        $verify_stmt->bind_param('ii', $data['session_id'], $_SESSION['user_id']);
        $verify_stmt->execute();
        $result = fetchOne($verify_stmt);
        $verify_stmt->close();
        
        if (!$result) {
            http_response_code(403);
            die(json_encode(['error' => 'Unauthorized']));
        }
        
        $notes = isset($data['notes']) ? $data['notes'] : '';
        
        $query = "INSERT INTO attendance_records (session_id, student_id, status, marked_by, notes) 
                  VALUES (?, ?, ?, ?, ?) 
                  ON DUPLICATE KEY UPDATE status = VALUES(status), marked_by = VALUES(marked_by), notes = VALUES(notes), marked_at = CURRENT_TIMESTAMP";
        $stmt = $conn->prepare($query);
        
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $stmt->bind_param(
            'iisss',
            $data['session_id'],
            $data['student_id'],
            $data['status'],
            $_SESSION['user_id'],
            $notes
        );
        
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }
        
        $stmt->close();
        
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Attendance marked successfully'
        ]);
    } catch (Exception $e) {
        error_log("Mark Attendance Error: " . $e->getMessage());
        http_response_code(500);
        die(json_encode(['error' => 'Failed to mark attendance']));
    }
}

/**
 * Get attendance records for a session
 * GET /api/attendance.php?action=list&session_id=1
 */
function listAttendance($conn) {
    try {
        if (!isset($_GET['session_id'])) {
            http_response_code(400);
            die(json_encode(['error' => 'Missing session_id']));
        }
        
        $query = "SELECT ar.id, ar.session_id, ar.student_id, u.user_id, u.first_name, u.last_name, 
                         ar.status, ar.marked_at, ar.notes
                  FROM attendance_records ar 
                  JOIN users u ON ar.student_id = u.id 
                  WHERE ar.session_id = ? ORDER BY u.last_name, u.first_name";
        $stmt = $conn->prepare($query);
        
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $stmt->bind_param('i', $_GET['session_id']);
        $stmt->execute();
        
        $records = fetchAll($stmt);
        $stmt->close();
        
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'attendance' => $records
        ]);
    } catch (Exception $e) {
        error_log("List Attendance Error: " . $e->getMessage());
        http_response_code(500);
        die(json_encode(['error' => 'Failed to fetch attendance']));
    }
}

/**
 * Get student attendance summary
 * GET /api/attendance.php?action=summary&student_id=1&group_id=1
 */
function getAttendanceSummary($conn) {
    try {
        if (!isset($_GET['student_id']) || !isset($_GET['group_id'])) {
            http_response_code(400);
            die(json_encode(['error' => 'Missing student_id or group_id']));
        }
        
        $student_id = $_GET['student_id'];
        $group_id = $_GET['group_id'];
        
        // Count different statuses
        $query = "SELECT 
                    COUNT(CASE WHEN ar.status = 'present' THEN 1 END) as present,
                    COUNT(CASE WHEN ar.status = 'absent' THEN 1 END) as absent,
                    COUNT(CASE WHEN ar.status = 'late' THEN 1 END) as late,
                    COUNT(CASE WHEN ar.status = 'excused' THEN 1 END) as excused,
                    COUNT(ar.id) as total_sessions
                  FROM attendance_records ar
                  JOIN sessions s ON ar.session_id = s.id
                  WHERE ar.student_id = ? AND s.group_id = ?";
        $stmt = $conn->prepare($query);
        
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $stmt->bind_param('ii', $student_id, $group_id);
        $stmt->execute();
        
        $summary = fetchOne($stmt);
        $stmt->close();
        
        if (!$summary) {
            $summary = [
                'present' => 0,
                'absent' => 0,
                'late' => 0,
                'excused' => 0,
                'total_sessions' => 0
            ];
        }
        
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'summary' => $summary
        ]);
    } catch (Exception $e) {
        error_log("Get Summary Error: " . $e->getMessage());
        http_response_code(500);
        die(json_encode(['error' => 'Failed to fetch summary']));
    }
}

/**
 * Bulk mark attendance
 * POST /api/attendance.php?action=bulk
 */
function bulkMarkAttendance($conn) {
    try {
        if ($_SESSION['role'] !== 'professor') {
            http_response_code(403);
            die(json_encode(['error' => 'Only professors can mark attendance']));
        }
        
        $data = json_decode(file_get_contents("php://input"), true);
        
        if (!isset($data['records']) || !is_array($data['records'])) {
            http_response_code(400);
            die(json_encode(['error' => 'Records array required']));
        }
        
        $successful = 0;
        $failed = 0;
        
        foreach ($data['records'] as $record) {
            if (!isset($record['session_id']) || !isset($record['student_id']) || !isset($record['status'])) {
                $failed++;
                continue;
            }
            
            $notes = isset($record['notes']) ? $record['notes'] : '';
            
            $query = "INSERT INTO attendance_records (session_id, student_id, status, marked_by, notes) 
                      VALUES (?, ?, ?, ?, ?) 
                      ON DUPLICATE KEY UPDATE status = VALUES(status), marked_by = VALUES(marked_by), notes = VALUES(notes)";
            $stmt = $conn->prepare($query);
            
            if ($stmt) {
                $stmt->bind_param(
                    'iisss',
                    $record['session_id'],
                    $record['student_id'],
                    $record['status'],
                    $_SESSION['user_id'],
                    $notes
                );
                
                if ($stmt->execute()) {
                    $successful++;
                } else {
                    $failed++;
                }
                $stmt->close();
            } else {
                $failed++;
            }
        }
        
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => "Marked $successful records, $failed failed",
            'successful' => $successful,
            'failed' => $failed
        ]);
    } catch (Exception $e) {
        error_log("Bulk Mark Attendance Error: " . $e->getMessage());
        http_response_code(500);
        die(json_encode(['error' => 'Failed to mark attendance']));
    }
}

// Route requests
$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {
    case 'mark':
        markAttendance($conn);
        break;
    case 'list':
        listAttendance($conn);
        break;
    case 'summary':
        getAttendanceSummary($conn);
        break;
    case 'bulk':
        bulkMarkAttendance($conn);
        break;
    default:
        http_response_code(404);
        echo json_encode(['error' => 'Invalid action']);
}

$conn->close();
?>
