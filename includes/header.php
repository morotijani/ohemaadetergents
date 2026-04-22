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
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root {
            --ohemaa-white: #FFFFFF;
            --ohemaa-bg-offwhite: #F8F9FA;
            --ohemaa-blue: #003366;
            --ohemaa-gold: #D4AF37;
            --ohemaa-gold-hover: #c49f27;
        }
        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--ohemaa-bg-offwhite);
            color: var(--ohemaa-blue);
        }
        h1, h2, h3, h4, h5, h6, .navbar-brand {
            font-family: 'Montserrat', sans-serif;
            font-weight: 700;
            color: var(--ohemaa-blue);
        }
        .navbar {
            background-color: var(--ohemaa-white);
            box-shadow: 0 2px 10px rgba(0, 51, 102, 0.05);
        }
        .nav-link {
            color: var(--ohemaa-blue);
            font-weight: 500;
        }
        .nav-link:hover {
            color: var(--ohemaa-gold);
        }
        .btn-gold {
            background-color: var(--ohemaa-gold);
            color: var(--ohemaa-white);
            font-weight: 600;
            border: none;
            transition: all 0.3s ease;
        }
        .btn-gold:hover {
            background-color: var(--ohemaa-gold-hover);
            color: var(--ohemaa-white);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(212, 175, 55, 0.2);
        }
        .btn-outline-gold {
            border: 2px solid var(--ohemaa-gold);
            color: var(--ohemaa-gold);
            font-weight: 600;
            background: transparent;
            transition: all 0.3s ease;
        }
        .btn-outline-gold:hover {
            background-color: var(--ohemaa-gold);
            color: var(--ohemaa-white);
        }
        .product-card {
            border: none;
            border-radius: 16px;
            overflow: hidden;
            transition: all 0.3s ease;
            height: 100%;
            background-color: var(--ohemaa-white);
            box-shadow: 0 4px 15px rgba(0, 51, 102, 0.04);
        }
        .product-card:hover {
            box-shadow: 0 10px 20px rgba(0, 51, 102, 0.08);
            transform: translateY(-5px);
        }
        .product-image {
            height: 250px;
            object-fit: cover;
            width: 100%;
            background-color: #f8f9fa;
        }
        .price-tag {
            color: var(--ohemaa-blue);
            font-weight: 700;
            font-size: 1.2rem;
        }
        .hero-section {
            background-color: var(--ohemaa-blue);
            color: var(--ohemaa-white);
            padding: 80px 0;
            text-align: center;
        }
        .hero-section h1 {
            color: var(--ohemaa-white);
        }
        .sidebar-title {
            color: var(--ohemaa-blue);
            font-weight: 700;
            border-bottom: 2px solid var(--ohemaa-gold);
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .category-link {
            color: #555;
            text-decoration: none;
            display: block;
            padding: 8px 0;
            transition: color 0.2s;
        }
        .category-link:hover, .category-link.active {
            color: var(--ohemaa-gold);
            font-weight: 600;
        }
        footer {
            background-color: var(--ohemaa-blue);
            color: var(--ohemaa-white);
            padding: 40px 0 20px;
            margin-top: 60px;
        }
        
        /* Mobile Sticky CTA Container */
        .mobile-sticky-cta {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: var(--ohemaa-white);
            padding: 15px;
            box-shadow: 0 -4px 20px rgba(0, 51, 102, 0.1);
            z-index: 1030;
            display: none;
        }
        @media (max-width: 767.98px) {
            .mobile-sticky-cta.active {
                display: flex;
            }
            .checkout-mobile-spacing {
                padding-bottom: 100px;
            }
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg sticky-top py-3">
    <div class="container">
        <a class="navbar-brand fs-3" href="/ohemaadetergents/index">Ohemaa</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav mx-auto">
                <li class="nav-item">
                    <a class="nav-link px-3" href="/ohemaadetergents/index">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link px-3" href="/ohemaadetergents/shop">Shop</a>
                </li>
            </ul>
            <div class="d-flex align-items-center">
                <a href="/ohemaadetergents/cart" class="nav-link position-relative me-3">
                    <i class="bi bi-cart3 fs-5"></i>
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
