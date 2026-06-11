<?php
require_once __DIR__ . '/../../src/Database.php';
require_once __DIR__ . '/../../src/Helpers.php';
require_once __DIR__ . '/../../includes/mailer.php';

use App\Database;
use App\Helpers;

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Helpers::jsonResponse(405, 'Method not allowed');
}

$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$email = trim($input['email'] ?? '');

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    Helpers::jsonResponse(400, 'Valid email is required');
}

try {
    $db = Database::getInstance()->getConnection();

    $stmt = $db->prepare("SELECT id FROM customers WHERE email = ?");
    $stmt->execute([$email]);
    $customer = $stmt->fetch();

    if ($customer) {
        $resetToken = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $updateStmt = $db->prepare("UPDATE customers SET reset_token = ?, reset_token_expires_at = ? WHERE id = ?");
        $updateStmt->execute([$resetToken, $expiresAt, $customer['id']]);

        $resetUrl = "http://" . $_SERVER['HTTP_HOST'] . "/ohemaadetergents/reset_password?token=" . $resetToken;
        $subject = "Password Reset Request - Ohemaa Detergents";
        $body = "
        <div style='font-family: sans-serif; max-width: 600px; margin: 0 auto; color: #000;'>
            <h2 style='font-family: serif; font-size: 24px; font-weight: normal; margin-bottom: 20px;'>Password Reset Request.</h2>
            <p style='font-size: 14px; font-weight: 300; line-height: 1.6; margin-bottom: 30px;'>
                We received a request to reset the password for your account. Please click the button below to set a new password. This link will expire in 1 hour.
            </p>
            <a href='{$resetUrl}' style='display: inline-block; padding: 15px 30px; background-color: #000; color: #fff; text-decoration: none; text-transform: uppercase; letter-spacing: 2px; font-size: 12px; font-weight: 500;'>
                Reset Password
            </a>
            <hr style='border: none; border-top: 1px solid #eee; margin: 40px 0;'>
            <p style='font-size: 11px; color: #666;'>If you did not request a password reset, please ignore this email.</p>
        </div>";

        sendMail($email, $subject, $body);
    }

    // Always return success to prevent email enumeration
    Helpers::jsonResponse(200, 'If your email is registered, you will receive a password reset link shortly.');

} catch (Exception $e) {
    Helpers::jsonResponse(500, 'Server error: ' . $e->getMessage());
}
