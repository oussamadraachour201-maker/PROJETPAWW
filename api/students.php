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
 * Get students in a group (Professor/Admin)
 * GET /api/students.php?action=list&group_id=1
 */
function listStudents($conn) {
    try {
        if (!isset($_GET['group_id'])) {
            http_response_code(400);
            die(json_encode(['error' => 'Missing group_id']));
        }
        
        $query = "SELECT u.id, u.user_id, u.first_name, u.last_name, u.email, gm.enrollment_date
                  FROM users u
                  JOIN group_members gm ON u.id = gm.student_id
                  WHERE gm.group_id = ? AND u.role = 'student'
                  ORDER BY u.last_name, u.first_name";
        $stmt = $conn->prepare($query);
        
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $stmt->bind_param('i', $_GET['group_id']);
        $stmt->execute();
        
        $students = fetchAll($stmt);
        $stmt->close();
        
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'students' => $students
        ]);
    } catch (Exception $e) {
        error_log("List Students Error: " . $e->getMessage());
        http_response_code(500);
        die(json_encode(['error' => 'Failed to fetch students']));
    }
}

/**
 * Add student to group (Admin)
 * POST /api/students.php?action=add
 */
function addStudent($conn) {
    try {
        if ($_SESSION['role'] !== 'admin') {
            http_response_code(403);
            die(json_encode(['error' => 'Admin only']));
        }
        
        $data = json_decode(file_get_contents("php://input"), true);
        
        $required = ['group_id', 'student_id'];
        foreach ($required as $field) {
            if (!isset($data[$field])) {
                http_response_code(400);
                die(json_encode(['error' => "Missing field: $field"]));
            }
        }
        
        $query = "INSERT INTO group_members (group_id, student_id) VALUES (?, ?)";
        $stmt = $conn->prepare($query);
        
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $stmt->bind_param('ii', $data['group_id'], $data['student_id']);
        
        if (!$stmt->execute()) {
            if ($stmt->errno === 1062) { // Duplicate
                http_response_code(409);
                die(json_encode(['error' => 'Student already in group']));
            }
            throw new Exception("Execute failed: " . $stmt->error);
        }
        
        $stmt->close();
        
        http_response_code(201);
        echo json_encode([
            'success' => true,
            'message' => 'Student added to group'
        ]);
    } catch (Exception $e) {
        error_log("Add Student Error: " . $e->getMessage());
        http_response_code(500);
        die(json_encode(['error' => 'Failed to add student']));
    }
}

/**
 * Remove student from group (Admin)
 * DELETE /api/students.php?action=remove
 */
function removeStudent($conn) {
    try {
        if ($_SESSION['role'] !== 'admin') {
            http_response_code(403);
            die(json_encode(['error' => 'Admin only']));
        }
        
        $data = json_decode(file_get_contents("php://input"), true);
        
        if (!isset($data['group_id']) || !isset($data['student_id'])) {
            http_response_code(400);
            die(json_encode(['error' => 'Missing group_id or student_id']));
        }
        
        $query = "DELETE FROM group_members WHERE group_id = ? AND student_id = ?";
        $stmt = $conn->prepare($query);
        
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $stmt->bind_param('ii', $data['group_id'], $data['student_id']);
        
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }
        
        if ($stmt->affected_rows === 0) {
            http_response_code(404);
            die(json_encode(['error' => 'Enrollment not found']));
        }
        
        $stmt->close();
        
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Student removed from group'
        ]);
    } catch (Exception $e) {
        error_log("Remove Student Error: " . $e->getMessage());
        http_response_code(500);
        die(json_encode(['error' => 'Failed to remove student']));
    }
}

/**
 * Import student list from Excel (Admin)
 * POST /api/students.php?action=import
 */
