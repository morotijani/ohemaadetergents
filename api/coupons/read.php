<?php
require_once __DIR__ . '/../../src/Database.php';
require_once __DIR__ . '/../../src/Helpers.php';
require_once __DIR__ . '/../../src/Auth.php';

use App\Database;
use App\Helpers;
use App\Auth;

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;

$admin = Auth::requireAdmin();

try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->query("SELECT id, HEX(coupon_id) as uuid, code, type, value, min_order_amount, max_uses, used_count, expiry_date, status, created_at FROM coupons ORDER BY created_at DESC");
    $coupons = $stmt->fetchAll();
    
    foreach ($coupons as &$c) {
        $c['uuid'] = Helpers::uuidBinToStr(hex2bin($c['uuid']));
    }

    Helpers::jsonResponse(200, 'Coupons fetched', $coupons);
} catch (\Exception $e) {
    Helpers::jsonResponse(500, 'Server error');
}
