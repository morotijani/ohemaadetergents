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
    
    $stmt = $db->query("SELECT e.*, a.name as admin_name FROM expenditure e JOIN admins a ON e.admin_id = a.id ORDER BY e.date DESC, e.created_at DESC");
    $list = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    Helpers::jsonResponse(200, 'Expenditures fetched', $list);
} catch (\Exception $e) {
    Helpers::jsonResponse(500, 'Server error');
}
