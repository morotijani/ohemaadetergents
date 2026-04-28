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
    $stmt = $db->query("SELECT id, HEX(category_id) as uuid, name, slug, description, created_at FROM categories ORDER BY name ASC");
    $categories = $stmt->fetchAll();
    
    foreach ($categories as &$cat) {
        $cat['uuid'] = Helpers::uuidBinToStr(hex2bin($cat['uuid']));
    }

    Helpers::jsonResponse(200, 'Categories fetched', $categories);
} catch (\Exception $e) {
    Helpers::jsonResponse(500, 'Server error');
}
