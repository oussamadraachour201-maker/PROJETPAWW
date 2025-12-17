<?php
require_once 'db_connect.php';

try {
    $pdo = get_db_connection();

    // Read the SQL file
    $sql = file_get_contents('database.sql');

    // Execute the SQL
    $pdo->exec($sql);

    echo "Database tables created successfully from database.sql";

} catch (Exception $e) {
    echo "Error importing database: " . $e->getMessage();
}
