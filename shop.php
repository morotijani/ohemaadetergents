<?php
require_once __DIR__ . '/src/Database.php';
use App\Database;

try {
    $db = Database::getInstance()->getConnection();

    $catStmt = $db->query("SELECT id, name, slug FROM categories ORDER BY name ASC");
    $categories = $catStmt->fetchAll();

    $categoryFilter = $_GET['category'] ?? '';
    $queryParams = [];
    $query = "SELECT p.id, p.name, p.slug, p.price, p.image_url, c.name as category_name 
              FROM products p 
              LEFT JOIN categories c ON p.category_id = c.id
              WHERE p.is_deleted = 0";

    if (!empty($categoryFilter)) {
        $query .= " AND c.slug = ?";
        $queryParams[] = $categoryFilter;
    }

    $query .= " ORDER BY p.created_at DESC";

    $stmt = $db->prepare($query);
    $stmt->execute($queryParams);
    $products = $stmt->fetchAll();

    // Attach sizes to each product
    if (!empty($products)) {
        $ids = array_column($products, 'id');
        $inClause = implode(',', array_fill(0, count($ids), '?'));
        $szStmt = $db->prepare("SELECT id, product_id, label, price, stock, is_default FROM product_sizes WHERE product_id IN ($inClause) ORDER BY sort_order ASC, id ASC");
        $szStmt->execute($ids);
        $allSizes = $szStmt->fetchAll();
        $sizesByPid = [];
        foreach ($allSizes as $sz) $sizesByPid[$sz['product_id']][] = $sz;
        foreach ($products as &$p) {
            $p['sizes']     = $sizesByPid[$p['id']] ?? [];
            $p['has_sizes'] = !empty($p['sizes']);
        }
        unset($p);
    }
} catch (Exception $e) {
    $categories = [];
    $products = [];
}

include 'includes/header.php';
?>

<header class="page-hero">
  <svg class="page-hero-watermark" viewBox="0 0 60 60" fill="none">
    <circle cx="30" cy="30" r="29" fill="none" stroke="#E7C766" stroke-width="1"/>
    <circle cx="30" cy="30" r="22" fill="none" stroke="#E7C766" stroke-width="1"/>
    <path d="M30 14 L34 26 L47 26 L36.5 33 L40.5 45 L30 37.5 L19.5 45 L23.5 33 L13 26 L26 26 Z" fill="#E7C766"/>
  </svg>
  <div class="wrap">
    <div class="breadcrumb"><a href="<?php echo BASE_URL; ?>index">Home</a><span>/</span><span>Products</span></div>
    <span class="eyebrow">The full range</span>
    <h1>Six products. One royal standard.</h1>
    <p class="lede">Every Ohemaa product is formulated, tested, and bottled in-house — nothing white-labelled, nothing outsourced.</p>
  </div>
  <div class="kente-strip" style="margin-top:48px;"></div>
</header>

