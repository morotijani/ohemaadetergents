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
    
    $stmt = $db->query("SELECT r.*, p.name as product_name, CONCAT(c.first_name, ' ', c.last_name) as customer_name 
                        FROM product_reviews r 
                        JOIN products p ON r.product_id = p.id 
                        JOIN customers c ON r.customer_id = c.id 
                        ORDER BY r.created_at DESC");
    $reviews = $stmt->fetchAll();
    
    Helpers::jsonResponse(200, 'Reviews fetched', $reviews);
} catch (\Exception $e) {
    Helpers::jsonResponse(500, 'Server error');
}
