<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Only require autoload if it exists (in case composer was used)
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

function sendMail($to, $subject, $body)
{
    // These should be loaded from the .env file using getenv() or $_ENV if populated by dotenv.
    // For this example, assuming a global $env array or similar mechanism is used,
    // or we fetch directly via getenv() assuming the app loads .env into environment variables.

    // We'll require a small function to read .env if it isn't loaded, or assume it's loaded.
    // Let's manually parse .env here just in case, since ohemaa might not have a dotenv library.
    $envPath = __DIR__ . '/../.env';
    $env = [];
    if (file_exists($envPath)) {
        $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0)
                continue;
            list($name, $value) = explode('=', $line, 2);
            $env[trim($name)] = trim($value);
        }
    }

    $mail = new PHPMailer(true);

    try {
        //Server settings
        $mail->isSMTP();
        $mail->Host = $env['SMTP_HOST'] ?? '';
        $mail->SMTPAuth = true;
        $mail->Username = $env['SMTP_USER'] ?? '';
        $mail->Password = $env['SMTP_PASS'] ?? '';
        $mail->SMTPSecure = $env['SMTP_SECURE'] ?? PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = $env['SMTP_PORT'] ?? 465;

        //Recipients
        $fromEmail = $env['SMTP_FROM_EMAIL'] ?? 'alerts@qwiktransfers.com';
        $fromName = $env['SMTP_FROM_NAME'] ?? 'Ohemaa Detergents';
        $mail->setFrom($fromEmail, $fromName);
        $mail->addAddress($to);

        //Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;

        $mail->send();
        return true;
    } catch (Exception $e) {
        // Log error or return false
        error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}
