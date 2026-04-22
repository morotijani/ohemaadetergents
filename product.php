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
                          WHERE p.slug = ?");
    $stmt->execute([$slug]);
    $product = $stmt->fetch();
    
    if (!$product) {
        header("Location: shop");
        exit;
    }
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

<div class="bg-white py-5 mb-5 border-bottom">
    <div class="container checkout-mobile-spacing">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index" class="text-decoration-none text-muted">Home</a></li>
                <li class="breadcrumb-item"><a href="shop" class="text-decoration-none text-muted">Shop</a></li>
                <?php if($product['category_name']): ?>
                    <li class="breadcrumb-item"><a href="shop?category=<?php echo urlencode(strtolower($product['category_name'])); ?>" class="text-decoration-none text-muted"><?php echo htmlspecialchars($product['category_name']); ?></a></li>
                <?php endif; ?>
                <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($product['name']); ?></li>
            </ol>
        </nav>

        <div class="row g-5 mt-2">
            <div class="col-md-6">
                <div class="position-relative bg-light rounded-4 p-4 text-center">
                    <span class="badge bg-gold position-absolute top-0 start-0 m-4 py-2 px-3 shadow-sm rounded-pill d-flex align-items-center" style="background-color: var(--ohemaa-gold); color: white;">
                        <i class="bi bi-shield-check me-2"></i> Clinical Grade
                    </span>
                    <img id="mainProductImage" src="<?php echo htmlspecialchars($allImages[0]); ?>" class="img-fluid rounded-3 object-fit-cover" style="max-height: 500px; width:100%; transition: opacity 0.3s;" alt="<?php echo htmlspecialchars($product['name']); ?>">
                    
                    <?php if (count($allImages) > 1): ?>
                    <div class="d-flex justify-content-center flex-wrap gap-2 mt-4">
                        <?php foreach ($allImages as $idx => $imgUrl): ?>
                            <img src="<?php echo htmlspecialchars($imgUrl); ?>" class="product-thumbnail rounded object-fit-cover border <?php echo $idx === 0 ? 'border-primary' : 'border-secondary'; ?>" style="width: 70px; height: 70px; cursor: pointer; border-width: 2px !important;" onclick="document.getElementById('mainProductImage').src=this.src; document.querySelectorAll('.product-thumbnail').forEach(el=>el.classList.replace('border-primary','border-secondary')); this.classList.replace('border-secondary','border-primary');">
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-md-6">
                <h1 class="display-5 fw-bold mb-3"><?php echo htmlspecialchars($product['name']); ?></h1>
                
                <div class="fs-2 fw-bold text-primary mb-4" style="color: var(--ohemaa-blue) !important;">
                    GHS <?php echo number_format($product['price'], 2); ?>
                </div>

                <div class="mb-4">
                    <p class="lead text-muted fs-6" style="line-height: 1.8;">
                        <?php echo nl2br(htmlspecialchars($product['description'])); ?>
                    </p>
                </div>

                <div class="d-flex align-items-center gap-3 mb-4">
                    <?php if ($product['stock'] > 0): ?>
                        <span class="badge bg-success-subtle text-success py-2 px-3 rounded-pill"><i class="bi bi-check-circle me-1"></i> In Stock</span>
                    <?php else: ?>
                        <span class="badge bg-danger-subtle text-danger py-2 px-3 rounded-pill"><i class="bi bi-x-circle me-1"></i> Out of Stock</span>
                    <?php endif; ?>
                </div>

                <div class="d-none d-md-flex align-items-center gap-3 mt-5">
                    <div class="input-group" style="width: 130px;">
                        <button class="btn btn-outline-secondary" type="button" onclick="document.getElementById('qty').stepDown()">-</button>
                        <input type="number" id="qty" class="form-control text-center" value="1" min="1" max="<?php echo $product['stock']; ?>">
                        <button class="btn btn-outline-secondary" type="button" onclick="document.getElementById('qty').stepUp()">+</button>
                    </div>
                    <button class="btn btn-gold btn-lg rounded-pill px-5 flex-grow-1" onclick="addToCart(<?php echo $product['id']; ?>, document.getElementById('qty').value)" <?php echo $product['stock'] <= 0 ? 'disabled' : ''; ?>>
                        <i class="bi bi-cart-plus me-2"></i> Add to Cart
                    </button>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- Mobile Sticky CTA -->
<div class="mobile-sticky-cta active d-md-none justify-content-between align-items-center">
    <div class="fw-bold fs-5" style="color: var(--ohemaa-blue);">GHS <?php echo number_format($product['price'], 2); ?></div>
    <button class="btn btn-gold rounded-pill px-4" onclick="addToCart(<?php echo $product['id']; ?>, 1)" <?php echo $product['stock'] <= 0 ? 'disabled' : ''; ?>>
        <i class="bi bi-cart-plus me-1"></i> Add to Cart
    </button>
</div>

<?php include 'includes/footer.php'; ?>
