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

if (empty($name) || empty($email)) {
    Helpers::jsonResponse(400, 'Name and email are required');
}

try {
    $db = Database::getInstance()->getConnection();
    
    // Check if email is used by another admin
    $stmt = $db->prepare("SELECT id FROM admins WHERE email = ? AND id != ?");
    $stmt->execute([$email, $admin['admin_id']]);
    if ($stmt->fetch()) {
        Helpers::jsonResponse(400, 'Email already in use');
    }

    $stmt = $db->prepare("UPDATE admins SET name = ?, email = ? WHERE id = ?");
    $stmt->execute([$name, $email, $admin['admin_id']]);
    
    Helpers::logAction($db, 'update_profile', "Updated profile: $email", $admin['admin_id']);
    
    Helpers::jsonResponse(200, 'Profile updated successfully');
} catch (\Exception $e) {
    Helpers::jsonResponse(500, 'Server error');
}
