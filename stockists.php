<?php 
$pageTitle = "Retail Partners";
include 'includes/header.php'; 

$locations = [
    ['name' => 'Qdeez', 'location' => 'Kasoa', 'phone' => '0552959909'],
    ['name' => 'Mama Rose Ent', 'location' => 'Comm 1', 'phone' => '0249776077'],
    ['name' => 'Aronel Mothercare', 'location' => 'Comm. 19 - Comm 1', 'phone' => '0541579854'],
    ['name' => 'Sky World Kids', 'location' => 'Makola - Accra', 'phone' => '0541732459'],
    ['name' => 'Baby Fusion', 'location' => 'Tse-Addo', 'phone' => '0598953851'],
    ['name' => 'Elz Beddings', 'location' => 'Tarkwa', 'phone' => '0537370037'],
    
    ['name' => 'Pharmacy Direct', 'location' => 'Adenta - Accra', 'phone' => '0599744400'],
    ['name' => 'K-Mart', 'location' => 'Accra', 'phone' => '0244632325 / 0599941771'],
    ['name' => 'Kids Avenue', 'location' => 'North Legon', 'phone' => '0262318540'],
    ['name' => 'Amalena Children\'s Haven', 'location' => 'Kasoa', 'phone' => '0241512093'],
    ['name' => 'Dear Baby', 'location' => 'Ashongman / Kumasi', 'phone' => '0244071555'],
    ['name' => 'Baby Bliss', 'location' => 'Kissiman', 'phone' => '0204822486'],
    
    ['name' => 'Kiddytrix', 'location' => 'Kwabenya', 'phone' => '0555910002 / 0244238856'],
    ['name' => 'Herty\'s Mothercare', 'location' => 'East Legon', 'phone' => '0554480812'],
    ['name' => 'Jusadas', 'location' => 'Haasto', 'phone' => '05544671909'],
    ['name' => 'Clean Baby', 'location' => 'East Legon', 'phone' => '0559511566'],
    ['name' => 'ABC Mothercare', 'location' => 'Westland', 'phone' => '0550005827'],
    ['name' => '5 Star Kids', 'location' => 'Haasto', 'phone' => '0551433401'],
];
?>

<div class="container-fluid px-4 px-lg-5 pt-5 mt-5">
    <div class="row pt-5 mb-5 pb-5 border-bottom border-light">
        <div class="col-lg-12 text-center">
            <h1 class="font-serif text-black" style="font-size: 3.5rem;">Retail Partners</h1>
            <p class="font-sans text-muted letter-spacing-wide text-uppercase mx-auto" style="font-size: 0.75rem; max-width: 400px; line-height: 1.8;">
                Find our premium formulations at these authorized locations across the country.
            </p>
        </div>
    </div>
</div>

<div class="container-fluid px-4 px-lg-5 mb-5 pb-5">
    <div class="row g-0 border-top border-start border-light">
        <?php foreach ($locations as $loc): ?>
            <div class="col-12 col-md-6 col-lg-4 border-end border-bottom border-light p-4 p-lg-5 text-center text-md-start">
                <h5 class="font-sans text-uppercase text-black mb-2 fw-600" style="font-size: 0.85rem; letter-spacing: 0.05em;">
                    <?php echo htmlspecialchars($loc['name']); ?>
                </h5>
                <p class="font-sans text-muted mb-3" style="font-size: 0.8rem;">
                    <?php echo htmlspecialchars($loc['location']); ?>
                </p>
                <div class="d-inline-flex border border-black px-3 py-2 mt-2">
                    <span class="font-sans text-black" style="font-size: 0.75rem; letter-spacing: 0.05em;">
                        <?php echo htmlspecialchars($loc['phone']); ?>
                    </span>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Partnership CTA -->
<section class="py-5 bg-off-white text-center border-top border-light">
    <div class="container py-5">
        <h3 class="font-serif text-black mb-4" style="font-size: 2.5rem;">Become a Stockist</h3>
        <p class="font-sans text-muted mb-5 mx-auto" style="max-width: 500px; font-size: 0.85rem; line-height: 1.8;">
            We are always looking to partner with premium retail spaces that align with our aesthetic and commitment to purity.
        </p>
        <a href="contact" class="btn btn-black px-5 py-3">Inquire Now</a>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
