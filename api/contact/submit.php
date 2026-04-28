<?php
require_once __DIR__ . '/../../src/Database.php';
require_once __DIR__ . '/../../src/Helpers.php';

use App\Database;
use App\Helpers;

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;
if ($_SERVER['REQUEST_METHOD'] !== 'POST') Helpers::jsonResponse(405, 'Method Not Allowed');

$db = Database::getInstance()->getConnection();

$data = json_decode(file_get_contents('php://input'), true);
if (!$data) $data = $_POST;

$name = trim($data['name'] ?? '');
$email = trim($data['email'] ?? '');
$subject = trim($data['subject'] ?? '');
$message = trim($data['message'] ?? '');

if (empty($name) || empty($email) || empty($message)) {
    Helpers::jsonResponse(400, 'Name, email, and message are required');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    Helpers::jsonResponse(400, 'Invalid email address');
}

try {
    $stmt = $db->prepare("INSERT INTO contact_messages (message_id, name, email, subject, message) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([
        Helpers::generateUuidV7Binary(),
        $name,
        $email,
        $subject,
        $message
    ]);

    Helpers::jsonResponse(201, 'Message sent successfully. We will get back to you soon!');
} catch (\Exception $e) {
    Helpers::jsonResponse(500, 'Server error: ' . $e->getMessage());
}
