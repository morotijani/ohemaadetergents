<?php
require_once __DIR__ . '/includes/mailer.php';

echo "Testing mailer...\n";
$result = sendMail('alerts@qwiktransfers.com', 'Test Email', 'This is a test email');
var_dump($result);
