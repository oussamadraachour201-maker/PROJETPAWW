<?php
// create_db.php - Creates the database if it doesn't exist

$cfg = require __DIR__ . '/config.php';

$servername = $cfg['host'];
$username = $cfg['username'];
$password = $cfg['password'];
$dbname = $cfg['database'];

try {
    // Connect to MySQL without specifying database
    $conn = new PDO("mysql:host=$servername", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create database if it doesn't exist
    $sql = "CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    $conn->exec($sql);

    echo "Database '$dbname' created successfully (or already exists).\n";

    // Now import the tables
    echo "Importing database schema...\n";
    require_once 'import_db.php';

} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}

$conn = null;
?>
