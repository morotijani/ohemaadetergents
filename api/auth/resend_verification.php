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

    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'];
    $verifyUrl = $protocol . $host . BASE_URL . "verify.php?token=" . $token;
    
    $subject = "Ohemaa Clean - Verify Your Account";
    $year = date('Y');
    
    $body = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body style="font-family: Arial, sans-serif; background-color: #fdfbf7; margin: 0; padding: 40px 0;">
    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #fdfbf7;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" border="0" style="background-color: #ffffff; border-radius: 12px; border: 1px solid #e9e6df; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.03);">
                    <tr>
                        <td align="center" style="background-color: #2b1b4d; padding: 40px 0;">
                            <h1 style="color: #e7c766; margin: 0; font-family: Georgia, serif; font-size: 28px; letter-spacing: 1px;">OHEMAA</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 40px 40px 50px 40px; color: #1a1620;">
                            <h2 style="margin-top: 0; font-size: 22px; font-weight: normal; color: #2b1b4d;">Welcome to Ohemaa Detergents</h2>
                            <p style="font-size: 16px; line-height: 1.6; color: #4a4650;">Please verify your email address to complete your registration and gain access to your personalized dashboard.</p>
                            
                            <div style="text-align: center; margin: 40px 0;">
                                <a href="{$verifyUrl}" style="display: inline-block; background-color: #c9a227; color: #16102b; font-weight: bold; font-size: 16px; text-decoration: none; padding: 14px 32px; border-radius: 100px;">Verify Email</a>
                            </div>
                            
                            <hr style="border: none; border-top: 1px solid #e9e6df; margin: 40px 0 20px 0;">
                            
                            <p style="font-size: 12px; color: #8a8690; text-align: center; margin: 0; line-height: 1.5;">
                                If you're having trouble clicking the button, copy and paste this link into your browser:<br>
                                <a href="{$verifyUrl}" style="color: #2b1b4d; word-break: break-all;">{$verifyUrl}</a>
                            </p>
                        </td>
                    </tr>
                </table>
                <p style="font-size: 12px; color: #8a8690; text-align: center; margin-top: 20px;">
                    &copy; {$year} Ohemaa Detergents. All rights reserved.
                </p>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;

    sendMail($email, $subject, $body);

    echo json_encode(['status' => 'success', 'message' => 'Verification email sent successfully.']);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'An error occurred. Please try again.']);
}
