<?php
namespace Src\Helpers;

class RateLimiter {
    protected static function storagePath($key) {
        return sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'rate_' . sha1($key) . '.json';
    }

    public static function check($key, $maxAttempts, $decaySeconds) {
        $path = self::storagePath($key);
        $now = time();
        $data = ['attempts' => 0, 'expires_at' => $now + $decaySeconds];
        if (file_exists($path)) {
            $decoded = json_decode(file_get_contents($path), true);
            if (is_array($decoded) && isset($decoded['attempts'], $decoded['expires_at'])) {
                $data = $decoded;
            }
        }
        if ($now >= $data['expires_at']) {
            $data = ['attempts' => 0, 'expires_at' => $now + $decaySeconds];
        }
        if ($data['attempts'] >= $maxAttempts) {
            return false;
        }
        $data['attempts']++;
        file_put_contents($path, json_encode($data), LOCK_EX);
        return true;
    }
}