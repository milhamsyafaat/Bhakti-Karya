<?php

/**
 * Helper global. Tersedia di seluruh aplikasi dan view.
 */

if (!function_exists('env')) {
    function env(string $key, $default = null)
    {
        $value = $_ENV[$key] ?? getenv($key);
        if ($value === false || $value === null || $value === '') {
            return $default;
        }
        switch (strtolower((string) $value)) {
            case 'true':
            case '(true)':
                return true;
            case 'false':
            case '(false)':
                return false;
            case 'null':
                return null;
        }
        return $value;
    }
}

if (!function_exists('config')) {
    function config(string $path, $default = null)
    {
        $segments = explode('.', $path);
        $value = $GLOBALS['config'] ?? [];
        foreach ($segments as $seg) {
            if (is_array($value) && array_key_exists($seg, $value)) {
                $value = $value[$seg];
            } else {
                return $default;
            }
        }
        return $value;
    }
}

if (!function_exists('base_path')) {
    function base_path(string $path = ''): string
    {
        return BASE_PATH . ($path ? '/' . ltrim($path, '/') : '');
    }
}

if (!function_exists('url')) {
    function url(string $path = ''): string
    {
        return rtrim(config('app.url', ''), '/') . '/' . ltrim($path, '/');
    }
}

if (!function_exists('asset')) {
    function asset(string $path = ''): string
    {
        return url('assets/' . ltrim($path, '/'));
    }
}

if (!function_exists('e')) {
    function e($value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('rupiah')) {
    function rupiah($angka): string
    {
        return 'Rp' . number_format((float) $angka, 0, ',', '.');
    }
}

if (!function_exists('redirect')) {
    function redirect(string $path): void
    {
        $target = preg_match('#^https?://#i', $path) ? $path : url($path);
        header('Location: ' . $target);
        exit;
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        return \App\Core\Session::csrfToken();
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        return '<input type="hidden" name="_token" value="' . e(csrf_token()) . '">';
    }
}

if (!function_exists('old')) {
    function old(string $key, $default = '')
    {
        $old = \App\Core\Session::get('_old', []);
        $value = $old[$key] ?? $default;
        return e($value);
    }
}

if (!function_exists('flash')) {
    function flash(string $key, $value = null)
    {
        if (func_num_args() === 1) {
            return \App\Core\Session::pull('_flash.' . $key);
        }
        \App\Core\Session::set('_flash.' . $key, $value);
    }
}

if (!function_exists('auth')) {
    function auth(): \App\Core\Auth
    {
        return new \App\Core\Auth();
    }
}

if (!function_exists('pelanggan_auth')) {
    function pelanggan_auth(): \App\Core\AuthPelanggan
    {
        return new \App\Core\AuthPelanggan();
    }
}

if (!function_exists('product_image')) {
    /**
     * URL gambar produk dengan fallback placeholder lokal.
     * Mendukung path lokal (assets/uploads/...) dan URL eksternal.
     */
    function product_image(?string $url): string
    {
        $url = trim((string) $url);
        if ($url === '') {
            return asset('img/placeholder.svg');
        }
        if (preg_match('#^https?://#i', $url)) {
            return $url;
        }
        // Path lokal hasil upload
        if (strpos($url, 'assets/') === 0 && is_file(BASE_PATH . '/public/' . $url)) {
            return url($url);
        }
        return asset('img/placeholder.svg');
    }
}

if (!function_exists('onerror_image')) {
    /**
     * Atribut onerror: jika gambar eksternal gagal dimuat browser,
     * ganti otomatis ke placeholder lokal.
     */
    function onerror_image(): string
    {
        $ph = asset('img/placeholder.svg');
        return "this.onerror=null;this.src='{$ph}';";
    }
}
