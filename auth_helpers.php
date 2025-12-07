<?php
// auth_helpers.php - simple auth utilities using PDO
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/db_connect.php';

function find_user_by_username($username) {
    $db = get_db_connection();
    $stmt = $db->prepare('SELECT id, username, password_hash, fullname, role_id FROM users WHERE username = :u LIMIT 1');
    $stmt->execute([':u' => $username]);
    $row = $stmt->fetch();
    if (!$row) return null;

    // Resolve role name
    $roleStmt = $db->prepare('SELECT name FROM roles WHERE id = :id LIMIT 1');
    $roleStmt->execute([':id' => $row['role_id']]);
    $r = $roleStmt->fetch();
    $row['role'] = $r ? $r['name'] : null;
    return $row;
}

function login_user_session($userRow) {
    // $userRow expects id, username, fullname, role
    $_SESSION['user_id'] = $userRow['id'];
    $_SESSION['username'] = $userRow['username'];
    $_SESSION['fullname'] = isset($userRow['fullname']) ? $userRow['fullname'] : null;
    $_SESSION['role'] = isset($userRow['role']) ? $userRow['role'] : null;
}

function logout_current_user() {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params['path'], $params['domain'], $params['secure'], $params['httponly']
        );
    }
    session_destroy();
}

function current_user() {
    if (!isset($_SESSION['user_id'])) return null;
    return [
        'id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'] ?? null,
        'fullname' => $_SESSION['fullname'] ?? null,
        'role' => $_SESSION['role'] ?? null,
    ];
}
