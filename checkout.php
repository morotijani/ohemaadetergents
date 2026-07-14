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
if (!empty($cartItems)) {
  $productIds = array_values(array_unique(array_column($cartItems, 'product_id')));
  $inClause = implode(',', array_fill(0, count($productIds), '?'));
  $stmt = $db->prepare("SELECT id, name, price, stock, image_url FROM products WHERE id IN ($inClause)");
  $stmt->execute($productIds);
  $productsData = $stmt->fetchAll(PDO::FETCH_ASSOC);
  $productsMap = [];
  foreach ($productsData as $p) {
      $productsMap[$p['id']] = $p;
  }

  foreach ($cartItems as $key => $item) {
    if (!isset($productsMap[$item['product_id']])) continue;
    $p = $productsMap[$item['product_id']];
    $qty = $item['qty'];
    $price = $item['size_price'] !== null ? $item['size_price'] : $p['price'];
    $total += ($qty * $price);
    $products[] = [
      'cart_key' => $key,
      'id' => $p['id'],
      'price' => $price,
      'qty' => $qty,
      'name' => $p['name'],
      'image_url' => $p['image_url'],
      'size_label' => $item['size_label']
    ];
  }
}

$deliveryFee = 0; // Will be calculated later
$grandTotal = $total;

include 'includes/header.php';
?>
<script src="https://js.paystack.co/v1/inline.js"></script>
<section style="padding-top:44px;">
  <div class="wrap">
    <div class="checkout-steps reveal">
      <span class="step done">✓ Cart</span>
      <span class="sep">—</span>
      <span class="step current">② Checkout</span>
      <span class="sep">—</span>
      <span class="step">③ Confirmation</span>
    </div>

    <div id="checkoutError" style="display: none; padding: 15px; background: #fee; border-left: 4px solid #c00; margin-bottom: 20px; font-size: 0.9rem; color: #c00;"></div>

    <div class="checkout-layout">
      <form id="checkoutForm" class="reveal">
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
            <input type="radio" name="payment" value="momo" style="display:none;" checked>
            <div><div class="pm-name">Mobile Money</div><div class="pm-desc">Pay instantly with MTN, Vodafone, or AirtelTigo</div></div>
          </div>
          <div class="payment-option">
            <div class="radio-dot"></div>
            <input type="radio" name="payment" value="card" style="display:none;">
            <div><div class="pm-name">Debit / Credit Card</div><div class="pm-desc">Visa, Mastercard accepted</div></div>
          </div>
          <div class="payment-option">
            <div class="radio-dot"></div>
            <input type="radio" name="payment" value="cod" style="display:none;">
            <div><div class="pm-name">Cash on Delivery</div><div class="pm-desc">Pay when your order arrives</div></div>
          </div>
        </div>
      </form>

      <div class="summary-card reveal">
        <h3>Order summary</h3>
        <div class="checkout-summary-items">
          <?php foreach ($products as $p): ?>
          <div class="mini-line">
            <span>
              <div style="font-weight:700; color:var(--ink); font-size:1.05rem;">
                <?php echo htmlspecialchars($p['name']); ?>
                <?php if ($p['size_label']): ?>
                  <span style="font-size: 0.8em; color: var(--ink-light); font-weight: normal;">(<?php echo htmlspecialchars($p['size_label']); ?>)</span>
                <?php endif; ?>
              </div>
              <span class="qty-tag">×<?php echo $p['qty']; ?></span>
            </span>
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
        <button type="submit" form="checkoutForm" class="btn btn-dark btn-full" id="placeOrderBtn" style="margin-top:20px; position: relative;">Place order — Secure Payment</button>
        <p class="form-note">By placing your order you agree to our Terms & delivery policy.</p>
      </div>

    </div>
  </div>
</section>

<script>
document.getElementById('checkoutForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const submitBtn = document.getElementById('placeOrderBtn');
    const originalText = submitBtn.textContent;
    submitBtn.textContent = 'Processing...';
    submitBtn.disabled = true;
    document.getElementById('checkoutError').style.display = 'none';

    const formData = new FormData(this);
    const data = Object.fromEntries(formData.entries());

    try {
        const res = await fetch(`${BASE_URL}/api/orders/checkout.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        
        const result = await res.json();

        if (result.status === 'success') {
            const verifyUrl = `${BASE_URL}/verify_payment.php?reference=`;
            
            const handler = PaystackPop.setup({
                key: result.data.public_key,
                access_code: result.data.access_code,
                email: result.data.email,
                amount: result.data.amount,
                currency: 'GHS',
                ref: result.data.reference,
                callback: function(response) {
                    window.location.href = verifyUrl + response.reference;
                },
                onClose: function() {
                    fetch(`${BASE_URL}/api/orders/cancel.php`, {
                        method: 'POST',
                        headers: {'Content-Type': 'application/json'},
                        body: JSON.stringify({ reference: result.data.reference })
                    }).then(() => {
                        const modal = document.getElementById('cancelModal');
                        if (modal) modal.classList.add('show');
                        submitBtn.textContent = originalText;
                        submitBtn.disabled = false;
                    });
                }
            });
            handler.openIframe();
        } else {
            document.getElementById('checkoutError').textContent = result.message || 'An error occurred';
            document.getElementById('checkoutError').style.display = 'block';
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
        }
    } catch (error) {
        document.getElementById('checkoutError').textContent = 'Network error. Please try again.';
        document.getElementById('checkoutError').style.display = 'block';
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
    }
});

function closeCancelModal() {
    const modal = document.getElementById('cancelModal');
    if (modal) modal.classList.remove('show');
}
</script>

<div class="modal-overlay" id="cancelModal">
  <div class="modal-box">
    <div class="modal-icon">
      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
      </svg>
    </div>
    <div class="modal-title">Payment Cancelled</div>
    <div class="modal-text">Your payment process was interrupted and your order has not been placed.</div>
    <button class="btn btn-dark btn-full" onclick="closeCancelModal()">Okay</button>
  </div>
</div>

<?php include 'includes/footer.php'; ?>