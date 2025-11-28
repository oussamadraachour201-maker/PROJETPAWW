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

define('UPLOAD_DIR', '../uploads/justifications/');

/**
 * Submit justification (Student only)
 * POST /api/justifications.php?action=submit (with file upload)
 */
function submitJustification($conn) {
    try {
        if ($_SESSION['role'] !== 'student') {
            http_response_code(403);
            die(json_encode(['error' => 'Only students can submit justifications']));
        }
        
        $session_id = isset($_POST['session_id']) ? $_POST['session_id'] : null;
        $justification_text = isset($_POST['justification_text']) ? $_POST['justification_text'] : '';
        
        if (!$session_id || empty($justification_text)) {
            http_response_code(400);
            die(json_encode(['error' => 'Missing session_id or justification_text']));
        }
        
        $file_path = null;
        
        // Handle file upload
        if (isset($_FILES['file'])) {
            $file = $_FILES['file'];
            $allowed_types = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];
            $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            
            if (!in_array($file_ext, $allowed_types)) {
                http_response_code(400);
                die(json_encode(['error' => 'Invalid file type']));
            }
            
            if ($file['size'] > 5 * 1024 * 1024) { // 5MB limit
                http_response_code(400);
                die(json_encode(['error' => 'File too large (max 5MB)']));
            }
            
            // Create upload directory if not exists
            if (!is_dir(UPLOAD_DIR)) {
                mkdir(UPLOAD_DIR, 0755, true);
            }
            
            $filename = 'justify_' . $_SESSION['user_id'] . '_' . time() . '.' . $file_ext;
            $file_path = UPLOAD_DIR . $filename;
            
            if (!move_uploaded_file($file['tmp_name'], $file_path)) {
                http_response_code(500);
                die(json_encode(['error' => 'Failed to upload file']));
            }
        }
        
        $query = "INSERT INTO justifications (student_id, session_id, justification_text, file_path, status) 
                  VALUES (?, ?, ?, ?, 'pending')";
        $stmt = $conn->prepare($query);
        
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $stmt->bind_param(
            'iiss',
            $_SESSION['user_id'],
            $session_id,
            $justification_text,
            $file_path
        );
        
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }
        
        $justification_id = $conn->insert_id;
        $stmt->close();
        
        http_response_code(201);
        echo json_encode([
            'success' => true,
            'message' => 'Justification submitted successfully',
            'justification_id' => $justification_id
        ]);
    } catch (Exception $e) {
        error_log("Submit Justification Error: " . $e->getMessage());
        http_response_code(500);
        die(json_encode(['error' => 'Failed to submit justification']));
    }
}

/**
 * Get student justifications
 * GET /api/justifications.php?action=student
 */
function getStudentJustifications($conn) {
    try {
        $query = "SELECT j.id, j.student_id, j.session_id, j.justification_text, j.file_path, 
                         j.status, j.submitted_at, j.reviewed_at, j.review_notes
                  FROM justifications j
                  WHERE j.student_id = ? ORDER BY j.submitted_at DESC";
        $stmt = $conn->prepare($query);
        
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $stmt->bind_param('i', $_SESSION['user_id']);
        $stmt->execute();
        
        $justifications = fetchAll($stmt);
        $stmt->close();
        
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'justifications' => $justifications
        ]);
    } catch (Exception $e) {
        error_log("Get Justifications Error: " . $e->getMessage());
        http_response_code(500);
        die(json_encode(['error' => 'Failed to fetch justifications']));
    }
}

/**
 * Get pending justifications (Professor/Admin)
 * GET /api/justifications.php?action=pending
 */
