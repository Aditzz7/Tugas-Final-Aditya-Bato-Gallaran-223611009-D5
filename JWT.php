<?php
header('Content-Type: application/json');

// ===== KONFIGURASI =====
$secret = 'password_hash_valen_99_numerik_api';
$token_ttl_seconds = 300; 
function base64UrlEncode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function base64UrlDecode($data) {
    $remainder = strlen($data) % 4;
    if ($remainder) $data .= str_repeat('=', 4 - $remainder);
    return base64_decode(strtr($data, '-_', '+/'));
}

function getAuthorizationHeader() {
    if (function_exists('getallheaders')) {
        $headers = getallheaders();
        foreach ($headers as $k => $v) {
            if (strtolower($k) === 'authorization') return $v;
        }
    }
    if (!empty($_SERVER['HTTP_AUTHORIZATION'])) return $_SERVER['HTTP_AUTHORIZATION'];
    if (!empty($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) return $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
    return null;
}

function json_response($data, $code = 200) {
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit();
}

// ===== CEK APAKAH ADA TOKEN DI HEADER =====
$authHeader = getAuthorizationHeader();

// Jika ADA token (verifikasi)

if ($authHeader) {
    if (!preg_match('/Bearer\s(\S+)/i', $authHeader, $matches)) {
        json_response(['status' => 'error', 'message' => 'Format Authorization salah (gunakan Bearer <token>)'], 400);
    }
    $jwt = $matches[1];

    // Pisah token
    $parts = explode('.', $jwt);
    if (count($parts) !== 3) {
        json_response(['status' => 'error', 'message' => 'Format token tidak valid'], 400);
    }

    list($header64, $payload64, $signatureProvided) = $parts;

    // Buat ulang signature
    $expected = rtrim(strtr(base64_encode(hash_hmac('sha256', "$header64.$payload64", $secret, true)), '+/', '-_'), '=');

    // Cek signature
    if (!hash_equals($expected, $signatureProvided)) {
        json_response(['status' => 'error', 'message' => 'Signature token tidak valid'], 401);
    }

    // Decode payload
    $payload = json_decode(base64UrlDecode($payload64), true);
    if (!is_array($payload)) {
        json_response(['status' => 'error', 'message' => 'Payload tidak bisa dibaca'], 400);
    }

    if (!isset($payload['exp']) || time() >= (int) $payload['exp']) {
        json_response([
            'status' => 'error',
            'message' => 'Token sudah kedaluwarsa',
            'payload' => $payload,
            'token_status' => 'EXPIRED'
        ], 401);
    }

    json_response([
        'status' => 'valid',
        'message' => 'Token masih berlaku',
        'payload' => $payload,
        'token_status' => 'ACTIVE'
    ]);
}

$header = ['alg' => 'HS256'];
$issuedAt = time();
$expiresAt = $issuedAt + $token_ttl_seconds;
$payload = [
    'sub' => 1,
    'name' => 'Valen',
    'role' => 'Admin',
    'iat' => $issuedAt,
    'exp' => $expiresAt
];

$header64 = base64UrlEncode(json_encode($header));
$payload64 = base64UrlEncode(json_encode($payload));
$signature = rtrim(strtr(base64_encode(hash_hmac('sha256', "$header64.$payload64", $secret, true)), '+/', '-_'), '=');
$jwt = "$header64.$payload64.$signature";

json_response([
    'status' => 'token_generated',
    'message' => 'Token berhasil dibuat dan akan kedaluwarsa dalam beberapa menit',
    'token' => $jwt,
    'length' => strlen($jwt),
    'payload' => $payload
]);