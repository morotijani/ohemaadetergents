<?php
require_once __DIR__ . '/src/Database.php';
require_once __DIR__ . '/src/Cart.php';

use App\Database;
use App\Cart;

$db = Database::getInstance()->getConnection();
$cart = new Cart();
$cartItems = $cart->getItems();

$products = [];
$total = 0;

if (!empty($cartItems)) {
    $ids = array_keys($cartItems);
    $inClause = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $db->prepare("SELECT id, name, price, image_url, stock FROM products WHERE id IN ($inClause)");
    $stmt->execute($ids);
    $productsData = $stmt->fetchAll();

    foreach ($productsData as $p) {
        $qty = $cartItems[$p['id']];
        $subtotal = $qty * $p['price'];
        $total += $subtotal;
        $products[] = [
            'id' => $p['id'],
            'name' => $p['name'],
            'price' => $p['price'],
            'image_url' => $p['image_url'],
            'qty' => $qty,
            'subtotal' => $subtotal,
            'stock' => $p['stock']
        ];
    }
}

include 'includes/header.php';
?>
<!-- Cart UI -->
<div class="container py-5">
    <h2 class="mb-4">Shopping Cart</h2>
    <?php if (empty($products)): ?>
        <div class="text-center py-5">
            <h4 class="text-muted">Your cart is empty</h4>
            <a href="shop.php" class="btn btn-gold mt-3">Continue Shopping</a>
        </div>
    <?php else: ?>
        <div class="row">
            <div class="col-lg-8">
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Product</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Subtotal</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $p): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="<?php echo $p['image_url'] ? htmlspecialchars($p['image_url']) : 'https://via.placeholder.com/60'; ?>" width="60" height="60" class="rounded object-fit-cover me-3">
                                            <span class="fw-bold"><?php echo htmlspecialchars($p['name']); ?></span>
                                        </div>
                                    </td>
                                    <td>GHS <?php echo number_format($p['price'], 2); ?></td>
                                    <td>
                                        <input type="number" class="form-control form-control-sm" style="width: 70px;" value="<?php echo $p['qty']; ?>" min="1" max="<?php echo $p['stock']; ?>" onchange="updateCart(<?php echo $p['id']; ?>, this.value)">
                                    </td>
                                    <td>GHS <?php echo number_format($p['subtotal'], 2); ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-danger" onclick="removeFromCart(<?php echo $p['id']; ?>)">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card bg-light border-0">
                    <div class="card-body p-4">
                        <h4 class="card-title mb-4">Order Summary</h4>
                        <div class="d-flex justify-content-between mb-3">
                            <span>Subtotal</span>
                            <span class="fw-bold">GHS <?php echo number_format($total, 2); ?></span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-4">
                            <span class="fs-5">Total</span>
                            <span class="fs-5 fw-bold" style="color: var(--ohemaa-blue);">GHS <?php echo number_format($total, 2); ?></span>
                        </div>
                        <a href="checkout.php" class="btn btn-gold w-100 py-3 rounded-pill">Proceed to Checkout</a>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
async function updateCart(productId, qty) {
    await fetch('/ohemaadetergents/cart_action.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({action: 'update', product_id: productId, qty: qty})
    });
    location.reload();
}

async function removeFromCart(productId) {
    await fetch('/ohemaadetergents/cart_action.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({action: 'remove', product_id: productId})
    });
    location.reload();
}
</script>

<?php include 'includes/footer.php'; ?>
