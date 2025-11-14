<?php

// Autoloader - use Composer if available, otherwise manual
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require __DIR__ . '/vendor/autoload.php';
} else {
    // Manual autoloader fallback
    spl_autoload_register(function($class){
        if(strpos($class, 'Src\\') === 0){
            $classPath = str_replace('\\', '/', substr($class, 4));
            $file = __DIR__ . '/src/' . $classPath . '.php';
            if(file_exists($file)){
                require $file;
            }else{
                error_log("Autoload gagal: $file tidak ditemukan");
            }
        }
    });
}

$cfg = require __DIR__ . '/config/env.php';
use Src\Helpers\Response;
use Src\Middlewares\CorsMiddleware;

CorsMiddleware::handle($cfg);

if($_SERVER['REQUEST_METHOD'] === 'OPTIONS'){
    http_response_code(204);
    exit;
}

/* ===========================================================
   🛡️ RATE LIMITING (Batasi request per IP)
   =========================================================== */
try {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $limit = 5;          // Maksimal request per jangka waktu
    $window = 60;          // Dalam detik (60 = 1 menit)
    $rateDir = __DIR__ . '/storage/ratelimit/';

    if (!is_dir($rateDir)) {
        if (!mkdir($rateDir, 0777, true)) {
            // Skip rate limiting if can't create directory
            goto skip_rate_limit;
        }
    }

    $file = $rateDir . md5($ip) . '.json';
    $now = time();

    if (file_exists($file)) {
        $data = json_decode(file_get_contents($file), true);
        if (!$data) {
            $data = ['count' => 0, 'start' => $now];
        }
    } else {
        $data = ['count' => 0, 'start' => $now];
    }

    // Reset window jika sudah lewat waktunya
    if ($now - $data['start'] > $window) {
        $data = ['count' => 0, 'start' => $now];
    }

    // Tambah hit
    $data['count']++;

    // Cek batas
    if ($data['count'] > $limit) {
        $retryAfter = $window - ($now - $data['start']);
        header('Retry-After: ' . $retryAfter);
        Response::jsonError(429, 'Too Many Requests ');
        exit;
    }

    // Simpan data rate limit
    file_put_contents($file, json_encode($data));
} catch (Exception $e) {
    // Skip rate limiting on error
    skip_rate_limit:
}

/* ===========================================================
   🚦 Routing dasar
   =========================================================== */
$uri = strtok($_SERVER['REQUEST_URI'], '?');
$base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
$path = '/' . trim(str_replace($base, '', $uri), '/');
$method = $_SERVER['REQUEST_METHOD'];

// ===== Daftar Route =====
$routes = [
    ['GET', '/api/v1/health', 'Src\Controllers\HealthController@show'],
    ['GET', '/api/v1/version', 'Src\Controllers\VersionController@show'],
    ['POST', '/api/v1/auth/login', 'Src\Controllers\AuthController@login'],
    ['GET', '/api/v1/users', 'Src\Controllers\UserController@index'],
    ['GET', '/api/v1/users/{id}', 'Src\Controllers\UserController@show'],
    ['POST', '/api/v1/users', 'Src\Controllers\UserController@store'],
    ['PUT', '/api/v1/users/{id}', 'Src\Controllers\UserController@update'],
    ['DELETE', '/api/v1/users/{id}', 'Src\Controllers\UserController@destroy'],
    ['GET', '/api/v1/uploads', 'Src\Controllers\UploadController@index'],
    ['POST', '/api/v1/upload', 'Src\Controllers\UploadController@store'],
];

// ===== Fungsi untuk mencocokkan route =====
function matchRoute($routes, $method, $path)
{
    foreach ($routes as $r) {
        [$m, $p, $h] = $r;
        if ($m !== $method) {
            continue;
        }

        $regex = preg_replace('#\{([^/]+)\}#', '([^/]+)', $p);
        if (preg_match('#^' . $regex . '$#', $path, $match)) {
            array_shift($match);
            return [$h, $match];
        }
    }
    return [null, null];
}

// ===== Jalankan routing =====
try {
    [$handler, $params] = matchRoute($routes, $method, $path);

    if (!$handler) {
        Response::jsonError(404, 'Route not found');
    }

    [$class, $action] = explode('@', $handler);

    if (!method_exists($class, $action)) {
        Response::jsonError(405, 'Method not allowed');
    }

    call_user_func_array([new $class($cfg), $action], $params);
} catch (Exception $e) {
    Response::jsonError(500, 'Internal Server Error: ' . $e->getMessage());
}
