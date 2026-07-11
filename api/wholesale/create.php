<?php
require_once __DIR__ . '/../../src/Database.php';
require_once __DIR__ . '/../../src/Auth.php';

use App\Database;
use App\Auth;

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;

$admin = Auth::requireAdmin();

$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

$name = $input['name'] ?? '';
$bottle_type = $input['bottle_type'] ?? '';
$carton_size = $input['carton_size'] ?? 0;
$tier1_price = $input['tier1_price'] ?? 0;
$tier2_price = $input['tier2_price'] ?? 0;
$tier3_price = $input['tier3_price'] ?? 0;

$product_key = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $name)) . rand(100,999);

if (empty($name) || empty($bottle_type) || empty($carton_size)) {
    echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
    exit;
}

try {
    $db = Database::getInstance()->getConnection();
    
    $stmt = $db->prepare("INSERT INTO wholesale_products (product_key, name, bottle_type, carton_size, tier1_price, tier2_price, tier3_price) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $product_key,
        $name,
        $bottle_type,
        $carton_size,
        $tier1_price,
        $tier2_price,
        $tier3_price
    ]);
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Product added successfully'
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
