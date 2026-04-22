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
    $stmt = $db->prepare("SELECT name, email, profile_image FROM admins WHERE id = ?");
    $stmt->execute([$admin['admin_id']]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    Helpers::jsonResponse(200, 'Profile fetched', $data);
} catch (\Exception $e) {
    Helpers::jsonResponse(500, 'Server error');
}
