<?php
ob_start();
error_reporting(0);
require_once __DIR__ . '/../../src/Database.php';
require_once __DIR__ . '/../../src/Helpers.php';
require_once __DIR__ . '/../../src/Auth.php';

use App\Database;
use App\Helpers;
use App\Auth;

// Clear any accidental output before headers
ob_clean();
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;

Auth::requireAdmin();

$id = (int)($_GET['id'] ?? 0);
if (!$id) Helpers::jsonResponse(400, 'Product ID is required');

try {
    $db = Database::getInstance()->getConnection();
    
    // 1. Core Details (HEX the binary UUID to prevent JSON errors)
    $stmt = $db->prepare("SELECT p.id, HEX(p.product_id) as uuid, p.category_id, p.name, p.slug, p.description, p.price, p.stock, p.stock_threshold, p.image_url, p.is_featured, p.created_at, c.name as category_name 
                          FROM products p 
                          LEFT JOIN categories c ON p.category_id = c.id 
                          WHERE p.id = ? AND p.is_deleted = 0");
    $stmt->execute([$id]);
    $product = $stmt->fetch();
    
    if (!$product) Helpers::jsonResponse(404, 'Product not found');

    // Convert binary to string for JSON safety
    $product['uuid'] = Helpers::uuidBinToStr(hex2bin($product['uuid']));

    // 2. Extra Images
    $stmt = $db->prepare("SELECT image_url FROM product_images WHERE product_id = ? ORDER BY id ASC");
    $stmt->execute([$id]);
    $product['extra_images'] = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // 3. Order History
    $stmt = $db->prepare("SELECT o.id, o.tracking_number, o.status, o.total_amount, o.created_at, oi.quantity, oi.unit_price as price_at_time
                          FROM order_items oi
                          JOIN orders o ON oi.order_id = o.id
                          WHERE oi.product_id = ?
                          ORDER BY o.created_at DESC");
    $stmt->execute([$id]);
    $orders = $stmt->fetchAll();

    // 4. Reviews
    $stmt = $db->prepare("SELECT r.*, CONCAT(c.first_name, ' ', c.last_name) as customer_name
                          FROM product_reviews r
                          JOIN customers c ON r.customer_id = c.id
                          WHERE r.product_id = ?
                          ORDER BY r.created_at DESC");
    $stmt->execute([$id]);
    $reviews = $stmt->fetchAll();

    // 5. Aggregated Metrics
    $stmt = $db->prepare("SELECT SUM(quantity) as total_sold, SUM(quantity * unit_price) as total_revenue
                          FROM order_items
                          WHERE product_id = ?");
    $stmt->execute([$id]);
    $metrics = $stmt->fetch();

    Helpers::jsonResponse(200, 'Product overview fetched', [
        'product' => $product,
        'orders' => $orders,
        'reviews' => $reviews,
        'metrics' => [
            'total_sold' => (int)($metrics['total_sold'] ?? 0),
            'total_revenue' => (float)($metrics['total_revenue'] ?? 0)
        ]
    ]);
} catch (\Exception $e) {
    Helpers::jsonResponse(500, 'Server error: ' . $e->getMessage());
}
