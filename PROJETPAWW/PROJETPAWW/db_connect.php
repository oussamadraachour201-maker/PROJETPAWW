<?php
// db_connect.php - returns a PDO connection or throws
function get_db_connection() {
    $cfg = require __DIR__ . '/config.php';

    $dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', $cfg['host'], $cfg['database']);

    try {
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        $pdo = new PDO($dsn, $cfg['username'], $cfg['password'], $options);
        return $pdo;
    } catch (PDOException $e) {
        // Optional: log to file
        $logDir = __DIR__ . '/logs';
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
        }
        $msg = '[' . date('Y-m-d H:i:s') . '] DB Connection Error: ' . $e->getMessage() . PHP_EOL;
        @file_put_contents($logDir . '/db_errors.log', $msg, FILE_APPEND);

        // Re-throw or return null depending on your app. We'll throw here.
        throw $e;
    }
}
