<?php
require_once __DIR__ . '/src/Database.php';
require_once __DIR__ . '/src/Cart.php';
require_once __DIR__ . '/src/Helpers.php';

use App\Database;
use App\Cart;
use App\Helpers;

$config = require __DIR__ . '/config/config.php';
if (!defined('BASE_URL')) {
    define('BASE_URL', rtrim($config['app']['url'], '/') . '/');
}
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

$deliveryFee = 0; // Will be calculated later
$grandTotal = $total;

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');

    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $townArea = trim($_POST['town_area'] ?? '');
    $region = trim($_POST['region'] ?? '');
    $deliveryNote = trim($_POST['delivery_note'] ?? '');

    if (empty($firstName) || empty($email) || empty($address)) {
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
            
            $stmt = $db->prepare("INSERT INTO orders (order_id, tracking_number, customer_id, total_amount, shipping_address, town_area, region, delivery_note, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
            $stmt->execute([$orderUuid, $trackingNumber, $customerId, $grandTotal, $address, $townArea, $region, $deliveryNote]);
            $orderId = $db->lastInsertId();

            $stmt = $db->prepare("INSERT INTO order_items (order_item_id, order_id, product_id, quantity, unit_price) VALUES (?, ?, ?, ?, ?)");
            foreach ($products as $p) {
                $stmt->execute([Helpers::generateUuidV7Binary(), $orderId, $p['id'], $p['qty'], $p['price']]);
            }

            // DB commit moved to after successful Paystack initialization
            
            $amountInPesewas = $grandTotal * 100; 
            
            $postData = [
                'email' => $email,
                'amount' => $amountInPesewas,
                'currency' => 'GHS',
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
                $db->commit(); // Only commit if Paystack successfully initialized
                $accessCode = $result['data']['access_code'];
                $reference = $result['data']['reference'] ?? $trackingNumber;
                $publicKey = $config['paystack']['public_key'];
                $verifyUrl = BASE_URL . 'verify_payment.php?reference=';
                echo "<!DOCTYPE html><html><head><title>Processing Payment...</title>";
                echo "<script src='https://js.paystack.co/v1/inline.js'></script>";
                echo "<style>body{background:#fdfbf7; display:flex; justify-content:center; align-items:center; height:100vh; margin:0; font-family:sans-serif;} .loader{border:4px solid #f3f3f3; border-top:4px solid #000; border-radius:50%; width:40px; height:40px; animation:spin 1s linear infinite;} @keyframes spin {0%{transform:rotate(0deg);} 100%{transform:rotate(360deg);}}</style>";
                echo "</head><body>";
                echo "<div style='text-align:center;'><div class='loader' style='margin:0 auto 20px auto;'></div><h2>Securely opening Paystack...</h2><p>Please do not refresh the page.</p></div>";
                echo "<script>
                    document.addEventListener('DOMContentLoaded', function() {
                        var handler = PaystackPop.setup({
                            key: '{$publicKey}',
                            access_code: '{$accessCode}',
                            email: '{$email}',
                            amount: {$amountInPesewas},
                            currency: 'GHS',
                            ref: '{$reference}',
                            callback: function(response) {
                                window.location.href = '{$verifyUrl}' + response.reference;
                            },
                            onClose: function() {
                                // User closed the popup without paying. Delete the pending order.
                                fetch('" . BASE_URL . "cancel_order.php', {
                                    method: 'POST',
                                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                                    body: 'reference=' + encodeURIComponent('{$trackingNumber}')
                                }).then(() => {
                                    window.location.href = '" . BASE_URL . "checkout';
                                });
                            }
                        });
                        handler.openIframe();
                    });
                </script>";
                echo "</body></html>";
                exit;
            } else {
                $db->rollBack(); // Rollback if Paystack failed to initialize
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
<section style="padding-top:44px;">
  <div class="wrap">
    <div class="checkout-steps reveal">
      <span class="step done">✓ Cart</span>
      <span class="sep">—</span>
      <span class="step current">② Checkout</span>
      <span class="sep">—</span>
      <span class="step">③ Confirmation</span>
    </div>

    <form method="POST"><div class="checkout-layout">

      
      <?php if ($error): ?>
        <div style="padding: 15px; background: #fee; border-left: 4px solid #c00; margin-bottom: 20px; font-size: 0.9rem; color: #c00;">
            <?php echo htmlspecialchars($error); ?>
        </div>
      <?php endif; ?>
      <div class="reveal">

        <div class="checkout-block">
          <h3>Contact information</h3>
          <div class="field-row">
            <div class="field">
              <label for="coFirstName">First name</label>
              <input id="coFirstName" name="first_name" type="text" value="<?php echo htmlspecialchars($defaultFirstName); ?>" required>
            </div>
            <div class="field">
              <label for="coLastName">Last name</label>
              <input id="coLastName" name="last_name" type="text" value="<?php echo htmlspecialchars($defaultLastName); ?>" required>
            </div>
          </div>
          <div class="field-row">
            <div class="field">
              <label for="coPhone">Phone number</label>
              <input id="coPhone" name="phone" type="tel" value="<?php echo htmlspecialchars($defaultPhone); ?>" required>
            </div>
            <div class="field">
              <label for="coEmail">Email</label>
              <input id="coEmail" name="email" type="email" value="<?php echo htmlspecialchars($defaultEmail); ?>" required>
            </div>
          </div>
        </div>

        <div class="checkout-block">
          <h3>Delivery address</h3>
          <div class="field">
            <label for="coAddr">Street address</label>
            <input id="coAddr" name="address" type="text" value="<?php echo htmlspecialchars($defaultAddress); ?>" placeholder="e.g. 12 Bantama High Street" required>
          </div>
          <div class="field-row">
            <div class="field">
              <label for="coCity">Town / area</label>
              <input id="coCity" name="town_area" type="text" value="Bantama, Kumasi">
            </div>
            <div class="field">
              <label for="coRegion">Region</label>
              <select id="coRegion" name="region">
                <option selected>Ashanti</option>
                <option>Greater Accra</option>
                <option>Eastern</option>
                <option>Central</option>
              </select>
            </div>
          </div>
          <div class="field">
            <label for="coNote">Delivery note (optional)</label>
            <input id="coNote" name="delivery_note" type="text" placeholder="e.g. Gate code, nearby landmark">
          </div>
        </div>

        <div class="checkout-block">
          <h3>Payment method</h3>
          <div class="payment-option active" onclick="">
            <div class="radio-dot"></div>
            <input type="radio" name="payment" style="display:none;" checked>
            <div><div class="pm-name">Mobile Money</div><div class="pm-desc">Pay instantly with MTN, Vodafone, or AirtelTigo</div></div>
          </div>
          <div class="payment-option">
            <div class="radio-dot"></div>
            <input type="radio" name="payment" style="display:none;">
            <div><div class="pm-name">Debit / Credit Card</div><div class="pm-desc">Visa, Mastercard accepted</div></div>
          </div>
          <div class="payment-option">
            <div class="radio-dot"></div>
            <input type="radio" name="payment" style="display:none;">
            <div><div class="pm-name">Cash on Delivery</div><div class="pm-desc">Pay when your order arrives</div></div>
          </div>
        </div>
      </div>

      <div class="summary-card reveal">
        <h3>Order summary</h3>
        <div class="checkout-summary-items">
          <?php foreach ($products as $p): ?>
          <div class="mini-line">
            <span><?php echo htmlspecialchars($p['name']); ?> <span class="qty-tag">×<?php echo $p['qty']; ?></span></span>
            <span>GH₵ <?php echo number_format($p['price'] * $p['qty'], 2); ?></span>
          </div>
          <?php endforeach; ?>
        </div>
        <div class="order-summary-row"><span class="lbl">Subtotal</span><span class="val">GH₵ <?php echo number_format($total, 2); ?></span></div>
        <div class="order-summary-row"><span class="lbl">Delivery</span><span class="val" style="font-size:0.85rem; font-style:italic; opacity:0.8;">Will be charged after delivery</span></div>
        <div class="order-summary-row" style="border-top:1.5px solid var(--line); font-size:1.05rem;">
          <span class="lbl" style="font-weight:700; color:var(--ink);">Total</span>
          <span class="val" style="font-weight:700;">GH₵ <?php echo number_format($grandTotal, 2); ?></span>
        </div>
        <button class="form-submit btn-full" type="submit" style="margin-top:18px;">Place order</button>
        <p class="form-note">By placing your order you agree to our Terms & delivery policy.</p>
      </div>

    </div></form>
  </div>
</section>

<?php include 'includes/footer.php'; ?>