function importStudents($conn) {
    try {
        if ($_SESSION['role'] !== 'admin') {
            http_response_code(403);
            die(json_encode(['error' => 'Admin only']));
        }
        
        $data = json_decode(file_get_contents("php://input"), true);
        
        if (!isset($data['group_id']) || !isset($data['students'])) {
            http_response_code(400);
            die(json_encode(['error' => 'Missing group_id or students array']));
        }
        
        if (!is_array($data['students'])) {
            http_response_code(400);
            die(json_encode(['error' => 'Students must be an array']));
        }
        
        $successful = 0;
        $failed = 0;
        
        foreach ($data['students'] as $student) {
            if (!isset($student['student_id'])) {
                $failed++;
                continue;
            }
            
            // Find or create user
            $user_query = "SELECT id FROM users WHERE user_id = ? AND role = 'student'";
            $user_stmt = $conn->prepare($user_query);
            $user_stmt->bind_param('s', $student['student_id']);
            $user_stmt->execute();
            $user_result = fetchOne($user_stmt);
            $user_stmt->close();
            
            if (!$user_result) {
                $failed++;
                continue;
            }
            
            $student_db_id = $user_result['id'];
            
            // Add to group
            $insert_query = "INSERT INTO group_members (group_id, student_id) VALUES (?, ?) 
                           ON DUPLICATE KEY UPDATE enrollment_date = enrollment_date";
            $insert_stmt = $conn->prepare($insert_query);
            
            if ($insert_stmt) {
                $insert_stmt->bind_param('ii', $data['group_id'], $student_db_id);
                if ($insert_stmt->execute()) {
                    $successful++;
                } else {
                    $failed++;
                }
                $insert_stmt->close();
            } else {
                $failed++;
            }
        }
        
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => "Imported $successful students, $failed failed",
            'successful' => $successful,
            'failed' => $failed
        ]);
    } catch (Exception $e) {
        error_log("Import Students Error: " . $e->getMessage());
        http_response_code(500);
        die(json_encode(['error' => 'Failed to import students']));
    }
}

/**
 * Export student list (Admin)
 * GET /api/students.php?action=export&group_id=1
 */
function exportStudents($conn) {
    try {
        if ($_SESSION['role'] !== 'admin') {
            http_response_code(403);
            die(json_encode(['error' => 'Admin only']));
        }
        
        if (!isset($_GET['group_id'])) {
            http_response_code(400);
            die(json_encode(['error' => 'Missing group_id']));
        }
        
        $query = "SELECT u.user_id, u.first_name, u.last_name, u.email, gm.enrollment_date
                  FROM users u
                  JOIN group_members gm ON u.id = gm.student_id
                  WHERE gm.group_id = ? AND u.role = 'student'
                  ORDER BY u.last_name, u.first_name";
        $stmt = $conn->prepare($query);
        
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $stmt->bind_param('i', $_GET['group_id']);
        $stmt->execute();
        
        $students = fetchAll($stmt);
        $stmt->close();
        
        // Export as CSV (compatible with Progres Excel format)
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="students_' . $_GET['group_id'] . '_' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        
        // CSV Header (Progres Excel compatible)
        fputcsv($output, ['ID', 'First Name', 'Last Name', 'Email', 'Enrollment Date'], ';');
        
        // Data rows
        foreach ($students as $student) {
            fputcsv($output, [
                $student['user_id'],
                $student['first_name'],
                $student['last_name'],
                $student['email'],
                $student['enrollment_date']
            ], ';');
        }
        
        fclose($output);
        exit;
    } catch (Exception $e) {
        error_log("Export Students Error: " . $e->getMessage());
        http_response_code(500);
        die(json_encode(['error' => 'Failed to export students']));
    }
}

// Route requests
$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {
    case 'list':
        listStudents($conn);
        break;
    case 'add':
        addStudent($conn);
        break;
    case 'remove':
        removeStudent($conn);
        break;
    case 'import':
        importStudents($conn);
        break;
    case 'export':
        exportStudents($conn);
        break;
    default:
        http_response_code(404);
        echo json_encode(['error' => 'Invalid action']);
}

$conn->close();
?>
