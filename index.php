<?php
require_once __DIR__ . '/src/Database.php';
use App\Database;

try {
    $db = Database::getInstance()->getConnection();

    $stmt = $db->query("SELECT id, name, slug, price, image_url FROM products WHERE is_featured = 1 AND is_deleted = 0 ORDER BY created_at DESC LIMIT 4");
    $featuredProducts = $stmt->fetchAll();

    if (empty($featuredProducts)) {
        $stmt = $db->query("SELECT id, name, slug, price, image_url FROM products WHERE is_deleted = 0 ORDER BY created_at DESC LIMIT 4");
        $featuredProducts = $stmt->fetchAll();
    }
} catch (Exception $e) {
    $featuredProducts = [];
}

include 'includes/header.php';
?>

<!-- Hero Section -->
<section class="hero-section" style="background: linear-gradient(to right, var(--primary) 0%, #002D5C 100%);">
    <div class="hero-bg-accent"></div>
    <div class="container hero-content">
        <div class="row align-items-center g-5">
            <div class="col-lg-7 text-center text-lg-start reveal">
                <div class="d-inline-flex align-items-center gap-2 badge-category mb-4" style="background: rgba(197, 160, 89, 0.15); color: var(--accent); padding: 0.6rem 1.2rem;">
                    <i class="bi bi-crown-fill"></i>
                    <span class="text-uppercase fw-bold" style="letter-spacing: 2px; font-size: 0.7rem;">The Royal Standard of Purity</span>
                </div>
                <h1 class="display-2 mb-4 text-white" style="line-height: 1.1;">
                    Elegance in <br>
                    <span class="text-gold italic">Every Fiber.</span>
                </h1>
                <p class="lead mb-5 text-white-50 pe-lg-5 fs-5">
                    Ohemaa Detergents isn't just a cleaner; it's a treatment. Designed for those who demand the absolute best for their home and wardrobe.
                </p>
                <div class="d-flex flex-column flex-sm-row gap-4 justify-content-center justify-content-lg-start">
                    <a href="shop" class="btn btn-gold btn-lg px-5 py-3 shadow-lg">
                        Explore Collection
                    </a>
                    <a href="#about" class="btn btn-outline-light btn-lg px-5 py-3" style="border-width: 2px;">
                        The Heritage
                    </a>
                </div>
                
                <div class="mt-5 d-flex align-items-center gap-4 justify-content-center justify-content-lg-start reveal" style="animation-delay: 0.4s;">
                    <div class="text-white-50 small">
                        <span class="d-block fw-bold text-white fs-5">50k+</span>
                        Royal Homes
                    </div>
                    <div class="vr bg-white opacity-25"></div>
                    <div class="text-white-50 small">
                        <span class="d-block fw-bold text-white fs-5">4.9/5</span>
                        Customer Rating
                    </div>
                </div>
            </div>
            <div class="col-lg-5 reveal" style="animation-delay: 0.2s;">
                <div class="position-relative">
                    <div class="position-absolute translate-middle-x start-50 top-50 w-100 h-100 bg-gold blur-lg opacity-10 rounded-circle"></div>
                    <img src="/ohemaadetergents/public/assets/img/hero_new.png" class="img-fluid rounded-lg shadow-lg position-relative" alt="Ohemaa Premium Clean" style="border: 2px solid rgba(197, 160, 89, 0.4); transform: perspective(1000px) rotateY(-5deg);">
                    
                    <!-- Floating Badge -->
                    <div class="position-absolute bottom-0 start-0 mb-n3 ms-n3 glass p-3 rounded-md shadow-lg reveal" style="animation-delay: 0.6s;">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-gold-soft p-2 rounded-circle">
                                <i class="bi bi-stars text-gold"></i>
                            </div>
                            <div class="small">
                                <span class="d-block fw-bold text-primary">Pure Formula</span>
                                <span class="text-muted">Clinically Tested</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section id="about" class="py-5 bg-white overflow-hidden">
    <div class="container py-5">
        <div class="text-center mb-5 reveal">
            <span class="badge-category mb-2">Our Promise</span>
            <h2 class="section-title center display-5 fw-800">The Royal Standards</h2>
        </div>
        <div class="row g-5">
            <div class="col-md-4 reveal">
                <div class="card border-0 h-100 bg-transparent text-center p-4">
                    <div class="bg-gold-soft p-4 rounded-circle d-inline-flex mb-4 mx-auto" style="width: 80px; height: 80px; align-items: center; justify-content: center;">
                        <i class="bi bi-shield-check fs-2 text-gold"></i>
                    </div>
                    <h4 class="fw-bold mb-3">Fiber Protection</h4>
                    <p class="text-muted">Our advanced polymers create a microscopic shield around each fiber, preventing wear and keeping clothes new for longer.</p>
                </div>
            </div>
            <div class="col-md-4 reveal" style="animation-delay: 0.1s;">
                <div class="card border-0 h-100 bg-transparent text-center p-4">
                    <div class="bg-gold-soft p-4 rounded-circle d-inline-flex mb-4 mx-auto" style="width: 80px; height: 80px; align-items: center; justify-content: center;">
                        <i class="bi bi-droplet-half fs-2 text-gold"></i>
                    </div>
                    <h4 class="fw-bold mb-3">Deep Extraction</h4>
                    <p class="text-muted">Bio-enzymes target stains at the molecular level, lifting even aged-in oil and protein stains with ease.</p>
                </div>
            </div>
            <div class="col-md-4 reveal" style="animation-delay: 0.2s;">
                <div class="card border-0 h-100 bg-transparent text-center p-4">
                    <div class="bg-gold-soft p-4 rounded-circle d-inline-flex mb-4 mx-auto" style="width: 80px; height: 80px; align-items: center; justify-content: center;">
                        <i class="bi bi-wind fs-2 text-gold"></i>
                    </div>
                    <h4 class="fw-bold mb-3">Aromatic Legacy</h4>
                    <p class="text-muted">Infused with essential oils from the royal gardens, leaving a subtle, sophisticated scent that lingers for 7 days.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Featured Products -->
