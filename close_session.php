<?php

// Expects POST: session_id
require_once __DIR__ . '/db_connect.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$session_id = isset($_POST['session_id']) ? intval($_POST['session_id']) : 0;
if (!$session_id) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing session_id']);
    exit;
}

try {
    $db = get_db_connection();
    $stmt = $db->prepare('UPDATE attendance_sessions SET status = :status WHERE id = :id');
    $stmt->execute([':status' => 'closed', ':id' => $session_id]);
    if ($stmt->rowCount() === 0) {
        http_response_code(404);
        echo json_encode(['error' => 'Session not found']);
    } else {
        echo json_encode(['success' => true]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to close session']);
}
