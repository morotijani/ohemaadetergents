<?php
$db = new PDO('mysql:host=127.0.0.1;dbname=ohemaa_db', 'root', '');
$stmt = $db->query("SHOW TABLES LIKE 'product_reviews'");
if($stmt->rowCount() > 0) {
    $stmt = $db->query('DESCRIBE product_reviews');
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo json_encode($row) . "\n";
    }
} else {
    echo "TABLE_NOT_FOUND";
}
