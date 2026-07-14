<?php
require_once __DIR__ . '/../../src/Database.php';
require_once __DIR__ . '/../../src/Helpers.php';
require_once __DIR__ . '/../../src/Auth.php';

use App\Database;
use App\Helpers;
use App\Auth;

// Require admin for viewing invoices
Auth::requireAdmin();

$id = $_GET['id'] ?? null;
if (!$id) die('Order ID required');

try {
    $db = Database::getInstance()->getConnection();
    
    // Fetch Order
    $stmt = $db->prepare("SELECT o.*, CONCAT(c.first_name, ' ', c.last_name) as customer_name, c.email as customer_email, c.phone as customer_phone 
                        FROM orders o 
                        LEFT JOIN customers c ON o.customer_id = c.id 
                        WHERE o.id = ?");
    $stmt->execute([$id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$order) die('Order not found');
    
    // Fetch Items
    $stmt = $db->prepare("SELECT oi.*, p.name as product_name 
                        FROM order_items oi 
                        JOIN products p ON oi.product_id = p.id 
                        WHERE oi.order_id = ?");
    $stmt->execute([$id]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    die('Error: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - #<?php echo $order['tracking_number']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Google+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Google Sans', sans-serif;
            background-color: #f8f9fa;
            padding: 40px 0;
        }
        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
            padding: 50px;
            box-shadow: 0 0 20px rgba(0,0,0,0.05);
            border-radius: 8px;
        }
        .brand-logo {
            font-size: 28px;
            font-weight: 700;
            color: #1a73e8;
            letter-spacing: -0.5px;
        }
        .invoice-header {
            border-bottom: 2px solid #f1f3f4;
            padding-bottom: 30px;
            margin-bottom: 30px;
        }
        .info-label {
            color: #5f6368;
            font-size: 13px;
            font-weight: 500;
            text-transform: uppercase;
            margin-bottom: 4px;
        }
        .table thead th {
            background-color: #f8f9fa;
            border-bottom: 2px solid #f1f3f4;
            color: #5f6368;
            font-size: 13px;
            font-weight: 500;
        }
        .total-row {
            font-size: 18px;
            font-weight: 700;
            color: #1a73e8;
        }
        @media print {
            body {
                background-color: #fff;
                padding: 0;
            }
            .invoice-container {
                box-shadow: none;
                max-width: 100%;
                padding: 0;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>

<div class="container no-print mb-4 d-flex justify-content-center">
    <button onclick="window.print()" class="btn btn-primary px-4 py-2 d-flex align-items-center">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-printer me-2" viewBox="0 0 16 16">
          <path d="M2.5 8a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1"/>
          <path d="M5 1a2 2 0 0 0-2 2v2H2a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h1v1a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-1h1a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-1V3a2 2 0 0 0-2-2zM4 3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2H4zM3 13V8h10v5a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1m1-9h8V3H4z"/>
        </svg>
        Print Invoice
    </button>
</div>

<div class="invoice-container">
    <div class="invoice-header d-flex justify-content-between align-items-start">
        <div>
            <div class="brand-logo mb-2">Ohemaa Detergents</div>
            <p class="text-muted small mb-0">Premium Cleaning Solutions</p>
            <p class="text-muted small">Accra, Ghana | +233 555 000 000</p>
        </div>
        <div class="text-end">
            <h2 class="fw-bold mb-1">INVOICE</h2>
            <p class="mb-0">#<?php echo $order['tracking_number']; ?></p>
            <p class="text-muted small">Date: <?php echo date('d M Y', strtotime($order['created_at'])); ?></p>
        </div>
    </div>

    <div class="row mb-5">
        <div class="col-6">
            <div class="info-label">Bill To</div>
            <p class="fw-bold mb-1"><?php echo $order['customer_name']; ?></p>
            <p class="text-muted small mb-1"><?php echo $order['customer_email']; ?></p>
            <p class="text-muted small mb-1"><?php echo $order['customer_phone']; ?></p>
            <p class="text-muted small"><?php echo nl2br($order['shipping_address']); ?></p>
        </div>
        <div class="col-6 text-end">
            <div class="info-label">Payment Status</div>
            <span class="badge bg-success-subtle text-success p-2 px-3" style="text-transform: capitalize;">Paid via Paystack</span>
            <div class="info-label mt-3">Order Status</div>
            <span class="badge bg-primary-subtle text-primary p-2 px-3" style="text-transform: capitalize;"><?php echo $order['status']; ?></span>
        </div>
    </div>

    <table class="table mb-4">
        <thead>
            <tr>
                <th class="ps-0">Description</th>
                <th class="text-center">Quantity</th>
                <th class="text-end">Unit Price</th>
                <th class="text-end pe-0">Total</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $subtotal = 0;
            foreach ($items as $item): 
                $lineTotal = $item['quantity'] * $item['unit_price'];
                $subtotal += $lineTotal;
            ?>
            <tr>
                <td class="ps-0 py-3">
                    <div class="fw-bold"><?php echo htmlspecialchars($item['product_name']); ?></div>
                    <?php if (!empty($item['size_label'])): ?>
                    <div style="font-size:12px; color:#5f6368; margin-top:2px;">Size: <?php echo htmlspecialchars($item['size_label']); ?></div>
                    <?php endif; ?>
                </td>
                <td class="text-center py-3"><?php echo $item['quantity']; ?></td>
                <td class="text-end py-3">GHS <?php echo number_format($item['unit_price'], 2); ?></td>
                <td class="text-end py-3 pe-0">GHS <?php echo number_format($lineTotal, 2); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="row justify-content-end">
        <div class="col-5">
            <div class="d-flex justify-content-between mb-2">
                <span class="text-muted">Subtotal</span>
                <span>GHS <?php echo number_format($subtotal, 2); ?></span>
            </div>
            <?php if ($order['total_amount'] < $subtotal): ?>
            <div class="d-flex justify-content-between mb-2 text-danger">
                <span>Discount</span>
                <span>- GHS <?php echo number_format($subtotal - $order['total_amount'], 2); ?></span>
            </div>
            <?php endif; ?>
            <div class="d-flex justify-content-between pt-3 border-top total-row">
                <span>Total Amount</span>
                <span>GHS <?php echo number_format($order['total_amount'], 2); ?></span>
            </div>
        </div>
    </div>

    <div class="mt-5 pt-5 border-top text-center text-muted small">
        <p class="mb-1">Thank you for choosing Ohemaa Detergents!</p>
        <p>If you have any questions about this invoice, please contact us at support@ohemaadetergents.com</p>
    </div>
</div>

</body>
</html>
