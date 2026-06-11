<?php
require_once __DIR__ . '/src/Database.php';
use App\Database;
try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->query("SELECT l.*, a.name as admin_name 
                        FROM logs l 
                        LEFT JOIN admins a ON l.admin_id = a.id 
                        ORDER BY l.created_at DESC 
                        LIMIT 100");
    $logs = $stmt->fetchAll();
    print_r($logs);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
