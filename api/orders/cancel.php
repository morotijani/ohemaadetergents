<?php
require_once __DIR__ . '/../../src/Database.php';
require_once __DIR__ . '/../../src/Helpers.php';

use App\Database;
use App\Helpers;

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Helpers::jsonResponse(405, 'Method not allowed');
}

$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$reference = $input['reference'] ?? null;

if (!$reference) {
    Helpers::jsonResponse(400, 'Invalid request, reference required');
}

try {
    $db = Database::getInstance()->getConnection();
    
    // Only delete if it's strictly still "pending" (prevent deleting paid orders)
    $stmt = $db->prepare("DELETE FROM orders WHERE tracking_number = ? AND status = 'pending'");
    $stmt->execute([$reference]);
    
    Helpers::jsonResponse(200, 'Order cancelled');
} catch (Exception $e) {
    Helpers::jsonResponse(500, 'Server error');
}
