<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$config = require_once __DIR__ . '/../config/config.php';
if (!defined('BASE_URL')) {
    define('BASE_URL', $config['app']['url'] . '/');
}
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
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/assets/css/main.css">
    <link rel="icon" href="<?php echo BASE_URL; ?>public/assets/img/logo.png" type="image/png">
    <script>const BASE_URL = '<?php echo rtrim(BASE_URL, '/'); ?>';</script>
</head>

<body>

    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container-fluid px-4 px-lg-5">

            <!-- Mobile toggler -->
            <button class="navbar-toggler border-0 shadow-none p-0 d-lg-none" type="button" data-bs-toggle="collapse"
                data-bs-target="#navbarNav">
                <i class="bi bi-list fs-2"></i>
            </button>

            <!-- Brand -->
            <a class="navbar-brand mx-auto mx-lg-0 absolute-center-mobile d-flex align-items-center gap-2"
                href="<?php echo BASE_URL; ?>index">
                <img src="<?php echo BASE_URL; ?>public/assets/img/logo.png" alt="Ohemaa Detergents"
                    style="height: 45px; width: auto; object-fit: contain;">
                <span class="d-none d-md-block font-serif text-black mb-0"
                    style="font-size: 1.15rem; letter-spacing: 0.02em;">Ohemaa Detergents</span>
            </a>

            <!-- Mobile Cart & Auth -->
            <div class="d-lg-none position-relative d-flex gap-3 align-items-center mt-1">
                <?php if (isset($_SESSION['customer_id'])): ?>
                    <a href="<?php echo BASE_URL; ?>profile" class="nav-link text-dark" title="Account"><i class="bi bi-person fs-4"></i></a>
                <?php else: ?>
                    <a href="<?php echo BASE_URL; ?>login" class="nav-link text-dark" title="Login"><i class="bi bi-person fs-4"></i></a>
                <?php endif; ?>
                <a href="<?php echo BASE_URL; ?>cart" class="nav-link text-dark position-relative" title="Bag">
                    <i class="bi bi-bag fs-4"></i>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-dark" style="font-size: 0.6rem; padding: 0.25em 0.4em; transform: translate(-30%, 10%) !important;">
                        <span id="cartBadgeMobile"><?php echo $cartCount; ?></span>
                    </span>
                </a>
            </div>

            <!-- Desktop Menu -->
            <div class="collapse navbar-collapse justify-content-between" id="navbarNav">
                <ul class="navbar-nav gap-2 gap-lg-4 mt-4 mt-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>index">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>shop">Shop</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>stockists">Stockists</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>about">Our Heritage</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>contact">Contact</a>
                    </li>
                </ul>

                <div class="d-none d-lg-flex align-items-center gap-4">
                    <?php if (isset($_SESSION['customer_id'])): ?>
                        <a href="<?php echo BASE_URL; ?>profile" class="nav-link">ACCOUNT</a>
                    <?php else: ?>
                        <a href="<?php echo BASE_URL; ?>login" class="nav-link">LOGIN</a>
                    <?php endif; ?>
                    <a href="<?php echo BASE_URL; ?>track_order" class="nav-link">Track Order</a>
                    <a href="<?php echo BASE_URL; ?>cart" class="nav-link">
                        BAG (<span id="cartBadge"><?php echo $cartCount; ?></span>)
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <style>
        /* Center brand on mobile */
        @media (max-width: 991.98px) {
            .absolute-center-mobile {
                position: absolute;
                left: 50%;
                transform: translateX(-50%);
            }
        }
    </style>

    <script>
        async function addToCart(productId, qty = 1, btn = null) {
            if (btn) {
                btn.disabled = true;
                btn.dataset.originalText = btn.innerHTML;
                btn.innerHTML = 'Adding...';
            }
            try {
                const res = await fetch(`${BASE_URL}/cart_action', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'add', product_id: productId, qty: qty })
                });
                const data = await res.json();
                if (data.status === 'success') {
                    document.getElementById('cartBadge').innerText = data.data.count;
                    if (document.getElementById('cartBadgeMobile')) {
                        document.getElementById('cartBadgeMobile').innerText = data.data.count;
                    }
                    if (btn) {
                        btn.innerHTML = 'Added to bag &#10003;';
                        setTimeout(() => {
                            btn.innerHTML = btn.dataset.originalText;
                            btn.disabled = false;
                        }, 2000);
                    } else {
                        alert(data.message || 'Added to bag successfully!');
                    }
                } else {
                    if (btn) {
                        btn.innerHTML = btn.dataset.originalText;
                        btn.disabled = false;
                    }
                    alert(data.message || 'Failed to add to cart');
                }
            } catch (e) { 
                console.error(e); 
                alert('Network error while adding to cart.');
            }
        }

        async function logoutUser() {
            try {
                const res = await fetch(`${BASE_URL}/api/auth/customer_logout.php', { method: 'POST' });
                const result = await res.json();
                if (res.ok && result.status === 'success') {
                    window.location.href = result.data.redirect;
                }
            } catch (e) {
                console.error(e);
            }
        }
    </script>