<?php
// create_session.php
// Expects POST: course_id, group_id, professor_id
require_once __DIR__ . '/db_connect.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$course = isset($_POST['course_id']) ? trim($_POST['course_id']) : '';
$group = isset($_POST['group_id']) ? trim($_POST['group_id']) : '';
$prof = isset($_POST['professor_id']) ? intval($_POST['professor_id']) : null;

if ($course === '' || $group === '' || !$prof) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing parameters']);
    exit;
}

try {
    $db = get_db_connection();
    $stmt = $db->prepare('INSERT INTO attendance_sessions (course_id, group_id, date, opened_by, status) VALUES (:course, :group, :date, :opened_by, :status)');
    $stmt->execute([
        ':course' => $course,
        ':group' => $group,
        ':date' => date('Y-m-d'),
        ':opened_by' => $prof,
        ':status' => 'open'
    ]);
    $id = $db->lastInsertId();
    echo json_encode(['success' => true, 'session_id' => $id]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to create session']);
}
