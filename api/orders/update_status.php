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

$orderId = (int)($input['id'] ?? 0);
$status = trim($input['status'] ?? '');

$validStatuses = ['pending', 'processing', 'completed', 'cancelled'];

if (!$orderId || !in_array($status, $validStatuses)) {
    Helpers::jsonResponse(400, 'Invalid order ID or status');
}

try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->execute([$status, $orderId]);
    
    if ($stmt->rowCount() > 0) {
        Helpers::logAction($db, 'update_order_status', "Updated order ID: $orderId to $status", $admin['admin_id']);
        Helpers::jsonResponse(200, 'Order status updated');
    } else {
        Helpers::jsonResponse(404, 'Order not found');
    }
} catch (\Exception $e) {
    Helpers::jsonResponse(500, 'Server error');
}
