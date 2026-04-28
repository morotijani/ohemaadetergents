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
$id = $data['id'] ?? null;

if (!$id) {
    Helpers::jsonResponse(400, 'ID is required');
}

try {
    $db = Database::getInstance()->getConnection();
    
    // Set products linked to this category to NULL
    $stmt = $db->prepare("UPDATE products SET category_id = NULL WHERE category_id = ?");
    $stmt->execute([$id]);

    $stmt = $db->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->execute([$id]);

    Helpers::jsonResponse(200, 'Category deleted successfully');
} catch (\Exception $e) {
    Helpers::jsonResponse(500, 'Server error');
}
