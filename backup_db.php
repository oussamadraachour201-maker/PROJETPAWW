<?php
// backup_db.php - generate a SQL dump (simple implementation using PDO)
require_once __DIR__ . '/db_connect.php';

try {
    $db = get_db_connection();

    $sqlOutput = "-- Backup generated on " . date('c') . "\n\n";

    // Get all tables
    $tables = [];
    $res = $db->query('SHOW TABLES');
    while ($row = $res->fetch(PDO::FETCH_NUM)) {
        $tables[] = $row[0];
    }

    foreach ($tables as $table) {
        // CREATE TABLE
        $create = $db->query("SHOW CREATE TABLE `{$table}`")->fetch(PDO::FETCH_ASSOC);
        $sqlOutput .= "-- Table structure for `{$table}`\n";
        $sqlOutput .= $create['Create Table'] . ";\n\n";

        // Data
        $rows = $db->query("SELECT * FROM `{$table}`")->fetchAll(PDO::FETCH_ASSOC);
        if (count($rows) > 0) {
            $sqlOutput .= "-- Dumping data for `{$table}`\n";
            foreach ($rows as $r) {
                $cols = array_map(function($c){ return "`".$c."`"; }, array_keys($r));
                $vals = array_map(function($v) use ($db) { if ($v === null) return 'NULL'; return "'" . str_replace("'", "\\'", $v) . "'"; }, array_values($r));
                $sqlOutput .= "INSERT INTO `{$table}` (" . implode(', ', $cols) . ") VALUES (" . implode(', ', $vals) . ");\n";
            }
            $sqlOutput .= "\n";
        }
    }

    $filename = 'backup_' . date('Ymd_His') . '.sql';
    header('Content-Type: application/sql');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    echo $sqlOutput;
    exit;

} catch (Exception $e) {
    http_response_code(500);
    echo "Error creating backup: " . $e->getMessage();
    exit;
}
