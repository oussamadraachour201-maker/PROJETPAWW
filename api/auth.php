<?php
// CORS Headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once 'db.php';
session_start();

/**
 * Login endpoint
 * POST /api/auth.php?action=login
 */
function login($conn) {
    try {
        $data = json_decode(file_get_contents("php://input"), true);
        
        if (!isset($data['email']) || !isset($data['password'])) {
            http_response_code(400);
            die(json_encode(['error' => 'Email and password required']));
        }
        
        $email = $data['email'];
        $password = $data['password'];
        
        $query = "SELECT id, user_id, first_name, last_name, email, role FROM users WHERE email = ?";
        $stmt = $conn->prepare($query);
        
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $stmt->bind_param('s', $email);
        $stmt->execute();
        
        $user = fetchOne($stmt);
        $stmt->close();
        
        if (!$user) {
            http_response_code(401);
            die(json_encode(['error' => 'Invalid credentials']));
        }
        
        // Verify password (assuming it's hashed)
        // In production, use password_hash() and password_verify()
        $stored_password_query = "SELECT password FROM users WHERE id = ?";
        $stored_stmt = $conn->prepare($stored_password_query);
        $stored_stmt->bind_param('i', $user['id']);
        $stored_stmt->execute();
        $result = fetchOne($stored_stmt);
        $stored_stmt->close();
        
        if (!$result || !password_verify($password, $result['password'])) {
            http_response_code(401);
            die(json_encode(['error' => 'Invalid credentials']));
        }
        
        // Set session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['first_name'] = $user['first_name'];
        $_SESSION['last_name'] = $user['last_name'];
        
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'user' => [
                'id' => $user['id'],
                'user_id' => $user['user_id'],
                'first_name' => $user['first_name'],
                'last_name' => $user['last_name'],
                'email' => $user['email'],
                'role' => $user['role']
            ]
        ]);
    } catch (Exception $e) {
        error_log("Login Error: " . $e->getMessage());
        http_response_code(500);
        die(json_encode(['error' => 'Login failed']));
    }
}

/**
 * Logout endpoint
 * POST /api/auth.php?action=logout
 */
function logout() {
    try {
        session_destroy();
        http_response_code(200);
        echo json_encode(['success' => true, 'message' => 'Logged out successfully']);
    } catch (Exception $e) {
        error_log("Logout Error: " . $e->getMessage());
        http_response_code(500);
        die(json_encode(['error' => 'Logout failed']));
    }
}

/**
 * Register endpoint (for admin use)
 * POST /api/auth.php?action=register
 */
function register($conn) {
    try {
        // Check if user is admin
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            http_response_code(403);
            die(json_encode(['error' => 'Unauthorized - Admin only']));
        }
        
        $data = json_decode(file_get_contents("php://input"), true);
        
        $required = ['user_id', 'first_name', 'last_name', 'email', 'password', 'role'];
        foreach ($required as $field) {
            if (!isset($data[$field])) {
                http_response_code(400);
                die(json_encode(['error' => "Missing field: $field"]));
            }
        }
        
        if (!in_array($data['role'], ['student', 'professor', 'admin'])) {
            http_response_code(400);
            die(json_encode(['error' => 'Invalid role']));
        }
        
        // Hash password
        $hashed_password = password_hash($data['password'], PASSWORD_BCRYPT);
        
        $query = "INSERT INTO users (user_id, first_name, last_name, email, password, role) 
                  VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $stmt->bind_param(
            'ssssss',
            $data['user_id'],
            $data['first_name'],
            $data['last_name'],
            $data['email'],
            $hashed_password,
            $data['role']
        );
        
        if (!$stmt->execute()) {
            if ($stmt->errno === 1062) { // Duplicate entry
                http_response_code(409);
                die(json_encode(['error' => 'User already exists']));
            }
            throw new Exception("Execute failed: " . $stmt->error);
        }
        
        $stmt->close();
        
        http_response_code(201);
        echo json_encode([
            'success' => true,
            'message' => 'User registered successfully',
            'user_id' => $conn->insert_id
        ]);
    } catch (Exception $e) {
        error_log("Register Error: " . $e->getMessage());
        http_response_code(500);
        die(json_encode(['error' => 'Registration failed']));
    }
}

/**
 * Get current user info
 * GET /api/auth.php?action=me
 */
function getCurrentUser() {
    try {
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            die(json_encode(['error' => 'Not authenticated']));
        }
        
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'user' => [
                'id' => $_SESSION['user_id'],
                'email' => $_SESSION['email'],
                'role' => $_SESSION['role'],
                'first_name' => $_SESSION['first_name'],
                'last_name' => $_SESSION['last_name']
            ]
        ]);
    } catch (Exception $e) {
        error_log("Get User Error: " . $e->getMessage());
        http_response_code(500);
        die(json_encode(['error' => 'Failed to get user info']));
    }
}

// Route requests
$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {
    case 'login':
        login($conn);
        break;
    case 'logout':
        logout();
        break;
    case 'register':
        register($conn);
        break;
    case 'me':
        getCurrentUser();
        break;
    default:
        http_response_code(404);
        echo json_encode(['error' => 'Invalid action']);
}

$conn->close();
?>
