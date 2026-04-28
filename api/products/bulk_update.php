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

$data = json_decode(file_get_contents('php://input'), true);
$categoryId = !empty($data['category_id']) ? (int)$data['category_id'] : null;
$target = $data['target'] ?? 'price'; // 'price' or 'stock'
$action = $data['action'] ?? 'increase'; // 'increase', 'decrease', 'set'
$type = $data['type'] ?? 'fixed'; // 'fixed' or 'percentage'
$value = (float)($data['value'] ?? 0);

if (!in_array($target, ['price', 'stock']) || !in_array($action, ['increase', 'decrease', 'set']) || $value < 0) {
    Helpers::jsonResponse(400, 'Invalid parameters');
}

try {
    $db = Database::getInstance()->getConnection();
    
    $whereClause = "";
    $params = [];
    if ($categoryId) {
        $whereClause = " WHERE category_id = ?";
        $params[] = $categoryId;
    }

    $column = ($target === 'price') ? 'price' : 'stock';
    $sql = "UPDATE products SET $column = ";

    if ($action === 'set') {
        $sql .= "?";
        array_unshift($params, $value);
    } else {
        $operator = ($action === 'increase') ? '+' : '-';
        if ($type === 'percentage') {
            $multiplier = $value / 100;
            $sql .= "$column $operator ($column * ?)";
            array_unshift($params, $multiplier);
        } else {
            $sql .= "$column $operator ?";
            array_unshift($params, $value);
        }
    }

    $sql .= $whereClause;

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $affected = $stmt->rowCount();

    Helpers::logAction($db, 'bulk_update', "Bulk updated $target for category " . ($categoryId ?? 'All') . ". Affected: $affected rows.", $admin['admin_id']);

    Helpers::jsonResponse(200, "Successfully updated $affected products.");
} catch (\Exception $e) {
    Helpers::jsonResponse(500, 'Server error: ' . $e->getMessage());
}
