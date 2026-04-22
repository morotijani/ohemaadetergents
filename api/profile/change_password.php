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

$currentPassword = $input['current_password'] ?? '';
$newPassword = $input['new_password'] ?? '';

if (empty($currentPassword) || empty($newPassword)) {
    Helpers::jsonResponse(400, 'Both current and new passwords are required');
}

try {
    $db = Database::getInstance()->getConnection();
    
    // Verify current password
    $stmt = $db->prepare("SELECT password_hash FROM admins WHERE id = ?");
    $stmt->execute([$admin['admin_id']]);
    $adminData = $stmt->fetch();

    if (!$adminData || !password_verify($currentPassword, $adminData['password_hash'])) {
        Helpers::jsonResponse(400, 'Incorrect current password');
    }

    $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
    $stmt = $db->prepare("UPDATE admins SET password_hash = ? WHERE id = ?");
    $stmt->execute([$newHash, $admin['admin_id']]);
    
    Helpers::logAction($db, 'change_password', "Changed password", $admin['admin_id']);
    
    Helpers::jsonResponse(200, 'Password changed successfully');
} catch (\Exception $e) {
    Helpers::jsonResponse(500, 'Server error');
}
