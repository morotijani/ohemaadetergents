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

include 'includes/header.php';
?>

<div class="bg-gold-soft py-5 mt-5">
    <div class="container py-4 text-center">
        <span class="badge-category mb-2">Our Collection</span>
        <h1 class="display-4 fw-800 mb-0">The Ohemaa Shop</h1>
    </div>
</div>

<div class="container py-5 mb-5">
    <div class="row">
        
        <!-- Sidebar Filters -->
        <div class="col-lg-3 mb-5 mb-lg-0">
            <div class="glass p-4 rounded-lg sticky-top" style="top: 100px; border-radius: var(--radius-lg);">
                <h5 class="fw-bold mb-4 d-flex align-items-center">
                    <i class="bi bi-filter-left me-2 text-gold"></i> Categories
                </h5>
                <div class="d-flex flex-column gap-2">
                    <a href="shop" class="category-link <?php echo empty($categoryFilter) ? 'active' : ''; ?> p-2 px-3 rounded-md text-decoration-none d-flex justify-content-between align-items-center">
                        All Products <i class="bi bi-chevron-right small opacity-50"></i>
                    </a>
                    <?php foreach ($categories as $cat): ?>
                        <a href="shop?category=<?php echo urlencode($cat['slug']); ?>" 
                           class="category-link <?php echo ($categoryFilter === $cat['slug']) ? 'active' : ''; ?> p-2 px-3 rounded-md text-decoration-none d-flex justify-content-between align-items-center">
                            <?php echo htmlspecialchars($cat['name']); ?>
                            <i class="bi bi-chevron-right small opacity-50"></i>
                        </a>
                    <?php endforeach; ?>
                </div>

                <hr class="my-4 opacity-10">

                <h5 class="fw-bold mb-3">Filter by Price</h5>
                <div class="px-2">
                    <input type="range" class="form-range custom-range" min="0" max="500" id="priceRange">
                    <div class="d-flex justify-content-between small text-muted mt-2">
                        <span>GHS 0</span>
                        <span>GHS 500</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Product Grid -->
        <div class="col-lg-9">
            
            <div class="d-flex justify-content-between align-items-center mb-5 pb-3 border-bottom">
                <p class="text-muted mb-0">Showing <span class="fw-bold text-primary"><?php echo count($products); ?></span> exquisite products</p>
                <div class="dropdown">
                    <button class="btn btn-outline-light text-dark btn-sm dropdown-toggle border" type="button" data-bs-toggle="dropdown">
                        Sort by: Latest
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                        <li><a class="dropdown-menu-item" href="#">Price: Low to High</a></li>
                        <li><a class="dropdown-menu-item" href="#">Price: High to Low</a></li>
                        <li><a class="dropdown-menu-item" href="#">Newest First</a></li>
                    </ul>
                </div>
            </div>

            <div class="row g-4">
                <?php if (empty($products)): ?>
                    <div class="col-12 text-center py-5">
                        <div class="bg-light p-5 rounded-lg d-inline-block">
                            <i class="bi bi-search fs-1 text-muted mb-3 d-block"></i>
                            <h4 class="text-dark fw-bold">No matches found</h4>
                            <p class="text-muted mb-4">Try adjusting your filters or search terms.</p>
                            <a href="shop" class="btn btn-gold px-4">Clear All Filters</a>
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($products as $product): ?>
                        <div class="col-6 col-md-4 reveal">
                            <div class="product-card">
                                <?php 
                                $img = $product['image_url'] ? $product['image_url'] : 'https://via.placeholder.com/600x600?text=Ohemaa+Product';
                                ?>
                                <div class="product-image-wrapper">
                                    <a href="product?slug=<?php echo urlencode($product['slug']); ?>">
                                        <img src="<?php echo htmlspecialchars($img); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                                    </a>
                                    <?php if($product['category_name']): ?>
                                        <span class="badge bg-white text-primary position-absolute top-0 end-0 m-3 shadow-sm border-0 small fw-bold px-3 py-2 rounded-pill" style="z-index: 2;">
                                            <?php echo htmlspecialchars($product['category_name']); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="p-4 d-flex flex-column flex-grow-1">
                                    <a href="product?slug=<?php echo urlencode($product['slug']); ?>" class="text-decoration-none">
                                        <h5 class="mb-3 fs-5 text-truncate" title="<?php echo htmlspecialchars($product['name']); ?>">
                                            <?php echo htmlspecialchars($product['name']); ?>
                                        </h5>
                                    </a>
                                    <div class="mt-auto">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <span class="price-tag">GHS <?php echo number_format($product['price'], 2); ?></span>
                                        </div>
                                        <button class="btn btn-gold w-100 rounded-pill py-2" onclick="addToCart(<?php echo $product['id']; ?>)">
                                            <i class="bi bi-bag-plus me-2"></i>Add to Bag
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
