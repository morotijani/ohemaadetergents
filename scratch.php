<?php
require 'config/config.php';
require 'src/Database.php';
require 'src/Helpers.php';
$db = App\Database::getInstance()->getConnection();

try {
    $db->beginTransaction();
    $orderUuid = App\Helpers::generateUuidV7Binary();
    $trackingNumber = 'TEST-' . rand(1000,9999);
    
    $stmt = $db->prepare("INSERT INTO orders (order_id, tracking_number, customer_id, total_amount, shipping_address, status) VALUES (?, ?, ?, ?, ?, 'pending')");
    $stmt->execute([$orderUuid, $trackingNumber, 1, 50.00, 'Test']);
    $orderId = $db->lastInsertId();
    
    echo "Order ID: " . $orderId . "\n";
    
    // Pick a real product from DB
    $product = $db->query("SELECT id, price FROM products LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    if (!$product) {
        die("No products found.");
    }
    
    $stmt = $db->prepare("INSERT INTO order_items (order_item_id, order_id, product_id, quantity, unit_price) VALUES (?, ?, ?, ?, ?)");
    $itemUuid = App\Helpers::generateUuidV7Binary();
    $stmt->execute([$itemUuid, $orderId, $product['id'], 2, $product['price']]);
    
    echo "Order Item inserted.\n";
    $db->commit();
    echo "Committed.\n";
    
    print_r($db->query("SELECT * FROM order_items WHERE order_id = " . $orderId)->fetchAll(PDO::FETCH_ASSOC));
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    $db->rollBack();
}
