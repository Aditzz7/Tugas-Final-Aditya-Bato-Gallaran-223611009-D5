<?php

namespace Src\Middlewares;

class AuthMiddleware
{
    private static function base64UrlDecode($data)
    {
        $data = str_replace(['-', '_'], ['+', '/'], $data);
        $mod4 = strlen($data) % 4;
        if ($mod4) {
            $data .= substr('====', $mod4);
        }
        return base64_decode($data);
    }

    private static function base64UrlEncode($data)
    {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
    }

    public static function handle(array $cfg)
    {
        $headers = getallheaders();
        $auth = $headers['Authorization'] ?? $_SERVER['HTTP_AUTHORIZATION'] ?? '';

        if (empty($auth)) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Authorization header required']);
            exit;
        }

        if (!preg_match('/Bearer\s+(\S+)/i', $auth, $matches)) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Invalid authorization format']);
            exit;
        }

        $token = $matches[1];

        // Simple JWT validation (basic implementation)
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Invalid token format']);
            exit;
        }

        $header = $parts[0];
        $payload = $parts[1];
        $signature = $parts[2];

        $secret = $cfg['app']['jwt_secret'] ?? 'password_hash_valen_99_numerik_api';
        $expectedSignature = self::base64UrlEncode(hash_hmac('sha256', $header . "." . $payload, $secret, true));

        if (!hash_equals($signature, $expectedSignature)) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Invalid token']);
            exit;
        }

        // Decode payload to check expiration
        $payloadData = json_decode(self::base64UrlDecode($payload), true);
        if (!$payloadData || !isset($payloadData['exp']) || $payloadData['exp'] < time()) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Token expired']);
            exit;
        }

        // Store user data for use in controllers
        $_SERVER['AUTH_USER'] = $payloadData;
    }
}