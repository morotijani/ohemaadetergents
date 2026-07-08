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

<section class="products" style="background:var(--paper); color:var(--ink); border-top:1px solid var(--line);">
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
              <span class="price" style="font-family:'Fraunces', serif; color:var(--gold-light); font-weight:600;">GH₵ <?php echo number_format($p['price'], 2); ?></span>
              <button class="btn btn-ghost" style="padding: 8px 16px; font-size:0.8rem;" onclick="addToCart(<?php echo $p['id']; ?>, 1, this)">Add to cart</button>
            </div>
          </div>
          <?php endforeach; ?>
      <?php endif; ?>

    </div>
  </div>
</section>

<style>
.products .product-card {
    background: #fff;
    border: 1px solid var(--line);
    color: var(--ink);
}
.products .product-card h3 { color: var(--indigo); }
.products .product-card:hover {
    background: var(--ivory);
    border-color: var(--gold);
}
.products .product-card .btn-ghost {
    border-color: var(--indigo);
    color: var(--indigo);
}
.products .product-card .btn-ghost:hover {
    background: var(--indigo);
    color: var(--ivory);
}
.filter-chip {
    padding: 8px 16px; border: 1px solid var(--line); border-radius: 100px;
    background: transparent; color: var(--ink); text-decoration: none;
    font-size: 0.9rem; font-weight: 600; cursor: pointer; transition: 0.2s;
}
.filter-chip:hover, .filter-chip.active {
    background: var(--indigo); color: var(--ivory); border-color: var(--indigo);
}
.filter-bar { display: flex; gap: 12px; flex-wrap: wrap; margin-bottom: 20px; }
</style>

<?php include 'includes/footer.php'; ?>
