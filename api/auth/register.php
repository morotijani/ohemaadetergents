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

$firstName = trim($input['first_name'] ?? '');
$lastName = trim($input['last_name'] ?? '');
$email = trim($input['email'] ?? '');
$password = $input['password'] ?? '';
$phone = trim($input['phone'] ?? '');
$address = trim($input['address'] ?? '');

if (empty($firstName) || empty($lastName) || empty($email) || empty($password)) {
    Helpers::jsonResponse(400, 'All fields are required');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    Helpers::jsonResponse(400, 'Invalid email format');
}

if (strlen($password) < 8) {
    Helpers::jsonResponse(400, 'Password must be at least 8 characters');
}

try {
    $db = Database::getInstance()->getConnection();

    // Check if email exists
    $stmt = $db->prepare("SELECT id FROM customers WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        Helpers::jsonResponse(400, 'Email is already registered');
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $verificationToken = bin2hex(random_bytes(32));
    $customerUuid = Helpers::generateUuidV7Binary();

    $stmt = $db->prepare("INSERT INTO customers (customer_id, first_name, last_name, email, password_hash, phone, address, is_verified, verification_token) VALUES (?, ?, ?, ?, ?, ?, ?, 0, ?)");
    $stmt->execute([$customerUuid, $firstName, $lastName, $email, $hashedPassword, $phone, $address, $verificationToken]);

    // Send verification email
    $verifyUrl = BASE_URL . "verify?token=" . $verificationToken;
    $subject = "Verify Your Account - Ohemaa Detergents";
    $body = "
    <div style='font-family: sans-serif; max-width: 600px; margin: 0 auto; color: #000;'>
        <h2 style='font-family: serif; font-size: 24px; font-weight: normal; margin-bottom: 20px;'>Welcome to Ohemaa Detergents.</h2>
        <p style='font-size: 14px; font-weight: 300; line-height: 1.6; margin-bottom: 30px;'>
            Thank you for creating an account with us. Please verify your email address to complete your registration and gain access to your personalized dashboard.
        </p>
        <a href='{$verifyUrl}' style='display: inline-block; padding: 15px 30px; background-color: #000; color: #fff; text-decoration: none; text-transform: uppercase; letter-spacing: 2px; font-size: 12px; font-weight: 500;'>
            Verify Email
        </a>
        <hr style='border: none; border-top: 1px solid #eee; margin: 40px 0;'>
        <p style='font-size: 11px; color: #666;'>If you did not create this account, please ignore this email.</p>
    </div>";

    $mailSent = sendMail($email, $subject, $body);

    Helpers::jsonResponse(200, 'Registration successful. Please check your email to verify your account.', ['mail_sent' => $mailSent]);

} catch (Exception $e) {
    Helpers::jsonResponse(500, 'Server error: ' . $e->getMessage());
}
