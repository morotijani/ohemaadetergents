<?php
require_once __DIR__ . '/src/Database.php';
use App\Database;

try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->query("SELECT * FROM wholesale_products WHERE status = 'active' ORDER BY id ASC");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $products = [];
}

$pageTitle = 'Wholesale — Ohemaa Detergents';
include 'includes/header.php';
?>

<header class="page-hero no-print">
  <svg class="page-hero-watermark" viewBox="0 0 60 60" fill="none">
    <circle cx="30" cy="30" r="29" fill="none" stroke="#E7C766" stroke-width="1"/>
    <circle cx="30" cy="30" r="22" fill="none" stroke="#E7C766" stroke-width="1"/>
    <path d="M30 14 L34 26 L47 26 L36.5 33 L40.5 45 L30 37.5 L19.5 45 L23.5 33 L13 26 L26 26 Z" fill="#E7C766"/>
  </svg>
  <div class="wrap">
    <div class="breadcrumb"><a href="<?php echo BASE_URL; ?>index">Home</a><span>/</span><span>Wholesale</span></div>
    <span class="eyebrow">For distributors, hotels & businesses</span>
    <h1>Wholesale pricing, built for volume.</h1>
    <p class="lede">Buy by the carton at tiered rates, build a quote in real time, and generate a pro-forma invoice you can send straight to your finance team.</p>
    <div class="btn-row" style="margin-top:28px;">
      <a href="#quote-builder" class="btn btn-primary">Build a quote</a>
      <a href="#price-list" class="btn btn-ghost">View price list</a>
    </div>
  </div>
  <div class="kente-strip" style="margin-top:48px;"></div>
</header>

<section class="no-print">
  <div class="wrap">
    <div class="value-grid reveal">
      <div class="value-card">
        <div class="value-icon"><svg viewBox="0 0 24 24" fill="none" stroke="#E7C766" stroke-width="2"><path d="M12 2l3 7h7l-5.5 4.5L18.5 21 12 16.5 5.5 21l2-7.5L2 9h7z"/></svg></div>
        <h3>Tiered carton pricing</h3>
        <p>The more you order, the lower your unit cost — three pricing tiers on every product.</p>
      </div>
      <div class="value-card">
        <div class="value-icon"><svg viewBox="0 0 24 24" fill="none" stroke="#E7C766" stroke-width="2"><path d="M4 4h16v16H4z"/><path d="M4 6l8 7 8-7"/></svg></div>
        <h3>Instant pro-forma invoicing</h3>
        <p>Build your order and generate a formal PI on the spot — no waiting on a sales rep.</p>
      </div>
      <div class="value-card">
        <div class="value-icon"><svg viewBox="0 0 24 24" fill="none" stroke="#E7C766" stroke-width="2"><path d="M3 21c0-4 4-6 9-6s9 2 9 6M12 11a4 4 0 100-8 4 4 0 000 8z"/></svg></div>
        <h3>A dedicated account contact</h3>
        <p>Every wholesale account is assigned a route rep for reorders, delivery windows, and support.</p>
      </div>
    </div>
  </div>
</section>

<div class="kente-strip thin no-print"></div>

<section id="price-list" class="no-print">
  <div class="wrap">
    <div class="section-head reveal">
      <span class="eyebrow">Current wholesale rates</span>
      <h2>Price list by carton tier.</h2>
      <p>Prices shown are per unit (GH₵) and unlock automatically as your order quantity grows. All cartons are mixed-case friendly on request.</p>
    </div>

    <div class="wholesale-table-wrap reveal">
      <table class="wholesale-table">
        <thead>
          <tr>
            <th>Product</th>
            <th>Carton size</th>
            <th>Tier 1 · 1–9 cartons</th>
            <th>Tier 2 · 10–49 cartons</th>
            <th>Tier 3 · 50+ cartons</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($products as $p): ?>
          <tr>
            <td><strong><?php echo htmlspecialchars($p['name']); ?></strong><br><span class="carton-note"><?php echo htmlspecialchars($p['bottle_type']); ?></span></td>
            <td><?php echo htmlspecialchars($p['carton_size']); ?> / carton</td>
            <td><span class="tier-price">GH₵ <?php echo number_format($p['tier1_price'], 2); ?></span><span class="tier-unit">per unit</span></td>
            <td><span class="tier-price">GH₵ <?php echo number_format($p['tier2_price'], 2); ?></span><span class="tier-unit">per unit</span></td>
            <td><span class="tier-price">GH₵ <?php echo number_format($p['tier3_price'], 2); ?></span><span class="tier-unit">per unit</span></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <p class="form-note" style="text-align:left; margin-top:14px;">Prices exclude delivery and estimated 15% VAT. Custom formulations and private-label runs are quoted separately — <a href="<?php echo BASE_URL; ?>contact" style="color:var(--indigo); font-weight:700;">contact our team</a>.</p>
  </div>
