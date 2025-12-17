<?php
session_start();
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($username) || empty($password)) {
    $_SESSION['login_error'] = 'Please enter both username and password.';
    header('Location: login.php');
    exit;
}

try {
    $pdo = get_db_connection();

    // Query user by user_id (username)
    $stmt = $pdo->prepare("SELECT id, user_id, first_name, last_name, email, password, role FROM users WHERE user_id = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        $_SESSION['login_error'] = 'Invalid username or password.';
        header('Location: login.php');
        exit;
    }

    // Verify password
    if (!password_verify($password, $user['password'])) {
        $_SESSION['login_error'] = 'Invalid username or password.';
        header('Location: login.php');
        exit;
    }

    // Set session variables
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_id_str'] = $user['user_id'];
    $_SESSION['first_name'] = $user['first_name'];
    $_SESSION['last_name'] = $user['last_name'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['fullname'] = $user['first_name'] . ' ' . $user['last_name'];

    // Redirect based on role
    switch ($user['role']) {
        case 'student':
            header('Location: student.php');
            break;
        case 'professor':
            header('Location: professor.php');
            break;
        case 'admin':
            header('Location: admin.php');
            break;
        default:
            $_SESSION['login_error'] = 'Invalid user role.';
            header('Location: login.php');
            break;
    }
    exit;

} catch (Exception $e) {
    $_SESSION['login_error'] = 'Login failed. Please try again.';
    header('Location: login.php');
    exit;
}
?>
