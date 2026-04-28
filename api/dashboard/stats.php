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
    
    // Core Stats
    $stats = [];
    
    // Revenue (exclude cancelled)
    $stmt = $db->query("SELECT SUM(total_amount) as total FROM orders WHERE status != 'cancelled'");
    $stats['revenue'] = (float)($stmt->fetch()['total'] ?? 0);
    
    // Orders count
    $stmt = $db->query("SELECT COUNT(*) as total FROM orders");
    $stats['total_orders'] = (int)($stmt->fetch()['total'] ?? 0);
    
    $stmt = $db->query("SELECT COUNT(*) as total FROM orders WHERE status = 'pending'");
    $stats['pending_orders'] = (int)($stmt->fetch()['total'] ?? 0);
    
    // Customers count
    $stmt = $db->query("SELECT COUNT(*) as total FROM customers");
    $stats['total_customers'] = (int)($stmt->fetch()['total'] ?? 0);
    
    // Products count
    $stmt = $db->query("SELECT COUNT(*) as total FROM products");
    $stats['total_products'] = (int)($stmt->fetch()['total'] ?? 0);
    
    // Expenditure
    $stmt = $db->query("SELECT SUM(amount) as total FROM expenditure");
    $stats['total_expenditure'] = (float)($stmt->fetch()['total'] ?? 0);
    
    // Net Profit
    $stats['net_profit'] = $stats['revenue'] - $stats['total_expenditure'];
    
    // Low Stock Items
    $stmt = $db->query("SELECT id, name, stock FROM products WHERE stock < 10 ORDER BY stock ASC LIMIT 5");
    $stats['low_stock'] = $stmt->fetchAll();
    
    // Recent Expenditures
    $stmt = $db->query("SELECT id, category, amount, description, date FROM expenditure ORDER BY date DESC LIMIT 5");
    $stats['recent_expenditures'] = $stmt->fetchAll();
    
    // Top Performing Products
    $stmt = $db->query("SELECT p.id, p.name, SUM(oi.quantity) as total_sold, p.image_url 
                        FROM order_items oi 
                        JOIN products p ON oi.product_id = p.id 
                        JOIN orders o ON oi.order_id = o.id 
                        WHERE o.status != 'cancelled' 
                        GROUP BY p.id 
                        ORDER BY total_sold DESC 
                        LIMIT 5");
    $stats['top_performers'] = $stmt->fetchAll();
    
    // Top VIP Customers
    $stmt = $db->query("SELECT c.id, CONCAT(c.first_name, ' ', c.last_name) as name, c.email, COUNT(o.id) as order_count, SUM(o.total_amount) as total_spent 
                        FROM customers c 
                        JOIN orders o ON c.id = o.customer_id 
                        WHERE o.status != 'cancelled' 
                        GROUP BY c.id 
                        ORDER BY total_spent DESC 
                        LIMIT 5");
    $stats['top_customers'] = $stmt->fetchAll();
    
    // Recent Orders
    $stmt = $db->query("SELECT o.id, o.tracking_number, o.total_amount, o.status, o.created_at, CONCAT(c.first_name, ' ', c.last_name) as customer_name 
                        FROM orders o 
                        LEFT JOIN customers c ON o.customer_id = c.id 
                        ORDER BY o.created_at DESC LIMIT 5");
    $stats['recent_orders'] = $stmt->fetchAll();
    
    // Monthly Sales for the current year
    $stmt = $db->query("SELECT MONTH(created_at) as month, SUM(total_amount) as amount 
                        FROM orders 
                        WHERE status != 'cancelled' AND YEAR(created_at) = YEAR(CURDATE()) 
                        GROUP BY MONTH(created_at) 
                        ORDER BY month ASC");
    $monthlyData = $stmt->fetchAll();
    
    $months = array_fill(1, 12, 0);
    foreach ($monthlyData as $row) {
        $months[(int)$row['month']] = (float)$row['amount'];
    }
    $stats['monthly_sales'] = array_values($months);

    Helpers::jsonResponse(200, 'Dashboard data fetched', $stats);
} catch (\Exception $e) {
    Helpers::jsonResponse(500, 'Server error: ' . $e->getMessage());
}
