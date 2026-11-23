<?php

namespace App\Core;

/**
 * Wrapper session + dukungan dot-notation + CSRF.
 */
class Session
{
    public static function start(string $name = 'bk_session'): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        // Pastikan folder penyimpanan session valid & writable.
        $savePath = session_save_path();
        $savePath = $savePath !== '' ? $savePath : sys_get_temp_dir();
        if (!is_dir($savePath) || !is_writable($savePath)) {
            $fallback = (defined('BASE_PATH') ? BASE_PATH : sys_get_temp_dir()) . '/storage/sessions';
            if (!is_dir($fallback)) {
                @mkdir($fallback, 0777, true);
            }
            if (is_dir($fallback) && is_writable($fallback)) {
                session_save_path($fallback);
            }
        }

        session_name($name);
        $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
        session_set_cookie_params([
            'lifetime' => 0,
            'path'     => '/',
            'httponly' => true,
            'secure'   => $secure,
            'samesite' => 'Lax',
        ]);
        session_start();
        if (!isset($_SESSION['_csrf'])) {
            $_SESSION['_csrf'] = bin2hex(random_bytes(16));
        }
    }

    public static function get(string $key, $default = null)
    {
        $segments = explode('.', $key);
        $value = $_SESSION;
        foreach ($segments as $seg) {
            if (is_array($value) && array_key_exists($seg, $value)) {
                $value = $value[$seg];
            } else {
                return $default;
            }
        }
        return $value;
    }

    public static function set(string $key, $value): void
    {
        $segments = explode('.', $key);
        $ref = &$_SESSION;
        foreach ($segments as $seg) {
            if (!isset($ref[$seg]) || !is_array($ref[$seg])) {
                $ref[$seg] = [];
            }
            $ref = &$ref[$seg];
        }
        $ref = $value;
    }

    public static function has(string $key): bool
    {
        return self::get($key, '__missing__') !== '__missing__';
    }

    public static function forget(string $key): void
    {
        $segments = explode('.', $key);
        $ref = &$_SESSION;
        foreach ($segments as $i => $seg) {
            if (!isset($ref[$seg])) {
                return;
            }
            if ($i === count($segments) - 1) {
                unset($ref[$seg]);
                return;
            }
            $ref = &$ref[$seg];
        }
    }

    public static function pull(string $key, $default = null)
    {
        $value = self::get($key, $default);
        self::forget($key);
        return $value;
    }

    public static function flash(string $key, $value): void
    {
        self::set('_flash.' . $key, $value);
    }

    public static function csrfToken(): string
    {
        return $_SESSION['_csrf'] ?? '';
    }

    public static function regenerate(): void
    {
        $_SESSION['_csrf'] = bin2hex(random_bytes(16));
    }

    public static function destroy(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
    }
}
