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
$input = json_decode(file_get_contents('php://input'), true);

$id = $input['id'] ?? null;

if (!$id) {
    Helpers::jsonResponse(400, 'ID is required');
}

try {
    $db = Database::getInstance()->getConnection();
    
    $stmt = $db->prepare("DELETE FROM expenditure WHERE id = ?");
    $stmt->execute([$id]);
    
    Helpers::logAction($db, 'delete_expenditure', "Deleted expenditure ID: $id", $admin['admin_id']);
    
    Helpers::jsonResponse(200, 'Expenditure deleted successfully');
} catch (\Exception $e) {
    Helpers::jsonResponse(500, 'Server error');
}
