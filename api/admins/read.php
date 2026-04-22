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
    
    $stmt = $db->query("SELECT id, HEX(admin_id) as uuid, name, email, created_at FROM admins ORDER BY created_at DESC");
    $adminsList = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($adminsList as &$item) {
        $item['uuid'] = Helpers::uuidBinToStr(hex2bin($item['uuid']));
    }

    Helpers::jsonResponse(200, 'Admins fetched', $adminsList);
} catch (\Exception $e) {
    Helpers::jsonResponse(500, 'Server error');
}
