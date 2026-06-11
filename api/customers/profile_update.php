<?php
session_start();
require_once __DIR__ . '/../../src/Database.php';
require_once __DIR__ . '/../../src/Helpers.php';

use App\Database;
use App\Helpers;

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Helpers::jsonResponse(405, 'Method not allowed');
}

if (!isset($_SESSION['customer_id'])) {
    Helpers::jsonResponse(401, 'Unauthorized');
}

$customerId = $_SESSION['customer_id'];
$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

$firstName = trim($input['first_name'] ?? '');
$lastName = trim($input['last_name'] ?? '');
$phone = trim($input['phone'] ?? '');

if (empty($firstName) || empty($lastName)) {
    Helpers::jsonResponse(400, 'First name and last name are required');
}

try {
    $db = Database::getInstance()->getConnection();

    $stmt = $db->prepare("UPDATE customers SET first_name = ?, last_name = ?, phone = ? WHERE id = ?");
    $stmt->execute([$firstName, $lastName, $phone, $customerId]);

    $_SESSION['customer_name'] = $firstName . ' ' . $lastName;

    Helpers::jsonResponse(200, 'Profile updated successfully', [
        'user' => [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'phone' => $phone
        ]
    ]);

} catch (Exception $e) {
    Helpers::jsonResponse(500, 'Server error: ' . $e->getMessage());
}
