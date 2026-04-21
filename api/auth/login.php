<?php
require_once __DIR__ . '/../../src/Database.php';
require_once __DIR__ . '/../../src/Helpers.php';
require_once __DIR__ . '/../../src/Auth.php';

use App\Database;
use App\Helpers;
use App\Auth;

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Helpers::jsonResponse(405, 'Method Not Allowed');
}

$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

$email = trim($input['email'] ?? '');
$password = $input['password'] ?? '';

if (empty($email) || empty($password)) {
    Helpers::jsonResponse(400, 'Email and password are required');
}

try {
    $db = Database::getInstance()->getConnection();
    
    $stmt = $db->prepare("SELECT id, admin_id, name, email, password_hash FROM admins WHERE email = ?");
    $stmt->execute([$email]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin['password_hash'])) {
        
        $payload = [
            'admin_id' => $admin['id'],
            'uuid' => Helpers::uuidBinToStr($admin['admin_id']),
            'email' => $admin['email'],
            'name' => $admin['name'],
            'role' => 'admin'
        ];
        
        $token = Auth::generateJwt($payload);
        Helpers::logAction($db, 'admin_login', 'Admin logged in successfully', $admin['id'], null);

        Helpers::jsonResponse(200, 'Login successful', [
            'token' => $token,
            'user' => [
                'name' => $admin['name'],
                'email' => $admin['email']
            ]
        ]);
    } else {
        Helpers::logAction($db, 'admin_login_failed', "Failed login attempt for email: $email", null, null);
        Helpers::jsonResponse(401, 'Invalid credentials');
    }
} catch (\Exception $e) {
    Helpers::jsonResponse(500, 'Server error');
}
