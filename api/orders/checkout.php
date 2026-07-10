<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../src/Database.php';
require_once __DIR__ . '/../../src/Cart.php';
require_once __DIR__ . '/../../src/Helpers.php';

use App\Database;
use App\Cart;
use App\Helpers;

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Helpers::jsonResponse(405, 'Method not allowed');
}

$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

$firstName = trim($input['first_name'] ?? '');
$lastName = trim($input['last_name'] ?? '');
$email = trim($input['email'] ?? '');
$phone = trim($input['phone'] ?? '');
$address = trim($input['address'] ?? '');
$townArea = trim($input['town_area'] ?? '');
$region = trim($input['region'] ?? '');
$deliveryNote = trim($input['delivery_note'] ?? '');

if (empty($firstName) || empty($email) || empty($address)) {
    Helpers::jsonResponse(400, 'Please fill in all required fields.');
}

$db = Database::getInstance()->getConnection();
$cart = new Cart();
$cartItems = $cart->getItems();

if (empty($cartItems)) {
    Helpers::jsonResponse(400, 'Your cart is empty.');
}

$total = 0;
$products = [];
$ids = array_keys($cartItems);
$inClause = implode(',', array_fill(0, count($ids), '?'));
$stmt = $db->prepare("SELECT id, name, price, stock, image_url FROM products WHERE id IN ($inClause)");
$stmt->execute($ids);
$productsData = $stmt->fetchAll();

foreach ($productsData as $p) {
    $qty = $cartItems[$p['id']];
    $total += ($qty * $p['price']);
    $products[] = ['id' => $p['id'], 'price' => $p['price'], 'qty' => $qty];
}

$grandTotal = $total;
$config = require __DIR__ . '/../../config/config.php';
$baseUrl = rtrim($config['app']['url'], '/') . '/';

try {
    $db->beginTransaction();

    $stmt = $db->prepare("SELECT id FROM customers WHERE email = ?");
    $stmt->execute([$email]);
    $customer = $stmt->fetch();

    if ($customer) {
        $customerId = $customer['id'];
    } else {
        $customerUuid = Helpers::generateUuidV7Binary();
        $randomPassword = bin2hex(random_bytes(8));
        $hash = password_hash($randomPassword, PASSWORD_DEFAULT);
        $stmt = $db->prepare("INSERT INTO customers (customer_id, first_name, last_name, email, phone, password_hash) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$customerUuid, $firstName, $lastName, $email, $phone, $hash]);
        $customerId = $db->lastInsertId();
    }

    $orderUuid = Helpers::generateUuidV7Binary();
    $trackingNumber = 'ORD-' . strtoupper(substr(uniqid(), -6));
    
    $stmt = $db->prepare("INSERT INTO orders (order_id, tracking_number, customer_id, total_amount, shipping_address, town_area, region, delivery_note, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
    $stmt->execute([$orderUuid, $trackingNumber, $customerId, $grandTotal, $address, $townArea, $region, $deliveryNote]);
    $orderId = $db->lastInsertId();

    $stmt = $db->prepare("INSERT INTO order_items (order_item_id, order_id, product_id, quantity, unit_price) VALUES (?, ?, ?, ?, ?)");
    foreach ($products as $p) {
        $stmt->execute([Helpers::generateUuidV7Binary(), $orderId, $p['id'], $p['qty'], $p['price']]);
    }

    $amountInPesewas = $grandTotal * 100; 
    
    $postData = [
        'email' => $email,
        'amount' => $amountInPesewas,
        'currency' => 'GHS',
        'reference' => $trackingNumber,
        'callback_url' => $baseUrl . 'verify_payment.php',
        'metadata' => ['custom_fields' => [['display_name' => "Order ID", 'variable_name' => "order_id", 'value' => $trackingNumber]]]
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://api.paystack.co/transaction/initialize");
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer " . $config['paystack']['secret_key'],
        "Content-Type: application/json"
    ]);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    $result = json_decode($response, true);
    
    if ($result && $result['status'] === true) {
        $db->commit();
        Helpers::jsonResponse(200, 'Success', [
            'access_code' => $result['data']['access_code'],
            'reference' => $result['data']['reference'] ?? $trackingNumber,
            'public_key' => $config['paystack']['public_key'],
            'email' => $email,
            'amount' => $amountInPesewas
        ]);
    } else {
        $db->rollBack();
        Helpers::jsonResponse(400, 'Payment initialization failed. Error: ' . ($result['message'] ?? 'Unknown error'));
    }
    
} catch (\Exception $e) {
    if ($db->inTransaction()) $db->rollBack();
    Helpers::jsonResponse(500, 'Checkout failed: ' . $e->getMessage());
}
