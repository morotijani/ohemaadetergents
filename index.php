<?php
require_once __DIR__ . '/src/Database.php';
use App\Database;

try {
    $db = Database::getInstance()->getConnection();

    $stmt = $db->query("SELECT id, name, slug, price, image_url FROM products WHERE is_featured = 1 ORDER BY created_at DESC LIMIT 4");
    $featuredProducts = $stmt->fetchAll();

    if (empty($featuredProducts)) {
        $stmt = $db->query("SELECT id, name, slug, price, image_url FROM products ORDER BY created_at DESC LIMIT 4");
        $featuredProducts = $stmt->fetchAll();
    }
} catch (Exception $e) {
    $featuredProducts = [];
}

include 'includes/header.php';
?>

<section class="hero-section">
    <div class="container py-5">
        <h1 class="display-4 fw-bold mb-4">Discover True Brightness</h1>
        <p class="lead mb-5 opacity-75 mx-auto" style="max-width: 600px;">Experience the power of Ohemaa Detergents. Tough on stains, gentle on fabrics.</p>
        <a href="shop.php" class="btn btn-gold btn-lg px-5 py-3 rounded-pill">Shop Now</a>
    </div>
</section>

<section class="container py-5 my-5">
    <div class="text-center mb-5">
        <h2 class="mb-3">Featured Products</h2>
        <div style="width: 60px; height: 3px; background-color: var(--ohemaa-gold); margin: 0 auto;"></div>
    </div>

    <div class="row g-4">
        <?php if (empty($featuredProducts)): ?>
            <div class="col-12 text-center text-muted">
                <p>No products available yet.</p>
            </div>
        <?php else: ?>
            <?php foreach ($featuredProducts as $product): ?>
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="product-card d-flex flex-column bg-white">
                        <?php 
                        $img = $product['image_url'] ? $product['image_url'] : 'https://via.placeholder.com/300x300?text=No+Image';
                        ?>
                        <img src="<?php echo htmlspecialchars($img); ?>" class="product-image img-fluid" alt="<?php echo htmlspecialchars($product['name']); ?>">
                        
                        <div class="p-3 p-md-4 d-flex flex-column flex-grow-1">
                            <h5 class="mb-2 fs-6 fs-md-5 text-truncate"><?php echo htmlspecialchars($product['name']); ?></h5>
                            <div class="mt-auto">
                                <div class="price-tag mb-3">GHS <?php echo number_format($product['price'], 2); ?></div>
                                <button class="btn btn-outline-gold w-100 rounded-pill" onclick="addToCart(<?php echo $product['id']; ?>)">
                                    <i class="bi bi-cart-plus me-2"></i>Add
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    
    <div class="text-center mt-5">
        <a href="shop.php" class="btn btn-outline-gold px-4 py-2 rounded-pill">View All Products</a>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
