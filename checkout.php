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

$loggedInCustomer = null;
if (isset($_SESSION['customer_id'])) {
    $stmt = $db->prepare("SELECT first_name, last_name, email, phone, address FROM customers WHERE id = ?");
    $stmt->execute([$_SESSION['customer_id']]);
    $loggedInCustomer = $stmt->fetch();
}

$defaultFirstName = $loggedInCustomer['first_name'] ?? '';
$defaultLastName = $loggedInCustomer['last_name'] ?? '';
$defaultEmail = $loggedInCustomer['email'] ?? '';
$defaultPhone = $loggedInCustomer['phone'] ?? '';
$defaultAddress = $loggedInCustomer['address'] ?? '';

$total = 0;
$products = [];
$ids = array_keys($cartItems);
$inClause = implode(',', array_fill(0, count($ids), '?'));
$stmt = $db->prepare("SELECT id, name, price, stock, image_url FROM products WHERE id IN ($inClause)");
$stmt->execute($ids);
$productsData = $stmt->fetchAll();

foreach ($productsData as $p) {
    $qty = $cartItems[$p['id']];
    $total += ($qty * $p['price']);
    $products[] = ['id' => $p['id'], 'price' => $p['price'], 'qty' => $qty, 'name' => $p['name'], 'image_url' => $p['image_url']];
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
                'callback_url' => BASE_URL . 'verify_payment.php',
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

<div class="container-fluid px-4 px-lg-5 pt-5 mt-5">
    <div class="row pt-5 mb-5 pb-5 border-bottom border-light">
        <div class="col-lg-12 text-center">
            <h1 class="font-serif text-black" style="font-size: 3.5rem;">Checkout</h1>
        </div>
    </div>
</div>

<div class="container-fluid px-4 px-lg-5 mb-5 pb-5">
    <?php if ($error): ?>
        <div class="alert bg-off-white text-danger border border-danger mb-5 rounded-0 font-sans p-3">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>
    
    <div class="row">
        <div class="col-lg-7 pe-lg-5 border-end border-light mb-5 mb-lg-0">
            <h4 class="font-sans text-uppercase letter-spacing-widest text-black mb-4 fw-600" style="font-size: 0.75rem;">Delivery Information</h4>
            <form method="POST">
                <div class="row g-4 mb-5">
                    <div class="col-md-6">
                        <label class="form-label font-sans text-uppercase letter-spacing-wide text-muted" style="font-size: 0.65rem;">First Name</label>
                        <input type="text" name="first_name" class="form-control rounded-0 border-black p-3" value="<?php echo htmlspecialchars($defaultFirstName); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label font-sans text-uppercase letter-spacing-wide text-muted" style="font-size: 0.65rem;">Last Name</label>
                        <input type="text" name="last_name" class="form-control rounded-0 border-black p-3" value="<?php echo htmlspecialchars($defaultLastName); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label font-sans text-uppercase letter-spacing-wide text-muted" style="font-size: 0.65rem;">Email Address</label>
                        <input type="email" name="email" class="form-control rounded-0 border-black p-3" value="<?php echo htmlspecialchars($defaultEmail); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label font-sans text-uppercase letter-spacing-wide text-muted" style="font-size: 0.65rem;">Phone Number</label>
                        <input type="text" name="phone" class="form-control rounded-0 border-black p-3" value="<?php echo htmlspecialchars($defaultPhone); ?>" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label font-sans text-uppercase letter-spacing-wide text-muted" style="font-size: 0.65rem;">Delivery Address</label>
                        <textarea name="address" class="form-control rounded-0 border-black p-3" rows="3" required><?php echo htmlspecialchars($defaultAddress); ?></textarea>
                    </div>
                </div>

                <h4 class="font-sans text-uppercase letter-spacing-widest text-black mb-4 fw-600 border-top border-light pt-5" style="font-size: 0.75rem;">Payment</h4>
                <div class="p-4 border border-black mb-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <input class="form-check-input rounded-0 mt-0 border-black me-3" type="radio" checked disabled>
                            <span class="font-sans text-black fw-600" style="font-size: 0.85rem;">Paystack (Card / Mobile Money)</span>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-black w-100 py-3 mt-4">
                    Confirm & Pay GHS <?php echo number_format($total, 2); ?>
                </button>
            </form>
        </div>
        
        <div class="col-lg-5 ps-lg-5">
            <div class="sticky-top" style="top: 100px;">
                <h4 class="font-sans text-uppercase letter-spacing-widest text-black mb-4 fw-600" style="font-size: 0.75rem;">Order Summary</h4>
                
                <div class="mb-4">
                    <?php foreach ($products as $p): ?>
                        <div class="d-flex align-items-center justify-content-between mb-3 border-bottom border-light pb-3">
                            <div class="d-flex align-items-center">
                                <div class="bg-off-white me-3" style="width: 50px; height: 60px; padding: 0.25rem;">
                                    <img src="<?php echo $p['image_url'] ? htmlspecialchars($p['image_url']) : 'https://via.placeholder.com/100?text=O'; ?>" class="w-100 h-100" style="object-fit: contain;">
                                </div>
                                <div>
                                    <span class="font-serif text-black d-block mb-1" style="font-size: 0.95rem;"><?php echo htmlspecialchars($p['name']); ?></span>
                                    <span class="font-sans text-muted" style="font-size: 0.7rem;">Qty: <?php echo $p['qty']; ?></span>
                                </div>
                            </div>
                            <span class="font-sans text-black" style="font-size: 0.85rem;">GHS <?php echo number_format($p['qty'] * $p['price'], 2); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="d-flex justify-content-between mb-2 font-sans" style="font-size: 0.85rem;">
                    <span class="text-muted">Subtotal</span>
                    <span class="text-black">GHS <?php echo number_format($total, 2); ?></span>
                </div>
                <div class="d-flex justify-content-between mb-4 font-sans" style="font-size: 0.85rem;">
                    <span class="text-muted">Shipping</span>
                    <span class="text-muted italic">Calculated on Pay</span>
                </div>
                
                <div class="d-flex justify-content-between border-top border-black pt-3 font-sans fw-600 text-black">
                    <span class="text-uppercase letter-spacing-wide" style="font-size: 0.75rem;">Total</span>
                    <span>GHS <?php echo number_format($total, 2); ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
