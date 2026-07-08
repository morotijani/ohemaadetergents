<?php
session_start();
if (!isset($_SESSION['customer_id'])) {
    header("Location: login");
    exit;
}

require_once __DIR__ . '/src/Database.php';
use App\Database;

try {
    $db = Database::getInstance()->getConnection();
    
    // Fetch customer details for sidebar
    $stmt = $db->prepare("SELECT first_name, last_name, email FROM customers WHERE id = ?");
    $stmt->execute([$_SESSION['customer_id']]);
    $customer = $stmt->fetch();

    // Fetch orders
    $stmt = $db->prepare("SELECT id, tracking_number, total_amount, status, created_at FROM orders WHERE customer_id = ? ORDER BY created_at DESC");
    $stmt->execute([$_SESSION['customer_id']]);
    $orders = $stmt->fetchAll();
    
    // Fetch items for each order
    foreach ($orders as &$order) {
        $itemStmt = $db->prepare("
            SELECT oi.quantity, oi.unit_price, p.name, p.image_url 
            FROM order_items oi
            JOIN products p ON oi.product_id = p.id
            WHERE oi.order_id = ?
        ");
        $itemStmt->execute([$order['id']]);
        $order['items'] = $itemStmt->fetchAll();
    }
    unset($order); // break the reference

} catch (Exception $e) {
    $orders = [];
    $customer = ['first_name' => '', 'last_name' => '', 'email' => ''];
}

include 'includes/header.php';
?>


<header class="page-hero" style="padding:48px 0 40px;">
  <svg class="page-hero-watermark" viewBox="0 0 60 60" fill="none">
    <circle cx="30" cy="30" r="29" fill="none" stroke="#E7C766" stroke-width="1"/>
    <circle cx="30" cy="30" r="22" fill="none" stroke="#E7C766" stroke-width="1"/>
    <path d="M30 14 L34 26 L47 26 L36.5 33 L40.5 45 L30 37.5 L19.5 45 L23.5 33 L13 26 L26 26 Z" fill="#E7C766"/>
  </svg>
  <div class="wrap">
    <div class="breadcrumb"><a href="index.html">Home</a><span>/</span><span>My Account</span><span>/</span><span>Orders</span></div>
    <h1 style="font-size:2rem; margin-top:14px;">Your orders</h1>
  </div>
</header>

<section style="padding-top:50px;">
  <div class="wrap account-shell">

    <div class="account-sidebar reveal">
      <div class="account-avatar"><?php echo strtoupper(substr($customer['first_name'], 0, 1) . substr($customer['last_name'], 0, 1)); ?></div>
      <div class="account-who">
        <span class="name"><?php echo htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']); ?></span>
        <span class="email"><?php echo htmlspecialchars($customer['email']); ?></span>
      </div>
      <nav class="account-nav">
        <a href="profile"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 4-6 8-6s8 2 8 6"/></svg>Profile</a>
        <a href="profile_orders" class="active"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 7l9-4 9 4-9 4-9-4z"/><path d="M3 7v10l9 4 9-4V7"/></svg>Orders</a>
        <a href="profile_password"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 00.3 1.9l.1.1a2 2 0 11-2.8 2.8l-.1-.1a1.7 1.7 0 00-1.9-.3 1.7 1.7 0 00-1 1.5V21a2 2 0 11-4 0v-.1a1.7 1.7 0 00-1-1.6 1.7 1.7 0 00-1.9.3l-.1.1a2 2 0 11-2.8-2.8l.1-.1a1.7 1.7 0 00.3-1.9 1.7 1.7 0 00-1.5-1H3a2 2 0 110-4h.1a1.7 1.7 0 001.5-1 1.7 1.7 0 00-.3-1.9l-.1-.1a2 2 0 112.8-2.8l.1.1a1.7 1.7 0 001.9.3H9a1.7 1.7 0 001-1.5V3a2 2 0 114 0v.1a1.7 1.7 0 001 1.5 1.7 1.7 0 001.9-.3l.1-.1a2 2 0 112.8 2.8l-.1.1a1.7 1.7 0 00-.3 1.9V9a1.7 1.7 0 001.5 1H21a2 2 0 110 4h-.1a1.7 1.7 0 00-1.5 1z"/></svg>Settings</a>
        <a href="track_order"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="6" width="15" height="12" rx="1"/><path d="M16 10h4l3 3v5h-7z"/><circle cx="6" cy="20" r="2"/><circle cx="18" cy="20" r="2"/></svg>Track an order</a>
        <a href="logout.php" class="logout"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><path d="M16 17l5-5-5-5"/><path d="M21 12H9"/></svg>Log out</a>
      </nav>
    </div>

    <div class="account-content reveal">
      <h2>Order history</h2>
      <p class="sub"><?php echo count($orders); ?> orders total. Track any active delivery from the list below.</p>

      <div class="filter-bar">
        <button class="filter-chip active" data-filter="all">All</button>
        <button class="filter-chip" data-filter="processing">Processing</button>
        <button class="filter-chip" data-filter="shipped">Shipped</button>
        <button class="filter-chip" data-filter="delivered">Delivered</button>
        <button class="filter-chip" data-filter="cancelled">Cancelled</button>
      </div>

      
      <div style="background:var(--paper); border:1px solid var(--line); border-radius:18px; padding:6px 28px;">
        <?php if (empty($orders)): ?>
            <div style="padding: 40px 0; text-align: center; color: var(--grey);">
                You have no orders yet. <a href="<?php echo BASE_URL; ?>shop" style="color: var(--ink); text-decoration: underline;">Browse products</a>
            </div>
        <?php else: ?>
            <?php foreach ($orders as $order): ?>
                <?php 
                    $itemsText = [];
                    foreach ($order['items'] as $item) {
                        $itemsText[] = $item['name'] . " &times;" . $item['quantity'];
                    }
                    $itemsStr = implode(', ', $itemsText);
                    $statusClass = strtolower($order['status']);
                ?>
                <div class="order-hist-item" data-category="<?php echo $statusClass; ?>">
                  <div>
                    <div class="order-hist-id"><?php echo htmlspecialchars($order['tracking_number']); ?> &middot; <?php echo date('M j, Y', strtotime($order['created_at'])); ?></div>
                    <div class="order-hist-items"><?php echo htmlspecialchars($itemsStr); ?></div>
                  </div>
                  <span class="status-badge status-<?php echo $statusClass; ?>"><?php echo ucfirst($order['status']); ?></span>
                  <span class="order-hist-total">GH₵ <?php echo number_format($order['total_amount'], 2); ?></span>
                  <a href="<?php echo BASE_URL; ?>track_order" class="btn btn-outline btn-sm">Track</a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
      </div>
  </div>

      <div class="no-results reveal" style="margin-top:20px;">
        <p>No orders match this filter.</p>
      </div>
    </div>

  </div>
</section>


<script>
document.querySelectorAll('.filter-chip').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.filter-chip').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        
        const filter = this.dataset.filter;
        let visibleCount = 0;
        document.querySelectorAll('.order-hist-item').forEach(item => {
            if (filter === 'all' || item.dataset.category === filter) {
                item.style.display = 'flex';
                visibleCount++;
            } else {
                item.style.display = 'none';
            }
        });

        const noResults = document.querySelector('.no-results');
        if (noResults) {
            noResults.style.display = visibleCount === 0 ? 'block' : 'none';
        }
    });
});
</script>
<?php include 'includes/footer.php'; ?>