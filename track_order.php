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
            $error = "No order found with that reference.";
        }
    } catch (Exception $e) {
        $error = "System error.";
    }
}

include 'includes/header.php';
?>

<div class="container-fluid px-4 px-lg-5 pt-5 mt-5">
    <div class="row pt-5 mb-5 pb-5 border-bottom border-light">
        <div class="col-lg-12 text-center">
            <h1 class="font-serif text-black" style="font-size: 3.5rem;">Track Order</h1>
        </div>
    </div>
</div>

<div class="container-fluid px-4 px-lg-5 mb-5 pb-5" style="min-height: 50vh;">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">
            <div class="text-center mb-5">
                <p class="font-sans text-muted letter-spacing-wide text-uppercase" style="font-size: 0.75rem;">
                    Please enter your order reference to view the current status.
                </p>
            </div>
            
            <form method="GET" class="mb-5 d-flex border border-black">
                <input type="text" name="tracking_number" class="form-control rounded-0 border-0 p-3 bg-transparent font-sans" placeholder="Reference Number" value="<?php echo htmlspecialchars($trackingNumber); ?>" required style="box-shadow: none;">
                <button class="btn btn-black rounded-0 px-4 py-3" type="submit">Track</button>
            </form>

            <?php if ($error): ?>
                <div class="alert bg-off-white text-danger border border-danger mb-5 rounded-0 font-sans p-3 text-center">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if ($order): ?>
                <div class="border border-black p-4 p-lg-5">
                    <div class="d-flex justify-content-between align-items-center mb-4 pb-4 border-bottom border-light">
                        <div>
                            <h5 class="font-serif text-black mb-1" style="font-size: 1.5rem;"><?php echo htmlspecialchars($order['tracking_number']); ?></h5>
                            <p class="font-sans text-muted mb-0" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em;">Placed on <?php echo date('M d, Y', strtotime($order['created_at'])); ?></p>
                        </div>
                        <?php 
                            $status = $order['status'];
                        ?>
                        <span class="font-sans text-uppercase letter-spacing-widest text-black fw-600" style="font-size: 0.75rem;">
                            [ <?php echo htmlspecialchars($status); ?> ]
                        </span>
                    </div>
                    
                    <div class="row g-4 mb-5">
                        <div class="col-6">
                            <span class="font-sans text-uppercase text-muted letter-spacing-wide d-block mb-1" style="font-size: 0.65rem;">Client</span>
                            <span class="font-sans text-black" style="font-size: 0.85rem;"><?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?></span>
                        </div>
                        <div class="col-6 text-end">
                            <span class="font-sans text-uppercase text-muted letter-spacing-wide d-block mb-1" style="font-size: 0.65rem;">Value</span>
                            <span class="font-sans text-black fw-600" style="font-size: 0.85rem;">GHS <?php echo number_format($order['total_amount'], 2); ?></span>
                        </div>
                    </div>

                    <!-- Progress Visualizer -->
                    <div class="position-relative mt-4 mb-3">
                        <div class="progress rounded-0" style="height: 1px; background-color: var(--light-grey);">
                            <?php 
                                $progress = 25;
                                if ($status === 'processing') $progress = 50;
                                if ($status === 'shipped') $progress = 75;
                                if ($status === 'completed') $progress = 100;
                                if ($status === 'cancelled') $progress = 0;
                            ?>
                            <div class="progress-bar bg-black" role="progressbar" style="width: <?php echo $progress; ?>%"></div>
                        </div>
                        <div class="d-flex justify-content-between position-absolute top-50 start-0 w-100 translate-middle-y px-1">
                            <div class="rounded-circle bg-black" style="width: 8px; height: 8px;"></div>
                            <div class="rounded-circle <?php echo $progress >= 50 ? 'bg-black' : 'bg-white border border-light'; ?>" style="width: 8px; height: 8px;"></div>
                            <div class="rounded-circle <?php echo $progress >= 75 ? 'bg-black' : 'bg-white border border-light'; ?>" style="width: 8px; height: 8px;"></div>
                            <div class="rounded-circle <?php echo $progress >= 100 ? 'bg-black' : 'bg-white border border-light'; ?>" style="width: 8px; height: 8px;"></div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between font-sans text-uppercase text-muted letter-spacing-widest" style="font-size: 0.55rem;">
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

<?php include 'includes/footer.php'; ?>
