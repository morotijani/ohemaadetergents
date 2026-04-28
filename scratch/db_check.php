<?php
$db = new PDO('mysql:host=127.0.0.1;dbname=ohemaa_db', 'root', '');
$stmt = $db->query("SHOW TABLES LIKE 'contact_messages'");
if($stmt->rowCount() > 0) {
    echo "TABLE_FOUND";
} else {
    echo "TABLE_NOT_FOUND";
}
