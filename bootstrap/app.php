<?php
/**
 * Bootstrap aplikasi: environment, autoloader, konfigurasi, session.
 * Dipanggil dari public/index.php.
 */

define('BASE_PATH', dirname(__DIR__));

// ---------------------------------------------------------------------
// Autoloader PSR-4 sederhana (App\ => app/)
// ---------------------------------------------------------------------
spl_autoload_register(function (string $class) {
    $prefix = 'App\\';
    if (strncmp($prefix, $class, strlen($prefix)) !== 0) {
        return;
    }
    $relative = substr($class, strlen($prefix));
    $file = BASE_PATH . '/app/' . str_replace('\\', '/', $relative) . '.php';
    if (is_file($file)) {
        require $file;
    }
});

// ---------------------------------------------------------------------
// Muat helper global
// ---------------------------------------------------------------------
require_once BASE_PATH . '/app/Core/helpers.php';

// ---------------------------------------------------------------------
// Muat file .env ke $_ENV bila belum
// ---------------------------------------------------------------------
(function () {
    $file = BASE_PATH . '/.env';
    if (!is_file($file)) {
        return;
    }
    foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#') {
            continue;
        }
        if (strpos($line, '=') === false) {
            continue;
        }
        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        $len = strlen($value);
        if ($len >= 2 && (($value[0] === '"' && $value[$len - 1] === '"')
            || ($value[0] === "'" && $value[$len - 1] === "'"))) {
            $value = substr($value, 1, -1);
        }
        $_ENV[$key] = $value;
        putenv("$key=$value");
    }
})();

// ---------------------------------------------------------------------
// Konfigurasi
// ---------------------------------------------------------------------
$appConfig = require BASE_PATH . '/config/app.php';
$GLOBALS['config'] = [
    'app'      => $appConfig,
    'database' => require BASE_PATH . '/config/database.php',
];

date_default_timezone_set($appConfig['timezone']);
error_reporting(E_ALL);
ini_set('display_errors', $appConfig['debug'] ? '1' : '0');

// ---------------------------------------------------------------------
// Session
// ---------------------------------------------------------------------
\App\Core\Session::start($appConfig['session']);
