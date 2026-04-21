<?php

namespace App;

class Auth
{
    private static string $secretKey = 'your_super_secret_jwt_key_should_be_in_env_in_production';
    
    public static function getSecret(): string
    {
        return $_ENV['JWT_SECRET'] ?? self::$secretKey;
    }

    public static function generateJwt(array $payload): string
    {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        
        $payload['iat'] = time();
        $payload['exp'] = time() + (60 * 60 * 24); // 24 hours
        
        $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode(json_encode($payload)));
        
        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, self::getSecret(), true);
        $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
        
        return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    }

    public static function validateJwt(string $jwt): ?array
    {
        $parts = explode('.', $jwt);
        if (count($parts) !== 3) {
            return null;
        }

        list($header, $payload, $signature) = $parts;

        $validSignature = hash_hmac('sha256', $header . "." . $payload, self::getSecret(), true);
        $base64UrlValidSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($validSignature));

        if (hash_equals($base64UrlValidSignature, $signature)) {
            $decodedPayload = json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], $payload)), true);
            if (isset($decodedPayload['exp']) && $decodedPayload['exp'] >= time()) {
                return $decodedPayload;
            }
        }
        return null;
    }

    public static function requireAdmin(): array
    {
        $token = Helpers::getBearerToken();
        if (!$token) {
            Helpers::jsonResponse(401, 'Unauthorized - Missing token');
        }

        $payload = self::validateJwt($token);
        if (!$payload || !isset($payload['admin_id'])) {
            Helpers::jsonResponse(401, 'Unauthorized - Invalid or expired token');
        }

        return $payload;
    }
}
