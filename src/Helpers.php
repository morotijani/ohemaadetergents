<?php

namespace App;

use Exception;

class Helpers
{
    public static function jsonResponse(int $statusCode, string $message, array $data = [])
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'status' => $statusCode >= 200 && $statusCode < 300 ? 'success' : 'error',
            'message' => $message,
            'data' => $data
        ]);
        exit;
    }

    public static function generateUuidV7Binary(): string
    {
        $timestamp = (int) (microtime(true) * 1000);
        $tsHex = str_pad(dechex($timestamp), 12, '0', STR_PAD_LEFT);
        $randomBytes = random_bytes(10);
        $randomHex = bin2hex($randomBytes);
        
        $versionAndRandom = '7' . substr($randomHex, 1, 3);
        $variantAndRandom = dechex(hexdec(substr($randomHex, 4, 1)) & 0x3 | 0x8) . substr($randomHex, 5);
        
        $uuidHex = $tsHex . $versionAndRandom . $variantAndRandom;
        return hex2bin($uuidHex);
    }
    
    public static function uuidBinToStr(string $bin): string
    {
        $hex = bin2hex($bin);
        return sprintf(
            '%s-%s-%s-%s-%s',
            substr($hex, 0, 8),
            substr($hex, 8, 4),
            substr($hex, 12, 4),
            substr($hex, 16, 4),
            substr($hex, 20, 12)
        );
    }

    public static function getBearerToken(): ?string
    {
        $headers = null;
        if (isset($_SERVER['Authorization'])) {
            $headers = trim($_SERVER["Authorization"]);
        } else if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
        } elseif (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
            if (isset($requestHeaders['Authorization'])) {
                $headers = trim($requestHeaders['Authorization']);
            }
        }
        
        if (!empty($headers)) {
            if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
                return $matches[1];
            }
        }
        return null;
    }

    public static function getClientIp(): string
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        return $ip;
    }

    public static function logAction(\PDO $db, string $action, string $description, ?int $adminId = null, ?int $customerId = null)
    {
        $stmt = $db->prepare("INSERT INTO logs (log_id, admin_id, customer_id, action, description, ip_address) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            self::generateUuidV7Binary(),
            $adminId,
            $customerId,
            $action,
            $description,
            self::getClientIp()
        ]);
    }
}