<section class="container py-5 my-5">
    <div class="text-center mb-5 reveal">
        <span class="badge-category mb-2">Curated for You</span>
        <h2 class="section-title center display-5 fw-800">Featured Collections</h2>
    </div>

    <div class="row g-4">
        <?php if (empty($featuredProducts)): ?>
            <div class="col-12 text-center text-muted py-5">
                <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                <p>New collections arriving soon.</p>
            </div>
        <?php else: ?>
            <?php foreach ($featuredProducts as $product): ?>
                <div class="col-6 col-md-4 col-lg-3 reveal">
                    <div class="product-card">
                        <?php 
                        $img = $product['image_url'] ? $product['image_url'] : 'https://via.placeholder.com/600x600?text=Ohemaa+Product';
                        ?>
                        <div class="product-image-wrapper">
                            <a href="product?slug=<?php echo urlencode($product['slug']); ?>">
                                <img src="<?php echo htmlspecialchars($img); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                            </a>
                        </div>
                        
                        <div class="p-4 d-flex flex-column flex-grow-1 text-center">
                            <a href="product?slug=<?php echo urlencode($product['slug']); ?>" class="text-decoration-none">
                                <h5 class="mb-2 fs-5 text-truncate"><?php echo htmlspecialchars($product['name']); ?></h5>
                            </a>
                            <div class="mb-3">
                                <span class="price-tag">GHS <?php echo number_format($product['price'], 2); ?></span>
                            </div>
                            <button class="btn btn-gold w-100 rounded-pill py-2" onclick="addToCart(<?php echo $product['id']; ?>)">
                                <i class="bi bi-bag-plus me-2"></i>Add to Cart
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    
    <div class="text-center mt-5 reveal">
        <a href="shop" class="btn btn-outline-gold px-5 py-3 rounded-pill">Discover More</a>
    </div>
</section>

<!-- Call to Action / Newsletter -->
<section class="py-5 mb-5 reveal">
    <div class="container">
        <div class="bg-primary rounded-lg p-5 text-center text-white position-relative overflow-hidden shadow-lg" style="border-radius: var(--radius-lg); background: linear-gradient(135deg, var(--primary) 0%, #003366 100%) !important;">
            <div class="hero-bg-accent" style="opacity: 0.4;"></div>
            <div class="position-relative z-index-2">
                <span class="badge-category mb-3" style="background: rgba(255,255,255,0.1); color: var(--accent);">Exclusive Access</span>
                <h2 class="display-5 fw-bold mb-4 text-white">Join the Royal Circle</h2>
                <p class="mb-5 opacity-75 mx-auto fs-5" style="max-width: 600px;">Experience early access to new collections and royal laundry tips delivered to your inbox.</p>
                <div class="row justify-content-center">
                    <div class="col-md-8 col-lg-6">
                        <div class="glass p-2 rounded-pill d-flex align-items-center">
                            <input type="email" class="form-control bg-transparent border-0 text-white px-4" placeholder="Your royal email" style="box-shadow: none;">
                            <button class="btn btn-gold rounded-pill px-4 py-2" type="button">Subscribe</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
