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

if (empty($input['uuid']) || empty($input['status'])) {
    Helpers::jsonResponse(400, 'UUID and status are required');
}

$uuid = $input['uuid'];
$status = $input['status'];

if (!in_array($status, ['pending', 'approved', 'rejected'])) {
    Helpers::jsonResponse(400, 'Invalid status');
}

try {
    $db = Database::getInstance()->getConnection();
    
    $stmt = $db->prepare("UPDATE stockist_applications SET status = ? WHERE HEX(application_id) = ?");
    $stmt->execute([$status, $uuid]);

    if ($stmt->rowCount() === 0) {
        Helpers::jsonResponse(404, 'Stockist application not found');
    }

    Helpers::jsonResponse(200, 'Status updated successfully');
} catch (Exception $e) {
    Helpers::jsonResponse(500, 'Database error: ' . $e->getMessage());
}
