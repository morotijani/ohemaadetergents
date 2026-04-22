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

if (!is_array($input)) {
    Helpers::jsonResponse(400, 'Invalid settings data');
}

try {
    $db = Database::getInstance()->getConnection();
    $db->beginTransaction();

    $stmt = $db->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");

    foreach ($input as $key => $value) {
        $stmt->execute([$key, $value]);
    }

    $db->commit();
    Helpers::logAction($db, 'update_settings', "Updated global settings", $admin['admin_id']);
    
    Helpers::jsonResponse(200, 'Settings updated successfully');
} catch (\Exception $e) {
    if ($db->inTransaction()) $db->rollBack();
    Helpers::jsonResponse(500, 'Server error');
}
