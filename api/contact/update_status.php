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

$data = json_decode(file_get_contents('php://input'), true);
$id = (int)($data['id'] ?? 0);
$status = $data['status'] ?? '';

if (!$id || !$status) {
    Helpers::jsonResponse(400, 'Message ID and status are required');
}

$allowedStatuses = ['unread', 'read', 'replied'];
if (!in_array($status, $allowedStatuses)) {
    Helpers::jsonResponse(400, 'Invalid status');
}

try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("UPDATE contact_messages SET status = ? WHERE id = ?");
    $stmt->execute([$status, $id]);

    Helpers::logAction($db, 'update_contact_status', "Updated message status to $status (ID: $id)", $admin['admin_id']);
    Helpers::jsonResponse(200, 'Status updated successfully');
} catch (\Exception $e) {
    Helpers::jsonResponse(500, 'Server error: ' . $e->getMessage());
}
