<?php
require_once __DIR__ . '/src/Database.php';
require_once __DIR__ . '/src/Cart.php';

use App\Database;
use App\Cart;

$config = require __DIR__ . '/config/config.php';
if (!defined('BASE_URL')) {
    define('BASE_URL', rtrim($config['app']['url'], '/') . '/');
}

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
    $stmt = $db->prepare("SELECT o.id as order_id, o.total_amount, o.status, c.email, c.first_name, c.last_name, o.shipping_address, o.town_area, o.region FROM orders o JOIN customers c ON o.customer_id = c.id WHERE o.tracking_number = ?");
    $stmt->execute([$reference]);
    $orderData = $stmt->fetch();
    
    if ($orderData) {
        // Always fetch items so we can display them on the page
        $stmt = $db->prepare("SELECT oi.product_id, oi.size_id, oi.quantity, oi.unit_price, oi.size_label, p.name FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
        $stmt->execute([$orderData['order_id']]);
        $items = $stmt->fetchAll();

        if ($orderData['status'] === 'pending') {
            $success = true;
            
            $stmt = $db->prepare("UPDATE orders SET status = 'processing' WHERE tracking_number = ?");
            $stmt->execute([$reference]);

            // ---- Reduce stock after confirmed payment ----
            $affectedProductIds = [];
            foreach ($items as $itm) {
                if (!empty($itm['size_id'])) {
                    // Sized product: reduce the specific size stock
                    $db->prepare("UPDATE product_sizes SET stock = GREATEST(0, stock - ?) WHERE id = ?")
                       ->execute([$itm['quantity'], $itm['size_id']]);
                    // Track product for re-sync below
                    $affectedProductIds[] = $itm['product_id'];
                } else {
                    // Simple product: reduce product stock directly
                    $db->prepare("UPDATE products SET stock = GREATEST(0, stock - ?) WHERE id = ?")
                       ->execute([$itm['quantity'], $itm['product_id']]);
                }
            }
            // Re-sync parent product stock from sum of size stocks
            foreach (array_unique($affectedProductIds) as $pid) {
                $db->prepare("UPDATE products SET stock = (SELECT COALESCE(SUM(stock), 0) FROM product_sizes WHERE product_id = ?) WHERE id = ?")
                   ->execute([$pid, $pid]);
            }
            // ---- End stock reduction ----

            $cart = new Cart();
            $cart->clear();

        // Send Order Confirmation Email
        require_once __DIR__ . '/includes/mailer.php';
        
        $subject = "Order Confirmation - {$reference}";
        $body = "
        <div style='font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, Helvetica, Arial, sans-serif; max-width: 600px; margin: 0 auto; background-color: #fdfbf7; padding: 40px 20px;'>
            <div style='text-align: center; margin-bottom: 30px;'>
                <h1 style='font-size: 24px; font-weight: 800; letter-spacing: 2px; margin: 0; color: #111;'>OHEMAA DETERGENTS</h1>
            </div>
            
            <div style='background-color: #ffffff; padding: 40px; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.03);'>
                <h2 style='font-size: 22px; font-weight: 600; margin-top: 0; margin-bottom: 12px; color: #111;'>Order Confirmed!</h2>
                <p style='font-size: 15px; color: #555; line-height: 1.6; margin-bottom: 30px;'>
                    Hi {$orderData['first_name']}, thank you for your purchase. We are getting your order ready for delivery.
                </p>
                
                <div style='background: #fdfbf7; padding: 20px; border-radius: 8px; margin-bottom: 30px; text-align: center;'>
                    <div style='font-size: 12px; text-transform: uppercase; letter-spacing: 1px; color: #888; margin-bottom: 5px;'>Tracking Number</div>
                    <div style='font-size: 20px; font-weight: 700; color: #111; letter-spacing: 2px;'>{$reference}</div>
                </div>
                
                <h3 style='font-size: 13px; text-transform: uppercase; letter-spacing: 1px; color: #888; border-bottom: 2px solid #f0f0f0; padding-bottom: 10px; margin-bottom: 20px;'>Order Summary</h3>
                <table style='width: 100%; border-collapse: collapse; margin-bottom: 30px; font-size: 14px;'>
                    <tbody>";
        
        foreach ($items as $item) {
            $priceFormatted = number_format($item['quantity'] * $item['unit_price'], 2);
            $sizeHtml = !empty($item['size_label']) ? "<br><span style='color:#aaa; font-size:12px;'>Size: {$item['size_label']}</span>" : '';
            $body .= "
                    <tr>
                        <td style='padding: 12px 0; color: #333; border-bottom: 1px solid #f5f5f5;'>
                            <strong>{$item['name']}</strong>{$sizeHtml}<br>
                            <span style='color: #888; font-size: 13px;'>Qty: {$item['quantity']}</span>
                        </td>
                        <td style='text-align: right; padding: 12px 0; color: #111; border-bottom: 1px solid #f5f5f5;'>GHS {$priceFormatted}</td>
                    </tr>";
        }
        
        $totalFormatted = number_format($orderData['total_amount'], 2);
        $body .= "
                    </tbody>
                    <tfoot>
                        <tr>
                            <td style='text-align: left; padding: 20px 0 0 0; font-weight: 700; font-size: 16px; color: #111;'>Total</td>
                            <td style='text-align: right; padding: 20px 0 0 0; font-weight: 700; font-size: 16px; color: #111;'>GHS {$totalFormatted}</td>
                        </tr>
                        <tr>
                            <td colspan='2' style='text-align: right; padding-top: 5px; font-size: 12px; color: #888; font-style: italic;'>(Delivery fee will be calculated upon arrival)</td>
                        </tr>
                    </tfoot>
                </table>

                <h3 style='font-size: 13px; text-transform: uppercase; letter-spacing: 1px; color: #888; border-bottom: 2px solid #f0f0f0; padding-bottom: 10px; margin-bottom: 15px;'>Delivery Address</h3>
                <p style='font-size: 14px; color: #555; line-height: 1.6; margin-bottom: 30px;'>
                    " . nl2br(htmlspecialchars($orderData['shipping_address'])) . "<br>
                    " . htmlspecialchars($orderData['town_area']) . ", " . htmlspecialchars($orderData['region']) . "
                </p>
                
                <div style='text-align: center; margin-top: 40px;'>
                    <a href='" . BASE_URL . "track_order?tracking_number=" . urlencode($reference) . "' style='display: inline-block; padding: 16px 32px; background-color: #111; color: #fff; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 14px; letter-spacing: 1px;'>TRACK ORDER</a>
                </div>
            </div>
            
            <div style='text-align: center; margin-top: 30px; font-size: 12px; color: #999;'>
                <p>If you have any questions, please reply directly to this email.</p>
                <p>&copy; " . date('Y') . " Ohemaa Detergents. All rights reserved.</p>
            </div>
        </div>";

        sendMail($orderData['email'], $subject, $body);
        } elseif ($orderData['status'] !== 'pending') {
            // Already processed
            $success = true;
        }
    }
}

