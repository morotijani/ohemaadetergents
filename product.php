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

    // Fetch approved reviews
    $stmt = $db->prepare("SELECT pr.rating, pr.comment, pr.created_at, c.first_name, c.last_name 
                          FROM product_reviews pr 
                          JOIN customers c ON pr.customer_id = c.id 
                          WHERE pr.product_id = ? AND pr.status = 'approved' 
                          ORDER BY pr.created_at DESC");
    $stmt->execute([$product['id']]);
    $reviews = $stmt->fetchAll();

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
                <div class="p-4 p-lg-5 pb-0 font-sans text-uppercase d-flex align-items-center flex-wrap gap-2" style="font-size: 0.65rem; letter-spacing: 0.1em;">
                    <a href="index" class="text-muted text-decoration-none hover-text-black">Home</a>
                    <span class="text-muted" style="font-size: 0.8em;">/</span>
                    <a href="shop" class="text-muted text-decoration-none hover-text-black">Collection</a>
                    <span class="text-muted" style="font-size: 0.8em;">/</span>
                    <span class="text-black fw-600"><?php echo htmlspecialchars($product['name']); ?></span>
                </div>

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

    <!-- Reviews Section -->
    <div class="row g-0 mt-5 pt-5 border-top border-light">
        <div class="col-lg-8 offset-lg-2 p-4 p-lg-5">
            <h3 class="font-serif text-black mb-5 text-center" style="font-size: 2rem;">Client Reviews</h3>
            
            <div id="reviews-container">
                <?php if (empty($reviews)): ?>
                    <p class="text-center font-sans text-muted mb-5">Be the first to review this product.</p>
                <?php else: ?>
                    <?php foreach ($reviews as $rev): ?>
                    <div class="review-item mb-5 pb-5 border-bottom border-light">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="font-sans text-uppercase letter-spacing-wide text-black fw-600" style="font-size: 0.75rem;">
                                <?php echo htmlspecialchars($rev['first_name'] . ' ' . substr($rev['last_name'], 0, 1) . '.'); ?>
                            </span>
                            <span class="text-black" style="font-size: 0.8rem; letter-spacing: 0.2em;">
                                <?php echo str_repeat('&#9733;', $rev['rating']) . str_repeat('&#9734;', 5 - $rev['rating']); ?>
                            </span>
                        </div>
                        <p class="font-sans text-muted mb-0 fw-300" style="font-size: 0.9rem; line-height: 1.8;">
                            "<?php echo nl2br(htmlspecialchars($rev['comment'])); ?>"
                        </p>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="text-center mt-5">
                <button type="button" class="btn btn-outline-black rounded-0 px-5 py-3 font-sans text-uppercase letter-spacing-wide" style="font-size: 0.75rem; border-width: 1px;" data-bs-toggle="modal" data-bs-target="#reviewModal">
                    Leave a Review
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Review Modal -->
<div class="modal fade" id="reviewModal" tabindex="-1" aria-labelledby="reviewModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered rounded-0">
    <div class="modal-content rounded-0 border-0 shadow-lg">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title font-serif" id="reviewModalLabel">Leave a Review</h5>
        <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <form id="reviewForm">
            <div id="reviewAlert" class="alert d-none font-sans" style="font-size: 0.8rem;"></div>
            <div class="mb-3">
                <label class="form-label font-sans text-uppercase letter-spacing-wide text-muted" style="font-size: 0.65rem;">Name</label>
                <input type="text" id="revName" class="form-control rounded-0 shadow-none font-sans" required>
            </div>
            <div class="mb-3">
                <label class="form-label font-sans text-uppercase letter-spacing-wide text-muted" style="font-size: 0.65rem;">Email</label>
                <input type="email" id="revEmail" class="form-control rounded-0 shadow-none font-sans" required>
            </div>
            <div class="mb-3">
                <label class="form-label font-sans text-uppercase letter-spacing-wide text-muted" style="font-size: 0.65rem;">Rating (1-5)</label>
                <select id="revRating" class="form-select rounded-0 shadow-none font-sans" required>
                    <option value="5">5 - Excellent</option>
                    <option value="4">4 - Very Good</option>
                    <option value="3">3 - Average</option>
                    <option value="2">2 - Poor</option>
                    <option value="1">1 - Terrible</option>
                </select>
            </div>
            <div class="mb-4">
                <label class="form-label font-sans text-uppercase letter-spacing-wide text-muted" style="font-size: 0.65rem;">Review</label>
                <textarea id="revComment" class="form-control rounded-0 shadow-none font-sans" rows="4" required></textarea>
            </div>
            <button type="submit" id="submitReviewBtn" class="btn btn-black w-100 rounded-0 py-3 font-sans text-uppercase letter-spacing-wide" style="font-size: 0.75rem;">Submit Review</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
document.getElementById('reviewForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('submitReviewBtn');
    const alertBox = document.getElementById('reviewAlert');
    btn.disabled = true;
    btn.innerText = 'Submitting...';
    alertBox.classList.add('d-none');
    alertBox.classList.remove('alert-success', 'alert-danger');

    const data = {
        product_id: <?php echo $product['id']; ?>,
        name: document.getElementById('revName').value,
        email: document.getElementById('revEmail').value,
        rating: document.getElementById('revRating').value,
        comment: document.getElementById('revComment').value
    };

    try {
        const res = await fetch('/ohemaadetergents/api/reviews/create.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        const result = await res.json();
        
        alertBox.classList.remove('d-none');
        if(res.ok) {
            alertBox.classList.add('alert-success');
            alertBox.innerText = result.message || 'Review submitted successfully.';
            document.getElementById('reviewForm').reset();
        } else {
            alertBox.classList.add('alert-danger');
            alertBox.innerText = result.message || 'Error submitting review.';
        }
    } catch(err) {
        alertBox.classList.remove('d-none');
        alertBox.classList.add('alert-danger');
        alertBox.innerText = 'Network error. Please try again.';
    } finally {
        btn.disabled = false;
        btn.innerText = 'Submit Review';
    }
});

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
