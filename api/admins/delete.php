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

$id = (int)($input['id'] ?? 0);

if (!$id) {
    Helpers::jsonResponse(400, 'Admin ID is required');
}

// Prevent self-deletion
if ($id === (int)$admin['id']) {
    Helpers::jsonResponse(400, 'You cannot delete yourself');
}

try {
    $db = Database::getInstance()->getConnection();
    
    // Find email for logging
    $stmt = $db->prepare("SELECT email FROM admins WHERE id = ?");
    $stmt->execute([$id]);
    $target = $stmt->fetch();
    
    if (!$target) {
        Helpers::jsonResponse(404, 'Admin not found');
    }

    $stmt = $db->prepare("DELETE FROM admins WHERE id = ?");
    $stmt->execute([$id]);
    
    Helpers::logAction($db, 'delete_admin', "Deleted admin: " . $target['email'], $admin['admin_id']);
    
    Helpers::jsonResponse(200, 'Admin deleted successfully');
} catch (\Exception $e) {
    Helpers::jsonResponse(500, 'Server error');
}
