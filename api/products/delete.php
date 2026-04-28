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
$db = Database::getInstance()->getConnection();

$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$id = (int)($input['id'] ?? 0);

if (!$id) Helpers::jsonResponse(400, 'Product ID is required');

try {
    $stmt = $db->prepare("SELECT name, image_url FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $product = $stmt->fetch();
    
    if (!$product) Helpers::jsonResponse(404, 'Product not found');
    
    // Soft delete: update is_deleted flag instead of deleting row
    $stmt = $db->prepare("UPDATE products SET is_deleted = 1 WHERE id = ?");
    $stmt->execute([$id]);
    
    Helpers::logAction($db, 'delete_product', "Deleted product: {$product['name']} (ID: $id)", $admin['admin_id']);
    
    Helpers::jsonResponse(200, 'Product deleted successfully');
} catch (\Exception $e) {
    Helpers::jsonResponse(500, 'Server error: ' . $e->getMessage());
}
