<?php
require_once __DIR__ . '/src/Database.php';
use App\Database;

$slug = $_GET['slug'] ?? '';
if (empty($slug)) {
    header("Location: shop");
    exit;
}

try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("SELECT p.id, p.name, p.slug, p.description, p.price, p.image_url, p.stock, c.name as category_name 
                          FROM products p 
                          LEFT JOIN categories c ON p.category_id = c.id 
                          WHERE p.slug = ? AND p.is_deleted = 0");
    $stmt->execute([$slug]);
    $product = $stmt->fetch();
    
    if (!$product) {
        header("Location: shop");
        exit;
    }
    
    // Images
    $stmt = $db->prepare("SELECT image_url FROM product_images WHERE product_id = ? ORDER BY id ASC");
    $stmt->execute([$product['id']]);
    $extraImages = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $allImages = [];
    if ($product['image_url']) {
        $allImages[] = $product['image_url'];
    }
    $allImages = array_merge($allImages, $extraImages);
    if (empty($allImages)) {
        $allImages[] = 'https://via.placeholder.com/600x600?text=No+Image';
    }

} catch (Exception $e) {
    header("Location: shop");
    exit;
}

include 'includes/header.php';
?>


<section style="padding-top:36px; padding-bottom:0;">
  <div class="wrap">
    <div class="breadcrumb pd-breadcrumb-inline" style="color:rgba(26,22,32,0.5);">
      <a href="<?php echo BASE_URL; ?>index" style="color:inherit;">Home</a><span>/</span>
      <a href="<?php echo BASE_URL; ?>shop" style="color:inherit;">Products</a><span>/</span>
      <span style="color:var(--ink); font-weight:700;">Multi-Surface Cleaner</span>
    </div>
  </div>
</section>

<section style="padding-top:26px;">
  <div class="wrap pd-layout">

    <div class="reveal">
      <div class="pd-gallery-main">
        <svg width="220" height="308" viewBox="0 0 220 308" fill="none">
          <ellipse cx="110" cy="296" rx="66" ry="10" fill="#19102C" opacity="0.4"/>
          <rect x="74" y="44" width="72" height="22" rx="6" fill="#8A6E1A"/>
          <rect x="83" y="22" width="54" height="30" rx="8" fill="#C9A227"/>
          <rect x="90" y="10" width="40" height="18" rx="5" fill="#E7C766"/>
          <path d="M52 74 Q52 64 68 64 L152 64 Q168 64 168 74 L180 262 Q182 282 162 282 L58 282 Q38 282 40 262 Z" fill="#F7F3EA" stroke="#2B1B4D" stroke-width="2"/>
          <path d="M58 96 Q58 86 72 86 L148 86 Q162 86 162 96 L172 246 Q174 262 158 262 L62 262 Q46 262 48 246 Z" fill="#E7C766" opacity="0.85"/>
          <rect x="64" y="112" width="92" height="112" rx="12" fill="#2B1B4D"/>
          <circle cx="110" cy="148" r="16" fill="none" stroke="#C9A227" stroke-width="1.4"/>
          <path d="M110 140 L112 146 L118 146 L113 150 L115 156 L110 152 L105 156 L107 150 L102 146 L108 146 Z" fill="#C9A227"/>
          <text x="110" y="180" text-anchor="middle" font-family="Fraunces, serif" font-size="16" font-weight="600" fill="#F7F3EA" letter-spacing="1.5">OHEMAA</text>
          <text x="110" y="195" text-anchor="middle" font-family="Space Mono, monospace" font-size="7" fill="#E7C766" letter-spacing="2">MULTI-SURFACE</text>
        </svg>
      </div>
      <div class="pd-thumbs">
        <div class="pd-thumb active"><svg viewBox="0 0 56 78"><rect x="10" y="14" width="36" height="6" rx="2" fill="#C9A227"/><path d="M6 26 Q6 20 14 20 L42 20 Q50 20 50 26 L54 70 Q55 78 46 78 L10 78 Q1 78 2 70 Z" fill="#F7F3EA"/><rect x="10" y="34" width="36" height="26" rx="4" fill="#C9A227"/></svg></div>
        <div class="pd-thumb"><svg viewBox="0 0 56 78"><rect x="10" y="14" width="36" height="6" rx="2" fill="#E7C766"/><path d="M6 26 Q6 20 14 20 L42 20 Q50 20 50 26 L54 70 Q55 78 46 78 L10 78 Q1 78 2 70 Z" fill="#F7F3EA"/><rect x="10" y="34" width="36" height="26" rx="4" fill="#1E6E63"/></svg></div>
        <div class="pd-thumb"><svg viewBox="0 0 56 78"><rect x="10" y="14" width="36" height="6" rx="2" fill="#A63A3A"/><path d="M6 26 Q6 20 14 20 L42 20 Q50 20 50 26 L54 70 Q55 78 46 78 L10 78 Q1 78 2 70 Z" fill="#F7F3EA"/><rect x="10" y="34" width="36" height="26" rx="4" fill="#A63A3A"/></svg></div>
      </div>
    </div>

    <div class="reveal">
      <span class="product-tag" style="color:var(--teal);">Surface Care</span>
      <h1><?php echo htmlspecialchars($product['name']); ?></h1>
      <div class="rating-row">
        <span class="stars">★★★★★</span>
        <span>4.8 out of 5 · 214 reviews</span>
      </div>
      <div class="pd-price-row">
        <span class="price">GH₵ <?php echo number_format($product['price'], 2); ?></span>
        <span class="was-price">GH₵ 32.00</span>
      </div>
      <p class="pd-desc">Lemon-fresh degreasing power for tiles, counters, and every hard surface in the house. Formulated to cut through grease in one wipe without leaving residue or overpowering fragrance behind.</p>

      <span class="option-label">Size</span>
      <div class="option-row">
        <button class="option-chip" type="button">350ml</button>
        <button class="option-chip active" type="button">750ml</button>
        <button class="option-chip" type="button">1.5L</button>
      </div>

      <div class="pd-buybox">
        <div class="qty-stepper">
          <button type="button" class="qty-minus" aria-label="Decrease quantity">–</button>
          <span class="qty-val">1</span>
          <button type="button" class="qty-plus" aria-label="Increase quantity">+</button>
        </div>
        <button class="add-btn" data-product="Multi-Surface Cleaner" onclick="const qty = parseInt(this.parentElement.querySelector('.qty-val')?.textContent || '1'); addToCart(<?php echo $product['id']; ?>, qty, this)">Add to cart</button>
      </div>

      <div class="pd-trust">
        <div class="trust-item">
          <svg viewBox="0 0 24 24" fill="none" stroke-width="2"><path d="M9 12l2 2 4-4"/><circle cx="12" cy="12" r="9"/></svg>
          Batch-tested in Kumasi
        </div>
        <div class="trust-item">
          <svg viewBox="0 0 24 24" fill="none" stroke-width="2"><path d="M3 3h2l2.6 12.4a2 2 0 0 0 2 1.6h7.8a2 2 0 0 0 2-1.6L21 8H6"/></svg>
          72hr dispatch from order
        </div>
        <div class="trust-item">
          <svg viewBox="0 0 24 24" fill="none" stroke-width="2"><path d="M12 21s7-6.5 7-11.5A7 7 0 105 9.5C5 14.5 12 21 12 21z"/></svg>
          200+ stockists nationwide
        </div>
      </div>
    </div>
  </div>
