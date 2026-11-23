<?php
/**
 * Router untuk PHP built-in server (live preview / dev).
 * Jalankan: php -S 127.0.0.1:8080 -t public server.php
 */

$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$file = __DIR__ . '/public' . $uri;

// Biarkan server built-in melayani berkas statis yang ada
if ($uri !== '/' && is_file($file)) {
    return false;
}

require __DIR__ . '/public/index.php';
