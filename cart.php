<?php
require_once __DIR__ . '/src/Database.php';
require_once __DIR__ . '/src/Cart.php';

use App\Database;
use App\Cart;

$cartObj = new Cart();
$cartItems = $cartObj->getItems();

$db = Database::getInstance()->getConnection();
$total = 0;
$products = [];

if (!empty($cartItems)) {
  $productIds = array_values(array_unique(array_column($cartItems, 'product_id')));
  $inClause = implode(',', array_fill(0, count($productIds), '?'));
  $stmt = $db->prepare("SELECT id, name, price, image_url FROM products WHERE id IN ($inClause)");
  $stmt->execute($productIds);
  $productsData = $stmt->fetchAll(PDO::FETCH_ASSOC);
  $productsMap = [];
  foreach ($productsData as $p) {
    $productsMap[$p['id']] = $p;
  }

  foreach ($cartItems as $key => $item) {
    if (!isset($productsMap[$item['product_id']]))
      continue;
    $p = $productsMap[$item['product_id']];
    $qty = $item['qty'];
    $price = $item['size_price'] !== null ? $item['size_price'] : $p['price'];
    $total += ($qty * $price);

    $products[] = [
      'cart_key' => $key,
      'product_id' => $p['id'],
      'price' => $price,
      'qty' => $qty,
      'name' => $p['name'],
      'image_url' => $p['image_url'],
      'size_label' => $item['size_label']
    ];
  }
}

include 'includes/header.php';
?>


<header class="page-hero" style="padding:48px 0 40px;">
  <svg class="page-hero-watermark" viewBox="0 0 60 60" fill="none">
    <circle cx="30" cy="30" r="29" fill="none" stroke="#E7C766" stroke-width="1" />
    <circle cx="30" cy="30" r="22" fill="none" stroke="#E7C766" stroke-width="1" />
    <path d="M30 14 L34 26 L47 26 L36.5 33 L40.5 45 L30 37.5 L19.5 45 L23.5 33 L13 26 L26 26 Z" fill="#E7C766" />
  </svg>
  <div class="wrap">
    <div class="breadcrumb"><a href="<?php echo BASE_URL; ?>index">Home</a><span>/</span><span>Cart</span></div>
    <span class="eyebrow"><?php echo $cartCount; ?> item<?php echo $cartCount === 1 ? '' : 's'; ?></span>
    <h1 style="font-size:2.2rem; margin-top:14px;">Your cart</h1>
  </div>
</header>

<section style="padding-top:56px;">
  <div class="wrap">
    <div class="cart-layout">


      <div class="js-cart-list" <?php if (empty($cartItems))
        echo 'style="display:none;"'; ?>>
        <?php foreach ($products as $p): ?>
          <?php $imgUrl = $p['image_url'] ? BASE_URL . $p['image_url'] : 'https://via.placeholder.com/100'; ?>
          <div class="cart-row" data-unit-price="<?php echo $p['price']; ?>">
            <img src="<?php echo htmlspecialchars($imgUrl); ?>" alt="<?php echo htmlspecialchars($p['name']); ?>"
              style="width: 56px; height: 78px; object-fit: contain; background: #fff; border: 1px solid var(--line); border-radius: 4px; padding: 4px;">
            <div>
              <div class="cart-item-name">
                <?php echo htmlspecialchars($p['name']); ?>
                <?php if ($p['size_label']): ?>
                  <span style="font-size: 0.85em; color: var(--ink-light);">
                    (<?php echo htmlspecialchars($p['size_label']); ?>)</span>
                <?php endif; ?>
              </div>
              <div class="cart-item-meta"></div>
              <a href="#" class="cart-remove"
                onclick="removeItem('<?php echo $p['cart_key']; ?>'); return false;">Remove</a>
            </div>
            <div class="qty-stepper">
              <button type="button" class="qty-minus" onclick="updateQty('<?php echo $p['cart_key']; ?>', -1)">–</button>
              <span class="qty-val"><?php echo $p['qty']; ?></span>
              <button type="button" class="qty-plus" onclick="updateQty('<?php echo $p['cart_key']; ?>', 1)">+</button>
            </div>
            <div class="cart-line-total">GH₵ <?php echo number_format($p['price'] * $p['qty'], 2); ?></div>
          </div>
        <?php endforeach; ?>

        <a href="<?php echo BASE_URL; ?>shop" class="btn btn-outline" style="margin-top:26px;">← Continue shopping</a>
      </div>

      <div class="js-cart-empty empty-state" <?php if (!empty($cartItems))
        echo 'style="display:none;"'; ?>>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
          <path d="M3 3h2l2.6 12.4a2 2 0 0 0 2 1.6h7.8a2 2 0 0 0 2-1.6L21 8H6" />
          <circle cx="9" cy="21" r="1" />
          <circle cx="18" cy="21" r="1" />
        </svg>
        <h3>Your cart is empty</h3>
        <p>Looks like you haven't added anything yet.</p>
        <a href="<?php echo BASE_URL; ?>shop" class="btn btn-primary">Browse products</a>
      </div>


      <?php if (!empty($cartItems)): ?>
        <div class="summary-card js-cart-summary">
          <h3>Order summary</h3>
          <div class="order-summary-row"><span class="lbl">Subtotal</span><span class="val js-subtotal">GH₵
              <?php echo number_format($total, 2); ?></span></div>
          <div class="order-summary-row"><span class="lbl">Delivery</span><span class="val">Calculated on Pay</span></div>
          <div class="promo-row">
            <input type="text" placeholder="Promo code">
            <button class="btn btn-outline btn-sm" type="button">Apply</button>
          </div>
          <div class="order-summary-row" style="border-top:1.5px solid var(--line); font-size:1.05rem;"><span class="lbl"
              style="font-weight:700; color:var(--ink);">Total</span><span class="val js-total"
              style="color:var(--gold-light);">GH₵ <?php echo number_format($total, 2); ?></span></div>
          <a href="<?php echo BASE_URL; ?>checkout" class="btn btn-dark btn-full" style="margin-top:20px;">Checkout</a>
          <p class="form-note">Delivery available worldwide, and select regions.</p>
        </div><?php endif; ?>
    </div>
  </div>
</section>


<script>
  function parseCartKey(cartKey) {
    const parts = String(cartKey).split(':');
    return {
      product_id: parseInt(parts[0], 10),
      size_id: parts[1] ? parseInt(parts[1], 10) : null
    };
  }

  async function updateQty(cartKey, change) {
    const { product_id, size_id } = parseCartKey(cartKey);
    try {
      const res = await fetch(`${BASE_URL}/api/cart/action.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'update_relative', product_id, size_id, change })
      });
      const data = await res.json();
      if (data.status === 'success') {
        window.location.reload();
      } else {
        alert(data.message || 'Failed to update quantity');
      }
    } catch (e) {
      console.error(e);
      alert('Network error');
    }
  }

  async function removeItem(cartKey) {
    if (!confirm('Remove this item from your bag?')) return;
    const { product_id, size_id } = parseCartKey(cartKey);
    try {
      const res = await fetch(`${BASE_URL}/api/cart/action.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'remove', product_id, size_id })
      });
      const data = await res.json();
      if (data.status === 'success') {
        window.location.reload();
      } else {
        alert(data.message || 'Failed to remove item');
      }
    } catch (e) {
      console.error(e);
      alert('Network error');
    }
  }
</script>
<?php include 'includes/footer.php'; ?>