</section>

<section id="quote-builder" class="heritage no-print">
  <div class="wrap">
    <div class="section-head reveal">
      <span class="eyebrow">Build your order</span>
      <h2>Get a quote in real time.</h2>
      <p>Enter cartons for each product — pricing updates instantly as you cross into the next tier.</p>
    </div>

    <div class="quote-builder reveal" id="quoteBuilder">
      <div class="quote-row quote-row-head">
        <span>Product</span><span>Cartons</span><span>Unit price</span><span>Line total</span>
      </div>

      <?php foreach ($products as $p): ?>
      <div class="quote-row" data-product-id="<?php echo htmlspecialchars($p['product_key']); ?>">
        <div><div class="quote-product-name"><?php echo htmlspecialchars($p['name']); ?></div><div class="quote-product-meta"><?php echo htmlspecialchars($p['carton_size']); ?> units / carton</div></div>
        <input type="number" class="carton-input" min="0" value="0">
        <span class="quote-unit-price">—</span>
        <span class="quote-line-total">GH₵ 0.00</span>
      </div>
      <?php endforeach; ?>

      <div class="quote-totals">
        <div class="order-summary-row"><span class="lbl">Subtotal</span><span class="val js-wh-subtotal">GH₵ 0.00</span></div>
        <div class="order-summary-row"><span class="lbl">Estimated VAT (15%)</span><span class="val js-wh-vat">GH₵ 0.00</span></div>
        <div class="order-summary-row" style="border-top:1.5px solid var(--line); font-size:1.05rem;"><span class="lbl" style="font-weight:700; color:var(--ink);">Estimated total</span><span class="val js-wh-total">GH₵ 0.00</span></div>
      </div>
    </div>

    <div class="two-col mt-lg">
      <div class="reveal">
        <h3 style="font-size:1.2rem; margin-bottom:14px;">Buyer details</h3>
        <p style="color:rgba(26,22,32,0.65); font-size:0.92rem; margin-bottom:20px;">Used to generate your pro-forma invoice. No account required.</p>
        <div class="field">
          <label for="whCompany">Company / business name</label>
          <input id="whCompany" type="text" placeholder="e.g. Golden Star Supermarkets Ltd.">
        </div>
        <div class="field-row">
          <div class="field">
            <label for="whContact">Contact person</label>
            <input id="whContact" type="text" placeholder="Full name">
          </div>
          <div class="field">
            <label for="whPhone">Phone number</label>
            <input id="whPhone" type="tel" placeholder="024 000 0000">
          </div>
        </div>
        <div class="field">
          <label for="whEmail">Email</label>
          <input id="whEmail" type="email" placeholder="you@company.com">
        </div>
        <div class="field">
          <label for="whAddress">Delivery address</label>
          <input id="whAddress" type="text" placeholder="e.g. Warehouse address, Kumasi">
        </div>
      </div>

      <div class="form-card on-paper reveal">
        <h3>Ready when you are</h3>
        <p class="sub">Your pro-forma invoice is generated instantly and valid for 14 days — no obligation to purchase.</p>
        <button class="btn btn-dark btn-full" type="button" onclick="generateInvoice()">Generate pro-forma invoice</button>
        <p class="form-note">Prefer to talk first? <a href="<?php echo BASE_URL; ?>contact" class="link-quiet">Contact our wholesale team</a></p>
      </div>
    </div>
  </div>
