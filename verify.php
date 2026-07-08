<?php
require_once __DIR__ . '/src/Database.php';
use App\Database;

require_once __DIR__ . '/includes/mailer.php';

$token = $_GET['token'] ?? '';
$success = false;
$message = 'Invalid or expired verification token.';

if (!empty($token)) {
    try {
        $db = Database::getInstance()->getConnection();
        
        $stmt = $db->prepare("SELECT id, email, first_name FROM customers WHERE verification_token = ?");
        $stmt->execute([$token]);
        $customer = $stmt->fetch();
        
        if ($customer) {
            $updateStmt = $db->prepare("UPDATE customers SET is_verified = 1, verification_token = NULL WHERE id = ?");
            $updateStmt->execute([$customer['id']]);
            $success = true;
            $message = 'Your email has been successfully verified. You can now sign in.';
            
            // Send Welcome Email
            $subject = "Welcome to Ohemaa Detergents!";
            $body = "
            <div style='font-family: sans-serif; max-width: 600px; margin: 0 auto; color: #000;'>
                <h2 style='font-family: serif; font-size: 24px; font-weight: normal; margin-bottom: 20px;'>Welcome aboard, {$customer['first_name']}!</h2>
                <p style='font-size: 14px; font-weight: 300; line-height: 1.6; margin-bottom: 30px;'>
                    Your account has been successfully verified and you are now fully onboarded. 
                    We are thrilled to have you join the Ohemaa Detergents family.
                </p>
                <p style='font-size: 14px; font-weight: 300; line-height: 1.6; margin-bottom: 30px;'>
                    You can now log in to view your orders, update your profile, and shop our signature collection.
                </p>
                <a href='" . BASE_URL . "login' style='display: inline-block; padding: 15px 30px; background-color: #000; color: #fff; text-decoration: none; text-transform: uppercase; letter-spacing: 2px; font-size: 12px; font-weight: 500;'>
                    Sign In Now
                </a>
                <hr style='border: none; border-top: 1px solid #eee; margin: 40px 0;'>
                <p style='font-size: 11px; color: #666;'>Thank you for choosing Ohemaa Detergents.</p>
            </div>";
            
            sendMail($customer['email'], $subject, $body);
        }
    } catch (Exception $e) {
        $message = 'Server error. Please try again later.';
    }
}
?>
<?php include 'includes/header.php'; ?>

<section class="py-5 bg-off-white" style="min-height: 80vh; display: flex; align-items: center;">
    <div class="container px-4">
        <div class="row justify-content-center">
            <div class="col-md-6 bg-white p-5 border border-light text-center">
                <?php if ($success): ?>
                    <i class="bi bi-check-circle text-success mb-4" style="font-size: 3rem;"></i>
                    <h2 class="font-serif mb-3" style="font-size: 2rem;">Verification Successful</h2>
                    <p class="font-sans text-muted mb-4"><?php echo $message; ?></p>
                    <a href="login" class="btn btn-dark rounded-0 px-5 py-3 font-sans text-uppercase letter-spacing-wide">Proceed to Login</a>
                <?php else: ?>
                    <i class="bi bi-x-circle text-danger mb-4" style="font-size: 3rem;"></i>
                    <h2 class="font-serif mb-3" style="font-size: 2rem;">Verification Failed</h2>
                    <p class="font-sans text-muted mb-4"><?php echo $message; ?></p>
                    <a href="login" class="btn btn-outline-dark rounded-0 px-5 py-3 font-sans text-uppercase letter-spacing-wide">Back to Login</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