function getPendingJustifications($conn) {
    try {
        if ($_SESSION['role'] === 'student') {
            http_response_code(403);
            die(json_encode(['error' => 'Unauthorized']));
        }
        
        $query = "SELECT j.id, j.student_id, u.first_name, u.last_name, u.user_id, 
                         j.session_id, j.justification_text, j.file_path, j.status, j.submitted_at
                  FROM justifications j
                  JOIN users u ON j.student_id = u.id
                  WHERE j.status = 'pending' ORDER BY j.submitted_at ASC";
        $stmt = $conn->prepare($query);
        
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $stmt->execute();
        
        $justifications = fetchAll($stmt);
        $stmt->close();
        
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'justifications' => $justifications
        ]);
    } catch (Exception $e) {
        error_log("Get Pending Error: " . $e->getMessage());
        http_response_code(500);
        die(json_encode(['error' => 'Failed to fetch pending justifications']));
    }
}

/**
 * Review justification (Professor/Admin)
 * PUT /api/justifications.php?action=review
 */
function reviewJustification($conn) {
    try {
        if ($_SESSION['role'] === 'student') {
            http_response_code(403);
            die(json_encode(['error' => 'Unauthorized']));
        }
        
        $data = json_decode(file_get_contents("php://input"), true);
        
        $required = ['justification_id', 'status'];
        foreach ($required as $field) {
            if (!isset($data[$field])) {
                http_response_code(400);
                die(json_encode(['error' => "Missing field: $field"]));
            }
        }
        
        if (!in_array($data['status'], ['approved', 'rejected'])) {
            http_response_code(400);
            die(json_encode(['error' => 'Invalid status']));
        }
        
        $review_notes = isset($data['review_notes']) ? $data['review_notes'] : '';
        
        $query = "UPDATE justifications SET status = ?, reviewer_id = ?, review_notes = ?, reviewed_at = CURRENT_TIMESTAMP 
                  WHERE id = ?";
        $stmt = $conn->prepare($query);
        
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $stmt->bind_param(
            'sisi',
            $data['status'],
            $_SESSION['user_id'],
            $review_notes,
            $data['justification_id']
        );
        
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }
        
        if ($stmt->affected_rows === 0) {
            http_response_code(404);
            die(json_encode(['error' => 'Justification not found']));
        }
        
        $stmt->close();
        
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Justification reviewed successfully'
        ]);
    } catch (Exception $e) {
        error_log("Review Justification Error: " . $e->getMessage());
        http_response_code(500);
        die(json_encode(['error' => 'Failed to review justification']));
    }
}

/**
 * Download justification file
 * GET /api/justifications.php?action=download&file_id=1
 */
function downloadFile($conn) {
    try {
        if (!isset($_GET['file_id'])) {
            http_response_code(400);
            die(json_encode(['error' => 'Missing file_id']));
        }
        
        $query = "SELECT file_path, student_id FROM justifications WHERE id = ?";
        $stmt = $conn->prepare($query);
        
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $stmt->bind_param('i', $_GET['file_id']);
        $stmt->execute();
        
        $result = fetchOne($stmt);
        $stmt->close();
        
        if (!$result || !$result['file_path']) {
            http_response_code(404);
            die(json_encode(['error' => 'File not found']));
        }
        
        // Check authorization
        if ($_SESSION['role'] === 'student' && $result['student_id'] !== $_SESSION['user_id']) {
            http_response_code(403);
            die(json_encode(['error' => 'Unauthorized']));
        }
        
        $file_path = $result['file_path'];
        
        if (!file_exists($file_path)) {
            http_response_code(404);
            die(json_encode(['error' => 'File not found on server']));
        }
        
        // Download file
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($file_path) . '"');
        header('Content-Length: ' . filesize($file_path));
        readfile($file_path);
        exit;
    } catch (Exception $e) {
        error_log("Download File Error: " . $e->getMessage());
        http_response_code(500);
        die(json_encode(['error' => 'Failed to download file']));
    }
}

// Route requests
$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {
    case 'submit':
        submitJustification($conn);
        break;
    case 'student':
        getStudentJustifications($conn);
        break;
    case 'pending':
        getPendingJustifications($conn);
        break;
    case 'review':
        reviewJustification($conn);
        break;
    case 'download':
        downloadFile($conn);
        break;
    default:
        http_response_code(404);
        echo json_encode(['error' => 'Invalid action']);
}

$conn->close();
?>