</section>

<section class="pd-tabs">
  <div class="wrap">
    <div class="tab-nav">
      <button class="tab-nav-btn active" data-tab-target="tab-description">Description</button>
      <button class="tab-nav-btn" data-tab-target="tab-ingredients">Ingredients</button>
      <button class="tab-nav-btn" data-tab-target="tab-howto">How to use</button>
      <button class="tab-nav-btn" data-tab-target="tab-reviews">Reviews (214)</button>
    </div>

    <div id="tab-description" class="tab-panel active">
      <p>Ohemaa Multi-Surface Cleaner is formulated for daily use across kitchens, bathrooms, and living spaces. It cuts through grease and grime without leaving the streaky residue common in harsher degreasers, and the lemon fragrance clears rather than lingers.</p>
      <p style="margin-top:14px;">Safe for use on tile, laminate, sealed granite, glass, and painted surfaces. Not recommended for unsealed stone or wood.</p>
    </div>

    <div id="tab-ingredients" class="tab-panel">
      <p>Aqua, Sodium Laureth Sulfate, Citric Acid, Lemon Fragrance Oil, Sodium Chloride, Preservative (Methylisothiazolinone), Citrus Limon Peel Extract.</p>
      <p style="margin-top:14px;">Full batch-specific composition available on request — contact our support team with your bottle's batch code.</p>
    </div>

    <div id="tab-howto" class="tab-panel">
      <ul>
        <li>Shake gently before first use.</li>
        <li>Spray directly onto the surface from 15–20cm away.</li>
        <li>Leave for 30 seconds on tougher grease marks.</li>
        <li>Wipe with a clean, damp cloth.</li>
        <li>For heavily soiled surfaces, dilute 1:4 with water for a larger-area wash.</li>
      </ul>
    </div>

    <div id="tab-reviews" class="tab-panel">
      <div class="rating-summary">
        <span class="rating-big">4.8</span>
        <div>
          <span class="stars">★★★★★</span>
          <p style="font-size:0.85rem; color:rgba(26,22,32,0.55); margin-top:4px;">Based on 214 verified purchases</p>
        </div>
      </div>

      <div class="review-card">
        <div class="review-head"><span class="review-name">Efua Mensah</span><span class="review-date">Jun 2026</span></div>
        <span class="stars">★★★★★</span>
        <p class="review-body" style="margin-top:8px;">Cuts through kitchen grease faster than anything I've used before, and the smell isn't overpowering. Repurchased three times now.</p>
      </div>
      <div class="review-card">
        <div class="review-head"><span class="review-name">Kwabena Asante</span><span class="review-date">May 2026</span></div>
        <span class="stars">★★★★★</span>
        <p class="review-body" style="margin-top:8px;">Consistent every time I buy it — same scent, same cleaning power. That's rare with local brands.</p>
      </div>
      <div class="review-card">
        <div class="review-head"><span class="review-name">Ama Yeboah</span><span class="review-date">Apr 2026</span></div>
        <span class="stars">★★★★☆</span>
        <p class="review-body" style="margin-top:8px;">Great on tile and counters. Wish it came in a bigger bottle for the price, but it lasts a long time.</p>
      </div>
    </div>
  </div>
