<?php
$db = new PDO('mysql:host=127.0.0.1;dbname=ohemaa_db', 'root', '');
print_r($db->query('DESCRIBE products')->fetchAll(PDO::FETCH_COLUMN));
