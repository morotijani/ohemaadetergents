<?php
require_once __DIR__ . '/../../src/Database.php';
require_once __DIR__ . '/../../src/Helpers.php';
require_once __DIR__ . '/../../src/Auth.php';

use App\Database;
use App\Helpers;
use App\Auth;

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;
if ($_SERVER['REQUEST_METHOD'] !== 'POST') Helpers::jsonResponse(405, 'Method Not Allowed');

$admin = Auth::requireAdmin();
$input = json_decode(file_get_contents('php://input'), true);

$orderId = (int)($input['id'] ?? 0);
$status = trim($input['status'] ?? '');

$validStatuses = ['pending', 'processing', 'completed', 'cancelled'];

if (!$orderId || !in_array($status, $validStatuses)) {
    Helpers::jsonResponse(400, 'Invalid order ID or status');
}

try {
    $db = Database::getInstance()->getConnection();
    
    // First, fetch order and customer details
    $stmt = $db->prepare("SELECT o.tracking_number, o.status as old_status, c.email, c.first_name 
                          FROM orders o 
                          JOIN customers c ON o.customer_id = c.id 
                          WHERE o.id = ?");
    $stmt->execute([$orderId]);
    $orderData = $stmt->fetch();

    if (!$orderData) {
        Helpers::jsonResponse(404, 'Order not found');
    }

    $stmt = $db->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->execute([$status, $orderId]);
    
    if ($stmt->rowCount() > 0 || $orderData['old_status'] !== $status) {
        Helpers::logAction($db, 'update_order_status', "Updated order ID: $orderId to $status", $admin['admin_id']);
        
        // Send email if status is completed or cancelled
        if (in_array($status, ['completed', 'cancelled'])) {
            require_once __DIR__ . '/../../includes/mailer.php';
            $subject = "Order " . ucfirst($status) . " - " . $orderData['tracking_number'];
            $body = "
            <div style='font-family: sans-serif; max-width: 600px; margin: 0 auto; color: #000;'>
                <h2 style='font-family: serif; font-size: 24px; font-weight: normal; margin-bottom: 20px;'>Hello {$orderData['first_name']},</h2>
                <p style='font-size: 14px; font-weight: 300; line-height: 1.6; margin-bottom: 30px;'>
                    The status of your order <strong>{$orderData['tracking_number']}</strong> has been updated to: <strong style='text-transform: uppercase;'>" . ucfirst($status) . "</strong>.
                </p>";
                
            if ($status === 'completed') {
                $body .= "<p style='font-size: 14px; font-weight: 300; line-height: 1.6; margin-bottom: 30px;'>Thank you for shopping with Ohemaa Detergents. We hope you enjoy your premium products.</p>";
            } elseif ($status === 'cancelled') {
                $body .= "<p style='font-size: 14px; font-weight: 300; line-height: 1.6; margin-bottom: 30px;'>If you have any questions regarding this cancellation, please reply to this email to reach our support team.</p>";
            }

            $body .= "
                <hr style='border: none; border-top: 1px solid #eee; margin: 40px 0;'>
                <p style='font-size: 11px; color: #666;'>Ohemaa Detergents</p>
            </div>";
            
            sendMail($orderData['email'], $subject, $body);
        }

        Helpers::jsonResponse(200, 'Order status updated');
    } else {
        Helpers::jsonResponse(404, 'Order not found');
    }
} catch (\Exception $e) {
    Helpers::jsonResponse(500, 'Server error');
}