include 'includes/header.php';
?>

<section style="padding-top: 80px; padding-bottom: 100px;">
    <div class="wrap" style="display: flex; justify-content: center;">
        <div class="summary-card reveal" style="max-width: 550px; width: 100%; text-align: center; padding: 50px 40px;">
            <?php if ($success): ?>
                <div style="font-size: 4rem; color: #111; margin-bottom: 15px; line-height: 1;">✓</div>
                <h2 style="font-size: 2.2rem; font-weight: 700; margin-bottom: 16px;">Payment Successful</h2>
                <p style="color: var(--text); margin-bottom: 35px; font-size: 1.05rem; line-height: 1.6;">Thank you for your order! We are processing it right away and an email confirmation has been sent to you.</p>
                
                <div style="background: var(--bg); padding: 24px; border-radius: 12px; margin-bottom: 24px;">
                    <div style="font-size: 0.8rem; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; color: var(--text); margin-bottom: 8px;">Your Tracking Number</div>
                    <div style="font-size: 1.6rem; font-weight: 700; color: var(--ink); letter-spacing: 2px;"><?php echo htmlspecialchars($reference); ?></div>
                </div>

                <!-- Order Details -->
                <div style="text-align: left; background: #fff; padding: 24px; border-radius: 12px; border: 1px solid var(--line); margin-bottom: 24px;">
                    <h3 style="font-size: 1.1rem; margin-bottom: 16px; border-bottom: 1px solid var(--line); padding-bottom: 12px;">Order Summary</h3>
                    
                    <div style="margin-bottom: 16px;">
                        <?php foreach($items as $item): ?>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 0.95rem;">
                            <span>
                                <?php echo htmlspecialchars($item['name']); ?>
                                <?php if (!empty($item['size_label'])): ?>
                                <span style="color: #888; font-size: 0.8rem; display:block;">Size: <?php echo htmlspecialchars($item['size_label']); ?></span>
                                <?php endif; ?>
                                <span style="color: #888; font-size: 0.85rem;">×<?php echo $item['quantity']; ?></span>
                            </span>
                            <span>GH₵ <?php echo number_format($item['quantity'] * $item['unit_price'], 2); ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div style="display: flex; justify-content: space-between; font-weight: 700; font-size: 1.1rem; border-top: 1px solid var(--line); padding-top: 12px;">
                        <span>Total</span>
                        <span>GH₵ <?php echo number_format($orderData['total_amount'], 2); ?></span>
                    </div>
                </div>

                <!-- Delivery Details -->
                <div style="text-align: left; background: #fff; padding: 24px; border-radius: 12px; border: 1px solid var(--line); margin-bottom: 40px;">
                    <h3 style="font-size: 1.1rem; margin-bottom: 12px;">Delivery Address</h3>
                    <p style="color: var(--text); font-size: 0.95rem; line-height: 1.6; margin: 0;">
                        <?php echo nl2br(htmlspecialchars($orderData['shipping_address'])); ?><br>
                        <?php echo htmlspecialchars($orderData['town_area']); ?>, <?php echo htmlspecialchars($orderData['region']); ?>
                    </p>
                </div>

                <div style="display: flex; flex-direction: column; gap: 12px;">
                    <a href="track_order?tracking_number=<?php echo urlencode($reference); ?>" class="btn btn-dark btn-full" style="padding: 16px;">Track Order</a>
                    <a href="shop" class="btn btn-full" style="background: transparent; color: var(--ink); border: 1.5px solid var(--line); padding: 16px;">Continue Shopping</a>
                </div>
            <?php else: ?>
                <div style="font-size: 4rem; color: #d00; margin-bottom: 15px; line-height: 1;">✗</div>
                <h2 style="font-size: 2.2rem; font-weight: 700; margin-bottom: 16px;">Payment Failed</h2>
                <p style="color: var(--text); margin-bottom: 35px; font-size: 1.05rem; line-height: 1.6;">We could not verify your payment. Please try again or contact support.</p>
                
                <a href="cart" class="btn btn-dark btn-full" style="padding: 16px;">Return to Cart</a>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
