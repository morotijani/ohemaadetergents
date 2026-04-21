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
              LEFT JOIN categories c ON p.category_id = c.id";

    if (!empty($categoryFilter)) {
        $query .= " WHERE c.slug = ?";
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

<div class="bg-light py-4 mb-5 border-bottom">
    <div class="container">
        <h1 class="mb-0 fs-2">Shop Our Products</h1>
    </div>
</div>

<div class="container mb-5 pb-5">
    <div class="row">
        
        <div class="col-lg-3 mb-4 mb-lg-0">
            <div class="bg-white p-4 border rounded-3">
                <h4 class="sidebar-title">Categories</h4>
                <a href="shop.php" class="category-link <?php echo empty($categoryFilter) ? 'active' : ''; ?>">All Products</a>
                <?php foreach ($categories as $cat): ?>
                    <a href="shop.php?category=<?php echo urlencode($cat['slug']); ?>" 
                       class="category-link <?php echo ($categoryFilter === $cat['slug']) ? 'active' : ''; ?>">
                        <?php echo htmlspecialchars($cat['name']); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="col-lg-9">
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <p class="text-muted mb-0">Showing <?php echo count($products); ?> results</p>
            </div>

            <div class="row g-4">
                <?php if (empty($products)): ?>
                    <div class="col-12 text-center py-5">
                        <i class="bi bi-box-seam fs-1 text-muted mb-3 d-block"></i>
                        <h4 class="text-muted">No products found.</h4>
                        <a href="shop.php" class="btn btn-gold mt-3">Clear Filters</a>
                    </div>
                <?php else: ?>
                    <?php foreach ($products as $product): ?>
                        <div class="col-6 col-md-4">
                            <div class="product-card d-flex flex-column bg-white">
                                <?php 
                                $img = $product['image_url'] ? $product['image_url'] : 'https://via.placeholder.com/300x300?text=No+Image';
                                ?>
                                <div class="position-relative">
                                    <img src="<?php echo htmlspecialchars($img); ?>" class="product-image img-fluid" alt="<?php echo htmlspecialchars($product['name']); ?>">
                                    <?php if($product['category_name']): ?>
                                        <span class="badge bg-light text-dark position-absolute top-0 end-0 m-2 shadow-sm border">
                                            <?php echo htmlspecialchars($product['category_name']); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="p-3 p-md-4 d-flex flex-column flex-grow-1">
                                    <h5 class="mb-2 fs-6 fs-md-5 text-truncate" title="<?php echo htmlspecialchars($product['name']); ?>">
                                        <?php echo htmlspecialchars($product['name']); ?>
                                    </h5>
                                    <div class="mt-auto">
                                        <div class="price-tag mb-3">GHS <?php echo number_format($product['price'], 2); ?></div>
                                        <button class="btn btn-outline-gold w-100 rounded-pill" onclick="addToCart(<?php echo $product['id']; ?>)">
                                            <i class="bi bi-cart-plus me-2"></i>Add to Cart
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
