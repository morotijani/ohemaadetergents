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

<div class="container-fluid px-4 px-lg-5 pt-5 mt-5">
    <div class="row pt-5 mb-5 pb-5 border-bottom border-light">
        <div class="col-lg-12 text-center">
            <h1 class="font-serif text-black" style="font-size: 3.5rem;">Collection</h1>
            <p class="font-sans text-muted letter-spacing-wide text-uppercase" style="font-size: 0.75rem;">Objects of Purity</p>
        </div>
    </div>
</div>

<div class="container-fluid px-4 px-lg-5 mb-5 pb-5">
    <div class="row">
        
        <!-- Sidebar Filters -->
        <div class="col-lg-2 mb-5 mb-lg-0 pe-lg-4 border-end border-light">
            <div class="sticky-top" style="top: 100px;">
                <h5 class="font-sans text-uppercase letter-spacing-widest text-black mb-4 fw-600" style="font-size: 0.75rem;">
                    Categories
                </h5>
                <div class="d-flex flex-column gap-2 mb-5">
                    <a href="shop" class="font-sans text-uppercase text-decoration-none" style="font-size: 0.75rem; letter-spacing: 0.05em; color: <?php echo empty($categoryFilter) ? 'var(--black)' : 'var(--grey)'; ?>; font-weight: <?php echo empty($categoryFilter) ? '600' : '400'; ?>;">
                        All Products
                    </a>
                    <?php foreach ($categories as $cat): ?>
                        <a href="shop?category=<?php echo urlencode($cat['slug']); ?>" 
                           class="font-sans text-uppercase text-decoration-none" style="font-size: 0.75rem; letter-spacing: 0.05em; color: <?php echo ($categoryFilter === $cat['slug']) ? 'var(--black)' : 'var(--grey)'; ?>; font-weight: <?php echo ($categoryFilter === $cat['slug']) ? '600' : '400'; ?>;">
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </a>
                    <?php endforeach; ?>
                </div>

                <h5 class="font-sans text-uppercase letter-spacing-widest text-black mb-3 fw-600" style="font-size: 0.75rem;">Filter</h5>
                <div>
                    <input type="range" class="form-range custom-range" min="0" max="500" id="priceRange">
                    <div class="d-flex justify-content-between font-sans text-muted mt-2" style="font-size: 0.7rem;">
                        <span>0</span>
                        <span>500+</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Product Grid -->
        <div class="col-lg-10 ps-lg-5">
            
            <div class="d-flex justify-content-between align-items-center mb-5">
                <p class="font-sans text-muted mb-0" style="font-size: 0.75rem;">
                    <?php echo count($products); ?> Products
                </p>
                <div class="dropdown">
                    <button class="btn btn-link-dark dropdown-toggle p-0 border-0" type="button" data-bs-toggle="dropdown" style="font-size: 0.75rem;">
                        Sort by: Latest
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end rounded-0 border-black shadow-none">
                        <li><a class="dropdown-item font-sans text-uppercase" style="font-size: 0.7rem;" href="#">Price: Low to High</a></li>
                        <li><a class="dropdown-item font-sans text-uppercase" style="font-size: 0.7rem;" href="#">Price: High to Low</a></li>
                        <li><a class="dropdown-item font-sans text-uppercase" style="font-size: 0.7rem;" href="#">Newest First</a></li>
                    </ul>
                </div>
            </div>

            <div class="row g-0">
                <?php if (empty($products)): ?>
                    <div class="col-12 text-center py-5">
                        <div class="p-5 d-inline-block border border-light">
                            <p class="font-sans text-muted mb-4 text-uppercase letter-spacing-wide" style="font-size: 0.75rem;">No formulations found.</p>
                            <a href="shop" class="btn-link-dark">Clear All Filters</a>
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($products as $index => $product): ?>
                        <div class="col-6 col-md-6 col-lg-4 reveal border-end border-bottom border-light">
                            <div class="product-card h-100">
                                <?php 
                                $img = $product['image_url'] ? $product['image_url'] : 'https://via.placeholder.com/600x600?text=Ohemaa';
                                ?>
                                <div class="product-image-wrapper bg-transparent">
                                    <a href="product?slug=<?php echo urlencode($product['slug']); ?>">
                                        <img src="<?php echo htmlspecialchars($img); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                                    </a>
                                </div>
                                
                                <div class="p-3 text-center">
                                    <a href="product?slug=<?php echo urlencode($product['slug']); ?>" class="text-decoration-none">
                                        <h5 class="product-title" title="<?php echo htmlspecialchars($product['name']); ?>">
                                            <?php echo htmlspecialchars($product['name']); ?>
                                        </h5>
                                    </a>
                                    <div class="mt-auto">
                                        <div class="product-price mb-3">
                                            GHS <?php echo number_format($product['price'], 2); ?>
                                        </div>
                                        <button class="btn btn-link-dark w-100 py-2 border-0" onclick="addToCart(<?php echo $product['id']; ?>)">
                                            + Add to Cart
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
