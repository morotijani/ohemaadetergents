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
    
    $stmt = $db->query("SELECT HEX(application_id) as uuid, owner_name, phone, shop_name, business_type, region, town_area, monthly_volume, status, created_at
                        FROM stockist_applications 
                        ORDER BY created_at DESC");
    $stockists = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($stockists as &$stockist) {
        $stockist['created_at_formatted'] = date('M j, Y g:i A', strtotime($stockist['created_at']));
    }

    Helpers::jsonResponse(200, 'Success', $stockists);
} catch (Exception $e) {
    Helpers::jsonResponse(500, 'Database error: ' . $e->getMessage());
}
