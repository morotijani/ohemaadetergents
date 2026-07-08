<?php
require_once __DIR__ . '/../../src/Database.php';
require_once __DIR__ . '/../../includes/mailer.php';

$config = require __DIR__ . '/../../config/config.php';
if (!defined('BASE_URL')) {
    define('BASE_URL', $config['app']['url'] . '/');
}

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
    
    $stmt = $db->prepare("SELECT id, is_verified, verification_token FROM customers WHERE email = ?");
    $stmt->execute([$email]);
    $customer = $stmt->fetch();
    
    if (!$customer) {
        // Return success even if not found to prevent email enumeration
        echo json_encode(['status' => 'success', 'message' => 'If an account exists, a verification email has been sent.']);
        exit;
    }

    if ($customer['is_verified']) {
        echo json_encode(['status' => 'success', 'message' => 'This account is already verified.']);
        exit;
    }

    $token = $customer['verification_token'];
    if (empty($token)) {
        $token = bin2hex(random_bytes(32));
        $updateStmt = $db->prepare("UPDATE customers SET verification_token = ? WHERE id = ?");
        $updateStmt->execute([$token, $customer['id']]);
    }

    $verifyUrl = BASE_URL . "verify.php?token=" . $token;
    $subject = "Verify Your Account - Ohemaa Detergents";
    $body = "
    <div style='font-family: sans-serif; max-width: 600px; margin: 0 auto; color: #000;'>
        <h2 style='font-family: serif; font-size: 24px; font-weight: normal; margin-bottom: 20px;'>Welcome to Ohemaa Detergents.</h2>
        <p style='font-size: 14px; font-weight: 300; line-height: 1.6; margin-bottom: 30px;'>
            Please verify your email address to complete your registration and gain access to your personalized dashboard.
        </p>
        <a href='{$verifyUrl}' style='display: inline-block; padding: 15px 30px; background-color: #000; color: #fff; text-decoration: none; text-transform: uppercase; letter-spacing: 2px; font-size: 12px; font-weight: 500;'>
            Verify Email
        </a>
    </div>";

    sendMail($email, $subject, $body);

    echo json_encode(['status' => 'success', 'message' => 'Verification email sent successfully.']);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'An error occurred. Please try again.']);
}
