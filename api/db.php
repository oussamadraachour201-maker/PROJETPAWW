<?php
// Database Configuration
$db_host = 'localhost';
$db_user = 'root';
$db_password = '';
$db_name = 'attendance_system';
// WAMP MySQL was moved to port 3307 to avoid conflicts with another
// MySQL instance running on 3306. Set the port here.
$db_port = 3307;

// Create connection
try {
    // Connect explicitly using the configured port so the app reaches
    // the WAMP MySQL instance now listening on 3307.
    $conn = new mysqli($db_host, $db_user, $db_password, $db_name, $db_port);
    
    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    // Set charset to UTF-8
    $conn->set_charset("utf8mb4");
    
} catch (Exception $e) {
    error_log("Database Connection Error: " . $e->getMessage());
    http_response_code(500);
    die(json_encode(['error' => 'Database connection failed']));
}

/**
 * Execute a query with proper error handling
 */
function executeQuery($conn, $query, $params = []) {
    try {
        if (!empty($params)) {
            $stmt = $conn->prepare($query);
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            $stmt->bind_param(str_repeat('s', count($params)), ...$params);
            if (!$stmt->execute()) {
                throw new Exception("Execute failed: " . $stmt->error);
            }
            return $stmt;
        } else {
            $result = $conn->query($query);
            if (!$result) {
                throw new Exception("Query failed: " . $conn->error);
            }
            return $result;
        }
    } catch (Exception $e) {
        error_log("Query Error: " . $e->getMessage());
        http_response_code(500);
        die(json_encode(['error' => 'Query execution failed']));
    }
}

/**
 * Fetch all results as associative array
 */
function fetchAll($stmt) {
    try {
        $result = $stmt->get_result();
        if (!$result) {
            throw new Exception("Get result failed");
        }
        return $result->fetch_all(MYSQLI_ASSOC);
    } catch (Exception $e) {
        error_log("Fetch Error: " . $e->getMessage());
        return [];
    }
}

/**
 * Fetch single row as associative array
 */
function fetchOne($stmt) {
    try {
        $result = $stmt->get_result();
        if (!$result) {
            throw new Exception("Get result failed");
        }
        return $result->fetch_assoc();
    } catch (Exception $e) {
        error_log("Fetch Error: " . $e->getMessage());
        return null;
    }
}
?>
