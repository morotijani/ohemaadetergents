<?php
require_once __DIR__ . '/../../src/Database.php';
require_once __DIR__ . '/../../src/Helpers.php';

use App\Database;
use App\Helpers;

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Helpers::jsonResponse(405, 'Method not allowed');
}

$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$token = $input['token'] ?? '';
$password = $input['password'] ?? '';
$confirmPassword = $input['confirm_password'] ?? '';

if (empty($token) || empty($password) || empty($confirmPassword)) {
    Helpers::jsonResponse(400, 'All fields are required');
}

if ($password !== $confirmPassword) {
    Helpers::jsonResponse(400, 'Passwords do not match');
}

if (strlen($password) < 8) {
    Helpers::jsonResponse(400, 'Password must be at least 8 characters');
}

try {
    $db = Database::getInstance()->getConnection();

    $stmt = $db->prepare("SELECT id FROM customers WHERE reset_token = ? AND reset_token_expires_at > NOW()");
    $stmt->execute([$token]);
    $customer = $stmt->fetch();

    if (!$customer) {
        Helpers::jsonResponse(400, 'Invalid or expired reset token');
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $updateStmt = $db->prepare("UPDATE customers SET password_hash = ?, reset_token = NULL, reset_token_expires_at = NULL WHERE id = ?");
    $updateStmt->execute([$hashedPassword, $customer['id']]);

    Helpers::jsonResponse(200, 'Password has been successfully reset. You can now login.', [
        'redirect' => 'login.php'
    ]);

} catch (Exception $e) {
    Helpers::jsonResponse(500, 'Server error: ' . $e->getMessage());
}
