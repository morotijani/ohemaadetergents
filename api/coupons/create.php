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
$code = strtoupper(trim($data['code'] ?? ''));
$type = $data['type'] ?? 'percentage';
$value = (float)($data['value'] ?? 0);
$minOrderAmount = (float)($data['min_order_amount'] ?? 0);
$maxUses = !empty($data['max_uses']) ? (int)$data['max_uses'] : null;
$expiryDate = !empty($data['expiry_date']) ? $data['expiry_date'] : null;
$status = $data['status'] ?? 'active';

if (empty($code) || $value <= 0) {
    Helpers::jsonResponse(400, 'Code and Value are required and must be positive');
}

try {
    $db = Database::getInstance()->getConnection();
    
    // Check if code exists
    $stmt = $db->prepare("SELECT id FROM coupons WHERE code = ?");
    $stmt->execute([$code]);
    if ($stmt->fetch()) {
        Helpers::jsonResponse(400, 'Coupon code already exists');
    }

    $uuid = Helpers::generateUuidV7Binary();
    $stmt = $db->prepare("INSERT INTO coupons (coupon_id, code, type, value, min_order_amount, max_uses, expiry_date, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$uuid, $code, $type, $value, $minOrderAmount, $maxUses, $expiryDate, $status]);

    Helpers::jsonResponse(201, 'Coupon created successfully');
} catch (\Exception $e) {
    Helpers::jsonResponse(500, 'Server error: ' . $e->getMessage());
}
