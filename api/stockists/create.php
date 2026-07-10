<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../src/Database.php';
require_once __DIR__ . '/../../src/Helpers.php';

use App\Database;
use App\Helpers;

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Helpers::jsonResponse(405, 'Method not allowed');
}

$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

try {
    $db = Database::getInstance()->getConnection();

    $ownerName = trim($input['owner_name'] ?? '');
    $phone = trim($input['phone'] ?? '');
    $shopName = trim($input['shop_name'] ?? '');
    $bizType = trim($input['biz_type'] ?? '');
    $region = trim($input['region'] ?? '');
    $townArea = trim($input['town_area'] ?? '');
    $monthlyVolume = trim($input['monthly_volume'] ?? '');

    if (empty($ownerName) || empty($phone) || empty($shopName) || empty($region) || empty($townArea)) {
        Helpers::jsonResponse(400, 'Please fill in all required fields.');
    }

    $appId = Helpers::generateUuidV7Binary();

    $stmt = $db->prepare("INSERT INTO stockist_applications (application_id, owner_name, phone, shop_name, business_type, region, town_area, monthly_volume, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
    $stmt->execute([$appId, $ownerName, $phone, $shopName, $bizType, $region, $townArea, $monthlyVolume]);

    Helpers::jsonResponse(200, 'Application submitted successfully');
} catch (Exception $e) {
    Helpers::jsonResponse(500, 'Failed to submit application: ' . $e->getMessage());
}
