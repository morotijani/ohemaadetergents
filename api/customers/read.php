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
    
    $stmt = $db->query("SELECT id, HEX(customer_id) as uuid, first_name, last_name, email, phone, created_at FROM customers ORDER BY created_at DESC");
    $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($customers as &$customer) {
        $customer['uuid'] = Helpers::uuidBinToStr(hex2bin($customer['uuid']));
    }

    Helpers::jsonResponse(200, 'Customers fetched', $customers);
} catch (\Exception $e) {
    Helpers::jsonResponse(500, 'Server error');
}
