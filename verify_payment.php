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
    $success = true;
    
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("UPDATE orders SET status = 'processing' WHERE tracking_number = ?");
    $stmt->execute([$reference]);
    
    $cart = new Cart();
    $cart->clear();
}

include 'includes/header.php';
?>
<div class="container py-5 text-center">
    <?php if ($success): ?>
        <i class="bi bi-check-circle-fill text-success" style="font-size: 5rem;"></i>
        <h2 class="mt-4">Payment Successful!</h2>
        <p class="lead">Thank you for your order. We are processing it right away.</p>
        <p class="fs-5 mt-3">Your Tracking Number is: <strong class="text-primary"><?php echo htmlspecialchars($reference); ?></strong></p>
        <p>You can use this number to track your order status.</p>
        <a href="track_order.php" class="btn btn-outline-gold mt-4 rounded-pill px-4">Track Order</a>
        <a href="index.php" class="btn btn-gold mt-4 rounded-pill px-4 ms-2">Back to Home</a>
    <?php else: ?>
        <i class="bi bi-x-circle-fill text-danger" style="font-size: 5rem;"></i>
        <h2 class="mt-4">Payment Failed</h2>
        <p class="lead">We could not verify your payment. Please try again or contact support.</p>
        <a href="cart.php" class="btn btn-gold mt-4 rounded-pill px-4">Return to Cart</a>
    <?php endif; ?>
</div>
<?php include 'includes/footer.php'; ?>
