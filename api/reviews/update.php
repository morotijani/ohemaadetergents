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
$id = $data['id'] ?? null;
$status = $data['status'] ?? null;

if (!$id || !in_array($status, ['approved', 'rejected', 'pending'])) {
    Helpers::jsonResponse(400, 'Invalid parameters');
}

try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("UPDATE product_reviews SET status = ? WHERE id = ?");
    $stmt->execute([$status, $id]);

    Helpers::logAction($db, 'review_status_update', "Updated review #$id status to $status", $admin['admin_id']);

    Helpers::jsonResponse(200, 'Review status updated');
} catch (\Exception $e) {
    Helpers::jsonResponse(500, 'Server error');
}
