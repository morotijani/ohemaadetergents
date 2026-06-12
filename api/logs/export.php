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
    
    $from = $_GET['from'] ?? null;
    $to = $_GET['to'] ?? null;
    
    $query = "SELECT l.action, l.description, l.ip_address, l.created_at, a.name as admin_name 
              FROM logs l 
              LEFT JOIN admins a ON l.admin_id = a.id";
    $params = [];
    
    if ($from && $to) {
        $query .= " WHERE l.created_at >= ? AND l.created_at <= ?";
        $params[] = $from . ' 00:00:00';
        $params[] = $to . ' 23:59:59';
    }
    
    $query .= " ORDER BY l.created_at DESC";
    
    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $logs = $stmt->fetchAll(\PDO::FETCH_ASSOC);
    
    $filename = "audit_logs_" . date('Y-m-d') . ".csv";
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    $output = fopen('php://output', 'w');
    
    // Output UTF-8 BOM for Excel compatibility
    fputs($output, "\xEF\xBB\xBF");
    
    fputcsv($output, ['Date & Time', 'Admin', 'Action', 'Description', 'IP Address']);
    
    foreach ($logs as $log) {
        fputcsv($output, [
            $log['created_at'],
            $log['admin_name'] ?: 'System',
            $log['action'],
            $log['description'],
            $log['ip_address']
        ]);
    }
    
    fclose($output);
    exit;
    
} catch (\Exception $e) {
    Helpers::jsonResponse(500, 'Server error');
}
