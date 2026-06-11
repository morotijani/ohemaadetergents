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

$oldPassword = $input['old_password'] ?? '';
$newPassword = $input['new_password'] ?? '';
$confirmPassword = $input['confirm_password'] ?? '';

if (empty($oldPassword) || empty($newPassword) || empty($confirmPassword)) {
    Helpers::jsonResponse(400, 'All fields are required');
}

if ($newPassword !== $confirmPassword) {
    Helpers::jsonResponse(400, 'New passwords do not match');
}

if (strlen($newPassword) < 8) {
    Helpers::jsonResponse(400, 'New password must be at least 8 characters');
}

try {
    $db = Database::getInstance()->getConnection();

    $stmt = $db->prepare("SELECT password_hash FROM customers WHERE id = ?");
    $stmt->execute([$customerId]);
    $customer = $stmt->fetch();

    if (!$customer) {
        Helpers::jsonResponse(404, 'User not found');
    }

    if (!password_verify($oldPassword, $customer['password_hash'])) {
        Helpers::jsonResponse(400, 'Incorrect old password');
    }

    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

    $updateStmt = $db->prepare("UPDATE customers SET password_hash = ? WHERE id = ?");
    $updateStmt->execute([$hashedPassword, $customerId]);

    Helpers::jsonResponse(200, 'Password updated successfully');

} catch (Exception $e) {
    Helpers::jsonResponse(500, 'Server error: ' . $e->getMessage());
}
