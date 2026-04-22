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

$category = trim($input['category'] ?? '');
$amount = $input['amount'] ?? 0;
$description = trim($input['description'] ?? '');
$date = $input['date'] ?? date('Y-m-d');

if (empty($category) || $amount <= 0 || empty($date)) {
    Helpers::jsonResponse(400, 'Category, amount and date are required');
}

try {
    $db = Database::getInstance()->getConnection();
    
    $stmt = $db->prepare("INSERT INTO expenditure (admin_id, category, amount, description, date) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$admin['admin_id'], $category, $amount, $description, $date]);
    
    Helpers::logAction($db, 'create_expenditure', "Added expenditure: $category - GHS $amount", $admin['admin_id']);
    
    Helpers::jsonResponse(201, 'Expenditure added successfully');
} catch (\Exception $e) {
    Helpers::jsonResponse(500, 'Server error');
}
