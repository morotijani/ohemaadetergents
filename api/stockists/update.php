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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Helpers::jsonResponse(405, 'Method not allowed');
}

$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

if (empty($input['uuid'])) {
    Helpers::jsonResponse(400, 'UUID is required');
}

$uuid = $input['uuid'];
$shopName = trim($input['shop_name'] ?? '');
$ownerName = trim($input['owner_name'] ?? '');
$phone = trim($input['phone'] ?? '');
$region = trim($input['region'] ?? '');
$townArea = trim($input['town_area'] ?? '');
$businessType = trim($input['business_type'] ?? '');
$monthlyVolume = trim($input['monthly_volume'] ?? '');

try {
    $db = Database::getInstance()->getConnection();
    
    $stmt = $db->prepare("UPDATE stockist_applications 
                          SET shop_name = ?, owner_name = ?, phone = ?, region = ?, town_area = ?, business_type = ?, monthly_volume = ? 
                          WHERE HEX(application_id) = ?");
    $stmt->execute([$shopName, $ownerName, $phone, $region, $townArea, $businessType, $monthlyVolume, $uuid]);

    if ($stmt->rowCount() === 0) {
        // Might be 0 if no changes were made, but we don't want to error out if they just clicked save
        // We can just return success
    }

    Helpers::jsonResponse(200, 'Application updated successfully');
} catch (Exception $e) {
    Helpers::jsonResponse(500, 'Database error: ' . $e->getMessage());
}
