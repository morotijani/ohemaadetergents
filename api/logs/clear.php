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

$admin = Auth::requireAdmin();

// Strict check for admin role, as requested by user
if (!isset($admin['role']) || strtolower($admin['role']) !== 'admin') {
    Helpers::jsonResponse(403, 'Forbidden: Only users with the Admin role can clear logs.');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Helpers::jsonResponse(405, 'Method Not Allowed');
}

$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$actionType = $input['action_type'] ?? '';

if (!in_array($actionType, ['all', 'older_than_90'])) {
    Helpers::jsonResponse(400, 'Invalid action type');
}

try {
    $db = Database::getInstance()->getConnection();
    
    if ($actionType === 'all') {
        $stmt = $db->prepare("DELETE FROM logs");
        $stmt->execute();
        Helpers::logAction($db, 'clear_logs', 'Cleared ALL activity logs from the database.', $admin['admin_id'], null);
        Helpers::jsonResponse(200, 'All logs have been completely cleared.');
    } else if ($actionType === 'older_than_90') {
        $stmt = $db->prepare("DELETE FROM logs WHERE created_at < DATE_SUB(NOW(), INTERVAL 90 DAY)");
        $stmt->execute();
        $deletedCount = $stmt->rowCount();
        Helpers::logAction($db, 'clear_logs', "Cleared $deletedCount activity logs older than 90 days.", $admin['admin_id'], null);
        Helpers::jsonResponse(200, "Cleared $deletedCount logs older than 90 days.");
    }
} catch (\Exception $e) {
    Helpers::jsonResponse(500, 'Server error while clearing logs.');
}
