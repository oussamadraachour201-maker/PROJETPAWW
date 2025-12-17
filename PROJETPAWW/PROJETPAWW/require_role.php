<?php
// require_role.php - include at top of pages to restrict access
if (session_status() === PHP_SESSION_NONE) session_start();
function require_role($allowed = []) {
    if (!is_array($allowed)) $allowed = [$allowed];
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }
    $role = $_SESSION['role'] ?? null;
    if (!in_array($role, $allowed)) {
        // Simple unauthorized page
        http_response_code(403);
        echo '<h2>403 - Forbidden</h2><p>You do not have permission to view this page.</p><p><a href="login.php">Return to login</a></p>';
        exit;
    }
}
