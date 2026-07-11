<?php
require_once __DIR__ . '/src/Database.php';
use App\Database;

try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->query("SELECT shop_name, region, town_area, phone, business_type 
                        FROM stockist_applications 
                        WHERE status = 'approved' 
                        ORDER BY region ASC, shop_name ASC");
    $stockists = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $stockists = [];
}

include 'includes/header.php'; 
?>

<header class="page-hero">
  <svg class="page-hero-watermark" viewBox="0 0 60 60" fill="none">
    <circle cx="30" cy="30" r="29" fill="none" stroke="#E7C766" stroke-width="1"/>
    <circle cx="30" cy="30" r="22" fill="none" stroke="#E7C766" stroke-width="1"/>
    <path d="M30 14 L34 26 L47 26 L36.5 33 L40.5 45 L30 37.5 L19.5 45 L23.5 33 L13 26 L26 26 Z" fill="#E7C766"/>
  </svg>
  <div class="wrap">
    <div class="breadcrumb"><a href="<?php echo BASE_URL; ?>index">Home</a><span>/</span><span>Stockists</span></div>
    <span class="eyebrow">200+ retail partners</span>
    <h1>Find Ohemaa near you.</h1>
    <p class="lede">From provision shops to hotels, here's where you can pick up the full Ohemaa range today.</p>
  </div>
  <div class="kente-strip" style="margin-top:48px;"></div>
</header>

<section>
  <div class="wrap">

    <div class="map-card reveal" style="margin-bottom:50px;">
      <svg viewBox="0 0 900 260" xmlns="http://www.w3.org/2000/svg">
        <rect width="900" height="260" fill="#2B1B4D"/>
        <path d="M0 190 Q160 150 320 185 T650 165 T900 180" stroke="#E7C766" stroke-width="2" fill="none" opacity="0.3"/>
        <path d="M0 90 Q200 60 380 95 T700 75 T900 90" stroke="#1E6E63" stroke-width="2" fill="none" opacity="0.35"/>
        <g>
          <circle cx="260" cy="120" r="7" fill="#C9A227"/>
          <circle cx="420" cy="150" r="7" fill="#C9A227"/>
          <circle cx="340" cy="90" r="7" fill="#C9A227"/>
          <circle cx="540" cy="110" r="9" fill="#E7C766"/>
          <circle cx="700" cy="160" r="7" fill="#C9A227"/>
          <circle cx="150" cy="160" r="7" fill="#C9A227"/>
        </g>
      </svg>
      <div class="map-pin-label">📍 200+ locations across Ghana</div>
    </div>

    <div class="directory-toolbar reveal">
      <div class="filter-bar" style="margin-bottom:0;">
        <button class="filter-chip active" data-filter="all">All regions</button>
        <?php
        $regions = array_unique(array_column($stockists, 'region'));
        sort($regions);
        foreach ($regions as $r):
            if (empty(trim($r))) continue;
            $filterVal = strtolower(htmlspecialchars($r));
            $displayName = htmlspecialchars($r);
        ?>
        <button class="filter-chip" data-filter="<?php echo $filterVal; ?>"><?php echo $displayName; ?></button>
        <?php endforeach; ?>
      </div>
      <div class="directory-search">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
        <input type="text" id="directorySearch" placeholder="Search by name or area">
      </div>
    </div>

    <div class="stockist-grid">

      <?php foreach ($stockists as $s): ?>
      <div class="stockist-card reveal" data-category="<?php echo strtolower(htmlspecialchars($s['region'])); ?>">
        <div class="stockist-top"><h3><?php echo htmlspecialchars($s['shop_name']); ?></h3><span class="stockist-region-tag"><?php echo htmlspecialchars($s['region']); ?></span></div>
        <div class="stockist-type"><?php echo htmlspecialchars($s['business_type']); ?></div>
        <div class="stockist-line"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 21s7-6.5 7-11.5A7 7 0 105 9.5C5 14.5 12 21 12 21z"/></svg><?php echo htmlspecialchars($s['town_area']); ?>, <?php echo htmlspecialchars($s['region']); ?></div>
        <div class="stockist-line"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.9v3a2 2 0 01-2.2 2 19.8 19.8 0 01-8.6-3 19.5 19.5 0 01-6-6 19.8 19.8 0 01-3-8.7A2 2 0 014.1 2h3a2 2 0 012 1.7c.1.9.3 1.8.6 2.7a2 2 0 01-.4 2.1L8 9.9a16 16 0 006 6l1.4-1.4a2 2 0 012.1-.4c.9.3 1.8.5 2.7.6a2 2 0 011.8 2.2z"/></svg><?php echo htmlspecialchars($s['phone']); ?></div>
        <div class="stockist-hours">Mon–Sat · 8:00am – 8:00pm</div>
      </div>
      <?php endforeach; ?>

    </div>

    <div class="no-results reveal">
      <p>No stockists match your search. Try a different region or area name.</p>
    </div>

  </div>
</section>

<section class="stockists">
  <div class="wrap" style="text-align:center;">
    <div class="reveal" style="max-width:560px; margin:0 auto;">
      <span class="eyebrow" style="justify-content:center;">Not on this list yet?</span>
      <h2>Bring Ohemaa to your shop.</h2>
      <p style="margin:16px auto 28px;">We're adding new retail partners every month. Apply and we'll route you to your nearest supply line.</p>
      <a href="<?php echo BASE_URL; ?>become_stockist" class="btn btn-dark">Become a stockist</a>
    </div>
  </div>
</section>

<?php include 'includes/footer.php'; ?>
