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

<div class="container py-5 mt-5">
    <div class="row pt-4">
        <div class="col-12">
            <nav aria-label="breadcrumb" class="mb-4 reveal">
                <ol class="breadcrumb bg-transparent p-0">
                    <li class="breadcrumb-item"><a href="index" class="text-decoration-none text-muted">Home</a></li>
                    <li class="breadcrumb-item"><a href="shop" class="text-decoration-none text-muted">Collections</a></li>
                    <?php if($product['category_name']): ?>
                        <li class="breadcrumb-item"><a href="shop?category=<?php echo urlencode(strtolower($product['category_name'])); ?>" class="text-decoration-none text-muted"><?php echo htmlspecialchars($product['category_name']); ?></a></li>
                    <?php endif; ?>
                    <li class="breadcrumb-item active fw-bold text-primary" aria-current="page"><?php echo htmlspecialchars($product['name']); ?></li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row g-5">
        <!-- Gallery Section -->
        <div class="col-lg-7 reveal">
            <div class="position-relative bg-white rounded-lg p-3 shadow-sm" style="border-radius: var(--radius-lg); border: 1px solid rgba(0,0,0,0.05);">
                <div class="position-absolute top-0 start-0 m-4 z-index-2">
                    <span class="badge-category bg-white shadow-sm">Premium Quality</span>
                </div>
                
                <div class="main-image-container overflow-hidden rounded-md mb-3" style="aspect-ratio: 1/1;">
                    <img id="mainProductImage" src="<?php echo htmlspecialchars($allImages[0]); ?>" class="img-fluid w-100 h-100 object-fit-cover transition-base" alt="<?php echo htmlspecialchars($product['name']); ?>">
                </div>
                
                <?php if (count($allImages) > 1): ?>
                <div class="d-flex gap-3 overflow-auto pb-2 scrollbar-hidden">
                    <?php foreach ($allImages as $idx => $imgUrl): ?>
                        <div class="product-thumbnail-wrapper p-1 rounded-md border-2 cursor-pointer <?php echo $idx === 0 ? 'border-gold' : 'border-transparent'; ?>" 
                             style="width: 80px; height: 80px; flex-shrink: 0;"
                             onclick="updateGallery(this, '<?php echo htmlspecialchars($imgUrl); ?>')">
                            <img src="<?php echo htmlspecialchars($imgUrl); ?>" class="w-100 h-100 object-fit-cover rounded-sm" alt="Thumbnail">
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Info Section -->
        <div class="col-lg-5 reveal" style="animation-delay: 0.2s;">
            <div class="ps-lg-4">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <?php if ($product['stock'] > 0): ?>
                        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill small fw-bold">
                            <i class="bi bi-check-circle-fill me-1"></i> In Stock & Ready to Ship
                        </span>
                    <?php else: ?>
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-2 rounded-pill small fw-bold">
                            <i class="bi bi-x-circle-fill me-1"></i> Currently Out of Stock
                        </span>
                    <?php endif; ?>
                </div>

                <h1 class="display-5 fw-800 mb-3 text-primary"><?php echo htmlspecialchars($product['name']); ?></h1>
                
                <div class="d-flex align-items-center gap-3 mb-4">
                    <div class="fs-2 fw-800 text-gold">
                        GHS <?php echo number_format($product['price'], 2); ?>
                    </div>
                    <div class="text-muted small">Tax included. Shipping calculated at checkout.</div>
                </div>

                <hr class="my-4 opacity-10">

                <div class="mb-5">
                    <h6 class="fw-bold text-uppercase letter-spacing-1 mb-3">The Experience</h6>
                    <p class="text-muted fs-6" style="line-height: 1.8;">
                        <?php echo nl2br(htmlspecialchars($product['description'])); ?>
                    </p>
                </div>

                <div class="d-flex flex-column gap-4">
                    <div class="d-flex align-items-center gap-3">
                        <label class="fw-bold small text-uppercase">Quantity</label>
                        <div class="input-group glass p-1 rounded-pill" style="width: 140px;">
                            <button class="btn btn-link text-dark p-0 px-3 text-decoration-none" type="button" onclick="document.getElementById('qty').stepDown()">-</button>
                            <input type="number" id="qty" class="form-control bg-transparent border-0 text-center fw-bold" value="1" min="1" max="<?php echo $product['stock']; ?>" style="box-shadow: none;">
                            <button class="btn btn-link text-dark p-0 px-3 text-decoration-none" type="button" onclick="document.getElementById('qty').stepUp()">+</button>
                        </div>
                    </div>

                    <div class="d-grid gap-3">
                        <button class="btn btn-gold btn-lg py-3 rounded-pill" onclick="addToCart(<?php echo $product['id']; ?>, document.getElementById('qty').value)" <?php echo $product['stock'] <= 0 ? 'disabled' : ''; ?>>
                            <i class="bi bi-bag-plus-fill me-2"></i> Add to Shopping Bag
                        </button>
                        <button class="btn btn-outline-primary btn-lg py-3 rounded-pill" <?php echo $product['stock'] <= 0 ? 'disabled' : ''; ?>>
                            Buy It Now
                        </button>
                    </div>
                </div>

                <!-- Product Features List -->
                <div class="mt-5 pt-4 border-top">
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="d-flex align-items-center gap-2 small">
                                <i class="bi bi-truck text-gold"></i>
                                <span>Fast Delivery</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="d-flex align-items-center gap-2 small">
                                <i class="bi bi-arrow-repeat text-gold"></i>
                                <span>Easy Returns</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="d-flex align-items-center gap-2 small">
                                <i class="bi bi-shield-check text-gold"></i>
                                <span>Secure Payment</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="d-flex align-items-center gap-2 small">
                                <i class="bi bi-award text-gold"></i>
                                <span>Original Brand</span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Reviews Section -->
    <div class="row mt-5 pt-5 reveal">
        <div class="col-lg-12">
            <hr class="mb-5 opacity-10">
            <div class="d-flex justify-content-between align-items-center mb-5">
                <div>
                    <h2 class="fw-800 text-primary mb-1">Customer Reviews</h2>
                    <p class="text-muted small mb-0">Discover what others are saying about this product.</p>
                </div>
                <button class="btn btn-outline-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#reviewModal">
                    <i class="bi bi-pencil-square me-2"></i> Write a Review
                </button>
            </div>

            <div class="row g-4" id="reviewsContainer">
                <?php
                $stmt = $db->prepare("SELECT r.*, CONCAT(c.first_name, ' ', c.last_name) as customer_name 
                                    FROM product_reviews r 
                                    JOIN customers c ON r.customer_id = c.id 
                                    WHERE r.product_id = ? AND r.status = 'approved' 
                                    ORDER BY r.created_at DESC");
                $stmt->execute([$product['id']]);
                $reviews = $stmt->fetchAll();

                if ($reviews):
                    foreach ($reviews as $rev):
                ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="glass p-4 rounded-lg h-100 border-0 shadow-sm transition-base hover-lift">
                            <div class="d-flex align-items-center gap-2 mb-3 text-gold">
                                <?php for($i=1; $i<=5; $i++): ?>
                                    <i class="bi bi-star<?php echo $i <= $rev['rating'] ? '-fill' : ''; ?>"></i>
                                <?php endfor; ?>
                            </div>
                            <p class="text-muted small mb-4" style="line-height: 1.6; min-height: 60px;">
                                "<?php echo nl2br(htmlspecialchars($rev['comment'])); ?>"
                            </p>
                            <div class="d-flex align-items-center gap-3 border-top pt-3">
                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold small" style="width: 32px; height: 32px;">
                                    <?php echo strtoupper(substr($rev['customer_name'], 0, 1)); ?>
                                </div>
                                <div>
                                    <h6 class="mb-0 small fw-bold"><?php echo htmlspecialchars($rev['customer_name']); ?></h6>
                                    <span class="text-muted" style="font-size: 0.7rem;"><?php echo date('M d, Y', strtotime($rev['created_at'])); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php 
                    endforeach;
                else: 
                ?>
                    <div class="col-12 text-center py-5">
                        <div class="opacity-25 mb-3">
                            <i class="bi bi-chat-heart display-1"></i>
                        </div>
                        <h5 class="text-muted fw-bold">No reviews yet</h5>
                        <p class="text-muted small">Be the first to share your experience!</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Review Submission Modal -->
<div class="modal fade" id="reviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content glass border-0 rounded-lg">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-800 text-primary">Share Your Experience</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="reviewForm">
                    <input type="hidden" id="reviewProductId" value="<?php echo $product['id']; ?>">
                    <div class="mb-4 text-center">
                        <label class="form-label d-block small fw-bold text-uppercase opacity-75 mb-3">Your Rating</label>
                        <div class="star-rating fs-2 text-gold cursor-pointer">
                            <i class="bi bi-star rating-star" data-rating="1"></i>
                            <i class="bi bi-star rating-star" data-rating="2"></i>
                            <i class="bi bi-star rating-star" data-rating="3"></i>
                            <i class="bi bi-star rating-star" data-rating="4"></i>
                            <i class="bi bi-star rating-star" data-rating="5"></i>
                        </div>
                        <input type="hidden" id="reviewRating" value="0">
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-uppercase opacity-75">Your Name</label>
                            <input type="text" id="reviewName" class="form-control glass border-0 p-3" required placeholder="John Doe">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-uppercase opacity-75">Email Address</label>
                            <input type="email" id="reviewEmail" class="form-control glass border-0 p-3" required placeholder="john@example.com">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-bold text-uppercase opacity-75">Your Review</label>
                        <textarea id="reviewComment" class="form-control glass border-0 p-3" rows="4" required placeholder="Tell us what you liked (or didn't like) about this product..."></textarea>
                    </div>

                    <button type="button" onclick="submitReview()" class="btn btn-gold w-100 py-3 rounded-pill fw-bold" id="submitReviewBtn">
                        Submit Review
                    </button>
                    <div id="reviewMessage" class="mt-3 small text-center d-none"></div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function updateGallery(el, src) {
    document.getElementById('mainProductImage').style.opacity = '0';
    setTimeout(() => {
        document.getElementById('mainProductImage').src = src;
        document.getElementById('mainProductImage').style.opacity = '1';
    }, 200);
    
    document.querySelectorAll('.product-thumbnail-wrapper').forEach(item => {
        item.classList.remove('border-gold');
        item.classList.add('border-transparent');
    });
    el.classList.remove('border-transparent');
    el.classList.add('border-gold');
}

// Review Star Logic
document.querySelectorAll('.rating-star').forEach(star => {
    star.addEventListener('mouseover', function() {
        const rating = this.dataset.rating;
        highlightStars(rating);
    });
    
    star.addEventListener('click', function() {
        const rating = this.dataset.rating;
        document.getElementById('reviewRating').value = rating;
        highlightStars(rating);
    });
});

document.querySelector('.star-rating').addEventListener('mouseleave', function() {
    const currentRating = document.getElementById('reviewRating').value;
    highlightStars(currentRating);
});

function highlightStars(rating) {
    document.querySelectorAll('.rating-star').forEach(star => {
        if (star.dataset.rating <= rating) {
            star.classList.remove('bi-star');
            star.classList.add('bi-star-fill');
        } else {
            star.classList.remove('bi-star-fill');
            star.classList.add('bi-star');
        }
    });
}

async function submitReview() {
    const btn = document.getElementById('submitReviewBtn');
    const msg = document.getElementById('reviewMessage');
    const rating = document.getElementById('reviewRating').value;
    const name = document.getElementById('reviewName').value;
    const email = document.getElementById('reviewEmail').value;
    const comment = document.getElementById('reviewComment').value;
    const productId = document.getElementById('reviewProductId').value;

    if (rating == 0 || !name || !email || !comment) {
        msg.innerText = 'Please fill in all fields and provide a rating.';
        msg.className = 'mt-3 small text-center text-danger';
        msg.classList.remove('d-none');
        return;
    }

    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Submitting...';

    try {
        const res = await fetch('/ohemaadetergents/api/reviews/create', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                product_id: productId,
                name: name,
                email: email,
                rating: rating,
                comment: comment
            })
        });
        const data = await res.json();
        
        msg.classList.remove('d-none');
        if (res.ok) {
            msg.innerText = data.message;
            msg.className = 'mt-3 small text-center text-success fw-bold';
            document.getElementById('reviewForm').reset();
            highlightStars(0);
            setTimeout(() => {
                bootstrap.Modal.getInstance(document.getElementById('reviewModal')).hide();
                msg.classList.add('d-none');
            }, 3000);
        } else {
            msg.innerText = data.message;
            msg.className = 'mt-3 small text-center text-danger';
        }
    } catch(e) {
        msg.innerText = 'Something went wrong. Please try again.';
        msg.className = 'mt-3 small text-center text-danger';
        msg.classList.remove('d-none');
    } finally {
        btn.disabled = false;
        btn.innerHTML = 'Submit Review';
    }
}
</script>

<!-- Mobile Sticky CTA -->
<div class="mobile-sticky-cta active d-md-none justify-content-between align-items-center glass">
    <div class="fw-800 fs-5 text-primary">GHS <?php echo number_format($product['price'], 2); ?></div>
    <button class="btn btn-gold rounded-pill px-4 shadow-sm" onclick="addToCart(<?php echo $product['id']; ?>, 1)" <?php echo $product['stock'] <= 0 ? 'disabled' : ''; ?>>
        <i class="bi bi-bag-plus me-1"></i> Add to Bag
    </button>
</div>

<?php include 'includes/footer.php'; ?>
