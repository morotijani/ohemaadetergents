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

<!-- Editorial Hero Section -->
<section class="hero-section">
    <div class="container-fluid px-4 px-lg-5">
        <div class="row align-items-center g-0">
            <div class="col-lg-5 order-2 order-lg-1 pe-lg-5 text-center text-lg-start reveal mt-5 mt-lg-0">
                <span class="font-sans text-uppercase letter-spacing-widest text-muted d-block mb-4" style="font-size: 0.7rem;">The Signature Collection</span>
                <h1 class="hero-title font-serif text-black">
                    Purity.<br>
                    <span class="italic text-muted">Redefined.</span>
                </h1>
                <p class="font-sans text-muted mb-5 fw-300" style="max-width: 400px; font-size: 1rem; margin: 0 auto 0 0;">
                    A delicate balance of clinical efficacy and organic luxury. Formulated for the most uncompromising fabrics.
                </p>
                <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center justify-content-lg-start">
                    <a href="shop" class="btn btn-black">
                        Shop Collection
                    </a>
                </div>
            </div>
            
            <div class="col-lg-7 order-1 order-lg-2 reveal ps-lg-5">
                <div class="product-image-wrapper p-0 m-0" style="padding-top: 80%; background: transparent;">
                    <img src="/ohemaadetergents/public/assets/img/hero_new.png" class="img-fluid" alt="Ohemaa Premium Clean" style="object-fit: cover; width: 100%; height: 100%; padding: 0;">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Values Section (Minimalist) -->
<section class="py-5 bg-white border-top border-bottom border-light">
    <div class="container-fluid px-4 px-lg-5 py-5">
        <div class="row g-5 text-center">
            <div class="col-md-4 reveal">
                <h4 class="font-sans text-uppercase letter-spacing-wide mb-3 fw-600" style="font-size: 0.85rem;">Molecular Shield</h4>
                <p class="font-sans text-muted fw-300" style="font-size: 0.85rem; max-width: 250px; margin: 0 auto;">Advanced polymers wrap each fiber, significantly extending the life and texture of your garments.</p>
            </div>
            <div class="col-md-4 reveal" style="animation-delay: 0.1s;">
                <h4 class="font-sans text-uppercase letter-spacing-wide mb-3 fw-600" style="font-size: 0.85rem;">Clinical Extraction</h4>
                <p class="font-sans text-muted fw-300" style="font-size: 0.85rem; max-width: 250px; margin: 0 auto;">Targeted bio-enzymes lift protein and lipid stains without compromising fabric integrity.</p>
            </div>
            <div class="col-md-4 reveal" style="animation-delay: 0.2s;">
                <h4 class="font-sans text-uppercase letter-spacing-wide mb-3 fw-600" style="font-size: 0.85rem;">Botanical Notes</h4>
                <p class="font-sans text-muted fw-300" style="font-size: 0.85rem; max-width: 250px; margin: 0 auto;">Subtle, lingering aromas derived from distilled essential oils. Never overpowering.</p>
            </div>
        </div>
    </div>
</section>

<!-- Featured Products (Borderless Grid) -->
<section class="py-5 my-5 bg-off-white">
    <div class="container-fluid px-4 px-lg-5">
        <div class="text-center mb-5 reveal">
            <h2 class="font-serif text-black" style="font-size: 2.5rem;">Curated Objects</h2>
        </div>

        <div class="row g-0">
            <?php if (empty($featuredProducts)): ?>
                <div class="col-12 text-center text-muted py-5 font-sans">
                    <p>New formulations arriving soon.</p>
                </div>
            <?php else: ?>
                <?php foreach ($featuredProducts as $product): ?>
                    <div class="col-12 col-md-6 col-lg-3 reveal border-end border-bottom border-light">
                        <div class="product-card h-100">
                            <?php 
                            $img = $product['image_url'] ? $product['image_url'] : 'https://via.placeholder.com/600x600?text=Ohemaa';
                            ?>
                            <div class="product-image-wrapper">
                                <a href="product?slug=<?php echo urlencode($product['slug']); ?>">
                                    <img src="<?php echo htmlspecialchars($img); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                                </a>
                            </div>
                            
                            <div class="p-3">
                                <a href="product?slug=<?php echo urlencode($product['slug']); ?>" class="text-decoration-none">
                                    <h5 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                                </a>
                                <div class="product-price mb-3">
                                    GHS <?php echo number_format($product['price'], 2); ?>
                                </div>
                                <button class="btn btn-link-dark w-100 py-2 border-0" onclick="addToCart(<?php echo $product['id']; ?>)">
                                    + Add to Cart
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Call to Action / Editorial Image -->
<section class="py-0 reveal">
    <div class="container-fluid p-0">
        <div class="row g-0">
            <div class="col-lg-6 bg-black text-white d-flex align-items-center" style="min-height: 50vh;">
                <div class="p-5 p-lg-5 w-100 mx-auto" style="max-width: 600px;">
                    <span class="font-sans text-uppercase letter-spacing-widest text-muted d-block mb-4" style="font-size: 0.7rem;">Journal</span>
                    <h2 class="font-serif text-white mb-4" style="font-size: 3rem; line-height: 1;">The Art of<br>Maintenance</h2>
                    <p class="font-sans fw-300 text-muted mb-5" style="font-size: 0.9rem;">
                        Discover our philosophy on garment care. We believe that what you wear is an extension of yourself, and caring for it should be a ritual, not a chore.
                    </p>
                    <a href="about" class="btn btn-outline-black" style="border-color: white; color: white;">Read Philosophy</a>
                </div>
            </div>
            <div class="col-lg-6 bg-light" style="min-height: 50vh; background: url('https://images.unsplash.com/photo-1606105961732-615f795db2d6?q=80&w=2070&auto=format&fit=crop') center/cover;">
                <!-- Lifestyle Image -->
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
