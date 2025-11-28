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
 * Record participation (Professor only)
 * POST /api/participation.php?action=record
 */
function recordParticipation($conn) {
    try {
        if ($_SESSION['role'] !== 'professor') {
            http_response_code(403);
            die(json_encode(['error' => 'Only professors can record participation']));
        }
        
        $data = json_decode(file_get_contents("php://input"), true);
        
        $required = ['session_id', 'student_id', 'participation_level'];
        foreach ($required as $field) {
            if (!isset($data[$field])) {
                http_response_code(400);
                die(json_encode(['error' => "Missing field: $field"]));
            }
        }
        
        if (!in_array($data['participation_level'], ['active', 'moderate', 'passive', 'none'])) {
            http_response_code(400);
            die(json_encode(['error' => 'Invalid participation level']));
        }
        
        $notes = isset($data['notes']) ? $data['notes'] : '';
        
        $query = "INSERT INTO participation (session_id, student_id, participation_level, recorded_by, notes) 
                  VALUES (?, ?, ?, ?, ?) 
                  ON DUPLICATE KEY UPDATE participation_level = VALUES(participation_level), notes = VALUES(notes)";
        $stmt = $conn->prepare($query);
        
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $stmt->bind_param(
            'iisss',
            $data['session_id'],
            $data['student_id'],
            $data['participation_level'],
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
            'message' => 'Participation recorded successfully'
        ]);
    } catch (Exception $e) {
        error_log("Record Participation Error: " . $e->getMessage());
        http_response_code(500);
        die(json_encode(['error' => 'Failed to record participation']));
    }
}

/**
 * Get participation for session
 * GET /api/participation.php?action=list&session_id=1
 */
function listParticipation($conn) {
    try {
        if (!isset($_GET['session_id'])) {
            http_response_code(400);
            die(json_encode(['error' => 'Missing session_id']));
        }
        
        $query = "SELECT p.id, p.session_id, p.student_id, u.user_id, u.first_name, u.last_name, 
                         p.participation_level, p.notes, p.recorded_at
                  FROM participation p
                  JOIN users u ON p.student_id = u.id
                  WHERE p.session_id = ? ORDER BY u.last_name, u.first_name";
        $stmt = $conn->prepare($query);
        
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $stmt->bind_param('i', $_GET['session_id']);
        $stmt->execute();
        
        $participation = fetchAll($stmt);
        $stmt->close();
        
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'participation' => $participation
        ]);
    } catch (Exception $e) {
        error_log("List Participation Error: " . $e->getMessage());
        http_response_code(500);
        die(json_encode(['error' => 'Failed to fetch participation']));
    }
}

/**
 * Record behavior (Professor only)
 * POST /api/behavior.php?action=record
 */
function recordBehavior($conn) {
    try {
        if ($_SESSION['role'] !== 'professor') {
            http_response_code(403);
            die(json_encode(['error' => 'Only professors can record behavior']));
        }
        
        $data = json_decode(file_get_contents("php://input"), true);
        
        $required = ['session_id', 'student_id', 'behavior_type'];
        foreach ($required as $field) {
            if (!isset($data[$field])) {
                http_response_code(400);
                die(json_encode(['error' => "Missing field: $field"]));
            }
        }
        
        if (!in_array($data['behavior_type'], ['positive', 'neutral', 'negative'])) {
            http_response_code(400);
            die(json_encode(['error' => 'Invalid behavior type']));
        }
        
        $description = isset($data['description']) ? $data['description'] : '';
        
        $query = "INSERT INTO behavior (session_id, student_id, behavior_type, description, recorded_by) 
                  VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $stmt->bind_param(
            'iissi',
            $data['session_id'],
            $data['student_id'],
            $data['behavior_type'],
            $description,
            $_SESSION['user_id']
        );
        
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }
        
        $stmt->close();
        
        http_response_code(201);
        echo json_encode([
            'success' => true,
            'message' => 'Behavior recorded successfully'
        ]);
    } catch (Exception $e) {
        error_log("Record Behavior Error: " . $e->getMessage());
        http_response_code(500);
        die(json_encode(['error' => 'Failed to record behavior']));
    }
}

/**
 * Get behavior records for session
 * GET /api/behavior.php?action=list&session_id=1
 */
function listBehavior($conn) {
    try {
        if (!isset($_GET['session_id'])) {
            http_response_code(400);
            die(json_encode(['error' => 'Missing session_id']));
        }
        
        $query = "SELECT b.id, b.session_id, b.student_id, u.user_id, u.first_name, u.last_name, 
                         b.behavior_type, b.description, b.recorded_at
                  FROM behavior b
                  JOIN users u ON b.student_id = u.id
                  WHERE b.session_id = ? ORDER BY u.last_name, u.first_name";
        $stmt = $conn->prepare($query);
        
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $stmt->bind_param('i', $_GET['session_id']);
        $stmt->execute();
        
        $behavior = fetchAll($stmt);
        $stmt->close();
        
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'behavior' => $behavior
        ]);
    } catch (Exception $e) {
        error_log("List Behavior Error: " . $e->getMessage());
        http_response_code(500);
        die(json_encode(['error' => 'Failed to fetch behavior records']));
    }
}

/**
 * Get student behavior summary
 * GET /api/behavior.php?action=summary&student_id=1&group_id=1
 */
function getBehaviorSummary($conn) {
    try {
        if (!isset($_GET['student_id']) || !isset($_GET['group_id'])) {
            http_response_code(400);
            die(json_encode(['error' => 'Missing student_id or group_id']));
        }
        
        $student_id = $_GET['student_id'];
        $group_id = $_GET['group_id'];
        
        $query = "SELECT 
                    COUNT(CASE WHEN b.behavior_type = 'positive' THEN 1 END) as positive,
                    COUNT(CASE WHEN b.behavior_type = 'neutral' THEN 1 END) as neutral,
                    COUNT(CASE WHEN b.behavior_type = 'negative' THEN 1 END) as negative,
                    COUNT(b.id) as total_records
                  FROM behavior b
                  JOIN sessions s ON b.session_id = s.id
                  WHERE b.student_id = ? AND s.group_id = ?";
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
                'positive' => 0,
                'neutral' => 0,
                'negative' => 0,
                'total_records' => 0
            ];
        }
        
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'summary' => $summary
        ]);
    } catch (Exception $e) {
        error_log("Get Behavior Summary Error: " . $e->getMessage());
        http_response_code(500);
        die(json_encode(['error' => 'Failed to fetch behavior summary']));
    }
}

// Route requests based on file name
$script_name = basename($_SERVER['SCRIPT_NAME']);
$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($script_name === 'participation.php') {
    switch ($action) {
        case 'record':
            recordParticipation($conn);
            break;
        case 'list':
            listParticipation($conn);
            break;
        default:
            http_response_code(404);
            echo json_encode(['error' => 'Invalid action']);
    }
} elseif ($script_name === 'behavior.php') {
    switch ($action) {
        case 'record':
            recordBehavior($conn);
            break;
        case 'list':
            listBehavior($conn);
            break;
        case 'summary':
            getBehaviorSummary($conn);
            break;
        default:
            http_response_code(404);
            echo json_encode(['error' => 'Invalid action']);
    }
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Invalid endpoint']);
}

$conn->close();
?>
