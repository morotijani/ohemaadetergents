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
}

include 'includes/header.php';
?>

<section class="py-5 bg-off-white" style="min-height: 80vh;">
    <div class="container px-4 mt-5 pt-4">
        <div class="row">
            <div class="col-12 mb-4">
                <h2 class="font-serif" style="font-size: 2.5rem;">My Account</h2>
            </div>
            
            <!-- Sidebar -->
            <div class="col-md-3 mb-5">
                <div class="list-group rounded-0 border-0">
                    <a href="profile" class="list-group-item list-group-item-action border-0 px-0 py-3 font-sans text-uppercase letter-spacing-wide bg-transparent text-muted border-bottom">Profile Details</a>
                    <a href="profile_orders" class="list-group-item list-group-item-action border-0 px-0 py-3 font-sans text-uppercase letter-spacing-wide bg-transparent fw-bold text-dark border-bottom">Order History</a>
                    <a href="profile_password" class="list-group-item list-group-item-action border-0 px-0 py-3 font-sans text-uppercase letter-spacing-wide bg-transparent text-muted border-bottom">Change Password</a>
                    <a href="#" onclick="logoutUser()" class="list-group-item list-group-item-action border-0 px-0 py-3 font-sans text-uppercase letter-spacing-wide bg-transparent text-muted">Sign Out</a>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-8 ps-md-5">
                <h4 class="font-sans text-uppercase letter-spacing-wide mb-4 text-dark" style="font-size: 0.9rem;">Order History</h4>
                
                <?php if (empty($orders)): ?>
                    <div class="bg-white p-5 border border-light text-center">
                        <p class="font-sans text-muted mb-0">You have no orders yet.</p>
                        <a href="shop" class="btn btn-dark rounded-0 mt-4 px-4 py-2 font-sans text-uppercase letter-spacing-wide" style="font-size: 0.8rem;">Browse Collection</a>
                    </div>
                <?php else: ?>
                    <div class="accordion rounded-0" id="ordersAccordion">
                        <?php foreach ($orders as $index => $order): ?>
                            <div class="accordion-item border-0 border-bottom border-light rounded-0 bg-transparent mb-3">
                                <h2 class="accordion-header" id="heading<?php echo $order['id']; ?>">
                                    <button class="accordion-button collapsed bg-white rounded-0 shadow-none px-4 py-4" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo $order['id']; ?>" aria-expanded="false" aria-controls="collapse<?php echo $order['id']; ?>">
                                        <div class="d-flex w-100 justify-content-between align-items-center me-3">
                                            <div>
                                                <span class="font-sans text-uppercase text-muted d-block" style="font-size: 0.7rem;">Order Placed</span>
                                                <span class="font-sans fw-bold" style="font-size: 0.9rem;"><?php echo date('M d, Y', strtotime($order['created_at'])); ?></span>
                                            </div>
                                            <div class="text-center d-none d-sm-block">
                                                <span class="font-sans text-uppercase text-muted d-block" style="font-size: 0.7rem;">Total</span>
                                                <span class="font-sans fw-bold" style="font-size: 0.9rem;">GHS <?php echo number_format($order['total_amount'], 2); ?></span>
                                            </div>
                                            <div class="text-end">
                                                <span class="font-sans text-uppercase text-muted d-block" style="font-size: 0.7rem;">Status</span>
                                                <span class="badge bg-<?php echo $order['status'] === 'completed' ? 'success' : ($order['status'] === 'cancelled' ? 'danger' : 'dark'); ?> rounded-0 font-sans fw-normal px-2 py-1" style="font-size: 0.7rem;"><?php echo ucfirst($order['status']); ?></span>
                                            </div>
                                        </div>
                                    </button>
                                </h2>
                                <div id="collapse<?php echo $order['id']; ?>" class="accordion-collapse collapse" aria-labelledby="heading<?php echo $order['id']; ?>" data-bs-parent="#ordersAccordion">
                                    <div class="accordion-body bg-white border-top border-light px-4 py-4">
                                        
                                        <?php if (!empty($order['tracking_number'])): ?>
                                        <div class="mb-4">
                                            <span class="font-sans text-uppercase text-muted" style="font-size: 0.7rem;">Tracking Number:</span>
                                            <span class="font-sans fw-bold ms-2" style="font-size: 0.9rem;"><?php echo htmlspecialchars($order['tracking_number']); ?></span>
                                        </div>
                                        <?php endif; ?>

                                        <?php foreach ($order['items'] as $item): ?>
                                            <div class="d-flex mb-3 align-items-center">
                                                <img src="<?php echo htmlspecialchars($item['image_url'] ?: 'https://via.placeholder.com/100x100?text=Ohemaa'); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" style="width: 60px; height: 60px; object-fit: cover;" class="me-3">
                                                <div class="flex-grow-1">
                                                    <h6 class="font-serif mb-1" style="font-size: 1rem;"><?php echo htmlspecialchars($item['name']); ?></h6>
                                                    <span class="font-sans text-muted" style="font-size: 0.8rem;">Qty: <?php echo $item['quantity']; ?></span>
                                                </div>
                                                <div class="text-end font-sans fw-bold" style="font-size: 0.9rem;">
                                                    GHS <?php echo number_format($item['unit_price'] * $item['quantity'], 2); ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<script>
async function logoutUser() {
    try {
        const res = await fetch('api/auth/customer_logout.php', { method: 'POST' });
        const result = await res.json();
        if (res.ok && result.status === 'success') {
            window.location.href = result.data.redirect;
        }
    } catch (e) {
        console.error(e);
    }
}
</script>

<?php include 'includes/footer.php'; ?>