</section>

<div class="kente-strip thin"></div>

<section class="products">
  <div class="wrap">
    <div class="section-head on-dark reveal">
      <span class="eyebrow"><?php echo htmlspecialchars($product['category_name'] ?? 'Product'); ?></span>
      <h2>Complete the clean.</h2>
    </div>
    <div class="product-grid">
      <div class="product-card reveal">
        <svg class="cap-icon" viewBox="0 0 56 78"><rect x="10" y="14" width="36" height="6" rx="2" fill="#A63A3A"/><path d="M6 26 Q6 20 14 20 L42 20 Q50 20 50 26 L54 70 Q55 78 46 78 L10 78 Q1 78 2 70 Z" fill="#F7F3EA"/><rect x="10" y="34" width="36" height="26" rx="4" fill="#A63A3A"/></svg>
        <span class="product-tag">Laundry</span>
        <h3>Liquid Detergent</h3>
        <p>Deep-cleans fabric fibres while staying gentle on hands.</p>
        <div class="product-card-foot">
          <span class="price">GH₵ 34.00</span>
          <button class="add-btn" data-product="Liquid Detergent">Add to cart</button>
        </div>
      </div>
      <div class="product-card reveal">
        <svg class="cap-icon" viewBox="0 0 56 78"><rect x="10" y="14" width="36" height="6" rx="2" fill="#1E6E63"/><path d="M6 26 Q6 20 14 20 L42 20 Q50 20 50 26 L54 70 Q55 78 46 78 L10 78 Q1 78 2 70 Z" fill="#F7F3EA"/><rect x="10" y="34" width="36" height="26" rx="4" fill="#1E6E63"/></svg>
        <span class="product-tag">Kitchen</span>
        <h3>Dishwashing Liquid</h3>
        <p>Cuts through grease in one rinse, gentle on hands.</p>
        <div class="product-card-foot">
          <span class="price">GH₵ 19.00</span>
          <button class="add-btn" data-product="Dishwashing Liquid">Add to cart</button>
        </div>
      </div>
      <div class="product-card reveal">
        <svg class="cap-icon" viewBox="0 0 56 78"><rect x="10" y="14" width="36" height="6" rx="2" fill="#E7C766"/><path d="M6 26 Q6 20 14 20 L42 20 Q50 20 50 26 L54 70 Q55 78 46 78 L10 78 Q1 78 2 70 Z" fill="#F7F3EA"/><rect x="10" y="34" width="36" height="26" rx="4" fill="#E7C766"/></svg>
        <span class="product-tag">Laundry</span>
        <h3>Fabric Softener</h3>
        <p>Keeps clothes soft and fragranced through three washes.</p>
        <div class="product-card-foot">
          <span class="price">GH₵ 26.00</span>
          <button class="add-btn" data-product="Fabric Softener">Add to cart</button>
        </div>
      </div>
    </div>
  </div>
</section>


<script>
function updateGallery(el, src) {
    const mainImg = document.getElementById('mainProductImage');
    mainImg.style.opacity = '0';
    setTimeout(() => {
        mainImg.src = src;
        mainImg.style.opacity = '1';
    }, 200);
    
    document.querySelectorAll('.thumb').forEach(item => {
        item.classList.remove('active');
    });
    el.classList.add('active');
}
</script>
<?php include 'includes/footer.php'; ?>
