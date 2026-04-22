<?php
require_once __DIR__ . '/src/Database.php';
$db = \App\Database::getInstance()->getConnection();
$sql = file_get_contents(__DIR__ . '/database/create_settings_table.sql');
$db->exec($sql);
echo "Settings Table Created and Seeded Successfully.\n";
