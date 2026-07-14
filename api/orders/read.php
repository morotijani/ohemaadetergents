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
    
    $stmt = $db->query("SELECT o.id, HEX(o.order_id) as uuid, o.tracking_number, o.total_amount, o.status, o.shipping_address, o.created_at, 
                               c.first_name, c.last_name, c.email, c.phone 
                        FROM orders o 
                        JOIN customers c ON o.customer_id = c.id 
                        ORDER BY o.created_at DESC");
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $stmtItems = $db->query("SELECT oi.order_id, oi.quantity, oi.unit_price, oi.size_label, p.name as product_name 
                             FROM order_items oi 
                             JOIN products p ON oi.product_id = p.id");
    $items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);
    
    $itemsByOrderId = [];
    foreach ($items as $item) {
        $itemsByOrderId[$item['order_id']][] = [
            'product_name' => $item['product_name'],
            'size_label'   => $item['size_label'],
            'quantity'     => $item['quantity'],
            'unit_price'   => $item['unit_price'],
            'subtotal'     => $item['quantity'] * $item['unit_price']
        ];
    }
    
    foreach ($orders as &$order) {
        $order['uuid'] = Helpers::uuidBinToStr(hex2bin($order['uuid']));
        $order['customer_name'] = $order['first_name'] . ' ' . $order['last_name'];
        $order['items'] = $itemsByOrderId[$order['id']] ?? [];
    }

    Helpers::jsonResponse(200, 'Orders fetched', $orders);
} catch (\Exception $e) {
    Helpers::jsonResponse(500, 'Server error: ' . $e->getMessage());
}
