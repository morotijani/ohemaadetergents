<?php
require_once __DIR__ . '/../../src/Database.php';
require_once __DIR__ . '/../../src/Helpers.php';
require_once __DIR__ . '/../../src/Auth.php';

use App\Database;
use App\Helpers;

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;

$data = json_decode(file_get_contents('php://input'), true);

$productId = $data['product_id'] ?? null;
$name = trim($data['name'] ?? '');
$email = trim($data['email'] ?? '');
$rating = (int)($data['rating'] ?? 0);
$comment = trim($data['comment'] ?? '');

if (!$productId || !$name || !$email || $rating < 1 || $rating > 5 || empty($comment)) {
    Helpers::jsonResponse(400, 'All fields are required');
}

try {
    $db = Database::getInstance()->getConnection();
    
    // 1. Find or create customer
    $stmt = $db->prepare("SELECT id FROM customers WHERE email = ?");
    $stmt->execute([$email]);
    $customer = $stmt->fetch();

    if ($customer) {
        $customerId = $customer['id'];
    } else {
        $customerUuid = Helpers::generateUuidV7Binary();
        // Split name into first and last
        $nameParts = explode(' ', $name, 2);
        $firstName = $nameParts[0];
        $lastName = $nameParts[1] ?? '';
        
        $stmt = $db->prepare("INSERT INTO customers (customer_id, first_name, last_name, email) VALUES (?, ?, ?, ?)");
        $stmt->execute([$customerUuid, $firstName, $lastName, $email]);
        $customerId = $db->lastInsertId();
    }

    // 2. Insert review
    $stmt = $db->prepare("INSERT INTO product_reviews (product_id, customer_id, rating, comment, status) VALUES (?, ?, ?, ?, 'pending')");
    $stmt->execute([$productId, $customerId, $rating, $comment]);

    Helpers::jsonResponse(201, 'Thank you! Your review has been submitted for approval.');
} catch (\Exception $e) {
    Helpers::jsonResponse(500, 'Server error: ' . $e->getMessage());
}
