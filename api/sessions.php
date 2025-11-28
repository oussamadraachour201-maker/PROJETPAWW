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
 * Create a new session (Professor only)
 * POST /api/sessions.php?action=create
 */
function createSession($conn) {
    try {
        if ($_SESSION['role'] !== 'professor') {
            http_response_code(403);
            die(json_encode(['error' => 'Only professors can create sessions']));
        }
        
        $data = json_decode(file_get_contents("php://input"), true);
        
        $required = ['group_id', 'session_date'];
        foreach ($required as $field) {
            if (!isset($data[$field])) {
                http_response_code(400);
                die(json_encode(['error' => "Missing field: $field"]));
            }
        }
        
        // Generate session code
        $session_code = 'SESSION-' . time() . '-' . mt_rand(1000, 9999);
        
        $query = "INSERT INTO sessions (session_code, group_id, session_date, session_time, status, created_by, notes) 
                  VALUES (?, ?, ?, ?, 'pending', ?, ?)";
        $stmt = $conn->prepare($query);
        
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $session_time = isset($data['session_time']) ? $data['session_time'] : null;
        $notes = isset($data['notes']) ? $data['notes'] : '';
        
        $stmt->bind_param(
            'sdsss',
            $session_code,
            $data['group_id'],
            $data['session_date'],
            $session_time,
            $_SESSION['user_id'],
            $notes
        );
        
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }
        
        $session_id = $conn->insert_id;
        $stmt->close();
        
        http_response_code(201);
        echo json_encode([
            'success' => true,
            'message' => 'Session created successfully',
            'session_id' => $session_id,
            'session_code' => $session_code
        ]);
    } catch (Exception $e) {
        error_log("Create Session Error: " . $e->getMessage());
        http_response_code(500);
        die(json_encode(['error' => 'Failed to create session']));
    }
}

/**
 * Open a session (Professor only)
 * PUT /api/sessions.php?action=open
 */
function openSession($conn) {
    try {
        if ($_SESSION['role'] !== 'professor') {
            http_response_code(403);
            die(json_encode(['error' => 'Only professors can open sessions']));
        }
        
        $data = json_decode(file_get_contents("php://input"), true);
        
        if (!isset($data['session_id'])) {
            http_response_code(400);
            die(json_encode(['error' => 'Missing session_id']));
        }
        
        $query = "UPDATE sessions SET status = 'open', updated_at = CURRENT_TIMESTAMP WHERE id = ? AND created_by = ?";
        $stmt = $conn->prepare($query);
        
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $stmt->bind_param('ii', $data['session_id'], $_SESSION['user_id']);
        
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }
        
        if ($stmt->affected_rows === 0) {
            http_response_code(404);
            die(json_encode(['error' => 'Session not found or unauthorized']));
        }
        
        $stmt->close();
        
        http_response_code(200);
        echo json_encode(['success' => true, 'message' => 'Session opened successfully']);
    } catch (Exception $e) {
        error_log("Open Session Error: " . $e->getMessage());
        http_response_code(500);
        die(json_encode(['error' => 'Failed to open session']));
    }
}

/**
 * Close a session (Professor only)
 * PUT /api/sessions.php?action=close
 */
function closeSession($conn) {
    try {
        if ($_SESSION['role'] !== 'professor') {
            http_response_code(403);
            die(json_encode(['error' => 'Only professors can close sessions']));
        }
        
        $data = json_decode(file_get_contents("php://input"), true);
        
        if (!isset($data['session_id'])) {
            http_response_code(400);
            die(json_encode(['error' => 'Missing session_id']));
        }
        
        $query = "UPDATE sessions SET status = 'closed', updated_at = CURRENT_TIMESTAMP WHERE id = ? AND created_by = ?";
        $stmt = $conn->prepare($query);
        
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $stmt->bind_param('ii', $data['session_id'], $_SESSION['user_id']);
        
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }
        
        if ($stmt->affected_rows === 0) {
            http_response_code(404);
            die(json_encode(['error' => 'Session not found or unauthorized']));
        }
        
        $stmt->close();
        
        http_response_code(200);
        echo json_encode(['success' => true, 'message' => 'Session closed successfully']);
    } catch (Exception $e) {
        error_log("Close Session Error: " . $e->getMessage());
        http_response_code(500);
        die(json_encode(['error' => 'Failed to close session']));
    }
}

/**
 * Get sessions for a group (Professor or Admin)
 * GET /api/sessions.php?action=list&group_id=1
 */
function listSessions($conn) {
    try {
        if (!isset($_GET['group_id'])) {
            http_response_code(400);
            die(json_encode(['error' => 'Missing group_id']));
        }
        
        // Verify professor owns the group
        if ($_SESSION['role'] === 'professor') {
            $verify_query = "SELECT g.id FROM groups g JOIN courses c ON g.course_id = c.id WHERE g.id = ? AND c.professor_id = ?";
            $verify_stmt = $conn->prepare($verify_query);
            $verify_stmt->bind_param('ii', $_GET['group_id'], $_SESSION['user_id']);
            $verify_stmt->execute();
            $result = fetchOne($verify_stmt);
            $verify_stmt->close();
            
            if (!$result) {
                http_response_code(403);
                die(json_encode(['error' => 'Unauthorized']));
            }
        }
        
        $query = "SELECT id, session_code, group_id, session_date, session_time, status, notes, created_at, updated_at 
                  FROM sessions WHERE group_id = ? ORDER BY session_date DESC";
        $stmt = $conn->prepare($query);
        
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $stmt->bind_param('i', $_GET['group_id']);
        $stmt->execute();
        
        $sessions = fetchAll($stmt);
        $stmt->close();
        
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'sessions' => $sessions
        ]);
    } catch (Exception $e) {
        error_log("List Sessions Error: " . $e->getMessage());
        http_response_code(500);
        die(json_encode(['error' => 'Failed to fetch sessions']));
    }
}

/**
 * Get a single session details
 * GET /api/sessions.php?action=get&session_id=1
 */
function getSession($conn) {
    try {
        if (!isset($_GET['session_id'])) {
            http_response_code(400);
            die(json_encode(['error' => 'Missing session_id']));
        }
        
        $query = "SELECT id, session_code, group_id, session_date, session_time, status, notes, created_at, updated_at 
                  FROM sessions WHERE id = ?";
        $stmt = $conn->prepare($query);
        
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $stmt->bind_param('i', $_GET['session_id']);
        $stmt->execute();
        
        $session = fetchOne($stmt);
        $stmt->close();
        
        if (!$session) {
            http_response_code(404);
            die(json_encode(['error' => 'Session not found']));
        }
        
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'session' => $session
        ]);
    } catch (Exception $e) {
        error_log("Get Session Error: " . $e->getMessage());
        http_response_code(500);
        die(json_encode(['error' => 'Failed to fetch session']));
    }
}

// Route requests
$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {
    case 'create':
        createSession($conn);
        break;
    case 'open':
        openSession($conn);
        break;
    case 'close':
        closeSession($conn);
        break;
    case 'list':
        listSessions($conn);
        break;
    case 'get':
        getSession($conn);
        break;
    default:
        http_response_code(404);
        echo json_encode(['error' => 'Invalid action']);
}

$conn->close();
?>
