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

    $stmt = $db->prepare("SELECT id, is_verified, first_name FROM customers WHERE email = ?");
    $stmt->execute([$email]);
    $customer = $stmt->fetch();

    if ($customer) {
        if ((int)$customer['is_verified'] === 1) {
            // Already verified, send generic success so we don't leak user states, or inform them.
            Helpers::jsonResponse(400, 'This account is already verified. Please sign in.');
        }

        $verificationToken = bin2hex(random_bytes(32));

        $updateStmt = $db->prepare("UPDATE customers SET verification_token = ? WHERE id = ?");
        $updateStmt->execute([$verificationToken, $customer['id']]);

        $verifyUrl = "http://" . $_SERVER['HTTP_HOST'] . "/ohemaadetergents/verify?token=" . $verificationToken;
        $subject = "Verify Your Account - Ohemaa Detergents";
        $body = "
        <div style='font-family: sans-serif; max-width: 600px; margin: 0 auto; color: #000;'>
            <h2 style='font-family: serif; font-size: 24px; font-weight: normal; margin-bottom: 20px;'>Welcome to Ohemaa, {$customer['first_name']}.</h2>
            <p style='font-size: 14px; font-weight: 300; line-height: 1.6; margin-bottom: 30px;'>
                Thank you for creating an account. Please click the button below to verify your email address and activate your account.
            </p>
            <a href='{$verifyUrl}' style='display: inline-block; padding: 15px 30px; background-color: #000; color: #fff; text-decoration: none; text-transform: uppercase; letter-spacing: 2px; font-size: 12px; font-weight: 500;'>
                Verify Email
            </a>
            <hr style='border: none; border-top: 1px solid #eee; margin: 40px 0;'>
            <p style='font-size: 11px; color: #666;'>If you did not request this, please ignore this email.</p>
        </div>";

        sendMail($email, $subject, $body);
    }

    Helpers::jsonResponse(200, 'If your email is registered and unverified, a new link has been sent.');

} catch (Exception $e) {
    Helpers::jsonResponse(500, 'Server error: ' . $e->getMessage());
}
