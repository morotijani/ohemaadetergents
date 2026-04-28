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

    // Low Stock Alerts
    $stmt = $db->query("SELECT COUNT(*) FROM products WHERE stock <= stock_threshold AND is_deleted = 0");
    $lowStock = $stmt->fetchColumn();

    // New Contact Messages (Unread)
    $stmt = $db->query("SELECT COUNT(*) FROM contact_messages WHERE status = 'unread'");
    $newMessages = $stmt->fetchColumn();

    Helpers::jsonResponse(200, 'Notifications fetched', [
        'new_orders' => (int)$newOrders,
        'new_reviews' => (int)$newReviews,
        'low_stock' => (int)$lowStock,
        'new_messages' => (int)$newMessages,
        'total' => (int)$newOrders + (int)$newReviews + (int)$lowStock + (int)$newMessages
    ]);
} catch (\Exception $e) {
    Helpers::jsonResponse(500, 'Server error');
}
