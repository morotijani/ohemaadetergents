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
    header("Location: cart");
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
include 'includes/header.php';
?>

<div class="bg-gold-soft py-5 mt-5">
    <div class="container py-4 text-center">
        <h1 class="display-4 fw-800 mb-0">Secure Checkout</h1>
    </div>
</div>

<div class="container py-5 mb-5">
    <?php if ($error): ?>
        <div class="alert alert-danger border-0 shadow-sm rounded-lg mb-4 p-3 d-flex align-items-center">
            <i class="bi bi-exclamation-triangle-fill me-3 fs-4"></i>
            <div><?php echo htmlspecialchars($error); ?></div>
        </div>
    <?php endif; ?>
    
    <div class="row g-5">
        <div class="col-lg-8 reveal">
            <div class="glass p-5 rounded-lg border-0 shadow-sm" style="border-radius: var(--radius-lg);">
                <h4 class="fw-bold mb-4 d-flex align-items-center">
                    <span class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 32px; height: 32px; font-size: 0.9rem;">1</span>
                    Delivery Information
                </h4>
                <form method="POST">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-uppercase opacity-75">First Name *</label>
                            <input type="text" name="first_name" class="form-control glass border-0 p-3" required style="box-shadow: none;">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-uppercase opacity-75">Last Name *</label>
                            <input type="text" name="last_name" class="form-control glass border-0 p-3" required style="box-shadow: none;">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-uppercase opacity-75">Email Address *</label>
                            <input type="email" name="email" class="form-control glass border-0 p-3" required style="box-shadow: none;">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-uppercase opacity-75">Phone Number *</label>
                            <input type="text" name="phone" class="form-control glass border-0 p-3" required style="box-shadow: none;">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold text-uppercase opacity-75">Delivery Address *</label>
                            <textarea name="address" class="form-control glass border-0 p-3" rows="3" required style="box-shadow: none;" placeholder="House Number, Street Name, City, Region"></textarea>
                        </div>
                    </div>

                    <div class="mt-5">
                        <h4 class="fw-bold mb-4 d-flex align-items-center">
                            <span class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 32px; height: 32px; font-size: 0.9rem;">2</span>
                            Payment Method
                        </h4>
                        <div class="p-4 border rounded-lg bg-light d-flex align-items-center justify-content-between mb-4">
                            <div class="d-flex align-items-center">
                                <div class="form-check me-3">
                                    <input class="form-check-input" type="radio" checked disabled>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-1">Secure Online Payment</h6>
                                    <p class="small text-muted mb-0">Pay via Mobile Money or Card</p>
                                </div>
                            </div>
                            <img src="https://upload.wikimedia.org/wikipedia/commons/b/ba/Paystack_Logo.png" height="25" alt="Paystack">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-gold w-100 py-3 rounded-pill fs-5 mt-4">
                        Confirm & Pay GHS <?php echo number_format($total, 2); ?>
                    </button>
                    
                    <p class="text-center mt-4 small text-muted">
                        By clicking "Confirm & Pay", you agree to our terms of service and shipping policies.
                    </p>
                </form>
            </div>
        </div>
        
        <!-- Order Summary Sticky -->
        <div class="col-lg-4 reveal" style="animation-delay: 0.2s;">
            <div class="glass p-4 rounded-lg sticky-top" style="top: 100px; border-radius: var(--radius-lg);">
                <h4 class="fw-bold mb-4">Order Summary</h4>
                
                <div class="mb-4">
                    <?php foreach ($products as $p): ?>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="d-flex align-items-center">
                                <div class="bg-white rounded-md border p-1 me-3 position-relative" style="width: 50px; height: 50px;">
                                    <img src="https://via.placeholder.com/100?text=O" class="w-100 h-100 object-fit-cover rounded-sm opacity-50">
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-primary" style="font-size: 0.6rem;"><?php echo $p['qty']; ?></span>
                                </div>
                                <span class="small fw-bold text-truncate" style="max-width: 120px;"><?php echo htmlspecialchars($p['name']); ?></span>
                            </div>
                            <span class="small fw-bold">GHS <?php echo number_format($p['qty'] * $p['price'], 2); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <hr class="my-4 opacity-10">
                
                <div class="d-flex justify-content-between mb-2 small text-muted">
                    <span>Subtotal</span>
                    <span>GHS <?php echo number_format($total, 2); ?></span>
                </div>
                <div class="d-flex justify-content-between mb-4 small text-muted">
                    <span>Shipping</span>
                    <span class="text-success fw-bold">Calculated on Pay</span>
                </div>
                
                <div class="d-flex justify-content-between fs-5 fw-800 text-primary border-top pt-3">
                    <span>Total Amount</span>
                    <span>GHS <?php echo number_format($total, 2); ?></span>
                </div>

                <div class="mt-5 p-3 rounded-md bg-gold-soft border border-gold border-opacity-25 text-center">
                    <p class="small mb-0 text-muted">
                        <i class="bi bi-shield-check text-gold me-2"></i>
                        Encrypted and secure transaction.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
