<?php
require_once __DIR__ . '/src/Database.php';
require_once __DIR__ . '/src/Cart.php';
require_once __DIR__ . '/src/Helpers.php';

use App\Database;
use App\Cart;
use App\Helpers;

$config = require __DIR__ . '/config/config.php';
$db = Database::getInstance()->getConnection();
$cart = new Cart();
$cartItems = $cart->getItems();

if (empty($cartItems)) {
    header("Location: cart.php");
    exit;
}

$total = 0;
$products = [];
$ids = array_keys($cartItems);
$inClause = implode(',', array_fill(0, count($ids), '?'));
$stmt = $db->prepare("SELECT id, name, price, stock FROM products WHERE id IN ($inClause)");
$stmt->execute($ids);
$productsData = $stmt->fetchAll();

foreach ($productsData as $p) {
    $qty = $cartItems[$p['id']];
    $total += ($qty * $p['price']);
    $products[] = ['id' => $p['id'], 'price' => $p['price'], 'qty' => $qty, 'name' => $p['name']];
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if (empty($firstName) || empty($lastName) || empty($email) || empty($address)) {
        $error = 'Please fill in all required fields.';
    } else {
        try {
            $db->beginTransaction();

            $stmt = $db->prepare("SELECT id FROM customers WHERE email = ?");
            $stmt->execute([$email]);
            $customer = $stmt->fetch();

            if ($customer) {
                $customerId = $customer['id'];
            } else {
                $customerUuid = Helpers::generateUuidV7Binary();
                $randomPassword = bin2hex(random_bytes(8));
                $hash = password_hash($randomPassword, PASSWORD_DEFAULT);
                $stmt = $db->prepare("INSERT INTO customers (customer_id, first_name, last_name, email, phone, password_hash) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$customerUuid, $firstName, $lastName, $email, $phone, $hash]);
                $customerId = $db->lastInsertId();
            }

            $orderUuid = Helpers::generateUuidV7Binary();
            $trackingNumber = 'ORD-' . strtoupper(substr(uniqid(), -6));
            
            $stmt = $db->prepare("INSERT INTO orders (order_id, tracking_number, customer_id, total_amount, shipping_address, status) VALUES (?, ?, ?, ?, ?, 'pending')");
            $stmt->execute([$orderUuid, $trackingNumber, $customerId, $total, $address]);
            $orderId = $db->lastInsertId();

            $stmt = $db->prepare("INSERT INTO order_items (order_item_id, order_id, product_id, quantity, unit_price) VALUES (?, ?, ?, ?, ?)");
            foreach ($products as $p) {
                $stmt->execute([Helpers::generateUuidV7Binary(), $orderId, $p['id'], $p['qty'], $p['price']]);
            }

            $db->commit();

            $amountInPesewas = $total * 100; 
            
            $postData = [
                'email' => $email,
                'amount' => $amountInPesewas,
                'reference' => $trackingNumber,
                'callback_url' => 'http://' . $_SERVER['HTTP_HOST'] . '/ohemaadetergents/verify_payment.php',
                'metadata' => ['custom_fields' => [['display_name' => "Order ID", 'variable_name' => "order_id", 'value' => $trackingNumber]]]
            ];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://api.paystack.co/transaction/initialize");
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "Authorization: Bearer " . $config['paystack']['secret_key'],
                "Content-Type: application/json"
            ]);
            
            $response = curl_exec($ch);
            curl_close($ch);
            
            $result = json_decode($response, true);
            
            if ($result && $result['status'] === true) {
                header("Location: " . $result['data']['authorization_url']);
                exit;
            } else {
                $error = 'Payment initialization failed. Ensure Paystack API keys are set. Error: ' . ($result['message'] ?? 'Unknown error');
            }
            
        } catch (\Exception $e) {
            if ($db->inTransaction()) $db->rollBack();
            $error = 'Checkout failed: ' . $e->getMessage();
        }
    }
}

include 'includes/header.php';
?>
<div class="container py-5">
    <h2 class="mb-4">Checkout</h2>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <div class="row">
        <div class="col-lg-8 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h4 class="mb-4">Delivery Details</h4>
                    <form method="POST">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">First Name *</label>
                                <input type="text" name="first_name" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Last Name *</label>
                                <input type="text" name="last_name" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email *</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Phone Number *</label>
                                <input type="text" name="phone" class="form-control" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Delivery Address *</label>
                                <textarea name="address" class="form-control" rows="3" required></textarea>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-gold mt-4 w-100 py-3 rounded-pill">Pay GHS <?php echo number_format($total, 2); ?> via Paystack</button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card border-0 bg-light">
                <div class="card-body p-4">
                    <h4 class="mb-4">Order Summary</h4>
                    <?php foreach ($products as $p): ?>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-truncate me-3"><?php echo htmlspecialchars($p['name']); ?> (x<?php echo $p['qty']; ?>)</span>
                            <span>GHS <?php echo number_format($p['qty'] * $p['price'], 2); ?></span>
                        </div>
                    <?php endforeach; ?>
                    <hr>
                    <div class="d-flex justify-content-between fs-5 fw-bold" style="color: var(--ohemaa-blue);">
                        <span>Total</span>
                        <span>GHS <?php echo number_format($total, 2); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
