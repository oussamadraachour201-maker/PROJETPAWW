<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'db.php';

echo "wachçolt<br>";

$result = executeQuery($conn, "SELECT * FROM users");
$users = fetchAll($result);

foreach ($users as $user) {
    echo "User: " . $user['name'] . " - Role: " . $user['role'] . "<br>";
}
?>