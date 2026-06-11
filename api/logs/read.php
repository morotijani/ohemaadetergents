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
    
    // Fetch logs with admin names
    $stmt = $db->query("SELECT l.*, a.name as admin_name 
                        FROM logs l 
                        LEFT JOIN admins a ON l.admin_id = a.id 
                        ORDER BY l.created_at DESC 
                        LIMIT 100");
    $logs = $stmt->fetchAll();
    
    foreach ($logs as &$log) {
        if (isset($log['log_id'])) {
            $log['log_id'] = Helpers::uuidBinToStr($log['log_id']);
        }
    }
    
    Helpers::jsonResponse(200, 'Audit logs fetched', $logs);
} catch (\Exception $e) {
    Helpers::jsonResponse(500, 'Server error');
}
