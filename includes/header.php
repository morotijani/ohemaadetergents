<?php
require_once __DIR__ . '/../src/Cart.php';
$cartObj = new \App\Cart();
$cartCount = $cartObj->count();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ohemaa Detergents</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/ohemaadetergents/public/assets/css/main.css">
</head>
<body>

<nav class="navbar navbar-expand-lg fixed-top">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="/ohemaadetergents/index">
            <span class="fw-900">OHEMAA</span>
            <span class="ms-2 small fw-bold text-gold d-none d-sm-inline" style="font-family: 'Montserrat'; letter-spacing: 4px; font-size: 0.6rem; vertical-align: middle;">PREMIUM</span>
        </a>
        
        <div class="d-flex align-items-center d-lg-none">
            <a href="/ohemaadetergents/cart" class="nav-link position-relative me-3">
                <i class="bi bi-bag-heart fs-4"></i>
                <span id="cartBadgeMobile" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.6rem;">
                    <?php echo $cartCount; ?>
                </span>
            </a>
            <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <i class="bi bi-list fs-2 text-primary"></i>
            </button>
        </div>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav mx-auto">
                <li class="nav-item">
                    <a class="nav-link px-lg-4" href="/ohemaadetergents/index">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link px-lg-4" href="/ohemaadetergents/shop">Shop</a>
                </li>
                <li class="nav-item d-lg-none">
                    <a class="nav-link px-lg-4" href="/ohemaadetergents/track_order">Track Order</a>
                </li>
            </ul>
            
            <div class="d-none d-lg-flex align-items-center gap-3">
                <a href="/ohemaadetergents/track_order" class="nav-link text-muted small me-2">Track Order</a>
                <a href="/ohemaadetergents/cart" class="nav-link position-relative p-0 me-2">
                    <div class="bg-gold-soft p-2 rounded-circle border border-gold">
                        <i class="bi bi-bag-heart fs-5 text-gold"></i>
                    </div>
                    <span id="cartBadge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.6rem;">
                        <?php echo $cartCount; ?>
                    </span>
                </a>
            </div>
        </div>
    </div>
</nav>

<script>
async function addToCart(productId, qty = 1) {
    try {
        const res = await fetch('/ohemaadetergents/cart_action', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({action: 'add', product_id: productId, qty: qty})
        });
        const data = await res.json();
        if (data.status === 'success') {
            document.getElementById('cartBadge').innerText = data.data.count;
            // Optionally show a toast here
        }
    } catch(e) { console.error(e); }
}
</script>
