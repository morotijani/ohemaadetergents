<?php
require_once __DIR__ . '/../../src/Database.php';
require_once __DIR__ . '/../../src/Helpers.php';
require_once __DIR__ . '/../../src/Auth.php';

use App\Database;
use App\Helpers;
use App\Auth;

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;
if ($_SERVER['REQUEST_METHOD'] !== 'POST') Helpers::jsonResponse(405, 'Method Not Allowed');

$admin = Auth::requireAdmin();
$input = json_decode(file_get_contents('php://input'), true);

$name = trim($input['name'] ?? '');
$email = trim($input['email'] ?? '');
$password = trim($input['password'] ?? '');

if (empty($name) || empty($email) || empty($password)) {
    Helpers::jsonResponse(400, 'All fields are required');
}

try {
    $db = Database::getInstance()->getConnection();
    
    // Check if email exists
    $stmt = $db->prepare("SELECT id FROM admins WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        Helpers::jsonResponse(400, 'Admin with this email already exists');
    }

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    $adminIdBin = Helpers::generateUuidV7Binary();

    $stmt = $db->prepare("INSERT INTO admins (admin_id, name, email, password_hash) VALUES (?, ?, ?, ?)");
    $stmt->execute([$adminIdBin, $name, $email, $passwordHash]);
    
    Helpers::logAction($db, 'create_admin', "Created new admin: $email", $admin['admin_id']);
    
    Helpers::jsonResponse(201, 'Admin created successfully');
} catch (\Exception $e) {
    Helpers::jsonResponse(500, 'Server error');
}
