<?php
require_once __DIR__ . '/../../src/Database.php';
require_once __DIR__ . '/../../src/Auth.php';

require_once __DIR__ . '/../../src/Helpers.php';

use App\Database;
use App\Auth;
use App\Helpers;

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;

// For read, we allow public, but if token is provided, we check if admin.
$token = Helpers::getBearerToken();
$isAdmin = false;
if ($token) {
    try {
        $admin = Auth::validateJwt($token);
        if ($admin) $isAdmin = true;
    } catch(Exception $e) {}
}

try {
    $db = Database::getInstance()->getConnection();
    
    $query = "SELECT * FROM wholesale_products";
    if (!$isAdmin) {
        $query .= " WHERE status = 'active'";
    }
    $query .= " ORDER BY id ASC";
    
    $stmt = $db->query($query);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'status' => 'success',
        'data' => $products
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
