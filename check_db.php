<?php
require 'src/Database.php';
$db = \App\Database::getInstance()->getConnection();
$stmt = $db->query('DESCRIBE product_reviews');
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
