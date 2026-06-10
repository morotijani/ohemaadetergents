<?php
require 'src/Database.php';
$db = \App\Database::getInstance()->getConnection();
$stmt = $db->query('SHOW TABLES');
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
