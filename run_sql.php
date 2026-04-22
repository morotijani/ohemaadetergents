<?php
require_once __DIR__ . '/src/Database.php';

if ($argc < 2) {
    die("Usage: php run_sql.php <path_to_sql_file>\n");
}

$sqlFile = $argv[1];
if (!file_exists($sqlFile)) {
    die("Error: File $sqlFile not found.\n");
}

try {
    $db = \App\Database::getInstance()->getConnection();
    $sql = file_get_contents($sqlFile);
    $db->exec($sql);
    echo "SQL script executed successfully.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
