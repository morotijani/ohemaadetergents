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
include 'includes/header.php';
?>

<div class="bg-gold-soft py-5 mt-5">
    <div class="container py-4 text-center">
        <h1 class="display-4 fw-800 mb-0">Your Shopping Bag</h1>
    </div>
</div>

<div class="container py-5 mb-5">
    <?php if (empty($products)): ?>
        <div class="text-center py-5 reveal">
            <div class="bg-white p-5 rounded-lg shadow-sm border d-inline-block">
                <i class="bi bi-bag-x fs-1 text-muted mb-4 d-block"></i>
                <h2 class="fw-bold mb-3">Your bag is empty</h2>
                <p class="text-muted mb-4">Looks like you haven't added any royal brightness to your bag yet.</p>
                <a href="shop" class="btn btn-gold btn-lg px-5 rounded-pill">Start Shopping</a>
            </div>
        </div>
    <?php else: ?>
        <div class="row g-5">
            <div class="col-lg-8 reveal">
                <div class="table-responsive glass rounded-lg p-4">
                    <table class="table align-middle border-transparent">
                        <thead>
                            <tr class="text-muted small text-uppercase letter-spacing-1">
                                <th class="border-0 pb-4">Product</th>
                                <th class="border-0 pb-4">Price</th>
                                <th class="border-0 pb-4">Quantity</th>
                                <th class="border-0 pb-4 text-end">Subtotal</th>
                                <th class="border-0 pb-4"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $p): ?>
                                <tr>
                                    <td class="py-4 border-0">
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-md overflow-hidden bg-light me-3" style="width: 80px; height: 80px; flex-shrink: 0;">
                                                <img src="<?php echo $p['image_url'] ? htmlspecialchars($p['image_url']) : 'https://via.placeholder.com/300?text=Ohemaa'; ?>" class="w-100 h-100 object-fit-cover">
                                            </div>
                                            <div>
                                                <span class="fw-bold d-block text-primary"><?php echo htmlspecialchars($p['name']); ?></span>
                                                <span class="text-muted small">Premium Quality</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-4 border-0 fw-bold">GHS <?php echo number_format($p['price'], 2); ?></td>
                                    <td class="py-4 border-0">
                                        <div class="input-group glass p-1 rounded-pill" style="width: 120px;">
                                            <input type="number" class="form-control bg-transparent border-0 text-center fw-bold" value="<?php echo $p['qty']; ?>" min="1" max="<?php echo $p['stock']; ?>" onchange="updateCart(<?php echo $p['id']; ?>, this.value)" style="box-shadow: none;">
                                        </div>
                                    </td>
                                    <td class="py-4 border-0 text-end fw-bold text-gold">GHS <?php echo number_format($p['subtotal'], 2); ?></td>
                                    <td class="py-4 border-0 text-end">
                                        <button class="btn btn-link text-danger p-0 px-2" onclick="removeFromCart(<?php echo $p['id']; ?>)">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-4 d-flex justify-content-between">
                    <a href="shop" class="btn btn-link text-muted text-decoration-none p-0">
                        <i class="bi bi-arrow-left me-2"></i> Continue Shopping
                    </a>
                </div>
            </div>

            <div class="col-lg-4 reveal" style="animation-delay: 0.2s;">
                <div class="glass p-4 rounded-lg sticky-top" style="top: 100px; border-radius: var(--radius-lg);">
                    <h4 class="fw-bold mb-4">Summary</h4>
                    
                    <div class="d-flex justify-content-between mb-3 text-muted">
                        <span>Bag Subtotal</span>
                        <span class="fw-bold">GHS <?php echo number_format($total, 2); ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-3 text-muted">
                        <span>Estimated Shipping</span>
                        <span class="small italic">Calculated at checkout</span>
                    </div>
                    
                    <hr class="my-4 opacity-10">
                    
                    <div class="d-flex justify-content-between mb-5">
                        <span class="fs-5 fw-bold">Total</span>
                        <span class="fs-4 fw-800 text-primary">GHS <?php echo number_format($total, 2); ?></span>
                    </div>
                    
                    <a href="checkout" class="btn btn-gold w-100 py-3 rounded-pill mb-3">
                        Proceed to Checkout <i class="bi bi-lock-fill ms-2"></i>
                    </a>
                    
                    <div class="text-center">
                        <p class="small text-muted mb-0"><i class="bi bi-shield-lock me-1 text-success"></i> Secure Checkout Guaranteed</p>
                    </div>
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
