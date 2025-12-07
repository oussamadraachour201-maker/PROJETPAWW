<?php
require_once __DIR__ . '/db_connect.php';

try {
    $pdo = get_db_connection();
    echo "Connection successful";
} catch (Exception $e) {
    echo "Connection failed: ";
    echo htmlspecialchars($e->getMessage());
}
