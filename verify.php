<?php
require_once __DIR__ . '/src/Database.php';
use App\Database;

require_once __DIR__ . '/includes/mailer.php';

$config = require __DIR__ . '/config/config.php';
if (!defined('BASE_URL')) {
    define('BASE_URL', $config['app']['url'] . '/');
}

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
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
            $host = $_SERVER['HTTP_HOST'];
            $loginUrl = $protocol . $host . BASE_URL . "login";
            
            $subject = "Welcome to Ohemaa Detergents!";
            $year = date('Y');
            
            $body = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body style="font-family: Arial, sans-serif; background-color: #fdfbf7; margin: 0; padding: 40px 0;">
    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #fdfbf7;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" border="0" style="background-color: #ffffff; border-radius: 12px; border: 1px solid #e9e6df; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.03);">
                    <tr>
                        <td align="center" style="background-color: #2b1b4d; padding: 40px 0;">
                            <h1 style="color: #e7c766; margin: 0; font-family: Georgia, serif; font-size: 28px; letter-spacing: 1px;">OHEMAA</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 40px 40px 50px 40px; color: #1a1620;">
                            <h2 style="margin-top: 0; font-size: 22px; font-weight: normal; color: #2b1b4d;">Welcome aboard, {$customer['first_name']}!</h2>
                            <p style="font-size: 16px; line-height: 1.6; color: #4a4650;">Your account has been successfully verified and you are now fully onboarded. We are thrilled to have you join the Ohemaa Detergents family.</p>
                            <p style="font-size: 16px; line-height: 1.6; color: #4a4650;">You can now log in to view your orders, update your profile, and shop our signature collection.</p>
                            
                            <div style="text-align: center; margin: 40px 0;">
                                <a href="{$loginUrl}" style="display: inline-block; background-color: #c9a227; color: #16102b; font-weight: bold; font-size: 16px; text-decoration: none; padding: 14px 32px; border-radius: 100px;">Sign In Now</a>
                            </div>
                            
                            <hr style="border: none; border-top: 1px solid #e9e6df; margin: 40px 0 20px 0;">
                            
                            <p style="font-size: 12px; color: #8a8690; text-align: center; margin: 0; line-height: 1.5;">
                                If you're having trouble clicking the button, copy and paste this link into your browser:<br>
                                <a href="{$loginUrl}" style="color: #2b1b4d; word-break: break-all;">{$loginUrl}</a>
                            </p>
                        </td>
                    </tr>
                </table>
                <p style="font-size: 12px; color: #8a8690; text-align: center; margin-top: 20px;">
                    &copy; {$year} Ohemaa Detergents. All rights reserved.
                </p>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
            
            sendMail($customer['email'], $subject, $body);
        }
    } catch (Exception $e) {
        $message = 'Server error. Please try again later.';
    }
}
?>
<?php include 'includes/header.php'; ?>

<div class="auth-shell">
  <div class="auth-visual">
    <svg class="seal" width="56" height="56" viewBox="0 0 60 60" fill="none">
      <circle cx="30" cy="30" r="29" fill="none" stroke="#C9A227" stroke-width="1.5" />
      <circle cx="30" cy="30" r="22" fill="none" stroke="#C9A227" stroke-width="1" />
      <path d="M30 14 L34 26 L47 26 L36.5 33 L40.5 45 L30 37.5 L19.5 45 L23.5 33 L13 26 L26 26 Z" fill="#C9A227" />
      <circle cx="30" cy="30" r="4" fill="#2B1B4D" />
    </svg>
    <?php if ($success): ?>
    <h2>Verified & Ready.</h2>
    <p>Your email is confirmed. You now have full access to your personalized dashboard, order history, and exclusive collections.</p>
    <?php else: ?>
    <h2>Oops.</h2>
    <p>Something went wrong with your verification link. It may be invalid or expired.</p>
    <?php endif; ?>
  </div>

  <div class="auth-form-side">
    <div class="auth-box" style="text-align:center;">
      <?php if ($success): ?>
        <div class="status-icon-circle" style="margin:0 auto 24px; color:#0c0; background:rgba(0,204,0,0.1);">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
          </svg>
        </div>
        <h3 style="font-size:1.4rem; margin-bottom:10px;">Verification Successful</h3>
        <p style="color:rgba(26,22,32,0.62); font-size:0.92rem; margin-bottom:26px;"><?php echo $message; ?></p>
        <a href="login" class="form-submit" style="text-align:center; display:block; text-decoration:none;">Proceed to Login</a>
      <?php else: ?>
        <div class="status-icon-circle" style="margin:0 auto 24px; color:#c00; background:rgba(204,0,0,0.1);">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </div>
        <h3 style="font-size:1.4rem; margin-bottom:10px;">Verification Failed</h3>
        <p style="color:rgba(26,22,32,0.62); font-size:0.92rem; margin-bottom:26px;"><?php echo $message; ?></p>
        <a href="login" class="form-submit" style="text-align:center; display:block; text-decoration:none;">Back to Login</a>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
