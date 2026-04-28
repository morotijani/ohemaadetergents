<?php
$db = new PDO('mysql:host=127.0.0.1;dbname=ohemaa_db', 'root', '');
$stmt = $db->query('DESCRIBE customers');
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo json_encode($row) . "\n";
}
