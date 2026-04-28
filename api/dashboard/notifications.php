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

Auth::requireAdmin();

try {
    $db = Database::getInstance()->getConnection();
    
    // New Orders (Pending)
    $stmt = $db->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'");
    $newOrders = $stmt->fetchColumn();

    // New Reviews (Pending Moderation)
    $stmt = $db->query("SELECT COUNT(*) FROM product_reviews WHERE status = 'pending'");
    $newReviews = $stmt->fetchColumn();

    Helpers::jsonResponse(200, 'Notifications fetched', [
        'new_orders' => (int)$newOrders,
        'new_reviews' => (int)$newReviews,
        'total' => (int)$newOrders + (int)$newReviews
    ]);
} catch (\Exception $e) {
    Helpers::jsonResponse(500, 'Server error');
}
