<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
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

  // Fetch sizes
  $stmt = $db->prepare("SELECT id, label, price, stock, is_default, sort_order FROM product_sizes WHERE product_id = ? ORDER BY sort_order ASC, id ASC");
  $stmt->execute([$product['id']]);
  $productSizes = $stmt->fetchAll();
  $hasSizes = !empty($productSizes);
  // If has sizes, show default size price; otherwise product.price is used
  $displayPrice = $product['price'];
  if ($hasSizes) {
    foreach ($productSizes as $sz) {
      if ($sz['is_default']) { $displayPrice = $sz['price']; break; }
    }
  }

  $stmt = $db->prepare("SELECT r.rating, r.comment, r.created_at, c.first_name, c.last_name 
                          FROM product_reviews r 
                          JOIN customers c ON r.customer_id = c.id 
                          WHERE r.product_id = ? AND r.status = 'approved' 
                          ORDER BY r.created_at DESC");
  $stmt->execute([$product['id']]);
  $reviews = $stmt->fetchAll();

  $totalReviews = count($reviews);
  $averageRating = 0;
  if ($totalReviews > 0) {
    $sum = array_sum(array_column($reviews, 'rating'));
    $averageRating = round($sum / $totalReviews, 1);
  }

  $loggedInUser = null;
  if (isset($_SESSION['customer_id'])) {
    $stmt = $db->prepare("SELECT first_name, last_name, email FROM customers WHERE id = ?");
    $stmt->execute([$_SESSION['customer_id']]);
    $loggedInUser = $stmt->fetch();
  }

  $stmt = $db->prepare("
        SELECT p.id, p.name, p.slug, p.description, p.price, p.image_url, c.name as category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE p.id != ? AND p.is_deleted = 0 
        ORDER BY RAND() LIMIT 3
    ");
  $stmt->execute([$product['id']]);
  $relatedProducts = $stmt->fetchAll();

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
      <span style="color:var(--ink); font-weight:700;"><?php echo htmlspecialchars($product['name']); ?></span>
    </div>
  </div>
</section>

<section style="padding-top:26px;">
  <div class="wrap pd-layout">

    <div class="reveal">
      <div class="pd-gallery-main">
        <?php
        $mainImg = $allImages[0];
        $mainImgUrl = (strpos($mainImg, 'http') === 0) ? $mainImg : BASE_URL . $mainImg;
        ?>
        <img src="<?php echo htmlspecialchars($mainImgUrl); ?>" id="mainProductImage"
          style="width: 100%; height: 100%; object-fit: contain; border-radius: 12px; background: #fff;"
          alt="<?php echo htmlspecialchars($product['name']); ?>">
      </div>
      <?php if (count($allImages) > 1): ?>
        <div class="pd-thumbs">
          <?php foreach ($allImages as $index => $img):
            $thumbUrl = (strpos($img, 'http') === 0) ? $img : BASE_URL . $img;
            ?>
            <div class="pd-thumb <?php echo $index === 0 ? 'active' : ''; ?>"
              onclick="document.getElementById('mainProductImage').src='<?php echo htmlspecialchars($thumbUrl); ?>'; document.querySelectorAll('.pd-thumb').forEach(t=>t.classList.remove('active')); this.classList.add('active');">
              <img src="<?php echo htmlspecialchars($thumbUrl); ?>"
                style="width: 100%; height: 100%; object-fit: contain; border-radius: 4px; background: #fff;"
                alt="Thumbnail">
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <div class="reveal">
      <span class="product-tag"
        style="color:var(--teal);"><?php echo htmlspecialchars($product['category_name'] ?? 'Product'); ?></span>
      <h1><?php echo htmlspecialchars($product['name']); ?></h1>
      <div class="rating-row">
        <span class="stars"><?php for ($i = 1; $i <= 5; $i++)
          echo ($i <= round($averageRating)) ? '★' : '☆'; ?></span>
        <span><?php echo number_format($averageRating, 1); ?> out of 5 · <?php echo $totalReviews; ?>
          review<?php echo $totalReviews === 1 ? '' : 's'; ?></span>
      </div>
      <div class="pd-price-row">
        <span class="price" id="pdPrice">GH&#8373; <?php echo number_format($displayPrice, 2); ?></span>
        <span class="was-price">GH&#8373; 32.00</span>
      </div>
      <p class="pd-desc"><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>

      <?php if ($hasSizes): ?>
      <span class="option-label">Size</span>
      <div class="option-row" id="sizeOptionRow">
        <?php foreach ($productSizes as $idx => $sz):
          $isActive = $sz['is_default'] ? 'active' : '';
        ?>
        <button class="option-chip <?php echo $isActive; ?>"
          type="button"
          data-size-id="<?php echo $sz['id']; ?>"
          data-size-price="<?php echo $sz['price']; ?>"
          data-size-stock="<?php echo $sz['stock']; ?>"
          data-size-label="<?php echo htmlspecialchars($sz['label']); ?>"
          onclick="selectSize(this)">
          <?php echo htmlspecialchars($sz['label']); ?>
        </button>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>

      <div class="pd-buybox">
        <div class="qty-stepper">
          <button type="button" class="qty-minus" aria-label="Decrease quantity">&#8211;</button>
          <span class="qty-val">1</span>
          <button type="button" class="qty-plus" aria-label="Increase quantity">+</button>
        </div>
        <button class="add-btn" id="pdAddBtn"
          onclick="addToCartFromDetail(<?php echo $product['id']; ?>, this)">
          Add to cart
        </button>
      </div>

      <div class="pd-trust">
        <div class="trust-item">
          <svg viewBox="0 0 24 24" fill="none" stroke-width="2">
            <path d="M9 12l2 2 4-4" />
            <circle cx="12" cy="12" r="9" />
          </svg>
          Batch-tested in Accra
        </div>
        <div class="trust-item">
          <svg viewBox="0 0 24 24" fill="none" stroke-width="2">
            <path d="M3 3h2l2.6 12.4a2 2 0 0 0 2 1.6h7.8a2 2 0 0 0 2-1.6L21 8H6" />
          </svg>
          72hr dispatch from order
        </div>
        <div class="trust-item">
          <svg viewBox="0 0 24 24" fill="none" stroke-width="2">
            <path d="M12 21s7-6.5 7-11.5A7 7 0 105 9.5C5 14.5 12 21 12 21z" />
          </svg>
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
      <button class="tab-nav-btn" data-tab-target="tab-reviews">Reviews (<?php echo $totalReviews; ?>)</button>
    </div>

    <div id="tab-description" class="tab-panel active">
      <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
    </div>

    <div id="tab-ingredients" class="tab-panel">
      <p>Aqua, Sodium Laureth Sulfate, Citric Acid, Lemon Fragrance Oil, Sodium Chloride, Preservative
        (Methylisothiazolinone), Citrus Limon Peel Extract.</p>
      <p style="margin-top:14px;">Full batch-specific composition available on request — contact our support team with
        your bottle's batch code.</p>
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
        <span class="rating-big"><?php echo number_format($averageRating, 1); ?></span>
        <div>
          <span class="stars"><?php for ($i = 1; $i <= 5; $i++)
            echo ($i <= round($averageRating)) ? '★' : '☆'; ?></span>
          <p style="font-size:0.85rem; color:rgba(26,22,32,0.55); margin-top:4px;">Based on <?php echo $totalReviews; ?>
            verified purchase<?php echo $totalReviews === 1 ? '' : 's'; ?></p>
        </div>
      </div>

      <?php if ($loggedInUser): ?>
        <form id="reviewForm"
          style="margin-top: 30px; margin-bottom: 40px; padding: 24px; background: #fff; border-radius: 8px; border: 1px solid var(--line);">
          <h3 style="font-size: 1.2rem; margin-bottom: 16px;">Leave a review</h3>
          <input type="hidden" id="revProductId" value="<?php echo $product['id']; ?>">
          <input type="hidden" id="revName"
            value="<?php echo htmlspecialchars($loggedInUser['first_name'] . ' ' . $loggedInUser['last_name']); ?>">
          <input type="hidden" id="revEmail" value="<?php echo htmlspecialchars($loggedInUser['email']); ?>">

          <div style="margin-bottom: 16px;">
            <label style="display:block; font-weight:600; margin-bottom:8px; font-size:0.9rem;">Rating</label>
            <div class="star-rating" style="font-size: 1.8rem; color: #ccc; cursor: pointer; display:inline-block;">
              <span data-val="1">★</span><span data-val="2">★</span><span data-val="3">★</span><span
                data-val="4">★</span><span data-val="5">★</span>
            </div>
            <input type="hidden" id="revRating" value="0">
          </div>
          <div style="margin-bottom: 20px;">
            <label style="display:block; font-weight:600; margin-bottom:8px; font-size:0.9rem;">Comment</label>
            <textarea id="revComment" rows="4" placeholder="What did you think of this product?" required
              style="width:100%; border:1px solid var(--line); border-radius:4px; padding:12px; font-family:inherit; resize:vertical;"></textarea>
          </div>
          <button type="submit" class="btn btn-dark" style="padding:12px 24px;">Submit Review</button>
          <div id="revMsg" style="margin-top:12px; font-size:0.9rem; font-weight:600;"></div>
        </form>
      <?php else: ?>
        <div
          style="margin-top: 30px; margin-bottom: 40px; padding: 24px; background: #fdfbf7; border-radius: 8px; border: 1px dashed var(--line); text-align:center;">
          <p style="margin-bottom:12px;">You must be logged in to leave a review.</p>
          <a href="<?php echo BASE_URL; ?>login" class="btn btn-outline" style="padding:8px 20px;">Log In</a>
        </div>
      <?php endif; ?>

      <?php if (empty($reviews)): ?>
        <p style="text-align:center; padding: 40px 0; color: #666;">No reviews yet. Be the first to review this product!
        </p>
      <?php else: ?>
        <?php foreach ($reviews as $rev): ?>
          <div class="review-card">
            <div class="review-head">
              <span class="review-name"><?php echo htmlspecialchars($rev['first_name'] . ' ' . $rev['last_name']); ?></span>
              <span class="review-date"><?php echo date('M Y', strtotime($rev['created_at'])); ?></span>
            </div>
            <span class="stars"><?php for ($i = 1; $i <= 5; $i++)
              echo ($i <= $rev['rating']) ? '★' : '☆'; ?></span>
            <p class="review-body" style="margin-top:8px;"><?php echo nl2br(htmlspecialchars($rev['comment'])); ?></p>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
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
      <?php foreach ($relatedProducts as $rp):
        $rpImg = $rp['image_url'] ? $rp['image_url'] : 'https://via.placeholder.com/600x600?text=No+Image';
        $rpImgUrl = (strpos($rpImg, 'http') === 0) ? $rpImg : BASE_URL . $rpImg;
        ?>
        <div class="product-card reveal">
          <img src="<?php echo htmlspecialchars($rpImgUrl); ?>" alt="<?php echo htmlspecialchars($rp['name']); ?>"
            style="width: 100%; height: 220px; object-fit: contain; margin-bottom: 22px;">
          <span class="product-tag"><?php echo htmlspecialchars($rp['category_name'] ?? 'Product'); ?></span>
          <h3><a href="<?php echo BASE_URL; ?>product?slug=<?php echo urlencode($rp['slug']); ?>"
              style="color:inherit;"><?php echo htmlspecialchars($rp['name']); ?></a></h3>
          <div class="product-card-foot"
            style="display:flex; justify-content:space-between; align-items:center; margin-top:20px;">
            <span class="price" style="font-family:'Fraunces', serif; color:var(--gold-light); font-weight:600;">GH₵
              <?php echo number_format($rp['price'], 2); ?></span>
            <button class="add-btn" onclick="addToCart(<?php echo $rp['id']; ?>, 1, this)">Add to cart</button>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>


<script>
  // Size chip selection — updates displayed price
  function selectSize(chip) {
    document.querySelectorAll('#sizeOptionRow .option-chip').forEach(c => c.classList.remove('active'));
    chip.classList.add('active');
    const price = parseFloat(chip.dataset.sizePrice);
    const priceEl = document.getElementById('pdPrice');
    if (priceEl && !isNaN(price)) {
      priceEl.textContent = 'GH₵ ' + price.toFixed(2);
    }
  }

  // Add to cart from detail page (size-aware)
  async function addToCartFromDetail(productId, btn) {
    const activeChip = document.querySelector('#sizeOptionRow .option-chip.active');
    const sizeId    = activeChip ? parseInt(activeChip.dataset.sizeId) : null;
    const sizeLabel = activeChip ? activeChip.dataset.sizeLabel : null;
    const qty       = parseInt(document.querySelector('.qty-val')?.textContent || '1', 10);

    const original = btn.textContent;
    btn.disabled   = true;
    btn.textContent = 'Adding...';

    try {
      const res = await fetch(`${BASE_URL}/api/cart/action.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'add', product_id: productId, qty, size_id: sizeId })
      });
      const data = await res.json();
      if (data.status === 'success') {
        btn.textContent = 'Added ✓';
        btn.classList.add('added');
        // Update badge
        document.querySelectorAll('.js-cart-badge').forEach(b => {
          b.textContent = data.data.count;
          b.style.display = data.data.count > 0 ? 'flex' : 'none';
        });
        const label = sizeLabel ? `(${sizeLabel})` : '';
        showToast(`Added to cart ${label}`);
        setTimeout(() => { btn.textContent = original; btn.classList.remove('added'); btn.disabled = false; }, 1400);
      } else {
        showToast(data.message || 'Could not add to cart');
        btn.disabled   = false;
        btn.textContent = original;
      }
    } catch(e) {
      showToast('Network error');
      btn.disabled   = false;
      btn.textContent = original;
    }
  }

  // Qty steppers (product detail page)
  const pdQtyEl  = document.querySelector('.pd-buybox .qty-val');
  const pdMinus  = document.querySelector('.pd-buybox .qty-minus');
  const pdPlus   = document.querySelector('.pd-buybox .qty-plus');
  if (pdMinus && pdPlus && pdQtyEl) {
    pdMinus.addEventListener('click', () => {
      let v = parseInt(pdQtyEl.textContent, 10);
      if (v > 1) pdQtyEl.textContent = v - 1;
    });
    pdPlus.addEventListener('click', () => {
      pdQtyEl.textContent = parseInt(pdQtyEl.textContent, 10) + 1;
    });
  }

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

  document.querySelectorAll('.star-rating span').forEach(star => {
    star.addEventListener('click', function () {
      const rating = this.getAttribute('data-val');
      document.getElementById('revRating').value = rating;
      const stars = this.parentElement.children;
      for (let i = 0; i < stars.length; i++) {
        stars[i].style.color = (i < rating) ? '#E7C766' : '#ccc';
      }
    });
  });

  document.getElementById('reviewForm')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn = e.target.querySelector('button');
    const msg = document.getElementById('revMsg');

    const data = {
      product_id: document.getElementById('revProductId').value,
      name: document.getElementById('revName').value,
      email: document.getElementById('revEmail').value,
      rating: document.getElementById('revRating').value,
      comment: document.getElementById('revComment').value
    };

    if (data.rating == 0) {
      msg.textContent = 'Please select a star rating.';
      msg.style.color = '#c00';
      return;
    }

    btn.disabled = true;
    btn.textContent = 'Submitting...';

    try {
      const res = await fetch(`${BASE_URL}/api/reviews/create.php`, {
        method: 'POST',
        body: JSON.stringify(data),
        headers: { 'Content-Type': 'application/json' }
      });
      const out = await res.json();
      if (out.status === 'success') {
        msg.textContent = out.message;
        msg.style.color = '#0c0';
        e.target.reset();
        document.querySelectorAll('.star-rating span').forEach(s => s.style.color = '#ccc');
        document.getElementById('revRating').value = 0;
      } else {
        msg.textContent = out.message || 'Error submitting review';
        msg.style.color = '#c00';
      }
    } catch (err) {
      msg.textContent = 'Network error';
      msg.style.color = '#c00';
    }
    btn.disabled = false;
    btn.textContent = 'Submit Review';
  });
</script>
<?php include 'includes/footer.php'; ?>