</section>

<section id="invoiceSection" style="display:none;">
  <div class="wrap">
    <div class="section-head center-head no-print">
      <span class="eyebrow">Your quote</span>
      <h2>Pro-forma invoice</h2>
      <p>Review the details below, then print or save as PDF for your records.</p>
    </div>
    <div class="invoice-doc" id="invoiceDoc"></div>
    <div class="invoice-actions no-print">
      <button class="btn btn-dark" type="button" onclick="window.print()">Print / Save as PDF</button>
      <a href="<?php echo BASE_URL; ?>become_stockist" class="btn btn-outline">Apply to become a stockist</a>
      <a href="<?php echo BASE_URL; ?>contact" class="btn btn-outline">Confirm this order with our team</a>
    </div>
  </div>
</section>

<section class="no-print">
  <div class="wrap">
    <div class="center-head reveal">
      <span class="eyebrow">Wholesale FAQs</span>
      <h2>Common questions</h2>
    </div>
    <div class="wrap-narrow" style="padding:0; margin:0 auto;">
      <div class="faq-accordion">
        <div class="faq-accordion-item">
          <button class="faq-accordion-trigger">Is there a minimum order quantity? <span class="plus">+</span></button>
          <div class="faq-accordion-panel"><div class="faq-accordion-panel-inner">Our wholesale pricing starts from a single carton per product, with better rates unlocking at 10 and 50 cartons. There's no strict minimum to request a quote.</div></div>
        </div>
        <div class="faq-accordion-item">
          <button class="faq-accordion-trigger">How is this different from becoming a stockist? <span class="plus">+</span></button>
          <div class="faq-accordion-panel"><div class="faq-accordion-panel-inner">Wholesale quotes are for one-off or occasional bulk purchases. Becoming a stockist sets you up with a standing account, a dedicated route rep, and recurring delivery — <a href="<?php echo BASE_URL; ?>become_stockist" style="color:var(--indigo); font-weight:700;">apply here</a> if that fits better.</div></div>
        </div>
        <div class="faq-accordion-item">
          <button class="faq-accordion-trigger">Can I combine products from different tiers? <span class="plus">+</span></button>
          <div class="faq-accordion-panel"><div class="faq-accordion-panel-inner">Yes — each product's tier is calculated independently based on the cartons you order of that specific product.</div></div>
        </div>
        <div class="faq-accordion-item">
          <button class="faq-accordion-trigger">What are your payment terms? <span class="plus">+</span></button>
          <div class="faq-accordion-panel"><div class="faq-accordion-panel-inner">Standard terms are 50% deposit to confirm the order and the balance due before dispatch, payable by Mobile Money or bank transfer. Established accounts may qualify for credit terms.</div></div>
        </div>
        <div class="faq-accordion-item">
          <button class="faq-accordion-trigger">How long does delivery take after payment? <span class="plus">+</span></button>
          <div class="faq-accordion-panel"><div class="faq-accordion-panel-inner">Standard batches ship within 72 hours of order confirmation. Large custom-volume orders may take longer — this will be confirmed with your quote.</div></div>
        </div>
      </div>
    </div>
  </div>
</section>

<script>
  window.wholesaleProductsDb = <?php
    $jsProducts = [];
    foreach ($products as $p) {
        $jsProducts[] = [
            'id' => $p['product_key'],
            'name' => $p['name'],
            'carton' => (int)$p['carton_size'],
            'tiers' => [
                ['min' => (int)$p['tier1_min'], 'max' => (int)$p['tier1_max'], 'price' => (float)$p['tier1_price']],
                ['min' => (int)$p['tier2_min'], 'max' => (int)$p['tier2_max'], 'price' => (float)$p['tier2_price']],
                ['min' => (int)$p['tier3_min'], 'max' => (int)$p['tier3_max'], 'price' => (float)$p['tier3_price']]
            ]
        ];
    }
    echo json_encode($jsProducts);
  ?>;
</script>

<?php include 'includes/footer.php'; ?>
