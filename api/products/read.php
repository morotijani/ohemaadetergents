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
    $stmt = $db->query("SELECT p.id, HEX(p.product_id) as uuid, p.name, p.slug, p.description, p.price, p.stock, p.stock_threshold, p.image_url, p.is_featured, p.category_id, c.name as category_name, p.created_at 
                        FROM products p 
                        LEFT JOIN categories c ON p.category_id = c.id 
                        WHERE p.is_deleted = 0
                        ORDER BY p.created_at DESC");
    $products = $stmt->fetchAll();
    
    $stmtImg = $db->query("SELECT product_id, image_url FROM product_images ORDER BY id ASC");
    $allImages = $stmtImg->fetchAll();
    $imagesByProduct = [];
    foreach ($allImages as $img) {
        $imagesByProduct[$img['product_id']][] = $img['image_url'];
    }

    foreach ($products as &$product) {
        $product['uuid'] = Helpers::uuidBinToStr(hex2bin($product['uuid']));
        $product['images'] = $imagesByProduct[$product['id']] ?? [];
        if ($product['image_url']) {
            array_unshift($product['images'], $product['image_url']);
        }
    }

    Helpers::jsonResponse(200, 'Products fetched', $products);
} catch (\Exception $e) {
    Helpers::jsonResponse(500, 'Server error: ' . $e->getMessage());
}
