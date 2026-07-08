<?php
require_once __DIR__ . '/src/Database.php';
require_once __DIR__ . '/src/Cart.php';

use App\Database;
use App\Cart;

$config = require __DIR__ . '/config/config.php';

$reference = $_GET['reference'] ?? '';
if (!$reference) {
    die('No reference supplied');
}

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://api.paystack.co/transaction/verify/" . rawurlencode($reference));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer " . $config['paystack']['secret_key']
]);
$response = curl_exec($ch);
curl_close($ch);

$result = json_decode($response, true);
$success = false;

if ($result && $result['status'] === true && $result['data']['status'] === 'success') {
    $db = Database::getInstance()->getConnection();
    
    // Check if it's already processing to prevent duplicate emails on refresh
    $stmt = $db->prepare("SELECT o.id as order_id, o.total_amount, o.status, c.email, c.first_name, c.last_name, o.shipping_address FROM orders o JOIN customers c ON o.customer_id = c.id WHERE o.tracking_number = ?");
    $stmt->execute([$reference]);
    $orderData = $stmt->fetch();
    
    if ($orderData && $orderData['status'] === 'pending') {
        $success = true;
        
        $stmt = $db->prepare("UPDATE orders SET status = 'processing' WHERE tracking_number = ?");
        $stmt->execute([$reference]);
        
        $cart = new Cart();
        $cart->clear();

        // Fetch order items for the email
        $stmt = $db->prepare("SELECT oi.quantity, oi.unit_price, p.name FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
        $stmt->execute([$orderData['order_id']]);
        $items = $stmt->fetchAll();

        // Send Order Confirmation Email
        require_once __DIR__ . '/includes/mailer.php';
        
        $subject = "Order Confirmation - {$reference}";
        $body = "
        <div style='font-family: sans-serif; max-width: 600px; margin: 0 auto; color: #000;'>
            <h2 style='font-family: serif; font-size: 24px; font-weight: normal; margin-bottom: 20px;'>Thank you for your order, {$orderData['first_name']}!</h2>
            <p style='font-size: 14px; font-weight: 300; line-height: 1.6; margin-bottom: 30px;'>
                We have received your order <strong>{$reference}</strong> and it is now being processed.
            </p>
            
            <h3 style='font-family: serif; font-size: 18px; margin-bottom: 15px;'>Order Details</h3>
            <table style='width: 100%; border-collapse: collapse; margin-bottom: 30px; font-size: 14px;'>
                <thead>
                    <tr style='border-bottom: 1px solid #000;'>
                        <th style='text-align: left; padding: 10px 0; font-weight: normal; text-transform: uppercase; font-size: 12px; letter-spacing: 1px;'>Item</th>
                        <th style='text-align: center; padding: 10px 0; font-weight: normal; text-transform: uppercase; font-size: 12px; letter-spacing: 1px;'>Qty</th>
                        <th style='text-align: right; padding: 10px 0; font-weight: normal; text-transform: uppercase; font-size: 12px; letter-spacing: 1px;'>Price</th>
                    </tr>
                </thead>
                <tbody>";
        
        foreach ($items as $item) {
            $priceFormatted = number_format($item['quantity'] * $item['unit_price'], 2);
            $body .= "
                    <tr style='border-bottom: 1px solid #eee;'>
                        <td style='padding: 15px 0;'>{$item['name']}</td>
                        <td style='text-align: center; padding: 15px 0;'>{$item['quantity']}</td>
                        <td style='text-align: right; padding: 15px 0;'>GHS {$priceFormatted}</td>
                    </tr>";
        }
        
        $totalFormatted = number_format($orderData['total_amount'], 2);
        $body .= "
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan='2' style='text-align: right; padding: 15px 0; font-weight: 600;'>Total:</td>
                        <td style='text-align: right; padding: 15px 0; font-weight: 600;'>GHS {$totalFormatted}</td>
                    </tr>
                </tfoot>
            </table>

            <h3 style='font-family: serif; font-size: 18px; margin-bottom: 15px;'>Delivery Address</h3>
            <p style='font-size: 14px; font-weight: 300; line-height: 1.6; margin-bottom: 30px;'>
                " . nl2br(htmlspecialchars($orderData['shipping_address'])) . "
            </p>
            <div style='text-align: center; margin-top: 30px;'>
                <a href='" . BASE_URL . "track_order?tracking_number=" . urlencode($reference) . "' style='display: inline-block; padding: 15px 30px; background-color: #000; color: #fff; text-decoration: none; text-transform: uppercase; letter-spacing: 2px; font-size: 12px; font-weight: 500;'>
                    Track Your Order
                </a>
            </div>
            <hr style='border: none; border-top: 1px solid #eee; margin: 40px 0;'>
            <p style='font-size: 11px; color: #666;'>If you have any questions, please reply to this email.</p>
        </div>";

        sendMail($orderData['email'], $subject, $body);
    } elseif ($orderData && $orderData['status'] !== 'pending') {
        // Already processed
        $success = true;
    }
}

include 'includes/header.php';
?>

<section class="py-5 bg-off-white" style="min-height: 80vh; display: flex; align-items: center;">
    <div class="container px-4">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6 bg-white p-5 border border-light text-center">
                <?php if ($success): ?>
                    <i class="bi bi-check-circle mb-4 text-black" style="font-size: 3rem;"></i>
                    <h2 class="font-serif mb-3" style="font-size: 2.5rem;">Payment Successful</h2>
                    <p class="font-sans text-muted mb-4" style="line-height: 1.8;">Thank you for your order. We are processing it right away and an email confirmation has been sent to you.</p>
                    
                    <div class="p-4 bg-off-white mb-5">
                        <p class="font-sans text-uppercase letter-spacing-wide text-muted mb-2" style="font-size: 0.75rem;">Your Tracking Number</p>
                        <p class="font-sans fw-600 text-black mb-0" style="font-size: 1.25rem; letter-spacing: 2px;"><?php echo htmlspecialchars($reference); ?></p>
                    </div>

                    <div class="d-flex flex-column flex-sm-row justify-content-center gap-3">
                        <a href="track_order?tracking_number=<?php echo urlencode($reference); ?>" class="btn btn-dark rounded-0 px-5 py-3 font-sans text-uppercase letter-spacing-wide" style="font-size: 0.8rem;">Track Order</a>
                        <a href="index" class="btn btn-outline-dark rounded-0 px-5 py-3 font-sans text-uppercase letter-spacing-wide" style="font-size: 0.8rem;">Back to Home</a>
                    </div>
                <?php else: ?>
                    <i class="bi bi-x-circle mb-4 text-danger" style="font-size: 3rem;"></i>
                    <h2 class="font-serif mb-3" style="font-size: 2.5rem;">Payment Failed</h2>
                    <p class="font-sans text-muted mb-5" style="line-height: 1.8;">We could not verify your payment. Please try again or contact support.</p>
                    
                    <a href="cart" class="btn btn-dark rounded-0 px-5 py-3 font-sans text-uppercase letter-spacing-wide" style="font-size: 0.8rem;">Return to Bag</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
