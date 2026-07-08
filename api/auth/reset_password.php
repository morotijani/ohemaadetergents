<?php
require_once __DIR__ . '/../../src/Database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$token = trim($input['token'] ?? '');
$newPass = $input['new_password'] ?? '';
$confirmPass = $input['confirm_password'] ?? '';

if (empty($token) || empty($newPass) || empty($confirmPass)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'All fields are required']);
    exit;
}

if (strlen($newPass) < 8) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Password must be at least 8 characters']);
    exit;
}

if ($newPass !== $confirmPass) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Passwords do not match']);
    exit;
}

try {
    $db = App\Database::getInstance()->getConnection();
    
    $stmt = $db->prepare("SELECT id FROM customers WHERE reset_token = ? AND reset_token_expires_at > NOW()");
    $stmt->execute([$token]);
    $customer = $stmt->fetch();
    
    if (!$customer) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid or expired token']);
        exit;
    }
    
    $hash = password_hash($newPass, PASSWORD_DEFAULT);
    
    $updateStmt = $db->prepare("UPDATE customers SET password_hash = ?, reset_token = NULL, reset_token_expires_at = NULL WHERE id = ?");
    $updateStmt->execute([$hash, $customer['id']]);

    echo json_encode(['status' => 'success', 'message' => 'Password updated successfully']);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'An error occurred while resetting password']);
}
