<?php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../src/Database.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$email = trim($input['email'] ?? '');

if (empty($email)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Email is required']);
    exit;
}

try {
    $db = App\Database::getInstance()->getConnection();
    
    $stmt = $db->prepare("SELECT id FROM customers WHERE email = ?");
    $stmt->execute([$email]);
    $customer = $stmt->fetch();
    
    if ($customer) {
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        $updateStmt = $db->prepare("UPDATE customers SET reset_token = ?, reset_token_expires_at = ? WHERE id = ?");
        $updateStmt->execute([$token, $expires, $customer['id']]);
        
        $resetLink = 'http://' . $_SERVER['HTTP_HOST'] . '/ohemaadetergents/reset_password?token=' . $token;
        
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = $_ENV['SMTP_HOST'] ?? '';
            $mail->SMTPAuth   = true;
            $mail->Username   = $_ENV['SMTP_USER'] ?? '';
            $mail->Password   = $_ENV['SMTP_PASS'] ?? '';
            $mail->SMTPSecure = $_ENV['SMTP_SECURE'] ?? 'ssl';
            $mail->Port       = $_ENV['SMTP_PORT'] ?? 465;

            $mail->setFrom($_ENV['SMTP_FROM_EMAIL'] ?? 'noreply@ohemaa-detergents.com', $_ENV['SMTP_FROM_NAME'] ?? 'Ohemaa Detergents');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = "Ohemaa Clean - Password Reset";
            
            $year = date('Y');
            $htmlBody = <<<HTML
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
                            <h2 style="margin-top: 0; font-size: 22px; font-weight: normal; color: #2b1b4d;">Password Reset</h2>
                            <p style="font-size: 16px; line-height: 1.6; color: #4a4650;">We received a request to reset the password associated with your account. If you made this request, please click the button below to securely choose a new password.</p>
                            
                            <div style="text-align: center; margin: 40px 0;">
                                <a href="{$resetLink}" style="display: inline-block; background-color: #c9a227; color: #16102b; font-weight: bold; font-size: 16px; text-decoration: none; padding: 14px 32px; border-radius: 100px;">Reset Password</a>
                            </div>
                            
                            <p style="font-size: 14px; line-height: 1.6; color: #4a4650; margin-bottom: 0;">If you didn't request a password reset, you can safely ignore this email. Your account remains secure.</p>
                            
                            <hr style="border: none; border-top: 1px solid #e9e6df; margin: 40px 0 20px 0;">
                            
                            <p style="font-size: 12px; color: #8a8690; text-align: center; margin: 0; line-height: 1.5;">
                                If you're having trouble clicking the button, copy and paste this link into your browser:<br>
                                <a href="{$resetLink}" style="color: #2b1b4d; word-break: break-all;">{$resetLink}</a>
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

            $mail->Body = $htmlBody;
            $mail->AltBody = "We received a request to reset your password.\n\n" .
                             "Copy and paste the link below into your browser to set a new password:\n" . 
                             $resetLink . "\n\n" .
                             "If you did not request this, please ignore this email.";

            $mail->send();
            error_log("Password reset email sent to $email via SMTP.");
        } catch (Exception $e) {
            error_log("Mailer Error: {$mail->ErrorInfo}");
            error_log("Password reset link (fallback): $resetLink");
        }
    }

    echo json_encode(['status' => 'success', 'message' => 'If an account exists with that email, a reset link has been sent.']);

} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'An error occurred during password reset']);
}
