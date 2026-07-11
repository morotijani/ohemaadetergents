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
$id = $input['id'] ?? 0;
$status = $input['status'] ?? '';

if (empty($id) || !in_array($status, ['active', 'inactive'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid parameters']);
    exit;
}

try {
    $db = Database::getInstance()->getConnection();
    
    $stmt = $db->prepare("UPDATE wholesale_products SET status = ? WHERE id = ?");
    $stmt->execute([$status, $id]);
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Status updated successfully'
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
