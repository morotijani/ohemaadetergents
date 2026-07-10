<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
if (!defined('BASE_URL')) {
  $config = require __DIR__ . '/../config/config.php';
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
  <?php
  $seoTitle = $pageTitle ?? 'Ohemaa Detergents — Cleanliness Fit for a Queen';
  $seoDescription = $pageDescription ?? 'Ohemaa Cleaning Agents. Premium, highly effective liquid soaps, detergents, and cleaning products formulated and bottled in Kumasi, Ghana.';
  $seoKeywords = $pageKeywords ?? 'Ohemaa, Detergents, Liquid Soap, Cleaning Products, Kumasi, Ghana, Home Care';
  $seoImage = $pageImage ?? (BASE_URL . 'public/assets/img/logo.png');
  $seoUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
  ?>
  <title><?php echo htmlspecialchars($seoTitle); ?></title>
  <meta name="description" content="<?php echo htmlspecialchars($seoDescription); ?>">
  <meta name="keywords" content="<?php echo htmlspecialchars($seoKeywords); ?>">
  <meta name="author" content="Ohemaa Detergents">
  <link rel="canonical" href="<?php echo htmlspecialchars($seoUrl); ?>">

  <!-- Open Graph / Facebook -->
  <meta property="og:type" content="website">
  <meta property="og:url" content="<?php echo htmlspecialchars($seoUrl); ?>">
  <meta property="og:title" content="<?php echo htmlspecialchars($seoTitle); ?>">
  <meta property="og:description" content="<?php echo htmlspecialchars($seoDescription); ?>">
  <meta property="og:image" content="<?php echo htmlspecialchars($seoImage); ?>">

  <!-- Twitter -->
  <meta property="twitter:card" content="summary_large_image">
  <meta property="twitter:url" content="<?php echo htmlspecialchars($seoUrl); ?>">
  <meta property="twitter:title" content="<?php echo htmlspecialchars($seoTitle); ?>">
  <meta property="twitter:description" content="<?php echo htmlspecialchars($seoDescription); ?>">
  <meta property="twitter:image" content="<?php echo htmlspecialchars($seoImage); ?>">
  <link
    href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,300;9..144,400;9..144,500;9..144,600;9..144,700&family=Manrope:wght@400;500;600;700;800&family=Space+Mono:wght@400;700&display=swap"
    rel="stylesheet">
  <!-- Bootstrap CSS (Kept for inner pages compatibility) -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <!-- Custom Theme CSS -->
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/assets/css/main.css">
  <link rel="icon" href="<?php echo BASE_URL; ?>public/assets/img/logo.png" type="image/png">
  <script>const BASE_URL = '<?php echo rtrim(BASE_URL, '/'); ?>';</script>
</head>

<body>
  <nav class="site-nav">
    <div class="nav-inner">
      <a href="index" class="brand">
        <svg class="seal" viewBox="0 0 60 60" fill="none">
          <circle cx="30" cy="30" r="29" fill="#2B1B4D" stroke="#C9A227" stroke-width="1.5" />
          <circle cx="30" cy="30" r="22" fill="none" stroke="#C9A227" stroke-width="1" />
          <path d="M30 14 L34 26 L47 26 L36.5 33 L40.5 45 L30 37.5 L19.5 45 L23.5 33 L13 26 L26 26 Z" fill="#C9A227" />
          <circle cx="30" cy="30" r="4" fill="#2B1B4D" />
        </svg>
        <div style="display: flex; flex-direction: column; justify-content: center; line-height: 1.1;">
          <span style="font-weight: 800; font-size: 1em;">OHEMAA</span>
          <span style="font-size: 0.4em; letter-spacing: 0.15em; opacity: 0.85; font-weight: 600;">DETERGENTS</span>
        </div>
      </a>
      <div class="nav-main">
        <div class="nav-links" id="navLinks">
          <a href="<?php echo BASE_URL; ?>about">About</a>
          <a href="<?php echo BASE_URL; ?>shop">Shop</a>
          <a href="<?php echo BASE_URL; ?>process">Process</a>
          <a href="<?php echo BASE_URL; ?>stockists">Stockists</a>
          <!-- <a href="#sustainability">Sustainability</a> -->
          <a href="<?php echo BASE_URL; ?>contact">Contact</a>
          <a href="<?php echo BASE_URL; ?>track_order">Track Order</a>
          <a href="<?php echo BASE_URL; ?>become_stockist" class="nav-cta"
            style="padding: 0px 4px 0px 4px !important">Become a
            Stockist</a>
        </div>
      </div>
      <div class="nav-side">
        <a href="<?php echo BASE_URL; ?>cart" class="icon-btn" aria-label="Cart">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M3 3h2l2.6 12.4a2 2 0 0 0 2 1.6h7.8a2 2 0 0 0 2-1.6L21 8H6" />
            <circle cx="9" cy="21" r="1" />
            <circle cx="18" cy="21" r="1" />
          </svg>
          <span class="badge js-cart-badge"><?php echo $cartCount; ?></span>
        </a>
        <?php if (isset($_SESSION['customer_id'])): ?>
          <a href="<?php echo BASE_URL; ?>profile" class="icon-btn" aria-label="Account">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="12" cy="8" r="4" />
              <path d="M4 20c0-4 4-6 8-6s8 2 8 6" />
            </svg>
          </a>
        <?php else: ?>
          <a href="<?php echo BASE_URL; ?>login" class="icon-btn" aria-label="Login">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="12" cy="8" r="4" />
              <path d="M4 20c0-4 4-6 8-6s8 2 8 6" />
            </svg>
          </a>
        <?php endif; ?>
        <button class="nav-toggle" id="navToggle" aria-label="Toggle menu" aria-expanded="false">
          <span></span><span></span><span></span>
        </button>
      </div>
    </div>
  </nav>