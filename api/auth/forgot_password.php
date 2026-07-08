<?php
require_once __DIR__ . '/../../src/Database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$email = trim($input['email'] ?? '');

if (empty($email)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Email is required']);
    exit;
}

try {
    $db = App\Database::getInstance()->getConnection();
    
    $stmt = $db->prepare("SELECT id FROM customers WHERE email = ?");
    $stmt->execute([$email]);
    $customer = $stmt->fetch();
    
    if ($customer) {
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        $updateStmt = $db->prepare("UPDATE customers SET reset_token = ?, reset_token_expires_at = ? WHERE id = ?");
        $updateStmt->execute([$token, $expires, $customer['id']]);
        
        // In a real application, you would use PHPMailer or a service like SendGrid here to send the actual email.
        // For this implementation, we will log it or just simulate success.
        $resetLink = 'http://' . $_SERVER['HTTP_HOST'] . '/ohemaadetergents/reset_password.php?token=' . $token;
        error_log("Password reset link for $email: $resetLink");
    }

    // Always return success even if email not found (security best practice)
    echo json_encode(['status' => 'success', 'message' => 'If an account exists with that email, a reset link has been sent.']);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'An error occurred during password reset']);
}
