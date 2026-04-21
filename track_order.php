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
<div class="container py-5" style="min-height: 60vh;">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm p-4">
                <h3 class="mb-4 text-center">Track Your Order</h3>
                
                <form method="GET" class="mb-4">
                    <div class="input-group">
                        <input type="text" name="tracking_number" class="form-control form-control-lg" placeholder="Enter Tracking Number (e.g., ORD-1A2B3C)" value="<?php echo htmlspecialchars($trackingNumber); ?>" required>
                        <button class="btn btn-gold px-4" type="submit">Track</button>
                    </div>
                </form>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <?php if ($order): ?>
                    <div class="bg-light p-4 rounded-3 border">
                        <h5 class="mb-3">Order Details</h5>
                        <p class="mb-2"><strong>Customer:</strong> <?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?></p>
                        <p class="mb-2"><strong>Date:</strong> <?php echo date('F j, Y', strtotime($order['created_at'])); ?></p>
                        <p class="mb-2"><strong>Total Amount:</strong> GHS <?php echo number_format($order['total_amount'], 2); ?></p>
                        
                        <div class="mt-4">
                            <strong>Status:</strong> 
                            <?php 
                                $status = $order['status'];
                                $badgeClass = 'bg-secondary';
                                if ($status === 'pending') $badgeClass = 'bg-warning text-dark';
                                if ($status === 'processing') $badgeClass = 'bg-primary';
                                if ($status === 'completed') $badgeClass = 'bg-success';
                                if ($status === 'cancelled') $badgeClass = 'bg-danger';
                            ?>
                            <span class="badge <?php echo $badgeClass; ?> fs-6 ms-2 text-capitalize"><?php echo htmlspecialchars($status); ?></span>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
