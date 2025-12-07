<?php
header('Content-Type: application/json; charset=utf-8');
// API: save attendance
// Expects JSON POST body { "session_id": optional, "course_id": optional, "date": "YYYY-MM-DD", "attendance": [{"student_id": <id or matricule>, "status":"present|absent|late|excused", "remark": "..."}, ...] }

try {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    if (!is_array($data)) {
        throw new Exception('Invalid JSON');
    }

    require_once __DIR__ . '/../db_connect.php';
    require_once __DIR__ . '/../auth_helpers.php';
    $pdo = get_db_connection();

    // require logged-in user
    $user = current_user();
    if (!$user) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Authentication required']);
        exit;
    }
    // only professors and admins can record attendance
    if (!in_array($user['role'], ['professor','admin'])) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Permission denied']);
        exit;
    }

    // Basic validation
    $attendanceList = $data['attendance'] ?? null;
    if (!$attendanceList || !is_array($attendanceList)) {
        throw new Exception('Missing attendance list');
    }

    $pdo->beginTransaction();

    $sessionId = $data['session_id'] ?? null;
    $date = $data['date'] ?? date('Y-m-d');
    $courseId = $data['course_id'] ?? 0;

    if (!$sessionId) {
        // Create a new session row and set opened_by to current user
        $stmt = $pdo->prepare('INSERT INTO sessions (course_id, date, opened_by, status) VALUES (?, ?, ?, ? )');
        $stmt->execute([$courseId, $date, $user['id'], 'open']);
        $sessionId = $pdo->lastInsertId();
    }

    // Prepare insert for attendance rows
    $ins = $pdo->prepare('INSERT INTO attendance (session_id, student_id, status, remark) VALUES (?, ?, ?, ?)');

    foreach ($attendanceList as $row) {
        $studentRef = $row['student_id'] ?? null;
        $status = $row['status'] ?? 'absent';
        $remark = $row['remark'] ?? null;

        if (!$studentRef) continue;

        // If studentRef is not numeric, attempt to resolve by matricule
        if (!is_numeric($studentRef)) {
            $q = $pdo->prepare('SELECT id FROM students WHERE matricule = ? LIMIT 1');
            $q->execute([$studentRef]);
            $found = $q->fetch();
            if ($found) {
                $studentId = $found['id'];
            } else {
                // skip unknown student
                continue;
            }
        } else {
            $studentId = (int)$studentRef;
        }

        $ins->execute([$sessionId, $studentId, $status, $remark]);
    }

    $pdo->commit();

    echo json_encode(['success' => true, 'session_id' => (int)$sessionId]);

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

?>
