<?php
require_once __DIR__ . '/src/Database.php';
use App\Database;

$trackingNumber = $_GET['tracking_number'] ?? '';
$contactInfo = $_GET['contact'] ?? '';
$order = null;
$error = '';

if ($trackingNumber && $contactInfo) {
    try {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT o.tracking_number, o.total_amount, o.status, o.created_at, 
                                     c.first_name, c.last_name, c.email, c.phone 
                              FROM orders o 
                              JOIN customers c ON o.customer_id = c.id 
                              WHERE o.tracking_number = ? AND (c.email = ? OR c.phone = ?)");
        $stmt->execute([$trackingNumber, $contactInfo, $contactInfo]);
        $order = $stmt->fetch();
        
        if (!$order) {
            $error = "We couldn't find an order matching that reference and contact info.";
        }
    } catch (Exception $e) {
        $error = "System error. Please try again later.";
    }
} elseif (isset($_GET['tracking_number'])) {
    $error = "Please provide both the order number and your phone or email.";
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
    <div class="breadcrumb"><a href="<?php echo BASE_URL; ?>index">Home</a><span>/</span><span>Track Order</span></div>
    <span class="eyebrow">Where's my order?</span>
    <h1>Track your delivery.</h1>
    <p class="lede">Enter your order number and the phone or email used at checkout to see the latest status.</p>
  </div>
  <div class="kente-strip" style="margin-top:48px;"></div>
</header>

<section>
  <div class="wrap-narrow">
    
    <div class="form-card on-paper reveal in">
      <h3>Enter your order details</h3>
      <p class="sub">You'll find your order number in the confirmation email or SMS.</p>
      <?php if ($error): ?>
        <div style="padding: 15px; background: #fee; border-left: 4px solid #c00; margin-bottom: 20px; font-size: 0.9rem; color: #c00;">
            <?php echo htmlspecialchars($error); ?>
        </div>
      <?php endif; ?>
      <form method="GET" action="">
        <div class="field">
          <label for="orderNum">Order number</label>
          <input id="orderNum" name="tracking_number" type="text" value="<?php echo htmlspecialchars($trackingNumber); ?>" placeholder="e.g. OHM-48213" required>
        </div>
        <div class="field">
          <label for="orderContact">Phone or email used at checkout</label>
          <input id="orderContact" name="contact" type="text" value="<?php echo htmlspecialchars($contactInfo); ?>" placeholder="024 000 0000 or you@email.com" required>
        </div>
        <button class="form-submit btn-full" type="submit">Track order</button>
      </form>
    </div>


    
    <?php if ($order): ?>
    <div class="reveal in" style="margin-top:56px;">
      <div class="section-head" style="margin-bottom:12px;">
        <span class="eyebrow">Order <?php echo htmlspecialchars($order['tracking_number']); ?></span>
        <h2 style="font-size:1.8rem; margin-top:12px;">
            <?php 
                if($order['status'] == 'completed') echo "Delivered.";
                elseif($order['status'] == 'shipped') echo "On its way to you.";
                elseif($order['status'] == 'cancelled') echo "Order Cancelled.";
                else echo "Processing your order.";
            ?>
        </h2>
        <p>Current Status: <?php echo ucfirst(htmlspecialchars($order['status'])); ?></p>
      </div>

      <div class="tracker">
        <?php 
            $status = $order['status'];
            $w = "25%";
            $s1 = "done"; $s2 = ""; $s3 = ""; $s4 = "";
            if ($status === 'processing') { $w = "50%"; $s2 = "current"; }
            if ($status === 'shipped') { $w = "75%"; $s2="done"; $s3="current"; }
            if ($status === 'completed') { $w = "100%"; $s2="done"; $s3="done"; $s4="done"; }
        ?>
        <div class="tracker-bar" style="width:<?php echo $w; ?>;"></div>
        <div class="tracker-step done"><div class="tracker-dot">✓</div><div class="tracker-label">Placed</div></div>
        <div class="tracker-step <?php echo $s2; ?>"><div class="tracker-dot"><?php echo $s2=='done'?'✓':'2'; ?></div><div class="tracker-label">Confirmed</div></div>
        <div class="tracker-step <?php echo $s3; ?>"><div class="tracker-dot"><?php echo $s3=='done'?'✓':'3'; ?></div><div class="tracker-label">Shipped</div></div>
        <div class="tracker-step <?php echo $s4; ?>"><div class="tracker-dot"><?php echo $s4=='done'?'✓':'4'; ?></div><div class="tracker-label">Delivered</div></div>
      </div>

      <div class="order-summary-card">
        <div class="order-summary-row"><span class="lbl">Order date</span><span class="val"><?php echo date('F j, Y', strtotime($order['created_at'])); ?></span></div>
        <div class="order-summary-row"><span class="lbl">Customer</span><span class="val"><?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?></span></div>
        <div class="order-summary-row"><span class="lbl">Total</span><span class="val">GH₵ <?php echo number_format($order['total_amount'], 2); ?></span></div>
      </div>

      <p class="form-note" style="margin-top:20px;">Something look wrong with this order? <a href="<?php echo BASE_URL; ?>contact" class="link-quiet">Contact support</a></p>
    </div>
    <?php endif; ?>

  </div>
</section>


<?php include 'includes/footer.php'; ?>
