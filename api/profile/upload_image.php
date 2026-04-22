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

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;
if ($_SERVER['REQUEST_METHOD'] !== 'POST') Helpers::jsonResponse(405, 'Method Not Allowed');

$admin = Auth::requireAdmin();

if (!isset($_FILES['profile_image'])) {
    Helpers::jsonResponse(400, 'No image uploaded');
}

$file = $_FILES['profile_image'];
$allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
if (!in_array($file['type'], $allowedTypes)) {
    Helpers::jsonResponse(400, 'Invalid file type. Only JPG, PNG and WEBP allowed.');
}

$uploadDir = __DIR__ . '/../../public/uploads/profile/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = 'admin_' . $admin['admin_id'] . '_' . time() . '.' . $ext;
$targetPath = $uploadDir . $filename;
$dbPath = 'public/uploads/profile/' . $filename;

if (move_uploaded_file($file['tmp_name'], $targetPath)) {
    try {
        $db = Database::getInstance()->getConnection();
        
        // Delete old image if exists
        $stmt = $db->prepare("SELECT profile_image FROM admins WHERE id = ?");
        $stmt->execute([$admin['admin_id']]);
        $oldImage = $stmt->fetchColumn();
        if ($oldImage && file_exists(__DIR__ . '/../../' . $oldImage)) {
            unlink(__DIR__ . '/../../' . $oldImage);
        }

        $stmt = $db->prepare("UPDATE admins SET profile_image = ? WHERE id = ?");
        $stmt->execute([$dbPath, $admin['admin_id']]);
        
        Helpers::logAction($db, 'update_profile_image', "Updated profile image", $admin['admin_id']);
        
        Helpers::jsonResponse(200, 'Profile image updated successfully', ['image_url' => $dbPath]);
    } catch (\Exception $e) {
        Helpers::jsonResponse(500, 'Server error');
    }
} else {
    Helpers::jsonResponse(500, 'Failed to save uploaded file');
}
