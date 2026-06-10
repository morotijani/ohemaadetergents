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

<div class="container-fluid px-0 mt-5 pt-4">
    <div class="row g-0">
        
        <!-- Gallery Section (Left) -->
        <div class="col-lg-7 border-end border-light">
            <div class="d-flex flex-column">
                <nav aria-label="breadcrumb" class="p-4 p-lg-5 pb-0">
                    <ol class="breadcrumb bg-transparent p-0 m-0">
                        <li class="breadcrumb-item"><a href="index" class="font-sans text-uppercase text-muted text-decoration-none" style="font-size: 0.65rem; letter-spacing: 0.1em;">Home</a></li>
                        <li class="breadcrumb-item"><a href="shop" class="font-sans text-uppercase text-muted text-decoration-none" style="font-size: 0.65rem; letter-spacing: 0.1em;">Collection</a></li>
                        <li class="breadcrumb-item active font-sans text-uppercase text-black fw-600" aria-current="page" style="font-size: 0.65rem; letter-spacing: 0.1em;"><?php echo htmlspecialchars($product['name']); ?></li>
                    </ol>
                </nav>

                <div class="p-4 p-lg-5 text-center bg-off-white">
                    <img id="mainProductImage" src="<?php echo htmlspecialchars($allImages[0]); ?>" class="img-fluid" alt="<?php echo htmlspecialchars($product['name']); ?>" style="max-height: 70vh; object-fit: contain;">
                </div>
                
                <?php if (count($allImages) > 1): ?>
                <div class="d-flex gap-3 overflow-auto p-4 p-lg-5 border-top border-light scrollbar-hidden">
                    <?php foreach ($allImages as $idx => $imgUrl): ?>
                        <div class="cursor-pointer border <?php echo $idx === 0 ? 'border-black' : 'border-light'; ?> p-2 bg-off-white product-thumbnail-wrapper" 
                             style="width: 100px; height: 100px; flex-shrink: 0;"
                             onclick="updateGallery(this, '<?php echo htmlspecialchars($imgUrl); ?>')">
                            <img src="<?php echo htmlspecialchars($imgUrl); ?>" class="w-100 h-100 object-fit-contain" alt="Thumbnail">
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Info Section (Right) -->
        <div class="col-lg-5">
            <div class="sticky-top p-4 p-lg-5" style="top: 80px;">
                <div class="mb-5">
                    <h1 class="font-serif text-black mb-3" style="font-size: 3rem; line-height: 1.1;"><?php echo htmlspecialchars($product['name']); ?></h1>
                    <?php if($product['category_name']): ?>
                        <p class="font-sans text-muted text-uppercase letter-spacing-wide mb-4" style="font-size: 0.75rem;"><?php echo htmlspecialchars($product['category_name']); ?></p>
                    <?php endif; ?>
                    
                    <div class="font-sans fw-600 text-black mb-4" style="font-size: 1.25rem;">
                        GHS <?php echo number_format($product['price'], 2); ?>
                    </div>
                    
                    <p class="font-sans text-muted mb-5" style="font-size: 0.85rem; line-height: 1.8;">
                        <?php echo nl2br(htmlspecialchars($product['description'])); ?>
                    </p>
                </div>

                <div class="d-flex flex-column gap-4">
                    <div class="d-flex border border-black" style="width: fit-content;">
                        <button class="btn btn-link text-black px-3 py-2 border-0 text-decoration-none rounded-0" type="button" onclick="document.getElementById('qty').stepDown()">-</button>
                        <input type="number" id="qty" class="form-control border-0 text-center rounded-0 font-sans fw-600 px-0 bg-transparent" value="1" min="1" max="<?php echo $product['stock']; ?>" style="width: 60px; box-shadow: none;">
                        <button class="btn btn-link text-black px-3 py-2 border-0 text-decoration-none rounded-0" type="button" onclick="document.getElementById('qty').stepUp()">+</button>
                    </div>

                    <?php if ($product['stock'] > 0): ?>
                        <button class="btn btn-black w-100 py-3 mt-3" onclick="addToCart(<?php echo $product['id']; ?>, document.getElementById('qty').value)">
                            Add to bag
                        </button>
                    <?php else: ?>
                        <button class="btn btn-outline-black w-100 py-3 mt-3" disabled>
                            Out of Stock
                        </button>
                    <?php endif; ?>
                </div>

                <div class="mt-5 pt-5 border-top border-light">
                    <h6 class="font-sans text-uppercase letter-spacing-wide text-black mb-4 fw-600" style="font-size: 0.75rem;">Shipping & Returns</h6>
                    <p class="font-sans text-muted mb-0" style="font-size: 0.75rem;">Complimentary shipping and returns on all orders over GHS 500. Read more about our return policy.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function updateGallery(el, src) {
    const mainImg = document.getElementById('mainProductImage');
    mainImg.style.opacity = '0';
    setTimeout(() => {
        mainImg.src = src;
        mainImg.style.opacity = '1';
    }, 200);
    
    document.querySelectorAll('.product-thumbnail-wrapper').forEach(item => {
        item.classList.remove('border-black');
        item.classList.add('border-light');
    });
    el.classList.remove('border-light');
    el.classList.add('border-black');
}
</script>

<?php include 'includes/footer.php'; ?>