<section class="products">
  <div class="wrap">

    <div class="filter-bar reveal in">
      <a href="<?php echo BASE_URL; ?>shop" class="filter-chip <?php echo empty($categoryFilter) ? 'active' : ''; ?>">All products</a>
      <?php foreach ($categories as $cat): ?>
      <a href="<?php echo BASE_URL; ?>shop?category=<?php echo urlencode($cat['slug']); ?>" class="filter-chip <?php echo ($categoryFilter === $cat['slug']) ? 'active' : ''; ?>">
        <?php echo htmlspecialchars($cat['name']); ?>
      </a>
      <?php endforeach; ?>
    </div>

    <div class="product-grid" style="margin-top: 40px;">

      <?php if (empty($products)): ?>
          <div class="col-12 text-center py-5" style="grid-column: 1 / -1;">
              <p>No products found.</p>
          </div>
      <?php else: ?>
          <?php foreach ($products as $p): ?>
          <?php $imgUrl = $p['image_url'] ? BASE_URL . $p['image_url'] : 'https://via.placeholder.com/300x400'; ?>
          <div class="product-card reveal in" data-category="<?php echo htmlspecialchars($p['category_name'] ?? ''); ?>">
            <img src="<?php echo htmlspecialchars($imgUrl); ?>" alt="<?php echo htmlspecialchars($p['name']); ?>" style="width: 100%; height: 220px; object-fit: contain; margin-bottom: 22px;">
            <span class="product-tag"><?php echo htmlspecialchars($p['category_name'] ?? 'Product'); ?></span>
            <h3><a href="<?php echo BASE_URL; ?>product?slug=<?php echo urlencode($p['slug']); ?>" style="color:inherit;"><?php echo htmlspecialchars($p['name']); ?></a></h3>
            <div class="product-card-foot" style="display:flex; justify-content:space-between; align-items:center; margin-top:20px;">
              <span class="price" style="font-family:'Fraunces', serif; color:var(--gold-light); font-weight:600;">
                <?php if ($p['has_sizes']): ?>From <?php endif; ?>GH&#8373; <?php echo number_format($p['price'], 2); ?>
              </span>
              <?php if ($p['has_sizes']): ?>
              <a href="<?php echo BASE_URL; ?>product?slug=<?php echo urlencode($p['slug']); ?>" class="add-btn" style="text-decoration:none;">Choose size</a>
              <?php else: ?>
              <button class="add-btn" onclick="addToCart(<?php echo $p['id']; ?>, 1, null, this)">Add to cart</button>
              <?php endif; ?>
            </div>
          </div>
          <?php endforeach; ?>
      <?php endif; ?>
      
      <div class="product-card reveal in" style="display:flex; flex-direction:column; justify-content:center; align-items:flex-start; background:transparent; border-style:dashed;">
        <h3 style="margin-bottom:12px;">Need a custom formula?</h3>
        <p>We produce private-label runs for salons, hotels, and cleaning services — your branding, our formulas.</p>
        <a href="<?php echo BASE_URL; ?>contact" class="btn btn-white" style="margin-top:8px;">Talk to our team</a>
      </div>

    </div>
  </div>
</section>

<section>
  <div class="wrap">
    <div class="center-head reveal">
      <span class="eyebrow">Common questions</span>
      <h2>Product FAQs</h2>
    </div>
    <div class="wrap-narrow" style="padding:0;">
      <div class="faq-accordion">
        <div class="faq-accordion-item">
          <button class="faq-accordion-trigger">Are Ohemaa products safe for sensitive skin? <span class="plus">+</span></button>
          <div class="faq-accordion-panel"><div class="faq-accordion-panel-inner">Our dishwashing liquid and liquid detergent are formulated within dermatologically tested pH ranges. If you have a diagnosed skin condition, we recommend a patch test before regular use.</div></div>
        </div>
        <div class="faq-accordion-item">
          <button class="faq-accordion-trigger">Can I buy Ohemaa products online for delivery? <span class="plus">+</span></button>
          <div class="faq-accordion-panel"><div class="faq-accordion-panel-inner">Yes — add items to your cart and check out for delivery within Kumasi and select regions. You can also find a stockist near you for same-day pickup.</div></div>
        </div>
        <div class="faq-accordion-item">
          <button class="faq-accordion-trigger">Do you offer bulk or wholesale pricing? <span class="plus">+</span></button>
          <div class="faq-accordion-panel"><div class="faq-accordion-panel-inner">Shops, hotels, and cleaning services ordering in volume can apply for wholesale pricing through our stockist programme or by contacting our business team directly.</div></div>
        </div>
        <div class="faq-accordion-item">
          <button class="faq-accordion-trigger">What's the shelf life of an unopened bottle? <span class="plus">+</span></button>
          <div class="faq-accordion-panel"><div class="faq-accordion-panel-inner">Unopened, all Ohemaa products are stable for 24 months from the batch date printed on the bottle. Store away from direct sunlight for best results.</div></div>
        </div>
      </div>
    </div>
  </div>
</section>



<?php include 'includes/footer.php'; ?>
