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

$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

$email = trim($input['email'] ?? '');
$password = $input['password'] ?? '';

if (empty($email) || empty($password)) {
    Helpers::jsonResponse(400, 'Email and password are required');
}

try {
    $db = Database::getInstance()->getConnection();

    $stmt = $db->prepare("SELECT id, password_hash, is_verified, first_name, last_name FROM customers WHERE email = ?");
    $stmt->execute([$email]);
    $customer = $stmt->fetch();

    if (!$customer) {
        Helpers::jsonResponse(401, 'Invalid credentials');
    }

    if (!password_verify($password, $customer['password_hash'])) {
        Helpers::jsonResponse(401, 'Invalid credentials');
    }

    if ((int)$customer['is_verified'] === 0) {
        Helpers::jsonResponse(403, 'Please verify your email address before logging in.', ['action' => 'verify', 'email' => $email]);
    }

    // Start session and set session variables
    $_SESSION['customer_id'] = $customer['id'];
    $_SESSION['customer_name'] = $customer['first_name'] . ' ' . $customer['last_name'];

    Helpers::jsonResponse(200, 'Login successful', [
        'redirect' => 'profile'
    ]);

} catch (Exception $e) {
    Helpers::jsonResponse(500, 'Server error: ' . $e->getMessage());
}
