<?php
require_once __DIR__ . '/src/Database.php';

use App\Database;

$trackingNumber = $_GET['tracking_number'] ?? '';
$order = null;
$error = '';

if ($trackingNumber) {
    try {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT o.tracking_number, o.total_amount, o.status, o.created_at, c.first_name, c.last_name 
                              FROM orders o 
                              JOIN customers c ON o.customer_id = c.id 
                              WHERE o.tracking_number = ?");
        $stmt->execute([$trackingNumber]);
        $order = $stmt->fetch();
        
        if (!$order) {
            $error = "No order found with that tracking number.";
        }
    } catch (Exception $e) {
        $error = "An error occurred.";
    }
}

include 'includes/header.php';
?>

<div class="bg-gold-soft py-5 mt-5">
    <div class="container py-4 text-center">
        <h1 class="display-4 fw-800 mb-0">Track Your Royal Package</h1>
    </div>
</div>

<div class="container py-5 mb-5" style="min-height: 60vh;">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8 reveal">
            <div class="glass p-5 rounded-lg border-0 shadow-sm" style="border-radius: var(--radius-lg);">
                <div class="text-center mb-5">
                    <div class="bg-gold-soft p-4 rounded-circle d-inline-flex mb-4">
                        <i class="bi bi-box-seam fs-1 text-gold"></i>
                    </div>
                    <p class="text-muted">Enter your tracking number to see the current status of your order.</p>
                </div>
                
                <form method="GET" class="mb-5">
                    <div class="input-group glass p-1 rounded-pill">
                        <input type="text" name="tracking_number" class="form-control bg-transparent border-0 px-4 py-3" placeholder="ORD-XXXXXX" value="<?php echo htmlspecialchars($trackingNumber); ?>" required style="box-shadow: none;">
                        <button class="btn btn-gold rounded-pill px-5" type="submit">Track Order</button>
                    </div>
                </form>

                <?php if ($error): ?>
                    <div class="alert alert-danger border-0 rounded-lg p-3 d-flex align-items-center mb-0">
                        <i class="bi bi-exclamation-circle me-3"></i>
                        <span><?php echo htmlspecialchars($error); ?></span>
                    </div>
                <?php endif; ?>

                <?php if ($order): ?>
                    <div class="bg-white p-4 rounded-lg border shadow-sm">
                        <div class="d-flex justify-content-between align-items-start mb-4">
                            <div>
                                <h5 class="fw-bold text-primary mb-1"><?php echo htmlspecialchars($order['tracking_number']); ?></h5>
                                <p class="small text-muted mb-0">Ordered on <?php echo date('M d, Y', strtotime($order['created_at'])); ?></p>
                            </div>
                            <?php 
                                $status = $order['status'];
                                $badgeStyle = 'background: rgba(108, 117, 125, 0.1); color: #6c757d;';
                                if ($status === 'pending') $badgeStyle = 'background: rgba(255, 193, 7, 0.1); color: #856404;';
                                if ($status === 'processing') $badgeStyle = 'background: rgba(13, 110, 253, 0.1); color: #0d6efd;';
                                if ($status === 'completed') $badgeStyle = 'background: rgba(25, 135, 84, 0.1); color: #198754;';
                                if ($status === 'cancelled') $badgeStyle = 'background: rgba(220, 53, 69, 0.1); color: #dc3545;';
                            ?>
                            <span class="badge rounded-pill px-3 py-2 text-capitalize" style="<?php echo $badgeStyle; ?>"><?php echo htmlspecialchars($status); ?></span>
                        </div>
                        
                        <div class="row g-3">
                            <div class="col-6">
                                <span class="small text-muted d-block">Recipient</span>
                                <span class="fw-bold"><?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?></span>
                            </div>
                            <div class="col-6 text-end">
                                <span class="small text-muted d-block">Total Value</span>
                                <span class="fw-bold text-gold">GHS <?php echo number_format($order['total_amount'], 2); ?></span>
                            </div>
                        </div>

                        <hr class="my-4 opacity-10">

                        <!-- Progress Bar Visualizer -->
                        <div class="position-relative mt-5 mb-3 px-2">
                            <div class="progress" style="height: 4px;">
                                <?php 
                                    $progress = 25;
                                    if ($status === 'processing') $progress = 50;
                                    if ($status === 'shipped') $progress = 75;
                                    if ($status === 'completed') $progress = 100;
                                    if ($status === 'cancelled') $progress = 0;
                                ?>
                                <div class="progress-bar bg-gold" role="progressbar" style="width: <?php echo $progress; ?>%"></div>
                            </div>
                            <div class="d-flex justify-content-between position-absolute top-50 start-0 w-100 translate-middle-y">
                                <div class="rounded-circle bg-gold" style="width: 12px; height: 12px;"></div>
                                <div class="rounded-circle <?php echo $progress >= 50 ? 'bg-gold' : 'bg-light border'; ?>" style="width: 12px; height: 12px;"></div>
                                <div class="rounded-circle <?php echo $progress >= 75 ? 'bg-gold' : 'bg-light border'; ?>" style="width: 12px; height: 12px;"></div>
                                <div class="rounded-circle <?php echo $progress >= 100 ? 'bg-gold' : 'bg-light border'; ?>" style="width: 12px; height: 12px;"></div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between small text-muted">
                            <span>Placed</span>
                            <span>Processed</span>
                            <span>Shipped</span>
                            <span>Delivered</span>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
