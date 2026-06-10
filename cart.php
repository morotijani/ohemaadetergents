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

<div class="container-fluid px-4 px-lg-5 pt-5 mt-5">
    <div class="row pt-5 mb-5 pb-5 border-bottom border-light">
        <div class="col-lg-12 text-center">
            <h1 class="font-serif text-black" style="font-size: 3.5rem;">Shopping Bag</h1>
        </div>
    </div>
</div>

<div class="container-fluid px-4 px-lg-5 mb-5 pb-5">
    <?php if (empty($products)): ?>
        <div class="text-center py-5">
            <p class="font-sans text-muted text-uppercase letter-spacing-wide mb-4" style="font-size: 0.75rem;">Your bag is currently empty.</p>
            <a href="shop" class="btn btn-black">Continue Shopping</a>
        </div>
    <?php else: ?>
        <div class="row">
            <div class="col-lg-8 pe-lg-5 mb-5 mb-lg-0 border-end border-light">
                <div class="table-responsive">
                    <table class="table align-middle" style="border-collapse: collapse;">
                        <thead>
                            <tr class="font-sans text-muted text-uppercase letter-spacing-widest" style="font-size: 0.65rem;">
                                <th class="border-top-0 border-bottom border-black pb-3 fw-600">Product</th>
                                <th class="border-top-0 border-bottom border-black pb-3 fw-600">Price</th>
                                <th class="border-top-0 border-bottom border-black pb-3 fw-600">Quantity</th>
                                <th class="border-top-0 border-bottom border-black pb-3 fw-600 text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $p): ?>
                                <tr>
                                    <td class="py-4 border-bottom border-light">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-off-white me-4" style="width: 80px; height: 100px; flex-shrink: 0; padding: 0.5rem;">
                                                <img src="<?php echo $p['image_url'] ? htmlspecialchars($p['image_url']) : 'https://via.placeholder.com/300?text=Ohemaa'; ?>" class="w-100 h-100" style="object-fit: contain;">
                                            </div>
                                            <div>
                                                <a href="product?slug=<?php echo urlencode($p['name']); ?>" class="font-serif text-black text-decoration-none" style="font-size: 1.1rem;"><?php echo htmlspecialchars($p['name']); ?></a>
                                                <br>
                                                <button class="btn btn-link text-muted p-0 text-decoration-none font-sans text-uppercase mt-2" style="font-size: 0.65rem; letter-spacing: 0.05em;" onclick="removeFromCart(<?php echo $p['id']; ?>)">Remove</button>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-4 border-bottom border-light font-sans text-black" style="font-size: 0.85rem;">GHS <?php echo number_format($p['price'], 2); ?></td>
                                    <td class="py-4 border-bottom border-light">
                                        <div class="d-flex border border-black" style="width: fit-content;">
                                            <input type="number" class="form-control border-0 text-center rounded-0 font-sans fw-600 px-0 bg-transparent" value="<?php echo $p['qty']; ?>" min="1" max="<?php echo $p['stock']; ?>" onchange="updateCart(<?php echo $p['id']; ?>, this.value)" style="width: 60px; box-shadow: none;">
                                        </div>
                                    </td>
                                    <td class="py-4 border-bottom border-light text-end font-sans text-black" style="font-size: 0.85rem;">GHS <?php echo number_format($p['subtotal'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="col-lg-4 ps-lg-5">
                <div class="sticky-top" style="top: 100px;">
                    <h4 class="font-sans text-uppercase letter-spacing-widest text-black mb-4 fw-600" style="font-size: 0.75rem;">Order Summary</h4>
                    
                    <div class="d-flex justify-content-between mb-3 font-sans" style="font-size: 0.85rem;">
                        <span class="text-muted">Subtotal</span>
                        <span class="text-black">GHS <?php echo number_format($total, 2); ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-4 font-sans" style="font-size: 0.85rem;">
                        <span class="text-muted">Shipping</span>
                        <span class="text-muted italic">Calculated at checkout</span>
                    </div>
                    
                    <div class="d-flex justify-content-between mb-5 border-top border-black pt-3">
                        <span class="font-sans text-uppercase letter-spacing-wide fw-600" style="font-size: 0.75rem;">Total</span>
                        <span class="font-sans fw-600 text-black">GHS <?php echo number_format($total, 2); ?></span>
                    </div>
                    
                    <a href="checkout" class="btn btn-black w-100 py-3 mb-3">
                        Checkout
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
async function updateCart(productId, qty) {
    await fetch('/ohemaadetergents/cart_action', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({action: 'update', product_id: productId, qty: qty})
    });
    location.reload();
}

async function removeFromCart(productId) {
    await fetch('/ohemaadetergents/cart_action', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({action: 'remove', product_id: productId})
    });
    location.reload();
}
</script>

<?php include 'includes/footer.php'; ?>
