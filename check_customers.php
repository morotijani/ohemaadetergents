<?php
require 'src/Database.php';
$db = \App\Database::getInstance()->getConnection();
$stmt = $db->query('DESCRIBE customers');
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
